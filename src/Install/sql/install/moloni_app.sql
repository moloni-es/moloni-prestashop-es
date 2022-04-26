CREATE TABLE IF NOT EXISTS PREFIX_moloni_app (
                `id` int(11) NOT null AUTO_INCREMENT,
                `client_id` varchar(250) CHARACTER SET utf8 NOT null,
                `client_secret` varchar(250) CHARACTER SET utf8 NOT null,
			    `access_token` varchar(250) CHARACTER SET utf8 NOT null,
			    `refresh_token` varchar(250) CHARACTER SET utf8 NOT null,
			    `company_id` int(11) NOT null,
			    `login_date` varchar(250) CHARACTER SET utf8 NOT null,
                `access_time` varchar(250) CHARACTER SET utf8 NOT null,
			    PRIMARY KEY (`id`)
			    ) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
