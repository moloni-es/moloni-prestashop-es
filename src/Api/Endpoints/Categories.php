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

class Categories extends Endpoint
{
    /**
     * Create a category
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns some data of the created category
     *
     * @throws MoloniApiException
     */
    public function mutationProductCategoryCreate(?array $variables = []): array
    {
        $query = 'mutation productCategoryCreate($companyId: Int!,$data: ProductCategoryInsert!)
        {
            productCategoryCreate(companyId: $companyId,data: $data)
            {
                data
                {
                    productCategoryId
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
     * Gets all categories
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns data of the categories
     *
     * @throws MoloniApiException
     */
    public function queryProductCategories(?array $variables = []): array
    {
        $query = 'query productCategories($companyId: Int!,$options: ProductCategoryOptions)
                {
                    productCategories(companyId: $companyId,options: $options)
                    {
                        data
                        {
                            productCategoryId
                            name
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

        return $this->paginatedPost($query, $variables, 'productCategories');
    }

    /**
     * Get the category of a product
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns category data
     *
     * @throws MoloniApiException
     */
    public function queryProductCategory(?array $variables = []): array
    {
        $query = 'query productCategory($companyId: Int!,$productCategoryId: Int!)
        {
            productCategory(companyId: $companyId,productCategoryId: $productCategoryId)
            {
                data
                {
                    name
                    posVisible
                    summary
                    visible
                    parent
                    {
                        productCategoryId
                        name
                    }
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
