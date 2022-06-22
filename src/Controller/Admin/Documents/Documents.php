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

use Tools;
use Exception;
use PrestaShopException;
use PrestaShopDatabaseException;
use Moloni\Enums\DocumentTypes;
use Moloni\Actions\Documents\FetchDocumentById;
use Moloni\Enums\Domains;
use Moloni\Tools\Settings;
use Moloni\Actions\Orders\OrderRestoreDiscard;
use Moloni\Actions\Documents\DocumentsDownloadPdf;
use Moloni\Actions\Documents\DocumentsListDetails;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Entity\MoloniOrderDocuments;
use Moloni\Api\MoloniApiClient;
use Moloni\Enums\MoloniRoutes;
use Moloni\Exceptions\MoloniException;
use Moloni\Repository\MoloniOrderDocumentsRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class Documents extends MoloniController
{
    /**
     * Created documents list
     *
     * @return Response
     */
    public function home(): Response
    {
        $page = Tools::getValue('page', 1);
        $filters = Tools::getValue('filters', []);
        $documents = $paginator = [];

        /** @var MoloniOrderDocumentsRepository $moloniDocumentRepository */
        $moloniDocumentRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(MoloniOrderDocuments::class);

        try {
            ['documents' => $createdDocuments, 'paginator' => $paginator] = $moloniDocumentRepository->getAllPaginated($page, $filters);

            $company = MoloniApiClient::companies()->queryCompany();
            $documents = (new DocumentsListDetails($createdDocuments, $company))->handle();
        } catch (Exception $e) {
            $msg = $this->trans('Error fetching documents list', 'Modules.Molonies.Errors');

            $this->addErrorMessage($msg);
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/documents/Documents.twig',
            [
                'documentArray' => $documents,
                'documentTypeArray' => DocumentTypes::getDocumentsTypes(),
                'filters' => $filters,
                'paginator' => $paginator,
                'companyName' => Settings::get('companyName'),
                'downloadDocumentRoute' => MoloniRoutes::DOCUMENTS_DOWNLOAD,
                'restoreDocumentRoute' => MoloniRoutes::DOCUMENTS_RESTORE,
                'thisRoute' => MoloniRoutes::DOCUMENTS,
            ]
        );
    }

    /**
     * Get view link
     *
     * @return RedirectResponse
     */
    public function view(): RedirectResponse
    {
        $documentId = (int)Tools::getValue('document_id', 0);

        /** @var MoloniOrderDocuments|null $document */
        $document = $this->getDoctrine()
            ->getRepository(MoloniOrderDocuments::class)
            ->findOneBy(['documentId' => $documentId], ['id' => 'DESC']);

        try {
            if ($document === null) {
                throw new MoloniException('Created document not found');
            }

            $moloniDocument = (new FetchDocumentById($document->getDocumentId(), $document->getDocumentType()))->handle();

            if (empty($moloniDocument)) {
                throw new MoloniException('Moloni document not found');
            }

            $company = MoloniApiClient::companies()->queryCompany();

            $link = Domains::MOLONI_AC . '/' . $company['slug'] . '/' . $document->getDocumentType() . '/view/' . $document->getDocumentId();

            return $this->redirect($link);
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors');

            $this->addErrorMessage($msg);
        }

        return $this->redirectToOrders();
    }

    /**
     * Get download link
     *
     * @return RedirectResponse
     */
    public function download(): RedirectResponse
    {
        $documentId = (int)Tools::getValue('document_id', 0);
        $documentType = Tools::getValue('document_type', '');

        try {
            if (!is_numeric($documentId) || $documentId <= 0) {
                throw new MoloniException('ID is invalid');
            }

            $url = (new DocumentsDownloadPdf($documentId, $documentType))->handle();

            if (empty($url)) {
                throw new MoloniException('Could not fetch pdf link.', [], ['result' => $url]);
            }
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());

            $this->addErrorMessage($msg, $e->getData());

            return $this->redirectToDocuments();
        }

        return $this->redirect($url);
    }

    /**
     * Restore discarded order
     *
     * @return RedirectResponse
     */
    public function restore(): RedirectResponse
    {
        $orderId = (int)Tools::getValue('order_id', 0);
        $page = (int)Tools::getValue('page', 1);
        $filters = Tools::getValue('filters', []);

        try {
            $action = new OrderRestoreDiscard($orderId, $this->getDoctrine()->getManager());
            $action->handle();

            $msg = $this->trans('Order restored with success.', 'Modules.Molonies.Common');
            $msg .= "(" . $action->getOrder()->reference . ")";

            $this->addSuccessMessage($msg);
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg, $e->getData());
        } catch (PrestaShopDatabaseException|PrestaShopException $e) {
            $msg = $this->trans('Error fetching Prestashop order', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);
        }

        return $this->redirectToDocuments($page, $filters);
    }
}
