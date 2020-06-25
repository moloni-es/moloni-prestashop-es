<?php

namespace Moloni\ES\Controllers\Api;

class Categories extends GeneralAPI
{
    /**
     * Create an category
     *
     * @param array $variables variables of the query
     *
     * @return array returns some data of the created category
     */
    public static function mutationProductCategoryCreate($variables = [])
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

        return Connector::graphqlClient($query, json_encode($variables));
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

        return self::getApiPaginator($query, $variables, 'productCategories');
    }

    /**
     * Get the category of an product
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
