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
use Country;
use Product;
use Configuration;
use Moloni\Builders\MoloniProduct\ProductCategory;
use Moloni\Exceptions\Product\MoloniProductCategoryException;
use Moloni\Enums\Countries;
use Moloni\Exceptions\MoloniException;
use Moloni\Exceptions\Product\MoloniProductTaxException;
use Moloni\Helpers\Settings;
use Moloni\Api\MoloniApiClient;
use Moloni\Enums\Boolean;
use Moloni\Enums\ProductType;
use Moloni\Enums\ProductVisibility;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Builders\Interfaces\BuilderInterface;
use Moloni\Builders\MoloniProduct\ProductTax;

class MoloniProductFromId implements BuilderInterface
{
    /**
     * Product id in Moloni
     *
     * @var int
     */
    protected $productId = 0;

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
     * Product variants
     *
     * @var array
     */
    protected $variants;

    /**
     * Prestashop product object
     *
     * @var Product
     */
    protected $prestashopProduct;

    /**
     * Constructor
     *
     * @param int $prestashopProductId
     *
     * @throws MoloniProductException
     */
    public function __construct(int $prestashopProductId)
    {
        $this->prestashopProduct = new Product($prestashopProductId, true, Configuration::get('PS_LANG_DEFAULT'));

        $this->init();
    }

    //          PRIVATES          //

    /**
     * Create data
     *
     * @throws MoloniProductException
     */
    protected function init(): MoloniProductFromId
    {
        $this
            ->verifyProduct()
            ->setVisibility()
            ->setName()
            ->setSummary()
            ->setReference()
            ->setCategory()
            ->setHasStock()
            ->setStock()
            ->setPrice()
            ->setType()
            ->setTax()
            ->setEcoTax()
            ->setWarehouseId()
            ->setIdentifications()
            ->setVariations()
            ->setMeasurementUnitId();

        return $this;
    }

    /**
     * Verify requirements to create product
     *
     * @throws MoloniProductException
     */
    protected function verifyProduct(): MoloniProductFromId
    {
        if (empty($this->prestashopProduct->id)) {
            throw new MoloniProductException('Prestashop product not found');
        }

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

        if (!empty($this->category)) {
            $props['productCategoryId'] = $this->category->getProductCategoryId();
        }

        if (!empty($this->tax)) {
            $props['taxes'][] = $this->tax->toArray();
        }

        if (!empty($this->exemptionReason)) {
            $props['exemptionReason'] = $this->exemptionReason;
        }

        if ($this->productExists()) {
            $props['productId'] = $this->getProductId();
        } elseif ($this->warehouseId > 0 && $this->productHasStock()) {
            $props['warehouseId'] = $this->warehouseId;
            $props['warehouses'] = [[
                'warehouseId' => $this->warehouseId,
                'stock' => $this->stock,
            ]];
        }

        return $props;
    }

    //          PUBLICS          //

