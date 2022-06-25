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

namespace Moloni\Builders\MoloniProduct\Helpers\Variants;

use Moloni\Tools\ProductAssociations;
use Moloni\Entity\MoloniProductAssociations;

class FindVariant
{
    private $combinationId;
    private $combinationReference;
    private $wantedPropertyPairs;
    private $allMoloniParentVariants;

    public function __construct(int $combinationId, string $combinationReference, array $allMoloniParentVariants, array $wantedPropertyPairs)
    {
        $this->combinationId = $combinationId;
        $this->combinationReference = $combinationReference;
        $this->wantedPropertyPairs = $wantedPropertyPairs;
        $this->allMoloniParentVariants = $allMoloniParentVariants;
    }

    public function handle(): array
    {
        if (empty($this->allMoloniParentVariants)) {
            return [];
        }

        /** @var MoloniProductAssociations|null $association */
        $association = ProductAssociations::findByPrestashopCombinationId($this->combinationId);

        if ($association !== null) {
            $targetVariant = $this->findVariantById($association->getMlVariantId());

            if (!empty($targetVariant)) {
                return $targetVariant;
            }
        }

        $targetVariant = $this->findVariantByCombinationReference();

        if (!empty($targetVariant)) {
            return $targetVariant;
        }

        return $this->findVariantByPropertyPairs();
    }

    private function findVariantById(int $needle): array
    {
        $variant = [];

        foreach ($this->allMoloniParentVariants as $parentVariant) {
            if ((int)$parentVariant['productId'] === $needle) {
                $variant = $parentVariant;

                break;
            }
        }

        return $variant;
    }

    private function findVariantByCombinationReference(): array
    {
        $variant = [];

        foreach ($this->allMoloniParentVariants as $parentVariant) {
            if ($parentVariant['reference'] === $this->combinationReference) {
                $variant = $parentVariant;

                break;
            }
        }

        return $variant;
    }

    private function findVariantByPropertyPairs()
    {
        $variant = [];

        foreach ($this->allMoloniParentVariants as $parentVariant) {
            if (count($this->wantedPropertyPairs) !== count($parentVariant['propertyPairs'])) {
                continue;
            }

            foreach ($this->wantedPropertyPairs as $propertyPair) {
                $found = false;

                foreach ($parentVariant['propertyPairs'] as $parentVariantPropertyPairs) {
                    if ($propertyPair['propertyId'] === $parentVariantPropertyPairs['propertyId'] &&
                        $propertyPair['propertyValueId'] === $parentVariantPropertyPairs['propertyValueId']) {
                        $found = true;

                        break;
                    }
                }

                if (!$found) {
                    continue 2;
                }
            }

            // A match was found, return
            $variant = $parentVariant;

            break;
        }

        return $variant;
    }
}
