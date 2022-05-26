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

namespace Moloni\Builders\PrestaProduct;

use Tools;
use Category;
use Configuration;
use PrestaShopException;
use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductCategoryException;

class ProductCategory
{
    /**
     * Moloni category id
     *
     * @var int
     */
    protected $productCategoryId = 0;

    /**
     * Moloni categories names
     *
     * @var array
     */
    protected $productCategoriesNames = [];

    /**
     * Prestashop categories ids
     *
     * @var array
     */
    protected $categoriesIds = [];

    /**
     * Constructor
     *
     * @param int $productCategoryId
     *
     * @throws MoloniProductCategoryException
     */
    public function __construct(int $productCategoryId)
    {
        $this->productCategoryId = $productCategoryId;

        $this->init();
    }

    //          PUBLICS          //

    /**
     * Search for Prestashop categories
     * @throws MoloniProductCategoryException
     */
    public function search(): void
    {
        //the root of all categories has id = 2
        $parentId = 2;
        $languageId = (int)Configuration::get('PS_LANG_DEFAULT');

        foreach ($this->productCategoriesNames as $categoriesName) {
            $query = Category::searchByNameAndParentCategoryId($languageId, $categoriesName, $parentId);

            if (empty($query)) {
                $category = new Category();
                $category->name = [$languageId => $categoriesName];
                $category->id_parent = $parentId;
                $category->link_rewrite = [$languageId => Tools::str2url($categoriesName)];

                try {
                    $category->save();
                } catch (PrestaShopException $e) {
                    throw new MoloniProductCategoryException('Error creating Prestashop category', [], [
                        'name' => $categoriesName,
                        'parentId' => $parentId,
                    ]);
                }

                array_unshift($this->categoriesIds, $category->id);

                $parentId = $category->id;
            } else {
                $parentId = $query['id_category'];

                array_unshift($this->categoriesIds, $query['id_category']);
            }
        }
    }

    //          PRIVATES          //

    /**
     * @throws MoloniProductCategoryException
     */
    protected function init(): void
    {
        $failsafe = 0;
        $categoryId = $this->productCategoryId;

        do {
            $query = $this->getById($categoryId);

            array_unshift($this->productCategoriesNames, $query['name']); //order needs to be inverted

            if ($query['parent'] === null) {
                break;
            }

            $categoryId = (int)$query['parent']['productCategoryId'];

            $failsafe++;
        } while ($failsafe < 100);
    }

    //          GETS          //

    /**
     * Gets Prestashop categories ids
     *
     * @return array
     */
    public function getCategoriesIds(): array
    {
        return $this->categoriesIds;
    }

    //          REQUESTS          //

    /**
     * Search for category by id
     *
     * @throws MoloniProductCategoryException
     */
    protected function getById($categoryId): array
    {
        $variables = [
            'productCategoryId' => $categoryId
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
