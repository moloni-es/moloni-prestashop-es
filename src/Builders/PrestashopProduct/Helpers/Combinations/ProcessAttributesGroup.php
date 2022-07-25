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

namespace Moloni\Builders\PrestashopProduct\Helpers\Combinations;

use Attribute;
use AttributeGroup;
use Configuration;
use Moloni\Traits\AttributesTrait;
use PrestaShopException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProcessAttributesGroup
{
    use AttributesTrait;

    private $languageId;
    private $moloniPropertyGroup;

    /**
     * Constructor
     *
     * @throws PrestaShopException
     */
    public function __construct(array $moloniPropertyGroup)
    {
        $this->moloniPropertyGroup = $moloniPropertyGroup;
        $this->languageId = Configuration::get('PS_LANG_DEFAULT');

        $this->handle();
    }

    /**
     * Handler
     *
     * @throws PrestaShopException
     */
    public function handle(): void
    {
        foreach ($this->moloniPropertyGroup['properties'] as $group) {
            $groupId = $this->getAttributeGroupByName($group['name']);

            if (empty($groupId)) {
                $groupObj = new AttributeGroup(null, $this->languageId);
                $groupObj->name = $group['name'];
                $groupObj->public_name = $group['name'];
                $groupObj->group_type = 'select';
                $groupObj->is_color_group = false;
                $groupObj->save();

                $groupId = $groupObj->id;
            }

            foreach ($group['values'] as $attribute) {
                $attributeId = $this->getAttributeByName($attribute['code'], $groupId);

                if (empty($attributeId)) {
                    $attributeObj = new Attribute(null, $this->languageId);
                    $attributeObj->name = $attribute['code'];
                    $attributeObj->id_attribute_group = $groupId;
                    $attributeObj->save();
                }
            }
        }
    }
}
