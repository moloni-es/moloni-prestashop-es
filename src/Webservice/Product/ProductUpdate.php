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

use Moloni\Builders\PrestashopProductSimple;
use Moloni\Builders\PrestashopProductWithCombinations;
use Moloni\Enums\Boolean;
use Moloni\Enums\StockSync;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Tools\Logs;
use Moloni\Tools\Settings;
use Moloni\Tools\SyncLogs;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductUpdate extends AbstractWebserviceAction
{
    public function handle(): void
    {
        if (!$this->shouldExecuteHandle()) {
            return;
        }

        try {
            $product = $this->fetchProductFromMoloni($this->productId);

            // Should not sync child product by itself
            if (!empty($product['parent'])) {
                return;
            }

            // Should not sync ignored references
            if (StockSync::isIgnoredReference($product['reference'])) {
                return;
            }

            if (empty($product['variants'])) {
                $productBuilder = new PrestashopProductSimple($product);
            } else {
                $productBuilder = new PrestashopProductWithCombinations($product);
            }

            $prestaProductId = $productBuilder->getPrestashopProductId();

            if ($prestaProductId > 0) {
                if (!SyncLogs::prestashopProductHasTimeout($prestaProductId)) {
                    SyncLogs::moloniProductAddTimeout($this->productId);
                    SyncLogs::prestashopProductAddTimeout($prestaProductId);

                    $productBuilder->update();
                }
            }
        } catch (MoloniProductException $e) {
            Logs::addErrorLog([['Error saving Prestashop product'], [$e->getMessage(), $e->getIdentifiers()]], $e->getData());
        }
    }

    private function shouldExecuteHandle(): bool
    {
        if ($this->productId < 1) {
            return false;
        }

        if ((int)Settings::get('updateProductsToPrestashop') === Boolean::NO) {
            return false;
        }

        if (SyncLogs::moloniProductHasTimeout($this->productId)) {
            return false;
        }

        return $this->isAuthenticated();
    }
}
