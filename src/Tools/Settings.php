<?php

/**
 * 2025 - Moloni.com
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

namespace Moloni\Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Settings
{
    /**
     * Settings
     *
     * @var array|null array form: [label => value]
     */
    private static $settings;

    /**
     * Construct
     *
     * @param array|null $settings
     */
    public function __construct(?array $settings)
    {
        self::$settings = $settings;
    }

    //          GETS          //

    /**
     * Returns a settings value from cache
     *
     * @param string $setting name of the setting
     *
     * @return string|int|array|null returns the cached string value or null
     */
    public static function get(string $setting)
    {
        return self::$settings[$setting] ?? null;
    }

    /**
     * Returns all the values from cache
     * (example): ['Type' => 'Bill', 'Status' => 'Draft', ...]
     *
     * @return array returns the cached array with settings data or false if empty
     */
    public static function getAll(): array
    {
        return self::$settings ?? [];
    }

    //          SETS          //

    /**
     * Saves an value in cache
     *
     * @param string $setting Setting name
     * @param string|int|array $value Setting value
     *
     * @return void
     */
    public static function set(string $setting, $value): void
    {
        self::$settings[$setting] = $value;
    }

    /**
     * Sets the cache with the array from param
     *
     * @param array|null $settingsArray array with the form (example): ['Type' => 'Bill', 'Status' => 'Draft', ...]
     *
     * @return void
     */
    public static function setAll(?array $settingsArray): void
    {
        self::$settings = $settingsArray;
    }
}
