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

use Product;
use Address;
use Category;
use Combination;
use Configuration;
use Country;
use Image;
use Moloni\Entity\MoloniProductAssociations;
use Moloni\Tools\ProductAssociations;
use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Interfaces\BuilderInterface;
use Moloni\Builders\MoloniProduct\Helpers\Variants\CreateMappingsAfterMoloniProductCreateOrUpdate;
use Moloni\Builders\MoloniProduct\Helpers\Variants\FindOrCreatePropertyGroup;
use Moloni\Builders\MoloniProduct\Helpers\Variants\UpdateMoloniVariantsProductImage;
use Moloni\Builders\MoloniProduct\ProductCategory;
use Moloni\Builders\MoloniProduct\ProductTax;
use Moloni\Builders\MoloniProduct\ProductVariant;
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

if (!defined('_PS_VERSION_')) {
    exit;
}

class MoloniProductWithVariants implements BuilderInterface
{
    use LogsTrait {
        disableLogs as traitDisableLogs;
    }

    /**
     * Moloni roduct
     *
     * @var array
     */
    protected $moloniProduct = [];


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
    protected $hasStock = true;

    /**
     * Measurement unit
     *
     * @var int
     */
    protected $measurementUnitId;

    /**
     * Product property group
     *
     * @var array
     */
    protected $propertyGroup = [];

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
     * Product variants
     *
     * @var ProductVariant[]
     */
    protected $variants = [];


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
    protected function init(): MoloniProductWithVariants
    {
        $this
            ->verifyPrestaProduct()
            ->setReference()
            ->fetchProductFromMoloni()
            ->setVisibility()
            ->setName()
            ->setSummary()
            ->setCategory()
            ->setHasStock()
            ->setPrice()
            ->setType()
            ->setTax()
            ->setEcoTax()
            ->setWarehouseId()
            ->setIdentifications()
            ->setMeasurementUnitId()
            ->setCoverImage()
            ->setPropertyGroup()
            ->setVariants();

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
            'identifications' => $this->identifications,
            'measurementUnitId' => $this->measurementUnitId,
            'variants' => [],
            'taxes' => [],
            'exemptionReason' => '',
        ];

        if ($this->shouldSyncName()) {
            $props['name'] = $this->name;
        }

        if ($this->shouldSyncDescription()) {
            $props['summary'] = $this->summary;
        }

        if ($this->shouldSyncPrice()) {
            $props['price'] = $this->price;
        }

        if (!empty($this->category)) {
            $props['productCategoryId'] = $this->category->getProductCategoryId();
        }

        if (!empty($this->tax)) {
            $props['taxes'][] = $this->tax->toArray();
        }

        if (!empty($this->exemptionReason)) {
            $props['exemptionReason'] = $this->exemptionReason;
        }

        if (!empty($this->propertyGroup)) {
            $props['propertyGroupId'] = $this->propertyGroup['propertyGroupId'];
        }

        foreach ($this->variants as $variant) {
            $props['variants'][] = $variant->toArray();
        }

        if ($this->productExists()) {
            $props['productId'] = $this->getMoloniProductId();

            // Check for unused variants that cannot be deleted
            foreach ($this->moloniProduct['variants'] as $existingVariant) {
                foreach ($props['variants'] as $newVariant) {
                    if (!isset($newVariant['productId'])) {
                        continue;
                    }

                    if ($existingVariant['productId'] === $newVariant['productId']) {
                        continue 2;
                    }
                }

                // If we cannot delete variant, set it as invisible
                if ($existingVariant['deletable'] === false) {
                    $props['variants'][] = [
                        'productId' => $existingVariant['productId'],
                        'visible' => Boolean::NO,
                    ];
                }
            }
        } else {
            $props['hasStock'] = $this->hasStock;
            $props['warehouseId'] = $this->warehouseId;
            $props['warehouses'] = [
                'warehouseId' => $this->warehouseId
            ];
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
        // Update all variants values
        foreach ($this->variants as $variant) {
            // Update product with the one just added
            $variant->setMoloniParent($this->moloniProduct);

            // If was an insert, we need to get the id
            if ($variant->getMoloniVariantId() === 0) {
                $variant->setMoloniVariant();
            }
        }

        if (!empty($this->coverImage) && $this->shouldSyncImage()) {
            new UpdateMoloniVariantsProductImage($this->coverImage, $this->moloniProduct, $this->variants);
        }

        new CreateMappingsAfterMoloniProductCreateOrUpdate(
            $this->prestashopProduct,
            $this->moloniProduct,
            $this->variants
        );
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
        if (!$this->moloniProduct['deletable'] && empty($this->moloniProduct['variants']) && $this->productExists()) {
            throw new MoloniProductException('Cannot update product in Moloni. Product types do not match');
        }
    }

