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

use Image;
use Configuration;
use Moloni\Api\MoloniApi;
use Moloni\Builders\MoloniProduct\ProductVariant;
use Moloni\Enums\Boolean;
use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UpdateMoloniVariantsProductImage
{
    private $languageId;

    private $coverImage;
    private $moloniProductMutated;
    private $variantBuilders;

    /**
     * Construct
     *
     * @param array $coverImage
     * @param array $moloniProductMutated
     * @param ProductVariant[] $variantBuilders
     */
    public function __construct(array $coverImage, array $moloniProductMutated, array $variantBuilders)
    {
        $this->coverImage = $coverImage;
        $this->moloniProductMutated = $moloniProductMutated;
        $this->variantBuilders = $variantBuilders;

        $this->languageId = Configuration::get('PS_LANG_DEFAULT');

        $this->handle();
    }

    private function handle(): void
    {
        if (empty($this->coverImage)) {
            return;
        }

        $files = [];
        $counter = 0;

        $image = new Image($this->coverImage['id_image'], $this->languageId);
        $files[] = _PS_PROD_IMG_DIR_ . $image->getExistingImgPath() . "." . $image->image_format;
        $map = '{ "0": ["variables.data.img"]';

        $props = [
            'data' => [
                'productId' => (int)$this->moloniProductMutated['productId'],
                'img' => '{' . $counter . '}',
                'variants' => [],
            ]
        ];

        $counter++;

        foreach ($this->moloniProductMutated['variants'] as $idx => $variant) {
            if ((int)$variant['visible'] === Boolean::YES) {
                $builder = $this->findBuilderByVariantId((int)$variant['productId']);

                if ($builder) {
                    $variantImage = $builder->getImage();

                    if (!empty($variantImage)) {
                        $image = new Image($variantImage['id_image'], $this->languageId);
                        $files[] = _PS_PROD_IMG_DIR_ . $image->getExistingImgPath() . "." . $image->image_format;;

                        $map .= ', "' . $counter . '": ["variables.data.variants.' . $idx . '.img"]';

                        $props['data']['variants'][] = [
                            'productId' => $variant['productId'],
                            'img' => '{' . $counter . '}',
                        ];

                        $counter++;

                        continue;
                    }
                }
            }

            $props['data']['variants'][] = [
                'productId' => $variant['productId']
            ];
        }

        $map .= ' }';
        $operations = ['query' => $this->getMutation(), 'variables' => $props];

        try {
            MoloniApi::postWithFile($operations, $map, $files);
        } catch (MoloniApiException $e) {
            // do not catch
        }
    }

    /**
     * Find variant corresponding builder
     *
     * @param int $wantedVariantId
     *
     * @return ProductVariant|null
     */
    private function findBuilderByVariantId(int $wantedVariantId): ?ProductVariant
    {
        if (empty($this->variantBuilders) || $wantedVariantId === 0) {
            return null;
        }

        $result = null;

        foreach($this->variantBuilders as $builder) {
            $builderVariantId = $builder->getMoloniVariantId();

            if ($builderVariantId === $wantedVariantId) {
                $result = $builder;

                break;
            }
        }

        return $result;
    }

    private function getMutation(): string
    {
        return 'mutation productUpdate($companyId: Int!,$data: ProductUpdate!)
        {
            productUpdate(companyId: $companyId ,data: $data)
            {
                data
                {
                    productId
                    name
                    reference
                }
                errors
                {
                    field
                    msg
                }
            }
        }';
    }
}
