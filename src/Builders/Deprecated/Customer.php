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

namespace Moloni\Builders\Deprecated;

use Address;
use Country;
use Customer as psCustomer;
use Moloni\Api\Endpoints\Countries as apiCountries;
use Moloni\Api\Endpoints\Customers as apiCustomer;
use Moloni\Api\Endpoints\GeographicZones as apiGeographicZones;
use Moloni\Helpers\Error;
use Moloni\Helpers\Log;
use Moloni\Helpers\Moloni;
use Moloni\Helpers\Settings;
use Order as psOrder;

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

    // pre-defined
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
                'Modules.Molonies.Errors'
            ));

            return false;
        }

        $this->setVat();

        if ($this->vat !== null) {
            if (!$this->loadByVat()) {
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

        $mutation = apiCustomer::mutationCustomerCreate($this->setVariables());

        if (!isset($mutation['data']['customerCreate']['data'])) {
            $this->addError($this->translator->trans(
                'Something went wrong creating the customer!!',
                [],
                'Modules.Molonies.Errors'
            ));

            return false;
        }

        Log::writeLog($this->translator->trans(
            'Customer ( %name% ) created!!',
            ['%name%' => $this->name],
            'Modules.Molonies.Success'
        ));

        $this->customerId = $mutation['data']['customerCreate']['data']['customerId'];

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
            'companyId' => (int) Moloni::get('company_id'),
            'options' => [
                'search' => [
                    'field' => 'vat',
                    'value' => $this->vat,
                ],
            ],
        ];

        $query = apiCustomer::queryCustomers($variables);

        if ($query === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching customers!!(vat)',
                [],
                'Modules.Molonies.Errors'
            ));

            return false;
        }

        if (empty($query)) {
            // no costumer with this VAT
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
            'companyId' => (int) Moloni::get('company_id'),
            'options' => [
                'search' => [
                    'field' => 'email',
                    'value' => $this->psCustomer->email,
                ],
            ],
        ];

        $query = (apiCustomer::queryCustomers($variables));

        if ($query === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching customers!!(email)',
                [],
                'Modules.Molonies.Errors'
            ));

            return false;
        }

        if (empty($query)) {
            // no costumer with this Email
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
     * Sets costumer VAT
     *
     * @return bool
     */
    public function setVat()
    {
        if ($this->addressCustomer['invoiceAddress']->vat_number !== '') {
            $this->vat = $this->addressCustomer['invoiceAddress']->vat_number;
        } elseif ($this->addressCustomer['invoiceAddress']->dni !== '') {
            $this->vat = $this->addressCustomer['invoiceAddress']->dni;
        } else {
            $this->vat = null;
        }

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
                'Modules.Molonies.Errors'
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

        $countryLangId = (apiCountries::queryCountries($variables));

        if ($countryLangId === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching countries!!',
                [],
                'Modules.Molonies.Errors'
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
            'companyId' => (int) Moloni::get('company_id'),
            'options' => [
                'filter' => [
                    'field' => 'number',
                    'comparison' => 'like',
                    'value' => Settings::get('ClientPrefix') . '%',
                ],
                'order' => [
                    'field' => 'createdAt',
                    'sort' => 'DESC',
                ],
                'pagination' => [
                    'page' => 1,
                    'qty' => 1,
                ],
            ],
        ];
        $result = (apiCustomer::queryCustomers($variables))['data']['customers']['data'];

        if (empty($result)) {
            $nextNumber = Settings::get('ClientPrefix') . '1';
        } else {
            // go straight for the first result because we only ask for 1
            $lastNumber = substr($result[0]['number'], strlen(Settings::get('ClientPrefix')));

            $nextNumber = ++$lastNumber;
            $nextNumber = Settings::get('ClientPrefix') . $nextNumber;
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
            'companyId' => (int) Moloni::get('company_id'),
            'options' => [
                'search' => [
                    'field' => 'name',
                    'value' => $this->addressCustomer['invoiceAddress']->city,
                ],
            ],
        ];

        $query = apiGeographicZones::queryGeographicZones($variables);

        if ($query === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching geographic zones!!',
                [],
                'Modules.Molonies.Errors'
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
            'companyId' => (int) Moloni::get('company_id'),
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
            'Modules.Molonies.Errors'
        ));

        return true;
    }
}
