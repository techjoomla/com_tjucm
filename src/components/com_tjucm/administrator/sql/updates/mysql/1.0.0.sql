--
-- Table structure for table `#__tj_ucm_data`
--

ALTER TABLE `#__tj_ucm_data` ADD `cluster_id` INT NULL DEFAULT NULL COMMENT 'store cluster field Id' AFTER `client`, ADD INDEX (`cluster_id`);

ALTER TABLE `#__tj_ucm_data` ADD `parent_id` int(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`;
