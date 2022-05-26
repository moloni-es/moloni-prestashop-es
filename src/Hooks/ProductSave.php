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

namespace Moloni\Hooks;

use Moloni\Builders\MoloniProductFromId;
use Moloni\Enums\Boolean;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Helpers\Settings;
use Moloni\Helpers\SyncLogs;

class ProductSave extends AbstractHookAction
{
    private $productId;

    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    public function handle(): void
    {
        if (!$this->shouldExecuteHandle()) {
            return;
        }

        try {
            SyncLogs::productAddTimeout($this->productId);

            $productBuilder = new MoloniProductFromId($this->productId);
            $productBuilder->search();

            if ($productBuilder->getProductId() !== 0) {
                if ((int)Settings::get('updateProductsToMoloni') === Boolean::YES) {
                    $productBuilder->update();
                }

                if ((int)Settings::get('syncStockToMoloni') === Boolean::YES) {
                    $productBuilder->updateStock();
                }
            } elseif ((int)Settings::get('addProductsToMoloni') === Boolean::YES) {
                $productBuilder->insert();
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

        if ((int)Settings::get('addProductsToMoloni') === Boolean::NO &&
            (int)Settings::get('updateProductsToMoloni') === Boolean::NO &&
            (int)Settings::get('syncStockToMoloni') === Boolean::NO) {
            return false;
        }

        if (SyncLogs::productHasTimeout($this->productId)) {
            return false;
        }

        return $this->isAuthenticated();
    }
}
