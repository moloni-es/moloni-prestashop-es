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

namespace Moloni\Api\Endpoints;

use Moloni\Api\MoloniApi;
use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class Endpoint
{
    /**
     * Requests cache
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Make a simple request
     *
     * @param string $query
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    protected function simplePost(string $query, ?array $variables): array
    {
        return MoloniApi::post([
           'query' => $query,
           'variables' => $variables,
        ]);
    }

    /**
     * Make a paginated request
     *
     * @param string $query
     * @param array|null $variables
     * @param string $key
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    protected function paginatedPost(string $query, ?array $variables, string $key): array
    {
        $pageNumber = 0;
        $pageLimit = 100;
        $data = [];

        do {
            ++$pageNumber;

            $variables['options']['pagination']['qty'] = 50;
            $variables['options']['pagination']['page'] = $pageNumber;

            $queryResult = MoloniApi::post([
                'query' => $query,
                'variables' => $variables,
            ]);

            if (isset($queryResult['errors'])) {
                throw new MoloniApiException('Error paginating request', [], ['query' => $query, 'variables' => $variables, 'result' => $queryResult]);
            }

            $querySize = $queryResult['data'][$key]['options']['pagination'];

            /** @noinspection SlowArrayOperationsInLoopInspection */
            $data = array_merge($data, $queryResult['data'][$key]['data']);
        } while ($querySize['count'] > ($querySize['qty'] * $querySize['page']) && $pageNumber < $pageLimit);

        return $data;
    }
}
