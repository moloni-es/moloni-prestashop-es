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

use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Document\OrderCustomer;
use Moloni\Builders\Document\OrderDelivery;
use Moloni\Builders\Document\OrderPayment;
use Moloni\Builders\Document\OrderProduct;
use Moloni\Builders\Document\OrderShipping;
use Moloni\Builders\Interfaces\BuilderInterface;
use Moloni\Enums\DocumentStatus;
use Moloni\Enums\DocumentTypes;
use Moloni\Exceptions\Document\MoloniDocumentCustomerException;
use Moloni\Exceptions\Document\MoloniDocumentDeliveryException;
use Moloni\Exceptions\Document\MoloniDocumentException;
use Moloni\Exceptions\Document\MoloniDocumentPaymentException;
use Moloni\Exceptions\Document\MoloniDocumentProductException;
use Moloni\Exceptions\Document\MoloniDocumentProductTaxException;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Helpers\Settings;
use Order;

class DocumentFromOrder implements BuilderInterface
{
    /**
     * Moloni document id
     *
     * @var int
     */
    public $documentId = 0;

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
     * @var string
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
     * @var OrderShipping
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
    protected $payment = [];

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
    public function __construct(Order $order)
    {
        $this->order = $order;

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
        $this->createProps = [];

        return $this;
    }

    //          PUBLICS          //

    /**
     * Create document in Moloni
     *
     * @return $this
     *
     * @throws MoloniDocumentException
     */
    public function createDocument(): DocumentFromOrder
    {
        $this->toArray();

        try {
            switch (Settings::get('Type')) {
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

        if ((int)Settings::get('Status') === DocumentStatus::CLOSED) {
            $this->closeDocument();
        }

        return $this;
    }

    /**
     * Close document
     *
     * @return $this
     */
    public function closeDocument(): DocumentFromOrder
    {
        $updateProps = [
            'document_id' => $this->documentId,
            'status' => DocumentStatus::CLOSED,
        ];

        return $this;
    }

    //          SETS          //

    /**
     * Defines order fiscal zone
     *
     * @return DocumentFromOrder
     */
    protected function setFicalZone(): DocumentFromOrder
    {
        // todo: this
        $fiscalZone = 'ES';

        $this->fiscalZone = strtoupper($fiscalZone);

        return $this;
    }

    /**
     * Defines order exchage info
     *
     * @return DocumentFromOrder
     */
    protected function setExchangeRate(): DocumentFromOrder
    {
        // todo: this
        $this->exchangeRate = [];

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
        if ($this->order->invoice_date === '0000-00-00 00:00:00') {
            $date = date('Y-m-d H:i:s');
        } else {
            $date = $this->order->invoice_date;
        }

        $this->dates = [
            'date' => $date,
            'expiration_date' => date('Y-m-d H:i:s'),
        ];

        return $this;
    }

    /**
     * Defines document reference
     *
     * @return DocumentFromOrder
     */
    protected function setOurReference(): DocumentFromOrder
    {
        $this->ourReference = $this->order->reference ?? '';

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
     * @throws MoloniDocumentProductTaxException
     * @throws MoloniDocumentProductException
     */
    protected function setProducts(): DocumentFromOrder
    {
        $products = $this->order->getCartProducts();

        foreach ($products as $product) {
            $orderProduct = new OrderProduct($product);

            $orderProduct
                ->search()
                ->buildData();

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
     * @throws MoloniDocumentProductException
     * @throws MoloniDocumentProductTaxException
     */
    protected function setShipping(): DocumentFromOrder
    {
        if ($this->order->total_shipping > 0) {
            $orderShipping = new OrderShipping($this->order);

            $orderShipping
                ->search()
                ->buildData();

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
        if ($this->order->id_address_delivery) {
            $delivery = new OrderDelivery($this->order);

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
        $orderPayments = $this->order->getOrderPayments();

        foreach ($orderPayments as $orderPayment) {
            if (empty($orderPayment->payment_method)) {
                continue;
            }

            $payments = new OrderPayment($orderPayment);

            $payments
                ->search();

            if ($payments->paymentMethodId === 0) {
                $payments->insert();
            }

            $this->payment[] = $payments;
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
        $this->notes = $this->order->note ?? '';

        return $this;
    }

    //          REQUESTS         //

    protected function fetchInsertedDocument(): array
    {
        return [];
    }
}
