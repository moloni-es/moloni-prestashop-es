<?php
/**
 * 2023 - Moloni.com
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

namespace Moloni\Actions\ProductsList\Prestashop;

use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Repository\ProductsRepository;
use Moloni\Tools\Settings;
use Product;

class FetchPrestashopProductsPaginated
{
    private $repository;

    private $page;
    private $filters = [];

    private $products = [];
    private $paginator = [];

    private $totalProducts = [];

    private $warehouseId;

    private $psShopId;
    private $psLanguageId;

    public function __construct(int $page, ProductsRepository $repository, int $psLanguageId, int $psShopId)
    {
        $this->page = $page;
        $this->repository = $repository;

        $this->psShopId = $psShopId;
        $this->psLanguageId = $psLanguageId;

        $this->warehouseId = $this->getWarehouse();
    }

    //         PUBLICS         //

    public function run()
    {
        try {
            $slug = MoloniApiClient::companies()->queryCompany()['slug'];
        } catch (MoloniApiException $e) {
            $slug = '';
        }

        $this->fetchProducts();

        foreach ($this->totalProducts as $product) {
            $ps = new Product($product['id_product'], false, $this->psLanguageId);

            $service = new VerifyProductForList($ps, $this->warehouseId, $slug);
            $service->run();

            $this->products[] = $service->getParsedProduct();
        }
    }

    //         PRIVATES         //

    private function getWarehouse(): int
    {
        $warehouseId = (int)Settings::get('syncStockToMoloniWarehouse');

        if ($warehouseId > 1) {
            return $warehouseId;
        }

        $params = [
            'options' => [
                'filter' => [
                    'field' => 'isDefault',
                    'comparison' => 'eq',
                    'value' => '1',
                ],
            ],
        ];

        try {
            $query = MoloniApiClient::warehouses()->queryWarehouses($params);

            if (!empty($query)) {
                return (int)$query[0]['warehouseId'];
            }
        } catch (MoloniApiException $e) {
        }

        return 0;
    }

    //         QUERIES         //

    private function fetchProducts()
    {
        ['products' => $this->totalProducts, 'paginator' => $this->paginator] = $this->repository->getProductsPaginated(
            $this->page,
            $this->psLanguageId,
            $this->psShopId,
            $this->filters
        );
    }

    //         SETS         //

    public function setFilters(?array $filters = []): void
    {
        $this->filters = $filters;
    }

    //         GETS         //

    public function getPaginator(): array
    {
        if (empty($this->paginator)) {
            $this->paginator = [
                'currentPage' => 1,
                'numberOfPages' => 1,
            ];
        }

        return $this->paginator;
    }

    public function getProducts(): array
    {
        return $this->products;
    }
}
