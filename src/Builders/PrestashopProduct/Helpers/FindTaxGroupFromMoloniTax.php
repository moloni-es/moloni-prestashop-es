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

namespace Moloni\Builders\PrestashopProduct\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

class FindTaxGroupFromMoloniTax
{
    private $moloniTax;

    public function __construct(array $moloniTax)
    {
        $this->moloniTax = $moloniTax;
    }

    public function handle(): int
    {
        $taxRulesGroupId = 0;

        $fiscalZone = $this->moloniTax['fiscalZone'] ?? 'ES';
        $countryId = \Country::getByIso($fiscalZone);
        $value = (float) ($this->moloniTax['value'] ?? 0);

        $taxes = array_reverse(\TaxRulesGroup::getAssociatedTaxRatesByIdCountry($countryId), true);

        foreach ($taxes as $id => $tax) {
            if ($value === (float) $tax) {
                $taxRuleGroupObject = new \TaxRulesGroup($id);

                if (!empty($taxRuleGroupObject->deleted) || empty($taxRuleGroupObject->active)) {
                    continue;
                }

                $taxRulesGroupId = $id;

                break;
            }
        }

        return $taxRulesGroupId;
    }
}
