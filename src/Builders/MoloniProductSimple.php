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

namespace Moloni\Builders;

use Address;
use Category;
use Configuration;
use Country;
use Image;
use Product;
use StockAvailable;
use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Interfaces\BuilderInterface;
use Moloni\Builders\MoloniProduct\Helpers\UpdateMoloniSimpleProductImage;
use Moloni\Builders\MoloniProduct\Helpers\UpdateMoloniProductStock;
use Moloni\Builders\MoloniProduct\ProductCategory;
use Moloni\Builders\MoloniProduct\ProductTax;
use Moloni\Enums\Boolean;
use Moloni\Enums\Countries;
use Moloni\Enums\ProductType;
use Moloni\Enums\ProductVisibility;
use Moloni\Enums\SyncFields;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniException;
use Moloni\Exceptions\Product\MoloniProductCategoryException;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Exceptions\Product\MoloniProductTaxException;
use Moloni\Tools\Logs;
use Moloni\Tools\Settings;
use Moloni\Traits\LogsTrait;

class MoloniProductSimple implements BuilderInterface
{
    use LogsTrait;

    /**
     * Moloni roduct
     *
     * @var array
     */
    protected $moloniProduct;


    /**
     * Visibility
     *
     * @var int
     */
    protected $visibility;

    /**
     * Category
     *
     * @var ProductCategory|null
     */
    protected $category;

    /**
     * Moloni product type
     *
     * @var int
     */
    protected $type;

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
     * Product summary
     *
     * @var string
     */
    protected $summary;

    /**
     * Product identifications
     *
     * @var array
     */
    protected $identifications = [];

    /**
     * Product cover image
     *
     * @var array
     */
    protected $coverImage = [];

    /**
     * Product price
     *
     * @var float
     */
    protected $price;

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
    protected $measurementUnitId;

    /**
     * Warehouse
     *
     * @var int
     */
    protected $warehouseId;

    /**
     * Tax builder
     *
     * @var ProductTax|null
     */
    protected $tax;

    /**
     * Eco tax
     *
     * @var float
     */
    protected $ecoTax;

    /**
     * Product Exemption reason
     *
     * @var string
     */
    protected $exemptionReason = '';


    /**
     * Fields that will be synced
     *
     * @var array
     */
    protected $syncFields;

    /**
     * Prestashop product object
     *
     * @var Product
     */
    protected $prestashopProduct;

    /**
     * Constructor
     *
     * @throws MoloniProductException
     */
    public function __construct(Product $prestashopProduct, ?array $syncFields = null)
    {
        $this->prestashopProduct = $prestashopProduct;

        $this->syncFields = $syncFields ?? Settings::get('productSyncFields') ?? SyncFields::getDefaultFields();

        $this->init();
    }

    //          PRIVATES          //

    /**
     * Create data
     *
     * @throws MoloniProductException
     */
    protected function init(): MoloniProductSimple
    {
        $this
            ->verifyPrestaProduct()
            ->setReference()
            ->fetchProductFromMoloni()
            ->setCoverImage()
            ->setVisibility()
            ->setName()
            ->setSummary()
            ->setCategory()
            ->setHasStock()
            ->setStock()
            ->setPrice()
            ->setType()
            ->setTax()
            ->setEcoTax()
            ->setWarehouseId()
            ->setIdentifications()
            ->setMeasurementUnitId();

        return $this;
    }

    /**
     * Create product information to save
     *
     * @return array
     */
    protected function toArray(): array
    {
        $props = [
            'visible' => $this->visibility,
            'type' => $this->type,
            'reference' => $this->reference,
            'name' => $this->name,
            'hasStock' => $this->hasStock,
            'price' => $this->price,
            'summary' => $this->summary,
            'identifications' => $this->identifications,
            'measurementUnitId' => $this->measurementUnitId
        ];

        if (!$this->shouldSyncName()) {
            unset($props['name']);
        }

        if (!$this->shouldSyncDescription()) {
            unset($props['summary']);
        }

        if (!$this->shouldSyncPrice()) {
            unset($props['price']);
        }

        if (!empty($this->category)) {
            $props['productCategoryId'] = $this->category->getProductCategoryId();
        }

        if (!empty($this->tax)) {
            $props['taxes'][] = $this->tax->toArray();
        } else {
            $props['taxes'] = [];
        }

        if (!empty($this->exemptionReason)) {
            $props['exemptionReason'] = $this->exemptionReason;
        }

        if ($this->productExists()) {
            $props['productId'] = $this->getMoloniProductId();
        } elseif ($this->warehouseId > 0 && $this->productHasStock()) {
            $props['warehouseId'] = $this->warehouseId;
            $props['warehouses'] = [[
                'warehouseId' => $this->warehouseId,
                'stock' => $this->stock,
            ]];
        }

        return $props;
    }

