<?php

/**
 * 2025 - Moloni.com
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

use Moloni\Api\MoloniApiClient;
use Moloni\Configurations;
use Moloni\Enums\DocumentTypes;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DocumentsDownloadPdf
{
    protected $documentId;
    protected $documentType;
    protected $configurations;

    public function __construct(int $documentId, string $documentType, Configurations $configurations)
    {
        $this->documentId = $documentId;
        $this->documentType = $documentType;
        $this->configurations = $configurations;
    }

    /**
     * @throws MoloniException
     */
    public function handle(): string
    {
        $url = '';

        $token = $this->fetchToken();

        if (!empty($token)) {
            $url = $this->configurations->get('media_api_url') . $token['path'] . '?jwt=' . $token['token'];
        }

        return $url;
    }

    /**
     * @throws MoloniException
     */
    private function fetchToken(): array
    {
        $variables = ['documentId' => $this->documentId];

        $map = [
            DocumentTypes::INVOICES => ['method' => [MoloniApiClient::invoice(), 'queryInvoiceGetPDFToken'], 'key' => 'invoiceGetPDFToken'],
            DocumentTypes::RECEIPTS => ['method' => [MoloniApiClient::receipt(), 'queryReceiptGetPDFToken'], 'key' => 'receiptGetPDFToken'],
            DocumentTypes::PRO_FORMA_INVOICES => ['method' => [MoloniApiClient::proFormaInvoice(), 'queryProFormaInvoiceGetPDFToken'], 'key' => 'proFormaInvoiceGetPDFToken'],
            DocumentTypes::PURCHASE_ORDERS => ['method' => [MoloniApiClient::purchaseOrder(), 'queryPurchaseOrderGetPDFToken'], 'key' => 'purchaseOrderGetPDFToken'],
            DocumentTypes::SIMPLIFIED_INVOICES => ['method' => [MoloniApiClient::simplifiedInvoice(), 'querySimplifiedInvoiceGetPDFToken'], 'key' => 'simplifiedInvoiceGetPDFToken'],
            DocumentTypes::ESTIMATE => ['method' => [MoloniApiClient::estimate(), 'queryEstimateGetPDFToken'], 'key' => 'estimateGetPDFToken'],
            DocumentTypes::BILLS_OF_LADING => ['method' => [MoloniApiClient::billsOfLading(), 'queryBillsOfLadingGetPDFToken'], 'key' => 'billsOfLadingGetPDFToken'],
        ];

        try {
            if (isset($map[$this->documentType])) {
                $entry = $map[$this->documentType];
                $result = call_user_func($entry['method'], $variables);

                $query = $result['data'][$entry['key']]['data'] ?? [];
            } else {
                $query = [];
            }
        } catch (MoloniApiException $e) {
            throw new MoloniException('Error fetching pdf token', $e->getData());
        }

        return $query;
    }
}
