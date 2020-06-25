<?php

namespace Moloni\ES\Controllers\Api;

use Moloni\ES\Controllers\Models\Log;

class GeneralAPI
{
    /**
     * Returns all data from an paginated query
     *
     * @param $query string query used
     * @param $variables array variables used
     * @param $keyString string string of the data query
     *
     * @return array|bool information received
     */
    public static function getApiPaginator($query, $variables, $keyString)
    {
        //to get all items we need to paginate
        $pageNumber = 0;
        $arrayAPI = [];
        do {
            ++$pageNumber;
            $variables['options']['pagination']['qty'] = 50;
            $variables['options']['pagination']['page'] = $pageNumber;
            $queryResult = Connector::graphqlClient($query, json_encode($variables));

            if (isset($queryResult['errors'])) {
                Log::writeLog('Something went wrong with pagination of API!!');

                return false;
            }

            $querySize = $queryResult['data'][$keyString]['options']['pagination'];
            $arrayAPI = array_merge($arrayAPI, $queryResult['data'][$keyString]['data']);
        } while ($querySize['count'] > ($querySize['qty'] * $querySize['page']));

        return $arrayAPI;
    }
}
