<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class Stock extends Endpoint
{
    /**
     * Adds stock to a product
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns info about the movement
     *
     * @throws MoloniApiException
     */
    public function mutationStockMovementManualEntryCreate(?array $variables = []): array
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

        return $this->simplePost($query, $variables);
    }

    /**
     * Removes stock from a product
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns info about the movement
     *
     * @throws MoloniApiException
     */
    public function mutationStockMovementManualExitCreate(?array $variables = []): array
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

        return $this->simplePost($query, $variables);
    }
}
