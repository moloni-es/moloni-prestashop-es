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

use Country;
use Moloni\Actions\Presta\ProcessAttributesGroup;
use Moloni\Enums\Boolean;
use Product;
use Configuration;
use PrestaShopException;
use TaxRulesGroup;
use Moloni\Api\MoloniApiClient;
use Moloni\Actions\Presta\UpdatePrestaProductImage;
use Moloni\Builders\PrestaProduct\ProductCombination;
use Moloni\Builders\Interfaces\BuilderInterface;
use Moloni\Builders\PrestaProduct\ProductCategory;
use Moloni\Actions\Presta\UpdatePrestaProductStock;
use Moloni\Enums\SyncFields;
use Moloni\Helpers\Logs;
use Moloni\Helpers\Settings;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Exceptions\Product\MoloniProductCategoryException;

class PrestaProductFromId implements BuilderInterface
{
    /**
     * Product id in Moloni
     *
     * @var int
     */
    protected $moloniProductId = 0;

    /**
     * Moloni product
     *
     * @var array|null
     */
    protected $moloniProduct;

    /**
     * Product id in Prestashop
     *
     * @var int
     */
    protected $prestashopProductId = 0;

    /**
     * Prestashop product
     *
     * @var Product|null
     */
    protected $prestashopProduct;


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
     * Product type
     *
     * @var string
     */
    protected $type = '';

    /**
     * Product summary
     *
     * @var string
     */
    protected $description;

    /**
     * Product category
     *
     * @var array
     */
    protected $categories = [];

    /**
     * Product isbn
     *
     * @var string
     */
    protected $isbn;

    /**
     * Product ean13
     *
     * @var string
     */
    protected $ean13;

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
     * Product tax;
     *
     * @var int|null
     */
    protected $taxRulesGroupId;

    /**
     * Product image path
     *
     * @var string
     */
    protected $imagePath = '';

    /**
     * Product variants
     *
     * @var ProductCombination[]
     */
    protected $combinations;


    /**
     * Fields that will be synced
     *
     * @var array
     */
    protected $syncFields;

    /**
     * Constructor
     *
     * @throws MoloniProductException
     */
    public function __construct(int $moloniProductId, ?array $syncFields = null)
    {
        $this->moloniProductId = $moloniProductId;

        $this->syncFields = $syncFields ?? Settings::get('productSyncFields') ?? SyncFields::getDefaultFields();

        $this->init();
    }

    //          PRIVATES          //

    /**
     * Create data
     *
     * @throws MoloniProductException
     */
    protected function init(): PrestaProductFromId
    {
        $this
            ->fetchProductFromMoloni()
            ->setReference()
            ->fetchProductFromPresta()
            ->setImagePath()
            ->setCombinations()
            ->setType()
            ->setName()
            ->setDescription()
            ->setIdentifications()
            ->setCategories()
            ->setHasStock()
            ->setWarehouseId()
            ->setStock()
            ->setPrice();

        return $this;
    }

    /**
     * After save requirements
     *
     * @return void
     *
     * @throws MoloniProductException
     */
    protected function afterSave(): void
    {
        $this->prestashopProductId = $this->prestashopProduct->id;

        if (!empty($this->categories)) {
            $this->prestashopProduct->deleteCategories();
            $this->prestashopProduct->addToCategories($this->categories);
        }

        if (!empty($this->imagePath) && $this->shouldSyncImage()) {
            new UpdatePrestaProductImage($this->prestashopProduct->id, $this->imagePath);
        }

        if ($this->productHasCombinations()) {
            // Check if Moloni groups exist
            try {
                new ProcessAttributesGroup($this->moloniProduct['propertyGroup']);
            } catch (PrestaShopException $e) {
                throw new MoloniProductException('Error when creating product attributes');
            }

            // Save combinations
            foreach ($this->combinations as $combination) {
                $combination->save();
            }
        }
    }

    /**
     * Set prestashop product values
     *
     * @return $this
     */
    protected function fillPrestaProduct(): PrestaProductFromId
    {
        if ($this->shouldSyncName()) {
            $this->prestashopProduct->name = $this->name;
        }

        if ($this->shouldSyncDescription()) {
            $this->prestashopProduct->description_short = $this->description;
        }

        if ($this->shouldSyncPrice()) {
            $this->prestashopProduct->price = $this->price;
        }

        $this->prestashopProduct->reference = $this->reference;

        if (!empty($this->categories)) {
            $this->prestashopProduct->id_category_default = $this->categories[0];
        }

        if (!empty($this->taxRulesGroupId)) {
            $this->prestashopProduct->id_tax_rules_group = $this->taxRulesGroupId;
        }

        if (!empty($this->type)) {
            $this->prestashopProduct->product_type = $this->type;
        }

        return $this;
    }

