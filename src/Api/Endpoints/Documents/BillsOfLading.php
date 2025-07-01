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

class BillsOfLading extends Endpoint
{
    /**
     * Fetch bill of lading
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryBillsOfLading(?array $variables = []): array
    {
        $query = $this->loadQuery('billsOfLading');

        return $this->simplePost($query, $variables);
    }

    /**
     * Get document token and path for bills of lading
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryBillsOfLadingGetPDFToken(?array $variables = []): array
    {
        $query = $this->loadQuery('billsOfLadingGetPDFToken');

        return $this->simplePost($query, $variables);
    }

    /**
     * Creates a bill of lading
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationBillsOfLadingCreate(?array $variables = []): array
    {
        $query = $this->loadMutation('billsOfLadingCreate');

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
    public function mutationBillsOfLadingUpdate(?array $variables = []): array
    {
        $query = $this->loadMutation('billsOfLadingUpdate');

        return $this->simplePost($query, $variables);
    }

    /**
     * Creates bills of lading pdf
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function mutationBillsOfLadingGetPDF(?array $variables = []): array
    {
        $query = $this->loadMutation('billsOfLadingGetPDF');

        return $this->simplePost($query, $variables);
    }

    /**
     * Send bill of lading by email
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationBillsOfLadingSendEmail(?array $variables = []): array
    {
        $query = $this->loadMutation('billsOfLadingSendMail');

        return $this->simplePost($query, $variables);
    }
}
