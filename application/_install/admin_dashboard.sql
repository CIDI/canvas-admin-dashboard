-- phpMyAdmin SQL Dump
-- version 3.3.7deb8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 15, 2014 at 12:11 PM
-- Server version: 5.1.73
-- PHP Version: 5.3.3-7+squeeze20

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: ``
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `canvas_account_id` int(11) NOT NULL,
  `account_id` varchar(32) DEFAULT NULL,
  `canvas_parent_id` int(11) NOT NULL,
  `parent_account_id` varchar(32) DEFAULT NULL,
  `name` varchar(32) NOT NULL,
  `status` varchar(32) NOT NULL,
  `synced_at` datetime NOT NULL,
  PRIMARY KEY (`canvas_account_id`),
  KEY `canvas_parent_id` (`canvas_parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `available_reports`
--

CREATE TABLE IF NOT EXISTS `available_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `created_at` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE IF NOT EXISTS `courses` (
  `canvas_course_id` int(11) NOT NULL,
  `course_id` varchar(50) DEFAULT NULL,
  `short_name` varchar(50) NOT NULL,
  `long_name` varchar(100) NOT NULL,
  `canvas_account_id` int(11) NOT NULL,
  `account_id` varchar(32) DEFAULT NULL,
  `canvas_term_id` int(11) NOT NULL,
  `term_id` varchar(32) DEFAULT NULL,
  `status` varchar(32) NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `synced_at` datetime NOT NULL,
  PRIMARY KEY (`canvas_course_id`),
  KEY `canvas_account_id` (`canvas_account_id`),
  KEY `canvas_term_id` (`canvas_term_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `course_codes`
--

CREATE TABLE IF NOT EXISTS `course_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `code_name` varchar(500) CHARACTER SET utf8 NOT NULL,
  `code_value` varchar(100) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `course_meta`
--

CREATE TABLE IF NOT EXISTS `course_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `meta_category_id` int(11) NOT NULL,
  `meta_name` varchar(200) NOT NULL,
  `meta_value` text,
  `synced_at` datetime NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_id` (`course_id`,`meta_category_id`,`meta_name`,`sort`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2868 ;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE IF NOT EXISTS `enrollments` (
  `canvas_course_id` int(11) NOT NULL,
  `course_id` varchar(50) DEFAULT NULL,
  `canvas_user_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role` varchar(32) NOT NULL,
  `canvas_section_id` int(11) DEFAULT NULL,
  `section_id` varchar(32) DEFAULT NULL,
  `status` varchar(32) DEFAULT NULL,
  `canvas_associated_user_id` varchar(32) DEFAULT NULL,
  `associated_user_id` varchar(32) DEFAULT NULL,
  `synced_at` datetime NOT NULL,
  PRIMARY KEY (`canvas_course_id`,`canvas_user_id`,`role`),
  KEY `status` (`status`),
  KEY `canvas_user_id` (`canvas_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `enrollment_counts`
--
CREATE TABLE IF NOT EXISTS `enrollment_counts` (
`role` varchar(32)
,`canvas_course_id` int(11)
,`enrollments` bigint(21)
);
-- --------------------------------------------------------

--
-- Table structure for table `meta_categories`
--

CREATE TABLE IF NOT EXISTS `meta_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(200) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_name` varchar(200) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `terms`
--

CREATE TABLE IF NOT EXISTS `terms` (
  `canvas_term_id` int(11) NOT NULL,
  `term_id` varchar(32) DEFAULT NULL,
  `name` varchar(32) NOT NULL,
  `status` varchar(32) NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `synced_at` datetime NOT NULL,
  PRIMARY KEY (`canvas_term_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `canvas_user_id` int(11) NOT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `login_id` varchar(32) NOT NULL,
  `first_name` varchar(32) DEFAULT NULL,
  `last_name` varchar(32) DEFAULT NULL,
  `email` varchar(32) DEFAULT NULL,
  `status` varchar(32) NOT NULL,
  `synced_at` datetime NOT NULL,
  PRIMARY KEY (`canvas_user_id`),
  KEY `login_id` (`login_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure for view `enrollment_counts`
--
DROP TABLE IF EXISTS `enrollment_counts`;

CREATE ALGORITHM=UNDEFINED DEFINER=`kennethl`@`%` SQL SECURITY DEFINER VIEW `enrollment_counts` AS select `enrollments`.`role` AS `role`,`enrollments`.`canvas_course_id` AS `canvas_course_id`,count(0) AS `enrollments` from `enrollments` group by `enrollments`.`canvas_course_id`,`enrollments`.`role` order by `enrollments`.`canvas_course_id`,`enrollments`.`role`;
