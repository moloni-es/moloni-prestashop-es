<?php

namespace Moloni\ES\Controllers\Models;

use Moloni\ES\Controllers\Api\PaymentMethods;

class Payment
{
    public $paymentMethodId;
    public $name;
    public $value = 0;

    /**
     * Payment constructor.
     *
     * @param $name string name of payment method
     */
    public function __construct($name)
    {
        $this->name = trim($name);
    }

    /**
     * Loads a payment method by name
     *
     * @return bool
     */
    public function loadByName()
    {
        $variables = [
            'companyId' => (int) Company::get('company_id'),
            'options' => [
                'search' => [
                    'field' => 'name',
                    'value' => $this->name,
                ],
            ],
        ];

        $paymentMethods = PaymentMethods::queryPaymentMethods($variables);

        if (!empty($paymentMethods)) {
            foreach ($paymentMethods as $paymentMethod) {
                if ($paymentMethod['name'] === $this->name) {
                    $this->paymentMethodId = (int) $paymentMethod['paymentMethodId'];

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Create a Payment Methods based on the name
     *
     * @return bool
     */
    public function create()
    {
        $mutation = PaymentMethods::mutationPaymentMethodCreate($this->setVariables());

        if (!empty($mutation['errors'])) {
            return false;
        }

        $mutation = $mutation['data']['paymentMethodCreate']['data'];

        if (isset($mutation['paymentMethodId'])) {
            $this->paymentMethodId = $mutation['paymentMethodId'];

            return true;
        }

        return false;
    }

    /**
     * Map this object properties to an array to insert/update a moloni Payment Value
     *
     * @return array
     */
    public function setVariables()
    {
        return [
            'companyId' => (int) Company::get('company_id'),
            'data' => [
                'name' => $this->name,
            ],
        ];
    }
}
