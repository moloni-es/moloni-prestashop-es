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

class CreditNote extends Endpoint
{
    /**
     * Gets credit note information
     *
     * @param array|null $variables array variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryCreditNote(?array $variables = []): array
    {
        $query = 'query creditNote($companyId: Int!,$documentId: Int!,$options: CreditNoteOptionsSingle)
                {
                    creditNote(companyId: $companyId,documentId: $documentId,options: $options)
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
     * Gets all credit notes
     *
     * @param array|null $variables array variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryCreditNotes(?array $variables = []): array
    {
        $query = 'query creditNotes($companyId: Int!,$options: CreditNoteOptions)
                {
                    creditNotes(companyId: $companyId,options: $options)
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

        return $this->paginatedPost($query, $variables, 'creditNotes');
    }

    /**
     * Get document token and path for credit notes
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryCreditNoteGetPDFToken(?array $variables = []): array
    {
        $query = 'query creditNoteGetPDFToken($documentId: Int!)
                {
                    creditNoteGetPDFToken(documentId: $documentId)
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
     * Creates a credit note
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationCreditNoteCreate(?array $variables = []): array
    {
        $query = 'mutation creditNoteCreate($companyId: Int!,$data: CreditNoteInsert!,$options:CreditNoteMutateOptions)
                {
                    creditNoteCreate(companyId: $companyId,data: $data,options: $options)
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
     * Creates credit notes pdf
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function mutationCreditNoteGetPDF(?array $variables = []): array
    {
        $query = 'mutation creditNoteGetPDF($companyId: Int!,$documentId: Int!)
                {
                    creditNoteGetPDF(companyId: $companyId,documentId: $documentId)
                }';

        return $this->simplePost($query, $variables);
    }
}
