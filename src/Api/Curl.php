<?php

namespace Moloni\Api;

use Moloni\Helpers\Error;
use Moloni\Helpers\Log;
use Moloni\Models\Moloni;

class Curl
{
    /**
     * Query the Moloni API
     *
     * @param string|array $query query to execute
     * @param string|array $variables variables of the query
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function simple($query = null, $variables = [])
    {
        $endpoint = 'https://api.moloni.es/v1';
        $authToken = Moloni::get('access_token');

        if ($authToken === false) {
            return ['errors' => ['msg' => 'There is no access token!!']];
        }

        $aux = ['query' => ($query), 'variables' => ($variables)];

        $headers = [];
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $authToken;

        $con = curl_init();

        curl_setopt($con, CURLOPT_URL, $endpoint);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($con, CURLOPT_POSTFIELDS, json_encode($aux));
        curl_setopt($con, CURLOPT_POST, 1);
        curl_setopt($con, CURLOPT_HTTPHEADER, $headers);

        $result = json_decode(curl_exec($con), true);

        // always save the last request
        Error::addRequest(
            [
                'query' => $query,
                'variables' => $variables,
                'result' => $result,
            ]
        );

        if (curl_errno($con)) {
            return ['errors' => ['msg' => curl_error($con)]];
        }
        curl_close($con);

        return $result;
    }

    /**
     * Returns all data from an paginated query
     *
     * @param $query string query used
     * @param $variables array variables used
     * @param $keyString string string of the data query
     *
     * @return array|bool information received
     */
    public static function complex($query, $variables, $keyString)
    {
        // to get all items we need to paginate
        $pageNumber = 0;
        $arrayAPI = [];
        do {
            ++$pageNumber;
            $variables['options']['pagination']['qty'] = 50;
            $variables['options']['pagination']['page'] = $pageNumber;
            $queryResult = self::simple($query, json_encode($variables));

            if (isset($queryResult['errors'])) {
                Log::writeLog('Something went wrong with pagination of API!!');

                return false;
            }

            $querySize = $queryResult['data'][$keyString]['options']['pagination'];
            $arrayAPI = array_merge($arrayAPI, $queryResult['data'][$keyString]['data']);
        } while ($querySize['count'] > ($querySize['qty'] * $querySize['page']));

        return $arrayAPI;
    }

    /**
     * Refreshes the current user tokens
     *
     * @return bool
     */
    public static function refreshTokens()
    {
        $dataArray = Moloni::getAll();
        if ($dataArray === false) {
            Error::addError('No company logged in!!');

            return false;
        }

        $url = 'https://api.moloni.es/v1/auth/grant';

        $postFields = 'grantType=refresh_token&apiClientId=' . $dataArray['client_id'] .
            '&clientSecret=' . $dataArray['client_secret'] .
            '&refreshToken=' . $dataArray['refresh_token'];

        $con = curl_init();

        curl_setopt($con, CURLOPT_URL, $url);
        curl_setopt($con, CURLOPT_POST, 1);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($con, CURLOPT_POSTFIELDS, $postFields);

        $resCurl = curl_exec($con);

        if (curl_errno($con)) {
            Error::addError(curl_error($con));

            return false;
        }
        curl_close($con);

        $res = json_decode($resCurl, true);

        // add the 'bad' request to the errors class
        if (isset($res['errors'])) {
            Error::addRequest(
                [
                    'url' => $url,
                    'post fields' => $postFields,
                    'result' => $res,
                ]
            );
        }

        return $res;
    }

    /**
     * Gets tokens after recieving the authentication code
     *
     * @param array $code Authentication code to get tokens
     *
     * @return array returns an array with tokens or an array with errors
     */
    public static function login($code)
    {
        $dataArray = Moloni::getAll();
        if ($dataArray === false) {
            Error::addError('No company logged in!!');

            return false;
        }

        $url = 'https://api.moloni.es/v1/auth/grant';

        $postFields = 'grantType=authorization_code&apiClientId=' . $dataArray['client_id'] .
            '&clientSecret=' . $dataArray['client_secret'] .
            '&code=' . $code;

        $con = curl_init();

        curl_setopt($con, CURLOPT_URL, $url);
        curl_setopt($con, CURLOPT_POST, 1);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($con, CURLOPT_POSTFIELDS, $postFields);

        $resCurl = curl_exec($con);

        if (curl_errno($con)) {
            Error::addError(curl_error($con));

            return false;
        }
        curl_close($con);

        $res = json_decode($resCurl, true);

        // add the 'bad' request to the errors class
        if (isset($res['errors'])) {
            Error::addRequest(
                [
                    'url' => $url,
                    'post fields' => $postFields,
                    'result' => $res,
                ]
            );
        }

        return $res;
    }
}
