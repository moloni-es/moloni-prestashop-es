<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class PaymentMethods extends Endpoint
{
    /**
     * Get payment methods info
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryPaymentMethod(?array $variables = []): array
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

        return $this->simplePost($query, $variables);
    }

    /**
     * Get All Payment Methods from Moloni ES
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryPaymentMethods(?array $variables = []): array
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

        return $this->paginatedPost($query, $variables, 'paymentMethods');
    }

    /**
     * Creates an payment method
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function mutationPaymentMethodCreate(?array $variables = []): array
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

        return $this->simplePost($query, $variables);
    }
}
