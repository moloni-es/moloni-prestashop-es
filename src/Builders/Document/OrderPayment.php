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

use Moloni\Builders\Interfaces\BuilderItemInterface;

class OrderPayment implements BuilderItemInterface
{

    /**
     * Payment method id
     *
     * @var int
     */
    public $paymentMethodId = 0;

    /**
     * Payment method name
     *
     * @var string
     */
    protected $name;

    /**
     * Order payment total
     *
     * @var float
     */
    protected $value;

    /**
     * Order payment time
     *
     * @var string
     */
    protected $paymentTime;

    /**
     * Payment method notes
     *
     * @var string
     */
    protected $notes;

    /**
     * Order payment
     *
     * @var array
     */
    protected $orderPayment;

    /**
     * Constructor
     *
     * @param array $order Shopify order data
     */
    public function __construct(array $orderPayment)
    {
        $this->orderPayment = $orderPayment;

        $this->init();
    }

    //          PUBLICS          //

    public function toArray(): array
    {
        return [];
    }

    public function insert(): OrderPayment
    {
        return $this;
    }

    public function search(): OrderPayment
    {
        return $this->searchByName();
    }

    //          PRIVATES          //

    protected function init(): OrderPayment
    {
        $this
            ->setName()
            ->setValue()
            ->setPaymentTime()
            ->setNotes();

        return $this;
    }

    //          SETS          //

    protected function setPaymentTime(): OrderPayment
    {
        return $this;
    }

    protected function setName(): OrderPayment
    {
        return $this;
    }

    protected function setNotes(): OrderPayment
    {
        return $this;
    }

    protected function setValue(): OrderPayment
    {
        return $this;
    }

    //          REQUESTS          //

    protected function searchByName(): OrderPayment
    {
        return $this;
    }
}
