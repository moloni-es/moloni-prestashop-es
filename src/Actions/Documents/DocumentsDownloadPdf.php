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

use Moloni\Api\MoloniApiClient;
use Moloni\Enums\DocumentTypes;
use Moloni\Enums\Domains;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniException;

class DocumentsDownloadPdf
{
    protected $documentId;
    protected $documentType;

    public function __construct(int $documentId, string $documentType)
    {
        $this->documentId = $documentId;
        $this->documentType = $documentType;
    }

    /**
     * @throws MoloniException
     */
    public function handle(): string
    {
        $url = '';

        $token = $this->fetchToken();

        if (!empty($token)) {
            $url = Domains::MOLONI_MEDIA_API . $token['path'] . '?jwt=' . $token['token'];
        }

        return $url;
    }

    /**
     * @throws MoloniException
     */
    private function fetchToken(): array
    {
        $query = [];
        $variables = [
            'documentId' =>  $this->documentId,
        ];

        try {
            switch ($this->documentType) {
                case DocumentTypes::INVOICES:
                    $query = MoloniApiClient::invoice()->queryInvoiceGetPDFToken($variables);
                    $query = $query['data']['invoiceGetPDFToken']['data'] ?? [];

                    break;
                case DocumentTypes::RECEIPTS:
                    $query = MoloniApiClient::receipt()->queryReceiptGetPDFToken($variables);
                    $query = $query['data']['receiptGetPDFToken']['data'] ?? [];

                    break;
                case DocumentTypes::PRO_FORMA_INVOICES:
                    $query = MoloniApiClient::proFormaInvoice()->queryProFormaInvoiceGetPDFToken($variables);
                    $query = $query['data']['proFormaInvoiceGetPDFToken']['data'] ?? [];

                    break;
                case DocumentTypes::PURCHASE_ORDERS:
                    $query = MoloniApiClient::purchaseOrder()->queryPurchaseOrderGetPDFToken($variables);
                    $query = $query['data']['purchaseOrderGetPDFToken']['data'] ?? [];

                    break;
                case DocumentTypes::SIMPLIFIED_INVOICES:
                    $query = MoloniApiClient::simplifiedInvoice()->querySimplifiedInvoiceGetPDFToken($variables);
                    $query = $query['data']['simplifiedInvoiceGetPDFToken']['data'] ?? [];

                    break;
                case DocumentTypes::ESTIMATE:
                    $query = MoloniApiClient::estimate()->queryEstimateGetPDFToken($variables);
                    $query = $query['data']['estimateGetPDFToken']['data'] ?? [];

                    break;
                case DocumentTypes::BILLS_OF_LADING:
                    $query = MoloniApiClient::billsOfLading()->queryBillsOfLadingGetPDFToken($variables);
                    $query = $query['data']['billsOfLadingGetPDFToken']['data'] ?? [];

                    break;
            }
        } catch (MoloniApiException $e) {
            throw new MoloniException('Error fetching pdf token', $e->getData());
        }

        return $query;
    }
}
