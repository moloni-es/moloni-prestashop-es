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

namespace Moloni\Controller\Admin\Documents;

use Order;
use Currency;
use Exception;
use PrestaShopDatabaseException;
use PrestaShopException;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Entity\MoloniDocuments;
use Moloni\Enums\DocumentTypes;
use Moloni\Enums\MoloniRoutes;
use Moloni\Exceptions\MoloniException;
use Moloni\Repository\MoloniDocumentsRepository;
use Moloni\Traits\DocumentTrait;
use Moloni\Services\OrderProcessing;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Documents extends MoloniController
{
    use DocumentTrait;

    /**
     * Created documents list
     *
     * @param Request $request
     *
     * @return Response
     */
    public function home(Request $request): Response
    {
        $page = $request->get('page', 1);
        $createdDocuments = $paginator = [];

        /** @var MoloniDocumentsRepository $moloniDocumentRepository */
        $moloniDocumentRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(MoloniDocuments::class);

        try {
            ['documents' => $createdDocuments, 'paginator' => $paginator] = $moloniDocumentRepository->getAllPaginated($page);
        } catch (Exception $e) {
            $this->addErrorMessage($this->trans('Error fetching documents list', 'Modules.Molonies.Common'));
        }

        foreach ($createdDocuments as &$document) {
            $orderId = (int)($document['order_id'] ?? 0);

            try {
                $order = new Order($orderId);
            } catch (PrestaShopDatabaseException|PrestaShopException $e) {
                $order = null;
            }

            if ($order === null || $order->id === null) {
                $document['order_not_found'] = true;
                continue;
            }

            if ($document['document_id'] < 0) {
                $document['order_discarded'] = true;
            }

            $document['order_currency'] = (new Currency($order->id_currency))->symbol;
            $document['order_total'] = $order->total_paid_tax_incl;
            $document['order_email'] = $order->getCustomer()->email;
            $document['order_customer'] = $order->getCustomer()->firstname . ' ' . $order->getCustomer()->lastname;

            $document['order_view_url'] = $this->getAdminLink(
                'AdminOrders',
                [
                    'vieworder' => '',
                    'id_order' => $orderId,
                ]
            );
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/documents/Documents.twig',
            [
                'documentArray' => $createdDocuments ?? [],
                'documentTypes' => DocumentTypes::getDocumentsTypes(),
                'downloadDocumentRoute' => MoloniRoutes::DOCUMENTS_DOWNLOAD,
                'viewDocumentRoute' => MoloniRoutes::DOCUMENTS_VIEW,
                'restoreDocumentRoute' => MoloniRoutes::DOCUMENTS_RESTORE,
                'thisRoute' => MoloniRoutes::DOCUMENTS,
                'paginator' => $paginator,
            ]
        );
    }

    public function download(?int $documentId): RedirectResponse
    {
        if (!is_numeric($documentId) || $documentId <= 0) {
            $msg = $this->trans('ID is invalid', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);

            return $this->redirectToDocuments();
        }

        $url = $this->getPdfUrl($documentId);

        return $this->redirect($url);
    }

    public function view(?int $documentId): RedirectResponse
    {
        if (!is_numeric($documentId) || $documentId <= 0) {
            $msg = $this->trans('ID is invalid', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);

            return $this->redirectToDocuments();
        }

        $url = $this->getDocumentUrl($documentId);

        return $this->redirect($url);
    }

    /**
     * Restore discarded order
     *
     * @param Request $request Request data
     * @param int|null $orderId Order to restore
     *
     * @return RedirectResponse
     */
    public function restore(Request $request, ?int $orderId): RedirectResponse
    {
        $page = $request->get('page', 1);

        try {
            $action = new OrderProcessing($orderId, $this->getDoctrine()->getManager());
            $action->discardOrder();

            $msg = $this->trans('Order restored with success.', 'Modules.Molonies.Common');
            $msg .= "(" . $action->order->reference . ")";

            $this->addSuccessMessage($msg);
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg, $e->getData());
        } catch (PrestaShopDatabaseException|PrestaShopException $e) {
            $msg = $this->trans('Error fetching Prestashop order', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);
        }

        return $this->redirectToDocuments($page);
    }
}
