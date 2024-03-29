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
use Moloni\Builders\MoloniProduct\Helpers\Variants\FindVariant;
use Moloni\Enums\Boolean;
use Moloni\Enums\ProductVisibility;
use Moloni\Enums\SyncFields;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Tools\Settings;
use Moloni\Traits\LogsTrait;
use Product;
use Shop;
use StockAvailable;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
     * Parent product name
     *
     * @var string
     */
    protected $parentName;

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
     * Fields that will be synced
     *
     * @var array
     */
    protected $syncFields;

    /**
     * Constructor
     *
     * @param Combination $prestashopCombination
     * @param string|null $parentName
     * @param array|null $moloniParentProduct
     * @param array|null $propertyPairs
     * @param array|null $syncFields
     */
    public function __construct(
        Combination $prestashopCombination,
        ?string $parentName = '',
        ?array $moloniParentProduct = [],
        ?array $propertyPairs = [],
        ?array $syncFields = null
    ) {
        $this->prestashopCombination = $prestashopCombination;

        $this->parentName = $parentName;
        $this->moloniParentProduct = $moloniParentProduct;
        $this->propertyPairs = $propertyPairs;

        $this->syncFields = $syncFields;

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
        ];

        if ($this->shouldSyncPrice()) {
            $props['price'] = $this->price;
        }

        if ($this->shouldSyncIdentifiers()) {
            $props['identifications'] = $this->identifications;
        }

        if ($this->variantExists()) {
            $props['productId'] = $this->getMoloniVariantId();
        } else {
            $props['propertyPairs'] = $this->propertyPairs;

            if ($this->parentHasStock()) {
                $props['warehouseId'] = $this->warehouseId;
                $warehouses = [
                    'warehouseId' => $this->warehouseId,
                ];

                if (!$this->parentExists()) {
                    $warehouses['stock'] = $this->stock;
                }

                $props['warehouses'] = [$warehouses];
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
        if (!$this->variantExists()) {
            return $this;
        }

        try {
            new UpdateMoloniProductStock(
                $this->getMoloniVariantId(),
                $this->warehouseId,
                $this->stock,
                $this->moloniVariant['warehouses'],
                $this->reference,
                $this->shouldWriteLogs()
            );
        } catch (MoloniApiException $e) {
            throw new MoloniProductException(
                'Error creating stock movement ({0})',
                ['{0}' => $this->reference],
                $e->getData()
            );
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
            $variant = (new FindVariant(
                $this->getPrestashopCombinationId(),
                $this->reference,
                $this->moloniParentProduct['variants'] ?? [],
                $this->propertyPairs
            ))->handle();

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
        switch (true) {
            case !empty($this->moloniVariant['name']):
                $this->name = $this->moloniVariant['name'];
                break;
            case !empty($this->parentName):
                $this->name = $this->parentName;
                break;
            case !empty($this->reference):
                $this->name = $this->reference;
                break;
            default:
                $this->name = 'Variant';
                break;
        }

        return $this;
    }

    /**
     * Variant reference
     *
     * @return ProductVariant
     */
    public function setReference(): ProductVariant
    {
        $reference = $this->prestashopCombination->reference;

        if (empty($reference)) {
            $reference = '';
        }

        $this->reference = $reference;

        return $this;
    }

    /**
     * Set variant identifications
     *
     * @return ProductVariant
     */
    public function setIdentifications(): ProductVariant
    {
        $identifications = [];

        $isEanFav = false;
        $isIsbnFav = false;
        $isUpcaFav = false;

        if (isset($this->moloniProduct['identifications']) && !empty($this->moloniProduct['identifications'])) {
            foreach ($this->moloniProduct['identifications'] as $identification) {
                switch ($identification['type']) {
                    case 'ISBN':
                        $isIsbnFav = $identification['favorite'];
                        continue 2;
                    case 'EAN13':
                        $isEanFav = $identification['favorite'];
                        continue 2;
                    case 'UPCA':
                        $isUpcaFav = $identification['favorite'];
                        continue 2;
                }

                $identifications[] = $identification;
            }
        }

        if (!empty($this->prestashopCombination->ean13)) {
            $identifications[] = [
                'type' => 'EAN13',
                'text' => $this->prestashopCombination->ean13,
                'favorite' => $isEanFav,
            ];
        }

        if (!empty($this->prestashopCombination->isbn)) {
            $identifications[] = [
                'type' => 'ISBN',
                'text' => $this->prestashopCombination->isbn,
                'favorite' => $isIsbnFav,
            ];
        }

        if (!empty($this->prestashopCombination->upc)) {
            $identifications[] = [
                'type' => 'UPCA',
                'text' => $this->prestashopCombination->upc,
                'favorite' => $isUpcaFav,
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
        $this->price = Product::getPriceStatic(
            $this->prestashopCombination->id_product,
            false,
            $this->prestashopCombination->id
        );

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
        $this->warehouseId = (int) ($warehouseId ?? Settings::get('syncStockToMoloniWarehouse') ?? 0);

        return $this;
    }

    /**
     * Set if variants has stock
     *
     * @param bool|null $parentHasStock
     *
     * @return ProductVariant
     */
    public function setParentHasStock(?bool $parentHasStock = null): ProductVariant
    {
        $this->parentHasStock = $parentHasStock ?? (bool) Boolean::YES;

        return $this;
    }

    /**
     * Set variant stock
     *
     * @param float|null $newStock
     *
     * @return ProductVariant
     */
    public function setStock(?float $newStock = null): ProductVariant
    {
        if ($newStock) {
            $this->stock = $newStock;

            return $this;
        }

        $this->stock = StockAvailable::getQuantityAvailableByProduct(
            $this->prestashopCombination->id_product,
            $this->prestashopCombination->id
        );

        return $this;
    }

    /**
     * Set variant image
     *
     * @return $this
     */
    public function setImage(): ProductVariant
    {
        $languageId = (int) Configuration::get('PS_LANG_DEFAULT');
        $shopId = (int) Shop::getContextShopID();

        $image = Image::getBestImageAttribute(
            $shopId,
            $languageId,
            $this->prestashopCombination->id_product,
            $this->prestashopCombination->id
        );

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

    /**
     * Set Moloni parent
     *
     * @param array $moloniParent
     *
     * @return ProductVariant
     */
    public function setMoloniParent(array $moloniParent): ProductVariant
    {
        $this->moloniParentProduct = $moloniParent;

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

        return (int) $this->moloniParentProduct['productId'];
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

        return (int) $this->moloniVariant['productId'];
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
     * Get variant data
     *
     * @return array
     */
    public function getMoloniVariant(): array
    {
        if (empty($this->moloniVariant)) {
            return [];
        }

        return $this->moloniVariant;
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

    //          VERIFICATIONS          //

    /**
     * Should sync variant price
     *
     * @return bool
     */
    protected function shouldSyncPrice(): bool
    {
        return !$this->variantExists() || in_array(SyncFields::PRICE, $this->syncFields, true);
    }

    /**
     * Should sync product identifiers (ISBN, EAN)
     *
     * @return bool
     */
    protected function shouldSyncIdentifiers(): bool
    {
        return in_array(SyncFields::IDENTIFIERS, $this->syncFields, true);
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
