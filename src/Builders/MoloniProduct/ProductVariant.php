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

use Combination;
use Configuration;
use Image;
use Moloni\Builders\MoloniProduct\Helpers\UpdateMoloniProductStock;
use Moloni\Enums\Boolean;
use Moloni\Enums\ProductVisibility;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Tools\Settings;
use Product;
use Shop;
use StockAvailable;

class ProductVariant
{
    /**
     * Moloni parent product id
     *
     * @var int
     */
    protected $moloniParentProductId = 0;

    /**
     * Moloni variant id
     *
     * @var int
     */
    protected $moloniVariantId = 0;

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
     * @param array|null $allMoloniVariants
     */
    public function __construct(Combination $prestashopCombination, ?array $allMoloniVariants = [])
    {
        $this->prestashopCombination = $prestashopCombination;
        $this->allMoloniVariants = $allMoloniVariants;
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
            ->setPropertyPairs();

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
            $props['productId'] = $this->moloniVariantId;
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
            new UpdateMoloniProductStock($this->moloniVariantId, $this->warehouseId, $this->stock, $this->moloniVariant['warehouses'], $this->reference);
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error creating stock movement ({0})', ['{0}' => $this->reference], $e->getData());
        }

        return $this;
    }

    //          GETS          //

    /**
     * Get variant id
     *
     * @return int
     */
    public function getMoloniVariantId(): int
    {
        return $this->moloniVariantId;
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
     * Get variant image
     *
     * @return array
     */
    public function getImage(): array
    {
        return $this->image;
    }

    //          SETS          //

    public function setMoloniVariant(?array $moloniVariant = []): ProductVariant
    {
        $existingVariant = [];

        // todo: verificar aqui tabela de associação

        if (!empty($moloniVariant)) {
            $existingVariant = $moloniVariant;
        } else {
            foreach ($this->allMoloniVariants as $variant) {
                if ((int)$variant['visible'] === Boolean::NO) {
                    continue;
                }

                // todo: verificar aqui tabela de associação
            }
        }

        $this->moloniVariant = $existingVariant;
        $this->moloniVariantId = $existingVariant['productId'] ?? 0;

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

        $this->image = Image::getBestImageAttribute($shopId, $languageId, $this->prestashopCombination->id_product, $this->prestashopCombination->id);

        return $this;
    }

    /**
     * Set parent id
     *
     * @param int $moloniParentProductId
     *
     * @return ProductVariant
     */
    public function setMoloniParentProductId(int $moloniParentProductId): ProductVariant
    {
        $this->moloniParentProductId = $moloniParentProductId;

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
        return $this->moloniParentProductId > 0;
    }

    /**
     * Check if current variant exists
     *
     * @return bool
     */
    protected function variantExists(): bool
    {
        return $this->moloniVariantId > 0;
    }
}
