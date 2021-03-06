-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Mer 06 Avril 2016 à 05:40
-- Version du serveur: 5.5.46-0ubuntu0.14.04.2-log
-- Version de PHP: 5.5.9-1ubuntu4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `panda`
--
CREATE DATABASE IF NOT EXISTS `panda` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `panda`;

-- --------------------------------------------------------

--
-- Structure de la table `concours`
--

CREATE TABLE IF NOT EXISTS `concours` (
  `section` tinyint(4) NOT NULL COMMENT 'numero section ou cid',
  `session` varchar(16) NOT NULL COMMENT 'l''année du concours',
  `code` varchar(5) NOT NULL COMMENT 'code concours 06/03',
  `statut` varchar(16) DEFAULT NULL,
  `intitule` text NOT NULL COMMENT 'intitule du concours ex CR1_BIGDATA',
  `sousjury1` text NOT NULL COMMENT 'Le code du sousjury, le nom du sous jury, suivi de la liste des logins des membres, en commençant par le president du sous jury, le tout séparé par des ; Si le nom est une chaine vide, la section est constituée en jury pleinier',
  `sousjury2` text NOT NULL COMMENT 'Le nom du sousjury2, suivi de la liste des logins des membres, en commençant par le president du sous jury, le tout séparé par des ;',
  `sousjury3` text NOT NULL COMMENT 'Le nom du sousjury3, suivi de la liste des logins des membres, en commençant par le president du sous jury, le tout séparé par des ;',
  `sousjury4` text NOT NULL COMMENT 'Le nom du sousjury4, suivi de la liste des logins des membres, en commençant par le president du sous jury, le tout séparé par des ;',
  `president1` varchar(64) NOT NULL,
  `president2` varchar(64) NOT NULL,
  `president3` varchar(64) NOT NULL,
  `president4` varchar(64) NOT NULL,
  `membressj1` text,
  `membressj2` text,
  `membressj3` text,
  `membressj4` text,
  UNIQUE KEY `section` (`section`,`session`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='contient les descriptifs des concours';

-- --------------------------------------------------------

--
-- Structure de la table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `section` tinyint(4) NOT NULL,
  `key` varchar(128) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`section`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `people`
--

CREATE TABLE IF NOT EXISTS `people` (
  `NUMSIRHUS` varchar(30) NOT NULL DEFAULT '',
  `concoursid` varchar(10) NOT NULL DEFAULT '',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` tinyint(4) NOT NULL,
  `nom` varchar(64) NOT NULL DEFAULT '',
  `prenom` varchar(64) NOT NULL DEFAULT '',
  `grade` varchar(32) NOT NULL DEFAULT '',
  `audition` text,
  `labo1` text,
  `labo2` text,
  `labo3` text,
  `theme1` text,
  `theme2` text,
  `theme3` text,
  `annee_recrutement` text,
  `genre` varchar(32) NOT NULL DEFAULT '',
  `Info0` text,
  `Info1` text,
  `Info2` text,
  `Info3` text,
  `Info4` text,
  `Info5` text,
  `Info6` text,
  `Info7` text,
  `Info8` text,
  `Info9` text,
  `Info10` text,
  `Info11` text,
  `Info12` text,
  `Info13` text,
  `Info14` text,
  `Info15` text,
  `Info16` text,
  `Info17` text,
  `Info18` text,
  `Info19` text,
  `Info20` text,
  `Info21` text,
  `Info22` text,
  `Info23` text,
  `Info24` text,
  `Info25` text,
  `Info26` text,
  `Info27` text,
  `Info28` text,
  `Info29` text,
  `Info30` text,
  `conflits` text,
  `birth` varchar(20) DEFAULT NULL,
  `diploma` varchar(20) DEFAULT NULL,
  `email` varchar(60) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `concoursid` (`concoursid`,`section`,`nom`,`prenom`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1931609 ;

-- --------------------------------------------------------

--
-- Structure de la table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `DKEY` varchar(22) NOT NULL DEFAULT '',
  `NUMSIRHUS` varchar(30) NOT NULL DEFAULT '',
  `section` tinyint(4) NOT NULL,
  `statut` varchar(32) NOT NULL DEFAULT 'doubleaveugle',
  `statut_celcc` varchar(32) DEFAULT NULL,
  `id_session` varchar(16) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_origine` int(11) NOT NULL,
  `nom` varchar(64) DEFAULT NULL,
  `prenom` varchar(64) DEFAULT NULL,
  `unite` varchar(32) DEFAULT NULL,
  `ecole` varchar(64) DEFAULT NULL,
  `grade_rapport` varchar(32) DEFAULT NULL,
  `type` varchar(16) DEFAULT NULL,
  `type_eval` varchar(4) DEFAULT NULL,
  `intitule` text NOT NULL,
  `concours` varchar(5) DEFAULT NULL,
  `concoursid` varchar(10) NOT NULL DEFAULT '',
  `peopleid` int(11) NOT NULL,
  `rapporteur` varchar(64) DEFAULT NULL,
  `rapporteur2` varchar(64) DEFAULT NULL,
  `rapporteur3` varchar(64) DEFAULT NULL,
  `prerapport` text,
  `prerapport2` text,
  `prerapport3` text,
  `rapport` text,
  `audition` text,
  `avis` varchar(32) DEFAULT NULL,
  `avis1` varchar(32) DEFAULT '',
  `avis2` varchar(32) DEFAULT '',
  `avis3` varchar(32) DEFAULT NULL,
  `auteur` varchar(40) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `avissousjury` text,
  `sousjury` text,
  `Generic0` text,
  `Generic1` text,
  `Generic2` text,
  `Generic3` text,
  `Generic4` text,
  `Generic5` text,
  `Generic6` text,
  `Generic7` text,
  `Generic8` text,
  `Generic9` text,
  `Generic10` text,
  `Generic11` text,
  `Generic12` text,
  `Generic13` text,
  `Generic14` text,
  `Generic15` text,
  `Generic16` text,
  `Generic17` text,
  `Generic18` text,
  `Generic19` text,
  `Generic20` text,
  `Generic21` text,
  `Generic22` text,
  `Generic23` text,
  `Generic24` text,
  `Generic25` text,
  `Generic26` text,
  `Generic27` text,
  `Generic28` text,
  `Generic29` text,
  `Generic30` text,
  `signataire` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=198223 ;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(16) NOT NULL,
  `section` int(11) NOT NULL,
  `nom` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` text,
  PRIMARY KEY (`id`,`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `units`
--

CREATE TABLE IF NOT EXISTS `units` (
  `section` tinyint(11) NOT NULL,
  `nickname` varchar(30) NOT NULL DEFAULT '',
  `code` varchar(32) NOT NULL DEFAULT '',
  `fullname` text NOT NULL,
  `directeur` varchar(40) NOT NULL,
  PRIMARY KEY (`section`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `section_numchaire` varchar(8) NOT NULL DEFAULT '',
  `CID_numchaire` varchar(8) NOT NULL DEFAULT '',
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `login` varchar(64) NOT NULL,
  `college` varchar(2) NOT NULL DEFAULT '',
  `sections` text NOT NULL COMMENT 'list of sections the user belongs to',
  `section_code` varchar(2) NOT NULL DEFAULT '',
  `section_role_code` varchar(5) NOT NULL DEFAULT '',
  `CID_code` varchar(2) NOT NULL DEFAULT '',
  `CID_role_code` varchar(5) NOT NULL DEFAULT '',
  `last_section_selected` tinyint(11) NOT NULL DEFAULT '0',
  `passHash` varchar(40) NOT NULL,
  `description` text NOT NULL,
  `permissions` int(11) NOT NULL DEFAULT '0',
  `email` text NOT NULL,
  `tel` text NOT NULL,
  `dsi` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'utilisateur importe depuis la base dsi',
  PRIMARY KEY (`login`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

