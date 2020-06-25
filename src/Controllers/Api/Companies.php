<?php

namespace Moloni\ES\Controllers\Api;

class Companies extends GeneralAPI
{
    /**
     * Gets all the companies that the logged in user has access
     *
     * @return array return and array with all companies Ids
     */
    public static function queryMe()
    {
        $query = 'query{
            me { 
                data { 
                    userCompanies { 
                        company { 
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

        return Connector::graphqlClient($query);
    }

    /**
     * Gets the information of the companies that the logged in user has access
     *
     * @param array $variables variables of the query
     *
     * @return array returns an array with the companies information
     */
    public static function queryCompany($variables)
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

        return Connector::graphqlClient($query, $variables);
    }

    /**
     * Gets information about fiscal zone and country
     *
     * @param array $variables variables of the query
     *
     * @return array returns data about company
     */
    public static function queryCompany2($variables)
    {
        $query = 'query company($companyId: Int!,$options: CompanyOptionsSingle)
                {
                    company(companyId: $companyId,options: $options)
                    {
                        data
                        {
                            companyId
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
                            }
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Connector::graphqlClient($query, $variables);
    }

    /**
     * Gets languages.
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryLanguage($variables = [])
    {
        $query = 'query language($languageId: Int!)
                {
                    language(languageId: $languageId)
                    {
                        data
                        {
                            languageId
                            name
                            iso3166
                            flag
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
     * Gets language info
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryLanguages($variables = [])
    {
        $query = 'query languages($options: LanguageOptions)
                {
                    languages(options: $options)
                    {
                        data
                        {
                            languageId
                            name
                            iso3166
                            flag
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

        return self::getApiPaginator($query, $variables, 'languages');
    }

    /**
     * Gets all countries
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryCountries($variables = [])
    {
        $query = 'query countries($options: CountryOptions)
                {
                    countries(options: $options)
                    {
                        data
                        {
                            countryId
                            iso3166_1
                            language
                            {
                                languageId
                            }
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

        return self::getApiPaginator($query, $variables, 'countries');
    }

    /**
     * Gets country info
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryCountry($variables = [])
    {
        $query = 'query country($countryId: Int!)
                {
                    country(countryId: $countryId)
                    {
                        data
                        {
                            countryId
                            iso3166_1
                            language
                            {
                                languageId
                            }
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
