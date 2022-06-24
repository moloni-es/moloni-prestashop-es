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

use Configuration;
use Moloni\Builders\MoloniProductSimple;
use Moloni\Builders\MoloniProductWithVariants;
use Moloni\Enums\Boolean;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Tools\Logs;
use Moloni\Tools\Settings;
use Moloni\Tools\SyncLogs;
use Product;

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
            SyncLogs::prestashopProductAddTimeout($this->productId);
            $product = new Product($this->productId, true, Configuration::get('PS_LANG_DEFAULT'));

            if ($product->product_type === 'combinations') {
                $productBuilder = new MoloniProductWithVariants($product);
            } else {
                $productBuilder = new MoloniProductSimple($product);
            }

            if ($productBuilder->getMoloniProductId() !== 0) {
                SyncLogs::moloniProductAddTimeout($productBuilder->getMoloniProductId());

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
            Logs::addErrorLog([['Error saving Moloni product'], [$e->getMessage(), $e->getIdentifiers()]], $e->getData());
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

        if (SyncLogs::prestashopProductHasTimeout($this->productId)) {
            return false;
        }

        return $this->isAuthenticated();
    }
}
