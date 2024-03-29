<?php
/**
 * 2022 - Moloni.com
 *
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Moloni
 * @copyright Moloni
 * @license   https://creativecommons.org/licenses/by-nd/4.0/
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
     * Creates a costumer
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationCustomerUpdate(?array $variables = []): array
    {
        $query = 'mutation customerUpdate($companyId: Int!,$data: CustomerUpdate!)
        {
            customerUpdate(companyId: $companyId,data: $data)
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
     * Gets the next number available for customers
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryCustomerNextNumber(?array $variables = []): array
    {
        $query = 'query customerNextNumber($companyId: Int!, $options: GetNextCustomerNumberOptions)
        {
            customerNextNumber(companyId: $companyId, options: $options)
            {
                data
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
