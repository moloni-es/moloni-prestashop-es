<?php

namespace Moloni\Api\Endpoints;

use Moloni\Api\Curl;

class Hooks extends Endpoint
{
    /**
     * Gets all hooks
     *
     * @param $variables
     *
     * @return array
     */
    public static function queryHooks($variables)
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

        return Curl::complex($query, $variables, 'hooks');
    }

    /**
     * Create a hook
     *
     * @param $variables
     *
     * @return array
     */
    public static function mutationHookCreate($variables)
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

        return Curl::simple($query, $variables);
    }

    /**
     * Delete hooks
     *
     * @param $variables
     *
     * @return array
     */
    public static function mutationHookDelete($variables)
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

        return Curl::simple($query, $variables);
    }
}
