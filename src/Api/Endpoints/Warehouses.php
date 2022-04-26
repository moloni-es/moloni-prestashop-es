<?php

namespace Moloni\Api\Endpoints;

use Moloni\Api\Curl;

class Warehouses extends Endpoint
{
    /**
     * Get All Warehouses from Moloni ES
     *
     * @param array $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryWarehouses(array $variables = []): array
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

        return Curl::complex($query, $variables, 'warehouses');
    }
}
