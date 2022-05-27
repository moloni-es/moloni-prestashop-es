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

namespace Moloni\Helpers;

use Shop;
use Moloni\Api\MoloniApi;
use Moloni\Entity\MoloniLogs;
use Moloni\Enums\LogLevel;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;

class Logs
{
    /**
     * Entity manager
     *
     * @var EntityManager
     */
    private static $entityManager;

    /**
     * Construct
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        self::$entityManager = $entityManager;
    }

    /**
     * Add success log
     *
     * @param array|string|null $message
     * @param array|null $data
     * @param int|null $orderId
     *
     * @return void
     */
    public static function addInfoLog($message, ?array $data = [], ?int $orderId = 0): void
    {
        self::addLog(LogLevel::INFO, $message, $data, $orderId);
    }

    /**
     * Add warning log
     *
     * @param array|string|null $message
     * @param array|null $data
     * @param int|null $orderId
     *
     * @return void
     */
    public static function addWarningLog($message, ?array $data = [], ?int $orderId = 0): void
    {
        self::addLog(LogLevel::WARNING, $message, $data, $orderId);
    }

    /**
     * Add error log
     *
     * @param array|string|null $message
     * @param array|null $data
     * @param int|null $orderId
     *
     * @return void
     */
    public static function addErrorLog($message, ?array $data = [], ?int $orderId = 0): void
    {
        self::addLog(LogLevel::ERROR, $message, $data, $orderId);
    }

    /**
     * Adds log to database
     *
     * @param int $level
     * @param array|string|null $message
     * @param array|null $data
     * @param int|null $orderId
     *
     * @return void
     */
    private static function addLog(int $level, $message, ?array $data = [], ?int $orderId = 0): void
    {
        switch (true) {
            case empty($message):
                $message = [['NULL', false]];
                break;
            case is_string($message):
                $message = [[$message, false]];
                break;
            case is_array($message) && !is_array($message[0]):
                $message = [$message];
                break;
        }

        $companyId = MoloniApi::getCompanyId();
        $shopId = (int)Shop::getContextShopID();

        $log = new MoloniLogs();
        $log->setShopId($shopId);
        $log->setOrderId($orderId);
        $log->setCompanyId($companyId);
        $log->setLevel($level);
        $log->setExtra(json_encode($data));
        $log->setMessage(json_encode($message));
        $log->setCreatedAt(time());

        try {
            self::$entityManager->persist($log);
            self::$entityManager->flush($log);
        } catch (ORMException $e) {
            // no need to catch anything
        }
    }
}
