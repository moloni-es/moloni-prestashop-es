<?php
/**
 * 2022 - Moloni.com
 *
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Moloni
 * @copyright Moloni
 * @license   https://creativecommons.org/licenses/by-nd/4.0/
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

namespace Moloni\Api\Endpoints\Documents;

use Moloni\Api\Endpoints\Endpoint;
use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Invoice extends Endpoint
{
    /**
     * Gets invoice information
     *
     * @param array|null $variables array variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryInvoice(?array $variables = []): array
    {
        $query = 'query invoice($companyId: Int!,$documentId: Int!,$options: InvoiceOptionsSingle)
                {
                    invoice(companyId: $companyId,documentId: $documentId,options: $options)
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
     * Gets all invoices
     *
     * @param array|null $variables array variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryInvoices(?array $variables = []): array
    {
        $query = 'query invoices($companyId: Int!,$options: InvoiceOptions)
                {
                    invoices(companyId: $companyId,options: $options)
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

        return $this->paginatedPost($query, $variables, 'invoices');
    }

    /**
     * Get document token and path for invoices
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryInvoiceGetPDFToken(?array $variables = []): array
    {
        $query = 'query invoiceGetPDFToken($documentId: Int!)
                {
                    invoiceGetPDFToken(documentId: $documentId)
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
     * Creates an invoice
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationInvoiceCreate(?array $variables = []): array
    {
        $query = 'mutation invoiceCreate($companyId: Int!,$data: InvoiceInsert!,$options: InvoiceMutateOptions){
                invoiceCreate(companyId: $companyId,data: $data,options: $options) {
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
    public function mutationInvoiceUpdate(?array $variables = []): array
    {
        $query = 'mutation invoiceUpdate($companyId: Int!,$data: InvoiceUpdate!)
        {
            invoiceUpdate(companyId: $companyId,data: $data) 
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
     * Creates invoice pdf
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function mutationInvoiceGetPDF(?array $variables = []): array
    {
        $query = 'mutation invoiceGetPDF($companyId: Int!,$documentId: Int!)
                {
                    invoiceGetPDF(companyId: $companyId,documentId: $documentId)
                }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Send invoice by mail
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationInvoiceSendEmail(?array $variables = []): array
    {
        $query = 'mutation invoiceSendMail($companyId: Int!,$documents: [Int]!,$mailData: MailData)
        {
            invoiceSendMail(companyId: $companyId,documents: $documents,mailData: $mailData)
        }';

        return $this->simplePost($query, $variables);
    }
}
