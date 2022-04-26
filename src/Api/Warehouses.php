<?php

namespace Moloni\Api;

class Warehouses
{
    /**
     * Get All Warehouses from Moloni ES
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryWarehouses($variables)
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
