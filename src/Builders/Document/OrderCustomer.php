<?php

/**
 * 2025 - Moloni.com
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

use Address;
use Customer;
use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Interfaces\BuilderItemInterface;
use Moloni\Enums\Countries;
use Moloni\Exceptions\Document\MoloniDocumentCustomerException;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Tools\Settings;
use Moloni\Traits\CountryTrait;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderCustomer implements BuilderItemInterface
{
    use CountryTrait;

    /**
     * Customer id in Moloni
     *
     * @var int
     */
    protected $customerId = 0;

    /**
     * Customer VAT
     *
     * @var string|null
     */
    protected $vat;

    /**
     * Customer email
     *
     * @var string
     */
    protected $email = '';

    /**
     * Customer number
     *
     * @var string
     */
    protected $number;

    /**
     * Customer name
     *
     * @var string
     */
    protected $name;

    /**
     * Customer phone
     *
     * @var string
     */
    protected $phone;

    /**
     * Customer address
     *
     * @var string
     */
    protected $address;

    /**
     * Customer city
     *
     * @var string
     */
    protected $city;

    /**
     * Customer zip-code
     *
     * @var string
     */
    protected $zipCode;

    /**
     * Customer country
     *
     * @var int
     */
    protected $countryId;

    /**
     * Customer language
     *
     * @var int
     */
    public $languageId;

    /**
     * Customer website
     *
     * @var string
     */
    protected $website;

    /**
     * Shopify order data
     *
     * @var \Order
     */
    protected $order;

    /**
     * Shopify order data
     *
     * @var \Customer
     */
    protected $customer;

    /**
     * Order billing address
     *
     * @var \Address
     */
    protected $billingAddress;

    /**
     * Constructor
     *
     * @throws MoloniDocumentCustomerException
     */
    public function __construct(\Order $order)
    {
        $this->order = $order;
        $this->customer = $order->getCustomer();
        $this->billingAddress = new \Address($order->id_address_invoice);

        $this->init();
    }

    //          PUBLICS          //

    /**
     * Searches customer in Moloni
     *
     * @throws MoloniDocumentCustomerException
     */
    public function search(): void
    {
        if (!empty($this->vat)) {
            $this->searchByVat();
        } else {
            $this->searchByEmail();
        }
    }

    /**
     * Creates a customer in Moloni
     *
     * @throws MoloniDocumentCustomerException
     */
    public function insert(): OrderCustomer
    {
        $this->setNumber();

        try {
            $params = [
                'data' => [
                    'vat' => $this->vat,
                    'number' => $this->number,
                    'name' => $this->name,
                    'address' => $this->address,
                    'city' => $this->city,
                    'zipCode' => $this->zipCode,
                    'email' => !empty($this->email) ? $this->email : null,
                    'website' => !empty($this->website) ? $this->website : null,
                    'phone' => $this->phone,
                    'countryId' => $this->countryId,
                    'languageId' => $this->languageId,
                ],
            ];

            $mutation = MoloniApiClient::customers()
                ->mutationCustomerCreate($params);

            $costumerId = $mutation['data']['customerCreate']['data']['customerId'] ?? 0;

            if ((int) $costumerId > 0) {
                $this->customerId = (int) $costumerId;
            } else {
                throw new MoloniDocumentCustomerException('Error creating customer ({0})', ['{0}' => $this->name], ['params' => $params, 'response' => $mutation]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentCustomerException('Error creating customer ({0})', ['{0}' => $this->name], $e->getData());
        }

        return $this;
    }

    /**
     * Update a customer in Moloni
     *
     * @throws MoloniDocumentCustomerException
     */
    public function update(): OrderCustomer
    {
        try {
            $params = [
                'data' => [
                    'customerId' => $this->customerId,
                    'name' => $this->name,
                    'address' => $this->address,
                    'city' => $this->city,
                    'zipCode' => $this->zipCode,
                    'website' => !empty($this->website) ? $this->website : null,
                    'phone' => $this->phone,
                    'countryId' => $this->countryId,
                    'languageId' => $this->languageId,
                ],
            ];

            $mutation = MoloniApiClient::customers()
                ->mutationCustomerUpdate($params);

            $costumerId = $mutation['data']['customerUpdate']['data']['customerId'] ?? 0;

            if ((int) $costumerId > 0) {
                $this->customerId = (int) $costumerId;
            } else {
                throw new MoloniDocumentCustomerException('Error updating customer ({0})', ['{0}' => $this->name], ['params' => $params, 'response' => $mutation]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentCustomerException('Error updating customer ({0})', ['{0}' => $this->name], $e->getData());
        }

        return $this;
    }

    /**
     * Exports customer data to array format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'customerId' => $this->customerId,
        ];
    }

    //          PRIVATES          //

    /**
     * Start initial values
     *
     * @throws MoloniDocumentCustomerException
     */
    protected function init(): OrderCustomer
    {
        $this
            ->setName()
            ->setLanguageAndCountryId()
            ->setVat()
            ->setEmail()
            ->setWebsite()
            ->setPhone()
            ->setAddress()
            ->setCity()
            ->setZipCode();

        return $this;
    }

    //          GETS          //

    /**
     * Fetch customer ID
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    //          SETS          //

    /**
     * Sets customer country
     *
     * @throws MoloniDocumentCustomerException
     */
    public function setLanguageAndCountryId(): OrderCustomer
    {
        try {
            ['countryId' => $countryId, 'languageId' => $languageId] = $this->getMoloniCountryById($this->billingAddress->id_country, $this->billingAddress->id_state);
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentCustomerException('Error fetching countries', [], $e->getData());
        }

        $this->countryId = $countryId;

        if (!empty(Settings::get('clientLanguage'))) {
            $this->languageId = (int) Settings::get('clientLanguage');
        } else {
            $this->languageId = $languageId;
        }

        return $this;
    }

    /**
     * Sets customer VAT
     *
     * @return OrderCustomer
     */
    public function setVat(): OrderCustomer
    {
        switch (true) {
            case !empty($this->billingAddress->vat_number):
                $vat = $this->billingAddress->vat_number;

                break;
            case !empty($this->billingAddress->dni):
                $vat = $this->billingAddress->dni;

                break;
            default:
                $vat = null;
                break;
        }

        $this->vat = $vat;

        if (empty($vat)) {
            return $this;
        }

        if ($this->countryId === Countries::SPAIN) {
            $vat = strtoupper($vat);

            if (stripos($vat, 'ES') === 0) {
                $vat = str_ireplace('ES', '', $vat);
            }

            $this->vat = \Moloni\Helpers\Customer::isVatEsValid($vat) ? $vat : null;
        } elseif ($this->countryId === Countries::PORTUGAL) {
            $vat = strtoupper($vat);

            if (stripos($vat, 'PT') === 0) {
                $vat = str_ireplace('PT', '', $vat);
            }

            $this->vat = \Moloni\Helpers\Customer::isVatPtValid($vat) ? $vat : null;
        }

        return $this;
    }

    /**
     * Sets customer email
     *
     * @return OrderCustomer
     */
    public function setEmail(): OrderCustomer
    {
        $this->email = $this->customer->email;

        return $this;
    }

    /**
     * Sets customer number
     *
     * @return OrderCustomer
     *
     * @throws MoloniDocumentCustomerException
     */
    public function setNumber(): OrderCustomer
    {
        try {
            $neddle = Settings::get('clientPrefix') ?? '';
            $neddle .= '%';

            $params = [
                'options' => [
                    'filter' => [
                        'field' => 'number',
                        'comparison' => 'like',
                        'value' => $neddle,
                    ],
                ],
            ];

            $query = MoloniApiClient::customers()->queryCustomerNextNumber($params);

            if (isset($query['data']['customerNextNumber']['data']) && !empty($query['data']['customerNextNumber']['data'])) {
                $number = $query['data']['customerNextNumber']['data'];
            } else {
                $number = Settings::get('clientPrefix') ?? '';
                $number .= '1';
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentCustomerException('Error fetching customer next number', [], $e->getData());
        }

        $this->number = (string) $number;

        return $this;
    }

    /**
     * Sets customer website
     *
     * @return OrderCustomer
     */
    public function setWebsite(): OrderCustomer
    {
        $this->website = $this->customer->website;

        return $this;
    }

    /**
     * Sets customer name
     *
     * @return OrderCustomer
     */
    public function setName(): OrderCustomer
    {
        $name = $this->customer->firstname;
        $name .= ' ';
        $name .= $this->customer->lastname;

        $this->name = $name;

        return $this;
    }

    /**
     * Sets customer phone
     *
     * @return OrderCustomer
     */
    public function setPhone(): OrderCustomer
    {
        switch (true) {
            case !empty($this->billingAddress->phone_mobile):
                $phone = $this->billingAddress->phone_mobile;

                break;
            case !empty($this->billingAddress->phone):
                $phone = $this->billingAddress->phone;

                break;
            default:
                $phone = '';
                break;
        }

        $this->phone = $phone;

        return $this;
    }

    /**
     * Sets customer address
     *
     * @return OrderCustomer
     */
    public function setAddress(): OrderCustomer
    {
        $address = $this->billingAddress->address1;
        $address .= ' ';
        $address .= $this->billingAddress->address2;

        if (empty(trim($address))) {
            $address = 'Desconhecido';
        }

        $this->address = $address;

        return $this;
    }

    /**
     * Sets customer city
     *
     * @return OrderCustomer
     */
    public function setCity(): OrderCustomer
    {
        $city = $this->billingAddress->city;

        if (empty($city)) {
            $city = 'Desconhecido';
        }

        $this->city = $city;

        return $this;
    }

    /**
     * Sets customer zip-code
     *
     * @return OrderCustomer
     */
    public function setZipCode(): OrderCustomer
    {
        $this->zipCode = $this->billingAddress->postcode ?? '';

        return $this;
    }

    //          REQUESTS          //

    /**
     * Search customer by VAT
     *
     * @throws MoloniDocumentCustomerException
     */
    protected function searchByVat(): void
    {
        $variables = [
            'options' => [
                'search' => [
                    'field' => 'vat',
                    'value' => $this->vat,
                ],
            ],
        ];

        try {
            $query = MoloniApiClient::customers()
                ->queryCustomers($variables);

            if (!empty($query)) {
                $this->customerId = $query[0]['customerId'];
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentCustomerException('Error fetching customer by VAT: ({0})', ['{0}' => $this->vat], $e->getData());
        }
    }

    /**
     * Search customer by email
     *
     * @throws MoloniDocumentCustomerException
     */
    protected function searchByEmail(): void
    {
        $variables = [
            'options' => [
                'search' => [
                    'field' => 'email',
                    'value' => $this->email,
                ],
            ],
        ];

        try {
            $query = MoloniApiClient::customers()
                ->queryCustomers($variables);

            if (!empty($query)) {
                $this->customerId = $query[0]['customerId'];
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentCustomerException('Error fetching customer by e-mail: ({0})', ['{0}' => $this->email], $e->getData());
        }
    }
}
