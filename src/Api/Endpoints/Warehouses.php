<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class Warehouses extends Endpoint
{
    /**
     * Get All Warehouses from Moloni ES
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryWarehouses(?array $variables = []): array
    {
        $query = 'query warehouses($companyId: Int!,$options: WarehouseOptions){
            warehouses(companyId: $companyId, options: $options) {
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
                    warehouseId
                    name
                    number
                }
            }
        }';

        return $this->paginatedPost($query, $variables, 'warehouses');
    }
}
