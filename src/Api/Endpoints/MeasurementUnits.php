<?php

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
