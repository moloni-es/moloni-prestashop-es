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

use Moloni\Exceptions\Product\MoloniProductCategoryException;
use Order;
use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Interfaces\BuilderItemInterface;
use Moloni\Enums\ProductType;
use Moloni\Exceptions\Document\MoloniDocumentShippingException;
use Moloni\Exceptions\Document\MoloniDocumentShippingTaxException;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniException;
use Moloni\Helpers\Settings;

class OrderShipping implements BuilderItemInterface
{
    /**
     * Product id in Moloni
     *
     * @var int
     */
    public $productId = 0;

    /**
     * Category
     *
     * @var int
     */
    protected $categoryId;

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
     * @var OrderShippingTax|null
     */
    protected $taxes;

    /**
     * Product Exemption reason
     *
     * @var string
     */
    protected $exemptionReason = '';

    /**
     * Measurement unit
     *
     * @var int
     */
    protected $measurementUnit;

    /**
     * Fiscal Zone
     *
     * @var array
     */
    protected $ficalZone;

    /**
     * Order data
     *
     * @var Order
     */
    protected $order;

    /**
     * Order product data
     *
     * @var array
     */
    protected $orderShipping;

    /**
     * Constructor
     *
     * @param Order $order
     * @param array $fiscalZone
     *
     * @throws MoloniDocumentShippingTaxException
     */
    public function __construct(Order $order, array $fiscalZone)
    {
        $this->order = $order;
        $this->orderShipping = $order->getShipping()[0] ?? [];
        $this->ficalZone = $fiscalZone;

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
            $params['taxes'][] = $this->taxes->toArray();
        }

        return $params;
    }

    /**
     * Creates shipping product
     *
     * @throws MoloniDocumentShippingException
     */
    public function insert(): void
    {
        $this
            ->setType()
            ->setMeasurementUnit()
            ->setCategory();

        try {
            $params = [
                'productCategoryId' => $this->categoryId,
                'type' => $this->type,
                'name' => $this->name,
                'measurementUnitId' => $this->measurementUnit,
                'price' => $this->price,
                'hasStock' => false,
            ];

            if (!empty($this->exemptionReason)) {
                $params['exemptionReason'] = $this->exemptionReason;
            }

            if (!empty($this->taxes)) {
                $params['taxes'][] = $this->taxes->toArray();
            }

            $mutation = MoloniApiClient::products()
                ->mutationProductCreate(['data' => $params]);

            $productId = $mutation['data']['productCreate']['data']['productId'] ?? 0;

            if ($productId > 0) {
                $this->productId = $productId;
            } else {
                throw new MoloniDocumentShippingException('Error creating shipping product', [], [
                    'mutation' => $mutation
                ]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentShippingException('Error creating shipping product', [], $e->getData());
        }
    }

    /**
     * Searches product in Moloni
     *
     * @throws MoloniDocumentShippingException
     */
    public function search(): OrderShipping
    {
        return $this->getByReference();
    }

    //          PRIVATES          //

    /**
     * Start initial values
     *
     * @return $this
     *
     * @throws MoloniDocumentShippingTaxException
     */
    protected function init(): OrderShipping
    {
        $this
            ->setReference()
            ->setName()
            ->setQuantity()
            ->setPrice()
            ->setTaxes()
            ->setDiscounts();

        return $this;
    }

    //          SETS          //

    /**
     * Define product reference
     *
     * @return OrderShipping
     */
    protected function setReference(): OrderShipping
    {
        $this->reference = 'envio';

        return $this;
    }

    /**
     * Set category
     *
     * @return $this
     *
     * @throws MoloniDocumentShippingException
     */
    protected function setCategory(): OrderShipping
    {
        try {
            $builder = new OrderShippingCategory('Envío', 0);
            $builder->search();

            if ($builder->productCategoryId === 0) {
                $builder->insert();
            }

            $this->categoryId = $builder->productCategoryId;
        } catch (MoloniException $e) {
            throw new MoloniDocumentShippingException($e->getMessage(), $e->getIdentifiers(), $e->getData());
        }

        return $this;
    }

    /**
     * Define name
     *
     * @return OrderShipping
     */
    protected function setName(): OrderShipping
    {
        $this->name = $this->orderShipping['carrier_name'] ?? 'CARRIER';

        return $this;
    }

    /**
     * Define price
     *
     * @return OrderShipping
     */
    protected function setPrice(): OrderShipping
    {
        $this->price = $this->orderShipping['shipping_cost_tax_excl'] ?? 0;
        $this->priceWithTaxes = $this->orderShipping['shipping_cost_tax_incl'] ?? 0;

        return $this;
    }

    /**
     * Define type
     *
     * @return OrderShipping
     */
    protected function setType(): OrderShipping
    {
        $this->type = ProductType::SERVICE;

        return $this;
    }

    /**
     * Calculate discounts
     *
     * @return OrderShipping
     */
    protected function setDiscounts(): OrderShipping
    {
        $discount = 0;
        $cartRules = $this->order->getCartRules();

        foreach ($cartRules as $cartRule) {
            if ((int)$cartRule['free_shipping'] === 1) {
                $discount = 100;

                break;
            }
        }

        $this->discount = $discount;

        return $this;
    }

    /**
     * Define product quantity
     *
     * @return OrderShipping
     */
    protected function setQuantity(): OrderShipping
    {
        $this->quantity = 1;

        return $this;
    }

    /**
     * Build product taxes
     *
     * @throws MoloniDocumentShippingTaxException
     */
    protected function setTaxes(): OrderShipping
    {
        $taxRate = $this->order->carrier_tax_rate;

        if ($taxRate > 0) {
            $taxBuilder = new OrderShippingTax($taxRate, $this->ficalZone, 0);

            try {
                $taxBuilder
                    ->search();

                if ($taxBuilder->taxId === 0) {
                    $taxBuilder
                        ->insert();
                }
            } catch (MoloniException $e) {
                throw new MoloniDocumentShippingTaxException($e->getMessage(), $e->getIdentifiers(), $e->getData());
            }

            $this->taxes = $taxBuilder;
        }

        if (empty($this->taxes)) {
            $this->exemptionReason = Settings::get('exemptionReasonShipping');
        }

        return $this;
    }

    /**
     * Set product measurement unit
     *
     * @return $this
     */
    protected function setMeasurementUnit(): OrderShipping
    {
        $this->measurementUnit = Settings::get('measurementUnit') ?? 0;

        return $this;
    }

    //          REQUESTS          //

    /**
     * Search shipping by reference
     *
     * @return OrderShipping
     *
     * @throws MoloniDocumentShippingException
     */
    protected function getByReference(): OrderShipping
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
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentShippingException('Error fetching shipping by reference: ({0})', ['{0}' => $this->reference], $e->getData());
        }

        return $this;
    }
}
