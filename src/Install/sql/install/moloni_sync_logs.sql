CREATE TABLE IF NOT EXISTS PREFIX_moloni_sync_logs(
        `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
        `type_id` INT NOT NULL,
        `entity_id` INT NOT NULL,
        `sync_date` VARCHAR(250) CHARACTER SET utf8 NOT NULL
    ) DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
