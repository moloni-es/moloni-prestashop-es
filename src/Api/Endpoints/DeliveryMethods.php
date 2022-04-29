<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class DeliveryMethods extends Endpoint
{
    /**
     * Create a new delivery methods
     *
     * @param array|null $variables Request variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationDeliveryMethodCreate(?array $variables = []): array
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

        return $this->simplePost($query, $variables);
    }

    /**
     * Get All DeliveryMethods from Moloni ES
     *
     * @param array|null $variables Request variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryDeliveryMethods(?array $variables = []): array
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

        return $this->paginatedPost($query, $variables, 'deliveryMethods');
    }
}
