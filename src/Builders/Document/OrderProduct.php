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

declare(strict_types=1);

namespace Moloni\Builders\Document;

use Product;
use Configuration;
use Moloni\Api\MoloniApiClient;
use Moloni\Traits\DiscountsTrait;
use Moloni\Entity\MoloniProductAssociations;
use Moloni\Builders\Document\Helpers\GetOrderProductTaxes;
use Moloni\Builders\Interfaces\BuilderItemInterface;
use Moloni\Builders\MoloniProductSimple;
use Moloni\Builders\MoloniProductWithVariants;
use Moloni\Enums\Boolean;
use Moloni\Enums\ProductType;
use Moloni\Enums\ProductInformation;
use Moloni\Exceptions\Document\MoloniDocumentProductException;
use Moloni\Exceptions\Document\MoloniDocumentProductTaxException;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniException;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Tools\ProductAssociations;
use Moloni\Tools\Settings;
use Moloni\Tools\SyncLogs;

class OrderProduct implements BuilderItemInterface
{
    use DiscountsTrait;

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
    protected $moloniProduct = [];

    /**
     * Product warehouse id
     *
     * @var int
     */
    protected $warehouseId;

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
     * Product price
     *
     * @var float
     */
    protected $price;

    /**
     * Product price with taxes
     *
     * @var float
     */
    protected $priceWithTaxes;

    /**
     * Product quantity
     *
     * @var float
     */
    protected $quantity;

    /**
     * Product discount
     *
     * @var float
     */
    protected $discount = 0;

    /**
     * Product reference
     *
     * @var string
     */
    protected $reference;

    /**
     * Taxes builder
     *
     * @var OrderProductTax[]
     */
    protected $taxes;

    /**
     * Product Exemption reason
     *
     * @var string
     */
    protected $exemptionReason = '';

    /**
     * Fiscal Zone
     *
     * @var array
     */
    protected $fiscalZone;

    /**
     * Product exchange rates
     *
     * @var array
     */
    protected $exchangeRate = [];

    /**
     * Order product data
     *
     * @var array
     */
    protected $orderProduct;

    /**
     * Constructor
     *
     * @param array $orderProduct
     * @param array $fiscalZone
     *
     * @throws MoloniDocumentProductTaxException
     */
    public function __construct(array $orderProduct, array $fiscalZone)
    {
        $this->orderProduct = $orderProduct;
        $this->fiscalZone = $fiscalZone;

        $this->init();
    }

    //          PUBLICS          //

    /**
     * Exports product data to array format
     *
     * @param int|null $order
     *
     * @return array
     */
    public function toArray(?int $order = 1): array
    {
        $this->setName();

        $params = [
            'productId' => $this->productId,
            'name' => $this->name,
            'price' => $this->price,
            'ordering' => $order,
            'qty' => $this->quantity,
            'discount' => $this->discount,
            'taxes' => [],
            'exemptionReason' => '',
        ];

        if (!empty($this->taxes)) {
            foreach ($this->taxes as $tax) {
                $params['taxes'][] = $tax->toArray();
            }
        }

        if (!empty($this->exemptionReason)) {
            $params['exemptionReason'] = $this->exemptionReason;
        }

        if (!empty($this->exchangeRate)) {
            // Invert exchage rate, because order currency !== company currency
            $params['price'] *= (1 / $this->exchangeRate['exchange']);
        }

        return $params;
    }

