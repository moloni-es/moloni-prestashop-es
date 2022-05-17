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
use Configuration;
use Currency;
use Order;
use OrderPayment as PrestashopOrderPayment;
use Moloni\Api\MoloniApiClient;
use Moloni\Helpers\Settings;
use Moloni\Traits\CountryTrait;
use Moloni\Builders\Document\OrderCustomer;
use Moloni\Builders\Document\OrderDelivery;
use Moloni\Builders\Document\OrderPayment;
use Moloni\Builders\Document\OrderProduct;
use Moloni\Builders\Document\OrderShipping;
use Moloni\Builders\Interfaces\BuilderInterface;
use Moloni\Enums\Boolean;
use Moloni\Enums\FiscalZone;
use Moloni\Enums\DocumentStatus;
use Moloni\Enums\DocumentTypes;
use Moloni\Enums\Countries;
use Moloni\Exceptions\Document\MoloniDocumentCustomerException;
use Moloni\Exceptions\Document\MoloniDocumentDeliveryException;
use Moloni\Exceptions\Document\MoloniDocumentException;
use Moloni\Exceptions\Document\MoloniDocumentPaymentException;
use Moloni\Exceptions\Document\MoloniDocumentProductException;
use Moloni\Exceptions\Document\MoloniDocumentProductTaxException;
use Moloni\Exceptions\Document\MoloniDocumentShippingException;
use Moloni\Exceptions\Document\MoloniDocumentWarning;
use Moloni\Exceptions\MoloniApiException;

class DocumentFromOrder implements BuilderInterface
{
    use CountryTrait;

    /**
     * Moloni document id
     *
     * @var int
     */
    public $documentId = 0;

    /**
     * Document type
     *
     * @var string
     */
    protected $documentType;

    /**
     * Document set id
     *
     * @var int
     */
    protected $documentSetId;

    /**
     * Inserted document
     *
     * @var array
     */
    protected $moloniDocument;

    /**
     * Document dates
     *
     * @var array
     */
    protected $dates;

    /**
     * Costumer information
     *
     * @var OrderCustomer
     */
    protected $customer;

    /**
     * Document our reference
     *
     * @var string
     */
    public $ourReference;

    /**
     * Document your reference
     *
     * @var string
     */
    protected $yourReference;

    /**
     * Document fiscal zone
     *
     * @var array
     */
    protected $fiscalZone;

    /**
     * Document discounts
     *
     * @var array
     */
    protected $discounts;

    /**
     * Document products
     *
     * @var OrderProduct[]
     */
    protected $products;

    /**
     * Document products
     *
     * @var OrderShipping|null
     */
    protected $shipping;

    /**
     * Document delivery
     *
     * @var OrderDelivery|null
     */
    protected $delivery;

    /**
     * Document exchange rates
     *
     * @var array
     */
    protected $exchangeRate = [];

    /**
     * Document payment methods
     *
     * @var OrderPayment[]
     */
    protected $payments = [];

    /**
     * Document notes
     *
     * @var string
     */
    protected $notes;

    /**
     * Create props
     *
     * @var array
     */
    public $createProps = [];

    /**
     * Prestashop Order
     *
     * @var Order
     */
    protected $order;

    /**
     * Moloni company information
     *
     * @var array
     */
    protected $company;

    /**
     * Constructor
     *
     * @throws MoloniDocumentException
     */
    public function __construct(Order $order, array $company)
    {
        $this->order = $order;
        $this->company = $company;

        $this->init();
    }

    //          PRIVATES          //

    /**
     * Start initial values
     *
     * @throws MoloniDocumentException
     */
    protected function init(): DocumentFromOrder
    {
        $this
            ->setDocumentType()
            ->setDocumentSet()
            ->setOurReference()
            ->setYourReference()
            ->setFicalZone()
            ->setExchangeRate()
            ->setCustomer()
            ->setDates()
            ->setDiscounts()
            ->setProducts()
            ->setShipping()
            ->setDelivery()
            ->setPaymentMethod()
            ->setNotes();

        return $this;
    }

