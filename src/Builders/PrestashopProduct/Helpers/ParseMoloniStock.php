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

namespace Moloni\Builders\PrestashopProduct\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

namespace Moloni\Builders\PrestashopProduct\Helpers;

class ParseMoloniStock
{
    private $moloniProduct;
    private $warehouseId;

    private $stock;

    public function __construct($moloniProduct, $warehouseId)
    {
        $this->moloniProduct = $moloniProduct;
        $this->warehouseId = $warehouseId;

        $this->run();
    }

    private function run()
    {
        $stock = 0.0;

        if ($this->warehouseId === 1) {
            $stock = (float)($this->moloniProduct['stock'] ?? 0);
        } else {
            foreach ($this->moloniProduct['warehouses'] as $warehouse) {
                $stock = (float)$warehouse['stock'];

                if ((int)$warehouse['warehouseId'] === $this->warehouseId) {
                    break;
                }
            }
        }

        $this->stock = $stock;
    }

    public function getStock(): float
    {
        return $this->stock;
    }
}
