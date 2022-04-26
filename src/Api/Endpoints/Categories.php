<?php

namespace Moloni\Api\Endpoints;

use Moloni\Api\Curl;

class Categories extends Endpoint
{
    /**
     * Create an category
     *
     * @param array $variables variables of the query
     *
     * @return array returns some data of the created category
     */
    public static function mutationProductCategoryCreate(array $variables = []): array
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

        return Curl::simple($query, json_encode($variables));
    }

    /**
     * Gets all categories
     *
     * @param array $variables variables of the query
     *
     * @return array returns data of the categories
     */
    public static function queryProductCategories($variables = [])
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

        return Curl::complex($query, $variables, 'productCategories');
    }

    /**
     * Get the category of a product
     *
     * @param array $variables variables of the query
     *
     * @return array returns category data
     */
    public static function queryProductCategory($variables = [])
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

        return Curl::simple($query, json_encode($variables));
    }
}
