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

declare(strict_types=1);

namespace Moloni\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Moloni\Enums\SyncLogsType;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MoloniSyncLogsRepository extends EntityRepository
{
    /**
     * Removes expired product timeouts
     *
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function removeExpiredDelays(int $delay): void
    {
        $results = $this->createQueryBuilder('s')
            ->where('s.syncDate < :time_now')
            ->setParameter('time_now', time() - $delay)
            ->getQuery()
            ->getResult();

        if (!empty($results)) {
            $entityManager = $this->getEntityManager();

            foreach ($results as $result) {
                $entityManager->remove($result);
                $entityManager->flush();
            }
        }
    }

    public function removePrestashopTimeout(int $prestashopId): void
    {
        $results = $this->createQueryBuilder('s')
            ->delete()
            ->where('s.prestashopId = :prestashopId')
            ->setParameter('prestashopId', $prestashopId)
            ->getQuery();

        $results->execute();
    }

    /**
     * Check if a product is locked for **stock sync**
     */
    public function prestashopProductStockHasTimeout(int $productId): bool
    {
        return $this->prestashopProductHasTimeOut($productId, SyncLogsType::PRODUCT_STOCK);
    }

    /**
     * Check if a product is locked for sync
     */
    public function prestashopProductHasTimeOut(int $productId, int $logType = SyncLogsType::PRODUCT): bool
    {
        $findConditions = [
            'prestashopId' => $productId,
            'typeId' => $logType
        ];

        return $this->findOneBy($findConditions) !== null;
    }

    public function moloniProductHasTimeOut($productId): bool
    {
        return $this->findOneBy(['moloniId' => $productId]) !== null;
    }
}
