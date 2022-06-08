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
use Image;
use Moloni\Builders\PrestashopProduct\Helpers\PrestaImage;
use PrestaShopDatabaseException;
use PrestaShopException;
use Shop;

class UpdatePrestaCombinationImage extends PrestaImage
{
    protected $prestashopProductId;
    protected $prestashopCombination;

    public function __construct(int $prestashopProductId, Combination $prestashopCombination, string $moloniImagePath)
    {
        parent::__construct($moloniImagePath);

        $this->prestashopProductId = $prestashopProductId;
        $this->prestashopCombination = $prestashopCombination;

        $this->handle();
    }

    private function handle(): void
    {
        if (empty($this->moloniImagePath)) {
            return;
        }

        $shopId = (int)Shop::getContextShopID();
        $combinationImage = Image::getBestImageAttribute($shopId, $this->languageId, $this->prestashopProductId, $this->prestashopCombination->id);

        if (!empty($combinationImage)) {
            $image = new Image((int)$combinationImage['id_image'], $this->languageId);
            $image->deleteImage(true);
        } else {
            $image = new Image(null, $this->languageId);
            $image->cover = false;
            $image->id_product = $this->prestashopProductId;

            try {
                $image->save();
            } catch (PrestaShopException $e) {
                // todo: write log?
                return;
            }

            $this->prestashopCombination->setImages([$image->id]);
        }

        try {
            $this->saveImage($image);
        } catch (PrestaShopDatabaseException $e) {
            // todo: write log?
            return;
        }
    }
}
