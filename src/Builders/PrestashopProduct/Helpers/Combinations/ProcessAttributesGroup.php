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

namespace Moloni\Builders\PrestashopProduct\Helpers\Combinations;

use Moloni\Helpers\Version;
use Moloni\Traits\AttributesTrait;

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
     * @throws \PrestaShopException
     */
    public function __construct(array $moloniPropertyGroup)
    {
        $this->moloniPropertyGroup = $moloniPropertyGroup;
        $this->languageId = \Configuration::get('PS_LANG_DEFAULT');

        $this->handle();
    }

    /**
     * Handler
     *
     * @throws \PrestaShopException
     */
    public function handle(): void
    {
        foreach ($this->moloniPropertyGroup['properties'] as $group) {
            $groupId = $this->getAttributeGroupByName($group['name']);

            if (empty($groupId)) {
                $groupObj = new \AttributeGroup(null, $this->languageId);
                $groupObj->name = $group['name'];
                $groupObj->public_name = $group['name'];
                $groupObj->group_type = 'select';
                $groupObj->is_color_group = false;
                $groupObj->save();

                $groupId = $groupObj->id;
            }

            foreach ($group['values'] as $attribute) {
                $attributeId = $this->getAttributeByName($attribute['value'], $groupId);

                if (empty($attributeId)) {
                    if (Version::isPrestashopNewVersion()) {
                        $attributeObj = new \ProductAttribute(null, $this->languageId);
                    } else {
                        $attributeObj = new \Attribute(null, $this->languageId);
                    }

                    $attributeObj->name = $attribute['value'];
                    $attributeObj->id_attribute_group = $groupId;
                    $attributeObj->save();
                }
            }
        }
    }
}