    /**
     * Create array of the order document
     *
     * @return $this
     */
    protected function toArray(): DocumentFromOrder
    {
        $props = [
            'data' => [
                'documentSetId' => $this->documentSetId,
                'date' => $this->dates['date'],
                'expirationDate' => $this->dates['expirationDate'],
                'ourReference' => $this->ourReference,
                'yourReference' => $this->yourReference,
                'customerId' => $this->customer->toArray()['customerId'],
                'notes' => $this->notes,
                'status' => DocumentStatus::DRAFT
            ]
        ];

        if (!empty($this->payments)) {
            foreach ($this->payments as $payment) {
                $props['payments'][] = $payment->toArray();
            }
        }

        if (!empty($this->products)) {
            $order = 0;

            foreach ($this->products as $product) {
                $props['products'][] = $product->toArray($order);

                $order++;
            }
        }

        if (!empty($this->shipping)) {
            $order = count($props['products']);

            $props['products'][] = $this->shipping->toArray($order);
        }

        if (!empty($this->delivery)) {
            $delivery = $this->delivery->toArray();

            $props = array_merge($props, $delivery);
        }

        if (!empty($this->exchangeRate)) {
            // todo: apply exchage rates
        }

        $this->createProps = ['data' => $props];

        return $this;
    }

    //          PUBLICS          //

    /**
     * Create document in Moloni
     *
     * @return $this
     *
     * @throws MoloniDocumentException
     * @throws MoloniDocumentWarning
     */
    public function createDocument(): DocumentFromOrder
    {
        $this->toArray();

        try {
            switch ($this->documentType) {
                case DocumentTypes::INVOICES:
                    $action = 'mutationInvoiceCreate';
                    $key = 'invoiceCreate';
                    break;
                case DocumentTypes::RECEIPTS:
                    $action = 'mutationReceiptCreate';
                    $key = 'receiptCreate';
                    break;
                case DocumentTypes::PRO_FORMA_INVOICES:
                    $action = 'mutationProFormaInvoiceCreate';
                    $key = 'proFormaInvoiceCreate';
                    break;
                case DocumentTypes::PURCHASE_ORDERS:
                    $action = 'mutationPurchaseOrderCreate';
                    $key = 'purchaseOrderCreate';
                    break;
                case DocumentTypes::SIMPLIFIED_INVOICES:
                    $action = 'mutationSimplifiedInvoiceCreate';
                    $key = 'simplifiedInvoiceCreate';
                    break;
                default:
                    throw new MoloniDocumentException('Document type not found');
            }

            $mutation = MoloniApiClient::documents()
                ->$action($this->createProps);
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentException($e->getMessage(), $e->getIdentifiers(), $e->getData());
        }

        $documentId = $mutation['data'][$key]['data']['documentId'] ?? 0;

        if ($documentId === 0) {
            throw new MoloniDocumentException('Error creating document', [], ['document_props' => $this->createProps, 'result' => $mutation]);
        }

        $this->documentId = $documentId;
        $this->moloniDocument = $mutation['data'][$key]['data'];

        if ((int) Settings::get('documentStatus') === DocumentStatus::CLOSED) {
            $difference = abs($this->moloniDocument['totalValue'] - $this->order->total_paid_tax_incl);

            if ($difference < 0.01) {
                $this->closeDocument();
            } else {
                throw new MoloniDocumentWarning('Could not close document, totals do not match', [], [
                    'documentProps' => $this->createProps,
                ]);
            }
        }

        return $this;
    }