    /**
     * Search product in Moloni
     *
     * @throws MoloniProductException
     */
    protected function fetchProductFromMoloni(): MoloniProductWithVariants
    {
        /** @var MoloniProductAssociations[]|null $associations */
        $associations = ProductAssociations::findByPrestashopProductId((int)$this->prestashopProduct->id);

        // If we have associations
        if (!empty($associations)) {
            $this->getById($associations[0]->getMlProductId());

            // If found, can return
            if (!empty($this->moloniProduct)) {
                return $this;
            }

            // Not found, delete associations
            ProductAssociations::deleteByPrestashopId((int)$this->prestashopProduct->id);
        }

        $this->getByReference();

        return $this;
    }

    //          PUBLICS          //

    /**
     * Create a product in Moloni
     *
     * @throws MoloniProductException
     */
    public function insert(): MoloniProductWithVariants
    {
        $props = $this->toArray();

        try {
            $mutation = MoloniApiClient::products()
                ->mutationProductCreate(['data' => $props]);

            $moloniProduct = $mutation['data']['productCreate']['data'] ?? [];

            if (!empty($moloniProduct)) {
                $this->moloniProduct = $moloniProduct;

                if ($this->shouldWriteLogs()) {
                    Logs::addInfoLog(
                        ['Product created in Moloni ({0})', ['{0}' => $this->reference]],
                        ['props' => $props]
                    );
                }

                $this->afterSave();
            } else {
                throw new MoloniProductException('Error creating product ({0})', ['{0}' => $this->reference], [
                    'mutation' => $mutation,
                    '$props' => $props
                ]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error creating product ({0})', ['{0}' => $this->reference], $e->getData()
            );
        }

        return $this;
    }

    /**
     * Update a product in Moloni
     *
     * @throws MoloniProductException
     */
    public function update(): MoloniProductWithVariants
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
                    Logs::addInfoLog(
                        ['Product updated in Moloni ({0})', ['{0}' => $this->reference]],
                        ['props' => $props]
                    );
                }

