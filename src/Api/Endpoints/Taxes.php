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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Taxes extends Endpoint
{
    /**
     * Gets all the taxes of the company
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns an array with taxes information
     *
     * @throws MoloniApiException
     */
    public function queryTaxes(?array $variables = []): array
    {
        $query = 'query taxes($companyId: Int!,$options: TaxOptions)
                {
                    taxes(companyId: $companyId,options: $options)
                    {
                        data
                        {
                            taxId
                            name
                            value
                            type
                            fiscalZone
                            country
                            {
                                countryId
                            }
                            fiscalZoneFinanceType
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

        return $this->paginatedPost($query, $variables, 'taxes');
    }

    /**
     * Gets tax info
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns an array with taxes information
     *
     * @throws MoloniApiException
     */
    public function queryTax(?array $variables = []): array
    {
        $query = 'query tax($companyId: Int!,$taxId: Int!)
                {
                    tax(companyId: $companyId,taxId: $taxId)
                    {
                        data
                        {
                            taxId
                            name
                            value
                            type
                            fiscalZone
                            country
                            {
                                countryId
                            }
                            fiscalZoneFinanceType
                            fiscalZoneFinanceTypeMode
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
     * Creates a tax
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns data about the created tax
     *
     * @throws MoloniApiException
     */
    public function mutationTaxCreate(?array $variables = []): array
    {
        $query = 'mutation taxCreate($companyId: Int!,$data: TaxInsert!)
                {
                    taxCreate(companyId: $companyId,data: $data)
                    {
                        data
                        {
                            taxId
                            name
                            value
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
