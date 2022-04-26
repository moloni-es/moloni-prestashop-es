<?php

namespace Moloni\Api\Endpoints;

use Moloni\Api\Curl;

class Companies extends Endpoint
{
    /**
     * Gets all the companies that the logged-in user has access
     *
     * @return array return and array with all companies Ids
     */
    public static function queryMe(): array
    {
        $query = 'query {
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

        return Curl::simple($query);
    }

    /**
     * Gets the information of the companies that the logged in user has access
     *
     * @param array $variables variables of the query
     *
     * @return array returns an array with the companies information
     */
    public static function queryCompany(array $variables = []): array
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
                options{
                    defaultLanguageId
                }
                errors 
                { field 
                msg 
                }
            }
        }';

        return Curl::simple($query, $variables);
    }
}
