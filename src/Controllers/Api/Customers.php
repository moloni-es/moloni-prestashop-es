<?php

namespace Moloni\ES\Controllers\Api;

class Customers extends GeneralAPI
{
    /**
     * Creates an costumer
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function mutationCustomerCreate($variables = [])
    {
        $query = 'mutation customerCreate($companyId: Int!,$data: CustomerInsert!)
                {
                    customerCreate(companyId: $companyId,data: $data)
                    {
                        data
                        {
                            customerId
                            name
                            vat
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

    /**
     * Gets costumer information
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryCustomer($variables = [])
    {
        $query = 'query customer($companyId: Int!,$customerId: Int!,$options: CustomerOptionsSingle)
                {
                    customer(companyId: $companyId,customerId: $customerId,options: $options)
                    {
                        data
                        {
                            customerId
                            name
                            discount
                            documentSet
                            {
                                documentSetId
                                name
                            }
                            vat
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

    /**
     * Gets costumers of the company
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryCustomers($variables = [])
    {
        $query = 'query customers($companyId: Int!,$options: CustomerOptions)
                {
                    customers(companyId: $companyId,options: $options)
                    {
                        data
                        {
                            customerId
                            name
                            number
                            discount
                            documentSet
                            {
                                documentSetId
                                name
                            }
                            country
                            {
                                countryId
                            }
                            language
                            {
                                languageId
                            }
                            vat
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

        return self::getApiPaginator($query, $variables, 'customers');
    }

    /**
     * Gets geographic zones
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryGeographicZones($variables = [])
    {
        $query = 'query geographicZones($companyId: Int!,$options: GeographicZoneOptions)
                {
                    geographicZones(companyId: $companyId,options: $options)
                    {
                        data
                        {
                            geographicZoneId
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

        return self::getApiPaginator($query, $variables, 'geographicZones');
    }
}
