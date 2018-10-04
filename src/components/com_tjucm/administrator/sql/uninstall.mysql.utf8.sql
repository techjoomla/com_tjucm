DROP TABLE IF EXISTS `#__tj_ucm_types`;
DROP TABLE IF EXISTS `#__tj_ucm_data`;

DELETE FROM `#__content_types` WHERE (type_alias LIKE 'com_tjucm.%');