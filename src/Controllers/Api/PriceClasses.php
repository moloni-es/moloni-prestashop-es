<?php

namespace Moloni\ES\Controllers\Api;

class PriceClasses
{
    /**
     * Get a price class
     *
     * @param $variables
     *
     * @return array|array[]|\string[][]
     */
    public static function queryPriceClass($variables)
    {
        $query = 'query priceClass($companyId: Int!,$priceClassId: Int!)
        {
            priceClass(companyId: $companyId,priceClassId: $priceClassId)
            {
                data
                {
                    priceClassId
                    visible
                    name
                }
                errors
                {
                    field
                    msg
                }
            }
        }';

        return Curl::simple($query, $variables);
    }

    /**
     * Get all price classes
     *
     * @param $variables
     *
     * @return array|array[]|\string[][]
     */
    public static function queryPriceClasses($variables)
    {
        $query = 'query priceClasses($companyId: Int!,$options: PriceClassOptions)
        {
            priceClasses(companyId: $companyId,options: $options)
            {
                data
                {
                    priceClassId
                    visible
                    name
                }
                errors
                {
                    field
                    msg
                }
            }
        }';

        return Curl::complex($query, $variables, 'priceClasses');
    }

    /**
     * Create a price class
     *
     * @param $variables
     *
     * @return array|array[]|\string[][]
     */
    public static function mutationPriceClassCreate($variables)
    {
        $query = 'mutation priceClassCreate($companyId: Int!,$data: PriceClassInsert!)
        {
            priceClassCreate(companyId: $companyId,data: $data)
            {
                data
                {
                    priceClassId
                    visible
                    name
                }
                errors
                {
                    field
                    msg
                }
            }
        }';

        return Curl::simple($query, $variables);
    }
}
