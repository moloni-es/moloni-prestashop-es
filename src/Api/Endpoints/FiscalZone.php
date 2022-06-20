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

class FiscalZone extends Endpoint
{
    /**
     * Get settings for a fiscal zone
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryFiscalZoneTaxSettings(?array $variables = []): array
    {
        $query = 'query fiscalZoneTaxSettings($companyId: Int!,$fiscalZone: String!)
        {
            fiscalZoneTaxSettings(companyId: $companyId,fiscalZone: $fiscalZone)
            {
                fiscalZone
                fiscalZoneModes
                {
                    typeId
                    name
                    visible
                    type
                    values
                    {
                        code
                        name
                    }
                }
                fiscalZoneFinanceTypes
                {
                    id
                    name
                    code
                    isVAT
                }
            }
        }';

        return $this->simplePost($query, $variables);
    }
}
