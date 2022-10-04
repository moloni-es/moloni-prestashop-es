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

declare(strict_types=1);

namespace Moloni\Controller\Admin\Products;

use Tools;
use Product;
use Configuration;
use Moloni\Api\MoloniApiClient;
use Moloni\Builders\PrestashopProductSimple;
use Moloni\Builders\PrestashopProductWithCombinations;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Builders\MoloniProductSimple;
use Moloni\Builders\MoloniProductWithVariants;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Repository\ProductsRepository;
use Moloni\Tools\SyncLogs;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Enums\MoloniRoutes;
use Moloni\Tools\Settings;
use Moloni\Actions\ProductsList\VerifyProductForList;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Products extends MoloniController
{
    public function home(): Response
    {
        $page = (int)Tools::getValue('page', 1);
        $filters = Tools::getValue('filters', []);
        $productsArray = [];

        /** @var ProductsRepository $repository */
        $repository = $this->get('moloni.repository.products');

        ['products' => $products, 'paginator' => $paginator] = $repository->getProductsPaginated(
            $page,
            $this->getContextLangId(),
            $this->getContextShopId(),
            $filters
        );

        try {
            $slug = MoloniApiClient::companies()->queryCompany()['slug'];
        } catch (MoloniApiException $e) {
            $slug = '';
        }

        $warehouseId = (int)Settings::get('syncStockToPrestashopWarehouse');

        foreach ($products as $product) {
            $obj = new Product($product['id_product'], false, $this->getContextLangId());

            $productsArray[] = (new VerifyProductForList($obj, $warehouseId, $slug))->getParsedProduct();
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/products/Products.twig',
            [
                'productsArray' => $productsArray,
                'filters' => $filters,
                'paginator' => $paginator,
                'companyName' => Settings::get('companyName'),
                'productsImportStockRoute' => MoloniRoutes::PRODUCTS_IMPORT_STOCK,
                'productsExportProductRoute' => MoloniRoutes::PRODUCTS_EXPORT_PRODUCT,
                'toolsRoute' => MoloniRoutes::TOOLS,
                'thisRoute' => MoloniRoutes::PRODUCTS,
            ]
        );
    }

    public function importStock(): Response
    {
        $productId = (int)Tools::getValue('product_id', 0);

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
            } else {
                throw new MoloniProductException('Product does not exist', null, [$productId]);
            }

            $response = $this->getCommonResponse($prestaProductId);
        } catch (MoloniProductException | MoloniApiException $e) {
            $response = [
                'valid' => 0,
                'message' => $this->trans($e->getMessage(), 'Modules.Molonies.Errors'),
                'result' => $e->getData(),
                'productRow' => '',
            ];
        }

        return new Response(json_encode($response));
    }

    public function exportProduct(): Response
    {
        $productId = (int)Tools::getValue('product_id', 0);

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
                $productBuilder->insert();
            } else {
                throw new MoloniProductException('Product already exists', null, [$productId]);
            }

            $response = $this->getCommonResponse($productId);
        } catch (MoloniProductException $e) {
            $response = [
                'valid' => 0,
                'message' => $this->trans($e->getMessage(), 'Modules.Molonies.Errors'),
                'result' => $e->getData(),
                'productRow' => '',
            ];
        }

        return new Response(json_encode($response));
    }

    private function getCommonResponse($prestaProductId): array
    {
        try {
            $slug = MoloniApiClient::companies()->queryCompany()['slug'];
        } catch (MoloniApiException $e) {
            $slug = '';
        }

        $warehouseId = (int)Settings::get('syncStockToPrestashopWarehouse');

        $obj = new Product($prestaProductId, false, $this->getContextLangId());
        $product = (new VerifyProductForList($obj, $warehouseId, $slug))->getParsedProduct();

        return [
            'valid' => 1,
            'message' => '',
            'result' => '',
            'productRow' => $this->renderView(
                '@Modules/molonies/views/templates/admin/products/blocks/TableBodyRow.twig',
                [
                    'product' => $product,
                ]
            )
        ];
    }
}
