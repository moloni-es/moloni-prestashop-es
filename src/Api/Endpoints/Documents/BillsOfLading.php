<?php

namespace Moloni\Api\Endpoints\Documents;

use Moloni\Api\Endpoints\Endpoint;
use Moloni\Exceptions\MoloniApiException;

class BillsOfLading extends Endpoint
{
    /**
     * Fetch bill of lading
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryBillsOfLading(?array $variables = []): array
    {
        $query = 'query billsOfLading($companyId: Int!,$documentId: Int!)
        {
            billsOfLading(companyId: $companyId,documentId: $documentId) 
            {
                errors
                {
                    field
                    msg
                }
                data
                {
                    documentId
                    number
                    totalValue
                    documentTotal
                    documentSetName
                    ourReference
                    pdfExport
                }
            }
        }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Get document token and path for bills of lading
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryBillsOfLadingGetPDFToken(?array $variables = []): array
    {
        $query = 'query billsOfLadingGetPDFToken($documentId: Int!)
        {
            billsOfLadingGetPDFToken(documentId: $documentId)
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
     * Creates a bill of lading
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationBillsOfLadingCreate(?array $variables = []): array
    {
        $query = 'mutation billsOfLadingCreate($companyId: Int!,$data: BillsOfLadingInsert!,
        $options: BillsOfLadingMutateOptions){
                billsOfLadingCreate(companyId: $companyId,data: $data,options: $options) {
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
     * Update an invoice
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationBillsOfLadingUpdate(?array $variables = []): array
    {
        $query = 'mutation billsOfLadingUpdate($companyId: Int!,$data: BillsOfLadingUpdate!)
        {
            billsOfLadingUpdate(companyId: $companyId,data: $data) 
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
     * Creates bills of lading pdf
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function mutationBillsOfLadingGetPDF(?array $variables = []): array
    {
        $query = 'mutation billsOfLadingGetPDF($companyId: Int!,$documentId: Int!)
        {
            billsOfLadingGetPDF(companyId: $companyId,documentId: $documentId)
        }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Send bill of lading by email
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationBillsOfLadingSendEmail(?array $variables = []): array
    {
        $query = 'mutation billsOfLadingSendMail($companyId: Int!,$documents: [Int]!,$mailData: MailData)
        {
            billsOfLadingSendMail(companyId: companyId,documents: $documents,mailData: $mailData)
        }';

        return $this->simplePost($query, $variables);
    }
}
