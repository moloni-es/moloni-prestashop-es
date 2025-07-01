<?php

/**
 * 2025 - Moloni.com
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

namespace Moloni\Hooks;

use Moloni\Api\MoloniApi;
use Moloni\Builders\MoloniProductSimple;
use Moloni\Builders\MoloniProductWithVariants;
use Moloni\Enums\Boolean;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Tools\Logs;
use Moloni\Tools\Settings;
use Moloni\Tools\SyncLogs;
use Product;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductStockUpdate extends AbstractHookAction
{
    private $productId;
    private $variantId;
    private $newQty;

    public function __construct(int $productId, int $variantId, float $newQty)
    {
        $this->productId = $productId;
        $this->variantId = $variantId;
        $this->newQty = $newQty;

        $this->handle();
    }

    private function handle(): void
    {
        if (!$this->shouldExecuteHandle()) {
            return;
        }

        try {
            SyncLogs::prestashopProductAddTimeout($this->productId);
            $product = new \Product($this->productId, true, \Configuration::get('PS_LANG_DEFAULT'));

            if ($product->product_type === 'combinations' && $product->hasCombinations()) {
                if (!$this->variantId) {
                    return;
                }

                $productBuilder = new MoloniProductWithVariants($product);
                if ($productBuilder->getMoloniProductId() !== 0) {
                    $productBuilder->updateStock($this->variantId, $this->newQty);
                }
            } else {
                $productBuilder = new MoloniProductSimple($product);
                if ($productBuilder->getMoloniProductId() !== 0) {
                    $productBuilder->setStock($this->newQty);
                    $productBuilder->updateStock();
                }
            }
        } catch (MoloniProductException $e) {
            Logs::addErrorLog(
                [['Error saving Moloni product'], [$e->getMessage(), $e->getIdentifiers()]],
                $e->getData()
            );
        }
    }

    /**
     * Let this conditions be the same to allow for updates or inserts if we are inserting or updating a product
     */
    private function shouldExecuteHandle(): bool
    {
        if ($this->productId < 1) {
            return false;
        }

        if ((int) Settings::get('addProductsToMoloni') === Boolean::NO
            && (int) Settings::get('updateProductsToMoloni') === Boolean::NO) {
            return false;
        }

        if ((int) Settings::get('syncStockToMoloni') === Boolean::NO) {
            return false;
        }

        if (SyncLogs::prestashopProductStockHasTimeout($this->productId)) {
            return false;
        }

        return MoloniApi::hasValidAuthentication();
    }
}
