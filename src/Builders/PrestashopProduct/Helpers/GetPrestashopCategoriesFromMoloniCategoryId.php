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

namespace Moloni\Builders\PrestashopProduct\Helpers;

use Category;
use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductCategoryException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class GetPrestashopCategoriesFromMoloniCategoryId
{
    private $moloniCategoryId;

    public function __construct(int $moloniCategoryId)
    {
        $this->moloniCategoryId = $moloniCategoryId;
    }

    /**
     * Handler
     *
     * @throws MoloniProductCategoryException
     */
    public function handle(): array
    {
        $parentId = \Category::getRootCategory()->id;
        $languageId = (int) \Configuration::get('PS_LANG_DEFAULT');

        $prestashopCategoryIds = [$parentId];
        $moloniCategoriesNames = $this->getMoloniCategoryTree();

        foreach ($moloniCategoriesNames as $moloniCategoryName) {
            $query = \Category::searchByNameAndParentCategoryId($languageId, $moloniCategoryName, $parentId);

            if (empty($query)) {
                $category = new \Category();
                $category->name = [$languageId => $moloniCategoryName];
                $category->id_parent = $parentId;
                $category->link_rewrite = [$languageId => \Tools::str2url($moloniCategoryName)];

                try {
                    $category->save();
                } catch (\PrestaShopException $e) {
                    throw new MoloniProductCategoryException('Error creating Prestashop category', [], ['name' => $moloniCategoryName, 'parentId' => $parentId]);
                }

                array_unshift($prestashopCategoryIds, $category->id);

                $parentId = $category->id;
            } else {
                $parentId = $query['id_category'];

                array_unshift($prestashopCategoryIds, $query['id_category']);
            }
        }

        return $prestashopCategoryIds;
    }

    /**
     * Fetch category tree names
     *
     * @throws MoloniProductCategoryException
     */
    protected function getMoloniCategoryTree(): array
    {
        $failsafe = 0;
        $categoryId = $this->moloniCategoryId;
        $productCategoriesNames = [];

        do {
            $query = $this->getById($categoryId);

            array_unshift($productCategoriesNames, $query['name']); // order needs to be inverted

            if ($query['parent'] === null) {
                break;
            }

            $categoryId = (int) $query['parent']['productCategoryId'];

            ++$failsafe;
        } while ($failsafe < 100);

        return $productCategoriesNames;
    }

    /**
     * Search for category by id
     *
     * @throws MoloniProductCategoryException
     */
    private function getById($categoryId): array
    {
        $variables = [
            'productCategoryId' => $categoryId,
        ];

        try {
            $query = MoloniApiClient::categories()
                ->queryProductCategory($variables);
        } catch (MoloniApiException $e) {
            throw new MoloniProductCategoryException('Error getting product categories', [], $e->getData());
        }

        return $query['data']['productCategory']['data'] ?? [];
    }
}
