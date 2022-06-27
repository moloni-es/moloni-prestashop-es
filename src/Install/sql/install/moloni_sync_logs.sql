CREATE TABLE IF NOT EXISTS PREFIX_moloni_sync_logs(
    `id` INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `shop_id` INT(11) NOT NULL,
    `type_id` INT(11) NOT NULL,
    `prestashop_id` INT(11) DEFAULT 0,
    `moloni_id` INT(11) DEFAULT 0,
    `sync_date` VARCHAR(250) NOT NULL
    ) ENGINE=ENGINE_TYPE DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
