-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 22, 2026 at 11:49 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `users_staff_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users_staff`
--

CREATE TABLE `users_staff` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','staff') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_staff`
--

INSERT INTO `users_staff` (`id`, `name`, `email`, `password`, `role`) VALUES
(2, 'victoria shelly', 'victoriaS@user.org', '$2y$12$vgtVXha/Th882dlELLONSuFMO6idNwjJu3aI8p6uzNnmatVFl9Keu', 'user'),
(3, 'megan', 'meganM@staff.org', '$2y$12$vgtVXha/Th882dlELLONSuFMO6idNwjJu3aI8p6uzNnmatVFl9Keu', 'staff'),
(4, 'joe', 'jomama@gmail.com', '90cf2dae9ceb83f65076428914937487', 'user'),
(5, 'johndoe', 'johndoe@user.org', '81dc9bdb52d04dc20036dbd8313ed055', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users_staff`
--
ALTER TABLE `users_staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users_staff`
--
ALTER TABLE `users_staff`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high') NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'open',
  `dueDate` datetime DEFAULT NULL,
  `assignedTo` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tickets_user_id_idx` (`user_id`),
  CONSTRAINT `tickets_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users_staff` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
