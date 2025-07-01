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
use Carrier;
use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Interfaces\BuilderItemInterface;
use Moloni\Enums\LoadAddress;
use Moloni\Exceptions\Document\MoloniDocumentDeliveryException;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Tools\Settings;
use Moloni\Traits\CountryTrait;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderDelivery implements BuilderItemInterface
{
    use CountryTrait;

    /**
     * Delivery method id
     *
     * @var int
     */
    protected $deliveryMethodId = 0;

    /**
     * Delivery method name
     *
     * @var string
     */
    protected $name;

    /**
     * Load address
     *
     * @var string
     */
    protected $loadAddress;

    /**
     * Load zip-code
     *
     * @var string
     */
    protected $loadZipCode;

    /**
     * Load city
     *
     * @var string
     */
    protected $loadCity;

    /**
     * Load country
     *
     * @var int
     */
    protected $loadCountry;

    /**
     * Destination address
     *
     * @var string
     */
    protected $destinationAddress;

    /**
     * Destination address
     *
     * @var string
     */
    protected $destinationZipCode;

    /**
     * Destination address
     *
     * @var string
     */
    protected $destinationCity;

    /**
     * Destination address
     *
     * @var int
     */
    protected $destinationCountry;

    /**
     * Delivery date
     *
     * @var string
     */
    protected $date;

    /**
     * Shopify order data
     *
     * @var \Order
     */
    protected $order;

    /**
     * Destination address
     *
     * @var \Address
     */
    protected $deliveryAddress;

    /**
     * Order carrier
     *
     * @var \Carrier
     */
    protected $orderCarrier;

    /**
     * Moloni company
     *
     * @var array
     */
    protected $company;

    /**
     * Constructor
     *
     * @throws MoloniDocumentDeliveryException
     */
    public function __construct(\Order $order, array $company)
    {
        $this->order = $order;
        $this->company = $company;

        $this->deliveryAddress = new \Address($order->id_address_delivery);
        $this->orderCarrier = new \Carrier($order->id_carrier);

        $this->init();
    }

    //          PUBLICS          //

    /**
     * Exports delivery data to array format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'deliveryMethodId' => $this->deliveryMethodId,
            'deliveryLoadDate' => $this->date,
            'deliveryLoadAddress' => $this->loadAddress,
            'deliveryLoadCity' => $this->loadCity,
            'deliveryLoadZipCode' => $this->loadZipCode,
            'deliveryLoadCountryId' => $this->loadCountry,
            'deliveryUnloadAddress' => $this->destinationAddress,
            'deliveryUnloadCity' => $this->destinationCity,
            'deliveryUnloadZipCode' => $this->destinationZipCode,
            'deliveryUnloadCountryId' => $this->destinationCountry,
        ];
    }

    /**
     * Creates a payment method in Moloni
     *
     * @return $this
     *
     * @throws MoloniDocumentDeliveryException
     */
    public function insert(): OrderDelivery
    {
        try {
            $params = [
                'data' => [
                    'name' => $this->name,
                    'isDefault' => false,
                ],
            ];

            $mutation = MoloniApiClient::deliveryMethods()
                ->mutationDeliveryMethodCreate($params);

            $deliveryMethodId = $mutation['data']['deliveryMethodCreate']['data']['deliveryMethodId'] ?? 0;

            if ((int) $deliveryMethodId > 0) {
                $this->deliveryMethodId = (int) $deliveryMethodId;
            } else {
                throw new MoloniDocumentDeliveryException('Error creating delivery method: ({0})', ['{0}' => $this->name], ['params' => $params, 'response' => $mutation]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentDeliveryException('Error creating delivery method: ({0})', ['{0}' => $this->name], $e->getData());
        }

        return $this;
    }

    /**
     * Searches payment in Moloni
     *
     * @throws MoloniDocumentDeliveryException
     */
    public function search(): OrderDelivery
    {
        return $this->searchByName();
    }

    //          PRIVATES          //

    /**
     * Start initial values
     *
     * @return OrderDelivery
     *
     * @throws MoloniDocumentDeliveryException
     */
    protected function init(): OrderDelivery
    {
        $this
            ->setName()
            ->setLoadAddress()
            ->setDeliveryAddress()
            ->setDate();

        return $this;
    }

    //          SETS          //

    /**
     * Delivery method ID getter
     *
     * @return int
     */
    public function getDeliveryMethodId(): int
    {
        return $this->deliveryMethodId;
    }

    //          SETS          //

    /**
     * Sets payment name
     *
     * @return OrderDelivery
     */
    protected function setName(): OrderDelivery
    {
        $this->name = $this->orderCarrier->name ?? 'Envío';

        return $this;
    }

    /**
     * Sets load address
     *
     * @return OrderDelivery
     *
     * @throws MoloniDocumentDeliveryException
     */
    protected function setLoadAddress(): OrderDelivery
    {
        $loadAddressSetting = (int) (Settings::get('loadAddress') ?? LoadAddress::MOLONI);

        if ($loadAddressSetting === LoadAddress::CUSTOM) {
            $this->loadAddress = Settings::get('customloadAddressAddress');
            $this->loadZipCode = Settings::get('customloadAddressZipCode');
            $this->loadCity = Settings::get('customloadAddressCity');
            $this->loadCountry = (int) Settings::get('customloadAddressCountry');
        } elseif ($loadAddressSetting === LoadAddress::MOLONI) {
            $this->loadAddress = $this->company['address'];
            $this->loadZipCode = $this->company['zipCode'];
            $this->loadCity = $this->company['city'];
            $this->loadCountry = $this->company['country']['countryId'];
        } elseif ($loadAddressSetting > 0) {
            $store = new \Store($loadAddressSetting);

            try {
                ['countryId' => $countryId] = $this->getMoloniCountryById($store->id_country, $store->id_state);
            } catch (MoloniApiException $e) {
                throw new MoloniDocumentDeliveryException('Error getting load country', [], $e->getData());
            }

            $this->loadAddress = $store->address1;
            $this->loadAddress .= ' ';
            $this->loadAddress .= $store->address2;
            $this->loadZipCode = $store->postcode;
            $this->loadCity = $store->city;
            $this->loadCountry = $countryId;
        }

        return $this;
    }

    /**
     * Sets delivery address
     *
     * @return OrderDelivery
     *
     * @throws MoloniDocumentDeliveryException
     */
    protected function setDeliveryAddress(): OrderDelivery
    {
        $this->destinationAddress = $this->deliveryAddress->address1;
        $this->destinationAddress .= ' ';
        $this->destinationAddress .= $this->deliveryAddress->address2;

        $this->destinationZipCode = $this->deliveryAddress->postcode;
        $this->destinationCity = $this->deliveryAddress->city;

        try {
            ['countryId' => $countryId] = $this->getMoloniCountryById($this->deliveryAddress->id_country, $this->deliveryAddress->id_state);

            $this->destinationCountry = $countryId;
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentDeliveryException('Error getting delivery country', [], $e->getData());
        }

        return $this;
    }

    /**
     * Define shipping date
     *
     * @return OrderDelivery
     */
    protected function setDate(): OrderDelivery
    {
        $this->date = date('Y-m-d H:i:s');

        return $this;
    }

    //          REQUESTS          //

    /**
     * Search for delivery method by name
     *
     * @return OrderDelivery
     *
     * @throws MoloniDocumentDeliveryException
     */
    protected function searchByName(): OrderDelivery
    {
        $variables = [
            'options' => [
                'search' => [
                    'field' => 'name',
                    'value' => $this->name,
                ],
            ],
        ];

        try {
            $query = MoloniApiClient::deliveryMethods()
                ->queryDeliveryMethods($variables);

            if (!empty($query)) {
                $this->deliveryMethodId = $query[0]['deliveryMethodId'];
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentDeliveryException('Error fetching delivery methods', [], $e->getData());
        }

        return $this;
    }
}
