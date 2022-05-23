<?php

namespace Moloni\Api\Endpoints\Documents;

use Moloni\Api\Endpoints\Endpoint;
use Moloni\Exceptions\MoloniApiException;

class Estimate extends Endpoint
{
    /**
     * Gets estimate information
     *
     * @param array|null $variables array variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryEstimate(?array $variables = []): array
    {
        $query = 'query estimate($companyId: Int!,$documentId: Int!,$options: EstimateOptionsSingle)
        {
            estimate(companyId: $companyId,documentId: $documentId,options: $options)
            {
                data
                {
                    documentId
                    number
                    ourReference
                    yourReference
                    entityVat
                    entityNumber
                    entityName
                    documentSetName
                    totalValue
                    pdfExport
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
     * Gets all estimates
     *
     * @param array|null $variables array variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryEstimates(?array $variables = []): array
    {
        $query = 'query estimates($companyId: Int!,$options: EstimateOptions)
        {
            estimates(companyId: $companyId,options: $options)
            {
                data
                {
                    documentId
                    number
                    ourReference
                    yourReference
                    entityVat
                    entityNumber
                    entityName
                    documentSetName
                    totalValue
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
                errors
                {
                    field
                    msg
                }
            }
        }';

        return $this->paginatedPost($query, $variables, 'estimates');
    }

    /**
     * Get document token and path for estimates
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryEstimateGetPDFToken(?array $variables = []): array
    {
        $query = 'query estimateGetPDFToken($documentId: Int!)
        {
            estimateGetPDFToken(documentId: $documentId)
            {
                data
                {
                    token
                    filename
                    path
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
     * Creates an estimate
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationEstimateCreate(?array $variables = []): array
    {
        $query = 'mutation estimateCreate($companyId: Int!,$data: EstimateInsert!,$options: EstimateMutateOptions){
            estimateCreate(companyId: $companyId,data: $data,options: $options) {
                errors{
                    field
                    msg
                }
                data{
                    documentId
                    number
                    totalValue
                    documentTotal
                    documentSetName
                    ourReference
                    currencyExchangeTotalValue
                }
            }
        }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Update an estimate
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationEstimateUpdate(?array $variables = []): array
    {
        $query = 'mutation estimateUpdate($companyId: Int!,$data: EstimateUpdate!)
        {
            estimateUpdate(companyId: $companyId,data: $data) 
            {
                errors
                {
                    field
                    msg
                }
                data
                {
                    documentId
                    status
                    currencyExchangeTotalValue                              
                }
            }
        }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Creates estimate pdf
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function mutationEstimateGetPDF(?array $variables = []): array
    {
        $query = 'mutation estimateGetPDF($companyId: Int!,$documentId: Int!)
        {
            estimateGetPDF(companyId: $companyId,documentId: $documentId)
        }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Send estimate by mail
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationEstimateSendMail(?array $variables = []): array
    {
        $query = 'mutation estimateSendMail($companyId: Int!,$documents: [Int]!,$mailData: MailData)
        {
            estimateSendMail(companyId: $companyId,documents: $documents,mailData: $mailData)
        }';

        return $this->simplePost($query, $variables);
    }
}
