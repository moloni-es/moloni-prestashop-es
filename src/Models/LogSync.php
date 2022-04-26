<?php

namespace Moloni\Models;

use Db;
use PrestaShopDatabaseException;

class LogSync
{
    /**
     * Db instance
     *
     * @var Db
     */
    private static $databaseConnection;

    /**
     * Validity of each log in seconds
     *
     * @var int
     */
    private static $logValidity = 20;

    /**
     * Procedure to check if an entity has been synced recently
     *
     * @param $typeId int (product = 1, ...)
     * @param $entityId int (exp: product_id)
     *
     * @return bool true or false
     *
     * @throws PrestaShopDatabaseException
     */
    public static function wasSyncedRecently($typeId, $entityId)
    {
        self::$databaseConnection = Db::getInstance();

        self::deleteOldLogs(); // delete old logs before checking entry

        if (self::getOne($typeId, $entityId)) {
            return true; // if an entry was found
        }

        self::addLog($typeId, $entityId); // add new entry

        return false; // if an entry was NOT found
    }

    /**
     * Checks for an log entry
     *
     * @param $typeId int
     * @param $entityId int
     *
     * @return bool
     */
    public static function getOne($typeId, $entityId)
    {
        $query = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'moloni_sync_logs 
            where `type_id` = ' . $typeId . ' AND `entity_id` =' . $entityId;

        $queryResult = self::$databaseConnection->getRow($query, false);

        if ((int) $queryResult['COUNT(*)'] === 0) {
            return false;
        }

        return true;
    }

    /**
     * Gets all database entries
     *
     * @throws PrestaShopDatabaseException
     */
    public static function getAll()
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'moloni_sync_logs';

        return self::$databaseConnection->executeS($query);
    }

    /**
     * Adds a new log
     *
     * @param $typeId int
     * @param $entityId int
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public static function addLog($typeId, $entityId)
    {
        self::$databaseConnection->insert(
            'moloni_sync_logs',
            [
                'type_id' => $typeId,
                'entity_id' => $entityId,
                'sync_date' => time() + self::$logValidity,
            ]
        );

        return true;
    }

    /**
     * Deletes logs that have more than defined seconds (default 20)
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public static function deleteOldLogs()
    {
        self::$databaseConnection->delete(
            'moloni_sync_logs',
            'sync_date < ' . time()
        );

        return true;
    }
}