    /**
     * Create a product in Moloni
     *
     * @throws MoloniProductException
     */
    public function insert(): MoloniProductFromId
    {
        $props = $this->toArray();

        try {
            $mutation = MoloniApiClient::products()
                ->mutationProductCreate(['data' => $props]);

            $moloniProduct = $mutation['data']['productCreate']['data'] ?? [];
            $productId = $moloniProduct['productId'] ?? 0;

            if ($productId > 0) {
                $this->productId = $productId;
                $this->moloniProduct = $moloniProduct;

                // todo: write log?
            } else {
                throw new MoloniProductException('Error creating product', [], [
                    'mutation' => $mutation
                ]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error creating product', [], $e->getData());
        }

        return $this;
    }

    /**
     * Update a product in Moloni
     *
     * @throws MoloniProductException
     */
    public function update(): MoloniProductFromId
    {
        $props = $this->toArray();

        try {
            $mutation = MoloniApiClient::products()
                ->mutationProductUpdate(['data' => $props]);

            $moloniProduct = $mutation['data']['productUpdate']['data'] ?? [];
            $productId = $moloniProduct['productId'] ?? 0;

            if ($productId > 0) {
                $this->moloniProduct = $moloniProduct;

                // todo: write log?
            } else {
                throw new MoloniProductException('Error updating product', [], [
                    'mutation' => $mutation
                ]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error updating product', [], $e->getData());
        }

        return $this;
    }

    public function updateStock(): MoloniProductFromId
    {
        if ($this->productHasStock()) {
            $props = [];
        }

        // todo: this?
        // todo: write log?

        return $this;
    }

    /**
     * Search product in Moloni
     *
     * @throws MoloniProductException
     */
    public function search(): MoloniProductFromId
    {
        return $this->getByReference();
    }

    //          GETS          //

    /**
     * Moloni product id getter
     *
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
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
    public function setVisibility(): MoloniProductFromId
    {
        $this->visibility = ProductVisibility::VISIBLE;

        return $this;
    }

    /**
     * Set product reference
     *
     * @return $this
     */
    public function setReference(): MoloniProductFromId
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
    public function setName(): MoloniProductFromId
    {
        $this->name = $this->prestashopProduct->name;

        return $this;
    }

    /**
     * Set product summary
     *
     * @return $this
     */
    public function setSummary(): MoloniProductFromId
    {
        $this->summary = strip_tags($this->prestashopProduct->description_short);

        return $this;
    }

    /**
     * Set product type
     *
     * @return $this
     */
    public function setPrice(): MoloniProductFromId
    {
        $this->price = $this->prestashopProduct->getPriceWithoutReduct(true);

        return $this;
    }

    /**
     * Set product type
     *
     * @return MoloniProductFromId
     */
    public function setType(): MoloniProductFromId
    {
        $this->type = ProductType::PRODUCT;

        return $this;
    }

    /**
     * Set has stock
     *
     * @return MoloniProductFromId
     */
    public function setHasStock(): MoloniProductFromId
    {
        $this->hasStock = (bool)Boolean::YES;

        return $this;
    }

    /**
     * Set stock quantity
     *
     * @return $this
     */
    public function setStock(): MoloniProductFromId
    {
        $this->stock = $this->prestashopProduct->quantity;

        return $this;
    }

    /**
     * Sets product tax
     *
     * @throws MoloniProductTaxException
     */
    public function setTax(): MoloniProductFromId
    {
        try {
            $mutation = MoloniApiClient::companies()->queryCompany();

            $address = new Address();
            $address->id_country = Country::getByIso($mutation['fiscalZone']['fiscalZone'] ?? 'ES');

            $taxRate = $this->prestashopProduct->getTaxesRate($address);

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
    public function setEcoTax(): MoloniProductFromId
    {
        $ecoTax = (float)$this->prestashopProduct->ecotax;

        if ($ecoTax > 0) {
            $this->price -= $ecoTax;

            //todo: what else?
        }

        $this->ecoTax = $ecoTax;

        return $this;
    }

    /**
     * Set product warehouse
     *
     * @return $this
     */
    public function setWarehouseId(): MoloniProductFromId
    {
        $warehouseId = Settings::get('syncStockToMoloniWarehouse');

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
                $warehouseId = 0;
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
    public function setCategory(): MoloniProductFromId
    {
        $categoriesNames = [];
        $categoriesIds = $this->prestashopProduct->getCategories();

        if (!empty($categoriesIds)) {
            $languageId = Configuration::get('PS_LANG_DEFAULT');

            foreach ($categoriesIds as $categoriesId) {
                $categoriesNames[] = (new Category($categoriesId, $languageId))->name;
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
    public function setMeasurementUnitId(): MoloniProductFromId
    {
        $this->measurementUnitId = (int)(Settings::get('measurementUnit') ?? 0);

        return $this;
    }

    /**
     * Set product variants
     *
     * @return $this
     */
    public function setVariations(): MoloniProductFromId
    {
        if ($this->prestashopProduct->product_type === 'combinations') {
            // todo: set combination products
            $this->variants = [];
        }

        return $this;
    }

    /**
     * Set product identifications
     *
     * @return MoloniProductFromId
     */
    public function setIdentifications(): MoloniProductFromId
    {
        $identifications = $this->moloniProduct['identifications'] ?? [];
        $identificators = ['EAN13', 'ISBN'];

        foreach ($identifications as $key => $identification) {
            if (in_array($identification['type'], $identificators, true)) {
                unset($identifications[$key]);
            }
        }

        if (!empty($this->prestashopProduct->ean13)) {
            $identifications[] =  [
                'type' => 'EAN13',
                'text' => $this->prestashopProduct->ean13,
                'favorite' => false
            ];
        }

        if (!empty($this->prestashopProduct->isbn)) {
            $identifications[] =  [
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
    protected function getByReference(): MoloniProductFromId
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
                $this->productId = $moloniProduct['productId'];
                $this->hasStock = $moloniProduct['hasStock'];
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error fetching product by reference: ({0})', [$this->reference], $e->getData());
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
        return $this->productId > 0;
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
}

