-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Ven 01 Mars 2013 à 13:24
-- Version du serveur: 5.1.63
-- Version de PHP: 5.3.3-7+squeeze14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `cn62`
--

-- --------------------------------------------------------

--
-- Structure de la table `people`
--

DROP TABLE IF EXISTS `people`;
CREATE TABLE IF NOT EXISTS `people` (
  `anneecandidature` text CHARACTER SET utf8 NOT NULL,
  `nom` text CHARACTER SET utf8 NOT NULL,
  `prenom` text CHARACTER SET utf8 NOT NULL,
  `grade` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
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
  `annee_recrutement` text CHARACTER SET utf8 NOT NULL,
  `proprietes` text CHARACTER SET utf8 NOT NULL,
  `genre` enum('femme','homme','') CHARACTER SET utf8 NOT NULL DEFAULT 'homme',
  `theseloc` varchar(8) CHARACTER SET utf8 DEFAULT 'fr',
  `statut_individu` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `reports`
--

DROP TABLE IF EXISTS `reports`;
CREATE TABLE IF NOT EXISTS `reports` (
  `statut` enum('vierge','prerapport','rapport','publie','supprime') NOT NULL,
  `id_session` varchar(64) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_origine` int(11) NOT NULL,
  `nom` varchar(64) NOT NULL,
  `prenom` varchar(64) NOT NULL,
  `unite` varchar(50) NOT NULL,
  `ecole` text NOT NULL,
  `grade` varchar(32) NOT NULL,
  `type` enum('Evaluation-Vague','Evaluation-MiVague','Promotion','Equivalence','Candidature','Suivi-PostEvaluation','Titularisation','Affectation','Reconstitution','Changement-Directeur','Changement-Directeur-Adjoint','Renouvellement','Association','Ecole','Comite-Evaluation','Generique') NOT NULL,
  `concours` text NOT NULL,
  `rapporteur` varchar(32) NOT NULL,
  `rapporteur2` varchar(32) NOT NULL,
  `prerapport` text NOT NULL,
  `anneesequivalence` varchar(32) NOT NULL,
  `production` text NOT NULL,
  `transfert` text NOT NULL,
  `encadrement` text NOT NULL,
  `responsabilites` text NOT NULL,
  `mobilite` text NOT NULL,
  `animation` text NOT NULL,
  `rayonnement` text NOT NULL,
  `rapport` text NOT NULL,
  `avis` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `auteur` varchar(40) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prerapport2` text NOT NULL,
  `production2` text NOT NULL,
  `transfert2` text NOT NULL,
  `encadrement2` text NOT NULL,
  `responsabilites2` text NOT NULL,
  `mobilite2` text NOT NULL,
  `animation2` text NOT NULL,
  `rayonnement2` text NOT NULL,
  `avis1` varchar(32) NOT NULL,
  `avis2` varchar(32) NOT NULL,
  `avissousjury` text NOT NULL,
  `sousjury` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=51943 ;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(64) CHARACTER SET utf8 NOT NULL,
  `nom` varchar(60) CHARACTER SET utf8 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `units`
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
-- Structure de la table `users`
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
