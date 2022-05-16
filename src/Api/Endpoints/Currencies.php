<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class Currencies extends Endpoint
{
    /**
     * Get All Currencies from Moloni ES
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryCurrencies(?array $variables = []): array
    {
        $query = 'query currencies($options: CurrencyOptions)
        {
            currencies(options: $options) 
            {
                errors
                {
                    field
                    msg
                }
                data
                {
                    currencyId
                    symbol
                    symbolPosition
                    numberDecimalPlaces
                    iso4217
                    largeCurrency
                    description
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
            }
        }';

        return $this->paginatedPost($query, $variables, 'currencies');
    }

    /**
     * Get All Currencies exchanges from Moloni ES
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryCurrencyExchanges(?array $variables = []): array
    {
        $query = 'query currencyExchanges($options: CurrencyExchangeOptions)
        {
            currencyExchanges(options: $options)
            {
                data
                {
                    currencyExchangeId
                    name
                    exchange
                    from
                    {
                        currencyId
                        iso4217
                    }
                    to
                    {
                        currencyId
                        iso4217
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

        return $this->paginatedPost($query, $variables, 'currencyExchanges');
    }
}
