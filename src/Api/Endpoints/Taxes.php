<?php

namespace Moloni\Api\Endpoints;

use Moloni\Api\Curl;

class Taxes extends Endpoint
{
    /**
     * Gets all the taxes of the company
     *
     * @param array $variables variables of the query
     *
     * @return array returns an array with taxes information
     */
    public static function queryTaxes($variables)
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

        return Curl::complex($query, $variables, 'taxes');
    }

    /**
     * Gets tax info
     *
     * @param array $variables variables of the query
     *
     * @return array returns an array with taxes information
     */
    public static function queryTax($variables)
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

        return Curl::simple($query, $variables);
    }

    /**
     * Creates an tax
     *
     * @param array $variables variables of the query
     *
     * @return array returns data about the created tax
     */
    public static function mutationTaxCreate($variables)
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

        return Curl::simple($query, $variables);
    }
}