    /**
     * Actions run after a save
     *
     * @return void
     */
    protected function afterSave(): void
    {
        if (!empty($this->coverImage) && $this->shouldSyncImage()) {
            new UpdateMoloniSimpleProductImage($this->coverImage, $this->getMoloniProductId());
        }
    }

    /**
     * Actions run before an update
     *
     * @return void
     *
     * @throws MoloniProductException
     */
    protected function beforeUpdate(): void
    {
        if (!empty($this->moloniProduct['variants']) && $this->productExists()) {
            throw new MoloniProductException('Cannot update product in Moloni. Product types do not match');
        }
    }

    /**
     * Search product in Moloni
     *
     * @throws MoloniProductException
     */
    protected function fetchProductFromMoloni(): MoloniProductSimple
    {
        $this->getByReference();

        return $this;
    }

    //          PUBLICS          //

    /**
     * Create a product in Moloni
     *
     * @throws MoloniProductException
     */
    public function insert(): MoloniProductSimple
    {
        $props = $this->toArray();

        try {
            $mutation = MoloniApiClient::products()
                ->mutationProductCreate(['data' => $props]);

            $moloniProduct = $mutation['data']['productCreate']['data'] ?? [];

            if (!empty($moloniProduct)) {
                $this->moloniProduct = $moloniProduct;

                if ($this->shouldWriteLogs()) {
                    Logs::addInfoLog(['Product created in Moloni ({0})', ['{0}' => $this->reference]], ['props' => $props]);
                }

                $this->afterSave();
            } else {
                throw new MoloniProductException('Error creating product ({0})', ['{0}' => $this->reference], [
                    'mutation' => $mutation
                ]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error creating product ({0})', ['{0}' => $this->reference], $e->getData());
        }

        return $this;
    }

    /**
     * Update a product in Moloni
     *
     * @throws MoloniProductException
     */
    public function update(): MoloniProductSimple
    {
        $this->beforeUpdate();

        $props = $this->toArray();

        try {
            $mutation = MoloniApiClient::products()
                ->mutationProductUpdate(['data' => $props]);

            $moloniProduct = $mutation['data']['productUpdate']['data'] ?? [];
            $productId = $moloniProduct['productId'] ?? 0;

            if ($productId > 0) {
                $this->moloniProduct = $moloniProduct;

                if ($this->shouldWriteLogs()) {
                    Logs::addInfoLog(['Product updated in Moloni ({0})', ['{0}' => $this->reference]], ['props' => $props]);
                }

                $this->afterSave();
            } else {
                throw new MoloniProductException('Error updating product ({0})', ['{0}' => $this->reference], [
                    'mutation' => $mutation
                ]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error updating product ({0})', ['{0}' => $this->reference], $e->getData());
        }

        return $this;
    }

    /**
     * Update product stock in Moloni
     *
     * @throws MoloniProductException
     */
    public function updateStock(): MoloniProductSimple
    {
        if ($this->productExists() && $this->productHasStock()) {
            try {
                new UpdateMoloniProductStock($this->getMoloniProductId(), $this->warehouseId, $this->stock, $this->moloniProduct['warehouses'], $this->reference, $this->shouldWriteLogs());
            } catch (MoloniApiException $e) {
                throw new MoloniProductException('Error creating stock movement ({0})', ['{0}' => $this->reference], $e->getData());
            }
        }

        return $this;
    }

    //          GETS          //

    /**
     * Moloni product id getter
     *
     * @return int
     */
    public function getMoloniProductId(): int
    {
        if (empty($this->moloniProduct)) {
            return 0;
        }

        return (int)$this->moloniProduct['productId'];
    }

    /**
     * Moloni product getter
     *
     * @return array
     */
    public function getMoloniProduct(): array
    {
        return $this->moloniProduct;
    }

    //          SETS          //

    /**
     * Set product visibility
     *
     * @return $this
     */
    public function setVisibility(): MoloniProductSimple
    {
        $this->visibility = ProductVisibility::VISIBLE;

        return $this;
    }

    /**
     * Set product reference
     *
     * @return $this
     */
    public function setReference(): MoloniProductSimple
    {
        $reference = $this->prestashopProduct->reference;

        if (empty($reference)) {
            $reference = $this->prestashopProduct->id;
        }

        $this->reference = $reference;

        return $this;
    }

    /**
     * Set product name
     *
     * @return $this
     */
    public function setName(): MoloniProductSimple
    {
        $this->name = $this->prestashopProduct->name;

        return $this;
    }

    /**
     * Set product summary
     *
     * @return $this
     */
    public function setSummary(): MoloniProductSimple
    {
        $this->summary = strip_tags($this->prestashopProduct->description_short);

        return $this;
    }

    /**
     * Set product type
     *
     * @return $this
     */
    public function setPrice(): MoloniProductSimple
    {
        $this->price = $this->prestashopProduct->getPriceWithoutReduct(true, null, 2);

        return $this;
    }

    /**
     * Set product type
     *
     * @return MoloniProductSimple
     */
    public function setType(): MoloniProductSimple
    {
        $this->type = ProductType::PRODUCT;

        return $this;
    }

    /**
     * Set has stock
     *
     * @return MoloniProductSimple
     */
    public function setHasStock(): MoloniProductSimple
    {
        $this->hasStock = $this->moloniProduct['hasStock'] ?? (bool)Boolean::YES;

        return $this;
    }

    /**
     * Set stock quantity
     *
     * @return $this
     */
    public function setStock(): MoloniProductSimple
    {
        $this->stock = StockAvailable::getQuantityAvailableByProduct($this->prestashopProduct->id);

        return $this;
    }

    /**
     * Sets product tax
     *
     * @throws MoloniProductTaxException
     */
    public function setTax(): MoloniProductSimple
    {
        try {
            $mutation = MoloniApiClient::companies()->queryCompany();

            $address = new Address();
            $address->id_country = Country::getByIso($mutation['fiscalZone']['fiscalZone'] ?? 'ES');

            $taxRate = (float)$this->prestashopProduct->getTaxesRate($address);

            if ($taxRate > 0) {
                $fiscalZone = [
                    'code' => $mutation['fiscalZone']['fiscalZone'] ?? 'ES',
                    'countryId' => $mutation['country']['countryId'] ?? Countries::SPAIN
                ];

                $taxBuilder = new ProductTax($taxRate, $fiscalZone, 1);
                $taxBuilder->search();

                if ($taxBuilder->getTaxId() === 0) {
                    $taxBuilder->insert();
                }

                $this->tax = $taxBuilder;
            } else {
                $this->exemptionReason = Settings::get('exemptionReasonProduct');
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductTaxException('Error fetching company data', [], $e->getData());
        } catch (MoloniException $e) {
            throw new MoloniProductTaxException('Error creating tax', [], $e->getData());
        }

        return $this;
    }

    /**
     * Sets product eco-tax
     *
     * @return $this
     */
    public function setEcoTax(): MoloniProductSimple
    {
        $ecoTax = (float)$this->prestashopProduct->ecotax;

        if ($ecoTax > 0) {
            $this->price -= $ecoTax;

            //todo: what else is needed?
        }

        $this->ecoTax = $ecoTax;

        return $this;
    }

    /**
     * Set product warehouse
     *
     * @return $this
     *
     * @throws MoloniProductException
     */
    public function setWarehouseId(): MoloniProductSimple
    {
        $warehouseId = (int)Settings::get('syncStockToMoloniWarehouse');

        if (in_array($warehouseId, [0, 1])) {
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
                $query = MoloniApiClient::warehouses()->queryWarehouses($params);

                if (!empty($query)) {
                    $warehouseId = $query[0]['warehouseId'];
                } else {
                    throw new MoloniProductException('Company does not have a default warehouse, please select one');
                }
            } catch (MoloniApiException $e) {
                throw new MoloniProductException('Error fetching default company warehouse', [], $e->getData());
            }
        }

        $this->warehouseId = $warehouseId;

        return $this;
    }

    /**
     * Set product category
     *
     * @return $this
     *
     * @throws MoloniProductCategoryException
     */
    public function setCategory(): MoloniProductSimple
    {
        if (!$this->shouldSyncCategories()) {
            return $this;
        }

        $categoriesNames = [];
        $categoriesIds = $this->prestashopProduct->getCategories();

        if (!empty($categoriesIds)) {
            $languageId = (int)Configuration::get('PS_LANG_DEFAULT');

            foreach ($categoriesIds as $categoriesId) {
                // Skip root categories
                if (in_array((int)$categoriesId, [1, 2])) {
                    continue;
                }

                $name = (new Category($categoriesId, $languageId))->name ?? '';

                // For some reason sometimes this comes empty
                if (empty($name)) {
                    continue;
                }

                $categoriesNames[] = $name;
            }
        }

        if (empty($categoriesNames)) {
            $categoriesNames = ['Prestashop'];
        }

        try {
            $parentId = 0;

            foreach ($categoriesNames as $category) {
                $builder = new ProductCategory($category, $parentId);

                $builder->search();

                if ($builder->getProductCategoryId() === 0) {
                    $builder->insert();
                }

                $parentId = $builder->getProductCategoryId();
            }

            /** @noinspection PhpUndefinedVariableInspection */
            $this->category = $builder;
        } catch (MoloniException $e) {
            throw new MoloniProductCategoryException($e->getMessage(), $e->getIdentifiers(), $e->getData());
        }

        return $this;
    }

    /**
     * Set product measurement unit
     *
     * @return $this
     */
    public function setMeasurementUnitId(): MoloniProductSimple
    {
        $this->measurementUnitId = (int)(Settings::get('measurementUnit') ?? 0);

        return $this;
    }

    /**
     * Set product image
     *
     * @return $this
     */
    public function setCoverImage(): MoloniProductSimple
    {
        /** @var array|null $coverImage */
        $coverImage = Image::getCover($this->prestashopProduct->id);

        $this->coverImage = $coverImage ?? [];

        return $this;
    }

    /**
     * Set product identifications
     *
     * @return MoloniProductSimple
     */
    public function setIdentifications(): MoloniProductSimple
    {
        $identifications = $this->moloniProduct['identifications'] ?? [];
        $identificators = ['EAN13', 'ISBN'];

        foreach ($identifications as $key => $identification) {
            if (in_array($identification['type'], $identificators, true)) {
                unset($identifications[$key]);
            }
        }

        if (!empty($this->prestashopProduct->ean13)) {
            $identifications[] = [
                'type' => 'EAN13',
                'text' => $this->prestashopProduct->ean13,
                'favorite' => false
            ];
        }

        if (!empty($this->prestashopProduct->isbn)) {
            $identifications[] = [
                'type' => 'ISBN',
                'text' => $this->prestashopProduct->isbn,
                'favorite' => false
            ];
        }

        $this->identifications = $identifications;

        return $this;
    }

    //          REQUESTS          //

    /**
     * Finds by reference
     *
     * @throws MoloniProductException
     */
    protected function getByReference(): MoloniProductSimple
    {
        $variables = [
            'options' => [
                'search' => [
                    'field' => 'reference',
                    'value' => $this->reference,
                ],
            ],
        ];

        try {
            $query = MoloniApiClient::products()
                ->queryProducts($variables);

            if (!empty($query)) {
                $moloniProduct = $query[0];

                $this->moloniProduct = $moloniProduct;
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error fetching product by reference: ({0})', ['{0}' => $this->reference], $e->getData());
        }

        return $this;
    }

    //          Auxiliary          //

    /**
     * Check if current product exists
     *
     * @return bool
     */
    protected function productExists(): bool
    {
        return $this->getMoloniProductId() > 0;
    }

    /**
     * Checks if product has stock
     *
     * @return bool
     */
    protected function productHasStock(): bool
    {
        return $this->hasStock;
    }

    //          VERIFICATIONS          //

    /**
     * Verify requirements to create product
     *
     * @throws MoloniProductException
     */
    protected function verifyPrestaProduct(): MoloniProductSimple
    {
        if (empty($this->prestashopProduct->id)) {
            throw new MoloniProductException('Prestashop product not found');
        }

        return $this;
    }

    /**
     * Should sync product name
     *
     * @return bool
     */
    protected function shouldSyncName(): bool
    {
        return !$this->productExists() || in_array(SyncFields::NAME, $this->syncFields, true);
    }

    /**
     * Should sync product price
     *
     * @return bool
     */
    protected function shouldSyncPrice(): bool
    {
        return !$this->productExists() || in_array(SyncFields::PRICE, $this->syncFields, true);
    }

    /**
     * Should sync product description
     *
     * @return bool
     */
    protected function shouldSyncDescription(): bool
    {
        return in_array(SyncFields::DESCRIPTION, $this->syncFields, true);
    }

    /**
     * Should sync product categories
     *
     * @return bool
     */
    protected function shouldSyncCategories(): bool
    {
        return in_array(SyncFields::CATEGORIES, $this->syncFields, true);
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
}
