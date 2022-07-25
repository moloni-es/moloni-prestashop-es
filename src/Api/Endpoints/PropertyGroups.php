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

class PropertyGroups extends Endpoint
{
    /**
     * Get multiple property groups
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function queryPropertyGroups(?array $variables = []): array
    {
        $query = 'query propertyGroups($companyId: Int!,$options: PropertyGroupOptions)
        {
            propertyGroups(companyId: $companyId,options: $options) 
            {
                data
                {
                    ' . $this->getCommonSegment() . '
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

        return $this->paginatedPost($query, $variables, 'propertyGroups');
    }

    /**
     * Get single property group
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function queryPropertyGroup(?array $variables = []): array
    {
        $query = 'query propertyGroup($companyId: Int!,$propertyGroupId: String!)
        {
            propertyGroup(companyId: $companyId,propertyGroupId: $propertyGroupId)
            {
                data
                {
                    ' . $this->getCommonSegment() . '
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
     * Update a property group
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationPropertyGroupUpdate(?array $variables = []): array
    {
        $query = 'mutation propertyGroupUpdate($companyId: Int!,$data: PropertyGroupUpdate!)
        {
            propertyGroupUpdate(companyId: $companyId,data: $data)
            {
                data
                {
                    ' . $this->getCommonSegment() . '
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
     * Create a property group
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationPropertyGroupCreate(?array $variables = []): array
    {
        $query = 'mutation propertyGroupCreate($companyId: Int!,$data: PropertyGroupInsert!)
        {
            propertyGroupCreate(companyId: $companyId,data: $data)
            {
                data
                {
                    ' . $this->getCommonSegment() . '
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
     * Common segments for all queries and mutations
     *
     * @return string
     */
    private function getCommonSegment(): string
    {
        return '
            propertyGroupId
            name
            visible
            deletable
            properties
            {
                propertyId
                name
                visible
                ordering
                deletable
                values
                {
                   propertyValueId
                   code
                   value
                   visible
                   ordering
                   deletable
                }
            }
        ';
    }
}
