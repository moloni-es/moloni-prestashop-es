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

class PurchaseOrder extends Endpoint
{
    /**
     * Creates a purchase order
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryPurchaseOrder(?array $variables = []): array
    {
        $query = 'query purchaseOrder($companyId: Int!,$documentId: Int!,$options: PurchaseOrderOptionsSingle)
                {
                    purchaseOrder(companyId: $companyId,documentId: $documentId,options: $options)
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
     * Gets all purchase orders
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryPurchaseOrders(?array $variables = []): array
    {
        $query = 'query purchaseOrders($companyId: Int!,$options: PurchaseOrderOptions)
                {
                    purchaseOrders(companyId: $companyId,options: $options)
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

        return $this->paginatedPost($query, $variables, 'purchaseOrders');
    }

    /**
     * Get document token and path for purchase orders
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryPurchaseOrderGetPDFToken(?array $variables = []): array
    {
        $query = 'query purchaseOrderGetPDFToken($documentId: Int!)
                {
                    purchaseOrderGetPDFToken(documentId: $documentId)
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
     * Creates a purchase order
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationPurchaseOrderCreate(?array $variables = []): array
    {
        $query = 'mutation purchaseOrderCreate($companyId: Int!,$data: 
        PurchaseOrderInsert!,$options: PurchaseOrderMutateOptions)
                {
                    purchaseOrderCreate(companyId: $companyId,data: $data,options: $options)
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
     * Update a purchase order
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationPurchaseOrderUpdate(?array $variables = []): array
    {
        $query = 'mutation purchaseOrderUpdate($companyId: Int!,$data: PurchaseOrderUpdate!)
        {
            purchaseOrderUpdate(companyId: $companyId,data: $data)
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
     * Send purchased order by mail
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationPurchaseOrderSendEmail(?array $variables = []): array
    {
        $query = 'mutation purchaseOrderSendMail($companyId: Int!,$documents: [Int]!,$mailData: MailData)
        {
            purchaseOrderSendMail(companyId: $companyId,documents: $documents,mailData: $mailData)
        }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Creates purchase order pdf
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function mutationPurchaseOrderGetPDF(?array $variables = []): array
    {
        $query = 'mutation purchaseOrderGetPDF($companyId: Int!,$documentId: Int!)
                {
                    purchaseOrderGetPDF(companyId: $companyId,documentId: $documentId)
                }';

        return $this->simplePost($query, $variables);
    }
}
