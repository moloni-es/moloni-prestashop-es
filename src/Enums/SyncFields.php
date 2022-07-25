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

namespace Moloni\Enums;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SyncFields
{
    public const NAME = 'name';
    public const PRICE = 'price';
    public const DESCRIPTION = 'description';
    public const CATEGORIES = 'categories';
    public const IMAGE = 'image';

    public static function getSyncFields(): array
    {
        return [
            'Name' => self::NAME,
            'Price' => self::PRICE,
            'Description' => self::DESCRIPTION,
            'Categories' => self::CATEGORIES,
            'Image' => self::IMAGE,
        ];
    }

    public static function getDefaultFields(): array
    {
        return [
            self::NAME,
            self::PRICE,
            self::CATEGORIES,
            self::DESCRIPTION,
        ];
    }
}
