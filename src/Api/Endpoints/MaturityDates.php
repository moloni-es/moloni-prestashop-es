<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class MaturityDates extends Endpoint
{
    /**
     * Get All Maturity Dates from Moloni ES
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryMaturityDates(?array $variables = []): array
    {
        $query = 'query maturityDates($companyId: Int!,$options: MaturityDateOptions){
            maturityDates(companyId: $companyId, options: $options) {
                errors{
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
                data{
                    maturityDateId
                    name
                    days
                    discount
                }
            }
        }';

        return $this->paginatedPost($query, $variables, 'maturityDates');
    }
}
