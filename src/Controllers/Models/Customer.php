<?php

namespace Moloni\ES\Controllers\Models;

use Address;
use Country;
use Customer as psCustomer;
use Moloni\ES\Controllers\Api\Customers as apiCostumers;
use Moloni\ES\Controllers\Api\Customers as apiCustomer;
use Moloni\ES\Controllers\Api\Documents as apiDocuments;
use Order as psOrder;
use PrestaShopBundle\Translation\DataCollectorTranslator;

class Customer
{
    private $psCustomer;
    private $psOrder;

    public $customerId;
    public $vat;
    public $number;
    public $name;
    public $address;
    public $city;
    public $zipCode;
    public $email;
    public $website;
    public $phone;
    public $contactName;
    public $contactEmail;
    public $contactPhone;
    public $notes;
    public $taxID;
    public $exemptionReason;
    public $geographicZoneId;
    public $countryId;
    public $languageId;
    public $maturityDateId;
    public $paymentMethodId;
    public $documentSetId;

    //pre-defined
    private $addressCustomer;
    private $isDefault;

    /**
     * translator component
     */
    public $translator;

    public function __construct(psCustomer $customer, psOrder $order, $translator)
    {
        $this->psCustomer = $customer;
        $this->psOrder = $order;
        $this->translator = $translator;
    }

