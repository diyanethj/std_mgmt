-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 23, 2025 at 05:32 AM
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
  `document_type` enum('nic_passport','academic_docs','diploma_certificate','employment_history','birth_certificate','passport_photos') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lead_id` (`lead_id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
CREATE TABLE IF NOT EXISTS `leads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `form_name` varchar(100) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `nic_number` varchar(12) DEFAULT NULL,
  `passport_number` varchar(20) DEFAULT NULL,
  `permanent_address` text,
  `current_address` text,
  `postcode` varchar(10) DEFAULT NULL,
  `mobile_no` varchar(15) DEFAULT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  `office_address` text,
  `office_email` varchar(100) DEFAULT NULL,
  `parent_guardian_name` varchar(100) DEFAULT NULL,
  `parent_contact_number` varchar(15) DEFAULT NULL,
  `parent_address` text,
  `company_institution` varchar(100) DEFAULT NULL,
  `work_experience` text,
  `assigned_user_id` int DEFAULT NULL,
  `status` enum('new','assigned','pending_registration','registered','declined') DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `registration_status` varchar(50) DEFAULT 'pending',
  `payment_plan_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assigned_user_id` (`assigned_user_id`),
  KEY `payment_plan_id` (`payment_plan_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1226 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `form_name`, `title`, `full_name`, `email`, `phone`, `date_of_birth`, `gender`, `nationality`, `marital_status`, `nic_number`, `passport_number`, `permanent_address`, `current_address`, `postcode`, `mobile_no`, `email_address`, `office_address`, `office_email`, `parent_guardian_name`, `parent_contact_number`, `parent_address`, `company_institution`, `work_experience`, `assigned_user_id`, `status`, `created_at`, `registration_status`, `payment_plan_id`) VALUES
