<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class Registration extends Endpoint
{
    /**
     * Add new registration
     *
     * @throws MoloniApiException
     */
    public function mutationCompanySignUp(?array $variables = []): array
    {
        $query = 'mutation companySignUp($data: CompanySignUp!)
        {
            companySignUp(data: $data)
        }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Check if slug is free
     *
     * @throws MoloniApiException
     */
    public function queryGetFreeSlug(?array $variables = []): array
    {
        $query = 'query getFreeSlug($slug: String!)
        {
            getFreeSlug(slug: $slug)
        }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Check if VAT is free
     *
     * @throws MoloniApiException
     */
    public function queryGetFreeVAT(?array $variables = []): array
    {
        $query = 'query getFreeVAT($vat: String!)
        {
            getFreeVAT(vat: $vat)
        }';

        return $this->simplePost($query, $variables);
    }
}
