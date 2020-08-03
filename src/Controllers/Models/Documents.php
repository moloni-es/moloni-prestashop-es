<?php

namespace Moloni\ES\Controllers\Models;

use Address;
use Carrier;
use Configuration;
use Country;
use Currency;
use Customer;
use Datetime;
use Moloni\ES\Controllers\Api\Companies as ApiCompanies;
use Moloni\ES\Controllers\Api\Documents as ApiDocuments;
use Moloni\ES\Controllers\Models\Company as modelCompany;
use Moloni\ES\Controllers\Models\Customer as modelCustomer;
use Moloni\ES\Controllers\Models\Settings as modelSettings;
use Order;
use OrderInvoice;
use PrestaShopBundle\Translation\TranslatorComponent;

class Documents
{
    //prestashop models
    private $psOrder;
    private $psCoinOrder;
    private $psDeliveryAddress;
    private $psCustomer;
    private $psCarrierOrder;
    private $psInvoiceIdOrder;
    private $moloniCustomer;
    private $documentType;

    public $companyId;
    public $customerId;
    public $documentId;
    public $documentSetId;
    public $ourReference;
    public $yourReference;
    public $date;
    public $products;
    public $status = 0;
    public $expirationDate;
    public $maturityDateId;
    public $currencyExchangeId;
    public $currencyExchangeExchange;
    public $deliveryMethodId;
    public $deliveryLoadDate;
    public $deliveryLoadAddress;
    public $deliveryLoadCity;
    public $deliveryLoadZipCode;
    public $deliveryLoadCountryId;
    public $deliveryUnloadAddress;
    public $deliveryUnloadCity;
    public $deliveryUnloadZipCode;
    public $deliveryUnloadCountryId;
    public $notes;
    public $moloniTotal;
    public $paymentMethod = [];
    public $paymentValue;
    public $percentDiscount;

    public $financialDiscount = 0;
    public $globalDiscount = 0;
    public $billOfLading;

    /**
     * translator instance form context
     */
    public $translator;

