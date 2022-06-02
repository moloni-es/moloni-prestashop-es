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
use Moloni\Helpers\Settings;
use Moloni\Actions\Moloni\UpdateMoloniProductStock;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Enums\Boolean;
use Moloni\Enums\ProductVisibility;

class ProductVariant
{
    /**
     * Moloni variant data
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
    protected $hasStock = false;

    /**
     * Has stock
     *
     * @var float
     */
    protected $stock = 0;

    /**
     * Measurement unit
     *
     * @var int
     */
    protected $measurementUnitId = 0;

    /**
     * Warehouse
     *
     * @var int
     */
    protected $warehouseId;

    /**
     * Variant attibutes
     *
     * @var array|null
     */
    protected $attributes = [];

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
     * @param int $prestashopCombinationId
     * @param int $warehouseId
     * @param array|null $allMoloniVariants
     */
    public function __construct(int $prestashopCombinationId, ?array $allMoloniVariants = [])
    {
        $this->allMoloniVariants = $allMoloniVariants;
        $this->prestashopCombination = new Combination($prestashopCombinationId, Configuration::get('PS_LANG_DEFAULT'));

        $this->init();
    }

    //          PRIVATES          //

    /**
     * Create data
     *
     * @return $this
     */
    protected function init(): ProductVariant
    {
        $this
            ->setReference()
            ->setMoloniVariant()
            ->setName()
            ->setIdentifications()
            ->setAttributes()
            ->setPrice()
            ->setHasStock()
            ->setStock()
            ->setMeasurementUnitId()
            ->setWarehouseId()
            ->setVisibility();

        return $this;
    }

    //          PUBLICS          //

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
            'hasStock' => $this->hasStock,
            'price' => $this->price,
            'identifications' => $this->identifications,
            'measurementUnitId' => $this->measurementUnitId
        ];

        if ($this->variantExists()) {
            $props['productId'] = $this->moloniVariantId;
        } else {
            if (!empty($this->attributes)) {
                // todo: Adicionar attributos
            }

            if ($this->warehouseId > 0 && $this->variantHasStock()) {
                $props['warehouseId'] = $this->warehouseId;
                $props['warehouses'] = [[
                    'warehouseId' => $this->warehouseId,
                    'stock' => $this->stock,
                ]];
            }
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
        if (!$this->variantExists() || !$this->variantHasStock()) {
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

                if ($variant['reference'] === $this->reference) {
                    $existingVariant = $variant;

                    break;
                }
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
        $this->name = $this->moloniVariant['name'] ?? $this->reference;

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
     * Set has stock
     *
     * @return ProductVariant
     */
    public function setHasStock(): ProductVariant
    {
        $this->hasStock = $this->moloniVariant['hasStock'] ?? (bool)Boolean::YES;

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
     * Set measurement unit
     *
     * @return ProductVariant
     */
    public function setMeasurementUnitId(): ProductVariant
    {
        $this->measurementUnitId = (int)(Settings::get('measurementUnit') ?? 0);

        return $this;
    }

    /**
     * Set variant attributes
     *
     * @return ProductVariant
     */
    public function setAttributes(): ProductVariant
    {
        // todo: get/create attributes in Moloni
        $this->attributes = [];

        return $this;
    }

    //          Auxiliary          //

    /**
     * Checks if product has stock
     *
     * @return bool
     */
    protected function variantHasStock(): bool
    {
        return $this->hasStock;
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
