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

namespace Moloni\Builders\Document;

use Tax;
use TaxCalculator;
use Moloni\Enums\ProductInformation;
use Moloni\Helpers\Settings;
use Moloni\Api\MoloniApiClient;
use Moloni\Enums\ProductType;
use Moloni\Builders\Interfaces\BuilderItemInterface;
use Moloni\Builders\MoloniProductFromId;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Exceptions\Document\MoloniDocumentProductTaxException;
use Moloni\Exceptions\Document\MoloniDocumentProductException;
use Moloni\Exceptions\MoloniException;
use Moloni\Exceptions\MoloniApiException;

class OrderProduct implements BuilderItemInterface
{
    /**
     * Product id in Moloni
     *
     * @var int
     */
    public $productId = 0;

    /**
     * Moloni roduct
     *
     * @var array
     */
    protected $moloniProduct;

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
    protected $discount;

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
     * Warehouse settings
     *
     * @var array|null Warehouse settings
     */
    protected $currencyExchange;

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
    public function toArray(?int $order = 0): array
    {
        $this->setName();

        $params = [
            'productId' => $this->productId,
            'price' => $this->price,
            'ordering' => $order,
            'qty' => $this->quantity,
            'discount' => $this->discount,
        ];

        if (!empty($this->exemptionReason)) {
            $params['exemptionReason'] = $this->exemptionReason;
        }

        if (!empty($this->taxes)) {
            $params['taxes'] = [];

            foreach ($this->taxes as $tax) {
                $params['taxes'][] = $tax->toArray();
            }
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
        try {
            $productBuilder = new MoloniProductFromId($this->orderProduct['product_id']);
            $productBuilder->insert();
        } catch (MoloniProductException $e) {
            throw new MoloniDocumentProductException($e->getMessage(), $e->getIdentifiers(), $e->getData());
        }

        $this->productId = $productBuilder->productId;
        $this->moloniProduct = $productBuilder->moloniProduct;
    }

    /**
     * Searches product in Moloni
     *
     * @throws MoloniDocumentProductException
     */
    public function search(): OrderProduct
    {
        return $this->getByReference();
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
            ->setType()
            ->setDiscounts();

        return $this;
    }

    //          SETS          //

    /**
     * Define product reference
     *
     * @return OrderProduct
     */
    protected function setReference(): OrderProduct
    {
        $reference = $this->orderProduct['reference'];

        if (empty($reference)) {
            $reference = $this->orderProduct['product_id'] ;
        }

        $this->reference = $reference;

        return $this;
    }

    /**
     * Define name
     *
     * @return OrderProduct
     */
    protected function setName(): OrderProduct
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
    protected function setPrice(): OrderProduct
    {
        $this->price = $this->orderProduct['unit_price_tax_excl'] ?? 0;
        $this->priceWithTaxes = $this->orderProduct['unit_price_tax_incl'] ?? 0;

        return $this;
    }

    /**
     * Define type
     *
     * @return OrderProduct
     */
    protected function setType(): OrderProduct
    {
        $this->type = ProductType::PRODUCT;

        return $this;
    }

    /**
     * Calculate discounts
     *
     * @return OrderProduct
     */
    protected function setDiscounts(): OrderProduct
    {
        $discount = 0;

        if ((float)$this->orderProduct['reduction_percent'] > 0) {
            $discount = (float)$this->orderProduct['reduction_percent'];
        } elseif ((float)$this->orderProduct['reduction_amount_tax_excl'] > 0) {
            $price = (float)$this->orderProduct['product_price'];

            if ($price > 0) {
                $discountedValue = (float)$this->orderProduct['reduction_amount_tax_excl'];
                $discount = (1 - ($price / ($price + $discountedValue))) * 100;
            } else {
                $discount = 100;
            }
        }

        switch (true) {
            case $discount > 100:
                $discount = 100;
                break;
            case $discount < 0:
                $discount = 0;
                break;
        }

        $this->discount = $discount;

        return $this;
    }

    /**
     * Define product quantity
     *
     * @return OrderProduct
     */
    protected function setQuantity(): OrderProduct
    {
        $this->quantity = $this->orderProduct['product_quantity'] ?? 1;

        return $this;
    }

    /**
     * Build order product taxes
     *
     * @return OrderProduct
     *
     * @throws MoloniDocumentProductTaxException
     */
    protected function setTaxes(): OrderProduct
    {
        $taxes = [];

        /** @var TaxCalculator $taxCalculator */
        $taxCalculator = $this->orderProduct['tax_calculator'];

        if (count($taxCalculator->taxes) > 0) {
            $taxOrder = 0;

            foreach ($taxCalculator->taxes as $tax) {
                /** @var Tax $tax */
                $taxBuilder = new OrderProductTax($tax->rate, $this->fiscalZone, $taxOrder);

                try {
                    $taxBuilder
                        ->search();

                    if ($taxBuilder->taxId === 0) {
                        $taxBuilder
                            ->insert();
                    }
                } catch (MoloniException $e) {
                    throw new MoloniDocumentProductTaxException($e->getMessage(), $e->getIdentifiers(), $e->getData());
                }

                $taxes[] = $taxBuilder;

                $taxOrder++;
            }
        }

        if (empty($taxes)) {
            $this->exemptionReason = Settings::get('exemptionReasonProduct');
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
    protected function setWarehouseId(): OrderProduct
    {
        $this->warehouseId = Settings::get('documentWarehouse');

        return $this;
    }

    //          REQUESTS          //

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
            ],
        ];

        try {
            $query = MoloniApiClient::products()
                ->queryProducts($variables);

            if (!empty($query)) {
                $this->productId = $query[0]['productId'];
                $this->moloniProduct = $query[0];
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentProductException('Error fetching product by reference: ({0})', [$this->reference], $e->getData());
        }

        return $this;
    }
}
