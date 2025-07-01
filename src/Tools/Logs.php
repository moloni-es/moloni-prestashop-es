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

namespace Moloni\Tools;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Moloni\Entity\MoloniLogs;
use Moloni\Enums\LogLevel;
use Moloni\MoloniContext;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Logs
{
    /**
     * Entity manager
     *
     * @var EntityManagerInterface
     */
    private static $entityManager;

    /**
     * Moloni context
     *
     * @var MoloniContext
     */
    private static $context;

    /**
     * Construct
     *
     * @param EntityManagerInterface $entityManager
     * @param MoloniContext $context
     */
    public function __construct(EntityManagerInterface $entityManager, MoloniContext $context)
    {
        self::$entityManager = $entityManager;
        self::$context = $context;
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
     * Add stocks log
     *
     * @param array|string|null $message
     * @param array|null $data
     * @param int|null $orderId
     *
     * @return void
     */
    public static function addStockLog($message, ?array $data = [], ?int $orderId = 0): void
    {
        self::addLog(LogLevel::STOCK, $message, $data, $orderId);
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
     * Add debug log
     *
     * @param array|string|null $message
     * @param array|null $data
     * @param int|null $orderId
     *
     * @return void
     */
    public static function addDebugLog($message, ?array $data = [], ?int $orderId = 0): void
    {
        self::addLog(LogLevel::DEBUG, $message, $data, $orderId);
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

        $log = new MoloniLogs();
        $log->setShopId((int)\Shop::getContextShopID());
        $log->setOrderId($orderId);
        $log->setCompanyId(self::$context->getCompanyId());
        $log->setLevel($level);
        $log->setExtra(json_encode($data));
        $log->setMessage(json_encode($message));
        $log->setCreatedAt(new \DateTime());

        try {
            self::$entityManager->persist($log);
            self::$entityManager->flush($log);
        } catch (ORMException $e) {
            // no need to catch anything
        }
    }
}
