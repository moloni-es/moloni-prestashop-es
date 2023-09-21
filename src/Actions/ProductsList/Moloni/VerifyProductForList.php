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

namespace Moloni\Actions\ProductsList\Moloni;

use Moloni\Enums\Boolean;
use Moloni\Helpers\Stock;
use Moloni\Tools\Settings;
use Product;
use Combination;
use Configuration;
use StockAvailable;
use Moloni\Enums\Domains;
use Moloni\Traits\AttributesTrait;
use Moloni\Builders\PrestashopProduct\Helpers\Combinations\FindOrCreateCombination;

class VerifyProductForList
{
    use AttributesTrait;

    private $parsedProduct = [];

    private $moloniProduct;

    private $prestaProduct;

    private $warehouseId;

    private $slug;

    private $psLanguageId;
    private $psManagesStock;

    private $productReferenceFallback;

    public function __construct(array $moloniProduct, int $warehouseId, string $slug)
    {
        $this->moloniProduct = $moloniProduct;
        $this->warehouseId = $warehouseId;
        $this->slug = $slug;

        $this->psLanguageId = Configuration::get('PS_LANG_DEFAULT');
        $this->psManagesStock = Configuration::get('PS_STOCK_MANAGEMENT');
        $this->productReferenceFallback = (int)Settings::get('productReferenceFallback');
    }

    //         PUBLICS         //

    public function run(): void
    {
        $this->parsedProduct = [
            'prestashop_id' => 0,
            'moloni_id' => $this->moloniProduct['productId'],
            'moloni_url' => Domains::MOLONI_AC . '/' . $this->slug . '/productCategories/products/all/' . $this->moloniProduct['productId'],
            'name' => $this->moloniProduct['name'],
            'reference' => $this->moloniProduct['reference'],
            'type' => empty($this->moloniProduct['variants']) ? 'Simple' : 'Variants',
            'uneven_stock' => false,
            'missing_product' => false,
            'notices' => [],
        ];

        $this->findByReference();
        $this->verifyPrestashopProduct();
        $this->compareBothProducts();
    }

    //         PRIVATES         //

    private function verifyPrestashopProduct()
    {
        if (empty($this->prestaProduct)) {
            $this->parsedProduct['notices'][] = ['Product does not exist in current Prestashop store.'];
            $this->parsedProduct['missing_product'] = true;

            return;
        }

        $this->parsedProduct['prestashop_id'] = $this->prestaProduct->id;
    }

    private function compareBothProducts()
    {
        if (empty($this->prestaProduct)) {
            return;
        }

        $checkStock = $this->psManagesStock && $this->warehouseId > 0;

        if ($checkStock && $this->moloniProduct['hasStock'] === false) {
            $this->parsedProduct['notices'][] = ['Product without stock active in Moloni.'];

            $checkStock = false;
        }

        if (empty($this->moloniProduct['variants']) && $this->prestaProduct->hasCombinations()) {
            $this->parsedProduct['notices'][] = ['Product types do not match.'];

            return;
        }

        if (!empty($this->moloniProduct['variants']) && !$this->prestaProduct->hasCombinations()) {
            $this->parsedProduct['notices'][] = ['Product types do not match.'];

            return;
        }

        if (empty($this->moloniProduct['variants'])) {
            if ($checkStock) {
                $this->checkSimpleStock();
            }
        } else {
            foreach ($this->moloniProduct['variants'] as $variant) {
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
                    $this->checkVariantStock($variant, $combination);
                }
            }
        }
    }

    //         FETCH         //

    private function findByReference()
    {
        $productId = (int)Product::getIdByReference($this->moloniProduct['reference']);

        if (!empty($productId)) {
            $this->prestaProduct = new Product($productId, true, $this->psLanguageId);
            return;
        }

        if ($this->productReferenceFallback === Boolean::YES && is_numeric($this->moloniProduct['reference'])) {
            $tryAndMatch = new Product((int)$this->moloniProduct['reference'], true, $this->psLanguageId);

            if (!empty($tryAndMatch->id)) {
                $this->prestaProduct = $tryAndMatch;
            }
        }
    }


    //         AUXILIARY         //


    private function checkVariantStock(array $variant, Combination $combination)
    {
        $moloniProductStock = Stock::getMoloniStock($variant, $this->warehouseId);
        $prestashopStock = (float)StockAvailable::getQuantityAvailableByProduct(
            $combination->id_product,
            $combination->id
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
        $moloniProductStock = Stock::getMoloniStock($this->moloniProduct, $this->warehouseId);

        if ($prestashopStock !== $moloniProductStock) {
            $this->parsedProduct['notices'][] = [
                "Product stock do not match (Moloni: {0}, Prestashop: {1}).",
                ['{0}' => $moloniProductStock, '{1}' => $prestashopStock]
            ];

            $this->parsedProduct['uneven_stock'] = true;
        }
    }

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

    //         GETS         //

    public function getParsedProduct(): array
    {
        return $this->parsedProduct;
    }
}
