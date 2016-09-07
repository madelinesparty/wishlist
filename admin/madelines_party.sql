-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 06, 2010 at 07:47 AM
-- Server version: 5.1.37
-- PHP Version: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `madelines_party`
--

-- --------------------------------------------------------

--
-- Table structure for table `families`
--

CREATE TABLE IF NOT EXISTS `families` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=33 ;

-- --------------------------------------------------------

--
-- Table structure for table `family_members`
--

CREATE TABLE IF NOT EXISTS `family_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `family_id` int(11) NOT NULL,
  `age` int(4) NOT NULL,
  `gender` enum('m','f','g') COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `family_id` (`family_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=68 ;

-- --------------------------------------------------------

--
-- Table structure for table `list_items`
--

CREATE TABLE IF NOT EXISTS `list_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `family_member_id` int(11) NOT NULL,
  `category` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `family_member_id` (`family_member_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=145 ;

-- --------------------------------------------------------

--
-- Table structure for table `list_items_users_join`
--

CREATE TABLE IF NOT EXISTS `list_items_users_join` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `list_item_id` (`list_item_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=52 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remember` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `remember` (`remember`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `family_members`
--
ALTER TABLE `family_members`
  ADD CONSTRAINT `family_members_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `families` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `list_items`
--
ALTER TABLE `list_items`
  ADD CONSTRAINT `list_items_ibfk_1` FOREIGN KEY (`family_member_id`) REFERENCES `family_members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `list_items_users_join`
--
ALTER TABLE `list_items_users_join`
  ADD CONSTRAINT `list_items_users_join_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `list_items_users_join_ibfk_1` FOREIGN KEY (`list_item_id`) REFERENCES `list_items` (`id`) ON DELETE CASCADE;
