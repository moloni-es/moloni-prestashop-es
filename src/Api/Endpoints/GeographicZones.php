<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class GeographicZones extends Endpoint
{
    /**
     * Gets geographic zones
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryGeographicZones(?array $variables = []): array
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

        return $this->paginatedPost($query, $variables, 'geographicZones');
    }
}
