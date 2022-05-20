<?php

namespace Moloni\Api\Endpoints\Documents;

use Moloni\Api\Endpoints\Endpoint;
use Moloni\Exceptions\MoloniApiException;

class ProFormaInvoice extends Endpoint
{
    /**
     * Creates a pro forma invoice
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryProFormaInvoice(?array $variables = []): array
    {
        $query = 'query proFormaInvoice($companyId: Int!,$documentId: Int!,$options: ProFormaInvoiceOptionsSingle)
                {
                    proFormaInvoice(companyId: $companyId,documentId: $documentId,options: $options)
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
     * Gets all pro forma invoices
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryProFormaInvoices(?array $variables = []): array
    {
        $query = 'query proFormaInvoices($companyId: Int!,$options: ProFormaInvoiceOptions)
                {
                    proFormaInvoices(companyId: $companyId,options: $options)
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

        return $this->paginatedPost($query, $variables, 'proFormaInvoices');
    }

    /**
     * Get document token and path for pro forma invoices
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryProFormaInvoiceGetPDFToken(?array $variables = []): array
    {
        $query = 'query proFormaInvoiceGetPDFToken($documentId: Int!)
                {
                    proFormaInvoiceGetPDFToken(documentId: $documentId)
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
     * Creates a pro forma invoice
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationProFormaInvoiceCreate(?array $variables = []): array
    {
        $query = 'mutation proFormaInvoiceCreate($companyId: Int!,$data: 
        ProFormaInvoiceInsert!,$options: ProFormaInvoiceMutateOptions)
                {
                    proFormaInvoiceCreate(companyId: $companyId,data: $data,options: $options)
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
                            currencyExchangeTotalValue
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
     * Update a pro forma invoice
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationProFormaInvoiceUpdate(?array $variables = []): array
    {
        $query = 'mutation proFormaInvoiceUpdate($companyId: Int!,$data: ProFormaInvoiceUpdate!)
        {
            proFormaInvoiceUpdate(companyId: $companyId,data: $data)
            {
                data
                {
                    documentId
                    status
                    currencyExchangeTotalValue
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
     * Creates pro forma invocie pdf
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function mutationProFormaInvoiceGetPDF(?array $variables = []): array
    {
        $query = 'mutation proFormaInvoiceGetPDF($companyId: Int!,$documentId: Int!)
                {
                    proFormaInvoiceGetPDF(companyId: $companyId,documentId: $documentId)
                }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Send pro forma invoice by mail
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationProFormaInvoiceSendEmail(?array $variables = []): array
    {
        $query = 'mutation proFormaInvoiceSendMail($companyId: Int!,$documents: [Int]!,$mailData: MailData)
        {
            proFormaInvoiceSendMail(companyId: $companyId,documents: $documents,mailData: $mailData)
        }';

        return $this->simplePost($query, $variables);
    }
}
