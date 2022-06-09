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
use Moloni\Entity\MoloniProductAssociations;
use Moloni\Tools\ProductAssociations;

class FindOrCreateCombination
{
    private $moloniVariantId;
    private $prestashopProductId;
    private $combinationReference;

    public function __construct(int $moloniVariantId, int $prestashopProductId, string $combinationReference)
    {
        $this->moloniVariantId = $moloniVariantId;
        $this->prestashopProductId = $prestashopProductId;
        $this->combinationReference = $combinationReference;
    }

    public function handle(): Combination
    {
        /** @var MoloniProductAssociations|null $association */
        $association = ProductAssociations::findByMoloniVariantId($this->moloniVariantId);

        if ($association !== null) {
            $combination = new Combination($association->getPsCombinationId());

            if (!empty($combination->id)) {
                return $combination;
            }
        }

        $combinationId = (int)Combination::getIdByReference($this->prestashopProductId, $this->combinationReference);

        if ($combinationId > 0) {
            return new Combination($combinationId);
        }

        return new Combination();
    }
}
