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

trait StringTrait
{
    private function cleanCodeString(string $string): string
    {
        // All chars upper case
        $string = strtoupper($string);

        // Replaces all spaces with hyphens.
        $string = str_replace(' ', '-', $string);

        // Removes special chars
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }
}
