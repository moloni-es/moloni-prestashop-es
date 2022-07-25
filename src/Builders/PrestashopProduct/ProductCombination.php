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

namespace Moloni\Builders\PrestashopProduct;

use Product;
use Combination;
use PrestaShopException;
use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Interfaces\BuilderInterface;
use Moloni\Builders\PrestashopProduct\Helpers\UpdatePrestaProductStock;
use Moloni\Builders\PrestashopProduct\Helpers\Combinations\FindOrCreateCombination;
use Moloni\Builders\PrestashopProduct\Helpers\Combinations\UpdatePrestaCombinationImage;
use Moloni\Enums\Boolean;
use Moloni\Enums\SyncFields;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductCombinationException;
use Moloni\Tools\Logs;
use Moloni\Tools\Settings;
use Moloni\Traits\LogsTrait;
use Moloni\Traits\AttributesTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductCombination implements BuilderInterface
{
    use AttributesTrait;
    use LogsTrait;

    /**
     * Moloni variant
     *
     * @var array|null
     */
    protected $moloniVariant;

    /**
     * Moloni parent product
     *
     * @var array
     */
    protected $moloniParent;

    /**
     * Prestashop product
     *
     * @var Product|null
     */
    protected $prestashopProduct;

    /**
     * Prestashop combination
     *
     * @var Combination|null
     */
    protected $prestashopCombination;


    /**
     * Variant name
     *
     * @var string
     */
    protected $name = '';

    /**
     * Variant reference
     *
     * @var string
     */
    protected $reference = '';

    /**
     * Variant isbn
     *
     * @var string
     */
    protected $isbn = '';

    /**
     * Variant ean13
     *
     * @var string
     */
    protected $ean13 = '';

    /**
     * Variant price
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
     * Warehouse
     *
     * @var int
     */
    protected $warehouseId = 0;

    /**
     * Has stock
     *
     * @var float
     */
    protected $stock = 0;

    /**
     * Variant image path
     *
     * @var string
     */
    protected $imagePath = '';

    /**
     * Combination attributes
     *
     * @var array
     */
    protected $attributes = [];


    /**
     * Fields that will be synced
     *
     * @var array
     */
    protected $syncFields;

    /**
     * Constructor
     *
     * @param Product $prestashopProduct
     * @param array $moloniParent
     * @param array $moloniVariant
     * @param array|null $syncFields
     */
    public function __construct(Product $prestashopProduct, array $moloniParent, array $moloniVariant, ?array $syncFields = null)
    {
        $this->moloniVariant = $moloniVariant;
        $this->moloniParent = $moloniParent;
        $this->prestashopProduct = $prestashopProduct;

        $this->syncFields = $syncFields ?? Settings::get('productSyncFields') ?? SyncFields::getDefaultFields();

        $this->init();
    }

    //          PRIVATES          //

    /**
     * Create data
     *
     * @return $this
     */
    protected function init(): ProductCombination
    {
        $this
            ->setReference()
            ->setName()
            ->setHasStock()
            ->setStock()
            ->setPrice()
            ->setIdentifications()
            ->setImagePath()
            ->setWarehouseId()
            ->setAttributes()
            ->fetchCombinationFromPresta();

        return $this;
    }

    /**
     * After insert actions
     *
     * @return void
     */
    protected function afterInsert(): void
    {
        if (!empty($this->attributes)) {
            $this->prestashopCombination->setAttributes($this->attributes);
        }
    }

    /**
     * After save actions
     *
     * @return void
     */
    protected function afterSave(): void
    {
        if (!empty($this->imagePath) && $this->shouldSyncImage()) {
            new UpdatePrestaCombinationImage($this->prestashopCombination, $this->imagePath);
        }
    }

    /**
     * Finds Prestashop combination
     *
     * @return $this
     */
    protected function fetchCombinationFromPresta(): ProductCombination
    {
        if ($this->parentExists()) {
            $combination = (new FindOrCreateCombination($this->getMoloniVariantId(), $this->prestashopProduct, $this->reference, $this->attributes))->handle();
        } else {
            $combination = new Combination();
        }

        $this->prestashopCombination = $combination;

        return $this;
    }

    /**
     * Set prestashop combination values
     *
     * @return $this
     */
    protected function fillPrestaCombination(): ProductCombination
    {
        if ($this->shouldSyncPrice()) {
            $this->prestashopCombination->price = $this->price;
        }

        $this->prestashopCombination->reference = $this->reference;
        $this->prestashopCombination->id_product = $this->prestashopProduct->id;
        $this->prestashopCombination->ean13 = $this->ean13;
        $this->prestashopCombination->isbn = $this->isbn;

        return $this;
    }

    //          PUBLICS          //

    /**
     * Insert variant in prestashop
     *
     * @return void
     *
     * @throws MoloniProductCombinationException
     */
    public function insert(): void
    {
        $this->fillPrestaCombination();

        try {
            $this->prestashopCombination->save();

            if ($this->shouldWriteLogs()) {
                Logs::addInfoLog(['Combination created in Prestashop ({0})', ['{0}' => $this->reference]], ['moloniVariant' => $this->moloniVariant]);
            }

            $this->afterInsert();
            $this->afterSave();
        } catch (PrestaShopException $e) {
            throw new MoloniProductCombinationException('Error creating combination ({0})', ['{0}' => $this->reference], [
                'moloniVariant' => $this->moloniVariant
            ]);
        }
    }

    /**
     * Update variant in prestashop
     *
     * @return void
     *
     * @throws MoloniProductCombinationException
     */
    public function update(): void
    {
        $this->fillPrestaCombination();

        try {
            $this->prestashopCombination->save();

            if ($this->shouldWriteLogs()) {
                Logs::addInfoLog(['Combination updated in Prestashop ({0})', ['{0}' => $this->reference]], ['moloniVariant' => $this->moloniVariant]);
            }

            $this->afterSave();
        } catch (PrestaShopException $e) {
            throw new MoloniProductCombinationException('Error updating combination ({0})', ['{0}' => $this->reference], [
                'moloniVariant' => $this->moloniVariant,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update variant stock in prestashop
     *
     * @return void
     */
    public function updateStock(): void
    {
        if (!$this->combinationExists() || !$this->combinationHasStock()) {
            return;
        }

        new UpdatePrestaProductStock((int)$this->prestashopProduct->id, (int)$this->prestashopCombination->id, $this->reference, $this->stock, $this->shouldWriteLogs());
    }

    //          GETS          //

    /**
     * Get Moloni variant
     *
     * @return array
     */
    public function getMoloniVariant(): array
    {
        return $this->moloniVariant;
    }

    /**
     * Get Moloni variant id
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
     * Get Prestashop combination
     */
    public function getCombination(): ?Combination
    {
        return $this->prestashopCombination;
    }

    /**
     * Get Prestashop combination id
     */
    public function getCombinationId(): int
    {
        return (int)$this->prestashopCombination->id;
    }

    /**
     * Get combination reference
     *
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    //          SETS          //

    /**
     * Set variant name
     *
     * @return $this
     */
    public function setName(): ProductCombination
    {
        $this->name = $this->moloniVariant['name'];

        return $this;
    }

    /**
     * Set variant reference
     *
     * @return $this
     */
    public function setReference(): ProductCombination
    {
        $this->reference = $this->moloniVariant['reference'] ?? '';

        return $this;
    }

    /**
     * Set variant identifications
     *
     * @return $this
     */
    public function setIdentifications(): ProductCombination
    {
        $isbn = '';
        $ean13 = '';

        if (!empty($this->moloniVariant['identifications'])) {
            foreach ($this->moloniVariant['identifications'] as $identification) {
                if ($identification['type'] === 'ISBN') {
                    $isbn = $identification['text'];

                    continue;
                }

                if ($identification['type'] === 'EAN13') {
                    $ean13 = $identification['text'];

                    continue;
                }
            }
        }

        $this->isbn = $isbn;
        $this->ean13 = $ean13;

        return $this;
    }

    /**
     * Set variant price
     *
     * @return $this
     */
    public function setPrice(): ProductCombination
    {
        $this->price = round($this->moloniParent['price'] - $this->moloniVariant['price'], 5);

        return $this;
    }

    /**
     * Set variant warehouse
     *
     * @return $this
     */
    public function setWarehouseId(): ProductCombination
    {
        $warehouseId = Settings::get('syncStockToPrestashopWarehouse');

        if (empty($warehouseId)) {
            $params = [
                'options' => [
                    'filter' => [
                        'field' => 'isDefault',
                        'comparison' => 'eq',
                        'value' => "1"
                    ],
                ]
            ];

            try {
                $mutation = MoloniApiClient::warehouses()
                    ->queryWarehouses($params);

                if (!empty($mutation)) {
                    $warehouseId = $mutation[0]['warehouseId'];
                }
            } catch (MoloniApiException $e) {
                $warehouseId = 1;
            }
        }

        $this->warehouseId = (int)$warehouseId;

        return $this;
    }

    /**
     * Set variant has stock
     *
     * @return $this
     */
    public function setHasStock(): ProductCombination
    {
        $this->hasStock = $this->moloniVariant['hasStock'] ?? (bool)Boolean::YES;

        return $this;
    }

    /**
     * Set variant stock
     *
     * @return $this
     */
    public function setStock(): ProductCombination
    {
        if ($this->combinationHasStock()) {
            $stock = 0;

            if ($this->warehouseId === 1) {
                $stock = (float)($this->moloniVariant['stock'] ?? 0);
            } else {
                foreach ($this->moloniVariant['warehouses'] as $warehouse) {
                    $stock = (float)$warehouse['stock'];

                    if ((int)$warehouse['warehouseId'] === $this->warehouseId) {
                        break;
                    }
                }
            }

            $this->stock = $stock;
        }

        return $this;
    }

    /**
     * Set image path
     *
     * @return $this
     */
    public function setImagePath(): ProductCombination
    {
        $imagePath = '';

        if (!empty($this->moloniVariant) && !empty($this->moloniVariant['img'])) {
            $imagePath = $this->moloniVariant['img'];
        }

        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * Set product attributes
     *
     * @return $this
     */
    public function setAttributes(): ProductCombination
    {
        $attributes = [];

        foreach ($this->moloniVariant['propertyPairs'] as $propertyPair) {
            $groupName = $propertyPair['property']['name'];
            $attributeName = $propertyPair['propertyValue']['value'];

            $groupId = $this->getAttributeGroupByName($groupName);
            $attributeId = $this->getAttributeByName($attributeName, $groupId);

            $attributes[] = $attributeId;
        }

        $this->attributes = $attributes;

        return $this;
    }

    //          VERIFICATIONS          //

    /**
     * Should sync variant price
     *
     * @return bool
     */
    protected function shouldSyncPrice(): bool
    {
        return !$this->combinationExists() || in_array(SyncFields::PRICE, $this->syncFields, true);
    }

    /**
     * Should sync product image
     *
     * @return bool
     */
    protected function shouldSyncImage(): bool
    {
        return in_array(SyncFields::IMAGE, $this->syncFields, true);
    }

    //          Auxiliary          //

    /**
     * Returns if combination already exists
     *
     * @return bool
     */
    protected function parentExists(): bool
    {
        return $this->prestashopProduct->id > 0;
    }

    /**
     * Returns if combination already exists
     *
     * @return bool
     */
    protected function combinationExists(): bool
    {
        return $this->prestashopCombination->id > 0;
    }

    /**
     * Returns if product has stock
     *
     * @return bool
     */
    protected function combinationHasStock(): bool
    {
        return $this->hasStock === true;
    }
}
