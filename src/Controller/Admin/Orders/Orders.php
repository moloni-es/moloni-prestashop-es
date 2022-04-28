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

namespace Moloni\Controller\Admin\Orders;

use Moloni\Controller\Admin\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Orders extends Controller
{
    public function home(Request $request): Response
    {
        // todo: this

        return $this->render(
            '@Modules/molonies/views/templates/admin/orders/Orders.twig',
            [
                'orderArray' => [],
                'documetArray' => [],
                'documentType' => 'invoice',
                'createDocumentRoute' => 'moloni_es_orders_create',
                'clearInvoiceRoute' => 'moloni_es_orders_discard',
                'thisRoute' => 'moloni_es_orders_home',
                'paginator' => [
                    'numberOfTabs' => 1,
                    'currentPage' => 1,
                    'offSet' => 1,
                    'linesPerPage' => 1,
                ],
            ]
        );
    }

    public function create(Request $request): RedirectResponse
    {
        // todo: this

        return $this->redirectToOrders();
    }

    public function discard(Request $request): RedirectResponse
    {
        // todo: this

        return $this->redirectToOrders();
    }
}
