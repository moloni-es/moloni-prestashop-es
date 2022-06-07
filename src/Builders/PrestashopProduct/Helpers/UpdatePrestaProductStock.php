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

use Moloni\Tools\Logs;
use StockAvailable;

class UpdatePrestaProductStock
{
    private $prestaProductId;
    private $prestaProductReference;
    private $attributeId;
    private $newStock;

    /**
     * Construct
     *
     * @param int $prestaProductId
     * @param string $prestaProductReference
     * @param int|null $attributeId
     * @param float|int|null $newStock
     */
    public function __construct(int $prestaProductId, ?int $attributeId = null, string $prestaProductReference = '', $newStock = 0)
    {
        $this->prestaProductId = $prestaProductId;
        $this->prestaProductReference = $prestaProductReference;
        $this->attributeId = $attributeId;
        $this->newStock = $newStock;

        $this->handle();
    }

    /**
     * Handler
     *
     * @return void
     */
    private function handle(): void
    {
        $currentStock = (float)StockAvailable::getQuantityAvailableByProduct($this->prestaProductId);

        if ($this->newStock !== $currentStock) {
            StockAvailable::setQuantity($this->prestaProductId, $this->attributeId, $this->newStock);

            $msg = [
                'Stock updated in Prestashop (old: {0} | new: {1}) ({2})', [
                    '{0}' => $currentStock,
                    '{1}' => $this->newStock,
                    '{2}' => $this->prestaProductReference,
                ]
            ];
        } else {
            $msg = ['Stock is already updated in Prestashop ({0})', ['{0}' => $this->prestaProductReference]];
        }

        Logs::addInfoLog($msg);
    }
}
