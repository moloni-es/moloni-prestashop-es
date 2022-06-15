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

namespace Moloni\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

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
        $timestamp = time() - $delay;

        $results = $this->createQueryBuilder('s')
            ->where('s.syncDate < :time_now')
            ->setParameter('time_now', new DateTime('@'. $timestamp))
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

    public function hasTimeOut($productId): bool
    {
        return $this->findOneBy(['entityId' => $productId]) !== null;
    }
}
