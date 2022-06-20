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

class MeasurementUnits extends Endpoint
{
    /**
     * Gets all measurement units
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns all measurement units
     *
     * @throws MoloniApiException
     */
    public function queryMeasurementUnits(?array $variables = []): array
    {
        $query = 'query measurementUnits($companyId: Int!,$options: MeasurementUnitOptions)
                {
                    measurementUnits(companyId: $companyId,options: $options)
                    {
                        data
                        {
                            measurementUnitId
                            name
                            abbreviation
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

        return $this->paginatedPost($query, $variables, 'measurementUnits');
    }

    /**
     * Create a measurement unit
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns some data of the created measurement data
     *
     * @throws MoloniApiException
     */
    public function mutationMeasurementUnitCreate(?array $variables = []): array
    {
        $query = 'mutation measurementUnitCreate($companyId: Int!,$data: MeasurementUnitInsert!)
                {
                    measurementUnitCreate(companyId: $companyId,data: $data)
                    {
                        data
                        {
                            measurementUnitId
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
