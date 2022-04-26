<?php

namespace Moloni\Api;

class PaymentMethods
{
    /**
     * Get payment methods info
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryPaymentMethod($variables)
    {
        $query = 'query paymentMethod($companyId: Int!,$paymentMethodId: Int!){
                paymentMethod(companyId: $companyId,paymentMethodId: $paymentMethodId) {
                    errors{
                        field
                        msg
                    }
                    data{
                        paymentMethodId
                        name
                        type
                        commission
                    }
                }
            }';

        return Curl::simple($query, $variables);
    }

    /**
     * Get All Payment Methods from Moloni ES
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryPaymentMethods($variables)
    {
        $query = 'query paymentMethods($companyId: Int!,$options: PaymentMethodOptions){
            paymentMethods(companyId: $companyId, options: $options) {
                errors{
                    field
                    msg
                }
                options
                {
                    pagination
                    {
                        page
                        qty
                        count
                    }
                }
                data{
                    paymentMethodId
                    name
                    type
                    commission
                }
            }
        }';

        return Curl::complex($query, $variables, 'paymentMethods');
    }

    /**
     * Creates an payment method
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function mutationPaymentMethodCreate($variables)
    {
        $query = 'mutation paymentMethodCreate($companyId: Int!,$data: PaymentMethodInsert!)
        {
            paymentMethodCreate(companyId: $companyId,data: $data)
            {
                data
                {
                    paymentMethodId
                    name
                }
                errors
                {
                    field
                    msg
                }
            }
        }';

        return Curl::simple($query, $variables);
    }
}
