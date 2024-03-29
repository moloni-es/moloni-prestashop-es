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

namespace Moloni\Actions\Registration;

use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class IsVatValid
{
    private $vat;

    public function __construct($vat)
    {
        $this->vat = $vat;
    }

    public function handle(): bool
    {
        $props = [
            'vat' => $this->vat
        ];

        try {
            $query = MoloniApiClient::registration()->queryGetFreeVAT($props);
        } catch (MoloniApiException $e) {
            $query = false;
        }

        return $query['data']['getFreeVAT'] ?? false;
    }
}
