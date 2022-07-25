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

class Stock extends Endpoint
{
    /**
     * Adds stock to a product
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns info about the movement
     *
     * @throws MoloniApiException
     */
    public function mutationStockMovementManualEntryCreate(?array $variables = []): array
    {
        $query = 'mutation stockMovementManualEntryCreate($companyId: Int!,$data: StockMovementManualInsert!)
                {
                    stockMovementManualEntryCreate(companyId: $companyId,data: $data)
                    {
                        data{
                            stockMovementId
                            type
                            direction
                            qty
                        }
                        errors{
                            field
                            msg
                        }
                    }
                }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Removes stock from a product
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns info about the movement
     *
     * @throws MoloniApiException
     */
    public function mutationStockMovementManualExitCreate(?array $variables = []): array
    {
        $query = 'mutation stockMovementManualExitCreate($companyId: Int!,$data: StockMovementManualInsert!)
                {
                    stockMovementManualExitCreate(companyId: $companyId,data: $data)
                    {
                        data{
                            stockMovementId
                            type
                            direction
                            qty
                        }
                        errors{
                            field
                            msg
                        }
                    }
                }';

        return $this->simplePost($query, $variables);
    }
}
