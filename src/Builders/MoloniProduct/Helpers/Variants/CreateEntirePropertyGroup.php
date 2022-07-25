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

use Moloni\Enums\Boolean;
use Moloni\Traits\ArrayTrait;
use Moloni\Traits\StringTrait;
use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateEntirePropertyGroup
{
    use ArrayTrait;
    use StringTrait;

    private $moloniPropertyGroups;
    private $prestashopCombinations;

    public function __construct(array $moloniPropertyGroups, array $prestashopCombinations)
    {
        $this->moloniPropertyGroups = $moloniPropertyGroups;
        $this->prestashopCombinations = $prestashopCombinations;
    }

    /**
     * Handler
     *
     * @return array
     *
     * @throws MoloniProductException
     */
    public function handle(): array
    {
        $propsForInsert = [];

        // Iterate over the shopify variants and prepare an array for insert into moloni
        foreach ($this->prestashopCombinations as $combinationGroups) {
            foreach ($combinationGroups as $groupName => $attributes) {
                foreach ($attributes as $attribute) {
                    $nameExistsKey = $this->findInName($propsForInsert, $groupName);

                    $newValue = [
                        'code' => $this->cleanReferenceString($attribute),
                        'value' => $attribute,
                        'ordering' => $nameExistsKey ? count($propsForInsert[$nameExistsKey]['values']) + 1 : 1,
                        'visible' => Boolean::YES,
                    ];

                    if ($nameExistsKey !== false) {
                        if (!$this->findInCode(
                            $propsForInsert[$nameExistsKey]['values'],
                            $newValue['code'],
                            [$this, 'cleanReferenceString']
                        )) {
                            $propsForInsert[$nameExistsKey]['values'][] = $newValue;
                        }
                    } else {
                        $propsForInsert[] = [
                            'name' => $groupName,
                            'ordering' => count($propsForInsert) + 1,
                            'values' => [
                                $newValue
                            ],
                            'visible' => Boolean::YES,
                        ];
                    }
                }
            }
        }

        // Loop like crazy trying to find a free group name
        for ($idx = 1; $idx <= 1000; $idx++) {
            $newGroupName = "Prestashop-" . str_pad($idx, 3, '0', STR_PAD_LEFT);

            if ($this->findInName($this->moloniPropertyGroups, $newGroupName) === false) {
                break;
            }
        }

        $creationVariables = [
            'data' => [
                'name' => $newGroupName,
                'properties' => $propsForInsert,
                'visible' => Boolean::YES,
            ]
        ];

        try {
            $mutation = MoloniApiClient::propertyGroups()
                ->mutationPropertyGroupCreate($creationVariables);

            $mutationData = $mutation['data']['propertyGroupCreate']['data'] ?? [];

            if (empty($mutationData)) {
                throw new MoloniProductException(
                    'Error creating {0} attribute group',
                    ['{0}' => $newGroupName],
                    ['mutation' => $mutation]
                );
            }
        } catch (MoloniApiException $e) {
            throw new MoloniProductException(
                'Error creating {0} attribute group',
                ['{0}' => $newGroupName],
                $e->getData()
            );
        }

        return (new PrepareVariantPropertiesReturn($mutationData, $this->prestashopCombinations))->handle();
    }
}
