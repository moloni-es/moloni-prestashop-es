<?php

namespace Moloni\ES\Controllers\Api;

class Stock
{
    /**
     * Adds stock to an product
     *
     * @param array $variables variables of the query
     *
     * @return array returns info about the movement
     */
    public static function mutationStockMovementManualEntryCreate($variables = [])
    {
        $query = 'mutation stockMovementManualEntryCreate($companyId: Int!,$data: StockMovementManualInsert!)
                {
                    stockMovementManualEntryCreate(companyId: $companyId,data: $data)
                    {
                        data{
                            stockMovementId
                            type
                            direction
                            qty
                        }
                        errors{
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, json_encode($variables));
    }

    /**
     * Removes stock from an product
     *
     * @param array $variables variables of the query
     *
     * @return array returns info about the movement
     */
    public static function mutationStockMovementManualExitCreate($variables = [])
    {
        $query = 'mutation stockMovementManualExitCreate($companyId: Int!,$data: StockMovementManualInsert!)
                {
                    stockMovementManualExitCreate(companyId: $companyId,data: $data)
                    {
                        data{
                            stockMovementId
                            type
                            direction
                            qty
                        }
                        errors{
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, json_encode($variables));
    }
}
