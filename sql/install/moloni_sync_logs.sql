CREATE TABLE IF NOT EXISTS PREFIX_moloni_sync_logs (
			    `log_id` int NOT null AUTO_INCREMENT,
                `type_id` int NOT null,
                `entity_id` int NOT null,
                `sync_date` varchar(250) CHARACTER SET utf8 NOT null,
			    PRIMARY KEY (`log_id`)
			) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
