<?php

/**
 * 2025 - Moloni.com
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
        $query = $this->loadQuery('invoice');

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
        $query = $this->loadQuery('invoices');

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
        $query = $this->loadQuery('invoiceGetPDFToken');

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
        $query = $this->loadMutation('invoiceCreate');

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
        $query = $this->loadMutation('invoiceUpdate');

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
        $query = $this->loadMutation('invoiceGetPDF');

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
        $query = $this->loadMutation('invoiceSendMail');

        return $this->simplePost($query, $variables);
    }
}
