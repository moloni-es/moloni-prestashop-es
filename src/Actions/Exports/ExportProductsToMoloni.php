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

namespace Moloni\Actions\Exports;

use Product;
use Moloni\Tools\Logs;
use Moloni\Tools\SyncLogs;
use Moloni\Builders\MoloniProductSimple;
use Moloni\Builders\MoloniProductWithVariants;
use Moloni\Exceptions\Product\MoloniProductException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ExportProductsToMoloni extends ExportProducts
{
    public function handle(): void
    {
        $start = ($this->page - 1) * $this->itemsPerPage;

        $products = Product::getProducts(
            $this->languageId,
            $start,
            $this->itemsPerPage,
            'id_product',
            'DESC',
            false,
            true
        );

        $this->totalResults = count($products);

        foreach ($products as $productData) {
            if (empty($productData['reference'])) {
                $this->errorProducts[] = [
                    $productData['id_product'] => 'Product has no reference in Prestashop.'
                ];

                continue;
            }

            SyncLogs::prestashopProductAddTimeout((int)$productData['id_product']);

            $product = new Product($productData['id_product'], true, $this->languageId);

            try {
                if ($product->product_type === 'combinations' && $product->hasCombinations()) {
                    $productBuilder = new MoloniProductWithVariants($product);
                } else {
                    $productBuilder = new MoloniProductSimple($product);
                }

                if ($productBuilder->getMoloniProductId() === 0) {
                    $productBuilder->disableLogs();
                    $productBuilder->insert();

                    $this->syncedProducts[] = $product->reference;
                } else {
                    $this->errorProducts[] = [
                        $product->reference => 'Product already exists in Moloni'
                    ];
                }
            } catch (MoloniProductException $e) {
                $this->errorProducts[] = [
                    $product->reference => $e->getData()
                ];
            }
        }

        $logMsg = ['Products export. Part {0}', ['{0}' => $this->page]];
        $logData = [
            'success' => $this->syncedProducts,
            'error' => $this->errorProducts,
        ];

        Logs::addInfoLog($logMsg, $logData);
    }
}
