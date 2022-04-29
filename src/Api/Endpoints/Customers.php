<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class Customers extends Endpoint
{
    /**
     * Creates a costumer
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationCustomerCreate(?array $variables = []): array
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

        return $this->simplePost($query, $variables);
    }

    /**
     * Gets costumer information
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryCustomer(?array $variables = []): array
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

        return $this->simplePost($query, $variables);
    }

    /**
     * Gets costumers of the company
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryCustomers(?array $variables = []): array
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

        return $this->paginatedPost($query, $variables, 'customers');
    }

    /**
     * Gets custom costumers of the company
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryCustomCustomers(?array $variables = []): array
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
