-- phpMyAdmin SQL Dump
-- version 3.3.7deb8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 29, 2014 at 10:04 PM
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
  `canvas_term_id` int(11) NOT NULL,
  `institution_id` int(11) NOT NULL,
  PRIMARY KEY (`canvas_account_id`,`canvas_term_id`,`institution_id`),
  KEY `canvas_parent_id` (`canvas_parent_id`),
  KEY `canvas_term_id` (`canvas_term_id`),
  KEY `institution_id` (`institution_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `account_meta`
--

CREATE TABLE IF NOT EXISTS `account_meta` (
  `canvas_account_id` int(11) NOT NULL,
  `canvas_term_id` int(11) NOT NULL,
  `depth` int(11) NOT NULL,
  `rght` int(11) DEFAULT NULL,
  `lft` int(11) DEFAULT NULL,
  `institution_id` int(11) NOT NULL,
  `whitelist` tinyint(1) NOT NULL,
  PRIMARY KEY (`canvas_account_id`,`canvas_term_id`,`institution_id`),
  KEY `depth` (`depth`),
  KEY `right` (`rght`),
  KEY `left` (`lft`),
  KEY `institution_id` (`institution_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
  `institution_id` int(11) NOT NULL,
  PRIMARY KEY (`canvas_course_id`,`canvas_term_id`,`institution_id`),
  KEY `canvas_account_id` (`canvas_account_id`),
  KEY `canvas_term_id` (`canvas_term_id`),
  KEY `status` (`status`),
  KEY `institution_id` (`institution_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `canvas_term_id` int(11) NOT NULL,
  `institution_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_id` (`course_id`,`meta_category_id`,`meta_name`,`sort`,`canvas_term_id`,`institution_id`),
  KEY `institution_id` (`institution_id`),
  KEY `canvas_term_id` (`canvas_term_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
  `canvas_term_id` int(11) NOT NULL,
  `institution_id` int(11) NOT NULL,
  PRIMARY KEY (`canvas_course_id`,`canvas_user_id`,`role`,`canvas_term_id`,`institution_id`),
  KEY `status` (`status`),
  KEY `canvas_user_id` (`canvas_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_counts`
--

CREATE TABLE IF NOT EXISTS `enrollment_counts` (
  `role` varchar(128) NOT NULL,
  `canvas_course_id` int(11) NOT NULL,
  `institution_id` int(11) NOT NULL,
  `canvas_term_id` int(11) NOT NULL,
  `enrollments` int(11) NOT NULL,
  PRIMARY KEY (`role`,`canvas_course_id`,`institution_id`,`canvas_term_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `institutions`
--

CREATE TABLE IF NOT EXISTS `institutions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_domain` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `primary_canvas_account_id` int(11) DEFAULT NULL,
  `oauth_token` varchar(300) DEFAULT NULL,
  `slug` varchar(128) NOT NULL,
  `logo` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_domain` (`api_domain`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `meta_categories`
--

CREATE TABLE IF NOT EXISTS `meta_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(200) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf8 NOT NULL,
  `description` text NOT NULL,
  `sql_query` text,
  `executable` tinyint(1) DEFAULT NULL,
  `code` varchar(32) NOT NULL,
  `institution_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

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
  `institution_id` int(11) NOT NULL,
  PRIMARY KEY (`canvas_term_id`,`institution_id`)
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
  `canvas_term_id` int(11) NOT NULL,
  `institution_id` int(11) NOT NULL,
  PRIMARY KEY (`canvas_user_id`,`canvas_term_id`,`institution_id`),
  KEY `login_id` (`login_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `xlists`
--

CREATE TABLE IF NOT EXISTS `xlists` (
  `canvas_xlist_course_id` int(11) NOT NULL,
  `xlist_course_id` varchar(100) NOT NULL,
  `canvas_section_id` int(11) NOT NULL,
  `section_id` varchar(100) NOT NULL,
  `status` varchar(32) NOT NULL,
  `institution_id` int(11) NOT NULL,
  `canvas_term_id` int(11) NOT NULL,
  `synced_at` datetime NOT NULL,
  PRIMARY KEY (`canvas_xlist_course_id`,`canvas_section_id`,`institution_id`,`canvas_term_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Reports querries
--

INSERT INTO `reports` (`id`, `name`, `description`, `sql_query`, `executable`, `code`, `institution_id`) VALUES
(1, 'Syllabus', 'Pull syllabus usage from Canvas for courses with student enrollments per term. Track if using Canvas and if the syllabus section is used. If there is a syllabus, link to syllabus. Make sortable by department/college/instructor. ', 'SELECT \r\nc.canvas_course_id, c.course_id, c.short_name as course_short_name, c.long_name as course_long_name, c.canvas_account_id, c.canvas_term_id, c.term_id, c.status as course_status, c.start_date as course_start_date, c.end_date as course_end_date, c.synced_at as course_synced_at, c.institution_id,\r\nec.role as enrollment_type, ec.enrollments,\r\na.account_id, a.canvas_parent_id, a.parent_account_id,\r\na.name as account_name, a.status as account_status, a.synced_at as account_synced_at,\r\nam.lft, am.rght, am.depth,\r\np_a.name as parent_account_name, p_a.status as parent_account_status, p_a.synced_at as parent_account_synced_at,\r\np_am.lft as parent_lft, p_am.rght as parent_rght, p_am.depth as parent_depth,\r\ne.canvas_user_id as teacher_canvas_user_id, e.user_id as teacher_user_id, e.canvas_section_id, e.section_id, e.status as teacher_status, e.canvas_associated_user_id, e.associated_user_id, e.synced_at as teacher_synced_at, \r\nu.login_id as teacher_login_id, u.first_name as teacher_first_name, u.last_name as teacher_last_name, u.email as teacher_email, u.synced_at as users_synced_at, \r\ncm.id as course_meta_id, cm.meta_category_id, cm.meta_name, cm.meta_value, cm.synced_at as course_meta_synced_at, cm.sort as course_meta_sort\r\nFROM `courses` as c\r\nJOIN enrollment_counts as ec ON (\r\n    c.canvas_course_id = ec.canvas_course_id\r\n    AND ec.role = ''student''\r\n    AND ec.enrollments > 0\r\n    AND c.canvas_term_id = :term\r\n    AND ec.institution_id = :institution\r\n)\r\nJOIN accounts as a ON (\r\n    c.canvas_account_id = a.canvas_account_id\r\n    AND a.institution_id = :institution\r\n)\r\nJOIN account_meta as am ON (\r\n    a.canvas_account_id = am.canvas_account_id \r\n    AND am.canvas_term_id = c.canvas_term_id\r\n    AND am.institution_id = :institution\r\n)\r\nJOIN accounts as p_a ON (\r\n    a.canvas_parent_id = p_a.canvas_account_id\r\n    AND p_a.institution_id = :institution\r\n)\r\nJOIN account_meta as p_am ON (\r\n    a.canvas_parent_id = p_am.canvas_account_id \r\n    AND p_am.canvas_term_id = c.canvas_term_id\r\n    AND p_am.institution_id = :institution\r\n)\r\nJOIN enrollments as e ON (\r\n    c.canvas_course_id = e.canvas_course_id\r\n    AND e.role = ''teacher''\r\n    AND e.institution_id = :institution\r\n)\r\nJOIN users as u ON (\r\n    e.canvas_user_id = u.canvas_user_id\r\n    AND u.institution_id = :institution\r\n)\r\nLEFT JOIN course_meta as cm ON (\r\n    c.canvas_course_id = cm.course_id\r\n    AND cm.meta_category_id = 1\r\n    AND meta_name = ''syllabus_body''\r\n    AND cm.institution_id = :institution\r\n) \r\nWHERE c.institution_id = :institution\r\nORDER BY p_am.lft, p_am.rght, p_a.name, am.lft, am.rght, a.name, a.canvas_account_id, c.long_name, c.canvas_course_id, u.last_name', 1, 'syllabus/dashboard', NULL),
(2, 'LTI Tools', 'A list of all LTI tools being used. When it was added, what privacy level, etc. Sortable by tool/course/department/college.', 'SELECT \r\ncount(c.canvas_course_id) as course_count,\r\nc.institution_id,\r\ncm_name.id as cm_name_id, cm_name.meta_name as cm_name_meta_name, cm_name.meta_value as tool_name,\r\n    cm_name.synced_at as cm_name_synced_at, cm_name.course_id as cm_name_course_id, cm_name.sort as cm_name_sort,\r\ncm_icon.id as cm_icon_id, cm_icon.meta_name as cm_icon_meta_name, cm_icon.meta_value as tool_icon,\r\n    cm_icon.synced_at as cm_icon_synced_at, cm_icon.course_id as cm_icon_course_id, cm_icon.sort as cm_icon_sort\r\nFROM (\r\n	SELECT * FROM courses AS t\r\n	WHERE t.canvas_term_id = :term\r\n    AND t.institution_id = :institution\r\n	) AS c\r\nJOIN (\r\n	SELECT * FROM enrollment_counts AS t\r\n	WHERE t.role = ''student''\r\n    AND t.enrollments > 0\r\n	AND t.canvas_term_id = :term\r\n    AND t.institution_id = :institution\r\n) AS ec ON ( c.canvas_course_id = ec.canvas_course_id )\r\nJOIN (\r\n	SELECT * FROM course_meta AS t\r\n    WHERE t.meta_category_id = 2\r\n    AND t.meta_name = ''name''\r\n	AND t.canvas_term_id = :term\r\n    AND t.institution_id = :institution\r\n) AS cm_name ON ( c.canvas_course_id = cm_name.course_id )\r\nLEFT JOIN (\r\n	SELECT * FROM course_meta as t\r\n    WHERE t.meta_category_id = 2\r\n    AND t.meta_name = ''icon_url''\r\n	AND t.canvas_term_id = :term\r\n    AND t.institution_id = :institution\r\n) AS cm_icon ON (\r\n    cm_name.course_id = cm_icon.course_id\r\n    AND cm_name.sort = cm_icon.sort\r\n) \r\nGROUP BY tool_name\r\nORDER BY tool_name', 1, 'lti/tools', NULL),
(3, 'LTI Course Tools', '', 'SELECT * \r\nFROM course_meta\r\nWHERE meta_category_id = 2\r\nAND course_id = :course\r\nAND institution_id = :institution\r\nORDER BY sort, meta_name', 1, 'lti/course_tools', NULL),
(4, 'LTI Tool Courses', 'List all of the courses for using an LTI tool based on the tool name', 'SELECT \r\nc.institution_id, c.long_name, c.course_id,\r\ncm_name.id as cm_name_id, cm_name.meta_name as cm_name_meta_name, cm_name.meta_value as tool_name,\r\n    cm_name.synced_at as cm_name_synced_at, cm_name.course_id as cm_name_course_id, cm_name.sort as cm_name_sort,\r\ncm_icon.id as cm_icon_id, cm_icon.meta_name as cm_icon_meta_name, cm_icon.meta_value as tool_icon,\r\n    cm_icon.synced_at as cm_icon_synced_at, cm_icon.course_id as cm_icon_course_id, cm_icon.sort as cm_icon_sort\r\nFROM (\r\n    SELECT * FROM courses\r\n    WHERE canvas_term_id = 522\r\n    AND institution_id = 3\r\n    ) AS c\r\nJOIN (\r\n    SELECT * FROM enrollment_counts\r\n    WHERE role = ''student''\r\n    AND enrollments > 0\r\n    AND canvas_term_id = 522\r\n    AND institution_id = 3\r\n) AS ec ON ( c.canvas_course_id = ec.canvas_course_id )\r\nJOIN (\r\n    SELECT * FROM course_meta\r\n    WHERE meta_category_id = 2\r\n    AND meta_name = ''name''\r\n    AND meta_value = ''Dropbox''\r\n    AND canvas_term_id = 522\r\n    AND institution_id = 3\r\n) AS cm_name ON ( c.canvas_course_id = cm_name.course_id )\r\nLEFT JOIN (\r\n    SELECT * FROM course_meta\r\n    WHERE meta_category_id = 2\r\n    AND meta_name = ''icon_url''\r\n    AND canvas_term_id = 522\r\n    AND institution_id = 3\r\n) AS cm_icon ON (\r\n    cm_name.course_id = cm_icon.course_id\r\n    AND cm_name.sort = cm_icon.sort\r\n) \r\nORDER BY c.long_name ASC', 1, 'lti/courses', NULL);

