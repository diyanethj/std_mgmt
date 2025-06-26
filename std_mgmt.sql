-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 26, 2025 at 12:53 PM
-- Server version: 8.2.0
-- PHP Version: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `std_mgmt`
--

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lead_id` int NOT NULL,
  `document_type` enum('nic','education','work_experience','birth_certificate') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lead_id` (`lead_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `lead_id`, `document_type`, `file_path`, `uploaded_at`) VALUES
(8, 648, 'nic', 'C:/wamp64/www/std_mgmt/uploads/documents/685d40a3bc507_685d20140e088_Envelop- Solid 1.docx', '2025-06-26 12:44:19'),
(9, 648, 'education', 'C:/wamp64/www/std_mgmt/uploads/documents/685d40a8e7520_685d17d99e62d_Envelop- Solid 2.docx', '2025-06-26 12:44:24'),
(10, 648, 'work_experience', 'C:/wamp64/www/std_mgmt/uploads/documents/685d40b565649_Envelop- Solid 1.docx', '2025-06-26 12:44:37');

-- --------------------------------------------------------

--
-- Table structure for table `followups`
--

DROP TABLE IF EXISTS `followups`;
CREATE TABLE IF NOT EXISTS `followups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lead_id` int NOT NULL,
  `number` int NOT NULL,
  `followup_date` date NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lead_id` (`lead_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `followups`
--

INSERT INTO `followups` (`id`, `lead_id`, `number`, `followup_date`, `comment`, `created_at`) VALUES
(6, 648, 2, '2025-06-26', 'RNR', '2025-06-26 12:45:15');

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
CREATE TABLE IF NOT EXISTS `leads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `form_name` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `permanent_address` text,
  `work_experience` text,
  `assigned_user_id` int DEFAULT NULL,
  `status` enum('new','assigned','pending_registration','registered','declined') DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assigned_user_id` (`assigned_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=651 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `form_name`, `full_name`, `email`, `phone`, `permanent_address`, `work_experience`, `assigned_user_id`, `status`, `created_at`) VALUES
(649, 'Workshop on Import & Export (May Batch) 2025', 'Meshan', 'ceylonpots@gmail.com', '94715121512', NULL, NULL, 2, 'declined', '2025-06-26 12:42:51'),
(650, 'Workshop on Import & Export (May Batch) 2025', 'Noordeen Nawoor-pichchai', 'Quicknet25@gmail.com', '94777731608', NULL, NULL, 2, 'registered', '2025-06-26 12:42:51'),
(648, 'Workshop on Import & Export (May Batch) 2025', 'Chameen Handungoda', 'chameenhandugoda@yahoo.com', '94777747701', 'Welimada', '1 year', 2, 'assigned', '2025-06-26 12:42:51'),
(647, 'Workshop on Import & Export (May Batch) 2025', 'Gentlemen Politics Mahesh', 'liyanagethushara77@gmail.com', '94727500800', NULL, NULL, 2, 'assigned', '2025-06-26 12:42:51'),
(645, 'Workshop on Import & Export (May Batch) 2025', 'Sarujan', 'sarusarujan20050904@gmail.com', '94771676159', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(646, 'Workshop on Import & Export (May Batch) 2025', 'Ranjana Gunawardana', 'ranjanag.mrt.89@gmail.com', '94772106046', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(642, 'Workshop on Import & Export (May Batch) 2025', 'Mohamed Ahsan', 'aishooahsan@gmail.com', '94750671111', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(643, 'Workshop on Import & Export (May Batch) 2025', 'Raveenthirarajah Nilaxshan', 'nilaxshan83@gmail.com', '94775004957', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(644, 'Workshop on Import & Export (May Batch) 2025', 'Dilakshan Dls', 'dilakslakshan@gmail.com', '94757127426', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(641, 'Workshop on Import & Export (May Batch) 2025', 'N. Denarshan', 'ndenarshan07@gmail.com', '94777725064', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(640, 'Workshop on Import & Export (May Batch) 2025', 'Mahmooth Majith', 'majithnat@gmail.com', '94778729618', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(639, 'Workshop on Import & Export (May Batch) 2025', 'Abdul Basith Thihariya', 'miabbasith@gmail.com', '94777697589', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(638, 'Workshop on Import & Export (May Batch) 2025', 'Kengalingam Kirushanth  in UAE', 'kengalingamkirushanth@yahoo.com', '94754404092', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(637, 'Workshop on Import & Export (May Batch) 2025', 'MR.HAPPY', 'umarcader2303@gmail.com', '94772380770', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(634, 'Workshop on Import & Export (May Batch) 2025', 'Karuna Susil Seneviratne', 'karunasusil@yahoo.com', '94712733779', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(635, 'Workshop on Import & Export (May Batch) 2025', 'Ronny Amalnath', 'ronny.amalnath@yahoo.com', '94770411986', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(636, 'Workshop on Import & Export (May Batch) 2025', 'Dileepa Suranjith Waduge', 'singhaconstruction04@gmail.com', '94714137571', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(633, 'Workshop on Import & Export (May Batch) 2025', 'Amjad Mohideen', 'amjad_ac123@rocketmail.com', '94777629840', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(632, 'Workshop on Import & Export (May Batch) 2025', 'Prabasha Thilakarathne', 'buddhipt@gmail.com', '94332292945', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(631, 'Workshop on Import & Export (May Batch) 2025', 'pmkumara', 'pmkumara5@gmail.com', '94772448985', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(630, 'Workshop on Import & Export (May Batch) 2025', 'Kavindu Piyathilaka', 'kavindupiyathilaka@gmail.com', '94778107634', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(629, 'Workshop on vat', 'Mohamed', 'maxmaxzahir123@gmail.com', '94773792626', NULL, NULL, 2, 'assigned', '2025-06-26 12:42:51'),
(628, 'Workshop on vat', 'Shashi Edirisinghe', 'shashiediri123@gmail.com', '94766715233', NULL, NULL, 2, 'assigned', '2025-06-26 12:42:51'),
(627, 'Workshop on vat', 'Ragulan Kanapathippillai', 'kragulan95@gmail.com', '94768880808', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51'),
(626, 'Workshop on vat', 'Mohamed Hasanar Azeez Ali', 'azeezali21@gmail.com', '94754330330', NULL, NULL, NULL, 'new', '2025-06-26 12:42:51');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

DROP TABLE IF EXISTS `registrations`;
CREATE TABLE IF NOT EXISTS `registrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lead_id` int NOT NULL,
  `marketing_manager_approval` enum('pending','accepted','declined') DEFAULT 'pending',
  `academic_user_approval` enum('pending','accepted','declined') DEFAULT 'pending',
  `status` enum('pending','completed','declined') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lead_id` (`lead_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `lead_id`, `marketing_manager_approval`, `academic_user_approval`, `status`, `created_at`, `updated_at`) VALUES
(5, 650, 'accepted', 'accepted', 'completed', '2025-06-26 12:43:56', '2025-06-26 12:49:37'),
(4, 649, 'declined', 'declined', 'declined', '2025-06-26 12:43:50', '2025-06-26 12:52:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','marketing_manager','marketing_user','academic_user','finance_user') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$sr1MTSPPBcCJWnxBbVocl.13aDyhm2oHQt39PPDC4jnnorqGzDrvC', 'admin', '2025-06-26 03:27:47'),
(2, 'marketing1', '$2y$10$sr1MTSPPBcCJWnxBbVocl.13aDyhm2oHQt39PPDC4jnnorqGzDrvC', 'marketing_user', '2025-06-26 09:00:54'),
(3, 'manager', '$2y$10$sr1MTSPPBcCJWnxBbVocl.13aDyhm2oHQt39PPDC4jnnorqGzDrvC', 'marketing_manager', '2025-06-26 09:01:04'),
(4, 'academic', '$2y$10$sr1MTSPPBcCJWnxBbVocl.13aDyhm2oHQt39PPDC4jnnorqGzDrvC', 'academic_user', '2025-06-26 09:01:14'),
(5, 'finance', '$2y$10$sr1MTSPPBcCJWnxBbVocl.13aDyhm2oHQt39PPDC4jnnorqGzDrvC', 'finance_user', '2025-06-26 09:01:33'),
(6, 'marketing2', '$2y$10$sr1MTSPPBcCJWnxBbVocl.13aDyhm2oHQt39PPDC4jnnorqGzDrvC', 'marketing_user', '2025-06-26 09:02:08');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
