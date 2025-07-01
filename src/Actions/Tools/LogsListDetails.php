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

namespace Moloni\Actions\Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LogsListDetails
{
    private $logs;

    public function __construct(?array $logs = [])
    {
        $this->logs = $logs;
    }

    public function handle(): array
    {
        if (empty($this->logs)) {
            return $this->logs;
        }

        foreach ($this->logs as &$log) {
            $log['message'] = json_decode($log['message'], true);
            $log['extra'] = json_decode($log['extra'], true);
        }

        return $this->logs;
    }
}
