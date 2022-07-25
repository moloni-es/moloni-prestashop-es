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

namespace Moloni\Traits;

use Attribute;
use Configuration;
use AttributeGroup;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait AttributesTrait
{
    private $cacheGroup = [];
    private $cacheAttribute = [];

    /**
     * Fetch attribute by name and group id
     *
     * @param string $name
     * @param int $groupId
     *
     * @return int
     */
    private function getAttributeByName(string $name, int $groupId): int
    {
        if (empty($this->cacheAttribute)) {
            $this->cacheAttribute = Attribute::getAttributes(Configuration::get('PS_LANG_DEFAULT'));
        }

        $idAttribute = 0;

        foreach ($this->cacheAttribute as $attribute) {
            if ($attribute['name'] === $name && (int)$attribute['id_attribute_group'] === $groupId) {
                $idAttribute = (int)$attribute['id_attribute'];

                break;
            }
        }

        return $idAttribute;
    }

    /**
     * Fetch attribute group by name
     *
     * @param string $name
     *
     * @return int
     */
    private function getAttributeGroupByName(string $name): int
    {
        if (empty($this->cacheGroup)) {
            $this->cacheGroup = AttributeGroup::getAttributesGroups(Configuration::get('PS_LANG_DEFAULT'));
        }

        $idGroup = 0;

        foreach ($this->cacheGroup as $group) {
            if ($group['name'] === $name) {
                $idGroup = (int)$group['id_attribute_group'];

                break;
            }
        }

        return $idGroup;
    }
}
