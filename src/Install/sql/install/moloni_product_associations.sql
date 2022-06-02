CREATE TABLE IF NOT EXISTS PREFIX_moloni_product_associations(
    `id` INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `ps_product_id` INT(11) NOT NULL,
    `ps_product_reference` VARCHAR(250) DEFAULT NULL,
    `ps_combination_id` INT(11) DEFAULT NULL,
    `ps_combination_reference` VARCHAR(250) DEFAULT NULL,
    `ml_product_id` INT(11) NOT NULL,
    `ml_product_reference` VARCHAR(250) DEFAULT NULL,
    `active` INT(11) DEFAULT NULL,
    ) DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
