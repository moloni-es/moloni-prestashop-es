<?php
/**
 * 2023 - Moloni.com
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

declare(strict_types=1);

namespace Moloni\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Stock
{
    public static function getMoloniStock(array $moloniProduct, ?int $warehouseId = 0): float
    {
        $stock = 0.0;

        if ($warehouseId === 1) {
            $stock = (float)($moloniProduct['stock'] ?? 0);
        } else {
            foreach ($moloniProduct['warehouses'] as $warehouse) {
                if ((int)$warehouse['warehouseId'] === $warehouseId) {
                    $stock = (float)$warehouse['stock'];

                    break;
                }
            }
        }

        return $stock;
    }
}
