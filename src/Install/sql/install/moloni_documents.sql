CREATE TABLE IF NOT EXISTS PREFIX_moloni_documents (
    `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `shop_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `document_id` int(11) NOT NULL,
    `document_type` varchar(60) DEFAULT NULL,
    `order_id` int(11) NOT NULL,
    `order_reference` varchar(60) NOT NULL,
    `created_at` varchar(250) DEFAULT CURRENT_TIMESTAMP,
    `metadata` TEXT
    ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
