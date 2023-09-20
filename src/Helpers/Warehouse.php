<?php

namespace Moloni\Helpers;

use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;

class Warehouse
{
    public static function getCompanyDefaultWarehouse(): int
    {
        $params = [
            'options' => [
                'filter' => [
                    'field' => 'isDefault',
                    'comparison' => 'eq',
                    'value' => '1',
                ],
            ],
        ];

        try {
            $query = MoloniApiClient::warehouses()->queryWarehouses($params);

            if (!empty($query)) {
                return (int)$query[0]['warehouseId'];
            }
        } catch (MoloniApiException $e) {}

        return 0;
    }
}
