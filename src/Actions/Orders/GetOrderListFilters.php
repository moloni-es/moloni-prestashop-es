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

namespace Moloni\Actions\Orders;

use Moloni\Tools\Settings;

if (!defined('_PS_VERSION_')) {
    exit;
}

class GetOrderListFilters
{
    private $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function handle(): array
    {
        if (empty($this->filters['created_date'])) {
            $this->filters['created_since'] = Settings::get('orderDateCreated');
        }

        if (empty($this->filters['order_state'])) {
            $this->filters['order_state'] = Settings::get('orderStatusToShow');
        } else {
            $this->filters['order_state'] = [$this->filters['order_state']];
        }

        return $this->filters;
    }
}
