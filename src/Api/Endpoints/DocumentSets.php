<?php

namespace Moloni\Api\Endpoints;

use Moloni\Api\Curl;

class DocumentSets extends Endpoint
{
    /**
     * Get All Documents Set from Moloni ES
     *
     * @param array $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryDocumentSets(array $variables = []): array
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

        return Curl::complex($query, $variables, 'documentSets');
    }
}
