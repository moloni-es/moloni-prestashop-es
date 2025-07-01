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
        $query = $this->loadQuery('proFormaInvoice');

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
        $query = $this->loadQuery('proFormaInvoices');

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
        $query = $this->loadQuery('proFormaInvoiceGetPDFToken');

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
        $query = $this->loadMutation('proFormaInvoiceCreate');

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
        $query = $this->loadMutation('proFormaInvoiceUpdate');

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
        $query = $this->loadMutation('proFormaInvoiceGetPDF');

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
        $query = $this->loadMutation('proFormaInvoiceSendMail');

        return $this->simplePost($query, $variables);
    }
}
