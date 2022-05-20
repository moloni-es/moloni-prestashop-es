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

namespace Moloni\Services;

use Moloni\Enums\Boolean;
use Moloni\Enums\DocumentStatus;
use Moloni\Enums\DocumentTypes;
use Moloni\Helpers\Settings;
use Shop;
use Order;
use PrestaShopDatabaseException;
use PrestaShopException;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Moloni\Api\MoloniApi;
use Moloni\Api\MoloniApiClient;
use Moloni\Builders\DocumentFromOrder;
use Moloni\Entity\MoloniDocuments;
use Moloni\Exceptions\Document\MoloniDocumentException;
use Moloni\Exceptions\Document\MoloniDocumentWarning;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniException;
use Moloni\Repository\MoloniDocumentsRepository;

class OrderProcessing
{
    /**
     * Order id
     *
     * @var int|null
     */
    public $orderId;

    /**
     * Order object
     *
     * @var Order|null
     */
    public $order;

    /**
     * Entity manager
     *
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * Documents entity repository
     *
     * @var ObjectRepository|MoloniDocumentsRepository
     */
    private $documentRepository;

    /**
     * Constructor
     *
     * @param int|null $orderId
     * @param ObjectManager $entityManager
     *
     * @throws MoloniException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct(?int $orderId, ObjectManager $entityManager)
    {
        if (!is_numeric($orderId) || $orderId < 0) {
            throw new MoloniException('ID is invalid');
        }

        $order = new Order($orderId);

        if (empty($order->id)) {
            throw new MoloniException('Order does not exist!');
        }

        $this->order = $order;
        $this->orderId = $orderId;
        $this->entityManager = $entityManager;
        $this->documentRepository = $entityManager->getRepository(MoloniDocuments::class);
    }

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
    public function createDocument(?string $documentType = null): void
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $documentType = $documentType ?? Settings::get('documentType');

        if ($this->documentRepository->findOneBy(['orderId' => $this->orderId])) {
            throw new MoloniException('Order already dicarded or created!');
        }

        $companyId = MoloniApi::getCompanyId();
        $company = MoloniApiClient::companies()->queryCompany();

        if ((int)(Settings::get('billOfLading') ?? Boolean::NO) === Boolean::YES) {
            $billOfLading = new DocumentFromOrder($this->order, $company);
            $billOfLading
                ->setDocumentType(DocumentTypes::BILLS_OF_LADING)
                ->setDocumentStatus(DocumentStatus::CLOSED)
                ->setSendEmail(Boolean::NO)
                ->setShippingInformation(Boolean::YES)
                ->createDocument();

            $this->saveRecord($billOfLading->getDocumentId(), $companyId, DocumentTypes::BILLS_OF_LADING);
        }

        if (isset($billOfLading)) {
            $builder = clone $billOfLading;

            $builder
                ->setDocumentType()
                ->setDocumentStatus()
                ->setSendEmail()
                ->setShippingInformation()
                ->addRelatedDocument($billOfLading->getDocumentId(), $billOfLading->getDocumentTotal());
        } else {
            $builder = new DocumentFromOrder($this->order, $company);
        }

        if ($documentType === DocumentTypes::INVOICE_AND_RECEIPT) {

        } else {
            $builder->createDocument();
            $this->saveRecord($builder->getDocumentId(), $companyId, $documentType);
        }
    }

    /**
     * Discard pending order
     *
     * @throws MoloniException
     */
    public function discardOrder(): void
    {
        if ($this->documentRepository->findOneBy(['orderId' => $this->orderId])) {
            throw new MoloniException('Order already dicarded or created!');
        }

        $this->saveRecord(-1, MoloniApi::getCompanyId(), '');
    }

    /**
     * Restore discarted order
     *
     * @throws MoloniException
     */
    public function restoreOrder(): void
    {
        /** @var MoloniDocuments|null $document */
        $document = $this->documentRepository->findOneBy(['orderId' => $this->orderId, 'documentId' => -1]);

        if ($document === null) {
            throw new MoloniException('Discarded order not found!');
        }

        $this->deleteRecord($document);
    }

    /**
     * Save document record
     *
     * @param int $documentId
     * @param int $companyId
     * @param string|null $documentType
     *
     * @return void
     */
    private function saveRecord(int $documentId, int $companyId, ?string $documentType = ''): void
    {
        $shopId = (int)Shop::getContextShopID();

        $document = new MoloniDocuments();
        $document->setShopId($shopId);
        $document->setDocumentId($documentId);
        $document->setCompanyId($companyId);
        $document->setDocumentType($documentType);
        $document->setOrderId($this->orderId);
        $document->setOrderReference($this->order->reference);
        $document->setCreatedAt(time());

        $this->entityManager->persist($document);
        $this->entityManager->flush();
    }

    /**
     * Delete saved record
     *
     * @param MoloniDocuments $entity
     *
     * @return void
     */
    private function deleteRecord(MoloniDocuments $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}
