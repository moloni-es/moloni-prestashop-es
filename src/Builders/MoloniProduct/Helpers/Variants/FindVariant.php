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

use Moloni\Entity\MoloniProductAssociations;
use Moloni\Tools\ProductAssociations;

class FindVariant
{
    private $combinationId;
    private $combinationReference;
    private $allMoloniParentVariants;

    public function __construct(int $combinationId, string $combinationReference, array $allMoloniParentVariants)
    {
        $this->combinationId = $combinationId;
        $this->combinationReference = $combinationReference;
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

        return $this->findVariantByReference($this->combinationReference);
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

    private function findVariantByReference(string $needle): array
    {
        $variant = [];

        foreach ($this->allMoloniParentVariants as $parentVariant) {
            if ($parentVariant['reference'] === $needle) {
                $variant = $parentVariant;

                break;
            }
        }

        return $variant;
    }
}
