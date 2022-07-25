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

declare(strict_types=1);

namespace Moloni\Exceptions;

use Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MoloniException extends Exception
{
    protected $data;
    protected $identifiers;
    protected $shouldCreateLog;

    /**
     * @param string $message
     * @param array $identifiers
     * @param array $data
     * @param bool $shouldCreateLog
     */
    public function __construct($message, $identifiers = [], $data = [], bool $shouldCreateLog = true)
    {
        $this->data = $data;
        $this->identifiers = $identifiers;

        $this->shouldCreateLog = $shouldCreateLog;

        parent::__construct($message);
    }

    public function getData(): array
    {
        return $this->data ?? [];
    }

    public function getIdentifiers(): array
    {
        return $this->identifiers ?? [];
    }

    public function shoudCreateLog(): bool
    {
        return $this->shouldCreateLog;
    }
}
