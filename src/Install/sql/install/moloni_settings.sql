CREATE TABLE IF NOT EXISTS PREFIX_moloni_settings (
    `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `company_id` int(11) NOT NULL,
    `shop_id` int(11) NOT NULL,
    `label` varchar(250) NOT NULL,
    `value` text NOT NULL
    ) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
