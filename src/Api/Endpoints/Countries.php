<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class Countries extends Endpoint
{
    /**
     * Gets all countries
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryCountries(?array $variables = []): array
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

        return $this->simplePost($query, $variables);
    }

    /**
     * Gets country info
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryCountry(?array $variables = []): array
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

        return $this->simplePost($query, $variables);
    }
}
