DROP TABLE IF EXISTS `#__csvuploads`;

CREATE TABLE `#__csvuploads` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` MEDIUMTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
  `alias` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_user_id` int(11) NOT NULL DEFAULT '0',
  `params` VARCHAR(1024) NOT NULL DEFAULT '',
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) NOT NULL DEFAULT '0',
  `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)
    ENGINE          = MyISAM
    AUTO_INCREMENT  = 0
    DEFAULT CHARSET = utf8;
