-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Lun 12 Janvier 2015 à 23:02
-- Version du serveur :  5.6.10
-- Version de PHP :  5.5.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `cn62`
--
CREATE DATABASE IF NOT EXISTS `panda` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `panda`;

-- --------------------------------------------------------

--
-- Structure de la table `concours`
--

DROP TABLE IF EXISTS `concours`;
CREATE TABLE IF NOT EXISTS `concours` (
  `section` int(11) NOT NULL COMMENT 'numero section ou cid',
  `session` text NOT NULL COMMENT 'l''année du concours',
  `code` varchar(10) NOT NULL COMMENT 'code concours 06/03',
  `intitule` text NOT NULL COMMENT 'intitule du concours ex CR1_BIGDATA',
  `postes` tinyint(4) NOT NULL DEFAULT '0',
  `sousjury1` text NOT NULL COMMENT 'Le code du sousjury, le nom du sous jury, suivi de la liste des logins des membres, en commençant par le president du sous jury, le tout séparé par des ; Si le nom est une chaine vide, la section est constituée en jury pleinier',
  `sousjury2` text NOT NULL COMMENT 'Le nom du sousjury2, suivi de la liste des logins des membres, en commençant par le president du sous jury, le tout séparé par des ;',
  `sousjury3` text NOT NULL COMMENT 'Le nom du sousjury3, suivi de la liste des logins des membres, en commençant par le president du sous jury, le tout séparé par des ;',
  `sousjury4` text NOT NULL COMMENT 'Le nom du sousjury4, suivi de la liste des logins des membres, en commençant par le president du sous jury, le tout séparé par des ;',
  `president1` varchar(32) NOT NULL,
  `president2` varchar(32) NOT NULL,
  `president3` varchar(32) NOT NULL,
  `president4` varchar(32) NOT NULL,
  `membressj1` text,
  `membressj2` text,
  `membressj3` text,
  `membressj4` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='contient les descriptifs des concours';

-- --------------------------------------------------------

--
-- Structure de la table `config`
--

DROP TABLE IF EXISTS `config`;
CREATE TABLE IF NOT EXISTS `config` (
  `section` int(11) NOT NULL,
  `key` text NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `people`
--

DROP TABLE IF EXISTS `people`;
CREATE TABLE IF NOT EXISTS `people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` int(11) NOT NULL,
  `nom` text NOT NULL,
  `prenom` text NOT NULL,
  `grade` varchar(32) NOT NULL DEFAULT '',
  `concourspresentes` varchar(64) DEFAULT '',
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
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=79 ;

-- --------------------------------------------------------

--
-- Structure de la table `reports`
--

DROP TABLE IF EXISTS `reports`;
CREATE TABLE IF NOT EXISTS `reports` (
  `section` int(11) NOT NULL,
  `statut` varchar(32) NOT NULL DEFAULT 'doubleaveugle',
  `id_session` varchar(64) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_origine` int(11) NOT NULL,
  `id_unite` int(11) NOT NULL DEFAULT '0',
  `id_people` int(11) NOT NULL DEFAULT '0',
  `nom` varchar(64) DEFAULT NULL,
  `prenom` varchar(64) DEFAULT NULL,
  `unite` varchar(50) DEFAULT NULL,
  `ecole` varchar(200) DEFAULT NULL,
  `grade_rapport` varchar(32) DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `concours` varchar(32) DEFAULT NULL,
  `rapporteur` varchar(64) DEFAULT NULL,
  `rapporteur2` varchar(64) DEFAULT NULL,
  `rapporteur3` varchar(64) DEFAULT NULL,
  `prerapport` text,
  `prerapport2` text,
  `prerapport3` text,
  `rapport` text,
  `audition` text,
  `avis` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT '',
  `avis1` varchar(32) DEFAULT '',
  `avis2` varchar(32) DEFAULT '',
  `avis3` varchar(32) DEFAULT NULL,
  `auteur` varchar(40) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `avissousjury` text,
  `sousjury` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=61222 ;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(64) NOT NULL,
  `section` int(11) NOT NULL,
  `nom` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `units`
--

DROP TABLE IF EXISTS `units`;
CREATE TABLE IF NOT EXISTS `units` (
  `section` int(11) NOT NULL,
  `nickname` text NOT NULL,
  `code` text NOT NULL,
  `fullname` text NOT NULL,
  `directeur` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(40) NOT NULL,
  `sections` text NOT NULL COMMENT 'list of sections the user belongs to',
  `last_section_selected` tinyint(11) NOT NULL DEFAULT '0',
  `passHash` varchar(40) NOT NULL,
  `description` text NOT NULL,
  `permissions` int(11) NOT NULL DEFAULT '0',
  `email` text NOT NULL,
  `tel` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5759 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
