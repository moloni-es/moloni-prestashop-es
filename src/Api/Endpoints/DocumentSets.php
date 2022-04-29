<?php

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

class DocumentSets extends Endpoint
{
    /**
     * Get All Documents Set from Moloni ES
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryDocumentSets(?array $variables = []): array
    {
        $query = 'query documentSets($companyId: Int!,$options: DocumentSetOptions){
            documentSets(companyId: $companyId, options: $options) {
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
                    documentSetId
                    name
                    isDefault
                }
            }
        }';

        return $this->paginatedPost($query, $variables, 'documentSets');
    }
}
