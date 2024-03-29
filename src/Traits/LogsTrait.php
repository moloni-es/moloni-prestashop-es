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

trait LogsTrait
{
    /**
     * If the builder should write logs
     *
     * @var bool
     */
    protected $writeLogs = true;

    /**
     * Disable logging
     *
     * @return void
     */
    public function disableLogs(): void
    {
        $this->writeLogs = false;
    }

    /**
     * Disable logging
     *
     * @return void
     */
    public function enableLogs(): void
    {
        $this->writeLogs = true;
    }

    /**
     * Check if logs should be writen
     *
     * @return bool
     */
    private function shouldWriteLogs(): bool
    {
        return $this->writeLogs;
    }
}
