CREATE TABLE IF NOT EXISTS PREFIX_moloni_app (
    `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `shop_id` int(11) NOT NULL,
    `client_id` varchar(250) NOT NULL,
    `client_secret` varchar(250) NOT NULL,
    `access_token` varchar(250) DEFAULT NULL,
    `refresh_token` varchar(250) DEFAULT NULL,
    `company_id` int(11) NOT NULL DEFAULT 0,
    `access_time` varchar(250) NOT NULL DEFAULT 0
    ) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
