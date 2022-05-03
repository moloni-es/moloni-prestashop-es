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

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Moloni\Api\MoloniApi;
use Moloni\Builders\DocumentFromOrder;
use Moloni\Controller\Admin\Controller;
use Moloni\Entity\MoloniDocuments;
use Moloni\Exceptions\MoloniException;
use Moloni\Repository\MoloniDocumentsRepository;
use Moloni\Traits\DocumentTypesTrait;
use Order;
use PrestaShopDatabaseException;
use PrestaShopException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Orders extends Controller
{
    use DocumentTypesTrait;

    public function home(Request $request): Response
    {
        $orders = [];
        $page = $request->get('page', 1);

        // todo: this

        return $this->render(
            '@Modules/molonies/views/templates/admin/orders/Orders.twig',
            [
                'orderArray' => $orders,
                'documetTypes' => $this->getDocumentsTypes(),
                'documentType' => 'invoice',
                'createDocumentRoute' => 'moloni_es_orders_create',
                'clearInvoiceRoute' => 'moloni_e-s_orders_discard',
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

    public function create(
        Request $request,
        MoloniDocumentsRepository $documentsRepository,
        ManagerRegistry $doctrine
    ): RedirectResponse {
        $orderId = $request->get('id', 0);
        $page = $request->get('page', 1);

        try {
            if (!is_numeric($orderId) || $orderId < 0) {
                throw new MoloniException('ID is invalid');
            }

            $order = new Order($orderId);

            if (empty($order->invoice_number)) {
                throw new MoloniException('Order does not exist!');
            }

            $document = $documentsRepository
                ->createQueryBuilder('d')
                ->where('order_id = :order_id')
                ->setParameter('order_id', $orderId)
                ->getQuery()
                ->getOneOrNullResult();

            if (!empty($document)) {
                throw new MoloniException('Order already dicarded or created!');
            }

            $builder = new DocumentFromOrder($order);
            $builder->createDocument();

            $document = new MoloniDocuments();
            $document->setStoreId(1);
            $document->setDocumentId($builder->documentId);
            $document->setCompanyId(MoloniApi::getSession()->getCompanyId());
            $document->setOrderId($orderId);
            $document->setOrderRef($order->reference);
            $document->setCreatedAt(time());

            $entityManager = $doctrine->getManager();
            $entityManager->persist($document);
            $entityManager->flush();
        } catch (PrestaShopDatabaseException|PrestaShopException $e) {
            $msg = $this->trans('Error fetching Prestashop order', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);
        } catch (NonUniqueResultException $e) {
            $msg = $this->trans('Error fetching created documents', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg);
        }

        return $this->redirectToOrders($page);
    }

    public function discard(
        Request $request,
        MoloniDocumentsRepository $documentsRepository,
        ManagerRegistry $doctrine
    ): RedirectResponse {
        $orderId = $request->get('id', 0);
        $page = $request->get('page', 1);

        try {
            if (!is_numeric($orderId) || $orderId < 0) {
                throw new MoloniException('ID is invalid');
            }

            $order = new Order($orderId);

            if (empty($order->invoice_number)) {
                throw new MoloniException('Order does not exist!');
            }

            $document = $documentsRepository
                ->createQueryBuilder('d')
                ->where('order_id = :order_id')
                ->setParameter('order_id', $orderId)
                ->getQuery()
                ->getOneOrNullResult();

            if (!empty($document)) {
                throw new MoloniException('Order already dicarded or created!');
            }

            $document = new MoloniDocuments();
            $document->setDocumentId(-1);
            $document->setStoreId(1);
            $document->setCompanyId(MoloniApi::getSession()->getCompanyId());
            $document->setOrderId($orderId);
            $document->setOrderRef($order->reference);
            $document->setCreatedAt(time());

            $entityManager = $doctrine->getManager();
            $entityManager->persist($document);
            $entityManager->flush();

            $msg = $this->trans('Order discarded with success.', 'Modules.Molonies.Errors');
            $this->addSuccessMessage($msg);
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg);
        } catch (PrestaShopDatabaseException|PrestaShopException $e) {
            $msg = $this->trans('Error fetching Prestashop order', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);
        } catch (NonUniqueResultException $e) {
            $msg = $this->trans('Error fetching created documents', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);
        }

        return $this->redirectToOrders($page);
    }
}
