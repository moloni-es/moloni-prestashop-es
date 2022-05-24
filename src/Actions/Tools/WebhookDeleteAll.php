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

namespace Moloni\Actions\Tools;

use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniException;

class WebhookDeleteAll
{
    /**
     * Delete all prestashop webhooks
     *
     * @throws MoloniException
     */
    public function handle(): void
    {
        $baseUrl = defined('__PS_BASE_URI__') ? __PS_BASE_URI__ : '';

        $props = [
            'options' => [
                'search' => [
                    'field' => 'url',
                    'value' => $baseUrl . 'api/moloniresource/',
                ],
            ]
        ];

        try {
            $query = MoloniApiClient::hooks()->queryHooks($props);

            if (!empty($query)) {
                $ids = [];

                foreach ($query as $hook) {
                    $ids[] = $hook['hookId'];
                }

                MoloniApiClient::hooks()->mutationHookDelete([
                    'hookId' => $ids,
                ]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniException('Error deleting hooks', [], [$e->getData()]);
        }
    }
}
