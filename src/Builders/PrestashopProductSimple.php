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

use Configuration;
use Moloni\Builders\Interfaces\BuilderInterface;
use Moloni\Builders\PrestashopProduct\Helpers\FindTaxGroupFromMoloniTax;
use Moloni\Builders\PrestashopProduct\Helpers\GetPrestashopCategoriesFromMoloniCategoryId;
use Moloni\Builders\PrestashopProduct\Helpers\UpdatePrestaProductImage;
use Moloni\Builders\PrestashopProduct\Helpers\UpdatePrestaProductStock;
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
use PrestaShopException;
use Product;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestashopProductSimple implements BuilderInterface
{
    use LogsTrait;

    /**
     * Moloni product
     *
     * @var array|null
     */
    protected $moloniProduct;

    /**
     * Prestashop product
     *
     * @var Product|null
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
    protected $isbn = '';

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
    protected $hasStock;

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
     * Product type
     *
     * @var string
     */
    protected $type = '';

    /**
     * Product image path
     *
     * @var string
     */
    protected $imagePath = '';


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
    protected function init(): PrestashopProductSimple
    {
        $this
            ->setReference()
            ->fetchProductFromPresta()
            ->setVisibility()
            ->setImagePath()
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
    }

    /**
     * Set prestashop product values
     *
     * @return $this
     */
    protected function fillPrestaProduct(): PrestashopProductSimple
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
    protected function fetchProductFromPresta(): PrestashopProductSimple
    {
        $productId = (int)Product::getIdByReference($this->reference);

        if ($productId > 0) {
            $product = new Product($productId, true, Configuration::get('PS_LANG_DEFAULT'));
        } else {
            if ((int)Settings::get('productReferenceFallback') === Boolean::YES && is_numeric($this->reference)) {
                $product = new Product((int)$this->reference, true, Configuration::get('PS_LANG_DEFAULT'));
            } else {
                $product = new Product();
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
            ->setTaxRulesGroupId()
            ->fillPrestaProduct();

        try {
            $this->prestashopProduct->save();

            if ($this->shouldWriteLogs()) {
                Logs::addInfoLog(['Product created in Prestashop ({0})', ['{0}' => $this->reference]], ['moloniProduct' => $this->moloniProduct]);
            }

            $this->afterSave();
        } catch (PrestaShopException $e) {
            throw new MoloniProductException('Error creating product ({0})', ['{0}' => $this->reference], [
                'message' => $e->getMessage(),
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

            if ($this->shouldWriteLogs()) {
                Logs::addInfoLog(['Product updated in Prestashop ({0})', ['{0}' => $this->reference]], ['moloniProduct' => $this->moloniProduct]);
            }

            $this->afterSave();
        } catch (PrestaShopException $e) {
            throw new MoloniProductException('Error updating product ({0})', ['{0}' => $this->reference], [
                'message' => $e->getMessage(),
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

        new UpdatePrestaProductStock($this->getPrestashopProductId(), null, $this->reference, $this->stock, $this->shouldWriteLogs());
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

        return (int)$this->moloniProduct['productId'];
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
        return (int)$this->prestashopProduct->id;
    }

    //          SETS          //

    /**
     * Set product visibility
     *
     * @return PrestashopProductSimple
     */
    public function setVisibility(): PrestashopProductSimple
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
    public function setName(): PrestashopProductSimple
    {
        $this->name = $this->moloniProduct['name'] ?? '';

        return $this;
    }

    /**
     * Set product reference
     *
     * @return $this
     */
    public function setReference(): PrestashopProductSimple
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
    public function setCategories(): PrestashopProductSimple
    {
        $categoryId = $this->moloniProduct['productCategory']['productCategoryId'] ?? 0;

        if ($categoryId > 0 && $this->shouldSyncCategories()) {
            $this->categories = (new GetPrestashopCategoriesFromMoloniCategoryId($categoryId))->handle();
        }

        return $this;
    }

    /**
     * Set product summary
     *
     * @return $this
     */
    public function setDescription(): PrestashopProductSimple
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
    public function setIdentifications(): PrestashopProductSimple
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
    public function setPrice(): PrestashopProductSimple
    {
        $this->price = (float)($this->moloniProduct['price'] ?? 0);

        return $this;
    }

    /**
     * Set product warehouse
     *
     * @return $this
     */
    public function setWarehouseId(): PrestashopProductSimple
    {
        $warehouseId = Settings::get('syncStockToPrestashopWarehouse');

        if (empty($warehouseId)) {
            $warehouseId = Warehouse::getCompanyDefaultWarehouse();
        }

        $this->warehouseId = (int)$warehouseId;

        return $this;
    }

    /**
     * Set product has stock
     *
     * @return $this
     */
    public function setHasStock(): PrestashopProductSimple
    {
        $this->hasStock = $this->moloniProduct['hasStock'] ?? (bool)Boolean::YES;

        return $this;
    }

    /**
     * Set product stock
     *
     * @return $this
     */
    public function setStock(): PrestashopProductSimple
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
    public function setImagePath(): PrestashopProductSimple
    {
        $imagePath = '';

        if (!empty($this->moloniProduct) && !empty($this->moloniProduct['img'])) {
            $imagePath = $this->moloniProduct['img'];
        }

        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * Set product type
     *
     * @return $this
     */
    public function setType(): PrestashopProductSimple
    {
        $this->type = 'standard';

        return $this;
    }

    /**
     * Sets product taxes
     *
     * @return $this
     */
    public function setTaxRulesGroupId(): PrestashopProductSimple
    {
        if (!empty($this->moloniProduct['taxes']) && !$this->productExists()) {
            $moloniTax = $this->moloniProduct['taxes'][0]['tax'] ?? [];

            $this->taxRulesGroupId = (new FindTaxGroupFromMoloniTax($moloniTax))->handle();
        }

        return $this;
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
