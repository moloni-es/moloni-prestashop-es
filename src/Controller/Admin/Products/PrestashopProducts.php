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

use Configuration;
use Moloni\Actions\ProductsList\Prestashop\FetchPrestashopProductsPaginated;
use Moloni\Actions\ProductsList\Prestashop\VerifyProductForList;
use Moloni\Api\MoloniApiClient;
use Moloni\Builders\MoloniProductSimple;
use Moloni\Builders\MoloniProductWithVariants;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Enums\MoloniRoutes;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Helpers\Warehouse;
use Moloni\Repository\ProductsRepository;
use Moloni\Tools\Settings;
use Moloni\Tools\SyncLogs;
use Product;
use Symfony\Component\HttpFoundation\Response;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestashopProducts extends MoloniController
{
    public function home(): Response
    {
        $page = (int)Tools::getValue('page', 1);
        $filters = Tools::getValue('filters', []);

        /** @var ProductsRepository $repository */
        $repository = $this->get('moloni.repository.products');

        $service = new FetchPrestashopProductsPaginated($page, $repository, $this->getContextLangId(), $this->getContextShopId());
        $service->setFilters($filters);
        $service->run();

        return $this->render(
            '@Modules/molonies/views/templates/admin/products/prestashop/Products.twig',
            [
                'productsArray' => $service->getProducts(),
                'filters' => $filters,
                'paginator' => $service->getPaginator(),
                'companyName' => Settings::get('companyName'),
                'productReferenceFallbackActive' => (int)Settings::get('productReferenceFallback'),
                'exportStockRoute' => MoloniRoutes::PRESTASHOP_PRODUCTS_EXPORT_STOCK,
                'exportProductRoute' => MoloniRoutes::PRESTASHOP_PRODUCTS_EXPORT_PRODUCT,
                'toolsRoute' => MoloniRoutes::TOOLS,
                'thisRoute' => MoloniRoutes::PRESTASHOP_PRODUCTS,
            ]
        );
    }

    public function exportStock(): Response
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

            if ($productBuilder->getMoloniProductId() > 0) {
                $productBuilder->updateStock();
            } else {
                throw new MoloniProductException('Product does not exist in Moloni', null, [$productId]);
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

    private function getWarehouse(): int
    {
        $warehouseId = (int)Settings::get('syncStockToMoloniWarehouse');

        if ($warehouseId > 1) {
            return $warehouseId;
        }

        return Warehouse::getCompanyDefaultWarehouse();
    }

    private function getCommonResponse($prestaProductId): array
    {
        try {
            $slug = MoloniApiClient::companies()->queryCompany()['slug'];
        } catch (MoloniApiException $e) {
            $slug = '';
        }

        $warehouseId = $this->getWarehouse();

        $ps = new Product($prestaProductId, false, $this->getContextLangId());

        $service = new VerifyProductForList($ps, $warehouseId, $slug);
        $service->run();

        return [
            'valid' => 1,
            'message' => '',
            'result' => '',
            'productRow' => $this->renderView(
                '@Modules/molonies/views/templates/admin/products/prestashop/blocks/TableBodyRow.twig',
                [
                    'product' => $service->getParsedProduct(),
                ]
            ),
        ];
    }
}
