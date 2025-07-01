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

declare(strict_types=1);

namespace Moloni;

use Moloni\Exceptions\MoloniException;

final class Configurations
{
    private $configs = [];

    /**
     * Constructor for the configuration data
     *
     * @throws MoloniException
     */
    public function __construct()
    {
        $directory = dirname(__DIR__);

        $configFile = "$directory/config/platform.php";

        if (!file_exists($configFile)) {
            throw new MoloniException('Configuration file for platform not found.');
        }

        $configs = require $configFile;

        if (!is_array($configs)) {
            throw new MoloniException('Invalid configuration file format.');
        }

        $this->configs = $configs;
    }

    //          Common (specific) getters          //

    public function getApiUrl()
    {
        return $this->get('api_url');
    }

    public function getAcUrl()
    {
        return $this->get('ac_url');
    }

    //          Generic getters          //

    public function get(string $key)
    {
        return $this->configs[$key] ?? null;
    }

    public function getAll(): array
    {
        return $this->configs ?? [];
    }
}
