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

use Category;
use Configuration;

trait CategoriesTrait
{
    public function getPrestashopCategoryTreeById(int $categoryId): array
    {
        $languageId = Configuration::get('PS_LANG_DEFAULT');
        $currentId = $categoryId;
        $rootCategoriesIds = [1, 2];

        $categoriesNames = [];
        $failsafe = 0;

        do {
            $category = new Category($currentId, $languageId);

            if (!empty($category->id)) {
                $currentId = $category->id_parent;

                array_unshift($categoriesNames, $category->name); //order needs to be inverted
            } else {
                $currentId = 1;
            }

            ++$failsafe;
        } while (!in_array($currentId, $rootCategoriesIds) && $failsafe < 100);

        return $categoriesNames;
    }
}
