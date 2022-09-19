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

namespace Moloni\Controller\Admin\Debug;

use Tools;
use Product;
use Configuration;
use Moloni\Tools\SyncLogs;
use Moloni\Enums\MoloniRoutes;
use Moloni\Api\MoloniApiClient;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Builders\MoloniProductSimple;
use Moloni\Builders\MoloniProductWithVariants;
use Moloni\Builders\PrestashopProductSimple;
use Moloni\Builders\PrestashopProductWithCombinations;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Debug extends MoloniController
{
    /**
     * Home view for debug actions
     *
     * @param array|null $data Data
     *
     * @return Response
     */
    public function home(array $data = []): Response
    {
        return $this->render(
            '@Modules/molonies/views/templates/admin/debug/Debug.twig',
            [
                'checkAttributesValidity' => MoloniRoutes::DEBUG_CHECK_ATTRIBUTES,
                'updateStockFromMoloni' => MoloniRoutes::DEBUG_UPDATE_STOCK_FROM_MOLONI,
                'updateProductFromMoloni' => MoloniRoutes::DEBUG_UPDATE_PRODUCT_FROM_MOLONI,
                'insertProductFromMoloni' => MoloniRoutes::DEBUG_INSERT_PRODUCT_FROM_MOLONI,
                'updateStockFromPrestashop' => MoloniRoutes::DEBUG_UPDATE_STOCK_FROM_PRESTASHOP,
                'updateProductFromPrestashop' => MoloniRoutes::DEBUG_UPDATE_PRODUCT_FROM_PRESTASHOP,
                'insertProductFromPrestashop' => MoloniRoutes::DEBUG_INSERT_PRODUCT_FROM_PRESTASHOP,
                'orders' => MoloniRoutes::ORDERS,
                'data' => $data,
            ]
        );
    }

    /**
     * Checks if attributes with all upper-case letters are being used
     *
     * @return Response
     */
    public function checkAttributesValidity(): Response
    {
        $products = Product::getProducts(
            Configuration::get('PS_LANG_DEFAULT'),
            0,
            700,
            'id_product',
            'DESC',
            false,
            true
        );

        $productsToVerify = [];

        foreach ($products as $product) {
            $productId = (int) $product['id_product'];

            // We only want to verify products with attributes
            if ($product['product_type'] !== 'combinations') {
                continue;
            }

            $productAttributtes = Product::getAttributesInformationsByProduct($productId);

            if (!empty($productAttributtes)) {
                foreach ($productAttributtes as $productAttributte) {
                    $attributeName = $productAttributte['attribute'];

                    // Remove all numbers from name
                    $attributeName = preg_replace('/[0-9]+/', '', $attributeName);

                    // Check if the name is all upper-cased. If so flag this product to verify
                    if (!empty($attributeName) && ctype_upper($attributeName)) {
                        // Add product to list to verify manually
                        $productsToVerify[$productId] = $productAttributtes;

                        break;
                    }
                }
            }
        }

        $response = [
            'valid' => 1,
            'result' => [
                'processed_products' => count($products),
                'flaged_products' => $productsToVerify,
            ],
        ];

        return $this->home($response);
    }

    /**
     * Update prestashop product stock based on Moloni
     */
    public function updateStockFromMoloni(): Response
    {
        $productId = (int) Tools::getValue('product_id', 0);

        $variables = [
            'productId' => $productId,
        ];

        SyncLogs::moloniProductAddTimeout($productId);

        try {
            $query = MoloniApiClient::products()->queryProduct($variables);

            $moloniProduct = $query['data']['product']['data'] ?? [];

            if (empty($moloniProduct)) {
                throw new MoloniProductException('Product not found', null, $variables);
            }

            if (empty($moloniProduct['variants'])) {
                $productBuilder = new PrestashopProductSimple($moloniProduct);
            } else {
                $productBuilder = new PrestashopProductWithCombinations($moloniProduct);
            }

            $prestaProductId = $productBuilder->getPrestashopProductId();

            if ($prestaProductId > 0) {
                SyncLogs::prestashopProductAddTimeout($prestaProductId);

                $productBuilder->updateStock();
            }

            $response = [
                'valid' => 1,
                'result' => 'Done :)',
            ];
        } catch (MoloniProductException|MoloniApiException $e) {
            $response = [
                'valid' => 0,
                'message' => $e->getMessage(),
                'result' => $e->getData(),
            ];
        }

        return $this->home($response);
    }

    /**
     * Update prestashop product stock based on Moloni
     */
    public function updateProductFromMoloni(): Response
    {
        $productId = (int) Tools::getValue('product_id', 0);

        $variables = [
            'productId' => $productId,
        ];

        SyncLogs::moloniProductAddTimeout($productId);

        try {
            $query = MoloniApiClient::products()->queryProduct($variables);

            $moloniProduct = $query['data']['product']['data'] ?? [];

            if (empty($moloniProduct)) {
                throw new MoloniProductException('Product not found', null, $variables);
            }

            if (empty($moloniProduct['variants'])) {
                $productBuilder = new PrestashopProductSimple($moloniProduct);
            } else {
                $productBuilder = new PrestashopProductWithCombinations($moloniProduct);
            }

            $prestaProductId = $productBuilder->getPrestashopProductId();

            if ($prestaProductId > 0) {
                SyncLogs::prestashopProductAddTimeout($prestaProductId);

                $productBuilder->update();
            } else {
                throw new MoloniProductException('Product does not exist', null, ['prestashopId' => $prestaProductId, 'moloniId' => $productId]);
            }

            $response = [
                'valid' => 1,
                'result' => 'Done :)',
            ];
        } catch (MoloniProductException|MoloniApiException $e) {
            $response = [
                'valid' => 0,
                'message' => $e->getMessage(),
                'result' => $e->getData(),
            ];
        }

        return $this->home($response);
    }

    /**
     * Update prestashop product stock based on Moloni
     */
    public function insertProductFromMoloni(): Response
    {
        $productId = (int) Tools::getValue('product_id', 0);

        $variables = [
            'productId' => $productId,
        ];

        SyncLogs::moloniProductAddTimeout($productId);

        try {
            $query = MoloniApiClient::products()->queryProduct($variables);

            $moloniProduct = $query['data']['product']['data'] ?? [];

            if (empty($moloniProduct)) {
                throw new MoloniProductException('Product not found', null, $variables);
            }

            if (empty($moloniProduct['variants'])) {
                $productBuilder = new PrestashopProductSimple($moloniProduct);
            } else {
                $productBuilder = new PrestashopProductWithCombinations($moloniProduct);
            }

            $prestaProductId = $productBuilder->getPrestashopProductId();

            if ($prestaProductId === 0) {
                $productBuilder->insert();
            } else {
                throw new MoloniProductException('Product already exists', null, ['prestashopId' => $prestaProductId, 'moloniId' => $productId]);
            }

            $response = [
                'valid' => 1,
                'result' => 'Done :)',
            ];
        } catch (MoloniProductException|MoloniApiException $e) {
            $response = [
                'valid' => 0,
                'message' => $e->getMessage(),
                'result' => $e->getData(),
            ];
        }

        return $this->home($response);
    }

    /**
     * Update Moloni product stock based on prestashop
     */
    public function updateStockFromPrestashop(): Response
    {
        $productId = (int) Tools::getValue('product_id', 0);

        SyncLogs::prestashopProductAddTimeout($productId);

        try {
            $product = new Product($productId, true, Configuration::get('PS_LANG_DEFAULT'));

            if (empty($product->id)) {
                throw new MoloniProductException('Product not found', null, [$productId]);
            }

            if ($product->product_type === 'combinations' && $product->hasCombinations()) {
                $productBuilder = new MoloniProductWithVariants($product);
            } else {
                $productBuilder = new MoloniProductSimple($product);
            }

            $moloniProductId = $productBuilder->getMoloniProductId();

            if ($moloniProductId > 0) {
                SyncLogs::moloniProductAddTimeout($moloniProductId);

                $productBuilder->updateStock();
            } else {
                throw new MoloniProductException('Product does not exist', null, [$productId]);
            }

            $response = [
                'valid' => 1,
                'result' => 'Done :)',
            ];
        } catch (MoloniProductException $e) {
            $response = [
                'valid' => 0,
                'message' => $e->getMessage(),
                'result' => $e->getData(),
            ];
        }

        return $this->home($response);
    }

    /**
     * Update Moloni product stock based on prestashop
     */
    public function updateProductFromPrestashop(): Response
    {
        $productId = (int) Tools::getValue('product_id', 0);

        SyncLogs::prestashopProductAddTimeout($productId);

        try {
            $product = new Product($productId, true, Configuration::get('PS_LANG_DEFAULT'));

            if (empty($product->id)) {
                throw new MoloniProductException('Product not found', null, [$productId]);
            }

            if ($product->product_type === 'combinations' && $product->hasCombinations()) {
                $productBuilder = new MoloniProductWithVariants($product);
            } else {
                $productBuilder = new MoloniProductSimple($product);
            }

            $moloniProductId = $productBuilder->getMoloniProductId();

            if ($moloniProductId > 0) {
                SyncLogs::moloniProductAddTimeout($moloniProductId);

                $productBuilder->update();
            } else {
                throw new MoloniProductException('Product does not exist', null, [$productId]);
            }

            $response = [
                'valid' => 1,
                'result' => 'Done :)',
            ];
        } catch (MoloniProductException $e) {
            $response = [
                'valid' => 0,
                'message' => $e->getMessage(),
                'result' => $e->getData(),
            ];
        }

        return $this->home($response);
    }

    /**
     * Update Moloni product stock based on prestashop
     */
    public function insertProductFromPrestashop(): Response
    {
        $productId = (int) Tools::getValue('product_id', 0);

        SyncLogs::prestashopProductAddTimeout($productId);

        try {
            $product = new Product($productId, true, Configuration::get('PS_LANG_DEFAULT'));

            if (empty($product->id)) {
                throw new MoloniProductException('Product not found', null, [$productId]);
            }

            if ($product->product_type === 'combinations' && $product->hasCombinations()) {
                $productBuilder = new MoloniProductWithVariants($product);
            } else {
                $productBuilder = new MoloniProductSimple($product);
            }

            if ($productBuilder->getMoloniProductId() === 0) {
                $productBuilder->update();
            } else {
                throw new MoloniProductException('Product already exists', null, [$productId]);
            }

            $response = [
                'valid' => 1,
                'result' => 'Done :)',
            ];
        } catch (MoloniProductException $e) {
            $response = [
                'valid' => 0,
                'message' => $e->getMessage(),
                'result' => $e->getData(),
            ];
        }

        return $this->home($response);
    }
}
