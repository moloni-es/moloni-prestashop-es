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

class PriceClasses extends Endpoint
{
    /**
     * Get a price class
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function queryPriceClass(?array $variables = []): array
    {
        $query = $this->loadQuery('priceClass');

        return $this->simplePost($query, $variables);
    }

    /**
     * Get all price classes
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function queryPriceClasses(?array $variables = []): array
    {
        $query = $this->loadQuery('priceClasses');

        return $this->paginatedPost($query, $variables, 'priceClasses');
    }

    /**
     * Create a price class
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationPriceClassCreate(?array $variables = []): array
    {
        $query = $this->loadMutation('priceClassCreate');

        return $this->simplePost($query, $variables);
    }
}
