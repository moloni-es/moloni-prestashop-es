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
use Moloni\Traits\AttributesTrait;
use Moloni\Enums\Domains;
use Moloni\Enums\Boolean;
use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Builders\PrestashopProduct\Helpers\ParseMoloniStock;
use Moloni\Builders\PrestashopProduct\Helpers\Combinations\FindOrCreateCombination;

class VerifyProductForList
{
    use AttributesTrait;

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

        if ((int)$this->moloniProduct['visible'] === Boolean::NO) {
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
        $checkStock = $this->warehouseId > 0;
        $checkCombinations = true;

        if (empty($this->moloniProduct)) {
            return;
        }

        if (empty($this->moloniProduct['variants']) && $this->prestaProduct->hasCombinations()) {
            $this->parsedProduct['notices'][] = ['Product types do not match.'];

            $checkStock = false;
            $checkCombinations = false;
        }

        if (!empty($this->moloniProduct['variants']) && !$this->prestaProduct->hasCombinations()) {
            $this->parsedProduct['notices'][] = ['Product types do not match.'];

            $checkStock = false;
        }

        if ($this->moloniProduct['hasStock'] === false) {
            $this->parsedProduct['notices'][] = ['Product without stock active in Moloni.'];

            $checkStock = false;
        }

        if ($this->prestaProduct->hasCombinations()) {
            if (!$checkCombinations) {
                return;
            }

            foreach ($this->moloniProduct['variants'] as $variant) {
                /** Invisible products are skipped */
                if ((int)$variant['visible'] === Boolean::NO) {
                    continue;
                }

                /** Find combination based on Moloni variant */
                $combination = (new FindOrCreateCombination(
                    (int)$variant['productId'],
                    $this->prestaProduct,
                    $variant['reference'],
                    $this->getAttributes($variant)
                ))->handle();

                /** Couldn´t match variant to any existing combination */
                if (empty($combination->id)) {
                    $this->parsedProduct['notices'][] = [
                        'Combination not found ({0}).', ['{0}' => $variant['reference']]
                    ];

                    continue;
                }

                if ($checkStock) {
                    $this->checkVariantStock($variant, $combination->id);
                }
            }
        } else {
            if ($checkStock) {
                $this->checkSimpleStock();
            }
        }
    }

    private function checkVariantStock($variant, $combinationId)
    {
        $moloniProductStock = (new ParseMoloniStock($variant, $this->warehouseId))->getStock();
        $prestashopStock = (float)StockAvailable::getQuantityAvailableByProduct(
            $this->prestaProduct->id,
            $combinationId
        );

        if ($prestashopStock !== $moloniProductStock) {
            $this->parsedProduct['notices'][] = [
                "Product combination stock do not match (Moloni: {0}, Prestashop: {1}).",
                ['{0}' => $moloniProductStock, '{1}' => $prestashopStock]
            ];

            $this->parsedProduct['uneven_stock'] = true;
        }
    }

    private function checkSimpleStock()
    {
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

    /**
     * Get product variant attributes
     *
     * @param array $moloniVariant
     *
     * @return array
     */
    private function getAttributes(array $moloniVariant): array
    {
        $attributes = [];

        foreach ($moloniVariant['propertyPairs'] as $propertyPair) {
            $groupName = $propertyPair['property']['name'];
            $attributeName = $propertyPair['propertyValue']['value'];

            $groupId = $this->getAttributeGroupByName($groupName);
            $attributeId = $this->getAttributeByName($attributeName, $groupId);

            $attributes[] = $attributeId;
        }

        return $attributes;
    }

    private function findByReference()
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
