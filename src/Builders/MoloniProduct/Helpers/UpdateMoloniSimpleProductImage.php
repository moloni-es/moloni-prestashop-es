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

namespace Moloni\Builders\MoloniProduct\Helpers;

use Image;
use Configuration;
use Moloni\Api\MoloniApi;
use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UpdateMoloniSimpleProductImage
{
    private $languageId;

    private $coverImage;
    private $moloniProductId;

    /**
     * Construct
     *
     * @param array $coverImage
     * @param int $moloniProductId
     */
    public function __construct(array $coverImage, int $moloniProductId)
    {
        $this->coverImage = $coverImage;
        $this->moloniProductId = $moloniProductId;

        $this->languageId = Configuration::get('PS_LANG_DEFAULT');

        $this->handle();
    }

    private function handle(): void
    {
        if (empty($this->coverImage)) {
            return;
        }

        $image = new Image($this->coverImage['id_image'], $this->languageId);

        $props = [
            'data' => [
                'productId' => $this->moloniProductId,
                'img' => '{0}'
            ]
        ];

        $file = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . "." . $image->image_format;

        try {
            $operations = ['query' => $this->getMutation(), 'variables' => $props];
            $map = '{ "0": ["variables.data.img"] }';

            MoloniApi::postWithFile($operations, $map, [$file]);
        } catch (MoloniApiException $e) {
            // todo: write log?
        }
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
