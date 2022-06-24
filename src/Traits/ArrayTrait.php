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

trait ArrayTrait
{
    private function findInCodeOrValue(array $array, string $needle)
    {
        foreach ($array as $value) {
            if ($value['code'] === $needle || $value['value'] === $needle) {
                return $value;
            }
        }

        return false;
    }

    private function findInCode(array $array, string $needle): bool
    {
        foreach ($array as $value) {
            if ($value['code'] === $needle) {
                return true;
            }
        }

        return false;
    }

    private function findInValue(array $array, string $needle)
    {
        foreach ($array as $key => $value) {
            if ($value['value'] === $needle) {
                return $key;
            }
        }

        return false;
    }

    private function findInName(array $array, string $needle)
    {
        foreach ($array as $key => $value) {
            if ($value['name'] === $needle) {
                return $key;
            }
        }

        return false;
    }
}
