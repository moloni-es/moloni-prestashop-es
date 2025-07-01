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

namespace Moloni\Builders\PrestashopProduct\Helpers\Combinations;

use Moloni\Builders\PrestashopProduct\ProductCombination;
use Moloni\Enums\Boolean;
use Moloni\Tools\ProductAssociations;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateMappingsAfterPrestaProductCreateOrUpdate
{
    private $moloniProduct;
    private $combinationBuilders;
    private $prestashopProduct;

    /**
     * Construct
     *
     * @param array $moloniProduct
     * @param \Product $prestashopProduct
     * @param ProductCombination[] $combinationBuilders
     */
    public function __construct(
        array $moloniProduct,
        \Product $prestashopProduct,
        array $combinationBuilders,
    ) {
        $this->moloniProduct = $moloniProduct;
        $this->prestashopProduct = $prestashopProduct;
        $this->combinationBuilders = $combinationBuilders;

        $this->handle();
    }

    private function handle(): void
    {
        $moloniParentId = $this->moloniProduct['productId'];
        $moloniParentReference = $this->moloniProduct['reference'];

        ProductAssociations::deleteByPrestashopId($this->prestashopProduct->id);
        ProductAssociations::deleteByMoloniId($moloniParentId);

        foreach ($this->combinationBuilders as $combinationBuilder) {
            ProductAssociations::add(
                $moloniParentId,
                $moloniParentReference,
                $combinationBuilder->getMoloniVariantId(),
                $this->prestashopProduct->id,
                $this->prestashopProduct->reference,
                $combinationBuilder->getCombinationId(),
                $combinationBuilder->getReference(),
                Boolean::YES
            );
        }
    }
}
