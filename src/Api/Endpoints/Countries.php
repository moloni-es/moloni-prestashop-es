<?php

namespace Moloni\Api\Endpoints;

use Moloni\Api\Curl;

class Countries extends Endpoint
{
    /**
     * Gets all countries
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryCountries(array $variables = []): array
    {
        $query = 'query countries($options: CountryOptions)
        {
            countries(options: $options)
            {
                data
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

        return Curl::simple($query, $variables);
    }

    /**
     * Gets country info
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryCountry(array $variables = []): array
    {
        $query = 'query country($countryId: Int!)
        {
            country(countryId: $countryId)
            {
                data
                {
                    countryId
                    iso3166_1
                    language
                    {
                        languageId
                    }
                }
                errors
                {
                    field
                    msg
                }
            }
        }';

        return Curl::simple($query, $variables);
    }
}
