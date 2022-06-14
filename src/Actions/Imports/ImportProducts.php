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

namespace Moloni\Actions\Imports;

abstract class ImportProducts
{
    /**
     * @var array
     */
    protected $syncedProducts = [];

    /**
     * @var array
     */
    protected $errorProducts = [];

    /**
     * @var int
     */
    protected $totalResults = 0;

    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $itemsPerPage = 50;

    public function __construct(?int $page = 1)
    {
        $this->page = $page;
    }

    public function getCurrentPercentage(): int
    {
        if ($this->totalResults === 0) {
            return 100;
        }

        $percentage = (($this->page * $this->itemsPerPage) / $this->totalResults) * 100;

        return (int)$percentage;
    }

    public function getHasMore(): bool
    {
        return $this->totalResults > ($this->page * $this->itemsPerPage);
    }

    public function getTotalResults(): int
    {
        return $this->totalResults;
    }

    public function getErrorProducts(): array
    {
        return $this->errorProducts;
    }

    public function getSyncedProducts(): array
    {
        return $this->syncedProducts;
    }
}
