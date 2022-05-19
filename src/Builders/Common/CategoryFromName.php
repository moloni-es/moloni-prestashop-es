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

namespace Moloni\Builders\Common;

use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Interfaces\BuilderItemInterface;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniException;
use Moloni\Exceptions\Product\MoloniProductCategoryException;

class CategoryFromName implements BuilderItemInterface
{
    /**
     * Category id in Moloni
     *
     * @var int
     */
    public $productCategoryId = 0;

    /**
     * Category parent id in Moloni
     *
     * @var int
     */
    public $parentId;

    /**
     * Category name
     *
     * @var string
     */
    protected $name;

    /**
     * Constructor
     *
     * @param string $categoryName
     * @param int|null $moloniParentId
     */
    public function __construct(string $categoryName, ?int $moloniParentId = 0)
    {
        $this->parentId = $moloniParentId;
        $this->name = $categoryName;
    }

    //          PUBLICS          //

    /**
     * Return data as array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'categoryId' => $this->productCategoryId,
            'parentId' => $this->parentId,
            'name' => $this->name,
        ];
    }

    /**
     * Find Moloni category ID
     *
     * @return CategoryFromName
     *
     * @throws MoloniException
     */
    public function search(): CategoryFromName
    {
        $this->getByName();

        return $this;
    }

    /**
     * Create category in Moloni
     *
     * @throws MoloniException
     */
    public function insert(): CategoryFromName
    {
        try {
            $params = [
                'data' => [
                    'name' => $this->name,
                    'parentId' => $this->parentId,
                ]
            ];

            $mutation = MoloniApiClient::categories()->mutationProductCategoryCreate($params);

            $productCategoryId = $mutation['data']['productCategoryCreate']['data']['productCategoryId'] ?? 0;

            if ((int)$productCategoryId > 0) {
                $this->productCategoryId = (int) $productCategoryId;
            } else {
                throw new MoloniException('Error creating category', [], ['params' => $params, 'response' => $mutation]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniException('Error creating category', [], $e->getData());
        }

        return $this;
    }

    //          REQUESTS          //

    /**
     * Search for taxes by value and fiscal zone
     *
     * @throws MoloniException
     */
    public function getByName(): void
    {
        $variables = [
            'options' => [
                'filter' => [
                    'field' => 'parentId',
                    'comparison' => 'eq',
                    'value' => (string)$this->parentId,
                ],
                'search' => [
                    'field' => 'name',
                    'value' => $this->name,
                ],
            ],
        ];

        try {
            $query = MoloniApiClient::categories()
                ->queryProductCategories($variables);

            if (!empty($query)) {
                $this->productCategoryId = $query[0]['productCategoryId'];
            }
        } catch (MoloniApiException $e) {
            throw new MoloniException('Error fetching categories', [], $e->getData());
        }
    }
}
