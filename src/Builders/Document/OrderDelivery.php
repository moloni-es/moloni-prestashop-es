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

use Order;
use Address;
use Carrier;
use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Interfaces\BuilderItemInterface;
use Moloni\Exceptions\Document\MoloniDocumentDeliveryException;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Helpers\Moloni;
use Moloni\Traits\CountryTrait;

class OrderDelivery implements BuilderItemInterface
{
    use CountryTrait;

    /**
     * Delivery method id
     *
     * @var int
     */
    public $deliveryMethodId = 0;

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
     * @var Order
     */
    protected $order;

    /**
     * Destination address
     *
     * @var Address
     */
    protected $deliveryAddress;

    /**
     * Order carrier
     *
     * @var Carrier
     */
    protected $orderCarrier;

    /**
     * Constructor
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->deliveryAddress = new Address($order->id_address_delivery);
        $this->orderCarrier = new Carrier($order->id_carrier);

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
            'delivery_departure_address' => $this->loadAddress,
            'delivery_departure_city' => $this->loadCity,
            'delivery_departure_zip_code' => $this->loadZipCode,
            'delivery_departure_country' => $this->loadCountry,
            'delivery_destination_address' => $this->destinationAddress,
            'delivery_destination_city' => $this->destinationCity,
            'delivery_destination_zip_code' => $this->destinationZipCode,
            'delivery_destination_country' => $this->destinationCountry,
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
                'companyId' => (int) Moloni::get('company_id'),
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
                throw new MoloniDocumentDeliveryException('Error creating delivery method: ({0})', [$this->name], ['params' => $params, 'response' => $mutation]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentDeliveryException('Error creating delivery method: ({0})', [$this->name], $e->getData());
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
     * Sets payment name
     *
     * @return OrderDelivery
     */
    protected function setName(): OrderDelivery
    {
        $this->name = $this->orderCarrier->name ?? 'Método de entrega';

        return $this;
    }

    /**
     * Sets load address
     *
     * @return OrderDelivery
     */
    protected function setLoadAddress(): OrderDelivery
    {
        // todo: repensar settings
        $this->loadAddress = '';
        $this->loadZipCode = '';
        $this->loadCity = '';
        $this->loadCountry = 70;

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
        $this->destinationAddress .= $this->deliveryAddress->address1;

        $this->destinationZipCode = $this->deliveryAddress->postcode;
        $this->destinationCity = $this->deliveryAddress->city;

        try {
            ['countryId' => $countryId] = $this->getMoloniCountryById($this->deliveryAddress->id_country);

            $this->destinationCountry = $countryId;
        } catch (MoloniAPIException $e) {
            throw new MoloniDocumentDeliveryException('Error fetching countries', [], $e->getData());
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
            'companyId' => (int) Moloni::get('company_id'),
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
            throw new MoloniDocumentDeliveryException('Error fetching payment methods', [], $e->getData());
        }

        return $this;
    }
}
