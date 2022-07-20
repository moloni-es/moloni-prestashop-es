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

use DateTime;
use Moloni\Enums\DocumentIdentifiers;
use Shop;
use Moloni\Api\MoloniApi;
use Moloni\Entity\MoloniOrderDocuments;
use Moloni\Exceptions\MoloniException;

class OrderDiscard extends AbstractOrderAction
{
    /**
     * Mark orders as discarded
     *
     * @return void
     *
     * @throws MoloniException
     */
    public function handle(): void
    {
        if ($this->documentRepository->findOneBy(['orderId' => $this->orderId])) {
            throw new MoloniException('Order already discarded or created!');
        }

        $document = new MoloniOrderDocuments();
        $document->setShopId((int)Shop::getContextShopID());
        $document->setDocumentId(DocumentIdentifiers::DISCARDED);
        $document->setCompanyId(MoloniApi::getCompanyId());
        $document->setDocumentType('');
        $document->setOrderId($this->orderId);
        $document->setOrderReference($this->order->reference);
        $document->setCreatedAt(new DateTime());

        $this->entityManager->persist($document);
        $this->entityManager->flush();
    }
}
