-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2012 at 03:20 PM
-- Server version: 5.5.27-log
-- PHP Version: 5.4.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `marmot`
--

-- --------------------------------------------------------

--
-- Table structure for table `chercheurs`
--



CREATE TABLE IF NOT EXISTS `chercheurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `prenom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `unite` varchar(50) CHARACTER SET utf8 NOT NULL,
  `grade` enum('CR2','CR1','DR2','DR1','DRCE1','DRCE2','ChaireMC','ChairePR','Emerite','MC','PR','PhD','HDR','None') CHARACTER SET utf8 NOT NULL,
  `date_recrutement` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=61 ;

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;

/*
ALTER TABLE `evaluations`
  ADD `labo1` text CHARACTER SET utf8 NOT NULL;
ALTER TABLE `evaluations`
  ADD `labo2` text CHARACTER SET utf8 NOT NULL;
ALTER TABLE `evaluations`
  ADD `labo3` text CHARACTER SET utf8 NOT NULL;
ALTER TABLE `evaluations`
  ADD `theme1` text CHARACTER SET utf8 NOT NULL;
ALTER TABLE `evaluations`
  ADD `theme2` text CHARACTER SET utf8 NOT NULL;
ALTER TABLE `evaluations`
  ADD `theme3` text CHARACTER SET utf8 NOT NULL;
ALTER TABLE `evaluations`
  ADD `theseAnnee` text CHARACTER SET utf8 NOT NULL;
ALTER TABLE `evaluations`
  ADD `theseLieu` text CHARACTER SET utf8 NOT NULL;
ALTER TABLE `evaluations`
  ADD `HDRAnnee` text CHARACTER SET utf8 NOT NULL;
ALTER TABLE `evaluations`
  ADD `HDRLieu` text CHARACTER SET utf8 NOT NULL;
  */
  
CREATE TABLE IF NOT EXISTS `evaluations` (
`statut` enum('vierge','prerapport','rapport','publie','supprime') CHARACTER SET utf8 NOT NULL,
  `id_session` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_origine` int(11) NOT NULL,
  `nom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `prenom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `unite` varchar(50) CHARACTER SET utf8 NOT NULL,
  `ecole` text CHARACTER SET utf8 NOT NULL,
  `grade` enum('CR2','CR1','DR2','DR1','DRCE1','DRCE2','ChaireMC','ChairePR','Emerite','MC','PR','PhD','HDR','None') CHARACTER SET utf8 NOT NULL,
  `type` enum('Evaluation-Vague','Evaluation-MiVague','Promotion','Equivalence','Candidature','Suivi-PostEvaluation','Titularisation','Affectation','Reconstitution','Changement-Directeur','Changement-Directeur-Adjoint','Renouvellement','Association','Ecole','Comite-Evaluation','Generique') CHARACTER SET utf8 NOT NULL,
  `concours` text CHARACTER SET utf8 NOT NULL,
  `rapporteur` text CHARACTER SET utf8 NOT NULL,
  `rapporteur2` text CHARACTER SET utf8 NOT NULL,
  `prerapport` text CHARACTER SET utf8 NOT NULL,
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
  `anneesequivalence` int(11) NOT NULL,
  `anciennete_grade` text CHARACTER SET utf8 NOT NULL,
  `date_recrutement` text CHARACTER SET utf8 NOT NULL,
  `production` text CHARACTER SET utf8 NOT NULL,
  `production_notes` text CHARACTER SET utf8 NOT NULL,
  `transfert` text CHARACTER SET utf8 NOT NULL,
  `transfert_notes` text CHARACTER SET utf8 NOT NULL,
  `encadrement` text CHARACTER SET utf8 NOT NULL,
  `encadrement_notes` text CHARACTER SET utf8 NOT NULL,
  `responsabilites` text CHARACTER SET utf8 NOT NULL,
  `responsabilites_notes` text CHARACTER SET utf8 NOT NULL,
  `mobilite` text CHARACTER SET utf8 NOT NULL,
  `mobilite_notes` text CHARACTER SET utf8 NOT NULL,
  `animation` text CHARACTER SET utf8 NOT NULL,
  `animation_notes` text CHARACTER SET utf8 NOT NULL,
  `rayonnement` text CHARACTER SET utf8 NOT NULL,
  `rayonnement_notes` text CHARACTER SET utf8 NOT NULL,
  `rapport` text CHARACTER SET utf8 NOT NULL,
  `avis` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `auteur` varchar(40) CHARACTER SET utf8 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=61 ;


CREATE TABLE IF NOT EXISTS `candidats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_origine` int(11) NOT NULL,
  `nom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `prenom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `grade` enum('CR2','CR1','DR2','DR1','DRCE1','DRCE2','ChaireMC','ChairePR','Emerite','MC','PR','PhD','HDR','None') CHARACTER SET utf8 NOT NULL,
  `rapporteur` text CHARACTER SET utf8 NOT NULL,
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
  `production` text CHARACTER SET utf8 NOT NULL,
  `transfert` text CHARACTER SET utf8 NOT NULL,
  `encadrement` text CHARACTER SET utf8 NOT NULL,
  `responsabilites` text CHARACTER SET utf8 NOT NULL,
  `mobilite` text CHARACTER SET utf8 NOT NULL,
  `animation` text CHARACTER SET utf8 NOT NULL,
  `rayonnement` text CHARACTER SET utf8 NOT NULL,
  `auteur` varchar(40) CHARACTER SET utf8 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=61 ;

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(60) CHARACTER SET utf8 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `sessions`
--

/*INSERT INTO `sessions` (`id`, `nom`, `date`) VALUES
(1, 'Automne', '2012-11-22 15:48:04'),
(2, 'Concours', '2013-01-14 12:53:00'),
(3, 'Printemps', '2013-04-14 12:53:00');*/

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE IF NOT EXISTS `units` (
  `nickname` text CHARACTER SET utf8 NOT NULL,
  `code` text CHARACTER SET utf8 NOT NULL,
  `fullname` text CHARACTER SET utf8 NOT NULL,
  `directeur` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `units`
--

/*INSERT INTO `units` (`nickname`, `code`, `fullname`, `directeur`) VALUES
('LaBRI', 'UMR5800', 'Laboratoire Bordelais de Recherche en Informatique', 'Pascal WEIL'),
('LIX', 'UMR7161', 'Laboratoire d''Informatique de l''Ecole Polytechnique', 'Olivier BOURNEZ');*/

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `login` varchar(40) CHARACTER SET utf8 NOT NULL,
  `passHash` varchar(40) CHARACTER SET utf8 NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `permissions` int(11) NOT NULL DEFAULT '0',
  `email` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`login`, `passHash`, `description`,`permissions`,`email`) VALUES
('admin', '$1$II0.x4/.$wcsKcSZ6Z0bMUUlWp/cS0/', 'Administrateur (Yann ou Hugo)',1000,'cn6@labri.fr'),
('hugo', '$1$jEk9YVFR$9AQw.nttY7jPKkAlzCX.K/', 'Hugo',1000,'hugo.gimbert@labri.fr'),
('yawn', '$1$/A..aK2.$B2tvrj3auxM2p.T7QXc9Z.', 'Yann Ponty', 0, 'yann.ponty@lix.polytechnique.fr');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;



