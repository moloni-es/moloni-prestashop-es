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

if (!defined('_PS_VERSION_')) {
    exit;
}

trait DiscountsTrait
{
    protected function calculateOriginalPrice(?float $discountedPrice = 0, ?float $discountedPercentage = 0)
    {
        $originalPrice = $discountedPrice;

        if ($discountedPercentage > 0) {
            $originalPrice = $discountedPrice / (1 - ($discountedPercentage / 100));
        }

        return $originalPrice;
    }

    protected function calculateDiscountedValue(?float $price = 0, ?float $discountedPercentage = 0)
    {
        $discountedValue = 0.0;

        if ($price > 0) {
            $discountedValue = $price * ($discountedPercentage / 100);
        }

        return $discountedValue;
    }

    protected function calculateDiscountPercentage(?float $price = 0, ?float $discountedValue = 0)
    {
        if ($price > 0) {
            $discount = ($discountedValue / $price) * 100;
        } else {
            $discount = 100;
        }

        switch (true) {
            case $discount > 100:
                $discount = 100;
                break;
            case $discount < 0:
                $discount = 0;
                break;
        }

        return $discount;
    }
}
