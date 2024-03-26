CREATE TABLE IF NOT EXISTS `#__tj_ucm_documents` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`title` VARCHAR(255)  NOT NULL ,
`ucm_type` INT(11)  NOT NULL, 
`description` TEXT NOT NULL ,
`document_body` TEXT NOT NULL,
`state` TINYINT(1)  NOT NULL ,
`params` VARCHAR(255)  NOT NULL ,
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
`created_by` INT(11)  NOT NULL ,
`created_date` DATETIME NOT NULL ,
`modified_by` INT(11)  NOT NULL ,
`modified_date` DATETIME NOT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
