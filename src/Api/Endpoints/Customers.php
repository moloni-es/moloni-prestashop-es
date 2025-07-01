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

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Customers extends Endpoint
{
    /**
     * Creates a costumer
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationCustomerCreate(?array $variables = []): array
    {
        $query = $this->loadMutation('customerCreate');

        return $this->simplePost($query, $variables);
    }

    /**
     * Creates a costumer
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationCustomerUpdate(?array $variables = []): array
    {
        $query = $this->loadMutation('customerUpdate');

        return $this->simplePost($query, $variables);
    }

    /**
     * Gets costumer information
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryCustomer(?array $variables = []): array
    {
        $query = $this->loadQuery('customer');

        return $this->simplePost($query, $variables);
    }

    /**
     * Gets costumers of the company
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryCustomers(?array $variables = []): array
    {
        $query = $this->loadQuery('customers');

        return $this->paginatedPost($query, $variables, 'customers');
    }

    /**
     * Gets the next number available for customers
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryCustomerNextNumber(?array $variables = []): array
    {
        $query = $this->loadQuery('customerNextNumber');

        return $this->simplePost($query, $variables);
    }
}
