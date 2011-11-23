-- phpMyAdmin SQL Dump
-- version 3.3.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 23, 2011 at 10:53 AM
-- Server version: 5.1.50
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `my_test_blog`
--
DROP DATABASE `my_test_blog`;
CREATE DATABASE `my_test_blog` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `my_test_blog`;

-- --------------------------------------------------------

--
-- Table structure for table `labels`
--

DROP TABLE IF EXISTS `labels`;
CREATE TABLE IF NOT EXISTS `labels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `labels`
--

INSERT INTO `labels` (`id`, `text`) VALUES
(1, 'fun'),
(2, 'work'),
(3, 'activities');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE IF NOT EXISTS `posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idAuthor` int(11) NOT NULL,
  `text` text NOT NULL,
  `creationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idAuthor` (`idAuthor`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `idAuthor`, `text`, `creationDate`) VALUES
(1, 1, 'My fun post.', '2011-11-21 00:00:00'),
(2, 1, 'My post about work and activities.', '2011-11-22 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `posts_labels`
--

DROP TABLE IF EXISTS `posts_labels`;
CREATE TABLE IF NOT EXISTS `posts_labels` (
  `idPost` bigint(20) unsigned NOT NULL,
  `idLabel` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idPost`,`idLabel`),
  KEY `idLabel` (`idLabel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `posts_labels`
--

INSERT INTO `posts_labels` (`idPost`, `idLabel`) VALUES
(1, 1),
(2, 2),
(2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL DEFAULT '',
  `nickname` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `nickname`) VALUES
(1, 'email@temail.com', 'MyNick');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `posts_labels`
--
ALTER TABLE `posts_labels`
  ADD CONSTRAINT `posts_labels_ibfk_1` FOREIGN KEY (`idPost`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `posts_labels_ibfk_2` FOREIGN KEY (`idLabel`) REFERENCES `labels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
