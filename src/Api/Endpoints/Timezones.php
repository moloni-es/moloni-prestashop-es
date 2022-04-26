<?php

namespace Moloni\Api\Endpoints;

use Moloni\Api\Curl;

class Timezones extends Endpoint
{
    /**
     * Get All Timezones from Moloni ES
     *
     * @param array $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryTimezones(array $variables = []): array
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

        return Curl::complex($query, $variables, 'timezones');
    }
}
