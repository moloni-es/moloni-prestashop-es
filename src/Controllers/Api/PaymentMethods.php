<?php

namespace Moloni\ES\Controllers\Api;

class PaymentMethods extends GeneralAPI
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

        return Connector::graphqlClient($query, $variables);
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

        return self::getApiPaginator($query, $variables, 'paymentMethods');
    }
}
