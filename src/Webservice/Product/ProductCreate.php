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

use Moloni\Builders\PrestashopProductFromId;
use Moloni\Enums\Boolean;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Tools\Logs;
use Moloni\Tools\Settings;
use Moloni\Tools\SyncLogs;

class ProductCreate extends AbstractWebserviceAction
{
    public function handle(): void
    {
        if (!$this->shouldExecuteHandle()) {
            return;
        }

        try {
            $productBuilder = new PrestashopProductFromId($this->productId);
            $prestaProductId = $productBuilder->getPrestashopProductId();

            if ($prestaProductId === 0) {
                $productBuilder->insert();

                SyncLogs::productAddTimeout($productBuilder->getPrestashopProductId());
            } elseif ((int)Settings::get('updateProductsToPrestashop') === Boolean::YES && !SyncLogs::productHasTimeout($prestaProductId)) {
                SyncLogs::productAddTimeout($prestaProductId);

                $productBuilder->update();
            }
        } catch (MoloniProductException $e) {
            Logs::addErrorLog([$e->getMessage(), $e->getIdentifiers()], $e->getData());
        }
    }

    private function shouldExecuteHandle(): bool
    {
        if ($this->productId < 1) {
            return false;
        }

        if ((int)Settings::get('addProductsToPrestashop') === Boolean::NO) {
            return false;
        }

        return $this->isAuthenticated();
    }
}
