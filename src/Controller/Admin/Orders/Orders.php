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

use Currency;
use Db;
use Moloni\Actions\Orders\OrderCreateDocument;
use Moloni\Actions\Orders\OrderDiscard;
use Moloni\Builders\MoloniProductFromId;
use Order;
use PrestaShopDatabaseException;
use PrestaShopException;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Enums\DocumentTypes;
use Moloni\Enums\MoloniRoutes;
use Moloni\Exceptions\Document\MoloniDocumentException;
use Moloni\Exceptions\Document\MoloniDocumentWarning;
use Moloni\Exceptions\MoloniException;
use Moloni\Helpers\Settings;
use Moloni\Repository\OrdersRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Orders extends MoloniController
{
    /**
     * Pending orders list
     *
     * @param Request $request
     *
     * @return Response
     */
    public function home(Request $request): Response
    {
        $page = $request->get('page', 1);

        /** @var OrdersRepository $repository */
        $repository = $this->get('moloni.repository.orders');
        ['orders' => $orders, 'paginator' => $paginator] = $repository->getPendingOrdersPaginated(
            $page,
            $this->getContextLangId(),
            Settings::get('dateCreated'),
            Settings::get('orderStatusToShow')
        );

        foreach ($orders as &$order) {
            $order['currency'] = (new Currency($order['id_currency']))->symbol;
            $order['view_url'] = $this->getAdminLink('AdminOrders', [
                'vieworder' => '',
                'id_order' => $order['id_order']
            ]);
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/orders/Orders.twig',
            [
                'orderArray' => $orders,
                'documetTypes' => DocumentTypes::getDocumentsTypes(),
                'documentType' => Settings::get('documentType'),
                'createDocumentRoute' => MoloniRoutes::ORDERS_CREATE,
                'discardOrderRoute' => MoloniRoutes::ORDERS_DISCARD,
                'thisRoute' => MoloniRoutes::ORDERS,
                'paginator' => $paginator,
            ]
        );
    }

    /**
     * Create document form order
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function create(Request $request): RedirectResponse
    {
        $orderId = (int)$request->get('order_id', 0);
        $documentType = $request->get('document_type');
        $page = $request->get('page');

        try {
            $action = new OrderCreateDocument($orderId, $this->getDoctrine()->getManager());
            $action->createDocument($documentType);

            $msg = $this->trans('Document created successfully.', 'Modules.Molonies.Common');
            $msg .= " (" . $action->order->reference . ")";

            $this->addSuccessMessage($msg);
        } catch (MoloniDocumentWarning $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addWarningMessage($msg, $e->getData());
        } catch (MoloniDocumentException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg, $e->getData());
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg, $e->getData());
        } catch (PrestaShopDatabaseException|PrestaShopException $e) {
            $msg = $this->trans('Error fetching Prestashop order', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);
        }

        return $this->redirectToOrders($page);
    }

    /**
     * Discard an order
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function discard(Request $request): RedirectResponse
    {
        $orderId = $request->get('id', 0);
        $page = $request->get('page', 1);

        try {
            $action = new OrderDiscard($orderId, $this->getDoctrine()->getManager());
            $action->discard();

            $msg = $this->trans('Order discarded with success.', 'Modules.Molonies.Common');
            $msg .= " (" . $action->order->reference . ")";

            $this->addSuccessMessage($msg);
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg, $e->getData());
        } catch (PrestaShopDatabaseException|PrestaShopException $e) {
            $msg = $this->trans('Error fetching Prestashop order', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);
        }

        return $this->redirectToOrders($page);
    }
}
