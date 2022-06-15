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

namespace Moloni\Tools;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Moloni\Entity\MoloniSyncLogs;
use Moloni\Enums\SyncLogsType;
use Moloni\Repository\MoloniSyncLogsRepository;

class SyncLogs
{
    /**
     * Entity manager
     *
     * @var EntityManager
     */
    private static $entityManager;

    /**
     * Moloni sync logs repository
     *
     * @var MoloniSyncLogsRepository
     */
    private static $syncLogsRepository;

    /**
     * Time interval where product is blocked
     *
     * @var int
     */
    private static $syncDelay = 30;

    /**
     * Construct
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        self::$entityManager = $entityManager;
        self::$syncLogsRepository = $entityManager->getRepository(MoloniSyncLogs::class);
    }

    /**
     * Check if a given product has timeout
     *
     * @param int $productId
     *
     * @return bool
     */
    public static function productHasTimeout(int $productId): bool
    {
        try {
            self::$syncLogsRepository->removeExpiredDelays(self::$syncDelay);
        } catch (OptimisticLockException|ORMException $e) {
            // no need to catch anything
        }

        return self::$syncLogsRepository->hasTimeOut($productId);
    }

    /**
     * Adds timeout to a given product
     *
     * @param int $productId Prestashop product id
     *
     * @return void
     */
    public static function productAddTimeout(int $productId): void
    {
        $syncLog = new MoloniSyncLogs();
        $syncLog->setEntityId($productId);
        $syncLog->setTypeId(SyncLogsType::PRODUCT);
        $syncLog->setSyncDate(new DateTime());

        try {
            self::$entityManager->persist($syncLog);
            self::$entityManager->flush();
        } catch (ORMException $e) {
            // no need to catch anything
        }
    }
}
