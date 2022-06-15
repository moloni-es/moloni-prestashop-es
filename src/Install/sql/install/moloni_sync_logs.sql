CREATE TABLE IF NOT EXISTS PREFIX_moloni_sync_logs(
        `id` INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
        `shop_id` INT(11) NOT NULL,
        `type_id` INT(11) NOT NULL,
        `entity_id` INT(11) NOT NULL,
        `sync_date` datetime DEFAULT CURRENT_TIMESTAMP
    ) DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
