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

class Hooks extends Endpoint
{
    /**
     * Gets all hooks
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function queryHooks(?array $variables = []): array
    {
        $query = 'query hooks($companyId: Int!,$options: HookOptions)
        {
            hooks(companyId: $companyId,options: $options)
            {
                data
                {
                    hookId
                    url
                    name
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

        return $this->paginatedPost($query, $variables, 'hooks');
    }

    /**
     * Create a hook
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationHookCreate(?array $variables = []): array
    {
        $query = 'mutation hookCreate($companyId: Int!,$data: HookInsert!)
        {
            hookCreate(companyId: $companyId,data: $data)
            {
                data
                {
                    hookId
                    url
                    name
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
     * Delete hooks
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationHookDelete(?array $variables = []): array
    {
        $query = 'mutation hookDelete($companyId: Int!,$hookId: [String!]!)
        {
            hookDelete(companyId: $companyId,hookId: $hookId)
            {
                status
                deletedCount
                elementsCount
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
