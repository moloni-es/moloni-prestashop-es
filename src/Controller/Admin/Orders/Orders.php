<?php

namespace Moloni\Controller\Admin\Orders;

use Currency;
use Db;
use Moloni\Builders\Deprecated\Documents as modelDocuments;
use Moloni\Controller\Admin\General;
use Moloni\Helpers\Error;
use Moloni\Helpers\Moloni;
use Moloni\Helpers\Settings;
use Order;
use PrestaShop\PrestaShop\Adapter\Entity\Tools;
use PrestaShopDatabaseException;
use PrestaShopException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Moloni\Controller\pSQL;

class Orders extends General
{
    /**
     * Renders orders view
     *
     * @return Response renders view
     *
     * @throws PrestaShopDatabaseException
     */
    public function indexAction()
    {
        if (!$this->checkTokenRedirect()) {
            return $this->redirectLogin();
        }

        // gets number of paid orders not generated
        $dataBase = Db::getInstance();

        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'orders` o
                WHERE NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'moloni_documents` t2 WHERE o.id_order =t2.id_order)
                AND o.`invoice_number` != 0';

        $numberOfOrders = (int) ($dataBase->executeS($sql))[0]['COUNT(*)'];

        $paginator = $this->getPaginator((int) Tools::getValue('page'), $numberOfOrders);

        $sql = 'SELECT *, (
                    SELECT osl.`name`
                    FROM `' . _DB_PREFIX_ . 'order_state_lang` osl
                    WHERE osl.`id_order_state` = o.`current_state`
                    AND osl.`id_lang` = ' . (int) $this->getContext()->language->id . '
                    LIMIT 1
                ) AS `state_name`, o.`date_add` AS `date_add`, o.`date_upd` AS `date_upd`
                FROM `' . _DB_PREFIX_ . 'orders` o
                LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = o.`id_customer`)
                WHERE NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'moloni_documents` t2 WHERE o.id_order =t2.id_order)
                AND o.`invoice_number` != 0
                ORDER BY o.`date_add` DESC
                LIMIT ' . $paginator['offSet'] . ',' . $paginator['linesPerPage'];

        $orderArray = $dataBase->executeS($sql);

        foreach ($orderArray as $key => $value) {
            // sets the order currency symbol and url to see order details
            $orderArray[$key]['currency'] = (new Currency($value['id_currency']))->symbol;
            // creates url to see order
            $orderArray[$key]['viewURL'] = $this->getAdminLink(
                'AdminOrders',
                [
                    'vieworder' => '',
                    'id_order' => $value['id_order'],
                ]
            );
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/orders/Orders.twig',
            [
                'orderArray' => $orderArray,
                'documetArray' => $this->getDocumentsTypes(),
                'documentType' => ((Settings::get('Type')) ? Settings::get('Type') : 'invoice'),
                'createDocumentRoute' => 'moloni_es_home_createdocument',
                'clearInvoiceRoute' => 'moloni_es_home_clearinvoice',
                'thisRoute' => 'moloni_es_home_index',
                'paginator' => $paginator,
            ]
        );
    }

    /**
     * Adds an order to the moloni_documents table with a negative id, so it gets discarded
     *
     * @return null|null
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function clearInvoice()
    {
        $orderId = Tools::getValue('id');

        if (empty($orderId)) {
            $this->addFlash('warning', $this->trans('Received id is empty!!', 'Modules.Molonies.Errors'));

            return $this->redirectOrders();
        }

        // get the min document id to decrement one
        $db = Db::getInstance();
        $sql = 'SELECT MIN(document_id) FROM ' . _DB_PREFIX_ . 'moloni_documents';
        $minId = (int) ($db->getRow($sql))['MIN(document_id)'];

        if ($minId >= 0) {
            $minId = -1;
        } else {
            --$minId;
        }

        $order = new Order($orderId);

        if (empty($order) || $order->invoice_number == 0) {
            $this->addFlash(
                'warning',
                $this->trans(
                    'Order does not exist!!',
                    'Modules.Molonies.Errors'
                )
            );

            return $this->redirectOrders();
        }

        // checks for the existence of an document with the received id
        $sql = 'SELECT count(*) FROM ' . _DB_PREFIX_ . 'moloni_documents 
                where `id_order` = ' . $orderId;
        $count = (int) ($db->getRow($sql))['count(*)'];

        // if found something in database, cant discard this order
        if ($count != 0) {
            $this->addFlash(
                'warning',
                $this->trans(
                    'Order already discarded or created!!',
                    'Modules.Molonies.Errors'
                )
            );
        }

        // adds the order to documents table
        $db->insert('moloni_documents', [
            'document_id' => $minId,
            'reference' => pSQL($order->reference),
            'company_id' => Moloni::get('company_id'),
            'store_id' => 1,
            'invoice_status' => 2,
            'id_order' => pSQL($order->id),
            'order_ref' => pSQL($order->reference),
            'order_total' => pSQL($order->total_paid_tax_incl),
            'invoice_total' => pSQL($order->total_paid_tax_incl),
            'metadata' => json_encode($order),
        ]);

        $this->addFlash('success', $this->trans(
            'Order discarded with success.',
            'Modules.Molonies.Success'
        ));

        return $this->redirectOrders();
    }

    /**
     * Generates document on Moloni ES
     *
     * @param Request $request
     *
     * @return null
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function createDocument(Request $request = null)
    {
        if (!$this->checkTokenRedirect()) {
            return $this->redirectLogin();
        }

        if ($request->request->get('document_type') == 'receipts'
            && Settings::get('Status') == 0) {
            $this->addFlash('warning', $this->trans(
                'Cannot create Invoice + Receipt as draft!!',
                'Modules.Molonies.Errors'
            ));

            return $this->redirectOrders();
        }

        // checks for the existence of an document with the received id
        $db = Db::getInstance();
        $sql = 'SELECT count(*) FROM ' . _DB_PREFIX_ . 'moloni_documents 
                where `id_order` = ' . $request->request->get('orderId');
        $count = (int) ($db->getRow($sql))['count(*)'];

        // if found something in database, cant create document for this order
        if ($count != 0) {
            $this->addFlash(
                'warning',
                $this->trans(
                    'Order already discarded or created!!',
                    'Modules.Molonies.Errors'
                )
            );
        }

        // Create document model and populates it
        $newDocument = new modelDocuments(
            $request->request->get('orderId'),
            $request->request->get('document_type'),
            $this->getContext()->getTranslator()
        );

        if (!$newDocument->init()) {
            $this->getUserErrorMessage();

            return $this->redirectOrders();
        }

        // Create document on Moloni ES
        if (!$newDocument->create()) {
            $this->getUserErrorMessage();

            return $this->redirectOrders();
        }

        if (Error::getErrors() === false) {
            $this->addFlash('success', $this->trans(
                'Document created successfully.',
                'Modules.Molonies.Success'
            ));
        } else {
            $this->getUserErrorMessage();
        }

        return $this->redirectOrders();
    }
}