    /**
     * Creates the product
     *
     * @throws MoloniDocumentProductException
     */
    public function insert(): void
    {
        SyncLogs::prestashopProductAddTimeout((int)$this->orderProduct['product_id']);

        try {
            $product = new Product($this->orderProduct['product_id'], true, Configuration::get('PS_LANG_DEFAULT'));

            if ($product->product_type === 'combinations') {
                $productBuilder = new MoloniProductWithVariants($product);
            } else {
                $productBuilder = new MoloniProductSimple($product);
            }

            $productBuilder->insert();
        } catch (MoloniProductException $e) {
            throw new MoloniDocumentProductException($e->getMessage(), $e->getIdentifiers(), $e->getData());
        }

        // Has variants, we need id of variant alone
        if ($productBuilder instanceof MoloniProductWithVariants) {
            /** @var MoloniProductAssociations $association */
            $association = ProductAssociations::findByPrestashopCombinationId((int)$this->orderProduct['product_attribute_id']);

            $this->productId = $association->getMlVariantId();
        } else {
            $this->productId = $productBuilder->getMoloniProductId();
        }

        $this->moloniProduct = $productBuilder->getMoloniProduct();
    }

    /**
     * Searches product in Moloni
     *
     * @throws MoloniDocumentProductException
     */
    public function search(): OrderProduct
    {
        if ((int)$this->orderProduct['product_attribute_id'] > 0) {
            /** @var MoloniProductAssociations|null $moloniVariantId */
            $moloniVariantId = ProductAssociations::findByPrestashopCombinationId((int)$this->orderProduct['product_attribute_id']);

            if ($moloniVariantId !== null) {
                $this->getById($moloniVariantId->getMlVariantId());
            }
        }

        if (empty($this->moloniProduct)) {
            $this->getByReference();
        }

        $this->afterSearch();

        return $this;
    }

    //          PRIVATES          //

    /**
     * Start initial values
     *
     * @return $this
     *
     * @throws MoloniDocumentProductTaxException
     */
    protected function init(): OrderProduct
    {
        $this
            ->setReference()
            ->setQuantity()
            ->setWarehouseId()
            ->setTaxes()
            ->setPrice()
            ->setType();

        return $this;
    }

    /**
     * After search verifications
     *
     * @throws MoloniDocumentProductException
     */
    protected function afterSearch(): OrderProduct
    {
        if (!empty($this->moloniProduct) && $this->moloniProduct['visible'] === Boolean::NO) {
            throw new MoloniDocumentProductException(
                'Product with reference ({0}) is invisible in Moloni. Please change the product visibility.',
                [
                    '{0}' => $this->reference
                ],
                [
                    'product' => $this->moloniProduct
                ]
            );
        }

        return $this;
    }

    //          GETS          //

    /**
     * Product id getter
     *
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    //          SETS          //

    /**
     * Define product reference
     *
     * @return OrderProduct
     */
    public function setReference(): OrderProduct
    {
        $reference = $this->orderProduct['reference'];

        if (empty($reference)) {
            $reference = (string)$this->orderProduct['product_id'];
        }

        $this->reference = $reference;

        return $this;
    }

