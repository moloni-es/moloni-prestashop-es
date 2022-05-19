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

use Shop;
use Store;
use Order;
use Currency;
use PrestaShopDatabaseException;
use PrestaShopException;
use Moloni\Helpers\Settings;
use Moloni\Builders\DocumentFromOrder;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Entity\MoloniDocuments;
use Moloni\Exceptions\MoloniException;
use Moloni\Enums\DocumentTypes;
use Moloni\Enums\MoloniRoutes;
use Moloni\Repository\OrdersRepository;
use Moloni\Repository\MoloniDocumentsRepository;
use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\Document\MoloniDocumentException;
use Moloni\Exceptions\Document\MoloniDocumentWarning;
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
        /** @var OrdersRepository $repository */
        $repository = $this->get('moloni.repository.orders');

        $page = $request->get('page', 1);
        [
            'orders' => $orders,
            'paginator' => $paginator
        ] = $repository->getPendingOrdersPaginated($page, $this->getContextLangId(), Settings::get('dateCreated'));

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
        $orderId = $request->get('order_id');
        $page = $request->get('page');

        try {
            if (!is_numeric($orderId) || $orderId < 0) {
                throw new MoloniException('ID is invalid');
            }

            $order = new Order($orderId);

            if (empty($order->id)) {
                throw new MoloniException('Order does not exist!');
            }

            /** @var MoloniDocumentsRepository $moloniDocumentRepository */
            $moloniDocumentRepository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository(MoloniDocuments::class);

            if ($moloniDocumentRepository->findOneBy(['orderId' => $orderId])) {
                throw new MoloniException('Order already dicarded or created!');
            }

            $company = MoloniApiClient::companies()
                ->queryCompany()['data']['company']['data'] ?? [];

            $builder = new DocumentFromOrder($order, $company);
            $builder->createDocument();

            $document = new MoloniDocuments();
            $document->setShopId((int)Shop::getContextShopID());
            $document->setDocumentId($builder->documentId);
            $document->setCompanyId($this->moloniContext->getCompanyId());
            $document->setOrderId($orderId);
            $document->setOrderReference($order->reference);
            $document->setCreatedAt(time());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($document);
            $entityManager->flush();

            $this->addSuccessMessage($this->trans('Document created successfully.', 'Modules.Molonies.Common'));
        } catch (MoloniDocumentWarning $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addWarningMessage($msg);
        } catch (MoloniDocumentException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg);
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg);
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
            if (!is_numeric($orderId) || $orderId < 0) {
                throw new MoloniException('ID is invalid');
            }

            $order = new Order($orderId);

            if ($order->id === null) {
                throw new MoloniException('Order does not exist!');
            }

            /** @var MoloniDocumentsRepository $moloniDocumentRepository */
            $moloniDocumentRepository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository(MoloniDocuments::class);

            if ($moloniDocumentRepository->findOneBy(['orderId' => $orderId])) {
                throw new MoloniException('Order already dicarded or created!');
            }

            $document = new MoloniDocuments();
            $document->setDocumentId(-1);
            $document->setShopId((int)Shop::getContextShopID());
            $document->setCompanyId($this->moloniContext->getCompanyId());
            $document->setOrderId($orderId);
            $document->setOrderReference($order->reference);
            $document->setCreatedAt(time());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($document);
            $entityManager->flush();

            $msg = $this->trans('Order discarded with success.', 'Modules.Molonies.Common');
            $this->addSuccessMessage($msg);
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg);
        } catch (PrestaShopDatabaseException|PrestaShopException $e) {
            $msg = $this->trans('Error fetching Prestashop order', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);
        }

        return $this->redirectToOrders($page);
    }
}
