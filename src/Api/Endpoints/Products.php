<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class Products extends Endpoint
{
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
                    ' . $this->getProductSegment() . '
                    ' . $this->getVariantSegment() . '
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
                            ' . $this->getProductSegment() . ' 
                            ' . $this->getVariantSegment() . '                   
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
                        data
                        {
                            ' . $this->getProductSegment() . '   
                            ' . $this->getVariantSegment() . '
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
                            ' . $this->getProductSegment() . '  
                            ' . $this->getVariantSegment() . '  
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
     * Update a product image
     *
     * @param array|null $variables variables of the query
     * @param string|null $file
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationProductImageUpdate(?array $variables = [], ?string $file = ''): array
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

        $operations = ['query' => $query, 'variables' => $variables];
        $map = '{ "0": ["variables.data.img"] }';

        return $this->postWithFile($operations, $map, $file);
    }

    //          PRIVATES          //

    /**
     * Product part of query
     *
     * @return string
     */
    private function getProductSegment(): string
    {
        return '
            name
            productId
            type
            reference
            summary
            price
            priceWithTaxes
            hasStock
            stock
            img
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
            warehouses
            {
                warehouseId
                stock
                minStock
            }
            productCategory{
                name
                productCategoryId
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
                    fiscalZone
                }
                value
                ordering
            }
        ';
    }

    /**
     * Variant part of query
     *
     * @return string
     */
    private function getVariantSegment(): string
    {
        return '
        variants
        {
            visible
            productId
            name
            reference
            summary
            price
            img
            priceWithTaxes
            hasStock
            stock
            warehouse
            {
                warehouseId
            }
            warehouses
            {
                warehouseId
                stock
                minStock
            }
            identifications
            {
                type
                favorite
                text
            } 
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
        ';
    }
}
