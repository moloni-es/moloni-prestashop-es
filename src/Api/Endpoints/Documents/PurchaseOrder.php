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
        $query = $this->loadQuery('purchaseOrder');

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
        $query = $this->loadQuery('purchaseOrders');

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
        $query = $this->loadQuery('purchaseOrderGetPDFToken');

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
        $query = $this->loadMutation('purchaseOrderCreate');

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
        $query = $this->loadMutation('purchaseOrderUpdate');

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
        $query = $this->loadMutation('purchaseOrderSendMail');

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
        $query = $this->loadMutation('purchaseOrderGetPDF');

        return $this->simplePost($query, $variables);
    }
}
