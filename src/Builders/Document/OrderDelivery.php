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

use Address;
use Order;
use Moloni\Builders\Interfaces\BuilderItemInterface;
use Moloni\Exceptions\Document\MoloniDocumentDeliveryException;

class OrderDelivery implements BuilderItemInterface
{
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
    protected $orderDestination;

    public function __construct($order)
    {
        $this->order = $order;
        $this->orderDestination = new Address($order->id_address_delivery);

        $this->init();
    }

    //          PUBLICS          //

    public function toArray(): array
    {
        return [];
    }

    public function insert(): OrderDelivery
    {
        $params = [
            'name' => $this->name
        ];

        return $this;
    }

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
        return $this;
    }

    /**
     * Define shipping date
     *
     * @return OrderDelivery
     */
    protected function setDate(): OrderDelivery
    {
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
    protected function searchByName()
    {
        return $this;
    }
}
