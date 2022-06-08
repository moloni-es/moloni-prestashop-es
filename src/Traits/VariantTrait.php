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

namespace Moloni\Traits;

use Moloni\Builders\MoloniProduct\ProductVariant;

trait VariantTrait
{
    /**
     * Find variant corresponding builder
     *
     * @param ProductVariant[]|array|null $variantBuilders
     * @param array|null $wantedPropertyPairs
     *
     * @return ProductVariant|null
     */
    private function findBuilder(?array $variantBuilders = [], ?array $wantedPropertyPairs = []): ?ProductVariant
    {
        if (empty($variantBuilders) || empty($wantedPropertyPairs)) {
            return null;
        }

        $result = null;

        $wantedPropertyPairsCount = count($wantedPropertyPairs);

        foreach($variantBuilders as $builder) {
            $builderProperties = $builder->getPropertyPairs();

            if (count($builderProperties) !== $wantedPropertyPairsCount) {
                continue;
            }

            foreach ($wantedPropertyPairs as $wantedPair) {
                $pairFound = false;

                foreach ($builderProperties as $variantPropertyPair) {
                    if ($wantedPair['propertyId'] === $variantPropertyPair['propertyId'] && $wantedPair['propertyValueId'] === $variantPropertyPair['propertyValueId']) {
                        $pairFound = true;
                        break;
                    }
                }

                if (!$pairFound) {
                    continue 2;
                }
            }

            $result = $builder;
            break;
        }

        return $result;
    }

    /**
     * Find builder corresponding variant
     *
     * @param array|null $moloniProductVariants
     * @param array|null $wantedPropertyPairs
     *
     * @return array|null
     */
    private function findVariant(?array $moloniProductVariants = [], ?array $wantedPropertyPairs = []): ?array
    {
        if (empty($moloniProductVariants) || empty($wantedPropertyPairs)) {
            return null;
        }

        $result = null;

        $wantedPropertyPairsCount = count($wantedPropertyPairs);

        foreach ($moloniProductVariants as $variant) {
            if (count($variant['propertyPairs']) !== $wantedPropertyPairsCount) {
                continue;
            }

            foreach ($wantedPropertyPairs as $wantedPair) {
                $pairFound = false;

                foreach ($variant['propertyPairs'] as $variantPropertyPair) {
                    if ($wantedPair['propertyId'] === $variantPropertyPair['propertyId'] && $wantedPair['propertyValueId'] === $variantPropertyPair['propertyValueId']) {
                        $pairFound = true;
                        break;
                    }
                }

                if (!$pairFound) {
                    continue 2;
                }
            }

            $result = $variant;
            break;
        }

        return $result;
    }
}
