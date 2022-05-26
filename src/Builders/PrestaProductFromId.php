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
use Configuration;
use PrestaShopException;
use StockAvailable;
use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Interfaces\BuilderInterface;
use Moloni\Builders\PrestaProduct\ProductCategory;
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
     * Moloni roduct
     *
     * @var array|null
     */
    protected $moloniProduct;

    /**
     * Product id in Prestashop
     *
     * @var int
     */
    protected $prestaProductId = 0;

    /**
     * Prestashop product
     *
     * @var Product|null
     */
    protected $prestaProduct;


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
     * @var array|null
     */
    protected $tax;

    /**
     * Product variants
     *
     * @var array
     */
    protected $variants;

    /**
     * Constructor
     *
     * @throws MoloniProductException
     */
    public function __construct(int $moloniProductId)
    {
        $this->moloniProductId = $moloniProductId;

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
            ->setVariants()
            ->setName()
            ->setDescription()
            ->setIdentifications()
            ->setHasStock()
            ->setWarehouse()
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
        $this->prestaProductId = $this->prestaProduct->id;

        if (!empty($this->categories)) {
            $this->prestaProduct->deleteCategories();
            $this->prestaProduct->addToCategories($this->categories);
        }
    }

    /**
     * Set prestashop product values
     *
     * @return $this
     */
    protected function fillPrestaProduct(): PrestaProductFromId
    {
        $this->prestaProduct->name = $this->name;
        $this->prestaProduct->reference = $this->reference;
        $this->prestaProduct->price = $this->price;
        $this->prestaProduct->description_short = $this->description;

        if (!empty($this->categories)) {
            $this->prestaProduct->id_category_default = $this->categories[0];
        }

        if (!empty($this->tax)) {
            // todo: apply default tax rule;
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

            $this->prestaProductId = $productId;
            $this->prestaProduct = $product;
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
        $this->prestaProduct = new Product();

        $this
            ->setTax()
            ->setCategories()
            ->fillPrestaProduct();

        try {
            $this->prestaProduct->save();

            // todo: write log?

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
        $this
            ->setCategories()
            ->fillPrestaProduct();

        try {
            $this->prestaProduct->save();

            // todo: write log?

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
        if (!$this->hasStock() || $this->hasVariants() || !$this->exists()) {
            return;
        }

        $currentStock = StockAvailable::getQuantityAvailableByProduct($this->prestaProductId);

        if ($this->stock !== $currentStock) {
            // todo: write log?
            StockAvailable::setQuantity($this->prestaProductId, null, $this->stock);
        } else {
            // todo: write log?
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
        return $this->moloniProductId;
    }

    /**
     * Get Prestashop product id
     *
     * @return int
     */
    public function getPrestaProductId(): int
    {
        return $this->prestaProductId;
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

        if ($categoryId > 0) {
            $builder = new ProductCategory($categoryId);
            $builder->search();

            $this->categories = $builder->getCategoriesIds();
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
        $this->price = $this->moloniProduct['price'] ?? 0;

        return $this;
    }

    /**
     * Set product warehouse
     *
     * @return $this
     */
    public function setWarehouse(): PrestaProductFromId
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
        $this->hasStock = $this->moloniProduct['hasStock'] ?? true;

        return $this;
    }

    /**
     * Set product stock
     *
     * @return $this
     */
    public function setStock(): PrestaProductFromId
    {
        if ($this->hasStock()) {
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
     * Sets product variants
     *
     * @return $this
     */
    public function setVariants(): PrestaProductFromId
    {
        $this->variants = $this->moloniProduct['variants'] ?? [];

        return $this;
    }

    /**
     * Sets product taxes
     *
     * @return $this
     */
    public function setTax(): PrestaProductFromId
    {
        if (!empty($this->moloniProduct['taxes'])) {
            $this->tax = [];
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
    protected function exists(): bool
    {
        return $this->prestaProductId > 0;
    }

    /**
     * Returns if product has variants
     *
     * @return bool
     */
    protected function hasStock(): bool
    {
        return $this->hasStock === true;
    }

    /**
     * Returns if product has variants
     *
     * @return bool
     */
    protected function hasVariants(): bool
    {
        return !empty($this->variants);
    }
}
