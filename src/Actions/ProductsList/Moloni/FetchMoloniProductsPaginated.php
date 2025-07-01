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

namespace Moloni\Actions\ProductsList\Moloni;

use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Tools\Settings;
use Moloni\Traits\AttributesTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

class FetchMoloniProductsPaginated
{
    use AttributesTrait;

    private $page;
    private $filters;
    private $products = [];

    private $paginator = [];
    private $itemsPerPage = 20;

    private $totalResults = 0;
    private $totalProducts = [];

    private $warehouseId;

    public function __construct(int $page, ?array $filters = [])
    {
        $this->page = $page;
        $this->filters = $filters;

        $this->warehouseId = (int) Settings::get('syncStockToPrestashopWarehouse');
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

        if (empty($this->totalProducts)) {
            return;
        }

        foreach ($this->totalProducts as $moloniProduct) {
            $service = new VerifyProductForList($moloniProduct, $this->warehouseId, $slug);
            $service->run();

            $this->products[] = $service->getParsedProduct();
        }

        $this->paginator = [
            'currentPage' => $this->page,
            'numberOfPages' => empty($this->totalResults) ? 1 : ceil($this->totalResults / $this->itemsPerPage),
        ];
    }

    //         REQUESTS         //

    private function fetchProducts()
    {
        $props = [
            'options' => [
                'order' => [
                    'field' => 'reference',
                    'sort' => 'ASC',
                ],
                'filter' => [
                    [
                        'field' => 'visible',
                        'comparison' => 'eq',
                        'value' => '1',
                    ],
                ],
                'pagination' => [
                    'page' => $this->page,
                    'qty' => $this->itemsPerPage,
                ],
            ],
        ];

        if (!empty($this->filters['name'])) {
            $props['options']['search'] = [
                'field' => 'name',
                'value' => $this->filters['name'],
            ];
        }

        if (!empty($this->filters['reference'])) {
            $props['options']['filter'][] = [
                'field' => 'reference',
                'comparison' => 'eq',
                'value' => $this->filters['reference'],
            ];
        }

        try {
            $query = MoloniApiClient::products()->queryProducts($props, true);

            $this->totalResults = (int) ($query['data']['products']['options']['pagination']['count'] ?? 0);
            $this->totalProducts = $query['data']['products']['data'] ?? [];
        } catch (MoloniApiException $e) {
            $this->totalResults = 0;
            $this->totalProducts = [];
        }
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
