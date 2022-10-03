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

namespace Moloni\Actions\ProductsList;

use Product;
use StockAvailable;
use Moloni\Enums\Domains;
use Moloni\Enums\Boolean;
use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Builders\PrestashopProduct\Helpers\ParseMoloniStock;

class VerifyProductForList
{
    /**
     * @var array
     */
    private $parsedProduct = [];

    /**
     * @var array
     */
    private $moloniProduct = [];


    /**
     * @var Product
     */
    private $prestaProduct;

    /**
     * @var int
     */
    private $warehouseId;

    /**
     * @var string
     */
    private $slug;

    /**
     * Constructor
     *
     * @param Product $prestaProduct
     * @param int $warehouseId
     * @param string $slug
     */
    public function __construct(Product $prestaProduct, int $warehouseId, string $slug)
    {
        $this->prestaProduct = $prestaProduct;
        $this->warehouseId = $warehouseId;
        $this->slug = $slug;

        $this->run();
    }

    private function run(): void
    {
        $this->parsedProduct['prestashop_id'] = $this->prestaProduct->id;
        $this->parsedProduct['moloni_id'] = 0;
        $this->parsedProduct['name'] = $this->prestaProduct->name;
        $this->parsedProduct['reference'] = $this->prestaProduct->reference;
        $this->parsedProduct['type'] = $this->prestaProduct->hasCombinations() ? 'Combinations' : 'Simple';
        $this->parsedProduct['uneven_stock'] = false;
        $this->parsedProduct['missing_product'] = false;
        $this->parsedProduct['notices'] = [];

        $this->findByReference();
        $this->verifyMoloniProduct();
        $this->compareBothProducts();
    }

    private function verifyMoloniProduct()
    {
        if (empty($this->moloniProduct)) {
            $this->parsedProduct['notices'][] = ['Product does not exist in current Moloni company.'];
            $this->parsedProduct['missing_product'] = true;

            return;
        }

        if ($this->moloniProduct['visible'] === Boolean::NO) {
            $this->parsedProduct['notices'][] = [
                'Product is invisible in Moloni. Cannot be used in document creation.'
            ];
        }

        $this->parsedProduct['moloni_id'] = $this->moloniProduct['productId'];
        $this->parsedProduct['moloni_url'] = Domains::MOLONI_AC
            . '/'
            . $this->slug
            . '/productCategories/products/all/'
            . $this->moloniProduct['productId'];
    }

    private function compareBothProducts()
    {
        $skipStockVerification = false;

        if (empty($this->moloniProduct)) {
            return;
        }

        if (empty($this->moloniProduct['variants']) && $this->prestaProduct->hasCombinations()) {
            $this->parsedProduct['notices'][] = ['Product types do not match.'];

            $skipStockVerification = true;
        }

        if (!empty($this->moloniProduct['variants']) && !$this->prestaProduct->hasCombinations()) {
            $this->parsedProduct['notices'][] = ['Product types do not match.'];

            $skipStockVerification = true;
        }

        if ($this->moloniProduct['hasStock'] === false) {
            $this->parsedProduct['notices'][] = ['Product without stock active in Moloni.'];

            $skipStockVerification = true;
        }

        if (!$skipStockVerification && $this->warehouseId > 0) {
            $prestashopStock = (float)StockAvailable::getQuantityAvailableByProduct($this->prestaProduct->id);
            $moloniProductStock = (new ParseMoloniStock($this->moloniProduct, $this->warehouseId))
                ->getStock();

            if ($prestashopStock !== $moloniProductStock) {
                $this->parsedProduct['notices'][] = [
                    "Product stock do not match (Moloni: {0}, Prestashop: {1}).",
                    ['{0}' => $moloniProductStock, '{1}' => $prestashopStock]
                ];

                $this->parsedProduct['uneven_stock'] = true;
            }
        }
    }

    protected function findByReference()
    {
        $variables = [
            'options' => [
                'filter' => [
                    [
                        'field' => 'visible',
                        'comparison' => 'in',
                        'value' => '[0, 1]'
                    ],
                    [
                        'field' => 'reference',
                        'comparison' => 'eq',
                        'value' => $this->prestaProduct->reference,
                    ],
                ],
            ],
        ];

        try {
            $query = MoloniApiClient::products()
                ->queryProducts($variables);

            if (!empty($query)) {
                $this->moloniProduct = $query[0];
            }
        } catch (MoloniApiException $e) {
            $this->moloniProduct = [];
        }
    }

    public function getParsedProduct(): array
    {
        return $this->parsedProduct;
    }
}
