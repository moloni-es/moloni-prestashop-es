<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class Timezones extends Endpoint
{
    /**
     * Get All Timezones from Moloni ES
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryTimezones(?array $variables = []): array
    {
        $query = 'query timezones($options: TimezoneOptions)
        {
            timezones(options: $options) 
            {
                errors
                {
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
                    timezoneId
                    name
                    visible
                    ordering
                    tzName
                    offset
                    country
                    {
                           countryId
                           iso3166_1
                           title
                           language
                           {
                                    languageId
                                    name
                           } 
                    }
                }
            }
        }';

        return $this->paginatedPost($query, $variables, 'timezones');
    }
}
