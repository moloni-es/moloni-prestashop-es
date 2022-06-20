<?php
/**
 * 2022 - Moloni.com
 *
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Moloni
 * @copyright Moloni
 * @license   https://creativecommons.org/licenses/by-nd/4.0/
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

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
