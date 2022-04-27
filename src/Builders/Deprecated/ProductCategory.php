<?php

namespace Moloni\Builders\Deprecated;

use Moloni\Api\Endpoints\Categories;
use Moloni\Helpers\Moloni;

class ProductCategory
{
    /**
     * Current category name
     *
     * @var string
     */
    public $name;

    /**
     * Current category id
     *
     * @var int
     */
    public $categoryId;

    /**
     * Current category parent id
     *
     * @var int
     */
    public $parentId;

    /**
     * ProductCategory constructor.
     *
     * @param $name
     * @param int $parentId
     */
    public function __construct($name, $parentId = 0)
    {
        $this->name = trim($name);
        $this->parentId = $parentId;
    }

    /**
     * Load a category by name and parent id
     *
     * @return $this|bool
     */
    public function loadByName()
    {
        $variables = ['companyId' => (int) Moloni::get('company_id'),
            'options' => [
                'filter' => [
                    'field' => 'parentId',
                    'comparison' => 'eq',
                    'value' => $this->parentId === 0 ? null : (string) $this->parentId,
                ],
                'search' => [
                    'field' => 'name',
                    'value' => $this->name,
                ],
            ],
        ];

        $categoriesList = Categories::queryProductCategories($variables);

        if (!empty($categoriesList) && is_array($categoriesList)) {
            foreach ($categoriesList as $category) {
                if (strcmp((string) $category['name'], (string) $this->name) === 0) {
                    $this->categoryId = $category['productCategoryId'];

                    return $this;
                }
            }
        }

        return false;
    }

    /**
     * Create a new category
     *
     * @return $this|bool
     */
    public function create()
    {
        $insert = Categories::mutationProductCategoryCreate($this->setVariables());
        $insert = $insert['data']['productCategoryCreate']['data'];

        if (isset($insert['productCategoryId'])) {
            $this->categoryId = $insert['productCategoryId'];

            return $this;
        }

        return false;
    }

    /**
     * Map this object properties to an array to insert a new Moloni category
     *
     * @return array
     */
    private function setVariables()
    {
        return [
            'companyId' => (int) Moloni::get('company_id'),
            'data' => [
                'name' => $this->name,
                'parentId' => $this->parentId === 0 ? null : (int) $this->parentId,
            ],
        ];
    }
}
