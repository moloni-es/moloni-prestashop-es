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

class DeliveryMethods extends Endpoint
{
    /**
     * Create a new delivery methods
     *
     * @param array|null $variables Request variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationDeliveryMethodCreate(?array $variables = []): array
    {
        $query = $this->loadMutation('deliveryMethodCreate');

        return $this->simplePost($query, $variables);
    }

    /**
     * Get All DeliveryMethods from Moloni ES
     *
     * @param array|null $variables Request variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryDeliveryMethods(?array $variables = []): array
    {
        $query = $this->loadQuery('deliveryMethods');

        return $this->paginatedPost($query, $variables, 'deliveryMethods');
    }
}