    /**
     * Define name
     *
     * @return OrderProduct
     */
    public function setName(): OrderProduct
    {
        $name = '';

        switch (Settings::get('useProductNameAndSummaryFrom')) {
            case ProductInformation::PRESTASHOP:
                $name = $this->orderProduct['product_name'];
                break;
            case ProductInformation::MOLONI:
                $name = $this->moloniProduct['name'];
                break;
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Define price
     *
     * @return OrderProduct
     */
    public function setPrice(): OrderProduct
    {
        $this->price = (float)($this->orderProduct['product_price'] ?? 0);
        $this->priceWithTaxes = (float)($this->orderProduct['unit_price_tax_incl'] ?? 0);

        return $this;
    }

    /**
     * Define type
     *
     * @return OrderProduct
     */
    public function setType(): OrderProduct
    {
        $this->type = ProductType::PRODUCT;

        return $this;
    }

    /**
     * Define product quantity
     *
     * @return OrderProduct
     */
    public function setQuantity(): OrderProduct
    {
        $this->quantity = (int)($this->orderProduct['product_quantity'] ?? 1);

        return $this;
    }

    /**
     * Build order product taxes
     *
     * @return OrderProduct
     *
     * @throws MoloniDocumentProductTaxException
     */
    public function setTaxes(): OrderProduct
    {
        try {
            $taxes = (new GetOrderProductTaxes($this->orderProduct, $this->fiscalZone))->handle();
        } catch (MoloniException $e) {
            throw new MoloniDocumentProductTaxException($e->getMessage(), $e->getIdentifiers(), $e->getData());
        }

        if (empty($taxes)) {
            $exemption = Settings::get('exemptionReasonProduct');

            if (empty($exemption)) {
                throw new MoloniDocumentProductTaxException(
                    'Product with reference ({0}) has no taxes applied. Please add an exemption reason in plugin settings.',
                    ['{0}' => $this->reference],
                    ['order_product' => $this->orderProduct]
                );
            }

            $this->exemptionReason = $exemption;
        } else {
            $this->taxes = $taxes;
        }

        return $this;
    }

    /**
     * Defines warehouse id
     *
     * @return OrderProduct
     */
    public function setWarehouseId(): OrderProduct
    {
        $this->warehouseId = Settings::get('documentWarehouse');

        return $this;
    }

    /**
     * Calculate discounts
     *
     * @param float|null $cuponDiscountsPercentage
     *
     * @return OrderProduct
     */
    public function setDiscounts(?float $cuponDiscountsPercentage = 0): OrderProduct
    {
        $discount = 0;
        $price = $this->price;

        if ((float)$this->orderProduct['reduction_percent'] > 0) {
            $discount = (float)$this->orderProduct['reduction_percent'];

            // This types of discounts are already discounted from the product, so we need to add them to price
            $price = $this->calculateOriginalPrice($price, $discount);
        } elseif ((float)$this->orderProduct['reduction_amount_tax_excl'] > 0) {
            // This types of discounts are already discounted from the product, so we need to add them to price
            $price = (float)$this->orderProduct['product_price'] + (float)$this->orderProduct['reduction_amount_tax_excl'];

            $discount = $this->calculateDiscountPercentage($price, (float)$this->orderProduct['reduction_amount_tax_excl']);
        } elseif ($cuponDiscountsPercentage > 0) {
            $discount = $cuponDiscountsPercentage;
        }

        $this->discount = $discount;
        $this->price = $price;

        return $this;
    }

    /**
     * Set product exchange rate
     *
     * @param array|null $exchangeRate
     *
     * @return OrderProduct
     */
    public function setExchangeRate(?array $exchangeRate = []): OrderProduct
    {
        $this->exchangeRate = $exchangeRate;

        return $this;
    }

    //          REQUESTS          //

    /**
     * Search product by variant id
     *
     * @param int $productId
     *
     * @return OrderProduct
     *
     * @throws MoloniDocumentProductException
     */
    protected function getById(int $productId): OrderProduct
    {
        $variables = [
            'productId' => $productId
        ];

        try {
            $query = MoloniApiClient::products()->queryProduct($variables);

            $moloniProduct = $query['data']['product']['data'] ?? [];

            if (!empty($moloniProduct)) {
                $this->productId = (int)$moloniProduct['productId'];
                $this->moloniProduct = $moloniProduct;
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentProductException('Error fetching product by ID: ({0})', [
                '{0}' => $this->reference
            ], $e->getData());
        }

        return $this;
    }

    /**
     * Search product by reference
     *
     * @return OrderProduct
     *
     * @throws MoloniDocumentProductException
     */
    protected function getByReference(): OrderProduct
    {
        $variables = [
            'options' => [
                'search' => [
                    'field' => 'reference',
                    'value' => $this->reference,
                ],
                'filter' => [
                    [
                        'field' => 'visible',
                        'comparison' => 'in',
                        'value' => '[0, 1]'
                    ],
                ],
                'includeVariants' => true
            ],
        ];

        try {
            $query = MoloniApiClient::products()
                ->queryProducts($variables);

            if (!empty($query)) {
                $this->productId = (int)$query[0]['productId'];
                $this->moloniProduct = $query[0];
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentProductException('Error fetching product by reference: ({0})', [
                '{0}' => $this->reference
            ], $e->getData());
        }

        return $this;
    }
}
