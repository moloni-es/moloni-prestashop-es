CREATE TABLE IF NOT EXISTS PREFIX_moloni_logs (
    `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `shop_id` int(11) NOT NULL,
    `order_id` int(11) DEFAULT NULL,
    `level` tinyint(4) NOT NULL,
    `message` text NOT NULL,
    `extra` text,
    `created_at` VARCHAR(250) DEFAULT CURRENT_TIMESTAMP
    ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
