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

class Companies extends Endpoint
{
    /**
     * Gets all the companies that the logged-in user has access
     *
     * @return array return and array with all companies Ids
     *
     * @throws MoloniApiException
     */
    public function queryMe(): array
    {
        $query = 'query {
            me { 
                data {
                    language
                    {
                        languageId
                        name
                    }
                    userCompanies 
                    {
                        company 
                        { 
                            companyId 
                        } 
                    }
                } 
                errors 
                { field 
                msg 
                }
            }
        }';

        return $this->simplePost($query, []);
    }

    /**
     * Gets the information of the companies that the logged in user has access
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns an array with the companies information
     *
     * @throws MoloniApiException
     */
    public function queryCompany(?array $variables = []): array
    {
        $query = 'query company($companyId: Int!,$options: CompanyOptionsSingle){ 
            company(companyId: $companyId,options: $options) { 
                data { 
                    companyId
                    name
                    email
                    address
                    city
                    zipCode
                    slug
                    img1
                    vat
                    documentCalculationsMode
                    currency
                    {
                        currencyId
                        iso4217
                    }
                    fiscalZone
                    {
                        fiscalZone
                        fiscalZoneFinanceTypes
                        {
                            id
                            name
                        }
                    }
                    country
                    {
                        countryId
                        iso3166_1
                        title
                    }
                }
                options{
                    defaultLanguageId
                }
                errors 
                { field 
                msg 
                }
            }
        }';

        return $this->simplePost($query, $variables)['data']['company']['data'] ?? [];
    }
}
