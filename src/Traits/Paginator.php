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

trait Paginator
{
    private function getPaginator(?int $currentPage = 1, ?int $entries = 0): array
    {
        $linesPerPage = 10; // lines per page
        $numberOfTabs = 1; // default number or tabs

        if ($currentPage < 0) {
            $currentPage = 1;
        }

        if ($entries <= $linesPerPage) {
            $currentPage = 1;
        } else {
            $numberOfTabs = ceil(($entries / $linesPerPage));

            if ($currentPage > $numberOfTabs) {
                $currentPage = (int) $numberOfTabs;
            }
        }
        // where it starts depending on the page
        $offSet = ($currentPage * $linesPerPage) - $linesPerPage;

        return [
            'numberOfTabs' => $numberOfTabs,
            'currentPage' => $currentPage,
            'offSet' => $offSet,
            'linesPerPage' => $linesPerPage,
        ];
    }
}
