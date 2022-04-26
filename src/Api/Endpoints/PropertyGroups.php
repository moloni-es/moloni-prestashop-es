<?php

namespace Moloni\Api\Endpoints;

use Moloni\Api\Curl;

class PropertyGroups extends Endpoint
{
    /**
     * Get multiple property groups
     * @param array $variables
     * @return array
     */
    public static function queryPropertyGroups(array $variables = []): array
    {
        $query = 'query propertyGroups($companyId: Int!,$options: PropertyGroupOptions)
        {
            propertyGroups(companyId: $companyId,options: $options) 
            {
                data
                {
                    propertyGroupId
                    name
                    visible
                    properties
                    {
                        propertyId
                        name
                        visible
                        ordering
                        values
                        {
                           propertyValueId
                           code
                           value
                           visible
                           ordering
                        }
                    }
                }
                errors
                {
                    field
                    msg
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
            }
        }';

        return Curl::complex($query, $variables, 'propertyGroups');
    }

    /**
     * Get single property group
     * @param array $variables
     * @return array
     */
    public static function queryPropertyGroup(array $variables = []): array
    {
        $query = 'query propertyGroup($companyId: Int!,$propertyGroupId: String!)
        {
            propertyGroup(companyId: $companyId,propertyGroupId: $propertyGroupId)
            {
                data
                {
                    propertyGroupId
                    name
                    visible
                    properties
                    {
                        propertyId
                        name
                        visible
                        ordering
                        values
                        {
                           propertyValueId
                           code
                           value
                           visible
                           ordering
                        }
                    }
                }
                errors
                {
                    field
                    msg
                }
            }
        }';

        return Curl::simple($query, $variables);
    }

    /**
     * Update a property group
     * @param array $variables
     * @return array
     */
    public static function mutationPropertyGroupUpdate(array $variables  = []): array
    {
        $query = 'mutation propertyGroupUpdate($companyId: Int!,$data: PropertyGroupUpdate!)
        {
            propertyGroupUpdate(companyId: $companyId,data: $data)
            {
                data
                {
                    propertyGroupId
                    name
                    visible
                    properties
                    {
                        propertyId
                        name
                        visible
                        ordering
                        values
                        {
                           propertyValueId
                           code
                           value
                           visible
                           ordering
                        }
                    }
                }
                errors
                {
                    field
                    msg
                }
            }
        }';

        return Curl::simple($query, $variables);
    }

    /**
     * Create a property group
     * @param array $variables
     * @return array
     */
    public static function mutationPropertyGroupCreate(array $variables = []): array
    {
        $query = 'mutation propertyGroupCreate($companyId: Int!,$data: PropertyGroupInsert!)
        {
            propertyGroupCreate(companyId: $companyId,data: $data)
            {
                data
                {
                    propertyGroupId
                    name
                    visible
                    properties
                    {
                        propertyId
                        name
                        visible
                        ordering
                        values
                        {
                           propertyValueId
                           code
                           value
                           visible
                           ordering
                        }
                    }
                }
                errors
                {
                    field
                    msg
                }
            }
        }';

        return Curl::simple($query, $variables);
    }
}
