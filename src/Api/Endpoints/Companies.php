<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class Companies extends Endpoint
{
    /**
     * Gets all the companies that the logged-in user has access
     *
     * @return array return and array with all companies Ids
     *
     * @throws MoloniApiException
     */
    public function queryMe(): array
    {
        $query = 'query {
            me { 
                data {
                    language
                    {
                        languageId
                        name
                    }
                    userCompanies 
                    {
                        company 
                        { 
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

        return $this->simplePost($query, []);
    }

    /**
     * Gets the information of the companies that the logged in user has access
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns an array with the companies information
     *
     * @throws MoloniApiException
     */
    public function queryCompany(?array $variables = []): array
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
                    img1
                    vat
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
                        iso3166_1
                        title
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

        return $this->simplePost($query, $variables);
    }
}
