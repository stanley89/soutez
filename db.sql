-- Adminer 3.1.0 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `prihlasky`;
CREATE TABLE `prihlasky` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `okrsek` int(11) NOT NULL,
  `jmeno` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `ulice` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `obec` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `psc` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `telefon` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `referer` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `agree` tinyint(4) NOT NULL,
  `confirmed` tinyint(4) NOT NULL,
  `locked` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


-- 2014-03-10 08:42:29
