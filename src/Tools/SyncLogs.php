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

namespace Moloni\Tools;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Moloni\Entity\MoloniSyncLogs;
use Moloni\Enums\SyncLogsType;
use Moloni\Repository\MoloniSyncLogsRepository;
use Shop;

if (!defined('_PS_VERSION_')) {
    exit;
}

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

        try {
            self::$syncLogsRepository->removeExpiredDelays(self::$syncDelay);
        } catch (OptimisticLockException|ORMException $e) {
            // no need to catch anything
        }
    }

    //          CHECKS          //

    /**
     * Check if a given Moloni product has timeout
     *
     * @param int $moloniId
     *
     * @return bool
     */
    public static function moloniProductHasTimeout(int $moloniId): bool
    {
        return self::$syncLogsRepository->moloniProductHasTimeOut($moloniId);
    }

    /**
     * Check if a given Prestashop product has timeout
     *
     * @param int $prestashopId
     *
     * @return bool
     */
    public static function prestashopProductHasTimeout(int $prestashopId): bool
    {
        return self::$syncLogsRepository->prestashopProductHasTimeOut($prestashopId);
    }

    public static function prestashopProductStockHasTimeout(int $prestashopId): bool
    {
        return self::$syncLogsRepository->prestashopProductStockHasTimeout($prestashopId);
    }

    //          SETS          //

    /**
     * Add product timeout
     *
     * @param int $moloniId
     * @param int $prestashopId
     *
     * @return void
     */
    public static function productAddTimeout(int $moloniId, int $prestashopId): void
    {
        self::addTimeout($moloniId, $prestashopId);
    }

    public static function prestashopProductRemoveTimeout(int $prestashopId): void
    {
        self::$syncLogsRepository->removePrestashopTimeout($prestashopId);
    }

    /**
     * Add log to a product stock update
     *
     * @param int $moloniId
     * @param int $prestashopId
     *
     * @return void
     */
    public static function productStockAddTimeout(int $moloniId, int $prestashopId): void
    {
        self::addTimeout($moloniId, $prestashopId, SyncLogsType::PRODUCT_STOCK);
    }

    /**
     * Adds timeout to a given moloni product
     *
     * @param int $moloniId Moloni product id
     *
     * @return void
     */
    public static function moloniProductAddTimeout(int $moloniId): void
    {
        self::addTimeout($moloniId);
    }

    /**
     * Adds timeout to a given prestashop product
     *
     * @param int $prestashopId Prestashop product id
     *
     * @return void
     */
    public static function prestashopProductAddTimeout(int $prestashopId): void
    {
        self::addTimeout(0, $prestashopId);
    }

    //          PRIVATES          //

    /**
     * Add product timeout
     */
    private static function addTimeout(
        ?int $moloniId = 0,
        ?int $prestashopId = 0,
        int $type = SyncLogsType::PRODUCT
    ): void {
        $shopId = (int)Shop::getContextShopID();

        $syncLog = new MoloniSyncLogs();
        $syncLog->setMoloniId($moloniId);
        $syncLog->setPrestashopId($prestashopId);
        $syncLog->setShopId($shopId);
        $syncLog->setTypeId($type);
        $syncLog->setSyncDate((string)time());

        try {
            self::$entityManager->persist($syncLog);
            self::$entityManager->flush();
        } catch (ORMException $e) {
            // no need to catch anything
        }
    }
}
