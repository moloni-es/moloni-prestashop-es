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

namespace Moloni\Guzzle;

use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface GuzzleInterface
{
    /**
     * Do post request
     *
     * @param string $url Request url
     * @param array|null $headers Request headers
     * @param array|null $body Request body
     *
     * @return mixed|null
     *
     * @throws MoloniApiException
     */
    public function post(string $url, ?array $headers = [], ?array $body = []);

    /**
     * Make authenticated post request with file
     *
     * @param array|null $operations
     * @param string|null $map
     * @param array|null $files
     * @param string|null $accessToken
     *
     * @return mixed|null
     *
     * @throws MoloniApiException
     */
    public function postWithFile(?array $operations = [], ?string $map = '', ?array $files = [], ?string $accessToken = '');
}
