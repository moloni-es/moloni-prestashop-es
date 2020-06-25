<?php

namespace Moloni\ES\Controllers\Api;

class MeasurementUnits extends GeneralAPI
{
    /**
     * Gets all measurement units
     *
     * @param array $variables variables of the query
     *
     * @return array returns all measurement units
     */
    public static function queryMeasurementUnits($variables = [])
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

        return self::getApiPaginator($query, $variables, 'measurementUnits');
    }

    /**
     * Create an measurement unit
     *
     * @param array $variables variables of the query
     *
     * @return array returns some data of the created measurement data
     */
    public static function mutationMeasurementUnitCreate($variables = [])
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

        return Connector::graphqlClient($query, json_encode($variables));
    }
}