    /**
     * Documents constructor.
     *
     * @param $orderId int order id
     * @param $documentType string type of the document to create
     * @param TranslatorComponent $translator translator component
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function __construct($orderId, $documentType, $translator)
    {
        //gets the translator instance
        $this->translator = $translator;

        if (!is_numeric($orderId) || empty($documentType)) {
            $this->addError($this->translator->trans(
                'Invalid order id or document type!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));
        }

        $this->psOrder = new Order($orderId);
        $this->psCoinOrder = new Currency($this->psOrder->id_currency);
        $this->psDeliveryAddress = new Address($this->psOrder->id_address_delivery);
        $this->psCarrierOrder = new Carrier($this->psOrder->id_carrier);
        $this->psInvoiceIdOrder = OrderInvoice::getInvoiceByNumber(
            (int) $this->psOrder->getOrderDetailList()[0]['id_order_invoice']
        );
        $this->psCustomer = new Customer($this->psOrder->id_customer);
        $this->moloniCustomer = new modelCustomer($this->psCustomer, $this->psOrder, $this->translator);

        $this->documentType = $documentType;
    }

    /**
     * Instantiate the class
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException|\PrestaShopException
     */
    public function init()
    {
        $datetime = (new DateTime())->format(Datetime::ATOM);
        $this->percentDiscount = (float) $this->getDiscountPercentage($this->psOrder);
        $invoiceDate = new DateTime($this->psOrder->invoice_date);

        $this->date = $datetime;
        $this->expirationDate = $datetime;
        $this->ourReference = $this->psOrder->reference;

        //check this value because in actionpaymentconfirmation it is empty
        if ($this->psOrder->invoice_date == '0000-00-00 00:00:00') {
            $this->deliveryLoadDate = $datetime;
        } else {
            $this->deliveryLoadDate = $invoiceDate->format(Datetime::ATOM);
        }

        $this->deliveryUnloadAddress = $this->psDeliveryAddress->address1 . $this->psDeliveryAddress->address2;
        $this->deliveryUnloadCity = $this->psDeliveryAddress->city;
        $this->deliveryUnloadZipCode = $this->psDeliveryAddress->postcode;
        $this->maturityDateId = (int) modelSettings::get('Maturity');
        $this->documentSetId = (int) modelSettings::get('Set');

        if (empty($this->documentSetId) ||
            empty($this->maturityDateId)) {
            $this->addError($this->translator->trans(
                'Please configure settings!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (!$this->setProducts()) {
            Log::writeLog($this->translator->trans(
                'Error setting products!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (!$this->setShipmentFee()) {
            Log::writeLog($this->translator->trans(
                'Error setting shipment fee!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (!$this->setCustomer()) {
            Log::writeLog($this->translator->trans(
                'Error setting customer!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (!$this->setAddressLoad()) {
            Log::writeLog($this->translator->trans(
                'Error setting address!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (!$this->setCurrency()) {
            Log::writeLog($this->translator->trans(
                'Error setting currency!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (!$this->setDeliveryMethods()) {
            Log::writeLog($this->translator->trans(
                'Error setting delivery methods!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (!$this->setDeliveryAddress()) {
            Log::writeLog($this->translator->trans(
                'Error setting delivery address!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (!$this->setTaxes()) {
            Log::writeLog($this->translator->trans(
                'Error setting taxes!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (!$this->setNotes()) {
            Log::writeLog($this->translator->trans(
                'Error setting notes!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (!$this->setPaymentMethods()) {
            Log::writeLog($this->translator->trans(
                'Error setting payment method!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        return true;
    }

    /**
     * Creates an documents(and a bill of lading if set to yes)
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function create()
    {
        if (Settings::get('Transport') == 1) {
            if (!$this->createBills()) {
                return false;
            }
        }

        switch ($this->documentType) {
            case 'invoices':
                $mutation = ApiDocuments::mutationInvoiceCreate($this->setVariables());

                if (isset($mutation['errors']) ||
                    !empty($mutation['data']['invoiceCreate']['errors'])) {
                    $this->addError($this->translator->trans(
                        'Error creating invoice!!',
                        [],
                        'Modules.Moloniprestashopes.Errors'
                    ));

                    return false;
                }

                $mutation = $mutation['data']['invoiceCreate']['data'];
                break;
            case 'receipts':
                $mutationInvoice = ApiDocuments::mutationInvoiceCreate($this->setVariables());

                if (isset($mutationInvoice['errors']) ||
                    !empty($mutationInvoice['data']['invoiceCreate']['errors'])) {
                    $this->addError($this->translator->trans(
                        'Error creating invoice!!',
                        [],
                        'Modules.Moloniprestashopes.Errors'
                    ));

                    return false;
                }

                $mutationInvoice = $mutationInvoice['data']['invoiceCreate']['data'];

                //set some info to close invoice
                $this->moloniTotal = $mutationInvoice['totalValue'];
                $this->documentId = $mutationInvoice['documentId'];
                $this->ourReference = $mutationInvoice['ourReference'];

                $this->documentType = 'invoices'; //temporary modification to close invoice
                $this->closeDocument(); //close invoice (sets $this->status to 1 if values match, 0 otherwise)
                $this->createPDF();

                Log::writeLog($this->translator->trans(
                    'Document created with success!! ( %doctype% ) ( %status% )',
                    ['%doctype%' => $this->documentType, '%status%' => $this->status],
                    'Modules.Moloniprestashopes.Success'
                ));

                //if the document did not close (values did not match), save invoice as draft
                if ((int) $this->status === 0) {
                    $this->saveDatabase();
                    $this->addError($this->translator->trans(
                        'Document saved as draft because values do not match.',
                        [],
                        'Modules.Moloniprestashopes.Errors'
                    ));

                    return false;
                }
                $this->documentType = 'receipts';

                //after creating invoice create receipt in draft
                $mutation = ApiDocuments::mutationReceiptCreate($this->setVariablesReceipt($mutationInvoice));

                if (isset($mutation['errors']) ||
                    !empty($mutation['data']['receiptCreate']['errors'])) {
                    $this->addError($this->translator->trans(
                        'Error creating receipt!!',
                        [],
                        'Modules.Moloniprestashopes.Errors'
                    ));

                    return false;
                }

                $mutation = $mutation['data']['receiptCreate']['data'];
                $this->documentId = $mutation['documentId'];
                break;
            case 'purchaseOrders':
                $mutation = ApiDocuments::mutationPurchaseOrderCreate($this->setVariables());

                if (isset($mutation['errors']) ||
                    !empty($mutation['data']['purchaseOrderCreate']['errors'])) {
                    $this->addError($this->translator->trans(
                        'Error creating purchase order.',
                        [],
                        'Modules.Moloniprestashopes.Errors'
                    ));

                    return false;
                }

                $mutation = $mutation['data']['purchaseOrderCreate']['data'];
                break;
            case 'proFormaInvoices':
                $mutation = ApiDocuments::mutationProFormaInvoiceCreate($this->setVariables());

                if (isset($mutation['errors']) ||
                    !empty($mutation['data']['proFormaInvoiceCreate']['errors'])) {
                    $this->addError($this->translator->trans(
                        'Error creating pro forma invoice!!',
                        [],
                        'Modules.Moloniprestashopes.Errors'
                    ));

                    return false;
                }

                $mutation = $mutation['data']['proFormaInvoiceCreate']['data'];
                break;
            case 'simplifiedInvoices':
                $mutation = ApiDocuments::mutationSimplifiedInvoiceCreate($this->setVariables());

                if (isset($mutation['errors']) ||
                    !empty($mutation['data']['simplifiedInvoiceCreate']['errors'])) {
                    $this->addError($this->translator->trans(
                        'Error creating simplified invoice!!',
                        [],
                        'Modules.Moloniprestashopes.Errors'
                    ));

                    return false;
                }

                $mutation = $mutation['data']['simplifiedInvoiceCreate']['data'];
                break;
            default:
                $this->addError($this->translator->trans(
                    'Document type does not exist.',
                    [],
                    'Modules.Moloniprestashopes.Errors'
                ));

                return false;
                break;
        }

        $this->documentId = $mutation['documentId'];
        if ($this->documentType !== 'receipts') {
            $this->ourReference = $mutation['ourReference'];
        }
        $this->moloniTotal = $mutation['totalValue'];

        //if documents are closed (in settings), close document
        //needs to be done after inserting but before saving and create pdf
        if ((int) modelSettings::get('Status') === 1) {
            $this->closeDocument();
        }

        if (!$this->saveDatabase()) {
            $this->addError($this->translator->trans(
                'Error saving to database!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if ($this->status === 1 && !$this->createPDF()) {
            $this->addError($this->translator->trans(
                'Error creating PDF',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        Log::writeLog($this->translator->trans(
            'Document created with success!! ( %doctype% ) ( %status% )',
            ['%doctype%' => $this->documentType, '%status%' => $this->status],
            'Modules.Moloniprestashopes.Success'
        ));

        //checks if the values match to show the user a msg
        $this->checkValues();

        return true;
    }

    /**
     * Sets the document currency
     *
     * @return bool
     */
    public function setCurrency()
    {
        $variables = [
            'options' => [
                'search' => [
                    'field' => 'iso4217',
                    'value' => $this->psCoinOrder->iso_code,
                ],
            ],
        ];

        $query = ApiDocuments::queryCurrencies($variables);

        if ($query === false) {
            $this->addError($this->translator->trans(
                'Error fetching currency!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        $this->currencyExchangeId = $query[0]['currencyId'];
        $this->currencyExchangeExchange = $query[0]['numberDecimalPlaces'];

        return true;
    }

    /**
     * Sets the delivery methods
     *
     * @return bool
     */
    public function setDeliveryMethods()
    {
        $variables = [
            'companyId' => (int) modelCompany::get('company_id'),
            'options' => [
                'search' => [
                    'field' => 'name',
                    'value' => $this->psCarrierOrder->name,
                ],
            ],
        ];

        $query = ApiDocuments::queryDeliveryMethods($variables);

        if ($query === false) {
            $this->addError($this->translator->trans(
                'Error fetching delivery methods',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (!empty($query)) {
            $this->deliveryMethodId = $query[0]['deliveryMethodId'];
        } else {
            $this->deliveryMethodId = null;
        }

        return true;
    }

    /**
     * Sets the delivery addresses
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function setDeliveryAddress()
    {
        if (Settings::get('Address') == 'moloni') {
            $variables = ['companyId' => (int) Company::get('company_id'), 'options' => null];
            $countryId = ApiCompanies::queryCompany2($variables);
            $companyAddressData = $countryId['data']['company']['data'];
            $this->deliveryLoadCountryId = $companyAddressData['country']['countryId'];
        } else {
            $addressPS = new \Store((int) Settings::get('Address'));
            $countryIsoDeliveryLoad = Country::getIsoById($addressPS->id_country);
            $variables = [
                'options' => [
                    'search' => [
                        'field' => 'iso3166_1',
                        'value' => $countryIsoDeliveryLoad,
                    ],
                ],
            ];

            $query = ApiDocuments::queryCountries($variables);

            if (empty($query)) {
                return false;
            }
            $this->deliveryLoadCountryId = $query[0]['countryId'];
        }
        $countryIsoDeliveryUnload = Country::getIsoById($this->psDeliveryAddress->id_country);
        $variables = [
            'options' => [
                'search' => [
                    'field' => 'iso3166_1',
                    'value' => $countryIsoDeliveryUnload,
                ],
            ],
        ];

        $query = ApiDocuments::queryCountries($variables);

        if ($query === false) {
            $this->addError($this->translator->trans(
                'Error fetching countries!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (empty($query)) {
            return false;
        }
        $this->deliveryUnloadCountryId = $query[0]['countryId'];

        return true;
    }

    /**
     * Sets the taxes
     *
     * @return bool
     */
    public function setTaxes()
    {
        return true;
    }

    /**
     * Sets the shippment fees
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function setShipmentFee()
    {
        //if it had no shipment fees, do nothing
        if ($this->psOrder->total_shipping == 0) {
            return true;
        }

        $fee = new Fees($this->psOrder, $this->translator);

        if (!$fee->init()) {
            return false;
        }

        //if the product does not exist in moloni, create it
        if (empty($fee->productId)) {
            if (!$fee->create()) {
                return false;
            }
        }

        //needs to be the last item
        $fee->ordering = count($this->products) + 1;

        //add shipment as product to the end of array
        $this->products[] = $fee->getVariablesForDocuments();

        return true;
    }

    /**
     * Sets the payment methods
     *
     * @return bool
     */
    public function setPaymentMethods()
    {
        $orderPayments = $this->psOrder->getOrderPayments();

        foreach ($orderPayments as $orderPayment) {
            if (!empty($orderPayment->payment_method)) {
                $paymentMethod = new Payment($orderPayment->payment_method);

                if (!$paymentMethod->loadByName() && !$paymentMethod->create()) {
                    return false;
                }

                if ($paymentMethod->paymentMethodId > 0) {
                    $this->paymentMethod[] = [
                        'paymentMethodId' => (int) $paymentMethod->paymentMethodId,
                        'paymentMethodName' => $paymentMethod->name,
                        'value' => (float) $orderPayment->amount,
                    ];
                }
            }
        }

        return true;
    }

    /**
     * Set the document notes
     *
     * @return bool
     */
    public function setNotes()
    {
        $this->notes = $this->psInvoiceIdOrder->note;

        return true;
    }

    /**
     * Creates a bill of lading
     *
     * @return bool
     */
    public function createBills()
    {
        $mutation = ApiDocuments::mutationBillsOfLadingCreate($this->setVariables('bills'));

        if (isset($mutation['errors'])) {
            $this->addError($this->translator->trans(
                'Error creating bill of landing',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        $mutation = $mutation['data']['billsOfLadingCreate']['data'];

        $this->billOfLading = $mutation;

        Log::writeLog($this->translator->trans(
            'Created bill of lading.',
            [],
            'Modules.Moloniprestashopes.Errors'
        ));

        return true;
    }

    /**
     * Returns the variables needed to create a document
     *
     * @param null $bills
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     */
    public function setVariables($bills = null)
    {
        $variables = [
            'companyId' => (int) modelCompany::get('company_id'),
            'data' => [
                'documentSetId' => $this->documentSetId,
                'date' => $this->date,
                'expirationDate' => $this->expirationDate,
                'maturityDateId' => $this->maturityDateId,
                'ourReference' => $this->ourReference,
                'customerId' => $this->customerId,
                'deliveryMethodId' => $this->deliveryMethodId,
                'deliveryLoadDate' => $this->deliveryLoadDate,
                'deliveryLoadAddress' => $this->deliveryLoadAddress,
                'deliveryLoadCity' => $this->deliveryLoadCity,
                'deliveryLoadZipCode' => $this->deliveryLoadZipCode,
                'deliveryLoadCountryId' => $this->deliveryLoadCountryId,
                'deliveryUnloadAddress' => $this->deliveryUnloadAddress,
                'deliveryUnloadCity' => $this->deliveryUnloadCity,
                'deliveryUnloadZipCode' => $this->deliveryUnloadZipCode,
                'deliveryUnloadCountryId' => $this->deliveryUnloadCountryId,
                'notes' => $this->notes,
                'status' => ($bills === null) ? 0 : 1,
                'products' => $this->products,
            ],
        ];

        if ($this->documentType == 'simplifiedInvoices') {
            $variables['data']['payments'] = $this->paymentMethod;
        }

        if (Settings::get('Send') == 0 && empty($bills)) {
            unset($variables['data']['deliveryMethodId']);
            unset($variables['data']['deliveryLoadDate']);
            unset($variables['data']['deliveryLoadAddress']);
            unset($variables['data']['deliveryLoadCity']);
            unset($variables['data']['deliveryLoadZipCode']);
            unset($variables['data']['deliveryLoadCountryId']);
            unset($variables['data']['deliveryUnloadAddress']);
            unset($variables['data']['deliveryUnloadCity']);
            unset($variables['data']['deliveryUnloadZipCode']);
            unset($variables['data']['deliveryUnloadCountryId']);
        }

        if (Settings::get('Transport') == 1 && empty($bills)) {
            $variables['data']['relatedWith']['relatedDocumentId'] = $this->billOfLading['documentId'];
            $variables['data']['relatedWith']['value'] = $this->billOfLading['totalValue'];
        }

        return $variables;
    }

    /**
     * Returns the variables needed to create a receipt
     *
     * @param $invoiceMutation array invoice data to relate with receipt
     *
     * @return array
     */
    public function setVariablesReceipt($invoiceMutation)
    {
        $variables = [
            'companyId' => (int) modelCompany::get('company_id'),
            'data' => [
                'documentSetId' => $this->documentSetId,
                'date' => $this->date,
                'customerId' => $this->customerId,
                'relatedWith' => [
                    'relatedDocumentId' => $invoiceMutation['documentId'],
                    'value' => $invoiceMutation['totalValue'],
                ],
                'notes' => $this->notes,
                'status' => 0,
                'totalValue' => $invoiceMutation['totalValue'],
            ],
        ];

        if ($this->documentType == 'receipts') {
            $variables['data']['payments'] = $this->paymentMethod;
        }

        return $variables;
    }

    /**
     * Adds the order products to the document products array
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function setProducts()
    {
        if (empty($this->psOrder->getCartProducts())) {
            $this->addError($this->translator->trans(
                'Order has no products!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        } else {
            //for each order product
            foreach ($this->psOrder->getCartProducts() as $key => $product) {
                $productPS = new \PrestaShop\PrestaShop\Adapter\Entity\Product(
                    $product['product_id'],
                    1,
                    Configuration::get('PS_LANG_DEFAULT')
                );

                $productPurchased = new Product($productPS, $this->translator);
                $productPurchased->priceWithTax = $product['total_price_tax_incl'];
                $productPurchased->discount = $this->percentDiscount;

                //see if product exists in moloni
                if (!$productPurchased->init()) {
                    return false;
                }

                //if not create it
                if (empty($productPurchased->productId)) {
                    if (!$productPurchased->create()) {
                        return false;
                    }
                }

                //information needed only in this case
                $productPurchased->ordering = $key;
                $productPurchased->qty = $product['product_quantity'];

                //gets all data to add to the products array
                $this->products[] = $productPurchased->setVariablesForDocument($product);
            }

            return true;
        }
    }

    /**
     * Creates ou gets the id of the order costumer (from moloni)
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function setCustomer()
    {
        if (!$this->moloniCustomer->init()) {
            return false;
        }
        if (empty($this->moloniCustomer->customerId)) {
            if (!$this->moloniCustomer->create()) {
                return false;
            }
        }

        $this->customerId = $this->moloniCustomer->customerId;

        return true;
    }

    /**
     * Sets the load address
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function setAddressLoad()
    {
        if (Settings::get('Address') == 'moloni') {
            $variables = ['companyId' => (int) Company::get('company_id'), 'options' => null];
            $companyAddress = ApiCompanies::queryCompany($variables);

            if (isset($companyAddress['errors'])) {
                $this->addError($this->translator->trans(
                    'Error getting company addresses!!',
                    [],
                    'Modules.Moloniprestashopes.Errors'
                ));

                return false;
            }

            $companyAddressData = $companyAddress['data']['company']['data'];
            $this->deliveryLoadAddress = $companyAddressData['address'];
            $this->deliveryLoadCity = $companyAddressData['city'];
            $this->deliveryLoadZipCode = $companyAddressData['zipCode'];
        } else {
            $addressPS = new \Store((int) Settings::get('Address'));
            $this->deliveryLoadAddress = $addressPS->address1[1];
            $this->deliveryLoadCity = $addressPS->city;
            $this->deliveryLoadZipCode = $addressPS->postcode;
        }

        return true;
    }

    /**
     * Returns true if the values match, false otherwise
     *
     * @return bool
     */
    public function checkValues()
    {
        if ($this->moloniTotal != $this->psOrder->total_paid_tax_incl) {
            $this->addError($this->translator->trans(
                'Document saved as draft because values do no match!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        return true;
    }

    /**
     * Closes the document
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function closeDocument()
    {
        //to close a document, values must be the same
        //otherwise create the document in draft mode
        if (!$this->checkValues()) {
            return false;
        }

        $variables = [
            'companyId' => (int) modelCompany::get('company_id'),
            'data' => [
                'documentId' => (int) $this->documentId,
                'status' => 1,
            ],
        ];

        switch ($this->documentType) {
            case 'invoices':
                $mutation = ApiDocuments::mutationInvoiceUpdate($variables);
                $mutation = $mutation['data']['invoiceUpdate']['data'];
                break;
            case 'receipts':
                $mutation = ApiDocuments::mutationReceiptUpdate($variables);
                $mutation = $mutation['data']['receiptUpdate']['data'];
                break;
            case 'purchaseOrders':
                $mutation = ApiDocuments::mutationPurchaseOrderUpdate($variables);
                $mutation = $mutation['data']['purchaseOrderUpdate']['data'];
                break;
            case 'proFormaInvoices':
                $mutation = ApiDocuments::mutationProFormaInvoiceUpdate($variables);
                $mutation = $mutation['data']['proFormaInvoiceUpdate']['data'];
                break;
            case 'simplifiedInvoices':
                $mutation = ApiDocuments::mutationSimplifiedInvoiceUpdate($variables);
                $mutation = $mutation['data']['simplifiedInvoiceUpdate']['data'];
                break;
            default:
                $this->addError($this->translator->trans(
                    'Document type does not exist.',
                    [],
                    'Modules.Moloniprestashopes.Errors'
                ));

                return false;
        }

        if (!isset($mutation['documentId'])) {
            $this->addError($this->translator->trans(
                'Error closing document!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        $this->status = 1;

        return true;
    }

    /**
     * Creates an entry for the document int the database
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function saveDatabase()
    {
        $dataBase = \Db::getInstance();
        $dataBase->insert(
            'moloni_documents',
            [
                'document_id' => $this->documentId,
                'reference' => pSQL($this->ourReference),
                'company_id' => modelCompany::get('company_id'),
                'store_id' => 1,
                'id_order' => $this->psOrder->id,
                'order_ref' => $this->psOrder->reference,
                'order_total' => $this->psOrder->total_paid,
                'id_order_invoice' => (int) $this->psOrder->getOrderDetailList()[0]['id_order_invoice'],
                'invoice_total' => $this->moloniTotal,
                'invoice_type' => pSQL($this->documentType),
                'invoice_status' => $this->status,
                'metadata' => json_encode($this->psOrder),
            ]
        );

        return true;
    }

    /**
     * Generates the document pdf
     * (In moloni ES the pdf needs to be generated before downloading)
     *
     * @return bool
     */
    public function createPDF()
    {
        $variables = [
            'companyId' => (int) Company::get('company_id'),
            'documentId' => (int) $this->documentId,
        ];

        switch ($this->documentType) {
            case 'invoices':
                $result = ApiDocuments::mutationInvoiceGetPDF($variables);
                $result = $result['data']['invoiceGetPDF'];
                break;
            case 'receipts':
                $result = ApiDocuments::mutationReceiptGetPDF($variables);
                $result = $result['data']['receiptGetPDF'];
                break;
            case 'purchaseOrders':
                $result = ApiDocuments::mutationPurchaseOrderGetPDF($variables);
                $result = $result['data']['purchaseOrderGetPDF'];
                break;
            case 'proFormaInvoices':
                $result = ApiDocuments::mutationProFormaInvoiceGetPDF($variables);
                $result = $result['data']['proFormaInvoiceGetPDF'];
                break;
            case 'simplifiedInvoices':
                $result = ApiDocuments::mutationSimplifiedInvoiceGetPDF($variables);
                $result = $result['data']['simplifiedInvoiceGetPDF'];
                break;
            default:
                $this->addError($this->translator->trans(
                    'Document type does not exist.',
                    [],
                    'Modules.Moloniprestashopes.Errors'
                ));

                return false;
                break;
        }

        if (!$result) {
            $this->addError($this->translator->trans(
                'Error creating pdf!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        return true;
    }

    /**
     * Adds an error to the error class and logs
     *
     * @param $msg string Error string
     *
     * @return bool
     */
    public function addError($msg)
    {
        Log::writeLog($msg);
        Error::addError($this->translator->trans(
            'Order: %reference% | Message: %msg%',
            ['%reference%' => $this->psOrder->reference, '%msg%' => $msg],
            'Modules.Moloniprestashopes.Errors'
        ));

        return true;
    }

    /**
     * Creates the url to view and download the document
     *
     * @param $idDocument int id of the document
     *
     * @return bool|string
     */
    public static function downloadPDF($idDocument)
    {
        //check if the id is the create document database
        $db = \Db::getInstance();
        $sql = 'SELECT invoice_type FROM ' . _DB_PREFIX_ . 'moloni_documents ' .
            'Where document_id = ' . $idDocument;
        $sqlQuery = $db->getRow($sql);

        if (empty($sqlQuery)) {
            Log::writeLog('Document does no exist!!');
            Error::addError('Document does no exist!!');

            return false;
        }

        $variables = [
            'documentId' => (int) $idDocument,
        ];

        switch ($sqlQuery['invoice_type']) {
            case 'invoices':
                $result = ApiDocuments::queryInvoiceGetPDFToken($variables);

                if (isset($result['errors'])) {
                    return false;
                }

                $result = $result['data']['invoiceGetPDFToken']['data'];
                break;
            case 'receipts':
                $result = ApiDocuments::queryReceiptGetPDFToken($variables);

                if (isset($result['errors'])) {
                    return false;
                }

                $result = $result['data']['receiptGetPDFToken']['data'];
                break;
            case 'purchaseOrders':
                $result = ApiDocuments::queryPurchaseOrderGetPDFToken($variables);

                if (isset($result['errors'])) {
                    return false;
                }

                $result = $result['data']['purchaseOrderGetPDFToken']['data'];
                break;
            case 'proFormaInvoices':
                $result = ApiDocuments::queryProFormaInvoiceGetPDFToken($variables);

                if (isset($result['errors'])) {
                    return false;
                }

                $result = $result['data']['proFormaInvoiceGetPDFToken']['data'];
                break;
            case 'simplifiedInvoices':
                $result = ApiDocuments::querySimplifiedInvoiceGetPDFToken($variables);

                if (isset($result['errors'])) {
                    return false;
                }

                $result = $result['data']['simplifiedInvoiceGetPDFToken']['data'];
                break;
            default:
                Log::writeLog('Document type does not exist.');
                Error::addError('Document type does not exist.');

                return false;
                break;
        }

        return 'https://mediaapi.moloni.org' . $result['path'] . '?jwt=' . $result['token'];
    }

    /**
     * Creates an url to see the document in ac.moloni.es
     *
     * @param $idDocument int id of the document
     *
     * @return bool|string
     */
    public static function viewURL($idDocument)
    {
        $dataBase = \Db::getInstance();
        $sql = 'SELECT invoice_type, company_id FROM ' . _DB_PREFIX_ . 'moloni_documents ' .
            'Where document_id = ' . $idDocument;
        $sqlQuery = $dataBase->getRow($sql);

        if (empty($sqlQuery)) {
            Log::writeLog('Document does no exist!!');
            Error::addError('Document does no exist!!');

            return false;
        }

        $variables = [
            'companyId' => (int) $sqlQuery['company_id'],
            'options' => ['defaultLanguageId' => 1],
        ];

        $result = ApiCompanies::queryCompany($variables);

        if (isset($result['errors'])) {
            Log::writeLog('Error getting the slug!!');
            Error::addError('Error getting the slug!!');

            return false;
        }

        $result = $result['data']['company']['data'];

        return 'https://ac.moloni.es/' . $result['slug'] . '/' . $sqlQuery['invoice_type'] . '/view/' . $idDocument;
    }

    //***** Copied from MoloniPT and modified ******//

    /**
     * Calculates the total discount percentage
     *
     * @param $order Order this document order
     *
     * @return false|float|int|mixed
     */
    private function getDiscountPercentage($order)
    {
        $cartRules = $order->getCartRules();
        $discount = 0;
        $discountTotal = 0;

        if (!empty($cartRules) && is_array($cartRules)) {
            if (count($cartRules) == 1) {
                $singleCartRule = \Db::getInstance()->getRow(
                    'SELECT * FROM ' . _DB_PREFIX_ . "cart_rule WHERE id_cart_rule = '" .
                    (int) $cartRules[0]['id_cart_rule'] . "'"
                );

                if (!empty($singleCartRule) && is_array($singleCartRule)) {
                    if ($singleCartRule['reduction_percent'] > 0) {
                        $discount = $singleCartRule['reduction_percent'];
                    } else {
                        $discountTotal = $this->getCartRulesTotal($cartRules);
                    }
                }
            } else {
                $discountTotal = $this->getCartRulesTotal($cartRules);
            }
        }

        if ($discountTotal != '0') {
            $discount = round(($discountTotal * 100) / $order->total_products, 5);
            $discount = ($discount > 100) ? 100 : $discount;
        }

        return $discount;
    }

    /**
     * Gets all order discounts
     *
     * @return float|int|mixed
     */
    private function getCartRulesTotal(array $cartRules)
    {
        $discountTotal = 0;
        $i = 0;
        foreach ($cartRules as $rule) {
            $discountTotal = $discountTotal + $rule['value_tax_excl'];
            if ($rule['free_shipping'] == 1) {
                ++$i;
            }
        }

        if ($i == count($cartRules)) {
            $discountTotal = $discountTotal - ((int) $this->psOrder->getShipping()[0]['shipping_cost_tax_excl']) * $i;
        } else {
            $discountTotal = $discountTotal - ((int) $this->psOrder->getShipping()[0]['shipping_cost_tax_excl']);
        }

        return $discountTotal;
    }
}
