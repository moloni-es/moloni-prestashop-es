<?php

namespace Moloni\Helpers;

class Settings
{
    /**
     * Settings
     *
     * @var array array form: [label => value]
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