    /**
     * Finds Moloni product by ID
     *
     * @throws MoloniProductException
     */
    protected function fetchProductFromMoloni(): PrestaProductFromId
    {
        return $this->getById();
    }

    /**
     * Finds Prestashop product by reference
     */
    protected function fetchProductFromPresta(): PrestaProductFromId
    {
        $productId = (int)Product::getIdByReference($this->reference);

        if ($productId > 0) {
            $product = new Product($productId, true, Configuration::get('PS_LANG_DEFAULT'));

            $this->prestashopProductId = $productId;
            $this->prestashopProduct = $product;
        } else {
            $this->prestashopProduct = new Product();
        }

        return $this;
    }

    //          PUBLICS          //

    /**
     * Create product in prestashop
     *
     * @return void
     *
     * @throws MoloniProductException
     * @throws MoloniProductCategoryException
     */
    public function insert(): void
    {
        $this
            ->setTaxRulesGroupId()
            ->fillPrestaProduct();

        try {
            $this->prestashopProduct->save();

            Logs::addInfoLog(['Product created in Prestashop ({0})', ['{0}' => $this->reference]], ['moloniProduct' => $this->moloniProduct]);

            $this->afterSave();
        } catch (PrestaShopException $e) {
            throw new MoloniProductException('Error creating product ({0})', ['{0}' => $this->reference], [
                'moloniProduct' => $this->moloniProduct
            ]);
        }
    }

    /**
     * Update product in prestashop
     *
     * @return void
     *
     * @throws MoloniProductException
     * @throws MoloniProductCategoryException
     */
    public function update(): void
    {
        $this->fillPrestaProduct();

        try {
            $this->prestashopProduct->save();

            Logs::addInfoLog(['Product updated in Prestashop ({0})', ['{0}' => $this->reference]], ['moloniProduct' => $this->moloniProduct]);

            $this->afterSave();
        } catch (PrestaShopException $e) {
            throw new MoloniProductException('Error saving product ({0})', ['{0}' => $this->reference], [
                'moloniProduct' => $this->moloniProduct
            ]);
        }
    }

    /**
     * Update product stock in prestashop
     *
     * @return void
     */
    public function updateStock(): void
    {
        if (!$this->productHasStock() || !$this->productExists()) {
            return;
        }

        if ($this->productHasCombinations()) {
            foreach ($this->combinations as $combination) {
                $combination->updateStock();
            }
        } else {
            new UpdatePrestaProductStock($this->prestashopProductId, null, $this->reference, $this->stock);
        }
    }

    //          VERIFICATIONS          //

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

    //          GETS          //

    /**
     * Get Moloni product id
     *
     * @return int
     */
    public function getMoloniProductId(): int
    {
        return $this->moloniProductId;
    }

    /**
     * Get Prestashop product id
     *
     * @return int
     */
    public function getPrestashopProductId(): int
    {
        return $this->prestashopProductId;
    }

    //          SETS          //

    /**
     * Set product name
     *
     * @return $this
     */
    public function setName(): PrestaProductFromId
    {
        $this->name = $this->moloniProduct['name'] ?? '';

        return $this;
    }

    /**
     * Set product reference
     *
     * @return $this
     */
    public function setReference(): PrestaProductFromId
    {
        $this->reference = $this->moloniProduct['reference'] ?? '';

        return $this;
    }

    /**
     * Set product category
     *
     * @return $this
     *
     * @throws MoloniProductCategoryException
     */
    public function setCategories(): PrestaProductFromId
    {
        $categoryId = $this->moloniProduct['productCategory']['productCategoryId'] ?? 0;

        if ($categoryId > 0 && $this->shouldSyncCategories()) {
            $builder = new ProductCategory($categoryId);
            $builder->search();

            $this->categories = $builder->getCategoriesIds();
        }

        return $this;
    }

    /**
     * Set product type
     *
     * @return $this
     */
    public function setType(): PrestaProductFromId
    {
        if ($this->productHasCombinations()) {
            $this->type = 'combinations';
        }

        return $this;
    }

