<?php

namespace Moloni\ES\Controllers\Api;

class MaturityDates extends GeneralAPI
{
    /**
     * Get All Maturity Dates from Moloni ES
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryMaturityDates($variables)
    {
        $query = 'query maturityDates($companyId: Int!,$options: MaturityDateOptions){
            maturityDates(companyId: $companyId, options: $options) {
                errors{
                    field
                    msg
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
                data{
                    maturityDateId
                    name
                    days
                    discount
                }
            }
        }';

        return self::getApiPaginator($query, $variables, 'maturityDates');
    }
}
