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

class Products extends Endpoint
{
    /**
     * Gets the information of a product
     *
     * @param array|null $variables variables of the query
     *
     * @return array information of the product
     *
     * @throws MoloniApiException
     */
    public function queryProduct(?array $variables = []): array
    {
        $query = $this->loadQuery('product');

        return $this->simplePost($query, $variables);
    }

    /**
     * Gets all products
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns all products
     *
     * @throws MoloniApiException
     */
    public function queryProducts(?array $variables = [], ?bool $singlePage = false): array
    {
        $query = $this->loadQuery('products');

        if ($singlePage === true) {
            return $this->simplePost($query, $variables);
        }

        return $this->paginatedPost($query, $variables, 'products');
    }

    /**
     * Create a new product
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns some data of the created product
     *
     * @throws MoloniApiException
     */
    public function mutationProductCreate(?array $variables = []): array
    {
        $query = $this->loadMutation('productCreate');

        return $this->simplePost($query, $variables);
    }

    /**
     * Update a product
     *
     * @param array|null $variables variables of the query
     *
     * @return array returns some data of the updated product
     *
     * @throws MoloniApiException
     */
    public function mutationProductUpdate(?array $variables = []): array
    {
        $query = $this->loadMutation('productUpdate');

        return $this->simplePost($query, $variables);
    }
}
