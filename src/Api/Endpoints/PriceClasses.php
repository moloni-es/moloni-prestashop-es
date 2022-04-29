<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class PriceClasses extends Endpoint
{
    /**
     * Get a price class
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function queryPriceClass(?array $variables = []): array
    {
        $query = 'query priceClass($companyId: Int!,$priceClassId: Int!)
        {
            priceClass(companyId: $companyId,priceClassId: $priceClassId)
            {
                data
                {
                    priceClassId
                    visible
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
     * Get all price classes
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function queryPriceClasses(?array $variables = []): array
    {
        $query = 'query priceClasses($companyId: Int!,$options: PriceClassOptions)
        {
            priceClasses(companyId: $companyId,options: $options)
            {
                data
                {
                    priceClassId
                    visible
                    name
                }
                errors
                {
                    field
                    msg
                }
            }
        }';

        return $this->paginatedPost($query, $variables, 'priceClasses');
    }

    /**
     * Create a price class
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationPriceClassCreate(?array $variables = []): array
    {
        $query = 'mutation priceClassCreate($companyId: Int!,$data: PriceClassInsert!)
        {
            priceClassCreate(companyId: $companyId,data: $data)
            {
                data
                {
                    priceClassId
                    visible
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
}