    /**
     * Set product summary
     *
     * @return $this
     */
    public function setDescription(): PrestaProductFromId
    {
        $this->description = $this->moloniProduct['summary'] ?? '';

        return $this;
    }

    /**
     * Set product identifications
     *
     * @return $this
     */
    public function setIdentifications(): PrestaProductFromId
    {
        $isbn = '';
        $ean13 = '';

        if (!empty($this->moloniProduct['identifications'])) {
            foreach ($this->moloniProduct['identifications'] as $identification) {
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
     * Set product price
     *
     * @return $this
     */
    public function setPrice(): PrestaProductFromId
    {
        $this->price = (float)($this->moloniProduct['price'] ?? 0);

        return $this;
    }

    /**
     * Set product warehouse
     *
     * @return $this
     */
    public function setWarehouseId(): PrestaProductFromId
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
                $mutation = MoloniApiClient::warehouses()->queryWarehouses($params);

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
     * Set product has stock
     *
     * @return $this
     */
    public function setHasStock(): PrestaProductFromId
    {
        $this->hasStock = $this->moloniProduct['hasStock'] ?? (bool)Boolean::YES;

        return $this;
    }

    /**
     * Set product stock
     *
     * @return $this
     */
    public function setStock(): PrestaProductFromId
    {
        if ($this->productHasStock()) {
            $stock = 0;

            if ($this->warehouseId === 1) {
                $stock = (float)($this->moloniProduct['stock'] ?? 0);
            } else {
                foreach ($this->moloniProduct['warehouses'] as $warehouse) {
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
    public function setImagePath(): PrestaProductFromId
    {
        $imagePath = '';

        if (!empty($this->moloniProduct) && !empty($this->moloniProduct['img'])) {
            $imagePath = $this->moloniProduct['img'];
        }

        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * Sets product variants
     *
     * @return $this
     */
    public function setCombinations(): PrestaProductFromId
    {
        $combinations = [];

        if (!empty($this->moloniProduct['variants'])) {
            foreach ($this->moloniProduct['variants'] as $variant) {
                $combinations[] = new ProductCombination($this->prestashopProduct, $variant);
            }
        }

        $this->combinations = $combinations;

        return $this;
    }

    /**
     * Sets product taxes
     *
     * @return $this
     */
    public function setTaxRulesGroupId(): PrestaProductFromId
    {
        if (!empty($this->moloniProduct['taxes']) && $this->productExists()) {
            $moloniTax = $this->moloniProduct['taxes'][0]['tax'] ?? [];

            $taxRulesGroupId = 0;

            $fiscalZone = $moloniTax['fiscalZone'] ?? 'ES';
            $countryId = Country::getByIso($fiscalZone);
            $value = (float)($moloniTax['value'] ?? 0);

            $taxes = array_reverse(TaxRulesGroup::getAssociatedTaxRatesByIdCountry($countryId), true);

            foreach ($taxes as $id => $tax) {
                if ($value === (float)$tax) {
                    $taxRulesGroupId = $id;

                    break;
                }
            }

            $this->taxRulesGroupId = $taxRulesGroupId;
        }

        return $this;
    }

    //          REQUESTS          //

    /**
     * Finds product by id
     *
     * @throws MoloniProductException
     */
    protected function getById(): PrestaProductFromId
    {
        $variables = [
            'productId' => $this->moloniProductId
        ];

        try {
            $query = MoloniApiClient::products()
                ->queryProduct($variables);

            $moloniProduct = $query['data']['product']['data'] ?? [];

            if (!empty($moloniProduct)) {
                $this->moloniProduct = $moloniProduct;
            } else {
                throw new MoloniProductException('Could not find product in Moloni ({0})', ['{0}' => $this->moloniProductId], [
                    'variables' => $variables,
                    'query' => $query,
                ]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error fetching product by id ({0})', ['{0}' => $this->moloniProductId], $e->getData());
        }

        return $this;
    }

    //          Auxiliary          //

    /**
     * Returns if product already exists
     *
     * @return bool
     */
    protected function productExists(): bool
    {
        return $this->prestashopProductId > 0;
    }

    /**
     * Returns if product has stock
     *
     * @return bool
     */
    protected function productHasStock(): bool
    {
        return $this->hasStock === true;
    }

    /**
     * Returns if product has variants
     *
     * @return bool
     */
    protected function productHasCombinations(): bool
    {
        return !empty($this->combinations);
    }
}