(1222, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Gentlemen Politics Mahesh', 'liyanagethushara77@gmail.com', '94727500800', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'pending_registration', '2025-07-23 05:29:08', 'pending', NULL),
(1223, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Chameen Handungoda', 'chameenhandugoda@yahoo.com', '94777747701', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'pending_registration', '2025-07-23 05:29:08', 'pending', NULL),
(1225, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Noordeen Nawoor-pichchai', 'Quicknet25@gmail.com', '94777731608', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'pending_registration', '2025-07-23 05:29:08', 'pending', NULL),
(1224, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Meshan', 'ceylonpots@gmail.com', '94715121512', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'pending_registration', '2025-07-23 05:29:08', 'pending', NULL),
(1221, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Ranjana Gunawardana', 'ranjanag.mrt.89@gmail.com', '94772106046', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'pending_registration', '2025-07-23 05:29:08', 'pending', NULL),
(1220, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Sarujan', 'sarusarujan20050904@gmail.com', '94771676159', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, 'assigned', '2025-07-23 05:29:08', 'pending', NULL),
(1201, 'Workshop on vat', NULL, 'Mohamed Hasanar Azeez Ali', 'azeezali21@gmail.com', '94754330330', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1202, 'Workshop on vat', NULL, 'Ragulan Kanapathippillai', 'kragulan95@gmail.com', '94768880808', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1203, 'Workshop on vat', NULL, 'Shashi Edirisinghe', 'shashiediri123@gmail.com', '94766715233', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1204, 'Workshop on vat', NULL, 'Mohamed', 'maxmaxzahir123@gmail.com', '94773792626', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1205, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Kavindu Piyathilaka', 'kavindupiyathilaka@gmail.com', '94778107634', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, 'assigned', '2025-07-23 05:29:08', 'pending', NULL),
(1206, 'Workshop on Import & Export (May Batch) 2025', NULL, 'pmkumara', 'pmkumara5@gmail.com', '94772448985', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1207, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Prabasha Thilakarathne', 'buddhipt@gmail.com', '94332292945', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1208, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Amjad Mohideen', 'amjad_ac123@rocketmail.com', '94777629840', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1209, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Karuna Susil Seneviratne', 'karunasusil@yahoo.com', '94712733779', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1210, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Ronny Amalnath', 'ronny.amalnath@yahoo.com', '94770411986', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1211, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Dileepa Suranjith Waduge', 'singhaconstruction04@gmail.com', '94714137571', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1212, 'Workshop on Import & Export (May Batch) 2025', NULL, 'MR.HAPPY', 'umarcader2303@gmail.com', '94772380770', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1213, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Kengalingam Kirushanth  in UAE', 'kengalingamkirushanth@yahoo.com', '94754404092', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1214, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Abdul Basith Thihariya', 'miabbasith@gmail.com', '94777697589', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1219, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Dilakshan Dls', 'dilakslakshan@gmail.com', '94757127426', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1218, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Raveenthirarajah Nilaxshan', 'nilaxshan83@gmail.com', '94775004957', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1217, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Mohamed Ahsan', 'aishooahsan@gmail.com', '94750671111', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1215, 'Workshop on Import & Export (May Batch) 2025', NULL, 'Mahmooth Majith', 'majithnat@gmail.com', '94778729618', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL),
(1216, 'Workshop on Import & Export (May Batch) 2025', NULL, 'N. Denarshan', 'ndenarshan07@gmail.com', '94777725064', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'new', '2025-07-23 05:29:08', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lead_payment_plans`
--

DROP TABLE IF EXISTS `lead_payment_plans`;
CREATE TABLE IF NOT EXISTS `lead_payment_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lead_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `assigned_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lead_id` (`lead_id`),
  KEY `plan_id` (`plan_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lead_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `receipt_path` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `payment_name` varchar(100) DEFAULT NULL,
  `plan_id` int DEFAULT NULL,
  `installment_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_id` (`lead_id`),
  KEY `plan_id` (`plan_id`),
  KEY `installment_id` (`installment_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_plans`
--

DROP TABLE IF EXISTS `payment_plans`;
CREATE TABLE IF NOT EXISTS `payment_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment_plans`
--

INSERT INTO `payment_plans` (`id`, `plan_name`, `total_amount`, `created_at`, `updated_at`) VALUES
(7, '12-MONTH DIPLOMA AND HIGHER DIPLOMA PROGRAMS - 6-MONTH INSTALLMENT PLAN (In Favour of CSBM LKR)', 90000.00, '2025-07-22 15:46:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_records`
--

DROP TABLE IF EXISTS `payment_records`;
CREATE TABLE IF NOT EXISTS `payment_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lead_id` int NOT NULL,
  `plan_installment_id` int NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `invoice_path` varchar(255) DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_id` (`lead_id`),
  KEY `plan_installment_id` (`plan_installment_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plan_installments`
--

DROP TABLE IF EXISTS `plan_installments`;
CREATE TABLE IF NOT EXISTS `plan_installments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plan_id` int NOT NULL,
  `installment_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL,
  `due_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `plan_installments`
--

INSERT INTO `plan_installments` (`id`, `plan_id`, `installment_name`, `amount`, `created_at`, `due_date`) VALUES
(28, 7, '6th Installment', 10000.00, '2025-07-22 15:46:54', '2026-02-01'),
(27, 7, '5th Installment', 15000.00, '2025-07-22 15:46:54', '2026-01-01'),
(26, 7, '4th Installment', 15000.00, '2025-07-22 15:46:54', '2025-12-01'),
(25, 7, '3rd Installment', 15000.00, '2025-07-22 15:46:54', '2025-11-01'),
(24, 7, '2nd Installment', 15000.00, '2025-07-22 15:46:54', '2025-10-01'),
(23, 7, '1st Installment', 15000.00, '2025-07-22 15:46:54', '2025-09-01'),
(22, 7, 'Registration Fee', 5000.00, '2025-07-22 15:46:54', '2025-08-01');

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
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `lead_id`, `marketing_manager_approval`, `academic_user_approval`, `status`, `created_at`, `updated_at`) VALUES
(35, 1221, 'pending', 'pending', 'pending', '2025-07-23 05:29:51', '2025-07-23 05:29:51'),
(34, 1224, 'pending', 'pending', 'pending', '2025-07-23 05:29:50', '2025-07-23 05:29:50'),
(33, 1225, 'pending', 'pending', 'pending', '2025-07-23 05:29:49', '2025-07-23 05:29:49'),
(32, 1223, 'pending', 'pending', 'pending', '2025-07-23 05:29:48', '2025-07-23 05:29:48'),
(31, 1222, 'pending', 'pending', 'pending', '2025-07-23 05:29:47', '2025-07-23 05:29:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','marketing_manager','marketing_user','academic_user','finance_user','student') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lead_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `lead_id` (`lead_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `lead_id`) VALUES
(1, 'admin', '$2y$10$sr1MTSPPBcCJWnxBbVocl.13aDyhm2oHQt39PPDC4jnnorqGzDrvC', 'admin', '2025-06-26 03:27:47', NULL),
(2, 'marketing1', '$2y$10$sr1MTSPPBcCJWnxBbVocl.13aDyhm2oHQt39PPDC4jnnorqGzDrvC', 'marketing_user', '2025-06-26 09:00:54', NULL),
(3, 'manager', '$2y$10$sr1MTSPPBcCJWnxBbVocl.13aDyhm2oHQt39PPDC4jnnorqGzDrvC', 'marketing_manager', '2025-06-26 09:01:04', NULL),
(4, 'academic', '$2y$10$sr1MTSPPBcCJWnxBbVocl.13aDyhm2oHQt39PPDC4jnnorqGzDrvC', 'academic_user', '2025-06-26 09:01:14', NULL),
(5, 'finance', '$2y$10$sr1MTSPPBcCJWnxBbVocl.13aDyhm2oHQt39PPDC4jnnorqGzDrvC', 'finance_user', '2025-06-26 09:01:33', NULL),
(6, 'marketing2', '$2y$10$sr1MTSPPBcCJWnxBbVocl.13aDyhm2oHQt39PPDC4jnnorqGzDrvC', 'marketing_user', '2025-06-26 09:02:08', NULL),
(10, 'ranjana', '$2y$10$ilcYCJRuWGJojVj5./wq6.7IfxEx6NKIeVGBURw7ZEPHKOKb/9iqe', 'student', '2025-07-23 05:30:38', 1221),
(11, 'meshan', '$2y$10$Pq90Y4dOtr.O3yIHEGshDeTBkBX9RZtswuusWXjupAJf6I94iI566', 'student', '2025-07-23 05:30:54', 1224);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