    /**
     * Populates the vars with the prestashop customer object values
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function init()
    {
        $this->addressCustomer['invoiceAddress'] = new Address($this->psOrder->id_address_invoice);

        $this->vat = !empty($this->addressCustomer['invoiceAddress']->vat_number) ?
            $this->addressCustomer['invoiceAddress']->vat_number :
            !empty($this->addressCustomer['invoiceAddress']->dni) ?
                $this->addressCustomer['invoiceAddress']->dni : null;
        $this->name = $this->psCustomer->firstname . ' ' . $this->psCustomer->lastname;
        $this->address = $this->addressCustomer['invoiceAddress']->
            address1 . $this->addressCustomer['invoiceAddress']->address2;
        $this->city = $this->addressCustomer['invoiceAddress']->city;
        $this->zipCode = $this->addressCustomer['invoiceAddress']->postcode;
        $this->email = $this->psCustomer->email;
        $this->website = $this->psCustomer->website;
        $this->phone = !empty($this->addressCustomer['invoiceAddress']->phone_mobile) ?
            $this->addressCustomer['invoiceAddress']->phone_mobile : $this->addressCustomer['invoiceAddress']->phone;
        $this->contactName = $this->psCustomer->firstname . ' ' . $this->psCustomer->lastname;
        $this->contactEmail = $this->psCustomer->email;
        $this->contactPhone = !empty($this->addressCustomer['invoiceAddress']->phone_mobile) ?
            $this->addressCustomer['invoiceAddress']->phone_mobile : $this->addressCustomer['invoiceAddress']->phone;
        $this->notes = $this->psCustomer->note;

        $this->isDefault = false;
        $this->taxID = Settings::get('Tax');
        $this->documentSetId = (int) Settings::get('Set');
        $this->paymentMethodId = (int) Settings::get('Payment');
        $this->maturityDateId = (int) Settings::get('Maturity');

        if (empty($this->taxID) ||
            empty($this->documentSetId) ||
            empty($this->paymentMethodId) ||
            empty($this->maturityDateId)) {
            $this->addError($this->translator->trans(
                'Please configure settings!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (!empty($this->addressCustomer->vat_number)) {
            if (!$this->loadByVat()) {
                return false;
            }
        } elseif (!empty($this->addressCustomer->dni)) {
            if (!$this->loadByDni()) {
                return false;
            }
        } else {
            if (!$this->loadByEmail()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Creates a customer on moloni
     *
     * @return bool
     */
    public function create()
    {
        if (!empty($this->customerId)) {
            return false;
        }

        if (!$this->setCountry()) {
            return false;
        }

        if (!$this->setNumber()) {
            return false;
        }

        if (!$this->setGeographicZone()) {
            return false;
        }

        $mutation = (apiCustomer::mutationCustomerCreate($this->setVariables()))['data']['customerCreate']['data'];

        if (empty($mutation)) {
            $this->addError($this->translator->trans(
                'Something went wrong creating the customer!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        Log::writeLog($this->translator->trans(
            'Customer ( %name% ) created!!',
            ['%name%' => $this->name],
            'Modules.Moloniprestashopes.Success'
        ));

        $this->customerId = $mutation['customerId'];

        return true;
    }

    /**
     * Loads an customer form moloni based on vat
     *
     * @return bool true or false
     */
    public function loadByVat()
    {
        $variables = [
            'companyId' => (int) Company::get('company_id'),
            'options' => [
                'search' => [
                    'field' => 'vat',
                    'value' => $this->addressCustomer->vat_number,
                ],
            ],
        ];

        $query = apiCostumers::queryCustomers($variables);

        if ($query === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching customers!!(vat)',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (empty($query)) {
            //no costumer with this VAT
            return true;
        }

        $this->customerId = $query[0]['customerId'];
        $this->number = $query[0]['number'];
        $this->vat = $query[0]['vat'];
        $this->countryId = $query[0]['country']['countryId'];
        $this->languageId = $query[0]['language']['languageId'];

        return true;
    }

    /**
     * Loads an customer form moloni based on Dni
     *
     * @return bool true or false
     */
    public function loadByDni()
    {
        $variables = [
            'companyId' => (int) Company::get('company_id'),
            'options' => [
                'search' => [
                    'field' => 'vat',
                    'value' => $this->addressCustomer->dni,
                ],
            ],
        ];

        $query = apiCostumers::queryCustomers($variables);

        if ($query === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching customers!!(dni)',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (empty($query)) {
            //no costumer with this DNI
            return true;
        }

        $this->customerId = $query[0]['customerId'];
        $this->number = $query[0]['number'];
        $this->vat = $query[0]['vat'];
        $this->countryId = $query[0]['country']['countryId'];
        $this->languageId = $query[0]['language']['languageId'];

        return true;
    }

    /**
     * Loads an customer form moloni based on email
     *
     * @return bool true or false
     */
    public function loadByEmail()
    {
        $variables = [
            'companyId' => (int) Company::get('company_id'),
            'options' => [
                'search' => [
                    'field' => 'email',
                    'value' => $this->psCustomer->email,
                ],
            ],
        ];

        $query = (apiCostumers::queryCustomers($variables));

        if ($query === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching customers!!(email)',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (empty($query)) {
            //no costumer with this Email
            return true;
        }

        $this->customerId = $query[0]['customerId'];
        $this->number = $query[0]['number'];
        $this->vat = $query[0]['vat'];
        $this->countryId = $query[0]['country']['countryId'];
        $this->languageId = $query[0]['language']['languageId'];

        return true;
    }

    /**
     * Sets the customer country information
     *
     * @return bool
     */
    public function setCountry()
    {
        $countryIso = Country::getIsoById($this->addressCustomer['invoiceAddress']->id_country);

        if (empty($countryIso)) {
            $this->addError($this->translator->trans(
                'No country iso for this costumer.',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        $variables = [
            'options' => [
                'search' => [
                    'field' => 'iso3166_1',
                    'value' => $countryIso,
                ],
            ],
        ];

        $countryLangId = (apiDocuments::queryCountries($variables));

        if ($countryLangId === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching countries!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        $this->countryId = $countryLangId[0]['countryId'];
        $this->languageId = $countryLangId[0]['language']['languageId'];

        return true;
    }

    /**
     * Sets the customer number
     *
     * @return bool
     */
    public function setNumber()
    {
        $variables = [
            'companyId' => (int) Company::get('company_id'),
            'options' => [
                'order' => [
                    'field' => 'number',
                    'sort' => 'DESC',
                ],
            ],
        ];

        $query = apiCustomer::queryCustomers($variables);

        if ($query === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching customers!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        $nextNumber = 1;
        foreach ($query as $number) {
            if (is_numeric($number['number'])) {
                $nextNumber = (int) $number['number'] + 1;
                break;
            }
        }

        $this->number = $nextNumber;

        return true;
    }

    /**
     * Sets the customer geographic zone
     *
     * @return bool
     */
    public function setGeographicZone()
    {
        $variables = [
            'companyId' => (int) Company::get('company_id'),
            'options' => [
                'search' => [
                    'field' => 'name',
                    'value' => $this->addressCustomer['invoiceAddress']->city,
                ],
            ],
        ];

        $query = apiCustomer::queryGeographicZones($variables);

        if ($query === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching geographic zones!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (empty($query)) {
            $this->geographicZoneId = null;
        } else {
            $this->geographicZoneId = $query[0]['geographicZoneId'];
        }

        return true;
    }

    /**
     * Creates an array with the arguments to connect to API
     *
     * @return array variables array
     */
    public function setVariables()
    {
        $variables = [
            'companyId' => (int) Company::get('company_id'),
            'data' => [
                'isDefault' => $this->isDefault,
                'vat' => $this->vat,
                'number' => (string) $this->number,
                'name' => $this->name,
                'address' => $this->address,
                'city' => $this->city,
                'zipCode' => $this->zipCode,
                'email' => $this->email,
                'website' => $this->website,
                'phone' => $this->phone,
                'contactName' => $this->contactName,
                'contactEmail' => $this->contactEmail,
                'contactPhone' => $this->contactPhone,
                'notes' => $this->notes,
                'geographicZoneId' => $this->geographicZoneId,
                'countryId' => $this->countryId,
                'languageId' => $this->languageId,
                'maturityDateId' => $this->maturityDateId,
                'paymentMethodId' => $this->paymentMethodId,
                'documentSetId' => $this->documentSetId,
            ],
        ];

        if (is_numeric($this->taxID)) {
            $variables['data']['taxId'] = (int) $this->taxID;
        }

        if (empty($this->website)) {
            unset($variables['data']['website']);
        }

        return $variables;
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
            'Costumer: %name%  Message: %msg%',
            ['%name%' => $this->psCustomer->firstname, '%msg%' => $msg],
            'Modules.Moloniprestashopes.Errors'
        ));

        return true;
    }
}
