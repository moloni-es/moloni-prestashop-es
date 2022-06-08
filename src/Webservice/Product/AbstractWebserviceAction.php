<?php
/**
 * 2022 - Moloni.com
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

namespace Moloni\Webservice\Product;

use Moloni\Api\MoloniApi;
use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;

abstract class AbstractWebserviceAction
{
    protected $productId;

    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    protected function isAuthenticated(): bool
    {
        return MoloniApi::hasValidAuthentication();
    }

    /**
     * @throws MoloniProductException
     */
    protected function fetchProductFromMoloni($productId): array
    {
        $variables = [
            'productId' => $productId
        ];

        try {
            $query = MoloniApiClient::products()->queryProduct($variables);

            $moloniProduct = $query['data']['product']['data'] ?? [];

            if (empty($moloniProduct)) {
                throw new MoloniProductException('Could not find product in Moloni ({0})', ['{0}' => $productId], [
                    'variables' => $variables,
                    'query' => $query,
                ]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error fetching product by id ({0})', ['{0}' => $productId], $e->getData());
        }

        return $moloniProduct;
    }
}
