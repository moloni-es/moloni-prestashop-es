<?php

namespace Moloni\Api\Endpoints;

use Moloni\Api\Curl;

class GeographicZones extends Endpoint
{
    /**
     * Gets geographic zones
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryGeographicZones(array $variables = []): array
    {
        $query = 'query geographicZones($companyId: Int!,$options: GeographicZoneOptions)
        {
            geographicZones(companyId: $companyId,options: $options)
            {
                data
                {
                    geographicZoneId
                    name
                    abbreviation
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
                errors
                {
                    field
                    msg
                }
            }
        }';

        return Curl::complex($query, $variables, 'geographicZones');
    }
}
