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
        $query = $this->loadQuery('simplifiedInvoice');

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
        $query = $this->loadQuery('simplifiedInvoices');

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
        $query = $this->loadQuery('simplifiedInvoiceGetPDFToken');

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
        $query = $this->loadMutation('simplifiedInvoiceCreate');

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
        $query = $this->loadMutation('simplifiedInvoiceUpdate');

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
        $query = $this->loadMutation('simplifiedInvoiceGetPDF');

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
        $query = $this->loadMutation('simplifiedInvoiceSendMail');

        return $this->simplePost($query, $variables);
    }
}
