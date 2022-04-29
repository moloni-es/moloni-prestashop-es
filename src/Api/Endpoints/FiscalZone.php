<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class FiscalZone extends Endpoint
{
    /**
     * Get settings for a fiscal zone
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryFiscalZoneTaxSettings(?array $variables = []): array
    {
        $query = 'query fiscalZoneTaxSettings($companyId: Int!,$fiscalZone: String!)
        {
            fiscalZoneTaxSettings(companyId: $companyId,fiscalZone: $fiscalZone)
            {
                fiscalZone
                fiscalZoneModes
                {
                    typeId
                    name
                    visible
                    type
                    values
                    {
                        code
                        name
                    }
                }
                fiscalZoneFinanceTypes
                {
                    id
                    name
                    code
                    isVAT
                }
            }
        }';

        return $this->simplePost($query, $variables);
    }
}
