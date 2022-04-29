<?php

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
                            child
                            {
                                name
                                productCategoryId
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
