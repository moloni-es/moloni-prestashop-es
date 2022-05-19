<?php

namespace Moloni\Api\Endpoints\Documents;

use Moloni\Api\Endpoints\Endpoint;
use Moloni\Exceptions\MoloniApiException;

class SimplifiedInvoice extends Endpoint
{
    /**
     * Gets simplified invoice information
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function querySimplifiedInvoice(?array $variables = []): array
    {
        $query = 'query simplifiedInvoice($companyId: Int!,$documentId: Int!,$options: SimplifiedInvoiceOptionsSingle)
                {
                    simplifiedInvoice(companyId: $companyId,documentId: $documentId,options: $options)
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
     * Gets all simplified invoices
     *
     * @param array|null $variables array variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function querySimplifiedInvoices(?array $variables = []): array
    {
        $query = 'query simplifiedInvoices($companyId: Int!,$options: SimplifiedInvoiceOptions)
                {
                    simplifiedInvoices(companyId: $companyId,options: $options)
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

        return $this->paginatedPost($query, $variables, 'simplifiedInvoices');
    }

    /**
     * Get document token and path for simplified invoices
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function querySimplifiedInvoiceGetPDFToken(?array $variables = []): array
    {
        $query = 'query simplifiedInvoiceGetPDFToken($documentId: Int!)
                {
                    simplifiedInvoiceGetPDFToken(documentId: $documentId)
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
     * Creates a simplified invoice
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationSimplifiedInvoiceCreate(?array $variables = []): array
    {
        $query = 'mutation simplifiedInvoiceCreate($companyId: Int!,$data: 
        SimplifiedInvoiceInsert!,$options: SimplifiedInvoiceMutateOptions)
                {
                    simplifiedInvoiceCreate(companyId: $companyId,data: $data,options: $options)
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
     * Update a simplified invoice
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationSimplifiedInvoiceUpdate(?array $variables = []): array
    {
        $query = 'mutation simplifiedInvoiceUpdate($companyId: Int!,$data: SimplifiedInvoiceUpdate!)
        {
            simplifiedInvoiceUpdate(companyId: $companyId,data: $data)
            {
                data
                {
                    documentId
                    status
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
     * Creates simplified invoice pdf
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function mutationSimplifiedInvoiceGetPDF(?array $variables = []): array
    {
        $query = 'mutation simplifiedInvoiceGetPDF($companyId: Int!,$documentId: Int!)
                {
                    simplifiedInvoiceGetPDF(companyId: $companyId,documentId: $documentId)
                }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Send simplified invoice by mail
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationSimplifiedInvoiceSendEmail(?array $variables = []): array
    {
        $query = 'mutation simplifiedInvoiceSendMail($companyId: Int!,$documents: [Int]!,$mailData: MailData)
        {
            simplifiedInvoiceSendMail(companyId: $companyId,documents: $documents,mailData: $mailData)
        }';

        return $this->simplePost($query, $variables);
    }
}
