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

namespace Moloni\Actions\Documents;

use Order;
use Currency;
use Moloni\Enums\DocumentTypes;
use Moloni\Enums\Domains;
use PrestaShopDatabaseException;
use PrestaShopException;

class DocumentsListDetails
{
    private $createdDocuments;
    private $company;

    public function __construct(?array $createdDocuments = [], ?array $company = [])
    {
        $this->company = $company;
        $this->createdDocuments = $createdDocuments;
    }

    public function handle(): array
    {
        if (empty($this->createdDocuments)) {
            return $this->createdDocuments;
        }

        foreach ($this->createdDocuments as &$document) {
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

            $document['order_currency'] = (new Currency($order->id_currency))->symbol;
            $document['order_total'] = $order->total_paid_tax_incl;
            $document['order_email'] = $order->getCustomer()->email;
            $document['order_customer'] = $order->getCustomer()->firstname . ' ' . $order->getCustomer()->lastname;
            $document['document_type_mame'] = DocumentTypes::getDocumentTypeName($document['document_type'] ?? '');

            if ($document['document_id'] < 0) {
                $document['order_discarded'] = true;
                continue;
            }

            $moloniDocument = (new FetchDocumentById($document['document_id'], $document['document_type']))->handle();

            if (empty($moloniDocument)) {
                $document['document_not_found'] = true;
                continue;
            }

            $document['document_link'] = Domains::MOLONI_AC . '/' . $this->company['slug'] . '/' . $document['document_type'] . '/view/' . $document['document_id'];

            if (!empty($moloniDocument['pdfExport'])) {
                $document['document_has_pdf'] = true;
            }
        }

        return $this->createdDocuments;
    }
}
