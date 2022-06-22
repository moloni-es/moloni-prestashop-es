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

use Product;
use Configuration;
use Moloni\Enums\Boolean;
use Moloni\Traits\StringTrait;
use Moloni\Traits\ArrayTrait;
use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;

class FindOrCreatePropertyGroup
{
    use ArrayTrait;
    use StringTrait;

    /**
     * @var array
     */
    private $prestashopCombinations;

    public function __construct(Product $prestashopProduct)
    {
        $this->prestashopCombinations = $this->preparePrestashopProductAttributes(
            $prestashopProduct->getAttributesGroups(Configuration::get('PS_LANG_DEFAULT'))
        );
    }

    /**
     * @throws MoloniProductException
     */
    public function handle(): array
    {
        if (empty($this->prestashopCombinations)) {
            return [];
        }

        try {
            $moloniPropertyGroups = MoloniApiClient::propertyGroups()->queryPropertyGroups();
        } catch (MoloniApiException $e) {
            throw new MoloniProductException('Error fetching property groups', [], $e->getData());
        }

        $matches = [];

        // Try to find the best property group
        foreach ($moloniPropertyGroups as $moloniPropertyGroup) {
            if (empty($moloniPropertyGroup['propertyGroupId']) || empty($moloniPropertyGroup['properties'])) {
                continue;
            }

            $propertyGroupPropertiesMatchCount = 0;

            foreach ($this->prestashopCombinations as $combinationGroups) {
                foreach ($combinationGroups as $name => $attributes) {
                    foreach ($moloniPropertyGroup['properties'] as $property) {
                        if (strtolower($name) === strtolower($property['name'])) {
                            $propertyGroupPropertiesMatchCount++;
                        }
                    }
                }
            }

            $matches[] = [
                'propertyGroupId' => $moloniPropertyGroup['propertyGroupId'],
                'count' => $propertyGroupPropertiesMatchCount,
            ];
        }

        // Sort by best match descending
        $this->orderMatches($matches);

        // No matches, or the best match is 0
        // We need to fully create it
        if (empty($matches) || $matches[0]['count'] === 0) {
            return (new CreateEntirePropertyGroup($moloniPropertyGroups, $this->prestashopCombinations))->handle();
        }

        /**
         * A match was found
         * If it was partial, we need to do a propertyGroup update to add the missing stuff
         * If it was 100% match, we can just return
         */

        $bestPropertyGroupId = (int)$matches[0]['propertyGroupId'];
        $bestPropertyGroup = $this->findInPropertyGroup($moloniPropertyGroups, $bestPropertyGroupId);

        $propertyGroupForUpdate = [
            'propertyGroupId' => $bestPropertyGroup['propertyGroupId'],
            'properties' => $bestPropertyGroup['properties'],
        ];

        // Delete unwanted props
        foreach ($propertyGroupForUpdate['properties'] as $idx => $group) {
            unset($propertyGroupForUpdate['properties'][$idx]['deletable']);

            foreach ($group['values'] as $idx2 => $property) {
                unset($propertyGroupForUpdate['properties'][$idx]['values'][$idx2]['deletable']);
            }
        }

        $updateNeeded = false;

        foreach ($this->prestashopCombinations as $combinationid => $groups) {
            foreach ($groups as $groupName => $attributes) {
                foreach ($attributes as $attribute) {
                    $propExistsKey = $this->findInName($propertyGroupForUpdate['properties'], $groupName);

                    // Property name exists
                    if ($propExistsKey !== false) {
                        $propExists = $propertyGroupForUpdate['properties'][$propExistsKey];

                        $valueExistsKey = $this->findInCodeOrValue($propExists['values'], $attribute);

                        // Property value doesn't, add value
                        if ($valueExistsKey === false) {
                            $updateNeeded = true;

                            $nextOrdering = $this->getNextPropertyOrder($propExists['values']);

                            $propertyGroupForUpdate['properties'][$propExistsKey]['values'][] = [
                                'code' => $this->cleanCodeString($attribute),
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
                                    'code' => $this->cleanCodeString($attribute),
                                    'value' => $attribute,
                                    'visible' => Boolean::YES,
                                    'ordering' => 1,
                                ]
                            ]
                        ];
                    }
                }
            }
        }

        // There was stuff missing, we need to update the property group
        if ($updateNeeded) {
            try {
                $mutation = MoloniApiClient::propertyGroups()->mutationPropertyGroupUpdate(['data' => $propertyGroupForUpdate]);

                $updatedGroup = $mutation['data']['propertyGroupUpdate']['data'] ?? [];

                if (empty($updatedGroup) || (int)$updatedGroup['propertyGroupId'] === 0) {
                    throw new MoloniProductException('Failed to update existing property group "{0}"', [
                        '{0}' => $bestPropertyGroup['name'] ?? ''
                    ], ['mutation' => $mutation, 'props' => $propertyGroupForUpdate]);
                }
            } catch (MoloniApiException $e) {
                throw new MoloniProductException('Failed to update existing property group "{0}"', [
                    '{0}' => $bestPropertyGroup['name'] ?? ''
                ], $e->getData());
            }

            return (new PrepareVariantPropertiesReturn($updatedGroup, $this->prestashopCombinations))->handle();
        }

        // This was a 100% match, we can return right away
        return (new PrepareVariantPropertiesReturn($bestPropertyGroup, $this->prestashopCombinations))->handle();
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
            $combinationId = (int)$attribute['id_product_attribute'];
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

    /**
     * Orders matches in descending order
     *
     * @param array $matches
     *
     * @return void
     */
    private function orderMatches(array &$matches): void
    {
        $countColumn = array_column($matches, 'count');

        array_multisort($countColumn, SORT_DESC, $matches);
    }

    /**
     * Find an attribute
     *
     * @param array $array
     * @param int $needle
     *
     * @return false|mixed
     */
    private function findInPropertyGroup(array $array, int $needle)
    {
        foreach ($array as $value) {
            if ((int)$value['propertyGroupId'] === $needle) {
                return $value;
            }
        }

        return false;
    }
}
