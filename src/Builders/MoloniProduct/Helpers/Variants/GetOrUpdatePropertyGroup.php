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

namespace Moloni\Builders\MoloniProduct\Helpers\Variants;

use Configuration;
use Moloni\Api\MoloniApiClient;
use Moloni\Enums\Boolean;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Traits\ArrayTrait;
use Product;

if (!defined('_PS_VERSION_')) {
    exit;
}

class GetOrUpdatePropertyGroup
{
    use ArrayTrait;

    /**
     * @var array
     */
    private $prestashopCombinations;

    /**
     * @var string
     */
    private $propertyGroupId;

    public function __construct(Product $prestashopProduct, string $propertyGroupId)
    {
        $this->prestashopCombinations = $this->preparePrestashopProductAttributes(
            $prestashopProduct->getAttributesGroups(Configuration::get('PS_LANG_DEFAULT'))
        );
        $this->propertyGroupId = $propertyGroupId;
    }

    /**
     * @throws MoloniProductException
     */
    public function handle(): array
    {
        if (empty($this->prestashopCombinations)) {
            return [];
        }

        $queryParams = [
            'propertyGroupId' => $this->propertyGroupId
        ];

        try {
            $moloniPropertyGroup = MoloniApiClient::propertyGroups()
                ->queryPropertyGroup($queryParams)['data']['propertyGroup']['data'] ?? [];
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error fetching property group', [], $e->getData());
        }

        /** Propery group is not found, exit process immediately */
        if (empty($moloniPropertyGroup)) {
            throw new MoloniProductException('Error fetching property group', [], $queryParams);
        }

        $propertyGroupForUpdate = [
            'propertyGroupId' => $moloniPropertyGroup['propertyGroupId'],
            'properties' => $moloniPropertyGroup['properties'],
        ];

        // Delete unwanted props
        foreach ($propertyGroupForUpdate['properties'] as $idx => $group) {
            unset($propertyGroupForUpdate['properties'][$idx]['deletable']);

            foreach ($group['values'] as $idx2 => $property) {
                unset($propertyGroupForUpdate['properties'][$idx]['values'][$idx2]['deletable']);
            }
        }

        $updateNeeded = false;

        foreach ($this->prestashopCombinations as $groups) {
            foreach ($groups as $groupName => $attributes) {
                foreach ($attributes as $attribute) {
                    $propExistsKey = $this->findInName($propertyGroupForUpdate['properties'], $groupName);

                    // Property name exists
                    if ($propExistsKey !== false) {
                        $propExists = $propertyGroupForUpdate['properties'][$propExistsKey];

                        $valueExistsKey = $this->findInCodeWithFallback(
                            $propExists['values'],
                            $attribute
                        );

                        // Property value doesn't, add value
                        if ($valueExistsKey === false) {
                            $updateNeeded = true;

                            $nextOrdering = $this->getNextPropertyOrder($propExists['values']);

                            $propertyGroupForUpdate['properties'][$propExistsKey]['values'][] = [
                                'code' => $this->cleanReferenceString($attribute),
                                'value' => $attribute,
                                'ordering' => $nextOrdering,
                                'visible' => Boolean::YES,
                            ];
                        }

                    // Property name doesn't exist
                    // need to create property and the value
                    } else {
                        $updateNeeded = true;

                        $nextOrdering = $this->getNextPropertyOrder($propertyGroupForUpdate['properties']);

                        $propertyGroupForUpdate['properties'][] = [
                            'ordering' => $nextOrdering,
                            'name' => $groupName,
                            'visible' => Boolean::YES,
                            'values' => [
                                [
                                    'code' => $this->cleanReferenceString($attribute),
                                    'value' => $attribute,
                                    'visible' => Boolean::YES,
                                    'ordering' => 1,
                                ],
                            ],
                        ];
                    }
                }
            }
        }

        // There was stuff missing, we need to update the property group
        if ($updateNeeded) {
            try {
                $mutation = MoloniApiClient::propertyGroups()->mutationPropertyGroupUpdate(
                    ['data' => $propertyGroupForUpdate]
                );

                $updatedPropertyGroup = $mutation['data']['propertyGroupUpdate']['data'] ?? [];

                if (empty($updatedPropertyGroup)) {
                    throw new MoloniProductException('Failed to update existing property group "{0}"', ['{0}' => $bestPropertyGroup['name'] ?? ''], ['mutation' => $mutation, 'props' => $propertyGroupForUpdate]);
                }
            } catch (MoloniApiException $e) {
                throw new MoloniProductException('Failed to update existing property group "{0}"', ['{0}' => $bestPropertyGroup['name'] ?? ''], $e->getData());
            }

            return (new PrepareVariantPropertiesReturn($updatedPropertyGroup, $this->prestashopCombinations))->handle();
        }

        // This was a 100% match, we can return right away
        return (new PrepareVariantPropertiesReturn($moloniPropertyGroup, $this->prestashopCombinations))->handle();
    }

    /**
     * Prepare initial data structure for looping
     *
     * @param array|null $productAttributes
     *
     * @return array
     */
    private function preparePrestashopProductAttributes(?array $productAttributes = []): array
    {
        /**
         * [
         *      'combination_id => [
         *          'group_name' => [
         *              'attribute_a',
         *              'attribute_b',
         *              ...
         *          ]
         *      ]
         * ]
         */
        $result = [];

        foreach ($productAttributes as $attribute) {
            $combinationId = (int) $attribute['id_product_attribute'];
            $groupName = $attribute['group_name'];
            $attributeName = $attribute['attribute_name'];

            if (!isset($result[$combinationId][$groupName])) {
                $result[$combinationId][$groupName] = [];
            }

            $result[$combinationId][$groupName][] = $attributeName;
        }

        return $result;
    }

    //          AUXILIARY          //

    /**
     * Get next attribute order
     *
     * @param array|null $properties
     *
     * @return int
     */
    private function getNextPropertyOrder(?array $properties = []): int
    {
        $lastOrder = 0;

        if (!empty($properties)) {
            $count = count($properties);
            $lastIndex = $count - 1;

            $lastOrder = $properties[$lastIndex]['ordering'] ?? 0;
        }

        return $lastOrder + 1;
    }
}
