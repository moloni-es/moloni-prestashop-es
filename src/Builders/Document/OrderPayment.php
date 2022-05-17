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

use OrderPayment as PrestashopOrderPayment;
use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Interfaces\BuilderItemInterface;
use Moloni\Exceptions\Document\MoloniDocumentPaymentException;
use Moloni\Exceptions\MoloniApiException;

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
     * Order payment
     *
     * @var PrestashopOrderPayment
     */
    protected $orderPayment;

    /**
     * Constructor
     *
     * @param PrestashopOrderPayment $orderPayment Order payment
     */
    public function __construct(PrestashopOrderPayment $orderPayment)
    {
        $this->orderPayment = $orderPayment;

        $this->init();
    }

    //          PUBLICS          //

    /**
     * Exports payment data to array format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'paymentMethodId' => $this->paymentMethodId,
            'paymentMethodName' => $this->name,
            'value' => $this->value,
            'date' => $this->paymentTime
        ];
    }

    /**
     * Creates a payment method in Moloni
     *
     * @throws MoloniDocumentPaymentException
     */
    public function insert(): OrderPayment
    {
        try {
            $params = [
                'data' => [
                    'name' => $this->name,
                ],
            ];

            $mutation = MoloniApiClient::paymentMethods()
                ->mutationPaymentMethodCreate($params);

            $paymentMethodId = $mutation['data']['paymentMethodCreate']['data']['paymentMethodId'] ?? 0;

            if ((int) $paymentMethodId > 0) {
                $this->paymentMethodId = (int) $paymentMethodId;
            } else {
                throw new MoloniDocumentPaymentException('Error creating payment method: ({0})', [$this->name], ['params' => $params, 'response' => $mutation]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentPaymentException('Error creating payment method: ({0})', [$this->name], $e->getData());
        }

        return $this;
    }

    /**
     * @throws MoloniDocumentPaymentException
     */
    public function search(): OrderPayment
    {
        return $this->searchByName();
    }

    //          PRIVATES          //

    /**
     * Start initial values
     *
     * @return $this
     */
    protected function init(): OrderPayment
    {
        $this
            ->setName()
            ->setValue()
            ->setPaymentTime();

        return $this;
    }

    //          SETS          //

    /**
     * Define payment date
     *
     * @return $this
     */
    protected function setPaymentTime(): OrderPayment
    {
        $this->paymentTime = $this->orderPayment->date_add;

        return $this;
    }

    /**
     * Sets payment name
     *
     * @return $this
     */
    protected function setName(): OrderPayment
    {
        $this->name = $this->orderPayment->payment_method ?? 'Método de pago';

        return $this;
    }

    /**
     * Sets payment total
     *
     * @return $this
     */
    protected function setValue(): OrderPayment
    {
        $this->value = $this->orderPayment->amount ?? 0;

        return $this;
    }

    //          REQUESTS          //

    /**
     * Search for payment method by name
     *
     * @throws MoloniDocumentPaymentException
     */
    protected function searchByName(): OrderPayment
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
            $query = MoloniApiClient::paymentMethods()
                ->queryPaymentMethods($variables);

            if (!empty($query)) {
                $this->paymentMethodId = $query[0]['paymentMethodId'];
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentPaymentException('Error fetching payment methods', [], $e->getData());
        }

        return $this;
    }
}
