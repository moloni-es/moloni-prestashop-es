<?php

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
