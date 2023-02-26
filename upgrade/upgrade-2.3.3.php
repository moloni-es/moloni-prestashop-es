<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_3_3($module): bool
{
    $database = Db::getInstance();

    /** Update current settings to remove any NULL value */
    $database->execute("UPDATE " . _DB_PREFIX_ . "moloni_settings SET `value` = '' WHERE `value` IS NULL");

    /** Change `value` column type to text */
    $database->execute("ALTER TABLE " . _DB_PREFIX_ . "moloni_settings MODIFY `value` text NOT NULL");

    return true;
}