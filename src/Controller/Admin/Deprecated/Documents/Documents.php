<?php

namespace Moloni\Controller\Admin\Documents;

use Currency;
use Moloni\Builders\Deprecated\Documents as modelDocuments;
use Moloni\Controller\Admin\Controller;
use Moloni\Helpers\Log;
use Moloni\Helpers\Moloni;
use Order;
use PrestaShop\PrestaShop\Adapter\Entity\Tools;

class Documents extends Controller
{
    /**
     * Renders documents view
     *
     * @return \Symfony\Component\HttpFoundation\Response renders view
     *
     * @throws \PrestaShopDatabaseException
     */
    public function display()
    {
        if (!$this->checkTokenRedirect()) {
            return $this->redirectToLogin();
        }

        $dataBase = \Db::getInstance();
        // get the number of documents created in this company
        $sql = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'moloni_documents
        WHERE `company_id` = ' . Moloni::get('company_id');

        $numberOfDocuments = (int) ($dataBase->executeS($sql))[0]['COUNT(*)'];

        $paginator = $this->getPaginator((int) Tools::getValue('page'), $numberOfDocuments);

        $sql = 'SELECT d.*, c.`email`,c.`firstname`,c.`lastname`, o.`id_order`'
            . ' FROM ' . _DB_PREFIX_ . 'moloni_documents d'
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.`id_order` = d.`id_order`)'
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = o.`id_customer`)'
            . ' WHERE d.`company_id` = ' . Moloni::get('company_id')
            . ' ORDER BY invoice_date DESC '
            . ' LIMIT ' . $paginator['offSet'] . ',' . $paginator['linesPerPage'];

        $documentArray = $dataBase->executeS($sql);

        foreach ($documentArray as $key => $value) {
            // sets the order currency symbol and url to see order details
            $documentArray[$key]['currency'] = (new Currency((new Order($value['id_order']))->id_currency))->symbol;

            // create url to see order information
            $documentArray[$key]['viewURL'] = $this->getAdminLink(
                'AdminOrders',
                [
                        'vieworder' => '',
                        'id_order' => $value['id_order'],
                    ]
            );

            // to show if the values of the invoices match
            if ($value['invoice_total'] != (new Order($value['id_order']))->total_paid_tax_incl) {
                $documentArray[$key]['wrong'] = true; // red value
            } else {
                $documentArray[$key]['wrong'] = false; // green value
            }
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/documents/Documents.twig',
            [
                'documentArray' => $documentArray, // documents to show
                'documentTypesArray' => $this->getDocumentsTypes(), // types of documents
                'downloadDocumentRoute' => 'moloni_es_documents_download',
                'moloniViewRoute' => 'moloni_es_documents_view_document',
                'thisRoute' => 'moloni_es_documents_home',
                'restoreOrderRoute' => 'moloni_es_documents_restore',
                'paginator' => $paginator,
            ]
        );
    }

    /**
     * Creates the link to download document pdf
     *
     * @param int $documentId documents id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|null redirects to pdf or documents page
     */
    public function downloadDocument($documentId)
    {
        if (!$this->checkTokenRedirect()) {
            return $this->redirectToLogin();
        }

        if (empty($documentId) || !is_numeric($documentId) || $documentId <= 0) {
            Log::writeLog('ID is invalid');

            $this->addFlash(
                'error',
                $this->trans(
                    'ID is invalid',
                    'Modules.Molonies.Errors'
                )
            );

            return $this->redirectToDocuments();
        }

        $url = modelDocuments::downloadPDF($documentId);

        if (!$url) {
            $this->getUserErrorMessage();

            return $this->redirectToDocuments();
        }

        return $this->redirect($url);
    }

    /**
     * Creates url to see document on moloni website
     *
     * @param int $documentId documents id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|null redirects to moloni.es or documents page
     */
    public function viewDocument($documentId)
    {
        if (!$this->checkTokenRedirect()) {
            return $this->redirectToLogin();
        }

        if (empty($documentId) || !is_numeric($documentId) || $documentId <= 0) {
            Log::writeLog('ID is invalid');

            $this->addFlash(
                'error',
                $this->trans(
                    'ID is invalid',
                    'Modules.Molonies.Errors'
                )
            );

            return $this->redirectToDocuments();
        }

        $query = (modelDocuments::viewURL($documentId));

        if (!$query) {
            $this->getUserErrorMessage();

            return $this->redirectToDocuments();
        }

        return $this->redirect($query);
    }

    /**
     * Restores order to the orders table
     *
     * @param $orderId
     *
     * @return |null
     */
    public function restoreOrder($orderId)
    {
        if (empty($orderId) || !is_numeric($orderId) || $orderId < 0) {
            $this->addFlash('warning', $this->trans('Received id is empty!!', 'Modules.Molonies.Errors'));

            return $this->redirectToDocuments();
        }

        $dataBase = \Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'moloni_documents '
            . 'WHERE id_order = ' . $orderId;

        $query = ($dataBase->getRow($sql));

        if (empty($query)) {
            $this->addFlash('error', $this->trans(
                'Order does not exist!!',
                'Modules.Molonies.Errors'
            ));

            return $this->redirectToDocuments();
        }

        // 0- draft 1-closed 2-discarded
        if ($query['invoice_status'] != 2) {
            $this->addFlash('error', $this->trans(
                'This order was not discarted!!',
                'Modules.Molonies.Errors'
            ));

            return $this->redirectToDocuments();
        }

        $dataBase->delete('moloni_documents', 'id_order =' . $orderId, 1, false);

        $this->addFlash('success', $this->trans(
            'Order restored with success.',
            'Modules.Molonies.Success'
        ));

        return $this->redirectToDocuments();
    }
}
