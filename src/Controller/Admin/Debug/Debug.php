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

use Country;
use TaxRulesGroup;
use Tools;
use Product;
use Configuration;
use Moloni\Tools\SyncLogs;
use Moloni\Enums\MoloniRoutes;
use Moloni\Api\MoloniApiClient;
use Moloni\Tools\ProductAssociations;
use Moloni\Entity\MoloniOrderDocuments;
use Moloni\Entity\MoloniProductAssociations;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Builders\MoloniProductSimple;
use Moloni\Builders\MoloniProductWithVariants;
use Moloni\Builders\PrestashopProductSimple;
use Moloni\Builders\PrestashopProductWithCombinations;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Repository\MoloniOrderDocumentsRepository;
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
    public function home(?array $data = []): Response
    {
        return $this->render(
            '@Modules/molonies/views/templates/admin/debug/Debug.twig',
            [
                'multipurpose' => MoloniRoutes::DEBUG_MULTIPURPOSE,
                'deleteOrderDocument' => MoloniRoutes::DEBUG_DELETE_ORDER_DOCUMENT,
                'updateStockFromMoloni' => MoloniRoutes::DEBUG_UPDATE_STOCK_FROM_MOLONI,
                'updateProductFromMoloni' => MoloniRoutes::DEBUG_UPDATE_PRODUCT_FROM_MOLONI,
                'insertProductFromMoloni' => MoloniRoutes::DEBUG_INSERT_PRODUCT_FROM_MOLONI,
                'updateStockFromPrestashop' => MoloniRoutes::DEBUG_UPDATE_STOCK_FROM_PRESTASHOP,
                'updateProductFromPrestashop' => MoloniRoutes::DEBUG_UPDATE_PRODUCT_FROM_PRESTASHOP,
                'insertProductFromPrestashop' => MoloniRoutes::DEBUG_INSERT_PRODUCT_FROM_PRESTASHOP,
                'dumpProductAssociations' => MoloniRoutes::DEBUG_DUMP_PRODUCT_ASSOCIATIONS,
                'orders' => MoloniRoutes::ORDERS,
                'data' => $data,
            ]
        );
    }

    /**
     * Multipurpose action to debug user problems
     *
     * @return Response
     */
    public function multipurpose(): Response
    {
        $taxRulesGroupId = 0;

        $fiscalZone = 'es';
        $countryId = Country::getByIso($fiscalZone);
        $value = 21.0;

        $taxes = array_reverse(TaxRulesGroup::getAssociatedTaxRatesByIdCountry($countryId), true);

        foreach ($taxes as $id => $tax) {
            if ($value === (float)$tax) {
                $taxRulesGroupId = $id;

                break;
            }
        }

        $response = [
            'valid' => 1,
            'result' => [
                'fiscalZone' => $fiscalZone,
                'countryId' => $countryId,
                'value' => $value,
                'taxes' => $taxes,
                'taxRulesGroupId' => $taxRulesGroupId,
            ],
        ];

        return $this->home($response);
    }

    /**
     * Checks if attributes with all upper-case letters are being used
     *
     * @return Response
     */
    public function deleteOrderDocument(): Response
    {
        $orderId = (int)Tools::getValue('order_id', 0);

        /** @var MoloniOrderDocumentsRepository $repository */
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(MoloniOrderDocuments::class);

        $repository->deleteByOrderId($orderId);

        $response = [
            'valid' => 1,
            'result' => 'Done :)'
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
                $productBuilder->insert();
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

    /**
     * Dump product associations table
     */
    public function dumpProductAssociations(): Response
    {
        $results = [];
        $productId = (int)Tools::getValue('product_id', 0);
        $type = Tools::getValue('type_id', '');

        switch ($type) {
            case 'MOLONI_PRODUCT':
                $result = ProductAssociations::findByMoloniParentId($productId);
                break;
            case 'MOLONI_VARIANT':
                $result = ProductAssociations::findByMoloniVariantId($productId);
                break;
            case 'PRESTASHOP_PRODUCT':
                $result = ProductAssociations::findByPrestashopProductId($productId);
                break;
            case 'PRESTASHOP_COMBINATION':
                $result = ProductAssociations::findByPrestashopCombinationId($productId);
                break;
            case 'ALL':
            default:
                $result = ProductAssociations::findAll();
                break;
        }

        /** @var MoloniProductAssociations[] $result */
        foreach ($result as $association) {
            $results[] = $association->toArray();
        }

        $response = [
            'valid' => 1,
            'result' => json_encode($results),
        ];

        return $this->home($response);
    }
}
