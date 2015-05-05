-- Adminer 3.3.3 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE `npppm` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `npppm`;

DROP TABLE IF EXISTS `FileHash`;
CREATE TABLE `FileHash` (
  `md5sum` text,
  `filename` text,
  `pluginName` text,
  `addedDate` datetime DEFAULT NULL,
  `status` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `plugins`;
CREATE TABLE `plugins` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `unicode_version` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `ansi_version` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `description` text,
  `full_description` text,
  `author` varchar(255) DEFAULT NULL,
  `homepage` text,
  `source_url` text,
  `latest_update` text,
  `stability` varchar(255) DEFAULT NULL,
  `aliases` text,
  `url` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `min_version` varchar(11) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `max_version` varchar(11) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `dependencies` text,
  `last_modified` datetime,
  `last_mod_user` varchar(30),
  `library` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `plugins_steps`;
CREATE TABLE `plugins_steps` (
  `plugin_id` int(11) unsigned NOT NULL,
  `step_id` int(11) unsigned NOT NULL,
  `order` smallint(3) unsigned zerofill NOT NULL,
  `plugin_type` tinyint(1) unsigned NOT NULL COMMENT '0=>ansi, 1=>unicode, 2=>ansi uninstall, 3=>unicode uninstall',
  `type` tinyint(1) unsigned NOT NULL COMMENT '0=>download, 1=>copy, 2=>run, 3=>delete',
  PRIMARY KEY (`plugin_id`,`plugin_type`,`step_id`,`order`),
  CONSTRAINT `plugins_steps_ibfk_1` FOREIGN KEY (`plugin_id`) REFERENCES `plugins` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `plugins_versions`;
CREATE TABLE `plugins_versions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_id` int(5) unsigned NOT NULL,
  `number` text NOT NULL,
  `md5` tinytext CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `plugin_id` (`plugin_id`),
  CONSTRAINT `plugins_versions_ibfk_3` FOREIGN KEY (`plugin_id`) REFERENCES `plugins` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1,	'login',	'Login privileges, granted after account confirmation'),
(2,	'admin',	'Administrative user, has access to everything.');

DROP TABLE IF EXISTS `roles_users`;
CREATE TABLE `roles_users` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_role_id` (`role_id`),
  CONSTRAINT `roles_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `roles_users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `steps_copies`;
CREATE TABLE `steps_copies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from` text NOT NULL,
  `to` text NOT NULL,
  `validate` tinyint(1) unsigned NOT NULL,
  `backup` tinyint(1) unsigned NOT NULL,
  `is_dir` tinyint(1) unsigned NOT NULL,
  `replace` tinyint(1) unsigned NOT NULL,
  `isgpup` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `steps_delete`;
CREATE TABLE `steps_delete` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `delete` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `steps_downloads`;
CREATE TABLE `steps_downloads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `filename` text,
  `md5` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `steps_run`;
CREATE TABLE `steps_run` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `run` text NOT NULL,
  `arguments` text NOT NULL,
  `outside` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `user_tokens`;
CREATE TABLE `user_tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `user_agent` varchar(40) NOT NULL,
  `token` varchar(40) NOT NULL,
  `type` varchar(100) NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_token` (`token`),
  KEY `fk_user_id` (`user_id`),
  CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(254) NOT NULL,
  `username` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL,
  `logins` int(10) unsigned NOT NULL DEFAULT '0',
  `last_login` int(10) unsigned DEFAULT NULL,
  `authorisation_token` text,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `valid_hash`;
CREATE TABLE valid_hash (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file` varchar(255) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `addedDate` datetime NOT NULL,
  `response` varchar(10) NOT NULL,
  `username` varchar(32),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2011-08-21 11:22:52
