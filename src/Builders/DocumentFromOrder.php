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
use Currency;
use DateTime;
use Doctrine\ORM\EntityManager;
use Moloni\Api\MoloniApi;
use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Document\OrderCustomer;
use Moloni\Builders\Document\OrderDelivery;
use Moloni\Builders\Document\OrderPayment;
use Moloni\Builders\Document\OrderProduct;
use Moloni\Builders\Document\OrderShipping;
use Moloni\Builders\Interfaces\BuilderInterface;
use Moloni\Entity\MoloniOrderDocuments;
use Moloni\Enums\Boolean;
use Moloni\Enums\CalculationMode;
use Moloni\Enums\Countries;
use Moloni\Enums\DocumentReference;
use Moloni\Enums\DocumentStatus;
use Moloni\Enums\DocumentTypes;
use Moloni\Enums\FiscalZone;
use Moloni\Exceptions\Document\MoloniDocumentCustomerException;
use Moloni\Exceptions\Document\MoloniDocumentDeliveryException;
use Moloni\Exceptions\Document\MoloniDocumentException;
use Moloni\Exceptions\Document\MoloniDocumentPaymentException;
use Moloni\Exceptions\Document\MoloniDocumentProductException;
use Moloni\Exceptions\Document\MoloniDocumentProductTaxException;
use Moloni\Exceptions\Document\MoloniDocumentShippingException;
use Moloni\Exceptions\Document\MoloniDocumentShippingTaxException;
use Moloni\Exceptions\Document\MoloniDocumentWarning;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Tools\Logs;
use Moloni\Tools\Settings;
use Moloni\Traits\CountryTrait;
use Moloni\Traits\DiscountsTrait;
use Order;
use OrderCore;
use OrderPayment as PrestashopOrderPayment;
use PrestaShop\PrestaShop\Adapter\ContextStateManager;
use Shop;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DocumentFromOrder implements BuilderInterface
{
    use CountryTrait;
    use DiscountsTrait;

    /**
     * Entity manager
     *
     * @var EntityManager
     */
    protected $entityManager;


    /**
     * Moloni document id
     *
     * @var int
     */
    protected $documentId = 0;

    /**
     * Moloni document total
     *
     * @var float
     */
    protected $documentTotal = 0;

    /**
     * Moloni document exchage total total
     *
     * @var float
     */
    protected $documentExchageTotal = 0;

    /**
     * Inserted document
     *
     * @var array
     */
    protected $moloniDocument;


    /**
     * Related documents
     *
     * @var array
     */
    protected $relatedWith = [];

    /**
     * Document type
     *
     * @var string
     */
    protected $documentType;

    /**
     * Document type name
     *
     * @var string
     */
    protected $documentTypeName;

    /**
     * Document status
     *
     * @var int|null
     */
    protected $documentStatus;

    /**
     * Document set id
     *
     * @var int
     */
    protected $documentSetId;

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
    protected $ourReference;

    /**
     * Document your reference
     *
     * @var string|null
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
     * Moloni round type
     *
     * @var null|int
     */
    protected $calculationMode;

    /**
     * Send document to customer via email
     *
     * @var int|null
     */
    protected $sendEmail;

    /**
     * If shipping information is added to document
     *
     * @var int
     */
    protected $useShipping = 0;


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
    public function __construct(Order $order, array $company, EntityManager $entityManager)
    {
        $this->order = $order;
        $this->company = $company;
        $this->entityManager = $entityManager;

        $this->init();
    }

    /**
     * Resets some values after cloning
     *
     * @return void
     */
    public function __clone()
    {
        $this->documentId = 0;
        $this->documentTotal = 0;
        $this->documentExchageTotal = 0;

        $this->relatedWith = [];

        $this->moloniDocument = [];
        $this->createProps = [];
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
            ->setDocumentStatus()
            ->setDocumentType()
            ->setDocumentSet()
            ->setSendEmail()
            ->setCalculationMode()
            ->setShippingInformation()
            ->setOurReference()
            ->setYourReference()
            ->setFiscalZone()
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
            'fiscalZone' => $this->fiscalZone['code'],
            'documentCalculationsMode' => $this->calculationMode,
            'documentSetId' => $this->documentSetId,
            'date' => $this->dates['date'],
            'expirationDate' => $this->dates['expirationDate'],
            'ourReference' => $this->ourReference,
            'yourReference' => $this->yourReference,
            'customerId' => $this->customer->toArray()['customerId'],
            'notes' => $this->notes,
            'status' => DocumentStatus::DRAFT,
        ];

        if (!empty($this->payments) && DocumentTypes::hasPayments($this->documentType)) {
            foreach ($this->payments as $payment) {
                $props['payments'][] = $payment->toArray();
            }
        }

        if (!empty($this->products) && $this->shouldAddProducts()) {
            $order = 1;

            foreach ($this->products as $product) {
                $props['products'][] = $product->toArray($order);

                $order++;
            }
        }

        if (!empty($this->shipping) && $this->shouldAddProducts()) {
            $order = count($props['products'] ?? 1);
            $order++;

            $props['products'][] = $this->shipping->toArray($order);
        }

        if (!empty($this->delivery) && $this->shouldAddShippingInformation()) {
            $delivery = $this->delivery->toArray();

            $props = array_merge($props, $delivery);
        }

        if (!empty($this->relatedWith)) {
            $relatedWithTotal = 0.0;
            $props['relatedWith'] = [];

            foreach ($this->relatedWith as $related) {
                $relatedWithTotal += $related['value'];

                $props['relatedWith'][] = [
                    'relatedDocumentId' => $related['documentId'],
                    'value' => $related['value'],
                ];

                /** Associate products from both documents */
                if (!empty($related['products']) && !empty($props['products'])) {
                    /**
                     * If multiple documents are associated, the need a global product counter
                     * Starts in -1 because the first thing we do is to increment its value
                     */
                    $currentProductIndex = -1;

                    /**
                     * Associate products from both documents
                     * We assume that the order of the documents is the same (beware if tring to do custom stuff)
                     */
                    foreach ($related['products'] as $associatedProduct) {
                        $currentProductIndex++;

                        /** To avoid errors, check lenght */
                        if (!isset($props['products'][$currentProductIndex])) {
                            continue;
                        }

                        /** Ids have to match */
                        if ((int)$props['products'][$currentProductIndex]['productId'] !== (int)$associatedProduct['productId']) {
                            continue;
                        }

                        $props['products'][$currentProductIndex]['relatedDocumentId'] = (int)$related['documentId'];
                        $props['products'][$currentProductIndex]['relatedDocumentProductId'] = (int)$associatedProduct['documentProductId'];
                    }
                }
            }

            /** Just Receipts things */
            if ($this->documentType === DocumentTypes::RECEIPTS) {
                unset($props['expirationDate'], $props['ourReference'], $props['yourReference'], $props['expirationDate']);

                $props['totalValue'] = $relatedWithTotal;
            }
        }

        if (!empty($this->exchangeRate)) {
            $props['currencyExchangeId'] = $this->exchangeRate['currencyExchangeId'];
            $props['currencyExchangeExchange'] = $this->exchangeRate['exchange'];
        }

        $this->createProps = ['data' => $props];

        return $this;
    }

    //          VERIFICATIONS          //

    /**
     * Checks if document should be closed
     *
     * @return bool
     */
    protected function shouldCloseDocument(): bool
    {
        return $this->documentStatus === DocumentStatus::CLOSED;
    }

    /**
     * Checks if document should be sent via email
     *
     * @return bool
     */
    protected function shouldSendEmail(): bool
    {
        return $this->sendEmail === Boolean::YES;
    }

    /**
     * Checks if document should have shipping information
     *
     * @return bool
     */
    protected function shouldAddShippingInformation(): bool
    {
        if (DocumentTypes::requiresDelivery($this->documentType)) {
            return true;
        }

        return $this->useShipping === Boolean::YES && DocumentTypes::hasDelivery($this->documentType);
    }

    /**
     * Checks if document type can have products
     *
     * @return bool
     */
    protected function shouldAddProducts(): bool
    {
        return DocumentTypes::hasProducts($this->documentType);
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
                    $mutation = MoloniApiClient::invoice()->mutationInvoiceCreate($this->createProps);
                    $moloniDocument = $mutation['data']['invoiceCreate']['data'] ?? 0;

                    break;
                case DocumentTypes::RECEIPTS:
                    $mutation = MoloniApiClient::receipt()->mutationReceiptCreate($this->createProps);
                    $moloniDocument = $mutation['data']['receiptCreate']['data'] ?? 0;

                    break;
                case DocumentTypes::PRO_FORMA_INVOICES:
                    $mutation = MoloniApiClient::proFormaInvoice()->mutationProFormaInvoiceCreate($this->createProps);
                    $moloniDocument = $mutation['data']['proFormaInvoiceCreate']['data'] ?? 0;

                    break;
                case DocumentTypes::PURCHASE_ORDERS:
                    $mutation = MoloniApiClient::purchaseOrder()->mutationPurchaseOrderCreate($this->createProps);
                    $moloniDocument = $mutation['data']['purchaseOrderCreate']['data'] ?? 0;

                    break;
                case DocumentTypes::SIMPLIFIED_INVOICES:
                    $mutation = MoloniApiClient::simplifiedInvoice()->mutationSimplifiedInvoiceCreate($this->createProps);
                    $moloniDocument = $mutation['data']['simplifiedInvoiceCreate']['data'] ?? 0;

                    break;
                case DocumentTypes::ESTIMATE:
                    $mutation = MoloniApiClient::estimate()->mutationEstimateCreate($this->createProps);
                    $moloniDocument = $mutation['data']['estimateCreate']['data'] ?? 0;

                    break;
                case DocumentTypes::BILLS_OF_LADING:
                    $mutation = MoloniApiClient::billsOfLading()->mutationBillsOfLadingCreate($this->createProps);
                    $moloniDocument = $mutation['data']['billsOfLadingCreate']['data'] ?? 0;

                    break;
                default:
                    throw new MoloniDocumentException('Document type not found');
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentException('Error creating {0} document', [
                '{0}' => $this->documentTypeName,
            ], $e->getData());
        }

        $documentId = $moloniDocument['documentId'] ?? 0;

        if ($documentId === 0) {
            throw new MoloniDocumentException('Error creating {0} document', [
                '{0}' => $this->documentTypeName,
            ], [
                'document_props' => $this->createProps, 'result' => $mutation
            ]);
        }

        $this->documentId = $documentId;
        $this->moloniDocument = $moloniDocument;

        $this->documentTotal = $moloniDocument['totalValue'];
        $this->documentExchageTotal = $moloniDocument['currencyExchangeTotalValue'] > 0 ? $moloniDocument['currencyExchangeTotalValue'] : $this->documentTotal;

        $this->saveRecord();

        if ($this->shouldCloseDocument()) {
            $difference = abs($this->documentExchageTotal - $this->order->total_paid_tax_incl);

            if ($difference < 0.01) {
                $this->closeDocument();
            } else {
                throw new MoloniDocumentWarning('Could not close {0}, totals do not match', [
                    '{0}' => $this->documentTypeName,
                ], [
                    'documentProps' => $this->createProps,
                    'mutation' => $moloniDocument
                ]);
            }
        }

        return $this;
    }

    /**
     * Close document
     *
     * @return $this
     *
     * @throws MoloniDocumentException
     */
    public function closeDocument(): DocumentFromOrder
    {
        $updateProps = [
            'data' => [
                'documentId' => $this->documentId,
                'status' => DocumentStatus::CLOSED,
            ]
        ];

        try {
            switch ($this->documentType) {
                case DocumentTypes::INVOICES:
                    $mutation = MoloniApiClient::invoice()->mutationInvoiceUpdate($updateProps);
                    $moloniDocument = $mutation['data']['invoiceUpdate']['data'] ?? 0;

                    break;
                case DocumentTypes::RECEIPTS:
                    $mutation = MoloniApiClient::receipt()->mutationReceiptUpdate($updateProps);
                    $moloniDocument = $mutation['data']['receiptUpdate']['data'] ?? 0;

                    break;
                case DocumentTypes::PRO_FORMA_INVOICES:
                    $mutation = MoloniApiClient::proFormaInvoice()->mutationProFormaInvoiceUpdate($updateProps);
                    $moloniDocument = $mutation['data']['proFormaInvoiceUpdate']['data'] ?? 0;

                    break;
                case DocumentTypes::PURCHASE_ORDERS:
                    $mutation = MoloniApiClient::purchaseOrder()->mutationPurchaseOrderUpdate($updateProps);
                    $moloniDocument = $mutation['data']['purchaseOrderUpdate']['data'] ?? 0;

                    break;
                case DocumentTypes::SIMPLIFIED_INVOICES:
                    $mutation = MoloniApiClient::simplifiedInvoice()->mutationSimplifiedInvoiceUpdate($updateProps);
                    $moloniDocument = $mutation['data']['simplifiedInvoiceUpdate']['data'] ?? 0;

                    break;
                case DocumentTypes::ESTIMATE:
                    $mutation = MoloniApiClient::estimate()->mutationEstimateUpdate($updateProps);
                    $moloniDocument = $mutation['data']['estimateUpdate']['data'] ?? 0;

                    break;
                case DocumentTypes::BILLS_OF_LADING:
                    $mutation = MoloniApiClient::billsOfLading()->mutationBillsOfLadingUpdate($updateProps);
                    $moloniDocument = $mutation['data']['billsOfLadingUpdate']['data'] ?? 0;

                    break;
                default:
                    throw new MoloniDocumentException('Document type not found');
            }

            $documentId = $moloniDocument['documentId'] ?? 0;

            if ($documentId === 0) {
                throw new MoloniApiException('Error closing {0} document', [
                    '{0}' => $this->documentTypeName,
                ], [
                    'mutation' => $mutation, 'props' => $updateProps
                ]);
            }

            $this->createPdf();

            if (!empty($this->shouldSendEmail())) {
                $this->sendEmail();
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentWarning('Error closing {0} document', [
                '{0}' => $this->documentTypeName,
            ], $e->getData());
        }

        return $this;
    }

    /**
     * Associate a document wiht the current one
     *
     * @param int $documentId Document id to associate
     * @param float $value Total value to associate
     * @param array $products Document products
     *
     * @return $this
     */
    public function addRelatedDocument(int $documentId, float $value, array $products = []): DocumentFromOrder
    {
        $this->relatedWith[] = [
            'documentId' => $documentId,
            'value' => $value,
            'products' => $products
        ];

        return $this;
    }

    /**
     * Add document creation log
     *
     * @return $this
     */
    public function addLog(): DocumentFromOrder
    {
        if ($this->documentId > 0) {
            $msg = [
                '{0} document created with success ({1})',
                [
                    '{0}' => $this->documentTypeName,
                    '{1}' => $this->ourReference,
                ]
            ];

            Logs::addInfoLog($msg, ['props' => $this->createProps]);
        }

        return $this;
    }

    //          GETS          //

    /**
     * Get created document id
     *
     * @return int
     */
    public function getDocumentId(): int
    {
        return $this->documentId;
    }

    /**
     * Get created document total
     *
     * @return float
     */
    public function getDocumentTotal(): float
    {
        return $this->documentTotal;
    }

    /**
     * Get created document products
     *
     * @return array
     */
    public function getDocumentProducts(): array
    {
        return $this->moloniDocument['products'] ?? [];
    }

    //          SETS          //

    /**
     * Defines type
     *
     * @param string|null $documentType
     *
     * @return DocumentFromOrder
     *
     * @throws MoloniDocumentException
     */
    public function setDocumentType(?string $documentType = null): DocumentFromOrder
    {
        $this->documentType = $documentType ?? Settings::get('documentType');

        if (empty($this->documentType)) {
            throw new MoloniDocumentException('No document type selected. Please choose one in plugin settings.');
        }

        $this->documentTypeName = DocumentTypes::getDocumentTypeName($this->documentType);

        return $this;
    }

    /**
     * Defines if document should be sent via email
     *
     * @return $this
     */
    public function setSendEmail(?int $sendByEmail = null): DocumentFromOrder
    {
        $this->sendEmail = (int)($sendByEmail ?? Settings::get('sendByEmail') ?? Boolean::NO);

        return $this;
    }

    /**
     * Defines if document should be closed
     *
     * @param int|null $documentStatus
     *
     * @return $this
     */
    public function setDocumentStatus(?int $documentStatus = null): DocumentFromOrder
    {
        $this->documentStatus = (int)($documentStatus ?? Settings::get('documentStatus') ?? DocumentStatus::DRAFT);

        return $this;
    }

    /**
     * Defines if document should be closed
     *
     * @param int|null $useShipping
     *
     * @return $this
     */
    public function setShippingInformation(?int $useShipping = null): DocumentFromOrder
    {
        $this->useShipping = (int)($useShipping ?? Settings::get('shippingInformation') ?? Boolean::NO);

        return $this;
    }

    /**
     * Defines order fiscal zone
     *
     * @return DocumentFromOrder
     *
     * @throws MoloniDocumentException
     */
    public function setFiscalZone(): DocumentFromOrder
    {
        $fiscalZone = [];
        $fiscalZoneSetting = Settings::get('fiscalZoneBasedOn');
        $addressId = 0;

        switch ($fiscalZoneSetting) {
            case FiscalZone::BILLING:
                $addressId = $this->order->id_address_invoice;
                break;
            case FiscalZone::SHIPPING:
                $addressId = $this->order->id_address_delivery;
                break;
            case FiscalZone::COMPANY:
                $fiscalZone = [
                    'code' => $this->company['fiscalZone']['fiscalZone'] ?? 'ES',
                    'countryId' => $this->company['country']['countryId'] ?? Countries::SPAIN
                ];
                break;
        }

        if ($addressId > 0) {
            try {
                $psAddress = new Address($addressId);

                ['countryId' => $countryId, 'code' => $code] = $this->getMoloniCountryById($psAddress->id_country, $psAddress->id_state);
            } catch (MoloniApiException $e) {
                throw new MoloniDocumentException('Error fetching document fiscal zone', [], $e->getData());
            }

            $fiscalZone = [
                'code' => $code,
                'countryId' => $countryId
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
    public function setExchangeRate(): DocumentFromOrder
    {
        $exchangeRate = [];
        $currency = new Currency($this->order->id_currency);

        if ($currency->iso_code !== $this->company['currency']['iso4217']) {
            $from = $this->company['currency']['iso4217'];
            $to = $currency->iso_code;

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
    public function setCustomer(): DocumentFromOrder
    {
        if ($this->order->id_customer) {
            $customer = new OrderCustomer($this->order);
            $customer->search();

            if ($customer->getCustomerId() === 0) {
                $customer->insert();
            } elseif ((int)Settings::get('clientUpdate') === Boolean::YES) {
                $customer->update();
            }

            $this->customer = $customer;
        }

        return $this;
    }

    /**
     * Defines dates for document
     *
     * @return DocumentFromOrder
     *
     * @throws MoloniDocumentException
     */
    public function setDates(): DocumentFromOrder
    {
        try {
            $date = (new DateTime())->format('Y-m-d\TH:i:sP');
        } catch (\Exception $e) {
            throw new MoloniDocumentException('Error occurred setting document dates');
        }

        $this->dates = [
            'date' => $date,
            'expirationDate' => $date,
        ];

        return $this;
    }

    /**
     * Defines dates for document
     *
     * @return DocumentFromOrder
     */
    public function setCalculationMode(): DocumentFromOrder
    {
        switch ($this->order->round_type) {
            case OrderCore::ROUND_ITEM:
                $calculationMode = CalculationMode::ITEM;
                break;
            case OrderCore::ROUND_LINE:
                $calculationMode = CalculationMode::LINE;
                break;
            case OrderCore::ROUND_TOTAL:
                $calculationMode = CalculationMode::DOCUMENT;
                break;
            default:
                $calculationMode = (int)$this->company['documentCalculationsMode'];
                break;
        }

        $this->calculationMode = $calculationMode;

        return $this;
    }

    /**
     * Defines set
     *
     * @return DocumentFromOrder
     */
    public function setDocumentSet(): DocumentFromOrder
    {
        $this->documentSetId = (int)(Settings::get('documentSet') ?? 0);

        return $this;
    }

    /**
     * Defines document reference
     *
     * @return DocumentFromOrder
     */
    public function setOurReference(): DocumentFromOrder
    {
        switch (Settings::get('documentReference')) {
            case DocumentReference::ID:
                $reference = (string)$this->order->id;
                break;
            case DocumentReference::REFERENCE:
            default:
                $reference = $this->order->reference;
                break;
        }

        $this->ourReference = $reference;

        return $this;
    }

    /**
     * Defines document reference
     *
     * @return DocumentFromOrder
     */
    public function setYourReference(): DocumentFromOrder
    {
        $this->yourReference = null;

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
    public function setProducts(): DocumentFromOrder
    {
        $products = $this->order->getOrderDetailList();

        foreach ($products as $product) {
            $orderProduct = new OrderProduct($product, $this->fiscalZone);

            $orderProduct
                ->setDiscounts($this->discounts['product_discount'])
                ->setExchangeRate($this->exchangeRate)
                ->search();

            if ($orderProduct->getProductId() === 0) {
                $orderProduct
                    ->insert();
            }

            $this->products[] = $orderProduct;
        }

        return $this;
    }

    /**
     * Builds documents shipping
     *
     * @return DocumentFromOrder
     *
     * @throws MoloniDocumentShippingException
     * @throws MoloniDocumentShippingTaxException
     */
    public function setShipping(): DocumentFromOrder
    {
        if ($this->order->total_shipping > 0) {
            $orderShipping = new OrderShipping($this->order, $this->fiscalZone);

            $orderShipping
                ->setExchangeRate($this->exchangeRate)
                ->search();

            if ($orderShipping->getProductId() === 0) {
                $orderShipping
                    ->insert();
            }

            $this->shipping = $orderShipping;
        }

        return $this;
    }

    /**
     * Defines document discounts
     *
     * @return DocumentFromOrder
     */
    public function setDiscounts(): DocumentFromOrder
    {
        $this->discounts = [
            'financial_discount' => 0,
            'special_discount' => 0,
        ];

        $productDiscount = 0;

        $cartRules = $this->order->getCartRules();

        if (!empty($cartRules)) {
            $accumulatedDiscount = 0;

            foreach ($cartRules as $cartRule) {
                if ((int)$cartRule['free_shipping'] === Boolean::YES) {
                    continue;
                }

                $accumulatedDiscount += (float)$cartRule['value_tax_excl'];
            }

            $productDiscount = $this->calculateDiscountPercentage((float)$this->order->total_products, $accumulatedDiscount);
        }

        $this->discounts['product_discount'] = $productDiscount;

        return $this;
    }

    /**
     * Buildes document delivery info
     *
     * @return DocumentFromOrder
     *
     * @throws MoloniDocumentDeliveryException
     */
    public function setDelivery(): DocumentFromOrder
    {
        if ($this->order->id_address_delivery > 0) {
            $delivery = new OrderDelivery($this->order, $this->company);

            $delivery
                ->search();

            if ($delivery->getDeliveryMethodId() === 0) {
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
    public function setPaymentMethod(): DocumentFromOrder
    {
        /** @var PrestashopOrderPayment[] $orderPayments */
        $orderPayments = $this->order->getOrderPayments();

        foreach ($orderPayments as $orderPayment) {
            if (empty($orderPayment->payment_method)) {
                continue;
            }

            $payment = new OrderPayment($orderPayment);

            $payment
                ->setExchangeRate($this->exchangeRate)
                ->search();

            if ($payment->getPaymentMethodId() === 0) {
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
    public function setNotes(): DocumentFromOrder
    {
        $this->notes = $this->order->note;

        return $this;
    }

    //          REQUESTS          //

    /**
     * Creates document PDF
     *
     * @return $this
     */
    protected function createPdf(): DocumentFromOrder
    {
        $variables = [
            'documentId' => $this->documentId,
        ];

        try {
            switch ($this->documentType) {
                case DocumentTypes::INVOICES:
                    MoloniApiClient::invoice()->mutationInvoiceGetPDF($variables);

                    break;
                case DocumentTypes::RECEIPTS:
                    MoloniApiClient::receipt()->mutationReceiptGetPDF($variables);

                    break;
                case DocumentTypes::PRO_FORMA_INVOICES:
                    MoloniApiClient::proFormaInvoice()->mutationProFormaInvoiceGetPDF($variables);

                    break;
                case DocumentTypes::PURCHASE_ORDERS:
                    MoloniApiClient::purchaseOrder()->mutationPurchaseOrderGetPDF($variables);

                    break;
                case DocumentTypes::SIMPLIFIED_INVOICES:
                    MoloniApiClient::simplifiedInvoice()->mutationSimplifiedInvoiceGetPDF($variables);

                    break;
                case DocumentTypes::ESTIMATE:
                    MoloniApiClient::estimate()->mutationEstimateGetPDF($variables);

                    break;
                case DocumentTypes::BILLS_OF_LADING:
                    MoloniApiClient::billsOfLading()->mutationBillsOfLadingGetPDF($variables);

                    break;
            }
        } catch (MoloniApiException $e) {
            // No need to catch anything
        }

        return $this;
    }

    /**
     * Send document by email
     *
     * @return $this
     */
    protected function sendEmail(): DocumentFromOrder
    {
        $customer = $this->customer->toArray();

        if (empty($customer['email'])) {
            return $this;
        }

        $variables = [
            'documents' => [
                $this->documentId,
            ],
            'mailData' => [
                'to' => [
                    'name' => $customer['name'],
                    'email' => $customer['email'],
                ],
                'message' => '',
                'attachment' => true,
            ]
        ];

        try {
            switch ($this->documentType) {
                case DocumentTypes::INVOICES:
                    MoloniApiClient::invoice()->mutationInvoiceSendEmail($variables);

                    break;
                case DocumentTypes::RECEIPTS:
                    MoloniApiClient::receipt()->mutationReceiptSendEmail($variables);

                    break;
                case DocumentTypes::PRO_FORMA_INVOICES:
                    MoloniApiClient::proFormaInvoice()->mutationProFormaInvoiceSendEmail($variables);

                    break;
                case DocumentTypes::PURCHASE_ORDERS:
                    MoloniApiClient::purchaseOrder()->mutationPurchaseOrderSendEmail($variables);

                    break;
                case DocumentTypes::SIMPLIFIED_INVOICES:
                    MoloniApiClient::simplifiedInvoice()->mutationSimplifiedInvoiceSendEmail($variables);

                    break;
                case DocumentTypes::ESTIMATE:
                    MoloniApiClient::estimate()->mutationEstimateSendMail($variables);

                    break;
                case DocumentTypes::BILLS_OF_LADING:
                    MoloniApiClient::billsOfLading()->mutationBillsOfLadingSendEmail($variables);

                    break;
            }
        } catch (MoloniApiException $e) {
            // No need to catch anything
        }

        return $this;
    }

    //          QUERIES          //

    /**
     * Save document creation record
     *
     * @return void
     */
    protected function saveRecord(): void
    {
        $shopId = (int)Shop::getContextShopID();
        $companyId = MoloniApi::getCompanyId();

        $document = new MoloniOrderDocuments();
        $document->setShopId($shopId);
        $document->setDocumentId($this->documentId);
        $document->setCompanyId($companyId);
        $document->setDocumentType($this->documentType);
        $document->setOrderId($this->order->id);
        $document->setOrderReference($this->ourReference);
        $document->setCreatedAt(new DateTime());

        $this->entityManager->persist($document);
        $this->entityManager->flush();
    }
}
