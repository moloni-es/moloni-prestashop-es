<?php

namespace Moloni\ES\Controllers\Api;

class Warehouses extends GeneralAPI
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

        return self::getApiPaginator($query, $variables, 'warehouses');
    }
}