                $this->afterSave();
            } else {
                throw new MoloniProductException('Error updating product ({0})', ['{0}' => $this->reference], [
                    'mutation' => $mutation,
                    'props' => $props,
                ]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductException(
                'Error updating product ({0})',
                ['{0}' => $this->reference],
                $e->getData()
            );
        }

        return $this;
    }

    /**
     * Update product stock in Moloni
     *
     * @throws MoloniProductException
     */
    public function updateStock(int $variantId, ?float $newQty): MoloniProductWithVariants
    {
        if ($variantId === 0 || !$this->productExists() || !$this->productHasStock()) {
            return $this;
        }

        foreach ($this->variants as $variant) {
            if ($variant->getPrestashopCombinationId() === $variantId) {
                $variant->setStock($newQty);
                $variant->updateStock();
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
    public function setVisibility(): MoloniProductWithVariants
    {
        $this->visibility = ProductVisibility::VISIBLE;

        return $this;
    }

    /**
     * Set product reference
     *
     * @return $this
     */
    public function setReference(): MoloniProductWithVariants
    {
        $reference = $this->prestashopProduct->reference;

        if (empty($reference)) {
            $reference = (string)$this->prestashopProduct->id;
        }

        $this->reference = $reference;

        return $this;
    }

    /**
     * Set product name
     *
     * @return $this
     */
    public function setName(): MoloniProductWithVariants
    {
        $this->name = $this->prestashopProduct->name;

        return $this;
    }

    /**
     * Set product summary
     *
     * @return $this
     */
    public function setSummary(): MoloniProductWithVariants
    {
        $this->summary = strip_tags($this->prestashopProduct->description_short);

        return $this;
    }

    /**
     * Set product type
     *
     * @return $this
     */
    public function setPrice(): MoloniProductWithVariants
    {
        $this->price = $this->prestashopProduct->getPriceWithoutReduct(true, null, 5);

        return $this;
    }

    /**
     * Set product type
     *
     * @return MoloniProductWithVariants
     */
    public function setType(): MoloniProductWithVariants
    {
        $this->type = ProductType::PRODUCT;

        return $this;
    }

    /**
     * Set has stock
     *
     * @return MoloniProductWithVariants
     */
    public function setHasStock(): MoloniProductWithVariants
    {
        $hasStock = $this->moloniProduct['hasStock'] ?? (bool)Boolean::YES;

        $this->hasStock = $hasStock;

        return $this;
    }

    /**
     * Sets product tax
     *
     * @throws MoloniProductTaxException
     */
    public function setTax(): MoloniProductWithVariants
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
    public function setEcoTax(): MoloniProductWithVariants
    {
        $ecoTax = (float)$this->prestashopProduct->ecotax;

        if ($ecoTax > 0) {
            $this->price -= $ecoTax;
            // todo: what else is needed?
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
    public function setWarehouseId(): MoloniProductWithVariants
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
    public function setCategory(): MoloniProductWithVariants
    {
        if (!$this->shouldSyncCategories()) {
            return $this;
        }

        $categoriesNames = [];

        if (!empty($this->prestashopProduct->id_category_default)) {
            $languageId = (int)Configuration::get('PS_LANG_DEFAULT');

            $categoryId = $this->prestashopProduct->id_category_default;
            $failsafe = 0;

            do {
                $categoryObj = new Category($categoryId, $languageId);

                // For some reason sometimes this comes empty
                if (empty($categoryObj->name)) {
                    break;
                }

                array_unshift($categoriesNames, $categoryObj->name);

                // Skip root categories
                if (in_array((int)$categoryObj->id_parent, [1, 2])) {
                    break;
                }

                // Next category is this category parent
                $categoryId = (int)$categoryObj->id_parent;

                $failsafe++;
            } while ($failsafe < 100 && $categoryId > 0);
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
    public function setMeasurementUnitId(): MoloniProductWithVariants
    {
        $this->measurementUnitId = (int)(Settings::get('measurementUnit') ?? 0);

        return $this;
    }

    /**
     * Set product image
     *
     * @return $this
     */
    public function setCoverImage(): MoloniProductWithVariants
    {
        /** @var array|null $coverImage */
        $coverImage = Image::getCover($this->prestashopProduct->id);

        $this->coverImage = $coverImage ?? [];

        return $this;
    }

    /**
     * Set property group
     *
     * @return MoloniProductWithVariants
     *
     * @throws MoloniProductException
     */
    public function setPropertyGroup(): MoloniProductWithVariants
    {
        $this->propertyGroup = (new FindOrCreatePropertyGroup($this->prestashopProduct))->handle();

        return $this;
    }

    /**
     * Set product variants
     *
     * @return $this
     */
    public function setVariants(): MoloniProductWithVariants
    {
        $prestashopCombinationsQuery = $this->prestashopProduct->getAttributeCombinations(null, false);

        foreach ($prestashopCombinationsQuery as $combinationQuery) {
            $combination = new Combination(
                $combinationQuery['id_product_attribute'],
                (int)Configuration::get('PS_LANG_DEFAULT')
            );

            $builder = new ProductVariant(
                $combination,
                $this->name,
                $this->moloniProduct,
                $this->propertyGroup['variants'][(int)$combination->id] ?? []
            );

            $builder
                ->setParentHasStock($this->hasStock)
                ->setWarehouseId($this->warehouseId);

            $this->variants[] = $builder;
        }

        return $this;
    }

    /**
     * Set product identifications
     *
     * @return MoloniProductWithVariants
     */
    public function setIdentifications(): MoloniProductWithVariants
    {
        $identifications = [];

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

        if (isset($this->moloniProduct['identifications']) && !empty($this->moloniProduct['identifications'])) {
            foreach ($this->moloniProduct['identifications'] as $identification) {
                if (!in_array($identification['type'], ['EAN13', 'ISBN'], true)) {
                    $identifications[] = $identification;
                }
            }
        }

        $this->identifications = $identifications;

        return $this;
    }

    /**
     * Disable logs proxy
     *
     * @return void
     */
    public function disableLogs(): void
    {
        $this->traitDisableLogs();

        foreach ($this->variants as $variant) {
            $variant->disableLogs();
        }
    }

    //          REQUESTS          //

    /**
     * Search product by variant id
     *
     * @param int $productId
     *
     * @return MoloniProductWithVariants
     *
     * @throws MoloniProductException
     */
    protected function getById(int $productId): MoloniProductWithVariants
    {
        $variables = [
            'productId' => $productId
        ];

        try {
            $query = MoloniApiClient::products()->queryProduct($variables);

            $moloniProduct = $query['data']['product']['data'] ?? [];

            if (!empty($moloniProduct)) {
                $this->moloniProduct = $moloniProduct;
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error fetching product by ID: ({0})', [
                '{0}' => $this->reference
            ], $e->getData());
        }

        return $this;
    }

    /**
     * Finds by reference
     *
     * @throws MoloniProductException
     */
    protected function getByReference(): MoloniProductWithVariants
    {
        $variables = [
            'options' => [
                'filter' => [
                    [
                        'field' => 'reference',
                        'comparison' => 'eq',
                        'value' => $this->reference
                    ]
                ],
            ],
        ];

        try {
            $query = MoloniApiClient::products()
                ->queryProducts($variables);

            if (!empty($query)) {
                $this->moloniProduct = $query[0];
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductException(
                'Error fetching product by reference: ({0})',
                ['{0}' => $this->reference],
                $e->getData()
            );
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
    protected function verifyPrestaProduct(): MoloniProductWithVariants
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
        if (!$this->productExists()) {
            return true;
        }

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
