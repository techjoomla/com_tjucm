CREATE TABLE IF NOT EXISTS `#__tj_ucm_types` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`asset_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
`ordering` INT(11)  NOT NULL ,
`title` VARCHAR(255)  NOT NULL ,
`alias` VARCHAR(255) COLLATE utf8_bin NOT NULL ,
`state` TINYINT(1)  NOT NULL ,
`type_description` TEXT NOT NULL ,
`unique_identifier` VARCHAR(255)  NOT NULL ,
`parent_id` INT(11)  NOT NULL ,
`params` VARCHAR(255)  NOT NULL ,
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
`created_by` INT(11)  NOT NULL ,
`created_date` DATETIME NOT NULL ,
`modified_by` INT(11)  NOT NULL ,
`modified_date` DATETIME NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__tj_ucm_data` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`asset_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
`ordering` INT(11)  NOT NULL ,
`state` TINYINT(1)  NOT NULL ,
`category_id` INT(11)  NOT NULL ,
`type_id` INT NOT NULL ,
`client` VARCHAR( 255 ) NOT NULL ,
`cluster_id` int(11) DEFAULT NULL COMMENT 'Store cluster field Id',
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
`created_by` INT(11)  NOT NULL ,
`draft` TINYINT(1)  NOT NULL ,
`created_date` DATETIME NOT NULL ,
`modified_by` INT(11)  NOT NULL ,
`modified_date` DATETIME NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;
