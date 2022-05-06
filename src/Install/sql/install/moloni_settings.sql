CREATE TABLE IF NOT EXISTS PREFIX_moloni_settings (
    `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `store_id` int(11) NOT null,
    `label` varchar(250) CHARACTER SET utf8 NOT null,
    `value` varchar(250) CHARACTER SET utf8
    ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
