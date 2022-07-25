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

use Product;
use Moloni\Enums\Boolean;
use Moloni\Tools\ProductAssociations;
use Moloni\Builders\MoloniProduct\ProductVariant;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateMappingsAfterMoloniProductCreateOrUpdate
{
    private $variantsBuilders;
    private $prestashopProduct;
    private $moloniProductMutated;

    /**
     * Construct
     *
     * @param array $moloniProductMutated
     * @param Product $prestashopProduct
     * @param ProductVariant[] $variantsBuilders
     */
    public function __construct(
        Product $prestashopProduct,
        array   $moloniProductMutated,
        array   $variantsBuilders
    )
    {
        $this->prestashopProduct = $prestashopProduct;

        $this->moloniProductMutated = $moloniProductMutated;
        $this->variantsBuilders = $variantsBuilders;

        $this->handle();
    }

    public function handle(): void
    {
        $moloniParentId = $this->moloniProductMutated['productId'];
        $moloniParentReference = $this->moloniProductMutated['reference'];

        ProductAssociations::deleteByPrestashopId($this->prestashopProduct->id);
        ProductAssociations::deleteByMoloniId($moloniParentId);

        foreach ($this->variantsBuilders as $variantsBuilder) {
            $insertedVariantId = $variantsBuilder->getMoloniVariantId();

            if ($insertedVariantId > 0) {
                ProductAssociations::add(
                    $moloniParentId,
                    $moloniParentReference,
                    $insertedVariantId,
                    $this->prestashopProduct->id,
                    $this->prestashopProduct->reference,
                    $variantsBuilder->getPrestashopCombinationId(),
                    $variantsBuilder->getReference(),
                    Boolean::YES
                );
            }
        }
    }
}
