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
use Moloni\Enums\DocumentTypes;
use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class FetchDocumentById
{
    private $documentId;
    private $documentType;

    public function __construct(int $documentId, string $documentType)
    {
        $this->documentId = $documentId;
        $this->documentType = $documentType;
    }

    public function handle(): array
    {
        $document = [];

        $variables = [
            'documentId' => $this->documentId,
        ];

        try {
            switch ($this->documentType) {
                case DocumentTypes::INVOICES:
                    $query = MoloniApiClient::invoice()->queryInvoice($variables);
                    $query = $query['data']['invoice']['data'] ?? [];

                    break;
                case DocumentTypes::RECEIPTS:
                    $query = MoloniApiClient::receipt()->queryReceipt($variables);
                    $query = $query['data']['receipt']['data'] ?? [];

                    break;
                case DocumentTypes::PRO_FORMA_INVOICES:
                    $query = MoloniApiClient::proFormaInvoice()->queryProFormaInvoice($variables);
                    $query = $query['data']['proFormaInvoice']['data'] ?? [];

                    break;
                case DocumentTypes::PURCHASE_ORDERS:
                    $query = MoloniApiClient::purchaseOrder()->queryPurchaseOrder($variables);
                    $query = $query['data']['purchaseOrder']['data'] ?? [];

                    break;
                case DocumentTypes::SIMPLIFIED_INVOICES:
                    $query = MoloniApiClient::simplifiedInvoice()->querySimplifiedInvoice($variables);
                    $query = $query['data']['simplifiedInvoice']['data'] ?? [];

                    break;
                case DocumentTypes::ESTIMATE:
                    $query = MoloniApiClient::estimate()->queryEstimate($variables);
                    $query = $query['data']['estimate']['data'] ?? [];

                    break;
                case DocumentTypes::BILLS_OF_LADING:
                    $query = MoloniApiClient::billsOfLading()->queryBillsOfLading($variables);
                    $query = $query['data']['billsOfLading']['data'] ?? [];

                    break;
                default:
                    $query = [];
                    break;
            }

            $document = $query;
        } catch (MoloniApiException $e) {
            // no need to catch anything
        }

        return $document;
    }
}
