<?php
/**
 * 2022 - Moloni.com
 *
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Moloni
 * @copyright Moloni
 * @license   https://creativecommons.org/licenses/by-nd/4.0/
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

namespace Moloni\Builders\MoloniProduct;

use Shop;
use Image;
use Product;
use Combination;
use Configuration;
use StockAvailable;
use Moloni\Builders\MoloniProduct\Helpers\UpdateMoloniProductStock;
use Moloni\Builders\MoloniProduct\Helpers\Variants\FindVariant;
use Moloni\Enums\Boolean;
use Moloni\Enums\ProductVisibility;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Tools\Settings;
use Moloni\Traits\LogsTrait;

class ProductVariant
{
    use LogsTrait;

    /**
     * Moloni parent product
     *
     * @var array
     */
    protected $moloniParentProduct = [];

    /**
     * Moloni variant data
     *
     * @var array
     */
    protected $moloniVariant = [];

    /**
     * Combination
     *
     * @var Combination|null
     */
    protected $prestashopCombination;


    /**
     * Visibility
     *
     * @var int
     */
    protected $visibility;

    /**
     * Product name
     *
     * @var string
     */
    protected $name;

    /**
     * Product reference
     *
     * @var string
     */
    protected $reference;

    /**
     * Product identifications
     *
     * @var array
     */
    protected $identifications = [];

    /**
     * Product price
     *
     * @var float
     */
    protected $price = 0;

    /**
     * Has stock
     *
     * @var bool
     */
    protected $parentHasStock;

    /**
     * Has stock
     *
     * @var float
     */
    protected $stock = 0;

    /**
     * Warehouse
     *
     * @var int
     */
    protected $warehouseId;

    /**
     * Variant property values
     *
     * @var array
     */
    protected $propertyPairs = [];

    /**
     * Product image
     *
     * @var array
     */
    protected $image = [];


    /**
     * All variants that the parent product has
     *
     * @var array|null
     */
    protected $allMoloniVariants;

    /**
     * Constructor
     *
     * @param Combination $prestashopCombination
     * @param array|null $moloniParentProduct
     */
    public function __construct(Combination $prestashopCombination, ?array $moloniParentProduct = [])
    {
        $this->prestashopCombination = $prestashopCombination;

        $this->moloniParentProduct = $moloniParentProduct;
        $this->allMoloniVariants = $moloniParentProduct['variants'] ?? [];

        $this->init();
    }

    //          PUBLICS          //

    /**
     * Create data
     *
     * @return $this
     */
    public function init(): ProductVariant
    {
        $this
            ->setReference()
            ->setMoloniVariant()
            ->setParentHasStock()
            ->setName()
            ->setIdentifications()
            ->setPrice()
            ->setStock()
            ->setVisibility()
            ->setImage();

        return $this;
    }

    /**
     * Create variant information to save
     *
     * @return array
     */
    public function toArray(): array
    {
        $props = [
            'visible' => $this->visibility,
            'name' => $this->name,
            'price' => $this->price,
            'identifications' => $this->identifications,
            'propertyPairs' => $this->propertyPairs
        ];

        if ($this->variantExists()) {
            $props['productId'] = $this->getMoloniVariantId();
        } else if ($this->parentHasStock()) {
            $props['warehouseId'] = $this->warehouseId;
            $warehouses = [
                'warehouseId' => $this->warehouseId,
            ];

            if (!$this->parentExists()) {
                $warehouses['stock'] = $this->stock;
            }

            $props['warehouses'] = [$warehouses];
        }

        return $props;
    }

    /**
     * Update variant stock
     *
     * @return $this
     *
     * @throws MoloniProductException
     */
    public function updateStock(): ProductVariant
    {
        if (!$this->variantExists()) {
            return $this;
        }

        try {
            new UpdateMoloniProductStock($this->getMoloniVariantId(), $this->warehouseId, $this->stock, $this->moloniVariant['warehouses'], $this->reference, $this->shouldWriteLogs());
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error creating stock movement ({0})', ['{0}' => $this->reference], $e->getData());
        }

        return $this;
    }

    //          SETS          //

    /**
     * Finds moloni variant
     *
     * @return $this
     */
    public function setMoloniVariant(): ProductVariant
    {
        if ($this->parentExists()) {
            $variant = (new FindVariant($this->getPrestashopCombinationId(), $this->reference, $this->allMoloniVariants))->handle();

            if (!empty($variant)) {
                $this->moloniVariant = $variant;
            }
        }

        return $this;
    }

    /**
     * Variant visibility
     *
     * @return ProductVariant
     */
    public function setVisibility(): ProductVariant
    {
        $this->visibility = ProductVisibility::VISIBLE;

        return $this;
    }

    /**
     * Variant name
     *
     * @return ProductVariant
     */
    public function setName(): ProductVariant
    {
        $this->name = $this->moloniVariant['name'] ?? $this->reference ?? 'Variant';

        return $this;
    }

    /**
     * Variant reference
     *
     * @return ProductVariant
     */
    public function setReference(): ProductVariant
    {
        $this->reference = $this->prestashopCombination->reference;

        return $this;
    }

    /**
     * Set variant identifications
     *
     * @return ProductVariant
     */
    public function setIdentifications(): ProductVariant
    {
        $identifications = $this->moloniVariant['identifications'] ?? [];
        $identificators = ['EAN13', 'ISBN'];

        foreach ($identifications as $key => $identification) {
            if (in_array($identification['type'], $identificators, true)) {
                unset($identifications[$key]);
            }
        }

        if (!empty($this->prestashopCombination->ean13)) {
            $identifications[] = [
                'type' => 'EAN13',
                'text' => $this->prestashopCombination->ean13,
                'favorite' => false
            ];
        }

        if (!empty($this->prestashopCombination->isbn)) {
            $identifications[] = [
                'type' => 'ISBN',
                'text' => $this->prestashopCombination->isbn,
                'favorite' => false
            ];
        }

        $this->identifications = $identifications;

        return $this;
    }

    /**
     * Set variant price
     *
     * @return ProductVariant
     */
    public function setPrice(): ProductVariant
    {
        $this->price = Product::getPriceStatic($this->prestashopCombination->id_product, false, $this->prestashopCombination->id);

        return $this;
    }

    /**
     * Set warehouse id
     *
     * @param int|null $warehouseId
     *
     * @return ProductVariant
     */
    public function setWarehouseId(?int $warehouseId = null): ProductVariant
    {
        $this->warehouseId = (int)($warehouseId ?? Settings::get('syncStockToMoloniWarehouse') ?? 0);

        return $this;
    }

    /**
     * Set if variants has stock
     *
     * @param bool|null $parentHasStock
     * @return ProductVariant
     */
    public function setParentHasStock(?bool $parentHasStock = null): ProductVariant
    {
        $this->parentHasStock = $parentHasStock ?? (bool)Boolean::YES;

        return $this;
    }

    /**
     * Set variant stock
     *
     * @return ProductVariant
     */
    public function setStock(): ProductVariant
    {
        $this->stock = StockAvailable::getQuantityAvailableByProduct($this->prestashopCombination->id_product, $this->prestashopCombination->id);

        return $this;
    }

    /**
     * Set variant image
     *
     * @return $this
     */
    public function setImage(): ProductVariant
    {
        $languageId = (int)Configuration::get('PS_LANG_DEFAULT');
        $shopId = (int)Shop::getContextShopID();

        $image = Image::getBestImageAttribute($shopId, $languageId, $this->prestashopCombination->id_product, $this->prestashopCombination->id);

        if ($image) {
            $this->image = $image;
        }

        return $this;
    }

    /**
     * Set variant property pairs
     *
     * @param array|null $propertyPairs
     *
     * @return ProductVariant
     */
    public function setPropertyPairs(?array $propertyPairs = []): ProductVariant
    {
        $this->propertyPairs = $propertyPairs;

        return $this;
    }

    //          GETS          //

    /**
     * Get parent id
     *
     * @return int
     */
    public function getMoloniParentId(): int
    {
        if (empty($this->moloniParentProduct)) {
            return 0;
        }

        return (int)$this->moloniParentProduct['productId'];
    }

    /**
     * Get variant id
     *
     * @return int
     */
    public function getMoloniVariantId(): int
    {
        if (empty($this->moloniVariant)) {
            return 0;
        }

        return (int)$this->moloniVariant['productId'];
    }

    /**
     * Get combination id
     *
     * @return int
     */
    public function getPrestashopCombinationId(): int
    {
        return $this->prestashopCombination->id;
    }

    /**
     * Get variant property pairs
     *
     * @return array
     */
    public function getPropertyPairs(): array
    {
        return $this->propertyPairs;
    }

    /**
     * Get variant reference
     *
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * Get variant image
     *
     * @return array
     */
    public function getImage(): array
    {
        return $this->image;
    }

    //          Auxiliary          //

    /**
     * Checks if product has stock
     *
     * @return bool
     */
    protected function parentHasStock(): bool
    {
        return $this->parentHasStock;
    }

    /**
     * Checks if product has stock
     *
     * @return bool
     */
    protected function parentExists(): bool
    {
        return $this->getMoloniParentId() > 0;
    }

    /**
     * Check if current variant exists
     *
     * @return bool
     */
    protected function variantExists(): bool
    {
        return $this->getMoloniVariantId() > 0;
    }
}
