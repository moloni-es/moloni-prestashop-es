CREATE TABLE IF NOT EXISTS PREFIX_moloni_settings (
			    `id` int(11) NOT null AUTO_INCREMENT,
                `store_id` int(11) NOT null,
			    `label` varchar(250) CHARACTER SET utf8 NOT null,
			    `value` varchar(250) CHARACTER SET utf8 ,
			    PRIMARY KEY (`id`)
			) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
