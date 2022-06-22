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

use Moloni\Entity\MoloniOrderDocuments;
use Moloni\Exceptions\MoloniException;

class OrderRestoreDiscard extends AbstractOrderAction
{
    /**
     * Restore discarted order
     *
     * @throws MoloniException
     */
    public function handle(): void
    {
        /** @var MoloniOrderDocuments|null $document */
        $document = $this->documentRepository->findOneBy(['orderId' => $this->orderId, 'documentId' => -1]);

        if ($document === null) {
            throw new MoloniException('Discarded order not found!');
        }

        $this->deleteRecord($document);
    }

    /**
     * Delete saved record
     *
     * @param MoloniOrderDocuments $entity
     *
     * @return void
     */
    private function deleteRecord(MoloniOrderDocuments $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}
