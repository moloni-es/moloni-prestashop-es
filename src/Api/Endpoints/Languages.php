<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class Languages extends Endpoint
{
    /**
     * Gets languages.
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryLanguage(?array $variables = []): array
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

        return $this->simplePost($query, $variables);
    }

    /**
     * Gets language info
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryLanguages(?array $variables = []): array
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

        return $this->paginatedPost($query, $variables, 'languages');
    }
}
