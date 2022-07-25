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

use Moloni\Traits\ArrayTrait;
use Moloni\Traits\StringTrait;
use Moloni\Exceptions\Product\MoloniProductException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrepareVariantPropertiesReturn
{
    use ArrayTrait;
    use StringTrait;

    private $moloniPropertyGroup;
    private $prestashopCombinations;

    public function __construct(array $moloniPropertyGroup, array $prestashopCombinations)
    {
        $this->moloniPropertyGroup = $moloniPropertyGroup;
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
        $result = [];

        foreach ($this->prestashopCombinations as $combinationId => $groups) {
            $variantProperties = [];

            foreach ($groups as $groupName => $attributes) {
                foreach ($attributes as $attribute) {
                    $propExistsKey = $this->findInName($this->moloniPropertyGroup['properties'], $groupName);

                    if ($propExistsKey === false) {
                        throw new MoloniProductException('Failed to find matching property name for "{0}".', ['{0}' => $groupName]);
                    }

                    $propExists = $this->moloniPropertyGroup['properties'][$propExistsKey];

                    $valueExists = $this->findInCode($propExists['values'], $this->cleanCodeString($attribute), [$this, 'cleanCodeString']);

                    if ($valueExists === false) {
                        throw new MoloniProductException('Failed to find matching property value for "{0}"', ['{0}' => $attribute]);
                    }

                    $variantProperties[] = [
                        'propertyId' => $propExists['propertyId'],
                        'propertyValueId' => $valueExists['propertyValueId'],
                    ];
                }
            }

            $result[$combinationId] = $variantProperties;
        }

        return [
            'propertyGroupId' => $this->moloniPropertyGroup['propertyGroupId'],
            'variants' => $result,
        ];
    }
}
