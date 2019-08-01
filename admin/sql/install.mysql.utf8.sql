DROP TABLE IF EXISTS `#__csvuploads`;

CREATE TABLE `#__csvuploads` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `catid` INT(11) NOT NULL DEFAULT '0',
  `contact_user_id` int(11) NOT NULL DEFAULT '0',
  `description` mediumtext NOT NULL,
  `file` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `params` text COLLATE utf8_unicode_ci,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(10) NOT NULL DEFAULT '0',
  `created_by_alias` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(10) NOT NULL DEFAULT '0',
  `modified_by_alias` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `checked_out` INT(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` INT(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) 
    ENGINE          = MyISAM
    AUTO_INCREMENT  = 0
    DEFAULT CHARSET = utf8;