    /**
     * Close document
     *
     * @return $this
     * @throws MoloniDocumentException
     */
    public function closeDocument(): DocumentFromOrder
    {
        $updateProps = [
            'data' => [
                'document_id' => $this->documentId,
                'status' => DocumentStatus::CLOSED,
            ]
        ];

        try {
            switch ($this->documentType) {
                case DocumentTypes::INVOICES:
                    $action = 'mutationInvoiceUpdate';
                    $key = 'invoiceUpdate';
                    break;
                case DocumentTypes::RECEIPTS:
                    $action = 'mutationReceiptUpdate';
                    $key = 'receiptUpdate';
                    break;
                case DocumentTypes::PRO_FORMA_INVOICES:
                    $action = 'mutationProFormaInvoiceUpdate';
                    $key = 'proFormaInvoiceUpdate';
                    break;
                case DocumentTypes::PURCHASE_ORDERS:
                    $action = 'mutationPurchaseOrderUpdate';
                    $key = 'purchaseOrderUpdate';
                    break;
                case DocumentTypes::SIMPLIFIED_INVOICES:
                    $action = 'mutationSimplifiedInvoiceUpdate';
                    $key = 'simplifiedInvoiceUpdate';
                    break;
                default:
                    throw new MoloniDocumentException('Document type not found');
            }

            $mutation = MoloniApiClient::documents()->$action($updateProps);

            $documentId = $mutation['data'][$key]['data']['documentId'] ?? 0;

            if ($documentId === 0) {
                throw new MoloniApiException('Error closing document', [], ['mutation' => $mutation, 'props' => $updateProps]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentWarning('Error closing document', [], $e->getData());
        }

        $this->documentId = $documentId;

        return $this;
    }

    //          SETS          //

    /**
     * Defines order fiscal zone
     *
     * @return DocumentFromOrder
     *
     * @throws MoloniDocumentException
     */
    protected function setFicalZone(): DocumentFromOrder
    {
        $fiscalZone = [];
        $id = 0;

        switch (Configuration::get('PS_TAX_ADDRESS_TYPE')) {
            case FiscalZone::BILLING:
                $id = $this->order->id_address_invoice;
                break;
            case FiscalZone::SHIPPING:
                $id = $this->order->id_address_delivery;
                break;
        }

        if ($id > 0) {
            try {
                ['countryId' => $countryId, 'code' => $code] = $this->getMoloniCountryById((new Address($id))->id_country);
            } catch (MoloniApiException $e) {
                throw new MoloniDocumentException('Error fetching document fiscal zone', [], $e->getData());
            }

            $fiscalZone = [
                'code' => $code,
                'countryId' => $countryId
            ];
        }

        if (empty($fiscalZone)) {
            $fiscalZone = [
                'code' => $this->company['fiscalZone']['fiscalZone'] ?? 'ES',
                'countryId' => $this->company['country']['countryId'] ?? Countries::SPAIN
            ];
        }

        $this->fiscalZone = $fiscalZone;

        return $this;
    }

    /**
     * Defines order exchage info
     *
     * @return DocumentFromOrder
     *
     * @throws MoloniDocumentException
     */
    protected function setExchangeRate(): DocumentFromOrder
    {
        $exchangeRate = [];
        $currency = new Currency($this->order->id_currency);

        if ($currency->iso_code !== $this->company['currency']['iso4217']) {
            $from = $currency->iso_code;
            $to = $this->company['currency']['iso4217'];
            $wantedPair = $from . ' ' . $to;

            $variables = [
                'options' => [
                    'search' => [
                        'field' => 'pair',
                        'value' => $wantedPair,
                    ],
                ],
            ];

            try {
                $query = MoloniApiClient::currencies()
                    ->queryCurrencyExchanges($variables);
            } catch (MoloniApiException $e) {
                throw new MoloniDocumentException('Error fetching exchange rate: ({0})', ['{0}' => $wantedPair], $e->getData());
            }

            foreach ($query as $currencyExchange) {
                if ($currencyExchange['from']['iso4217'] === $from && $currencyExchange['to']['iso4217'] === $to) {
                    $exchangeRate = $currencyExchange;
                    break;
                }
            }

            if (empty($exchangeRate)) {
                throw new MoloniDocumentException('Could not find exchange rate: ({0})', ['{0}' => $wantedPair]);
            }
        }

        $this->exchangeRate = $exchangeRate;

        return $this;
    }

    /**
     * Builds order customer
     *
     * @return DocumentFromOrder
     *
     * @throws MoloniDocumentCustomerException
     */
    protected function setCustomer(): DocumentFromOrder
    {
        if ($this->order->id_customer) {
            $customer = new OrderCustomer($this->order);

            $customer
                ->search();

            if ($customer->customerId === 0) {
                $customer
                    ->insert();
            }

            $this->customer = $customer;
        }

        return $this;
    }

    /**
     * Defines dates for document
     *
     * @return DocumentFromOrder
     */
    protected function setDates(): DocumentFromOrder
    {
        $this->dates = [
            'date' => $this->order->date_add,
            'expirationDate' => date('Y-m-d H:i:s'),
        ];

        return $this;
    }

    /**
     * Defines type
     *
     * @return DocumentFromOrder
     */
    protected function setDocumentType(): DocumentFromOrder
    {
        $this->documentSetId = Settings::get('documentType');

        return $this;
    }

    /**
     * Defines set
     *
     * @return DocumentFromOrder
     */
    protected function setDocumentSet(): DocumentFromOrder
    {
        $this->documentSetId = Settings::get('documentSet') ?? 0;

        return $this;
    }

    /**
     * Defines document reference
     *
     * @return DocumentFromOrder
     */
    protected function setOurReference(): DocumentFromOrder
    {
        $this->ourReference = $this->order->reference;

        return $this;
    }

    /**
     * Defines document reference
     *
     * @return DocumentFromOrder
     */
    protected function setYourReference(): DocumentFromOrder
    {
        $this->yourReference = '';

        return $this;
    }

    /**
     * Builds document prodcuts
     *
     * @return DocumentFromOrder
     *
     * @throws MoloniDocumentProductException
     * @throws MoloniDocumentProductTaxException
     */
    protected function setProducts(): DocumentFromOrder
    {
        $products = $this->order->getCartProducts();

        foreach ($products as $product) {
            $orderProduct = new OrderProduct($product, $this->fiscalZone);

            $orderProduct
                ->search();

            if ($orderProduct->productId === 0) {
                $orderProduct
                    ->insert();
            }

            $this->products[] = $orderProduct;
        }

        return $this;
    }

    /**
     * Defines document discounts
     *
     * @return DocumentFromOrder
     */
    protected function setDiscounts(): DocumentFromOrder
    {
        $this->discounts = [
            'financial_discount' => 0,
            'special_discount' => 0,
        ];

        return $this;
    }

    /**
     * Builds documents shipping
     *
     * @return DocumentFromOrder
     *
     * @throws MoloniDocumentShippingException
     */
    protected function setShipping(): DocumentFromOrder
    {
        if ($this->order->total_shipping > 0) {
            $orderShipping = new OrderShipping($this->order, $this->fiscalZone);

            $orderShipping
                ->search();

            if ($orderShipping->productId === 0) {
                $orderShipping
                    ->insert();
            }

            $this->shipping = $orderShipping;
        }

        return $this;
    }

    /**
     * Buildes document delivery info
     *
     * @return DocumentFromOrder
     *
     * @throws MoloniDocumentDeliveryException
     */
    protected function setDelivery(): DocumentFromOrder
    {
        if ($this->order->id_address_delivery > 0 &&
            (int)Settings::get('shippingInformation') === Boolean::YES) {
            $delivery = new OrderDelivery($this->order, $this->company);

            $delivery
                ->search();

            if ($delivery->deliveryMethodId === 0) {
                $delivery
                    ->insert();
            }

            $this->delivery = $delivery;
        }

        return $this;
    }

    /**
     * Builds order payments data
     *
     * @return DocumentFromOrder
     *
     * @throws MoloniDocumentPaymentException
     */
    protected function setPaymentMethod(): DocumentFromOrder
    {
        /** @var PrestashopOrderPayment[] $orderPayments */
        $orderPayments = $this->order->getOrderPayments();

        foreach ($orderPayments as $orderPayment) {
            if (empty($orderPayment->payment_method)) {
                continue;
            }

            $payment = new OrderPayment($orderPayment);

            $payment
                ->search();

            if ($payment->paymentMethodId === 0) {
                $payment->insert();
            }

            $this->payments[] = $payment;
        }

        return $this;
    }

    /**
     * Defines document notes
     *
     * @return DocumentFromOrder
     */
    protected function setNotes(): DocumentFromOrder
    {
        $this->notes = $this->order->note;

        return $this;
    }
}
