<?php

namespace Moloni\Api\Endpoints;

use Moloni\Api\Curl;

class DeliveryMethods extends Endpoint
{
    /**
     * Create a new delivery methods
     *
     * @param array $variables Request variables
     *
     * @return array
     */
    public static function mutationDeliveryMethodCreate(array $variables = []): array
    {
        $query = 'mutation deliveryMethodCreate($companyId: Int!,$data: DeliveryMethodInsert!)
        {
            deliveryMethodCreate(companyId: $companyId,data: $data) 
            {
                errors
                {
                    field
                    msg
                }
                data
                {
                    deliveryMethodId
                    name
                }
            }
        }';

        return Curl::simple($query, $variables);
    }

    /**
     * Get All DeliveryMethods from Moloni ES
     *
     * @param array $variables Request variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryDeliveryMethods(array $variables = []): array
    {
        $query = 'query deliveryMethods($companyId: Int!,$options: DeliveryMethodOptions)
        {
            deliveryMethods(companyId: $companyId,options: $options) 
            {
                errors
                {
                    field
                    msg
                }
                data
                {
                    deliveryMethodId
                    name
                    isDefault
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
            }
        }';

        return Curl::complex($query, $variables, 'deliveryMethods');
    }
}
