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

class Receipt extends Endpoint
{
    /**
     * Gets receipt information
     *
     * @param array|null $variables array variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryReceipt(?array $variables = []): array
    {
        $query = $this->loadQuery('receipt');

        return $this->simplePost($query, $variables);
    }

    /**
     * Gets all receipts
     *
     * @param array|null $variables array variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryReceipts(?array $variables = []): array
    {
        $query = $this->loadQuery('receipts');

        return $this->paginatedPost($query, $variables, 'receipts');
    }

    /**
     * Get document token and path for receipts
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryReceiptGetPDFToken(?array $variables = []): array
    {
        $query = $this->loadQuery('receiptGetPDFToken');

        return $this->simplePost($query, $variables);
    }

    /**
     * Creates receipt pdf
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function mutationReceiptGetPDF(?array $variables = []): array
    {
        $query = $this->loadMutation('receiptGetPDF');

        return $this->simplePost($query, $variables);
    }

    /**
     * Creates a receipt
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationReceiptCreate(?array $variables = []): array
    {
        $query = $this->loadMutation('receiptCreate');

        return $this->simplePost($query, $variables);
    }

    /**
     * Update a receipt
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationReceiptUpdate(?array $variables = []): array
    {
        $query = $this->loadMutation('receiptUpdate');

        return $this->simplePost($query, $variables);
    }

    /**
     * Send receipt by mail
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationReceiptSendEmail(?array $variables = []): array
    {
        $query = $this->loadMutation('receiptSendMail');

        return $this->simplePost($query, $variables);
    }
}
