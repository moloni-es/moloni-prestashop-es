CREATE TABLE IF NOT EXISTS PREFIX_moloni_documents (
			    `document_id` int(11) NOT null AUTO_INCREMENT,
                `reference` varchar(30),
                `company_id` int(11),
                `store_id` int(11),
			    `id_order` int(11) ,
                `order_ref` varchar(30),
                `order_total` varchar(30),
                `id_order_invoice` int(11),
                `invoice_total` varchar(30),
                `invoice_type` varchar(50),
                `invoice_date` timestamp NOT null DEFAULT CURRENT_TIMESTAMP,
                `invoice_status` varchar(30),
			    `metadata` TEXT,
			 PRIMARY KEY (`document_id`)
			) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
