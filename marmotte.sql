-- phpMyAdmin SQL Dump
-- version 3.5.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 09, 2013 at 11:45 AM
-- Server version: 5.1.63-0+squeeze1
-- PHP Version: 5.3.3-7+squeeze14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `cn6`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidats`
--

DROP TABLE IF EXISTS `candidats`;
CREATE TABLE IF NOT EXISTS `candidats` (
  `cle` text CHARACTER SET utf8 NOT NULL,
  `anneecandidature` int(11) NOT NULL,
  `nom` text CHARACTER SET utf8 NOT NULL,
  `prenom` text CHARACTER SET utf8 NOT NULL,
  `grade` enum('CR2','CR1','DR2','DR1','DRCE1','DRCE2','ChaireMC','ChairePR','Emerite','MC','PR','PhD','HDR','None','chercheur','postdoc','CR1_INRIA') CHARACTER SET utf8 NOT NULL,
  `theseAnnee` text CHARACTER SET utf8 NOT NULL,
  `theseLieu` text CHARACTER SET utf8 NOT NULL,
  `HDRAnnee` text CHARACTER SET utf8 NOT NULL,
  `HDRLieu` text CHARACTER SET utf8 NOT NULL,
  `labo1` text CHARACTER SET utf8 NOT NULL,
  `labo2` text CHARACTER SET utf8 NOT NULL,
  `labo3` text CHARACTER SET utf8 NOT NULL,
  `theme1` text CHARACTER SET utf8 NOT NULL,
  `theme2` text CHARACTER SET utf8 NOT NULL,
  `theme3` text CHARACTER SET utf8 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parcours` text CHARACTER SET utf8 NOT NULL,
  `productionResume` text CHARACTER SET utf8 NOT NULL,
  `projetrecherche` text CHARACTER SET utf8 NOT NULL,
  `concourspresentes` text CHARACTER SET utf8 NOT NULL,
  `fichiers` text CHARACTER SET utf8 NOT NULL,
  `date_recrutement` text CHARACTER SET utf8 NOT NULL,
  `proprietes` text CHARACTER SET utf8 NOT NULL,
  `genre` enum('femme','homme','','') CHARACTER SET utf8 NOT NULL DEFAULT 'homme',
  `theseloc` enum('fr','eu','ru','us','asia','africa','other','southamerica') CHARACTER SET utf8 DEFAULT 'fr'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `chercheurs`
--

DROP TABLE IF EXISTS `chercheurs`;
CREATE TABLE IF NOT EXISTS `chercheurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `prenom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `unite` varchar(50) CHARACTER SET utf8 NOT NULL,
  `grade` enum('CR2','CR1','DR2','DR1','DRCE1','DRCE2','ChaireMC','ChairePR','Emerite','MC','PR','PhD','HDR','None') CHARACTER SET utf8 NOT NULL,
  `date_recrutement` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE IF NOT EXISTS `evaluations` (
  `statut` enum('vierge','prerapport','rapport','publie','supprime') CHARACTER SET utf8 NOT NULL,
  `id_session` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_origine` int(11) NOT NULL,
  `nom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `prenom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `unite` varchar(50) CHARACTER SET utf8 NOT NULL,
  `ecole` text CHARACTER SET utf8 NOT NULL,
  `grade` enum('CR2','CR1','DR2','DR1','DRCE1','DRCE2','ChaireMC','ChairePR','Emerite','MC','PR','PhD','HDR','None','chercheur','postdoc','CR1_INRIA') CHARACTER SET utf8 NOT NULL,
  `type` enum('Evaluation-Vague','Evaluation-MiVague','Promotion','Equivalence','Candidature','Suivi-PostEvaluation','Titularisation','Affectation','Reconstitution','Changement-Directeur','Changement-Directeur-Adjoint','Renouvellement','Association','Ecole','Comite-Evaluation','Generique') CHARACTER SET utf8 NOT NULL,
  `concours` text CHARACTER SET utf8 NOT NULL,
  `rapporteur` text CHARACTER SET utf8 NOT NULL,
  `rapporteur2` text CHARACTER SET utf8 NOT NULL,
  `prerapport` text CHARACTER SET utf8 NOT NULL,
  `anneesequivalence` int(11) NOT NULL,
  `labo1` text CHARACTER SET utf8 NOT NULL,
  `labo2` text CHARACTER SET utf8 NOT NULL,
  `labo3` text CHARACTER SET utf8 NOT NULL,
  `theme1` text CHARACTER SET utf8 NOT NULL,
  `theme2` text CHARACTER SET utf8 NOT NULL,
  `theme3` text CHARACTER SET utf8 NOT NULL,
  `anciennete_grade` text CHARACTER SET utf8 NOT NULL,
  `date_recrutement` text CHARACTER SET utf8 NOT NULL,
  `production` text CHARACTER SET utf8 NOT NULL,
  `transfert` text CHARACTER SET utf8 NOT NULL,
  `encadrement` text CHARACTER SET utf8 NOT NULL,
  `responsabilites` text CHARACTER SET utf8 NOT NULL,
  `mobilite` text CHARACTER SET utf8 NOT NULL,
  `animation` text CHARACTER SET utf8 NOT NULL,
  `rayonnement` text CHARACTER SET utf8 NOT NULL,
  `rapport` text CHARACTER SET utf8 NOT NULL,
  `avis` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `auteur` varchar(40) CHARACTER SET utf8 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prerapport2` text CHARACTER SET utf8 NOT NULL,
  `production2` text CHARACTER SET utf8 NOT NULL,
  `transfert2` text CHARACTER SET utf8 NOT NULL,
  `encadrement2` text CHARACTER SET utf8 NOT NULL,
  `responsabilites2` text CHARACTER SET utf8 NOT NULL,
  `mobilite2` text CHARACTER SET utf8 NOT NULL,
  `animation2` text CHARACTER SET utf8 NOT NULL,
  `rayonnement2` text CHARACTER SET utf8 NOT NULL,
  `avis1` text CHARACTER SET utf8 NOT NULL,
  `avis2` text CHARACTER SET utf8 NOT NULL,
  `avissousjury` text CHARACTER SET utf8 NOT NULL,
  `cleindividu` text CHARACTER SET utf8 NOT NULL,
  `sousjury` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14234 ;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(60) CHARACTER SET utf8 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
CREATE TABLE IF NOT EXISTS `units` (
  `nickname` text CHARACTER SET utf8 NOT NULL,
  `code` text CHARACTER SET utf8 NOT NULL,
  `fullname` text CHARACTER SET utf8 NOT NULL,
  `directeur` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `login` varchar(40) CHARACTER SET utf8 NOT NULL,
  `passHash` varchar(40) CHARACTER SET utf8 NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `permissions` int(11) NOT NULL DEFAULT '0',
  `email` text CHARACTER SET utf8 NOT NULL,
  `tel` text CHARACTER SET utf8 NOT NULL,
  `sousjury` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
