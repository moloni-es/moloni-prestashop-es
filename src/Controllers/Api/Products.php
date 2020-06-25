<?php

namespace Moloni\ES\Controllers\Api;

class Products extends GeneralAPI
{
    /**
     * Create a new product
     *
     * @param array $variables variables of the query
     *
     * @return array returns some data of the created product
     */
    public static function mutationProductCreate($variables = [])
    {
        $query = 'mutation productCreate($companyId: Int!,$data: ProductInsert!)
                {
                    productCreate(companyId: $companyId,data: $data) 
                    {
                        data{
                            productId
                            name
                        }
                        errors{
                            field
                            msg
                        }
                    }
                }';

        return Connector::graphqlClient($query, json_encode($variables));
    }

    /**
     * Update a product
     *
     * @param array $variables variables of the query
     *
     * @return array returns some data of the updated product
     */
    public static function mutationProductUpdate($variables = [])
    {
        $query = 'mutation productUpdate($companyId: Int!,$data: ProductUpdate!)
                {
                    productUpdate(companyId: $companyId ,data: $data)
                    {
                        data
                        {
                            productId
                            name
                            reference
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
     * Create an measurement unit
     *
     * @param array $variables variables of the query
     *
     * @return array returns some data of the created measurement data
     */
    public static function mutationMeasurementUnitCreate($variables = [])
    {
        $query = 'mutation measurementUnitCreate($companyId: Int!,$data: MeasurementUnitInsert!)
                {
                    measurementUnitCreate(companyId: $companyId,data: $data)
                    {
                        data
                        {
                            measurementUnitId
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

    /**
     * Gets the information of a product
     *
     * @param array $variables variables of the query
     *
     * @return array information of the product
     */
    public static function queryProduct($variables = [])
    {
        $query = 'query product($companyId: Int!,$productId: Int!)
                {
                    product(companyId: $companyId,productId: $productId)
                    {
                        data
                        {
                            name
                            productId
                            type
                            reference
                            summary
                            price
                            priceWithTaxes
                            hasStock
                            stock
                            measurementUnit
                            {
                                measurementUnitId
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

        return Connector::graphqlClient($query, json_encode($variables));
    }

    /**
     * Gets all products
     *
     * @param array $variables variables of the query
     *
     * @return array returns all products
     */
    public static function queryProducts($variables = [])
    {
        $query = 'query products($companyId: Int!,$options: ProductOptions)
                {
                    products(companyId: $companyId,options: $options)
                    {
                        data
                        {
                            name
                            productId
                            type
                            reference
                            summary
                            price
                            priceWithTaxes
                            hasStock
                            stock
                            measurementUnit
                            {
                                measurementUnitId
                                name
                            }   
                            warehouse
                            {
                                warehouseId
                            }
                            productCategory{
                                name
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

        return self::getApiPaginator($query, $variables, 'products');
    }
}
