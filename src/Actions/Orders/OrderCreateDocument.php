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

namespace Moloni\Actions\Orders;

use Moloni\Api\MoloniApiClient;
use Moloni\Builders\DocumentFromOrder;
use Moloni\Enums\Boolean;
use Moloni\Enums\DocumentStatus;
use Moloni\Enums\DocumentTypes;
use Moloni\Exceptions\Document\MoloniDocumentException;
use Moloni\Exceptions\Document\MoloniDocumentWarning;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniException;
use Moloni\Tools\Settings;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderCreateDocument extends AbstractOrderAction
{
    /**
     * Create Moloni document
     *
     * @param string|null $documentType Document type
     *
     * @return void
     *
     * @throws MoloniDocumentException
     * @throws MoloniDocumentWarning
     * @throws MoloniException
     * @throws MoloniApiException
     */
    public function handle(?string $documentType = null): void
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $documentType = $documentType ?? Settings::get('documentType') ?? '';

        $existingOrderDocument = $this->documentRepository->findOneBy(['orderId' => $this->orderId]);
        if ($existingOrderDocument) {
            throw new MoloniException(
                'Order already discarded or created!',
                $this->orderId,
                $existingOrderDocument->toArray(),
                false
            );
        }

        $company = MoloniApiClient::companies()->queryCompany();

        if ($this->shouldCreateBillOfLading($documentType)) {
            $billOfLading = new DocumentFromOrder($this->order, $company, $this->entityManager);
            $billOfLading
                ->setDocumentType(DocumentTypes::BILLS_OF_LADING)
                ->setDocumentStatus(DocumentStatus::CLOSED)
                ->setSendEmail(Boolean::NO)
                ->setShippingInformation(Boolean::YES)
                ->createDocument()
                ->addLog();
        }

        if (isset($billOfLading)) {
            $builder = clone $billOfLading;

            $builder
                ->setDocumentType()
                ->setDocumentStatus()
                ->setSendEmail()
                ->setShippingInformation()
                ->addRelatedDocument($billOfLading->getDocumentId(), $billOfLading->getDocumentTotal());

            unset($billOfLading);
        } else {
            $builder = new DocumentFromOrder($this->order, $company, $this->entityManager);
        }

        if ($documentType === DocumentTypes::INVOICE_AND_RECEIPT) {
            $builder
                ->setDocumentType(DocumentTypes::INVOICES)
                ->setDocumentStatus(DocumentStatus::CLOSED)
                ->setSendEmail(Boolean::NO)
                ->createDocument()
                ->addLog();

            $receipt = clone $builder;

            $receipt
                ->addRelatedDocument($builder->getDocumentId(), $builder->getDocumentTotal())
                ->setDocumentType(DocumentTypes::RECEIPTS)
                ->setDocumentStatus(DocumentStatus::CLOSED)
                ->setSendEmail()
                ->createDocument()
                ->addLog();

        } else {
            $builder
                ->createDocument()
                ->addLog();
        }
    }

    private function shouldCreateBillOfLading(string $documentType): bool
    {
        return (int)Settings::get('billOfLading') === Boolean::YES && $documentType !== DocumentTypes::BILLS_OF_LADING;
    }
}
