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

namespace Moloni\Builders\Document\Helpers;

class CalculateDiscountPercentage
{
    private $price;
    private $discountedValue;

    public function __construct($price, $discountedValue)
    {
        $this->price = $price;
        $this->discountedValue = $discountedValue;
    }

    public function handle()
    {
        if ($this->price > 0) {
            $discount = (1 - ($this->price / ($this->price + $this->discountedValue))) * 100;
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
