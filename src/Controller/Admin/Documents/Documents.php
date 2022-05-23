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

use Exception;
use PrestaShopDatabaseException;
use PrestaShopException;
use Moloni\Actions\Orders\OrderRestoreDiscard;
use Moloni\Actions\Documents\DocumentsDownloadPdf;
use Moloni\Actions\Documents\DocumentsListDetails;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Entity\MoloniDocuments;
use Moloni\Api\MoloniApiClient;
use Moloni\Enums\MoloniRoutes;
use Moloni\Exceptions\MoloniException;
use Moloni\Repository\MoloniDocumentsRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Documents extends MoloniController
{
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
        $documents = $paginator = [];

        /** @var MoloniDocumentsRepository $moloniDocumentRepository */
        $moloniDocumentRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(MoloniDocuments::class);

        try {
            ['documents' => $createdDocuments, 'paginator' => $paginator] = $moloniDocumentRepository->getAllPaginated($page);

            $company = MoloniApiClient::companies()->queryCompany();
            $documents = (new DocumentsListDetails())->getDetails($createdDocuments, $company);
        } catch (Exception $e) {
            $this->addErrorMessage($this->trans('Error fetching documents list', 'Modules.Molonies.Common'));
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/documents/Documents.twig',
            [
                'documentArray' => $documents,
                'downloadDocumentRoute' => MoloniRoutes::DOCUMENTS_DOWNLOAD,
                'restoreDocumentRoute' => MoloniRoutes::DOCUMENTS_RESTORE,
                'thisRoute' => MoloniRoutes::DOCUMENTS,
                'paginator' => $paginator,
            ]
        );
    }

    /**
     * Get download link
     *
     * @param Request $request
     * @param int|null $documentId
     *
     * @return RedirectResponse
     */
    public function download(Request $request, ?int $documentId): RedirectResponse
    {
        $documentType = $request->get('documentType', '');
        $page = $request->get('page', 1);

        if (!is_numeric($documentId) || $documentId <= 0) {
            $msg = $this->trans('ID is invalid', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);

            return $this->redirectToDocuments($page);
        }

        $url = (new DocumentsDownloadPdf($documentId, $documentType))->downloadUrl();

        if (empty($url)) {
            $msg = $this->trans('Could not fetch pdf link.', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);

            return $this->redirectToDocuments($page);
        }

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
            $action = new OrderRestoreDiscard($orderId, $this->getDoctrine()->getManager());
            $action->restoreOrder();

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
