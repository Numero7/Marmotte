-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2012 at 02:38 AM
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
-- Table structure for table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE IF NOT EXISTS `evaluations` (
  `id_session` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_origine` int(11) NOT NULL,
  `nom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `prenom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `unite` varchar(50) CHARACTER SET utf8 NOT NULL,
  `grade` varchar(10) CHARACTER SET utf8 NOT NULL,
  `type` enum('Evaluation-Vague','Evaluation-MiVague','Promotion','Candidature','Suivi-PostEvaluation','Titularisation','ConfirmationAffectation') CHARACTER SET utf8 NOT NULL,
  `rapporteur` text CHARACTER SET utf8 NOT NULL,
  `prerapport` text CHARACTER SET utf8 NOT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=34 ;

--
-- Dumping data for table `evaluations`
--

INSERT INTO `evaluations` (`id_session`, `id`, `id_origine`, `nom`, `prenom`, `unite`, `grade`, `type`, `rapporteur`, `prerapport`, `anciennete_grade`, `date_recrutement`, `production`, `production_notes`, `transfert`, `transfert_notes`, `encadrement`, `encadrement_notes`, `responsabilites`, `responsabilites_notes`, `mobilite`, `mobilite_notes`, `animation`, `animation_notes`, `rayonnement`, `rayonnement_notes`, `rapport`, `avis`, `auteur`, `date`) VALUES
(1, 1, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Blabla', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 06 ...', 'Tres favorable', 'john', '2012-11-21 09:17:26'),
(1, 2, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que....', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Reservé', 'jim', '2012-11-22 12:12:50'),
(1, 3, 3, 'Summer', 'Dona', 'UMR 6666', 'DR2', 'Promotion', 'Jim', 'rgaqerg', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ergqegqge', '', '2012-11-22 12:18:18'),
(1, 4, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que....', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Reservé', '', '2012-11-22 17:15:20'),
(1, 5, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'D''accord, mais alors', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Favorable', '', '2012-11-22 17:22:02'),
(1, 6, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que dfvergfer....', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Reservé', '', '2012-11-22 17:22:59'),
(1, 7, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que dfvergfer....', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Reservé', '', '2012-11-22 17:24:23'),
(1, 8, 3, 'Summer', 'Dona', 'UMR 6666', 'DR2', 'Promotion', 'Jim', 'rgaqerg', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ergqegqge', 'admin', '2012-11-22 17:32:39'),
(1, 9, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que dfvergfer....', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Reservé', 'admin', '2012-11-22 17:51:17'),
(1, 10, 3, 'Summer', 'Dona', 'UMR 6666', 'DR2', 'Promotion', 'Jim', 'rgaqerg', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ewrwr', 'ergqegqge', 'admin', '2012-11-22 19:52:44'),
(1, 11, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que dfvergfer....', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Reservé', 'admin', '2012-11-22 19:53:20'),
(1, 12, 3, 'Summer', 'Dona', 'UMR 6666', 'DR2', 'Promotion', 'Jim', 'rgaqerg', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ewrwr', 'ergqegqge', 'admin', '2012-11-22 19:53:35'),
(1, 13, 3, 'Summer', 'Dona', 'UMR 6666', 'DR2', 'Promotion', 'Jim', 'rgaqerg', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ewrwr', 'ergqegqge', 'admin', '2012-11-22 19:53:58'),
(2, 14, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que dfvergfer....', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Reservé', 'admin', '2012-11-22 20:04:14'),
(2, 15, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que dfvergfer....', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Reservé', 'admin', '2012-11-22 20:04:24'),
(1, 16, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que dfvergfer....', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Reservé', 'admin', '2012-11-22 20:04:45'),
(1, 17, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que dfvergfer....', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Reservé', 'admin', '2012-11-22 20:05:22'),
(2, 18, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que dfvergfer....', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Reservé', 'admin', '2012-11-22 20:05:55'),
(2, 19, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que dfvergfer....', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Reservé', 'admin', '2012-11-22 20:06:57'),
(1, 20, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que dfvergfer....', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Reservé', 'admin', '2012-11-22 20:08:14'),
(2, 21, 3, 'Summer', 'Dona', 'UMR 6666', 'DR2', 'Promotion', 'Jim', 'rgaqerg', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ewrwr', 'ergqegqge', 'admin', '2012-11-22 20:08:21'),
(1, 22, 3, 'Summer', 'Dona', 'UMR 6666', 'DR2', 'Promotion', 'Jim', 'rgaqerg', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ewrwr', 'ergqegqge', 'admin', '2012-11-22 20:09:43'),
(2, 23, 3, 'Summer', 'Dona', 'UMR 6666', 'DR2', 'Promotion', 'Jim', 'rgaqerg', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ewrwr', 'ergqegqge', 'admin', '2012-11-22 20:09:47'),
(1, 24, 3, 'Summer', 'Dona', 'UMR 6666', 'DR2', 'Evaluation-Vague', 'Jim', 'rgaqerg', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ewrwr', 'ergqegqge', 'admin', '2012-11-22 20:10:36'),
(2, 25, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-Vague', 'John', 'Quand meme, je trouve que dfvergfer.... mais en meme temps on peut dire que', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Moyen bof', 'admin', '2012-11-22 20:18:28'),
(2, 26, 26, 'earg', 'er', 'erg', 'erg', '', 'erg', 'erg', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'eg', 'etg', 'admin', '2012-11-22 20:37:55'),
(2, 27, 26, 'Blunt', 'James', 'UMR 7773', 'CR1', 'Evaluation-MiVague', 'Jules', '2-3 trucs assez cools', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section ', 'Assez favorable', 'admin', '2012-11-22 20:38:56'),
(2, 28, 1, 'Pass', 'Joe', 'UMR 7172', 'CR2', 'Evaluation-MiVague', 'John', 'Quand meme, je trouve que dfvergfer.... mais en meme temps on peut dire que', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section 6 du com...', 'Moyen bof', 'admin', '2012-11-22 20:46:45'),
(2, 29, 26, 'Blunt', 'James', 'UMR 7773', 'CR1', 'Promotion', 'Jules', '2-3 trucs assez cools', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'La section ', 'Assez favorable', 'admin', '2012-11-23 00:17:03'),
(1, 30, 30, 'Jo', 'wede', 'oijoih', 'oj', '', 'a', 'oij', 'oh', 'oh', 'ihoonb', 'ohin', 'oi', 'oihj', 'oih', 'oih', 'oih', 'oih', 'oih', 'oih', 'oi', 'ho', 'i', 'hoi', 'h', 'oih', 'admin', '2012-11-23 01:19:40'),
(1, 31, 31, 'Jo', 'wede', 'oijoih', 'oj', '', 'a', 'oij', 'oh', 'oh', 'ihoonb', 'ohin', 'oi', 'oihj', 'oih', 'oih', 'oih', 'oih', 'oih', 'oih', 'oi', 'ho', 'i', 'hoi', 'h', 'oih', 'admin', '2012-11-23 01:19:52'),
(1, 32, 30, 'Jo', 'wede', 'oijoih', 'oj', 'Candidature', 'a', 'oij', 'oh', 'oh', 'ihoonb', 'ohin', 'oi', 'oihj', 'oih', 'oih', 'oih', 'oih', 'oih', 'oih', 'oi', 'ho', 'i', 'hoi', 'h', 'oih', 'admin', '2012-11-23 01:20:57'),
(1, 33, 31, 'Jo', 'wede', 'oijoih', 'oj', 'Evaluation-Vague', 'a', 'oij', 'oh', 'oh', 'ihoonb', 'ohin', 'oi', 'oihj', 'oih', 'oih', 'oih', 'oih', 'oih', 'oih', 'oi', 'ho', 'i', 'hoi', 'h', 'oih', 'admin', '2012-11-23 01:30:23');

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

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `nom`, `date`) VALUES
(1, 'Automne', '2012-11-22 15:48:04'),
(2, 'Printemps', '2013-04-14 12:53:00');

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


INSERT INTO `units` (`nickname`, `code`, `fullname`, `directeur`) VALUES
('LaBRI', 'UMR5800', 'Laboratoire Bordelais de Recherche en Informatique','Pascal WEIL' ),
('LIX', 'UMR7161', 'Laboratoire d''Informatique de l''Ecole Polytechnique','Olivier BOURNEZ');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `login` varchar(40) CHARACTER SET utf8 NOT NULL,
  `passHash` varchar(40) CHARACTER SET utf8 NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`login`, `passHash`, `description`) VALUES
('admin', '$1$/t..az..$dacFT5V./AWiz2RLbvaAp0', 'Administrateur (Yann ou Hugo)');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
