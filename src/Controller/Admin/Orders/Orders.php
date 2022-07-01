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

use Tools;
use Currency;
use OrderState;
use PrestaShopException;
use PrestaShopDatabaseException;
use Moloni\Actions\Orders\GetOrderListFilters;
use Moloni\Actions\Orders\OrderCreateDocument;
use Moloni\Actions\Orders\OrderDiscard;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Enums\DocumentTypes;
use Moloni\Enums\MoloniRoutes;
use Moloni\Exceptions\Document\MoloniDocumentException;
use Moloni\Exceptions\Document\MoloniDocumentWarning;
use Moloni\Exceptions\MoloniException;
use Moloni\Repository\OrdersRepository;
use Moloni\Tools\Logs;
use Moloni\Tools\Settings;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class Orders extends MoloniController
{
    /**
     * Pending orders list
     *
     * @return Response
     */
    public function home(): Response
    {
        $page = Tools::getValue('page', 1);
        $filters = Tools::getValue('filters', []);

        /** @var OrdersRepository $repository */
        $repository = $this->get('moloni.repository.orders');

        ['orders' => $orders, 'paginator' => $paginator] = $repository->getPendingOrdersPaginated(
            $page,
            $this->getContextLangId(),
            (new GetOrderListFilters($filters))->handle()
        );

        foreach ($orders as &$order) {
            $order['currency'] = (new Currency($order['id_currency']))->symbol;
            $order['view_url'] = $this->getAdminLink('AdminOrders', [
                'vieworder' => '',
                'id_order' => $order['id_order'],
            ]);
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/orders/Orders.twig',
            [
                'orderArray' => $orders,
                'orderStatesArray' => OrderState::getOrderStates($this->getContextLangId()),
                'filters' => $filters,
                'paginator' => $paginator,
                'companyName' => Settings::get('companyName'),
                'documentType' => Settings::get('documentType'),
                'documentTypes' => DocumentTypes::getDocumentsTypes(),
                'createDocumentRoute' => MoloniRoutes::ORDERS_CREATE,
                'discardOrderRoute' => MoloniRoutes::ORDERS_DISCARD,
                'thisRoute' => MoloniRoutes::ORDERS,
            ]
        );
    }

    /**
     * Create document form order
     *
     * @return RedirectResponse
     */
    public function create(): RedirectResponse
    {
        $orderId = (int)Tools::getValue('order_id', 0);
        $documentType = Tools::getValue('document_type');
        $fromOrderPage = Tools::getValue('from_order_page', false);

        $page = (int)Tools::getValue('page', 1);
        $filters = Tools::getValue('filters', []);

        try {
            $action = new OrderCreateDocument($orderId, $this->getDoctrine()->getManager());
            $action->handle($documentType);

            $msg = $this->trans('Document created successfully. ({0})', 'Modules.Molonies.Common', ['{0}' => $action->getOrder()->reference]);

            $this->addSuccessMessage($msg);
        } catch (MoloniDocumentWarning $e) {
            $auxMessage = 'Warning processing order ({0})';
            $auxIdentifiers = ['{0}' => isset($action) ? $action->getOrder()->reference : ''];

            Logs::addWarningLog([[$auxMessage, $auxIdentifiers], [$e->getMessage(), $e->getIdentifiers()]], $e->getData(), $orderId);

            $msg = $this->trans($auxMessage, 'Modules.Molonies.Errors', $auxIdentifiers);

            $this->addWarningMessage($msg, $e->getData());
        } catch (MoloniDocumentException|MoloniException $e) {
            $auxMessage = 'Error processing order ({0})';
            $auxIdentifiers = ['{0}' => isset($action) ? $action->getOrder()->reference : ''];

            Logs::addErrorLog([[$auxMessage, $auxIdentifiers], [$e->getMessage(), $e->getIdentifiers()]], $e->getData(), $orderId);

            $msg = $this->trans($auxMessage, 'Modules.Molonies.Errors', $auxIdentifiers);

            $this->addErrorMessage($msg, $e->getData());
        } catch (PrestaShopDatabaseException|PrestaShopException $e) {
            $msg = $this->trans('Error fetching Prestashop order', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);
        }

        if ($fromOrderPage) {
            return $this->redirectToAdminOrderPage($orderId);
        }

        return $this->redirectToOrders($page, $filters);
    }

    /**
     * Discard an order
     *
     * @return RedirectResponse
     */
    public function discard(): RedirectResponse
    {
        $orderId = (int)Tools::getValue('orderId', 0);
        $page = (int)Tools::getValue('page', 1);
        $filters = Tools::getValue('filters', []);

        try {
            $action = new OrderDiscard($orderId, $this->getDoctrine()->getManager());
            $action->handle();

            $msg = $this->trans('Order discarded with success.', 'Modules.Molonies.Common');
            $msg .= ' (' . $action->getOrder()->reference . ')';

            $this->addSuccessMessage($msg);
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg, $e->getData());
        } catch (PrestaShopDatabaseException|PrestaShopException $e) {
            $msg = $this->trans('Error fetching Prestashop order', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);
        }

        return $this->redirectToOrders($page, $filters);
    }
}
