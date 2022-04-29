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
use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Helpers\Moloni;

trait MoloniCategories
{
    private function getAllMoloniCategories(int $parentId = 0): array
    {
        $array = [];

        $variables = [
            'companyId' => (int) Moloni::get('company_id'),
            'options' => [
                'filter' => [
                    'field' => 'parentId',
                    'comparison' => 'eq',
                    'value' => $parentId,
                ],
            ],
        ];

        try {
            $categories = MoloniApiClient::categories()
                ->queryProductCategories($variables);

            foreach ($categories as $category) {
                if ($category['name'] === 'Envío') {
                    continue;
                }

                if (empty($category['child'])) {
                    $array[$category['name']] = [];
                } else {
                    $array[$category['name']] = $this->getAllMoloniCategories((int) $category['productCategoryId']);
                }
            }
        } catch (MoloniApiException $e) {
            // todo: this
        }

        return $array;
    }

    private function createMoloniCategories(array $categories, int $parentId = 0): bool
    {
        $lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $somethingNew = false;

        foreach ($categories as $name => $child) {
            $query = Category::searchByNameAndParentCategoryId($lang, $name, $parentId);

            if (empty($query)) {
                $category = new Category();
                $category->name = [$lang => (string) $name];
                $category->id_parent = $parentId;
                $category->link_rewrite = [1 => \Tools::str2url((string) $name)];

                try {
                    $category->save();
                } catch (\PrestaShopException $e) {
                    // todo: this

                    break;
                }

                $currentId = $category->id;

                $somethingNew = true;
            } else {
                $currentId = $query['id_category'];
            }

            if (!empty($child)) {
                $this->createMoloniCategories($child, (int) $currentId);
            }
        }

        return $somethingNew;
    }
}
