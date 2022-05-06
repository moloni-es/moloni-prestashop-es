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

use Doctrine\ORM\NonUniqueResultException;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Exceptions\MoloniException;
use Moloni\Repository\MoloniDocumentsRepository;
use Moloni\Traits\DocumentActionsTrait;
use Moloni\Traits\DocumentTypesTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Documents extends MoloniController
{
    use DocumentTypesTrait;
    use DocumentActionsTrait;

    public function home(Request $request): Response
    {
        $page = $request->get('page', 1);

        //['documents' => $documents, 'paginator' => $paginator] = $documentsRepository->getAllPaginated($page);

        return $this->render(
            '@Modules/molonies/views/templates/admin/documents/Documents.twig',
            [
                'documentArray' => $documents ?? [],
                'documentTypes' => $this->getDocumentsTypes(),
                'downloadDocumentRoute' => 'moloni_es_documents_download',
                'moloniViewRoute' => 'moloni_es_documents_view_document',
                'thisRoute' => 'moloni_es_documents_home',
                'restoreOrderRoute' => 'moloni_es_documents_restore',
                'paginator' => $paginator ?? [],
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

    public function open(?int $documentId): RedirectResponse
    {
        if (!is_numeric($documentId) || $documentId <= 0) {
            $msg = $this->trans('ID is invalid', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);

            return $this->redirectToDocuments();
        }

        $url = $this->getDocumentUrl($documentId);

        return $this->redirect($url);
    }

    public function restore(?int $orderId, MoloniDocumentsRepository $documentsRepository): void
    {
        if (is_numeric($orderId) && $orderId > 0) {
            try {
                $document = $documentsRepository
                    ->createQueryBuilder('d')
                    ->where('order_id = :order_id')
                    ->setParameter('order_id', $orderId)
                    ->getQuery()
                    ->getOneOrNullResult();

                if (empty($document)) {
                    throw new MoloniException('Error fetching document');
                }

                // todo: delete
            } catch (NonUniqueResultException|MoloniException $e) {
                $msg = $this->trans('Error fetching document', 'Modules.Molonies.Errors');

                $this->addErrorMessage($msg);
            }
        } else {
            $msg = $this->trans('ID is invalid', 'Modules.Molonies.Errors');

            $this->addErrorMessage($msg);
        }

        $this->redirectToDocuments();
    }
}
