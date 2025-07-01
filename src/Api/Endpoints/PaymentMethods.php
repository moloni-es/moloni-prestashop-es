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

class PaymentMethods extends Endpoint
{
    /**
     * Get payment methods info
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryPaymentMethod(?array $variables = []): array
    {
        $query = $this->loadQuery('paymentMethod');

        return $this->simplePost($query, $variables);
    }

    /**
     * Get All Payment Methods from Moloni ES
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryPaymentMethods(?array $variables = []): array
    {
        $query = $this->loadQuery('paymentMethods');

        return $this->paginatedPost($query, $variables, 'paymentMethods');
    }

    /**
     * Creates an payment method
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function mutationPaymentMethodCreate(?array $variables = []): array
    {
        $query = $this->loadMutation('paymentMethodCreate');

        return $this->simplePost($query, $variables);
    }
}
