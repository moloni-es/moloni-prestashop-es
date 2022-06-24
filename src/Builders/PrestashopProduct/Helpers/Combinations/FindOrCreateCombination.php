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

namespace Moloni\Builders\PrestashopProduct\Helpers\Combinations;

use Combination;
use Configuration;
use Moloni\Entity\MoloniProductAssociations;
use Moloni\Tools\ProductAssociations;
use Product;

class FindOrCreateCombination
{
    private $moloniVariantId;
    private $prestashopProduct;
    private $combinationReference;
    private $attributes;

    public function __construct(int $moloniVariantId, Product $prestashopProduct, string $combinationReference, ?array $attributes = [])
    {
        $this->moloniVariantId = $moloniVariantId;
        $this->prestashopProduct = $prestashopProduct;
        $this->combinationReference = $combinationReference;
        $this->attributes = $attributes;
    }

    public function handle(): Combination
    {
        /** @var MoloniProductAssociations|null $association */
        $association = ProductAssociations::findByMoloniVariantId($this->moloniVariantId);

        // If found some association
        if ($association !== null) {
            $combination = new Combination($association->getPsCombinationId());

            // If combinations still exists
            if (!empty($combination->id)) {
                return $combination;
            }
        }

        // Find by reference
        $combinationId = (int)Combination::getIdByReference($this->prestashopProduct->id, $this->combinationReference);

        if ($combinationId > 0) {
            return new Combination($combinationId);
        }

        $existingCombinations = $this->prestashopProduct->getAttributeCombinations();

        // Find by attribute match
        if (!empty($existingCombinations)) {
            $languageId = (int)Configuration::get('PS_LANG_DEFAULT');

            foreach ($existingCombinations as $existingCombination) {
                $auxCombination = new Combination($existingCombination['id_product_attribute']);
                $auxCombinationAttributes = $auxCombination->getAttributesName($languageId);

                // Attributes cound do not match, continue search
                if (count($this->attributes) !== count($auxCombinationAttributes)) {
                    continue;
                }

                foreach ($this->attributes as $attribute) {
                    $found = false;

                    foreach ($auxCombinationAttributes as $auxAttribute) {
                        if ($attribute === (int)$auxAttribute['id_attribute']) {
                            $found = true;

                            break;
                        }
                    }

                    if (!$found) {
                        continue 2;
                    }
                }

                // A match was found, return
                return $auxCombination;
            }
        }

        return new Combination();
    }
}
