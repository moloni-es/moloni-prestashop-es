<?php

/**
 * 2025 - Moloni.com
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

use Moloni\Builders\Interfaces\BuilderInterface;
use Moloni\Builders\PrestashopProduct\Helpers\Combinations\CreateMappingsAfterPrestaProductCreateOrUpdate;
use Moloni\Builders\PrestashopProduct\Helpers\Combinations\ProcessAttributesGroup;
use Moloni\Builders\PrestashopProduct\Helpers\FindTaxGroupFromMoloniTax;
use Moloni\Builders\PrestashopProduct\Helpers\GetPrestashopCategoriesFromMoloniCategoryId;
use Moloni\Builders\PrestashopProduct\Helpers\UpdatePrestaProductImage;
use Moloni\Builders\PrestashopProduct\ProductCombination;
use Moloni\Enums\Boolean;
use Moloni\Enums\ProductVisibility;
use Moloni\Enums\SyncFields;
use Moloni\Exceptions\Product\MoloniProductCategoryException;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Helpers\Stock;
use Moloni\Helpers\Warehouse;
use Moloni\Tools\Logs;
use Moloni\Tools\Settings;
use Moloni\Traits\LogsTrait;
use Product;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestashopProductWithCombinations implements BuilderInterface
{
    use LogsTrait {
        disableLogs as traitDisableLogs;
    }

    /**
     * Moloni product
     *
     * @var array|null
     */
    protected $moloniProduct;

    /**
     * Prestashop product
     *
     * @var \Product|null
     */
    protected $prestashopProduct;

    /**
     * Visibility
     *
     * @var string
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
    protected $ean13 = '';

    /**
     * Product UPC-A
     *
     * @var string
     */
    protected $upc = '';

    /**
     * Product price
     *
     * @var float
     */
    protected $price = 0.0;

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
    public function __construct(array $moloniProduct, ?array $syncFields = null)
    {
        $this->moloniProduct = $moloniProduct;

        $this->syncFields = $syncFields ?? Settings::get('productSyncFields') ?? SyncFields::getDefaultFields();

        $this->init();
    }

    //          PRIVATES          //

    /**
     * Create data
     *
     * @throws MoloniProductException
     */
    protected function init(): PrestashopProductWithCombinations
    {
        $this
            ->setReference()
            ->fetchProductFromPresta()
            ->setVisibility()
            ->setImagePath()
            ->setAttributes()
            ->setCombinations()
            ->setType()
            ->setName()
            ->setDescription()
            ->setIdentifications()
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
        if (!empty($this->categories)) {
            $this->prestashopProduct->deleteCategories();
            $this->prestashopProduct->addToCategories($this->categories);
        }

        if (!empty($this->imagePath) && $this->shouldSyncImage()) {
            new UpdatePrestaProductImage($this->prestashopProduct->id, $this->imagePath);
        }

        // Save combinations
        foreach ($this->combinations as $combination) {
            if ($combination->getCombinationId() > 0) {
                $combination->update();
            } else {
                $combination->insert();
                $combination->updateStock();
            }
        }

        new CreateMappingsAfterPrestaProductCreateOrUpdate($this->moloniProduct, $this->prestashopProduct, $this->combinations);
    }

    /**
     * Set prestashop product values
     *
     * @return $this
     */
    protected function fillPrestaProduct(): PrestashopProductWithCombinations
    {
        if ($this->shouldSyncVisibility()) {
            $this->prestashopProduct->visibility = $this->visibility;
        }

        if ($this->shouldSyncName()) {
            $this->prestashopProduct->name = $this->name;
        }

        if ($this->shouldSyncDescription()) {
            $this->prestashopProduct->description_short = $this->description;
        }

        if ($this->shouldSyncPrice()) {
            $this->prestashopProduct->price = $this->price;
        }

        if (empty($this->prestashopProduct->link_rewrite)) {
            $this->prestashopProduct->link_rewrite = $this->linkRewrite();
        }

        if ($this->shouldSyncIdentifiers()) {
            $this->prestashopProduct->ean13 = $this->ean13;
            $this->prestashopProduct->isbn = $this->isbn;
            $this->prestashopProduct->upc = $this->upc;
        }

        if (!$this->productExists()) {
            $this->prestashopProduct->reference = $this->reference;
        }

        $this->prestashopProduct->product_type = $this->type;

        if (!empty($this->categories)) {
            $this->prestashopProduct->id_category_default = $this->categories[0];
        }

        if (!empty($this->taxRulesGroupId)) {
            $this->prestashopProduct->id_tax_rules_group = $this->taxRulesGroupId;
        }

        return $this;
    }

    /**
     * Finds Prestashop product by reference
     */
    protected function fetchProductFromPresta(): PrestashopProductWithCombinations
    {
        $productId = (int) \Product::getIdByReference($this->reference);

        if ($productId > 0) {
            $product = new \Product($productId, true, \Configuration::get('PS_LANG_DEFAULT'));
        } else {
            if ((int) Settings::get('productReferenceFallback') === Boolean::YES && is_numeric($this->reference)) {
                $product = new \Product((int) $this->reference, true, \Configuration::get('PS_LANG_DEFAULT'));
            } else {
                $product = new \Product();
            }
        }

        $this->prestashopProduct = $product;

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
            ->setCategories()
            ->setTaxRulesGroupId()
            ->fillPrestaProduct();

        try {
            $this->prestashopProduct->save();

            if ($this->shouldWriteLogs()) {
                Logs::addInfoLog(['Product created in Prestashop ({0})', ['{0}' => $this->reference]], ['moloniProduct' => $this->moloniProduct]);
            }

            $this->afterSave();
        } catch (\PrestaShopException $e) {
            throw new MoloniProductException('Error creating product ({0})', ['{0}' => $this->reference], ['message' => $e->getMessage(), 'moloniProduct' => $this->moloniProduct]);
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
        $this
            ->setCategories()
            ->fillPrestaProduct();

        try {
            $this->prestashopProduct->save();

            if ($this->shouldWriteLogs()) {
                Logs::addInfoLog(['Product updated in Prestashop ({0})', ['{0}' => $this->reference]], ['moloniProduct' => $this->moloniProduct]);
            }

            $this->afterSave();
        } catch (\PrestaShopException $e) {
            throw new MoloniProductException('Error updating product ({0})', ['{0}' => $this->reference], ['message' => $e->getMessage(), 'moloniProduct' => $this->moloniProduct]);
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

        foreach ($this->combinations as $combination) {
            $combination->updateStock();
        }
    }

    //          GETS          //

    /**
     * Get Moloni product id
     *
     * @return int
     */
    public function getMoloniProductId(): int
    {
        if (empty($this->moloniProduct)) {
            return 0;
        }

        return (int) $this->moloniProduct['productId'];
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * Get Prestashop product id
     *
     * @return int
     */
    public function getPrestashopProductId(): int
    {
        return (int) $this->prestashopProduct->id;
    }

    //          SETS          //

    /**
     * Set product visibility
     *
     * @return PrestashopProductWithCombinations
     */
    public function setVisibility(): PrestashopProductWithCombinations
    {
        if ((int) $this->moloniProduct['visible'] === ProductVisibility::VISIBLE) {
            $this->visibility = 'both';
        } else {
            $this->visibility = 'none';
        }

        return $this;
    }

    /**
     * Set product name
     *
     * @return $this
     */
    public function setName(): PrestashopProductWithCombinations
    {
        $this->name = $this->moloniProduct['name'] ?? '';

        return $this;
    }

    /**
     * Set product reference
     *
     * @return $this
     */
    public function setReference(): PrestashopProductWithCombinations
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
    public function setCategories(): PrestashopProductWithCombinations
    {
        if (!empty($this->categories)) {
            return $this;
        }

        $categoryId = $this->moloniProduct['productCategory']['productCategoryId'] ?? 0;

        if ($categoryId > 0 && $this->shouldSyncCategories()) {
            $this->categories = (new GetPrestashopCategoriesFromMoloniCategoryId($categoryId))->handle();
        }

        return $this;
    }

    /**
     * Set product type
     *
     * @return $this
     */
    public function setType(): PrestashopProductWithCombinations
    {
        $this->type = 'combinations';

        return $this;
    }

    /**
     * Set product summary
     *
     * @return $this
     */
    public function setDescription(): PrestashopProductWithCombinations
    {
        $this->description = $this->moloniProduct['summary'] ?? '';
        $this->description = substr($this->description, 0, 800);

        return $this;
    }

    /**
     * Set product identifications
     *
     * @return $this
     */
    public function setIdentifications(): PrestashopProductWithCombinations
    {
        $isbn = '';
        $ean13 = '';
        $upc = '';

        if (!empty($this->moloniProduct['identifications'])) {
            foreach ($this->moloniProduct['identifications'] as $identification) {
                switch ($identification['type']) {
                    case 'ISBN':
                        $isbn = $identification['text'];
                        break;
                    case 'EAN13':
                        $ean13 = $identification['text'];
                        break;
                    case 'UPCA':
                        $upc = $identification['text'];
                        break;
                }
            }
        }

        $this->isbn = $isbn;
        $this->ean13 = $ean13;
        $this->upc = $upc;

        return $this;
    }

    /**
     * Set product price
     *
     * @return $this
     */
    public function setPrice(): PrestashopProductWithCombinations
    {
        $this->price = (float) ($this->moloniProduct['price'] ?? 0);

        return $this;
    }

    /**
     * Set product warehouse
     *
     * @return $this
     */
    public function setWarehouseId(): PrestashopProductWithCombinations
    {
        $warehouseId = Settings::get('syncStockToPrestashopWarehouse');

        if (empty($warehouseId)) {
            $warehouseId = Warehouse::getCompanyDefaultWarehouse();

            if (empty($warehouseId)) {
                $warehouseId = 1;
            }
        }

        $this->warehouseId = (int) $warehouseId;

        return $this;
    }

    /**
     * Set product has stock
     *
     * @return $this
     */
    public function setHasStock(): PrestashopProductWithCombinations
    {
        $this->hasStock = $this->moloniProduct['hasStock'] ?? (bool) Boolean::YES;

        return $this;
    }

    /**
     * Set product stock
     *
     * @return $this
     */
    public function setStock(): PrestashopProductWithCombinations
    {
        if ($this->productHasStock()) {
            $this->stock = Stock::getMoloniStock($this->moloniProduct, $this->warehouseId);
        }

        return $this;
    }

    /**
     * Set image path
     *
     * @return $this
     */
    public function setImagePath(): PrestashopProductWithCombinations
    {
        $imagePath = '';

        if (!empty($this->moloniProduct) && !empty($this->moloniProduct['img'])) {
            $imagePath = $this->moloniProduct['img'];
        }

        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * Set attributes
     *
     * @return $this
     *
     * @throws MoloniProductException
     */
    public function setAttributes(): PrestashopProductWithCombinations
    {
        // Check if Moloni groups exist
        try {
            new ProcessAttributesGroup($this->moloniProduct['propertyGroup']);
        } catch (\PrestaShopException $e) {
            throw new MoloniProductException('Error when creating product attributes', [], [$e->getMessage()]);
        }

        return $this;
    }

    /**
     * Sets product variants
     *
     * @return $this
     */
    public function setCombinations(): PrestashopProductWithCombinations
    {
        $combinations = [];

        foreach ($this->moloniProduct['variants'] as $variant) {
            if ($variant['visible'] === Boolean::YES) {
                $combinations[] = new ProductCombination($this->prestashopProduct, $this->moloniProduct, $variant);
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
    public function setTaxRulesGroupId(): PrestashopProductWithCombinations
    {
        if (!empty($this->moloniProduct['taxes']) && !$this->productExists()) {
            $moloniTax = $this->moloniProduct['taxes'][0]['tax'] ?? [];

            $this->taxRulesGroupId = (new FindTaxGroupFromMoloniTax($moloniTax))->handle();
        }

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

        foreach ($this->combinations as $combination) {
            $combination->disableLogs();
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
        return !$this->productExists() || in_array(SyncFields::CATEGORIES, $this->syncFields, true);
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

    /**
     * Should sync product image
     *
     * @return bool
     */
    protected function shouldSyncImage(): bool
    {
        return in_array(SyncFields::IMAGE, $this->syncFields, true);
    }

    /**
     * Should sync product visibility
     *
     * @return bool
     */
    protected function shouldSyncVisibility(): bool
    {
        return in_array(SyncFields::VISIBILITY, $this->syncFields, true);
    }

    //          Auxiliary          //

    /**
     * Returns if product already exists
     *
     * @return bool
     */
    protected function productExists(): bool
    {
        return $this->getPrestashopProductId() > 0;
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
     * Cleans link rewrite field
     *
     * @return string
     */
    private function linkRewrite(): string
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', $this->name);
    }
}
