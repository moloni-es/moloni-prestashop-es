CREATE TABLE IF NOT EXISTS PREFIX_moloni_app (
    `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `store_id` int(11) NOT null,
    `client_id` varchar(250) CHARACTER SET utf8 NOT null,
    `client_secret` varchar(250) CHARACTER SET utf8 NOT null,
    `access_token` varchar(250) CHARACTER SET utf8 NOT null,
    `refresh_token` varchar(250) CHARACTER SET utf8 NOT null,
    `company_id` int(11) NOT null,
    `login_date` varchar(250) CHARACTER SET utf8 NOT null,
    `access_time` varchar(250) CHARACTER SET utf8 NOT null
    ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
