-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jun 20, 2021 at 01:21 PM
-- Server version: 10.4.13-MariaDB
-- PHP Version: 7.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `login_register`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `email` varchar(128) NOT NULL,
  `password` varchar(256) NOT NULL,
  `remember_me` tinyint(1) NOT NULL DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `token` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `name` (`name`),
  KEY `remember_me` (`remember_me`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `name`, `email`, `password`, `remember_me`, `last_login`, `token`) VALUES
(1, 'Staark', 'ionuzcostin@gmail.com', '$2y$12$OhN7/jNz4W8j3.Y.XJflY.lYtqWIDTlAQEeEEZkedmb3Duk5AncsG', 0, '2021-06-20 12:58:35', 'qwnLsmyVXU6ceYa3DkzE4oNv80GfHIST'),
(2, 'Test User', 'test_user@yahoo.com', '$2y$12$MbsIuzQfGaWiXxyqSAkYy..H27KMVZ/yBWUKDtzceQRE3gYuWHBIa', 0, NULL, ''),
(19, 'Test User23', 'test_user23@yahoo.com', '$2y$12$kZPhNn6jF3XSDcfcZTjCR.eDPWMghPt2HL5oo.Z1EYTPLGfK30tzy', 1, '2021-06-20 13:20:33', 'CVATHvQ4iPkpjzsnNLI6c5UtguSaRdye');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `token` varchar(32) NOT NULL,
  `email` varchar(128) NOT NULL,
  `key` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`email`),
  UNIQUE KEY `key` (`key`),
  UNIQUE KEY `token` (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`token`, `email`, `key`, `password`, `active`) VALUES
('CVATHvQ4iPkpjzsnNLI6c5UtguSaRdye', 'test_user23@yahoo.com', 'e207fi0devpnhbk9hjralbkqmr', '$2y$12$kZPhNn6jF3XSDcfcZTjCR.eDPWMghPt2HL5oo.Z1EYTPLGfK30tzy', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
