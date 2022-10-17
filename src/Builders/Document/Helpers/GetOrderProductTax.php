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

namespace Moloni\Builders\Document\Helpers;

use Tax;
use Moloni\Exceptions\MoloniException;
use Moloni\Builders\Document\OrderProductTax;

if (!defined('_PS_VERSION_')) {
    exit;
}

class GetOrderProductTax
{
    private $orderProduct;
    private $fiscalZone;

    /**
     * Construct
     *
     * @param array $orderProduct
     * @param array $fiscalZone
     */
    public function __construct(array $orderProduct, array $fiscalZone)
    {
        $this->orderProduct = $orderProduct;
        $this->fiscalZone = $fiscalZone;
    }

    /**
     * Handler
     *
     * @return OrderProductTax|null
     *
     * @throws MoloniException
     */
    public function handle(): ?OrderProductTax
    {
        $taxBuilder = null;
        $taxValue = (float)($this->orderProduct['tax_rate'] ?? 0);

        if ((int)$taxValue === 0 && $this->orderProduct['unit_price_tax_incl'] !== $this->orderProduct['unit_price_tax_excl']) {
            $taxValue = (100 * ((float)$this->orderProduct['unit_price_tax_incl'] - (float)$this->orderProduct['unit_price_tax_excl'])) / (float)$this->orderProduct['unit_price_tax_excl'];
            $taxValue = round($taxValue, 2);
        }

        if ($taxValue > 0) {
            $taxBuilder = new OrderProductTax($taxValue, $this->fiscalZone, 1);

            $taxBuilder->search();

            if ($taxBuilder->getTaxId() === 0) {
                $taxBuilder->insert();
            }
        }

        return $taxBuilder;
    }
}