<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class Products extends Endpoint
{
    /**
     * Create a new product
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns some data of the created product
     *
     * @throws MoloniApiException
     */
    public function mutationProductCreate(?array $variables = []): array
    {
        $query = 'mutation productCreate($companyId: Int!,$data: ProductInsert!)
                {
                    productCreate(companyId: $companyId,data: $data) 
                    {
                        data{
                            productId
                            name
                            identifications
                            {
                                type
                                favorite
                                text
                            }
                        }
                        errors{
                            field
                            msg
                        }
                    }
                }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Update a product
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns some data of the updated product
     *
     * @throws MoloniApiException
     */
    public function mutationProductUpdate(?array $variables = []): array
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
                            identifications
                            {
                                type
                                favorite
                                text
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

    /**
     * Gets the information of a product
     *
     * @param array|null $variables variables of the query
     *
     * @return array information of the product
     *
     * @throws MoloniApiException
     */
    public function queryProduct(?array $variables = []): array
    {
        $query = 'query product($companyId: Int!,$productId: Int!)
        {
            product(companyId: $companyId,productId: $productId)
            {
                data
                {
                    visible
                    name
                    productId
                    type
                    reference
                    summary
                    price
                    priceWithTaxes
                    hasStock
                    stock
                    minStock
                    measurementUnit
                    {
                        measurementUnitId
                        name
                    }   
                    warehouse
                    {
                        warehouseId
                    }
                    productCategory
                    {
                        productCategoryId
                        name
                    }
                    identifications
                    {
                        type
                        favorite
                        text
                    }                
                    variants
                    {
                        visible
                        productId
                        name
                        reference
                        summary
                        price
                        priceWithTaxes
                        hasStock
                        stock
                        propertyPairs
                        {
                            property
                            {
                                name
                            }
                            propertyValue
                            {
                                code
                                value
                            }
                        }
                    }
                    parent
                    {
                        productId
                        name
                    }
                    propertyGroup
                    {
                        propertyGroupId
                        name
                        properties
                        {
                            propertyId
                            name
                            ordering
                            values
                            {
                                propertyValueId
                                code
                                value
                            }
                        }
                    }
                    taxes
                    {
                        tax
                        {
                            taxId
                            value
                            name
                        }
                        value
                        ordering
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

    /**
     * Gets all products
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns all products
     *
     * @throws MoloniApiException
     */
    public function queryProducts(?array $variables = []): array
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
                            identifications
                            {
                                type
                                favorite
                                text
                            }
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

        return $this->paginatedPost($query, $variables, 'products');
    }
}
