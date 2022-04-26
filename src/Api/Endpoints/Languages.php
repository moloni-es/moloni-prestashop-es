<?php

namespace Moloni\Api\Endpoints;

use Moloni\Api\Curl;

class Languages extends Endpoint
{
    /**
     * Gets languages.
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryLanguage(array $variables = []): array
    {
        $query = 'query language($languageId: Int!)
        {
            language(languageId: $languageId)
            {
                data
                {
                    languageId
                    name
                    iso3166
                    flag
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
     * Gets language info
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryLanguages(array $variables = []): array
    {
        $query = 'query languages($options: LanguageOptions)
        {
            languages(options: $options)
            {
                data
                {
                    languageId
                    name
                    iso3166
                    flag
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

        return Curl::complex($query, $variables, 'languages');
    }
}
