<?php

namespace Moloni\Actions\Documents;

use Moloni\Api\MoloniApiClient;
use Moloni\Enums\DocumentTypes;
use Moloni\Enums\Domains;
use Moloni\Exceptions\MoloniApiException;

class DocumentsDownloadPdf
{
    protected $documentId;
    protected $documentType;

    public function __construct(int $documentId, string $documentType)
    {
        $this->documentId = $documentId;
        $this->documentType = $documentType;
    }

    public function handle(): string
    {
        $url = '';

        $token = $this->fetchToken();

        if (!empty($token)) {
            $url = Domains::MOLONI_MEDIA_API . $token['path'] . '?jwt=' . $token['token'];
        }

        return $url;
    }

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
            // todo: catch this??
        }

        return $query;
    }
}
