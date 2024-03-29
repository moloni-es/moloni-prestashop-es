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

class LogLevel
{
    public const INFO = 1;
    public const WARNING = 2;
    public const ERROR = 3;
    public const HIDDEN = 4;
    public const STOCK = 5;
    public const DEBUG = 6;

    public static function getLogLevels(): array
    {
        return [
            'Information' => self::INFO,
            'Warning' => self::WARNING,
            'Error' => self::ERROR,
            'Stock' => self::STOCK,
            'Debug' => self::DEBUG,
        ];
    }
}
