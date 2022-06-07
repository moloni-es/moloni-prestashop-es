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

use Moloni\Builders\MoloniProductWithVariants;
use Product;
use Configuration;
use Moloni\Enums\Boolean;
use Moloni\Builders\MoloniProductSimple;
use Moloni\Helpers\Logs;
use Moloni\Helpers\Settings;
use Moloni\Helpers\SyncLogs;
use Moloni\Exceptions\Product\MoloniProductException;

class ProductSave extends AbstractHookAction
{
    private $productId;

    public function __construct(int $productId)
    {
        $this->productId = $productId;

        $this->handle();
    }

    private function handle(): void
    {
        if (!$this->shouldExecuteHandle()) {
            return;
        }

        try {
            SyncLogs::productAddTimeout($this->productId);
            $product = new Product($this->productId, true, Configuration::get('PS_LANG_DEFAULT'));

            if ($product->product_type === 'combinations') {
                $productBuilder = new MoloniProductWithVariants($product);
            } else {
                $productBuilder = new MoloniProductSimple($product);
            }

            $productBuilder->search();

            if ($productBuilder->getMoloniProductId() !== 0) {
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
