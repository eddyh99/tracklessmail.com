-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 15, 2022 at 09:35 AM
-- Server version: 8.0.31
-- PHP Version: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mifmail_email`
--

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `token` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `name`, `email`, `password`, `token`) VALUES
(1, 'Prince', 'm3rc4n73@gmail.com', '8ec5ad533b2cce856483562127c78de6ddbf6ab4', ''),
(2, 'Camilla', 'camillaguada7@gmail.com', '8ec5ad533b2cce856483562127c78de6ddbf6ab4', ''),
(3, 'Gede Almana', 'almana.gede@gmail.com', '8ec5ad533b2cce856483562127c78de6ddbf6ab4', ''),
(4, 'Nicola', 'disantonicola@gmail.com', '8ec5ad533b2cce856483562127c78de6ddbf6ab4', '');

-- --------------------------------------------------------

--
-- Table structure for table `regismail`
--

CREATE TABLE `regismail` (
  `anonmail` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `reset` tinyint NOT NULL DEFAULT '0',
  `activate` tinyint NOT NULL DEFAULT '0' COMMENT '0: non aktif, 1: aktif',
  `tempcode` varchar(10) NOT NULL,
  `timecreate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `regismail`
--

INSERT INTO `regismail` (`anonmail`, `email`, `reset`, `activate`, `tempcode`, `timecreate`) VALUES
('admin@mifmail.vip', 'codeexx441@gmail.com', 0, 1, '', '2021-08-27 01:02:02'),
('dreamteam@mifmail.vip', 'kingwizemusic@gmail.com', 0, 0, '', '2021-08-30 02:23:07'),
('eddyh99@mifmail.vip', 'eddyh99@gmail.com', 0, 1, '', '2021-08-18 00:52:04'),
('m3rc4n73@mifmail.vip', 'm3rc4n73@gmail.com', 0, 1, 'UhoBPQMdl', '2021-08-18 00:36:04'),
('prince@mifmail.vip', 'principe.nerini@gmail.com', 0, 1, '', '2021-08-18 00:52:04'),
('Skylest86@mifmail.vip', 'skylest7@gmail.com', 0, 1, '', '2022-01-24 20:00:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `regismail`
--
ALTER TABLE `regismail`
  ADD PRIMARY KEY (`anonmail`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
