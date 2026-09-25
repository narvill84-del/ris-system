-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 24, 2026 at 08:20 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ris_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ris_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `ris_id`, `action`, `details`, `created_at`) VALUES
(18, 1, 11, 'CREATE', 'Form created with RIS Number: RIS-2026-07-00001', '2026-07-27 01:58:28'),
(19, 1, 12, 'CREATE', 'Form created with RIS Number: RIS-2026-07-00002', '2026-07-27 01:58:28'),
(21, 1, 14, 'CREATE', 'Form created with RIS Number: RIS-2026-07-00004', '2026-07-27 01:58:29'),
(26, 1, 10, 'UPDATE', 'Form updated', '2026-07-27 03:20:23'),
(27, 1, 9, 'UPDATE', 'Form updated', '2026-07-27 03:40:59'),
(31, 1, 18, 'CREATE', 'Form created with RIS Number: RIS-2026-07-00008', '2026-07-28 00:21:49'),
(35, 1, 19, 'CREATE', 'Form created with RIS Number: RIS-2026-07-00009', '2026-07-28 04:31:45');

-- --------------------------------------------------------

--
-- Table structure for table `ris_forms`
--

CREATE TABLE `ris_forms` (
  `id` int(11) NOT NULL,
  `ris_number` varchar(50) NOT NULL,
  `sai_number` varchar(50) DEFAULT NULL,
  `office_name` varchar(100) NOT NULL,
  `responsibility_center_code` varchar(50) DEFAULT NULL,
  `ris_date` date NOT NULL,
  `sai_date` date DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `descriptions` longtext DEFAULT NULL,
  `requested_by` varchar(100) DEFAULT NULL,
  `requested_by_signature` varchar(255) DEFAULT NULL,
  `requested_by_designation` varchar(100) DEFAULT NULL,
  `requested_by_date` date DEFAULT NULL,
  `approved_by` varchar(100) DEFAULT NULL,
  `approved_by_signature` varchar(255) DEFAULT NULL,
  `approved_by_designation` varchar(100) DEFAULT NULL,
  `approved_by_date` date DEFAULT NULL,
  `received_by` varchar(100) DEFAULT NULL,
  `received_by_signature` varchar(255) DEFAULT NULL,
  `received_by_designation` varchar(100) DEFAULT NULL,
  `received_by_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `status` enum('DRAFT','SUBMITTED','APPROVED','RECEIVED','ARCHIVED') DEFAULT 'DRAFT',
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ris_forms`
--

INSERT INTO `ris_forms` (`id`, `ris_number`, `sai_number`, `office_name`, `responsibility_center_code`, `ris_date`, `sai_date`, `purpose`, `descriptions`, `requested_by`, `requested_by_signature`, `requested_by_designation`, `requested_by_date`, `approved_by`, `approved_by_signature`, `approved_by_designation`, `approved_by_date`, `received_by`, `received_by_signature`, `received_by_designation`, `received_by_date`, `created_at`, `updated_at`, `created_by`, `status`, `deleted_at`, `deleted_by`) VALUES
(9, 'RIS-2026-06-00003', '', 'Treasury', '8771', '2026-06-10', '2026-06-10', 'Issuance of Accountable Form for the Fee Collection', '', 'EPAFANIA O. SAWADJAAN', NULL, 'RCO', '2026-06-10', 'JUNEE ROSSE S. EMPALMADO', NULL, 'Acting Municipal Treasurer', '2026-06-10', 'EPIFANIA O. SAWADJAAN', NULL, 'RCO', '2026-06-10', '2026-06-10 07:22:38', '2026-06-10 07:22:38', 1, 'DRAFT', NULL, NULL),
(10, 'RIS-2026-06-00004', '', 'Treasury', '8771', '2026-06-10', '2026-06-10', 'Issuance of Accountable Form for the Fee Collection', '', 'EPAFANIA O. SAWADJAAN', NULL, 'RCO', '2026-06-10', 'JUNEE ROSSE S. EMPALMADO', NULL, 'Acting Municipal Treasurer', '2026-06-10', 'EPIFANIA O. SAWADJAAN', NULL, 'RCO', '2026-06-10', '2026-06-10 07:22:49', '2026-07-27 02:26:24', 1, 'DRAFT', NULL, NULL),
(11, 'RIS-2026-07-00001', '', 'WATERWORKS', '8771', '2026-05-19', '0000-00-00', 'Monthly collection for payment.', '', 'NERRY C. LUBAT', NULL, 'Admin. Asst V', '2026-05-19', 'JUNEE ROSSE S. EMPALMADO', NULL, 'Acting Municipal Treasurer', '2026-05-19', 'NERRY C. LUBAT', NULL, 'Admin. Asst. V', '2026-05-19', '2026-07-27 01:58:28', '2026-07-27 01:58:28', 1, 'DRAFT', NULL, NULL),
(12, 'RIS-2026-07-00002', '', 'WATERWORKS', '8771', '2026-05-19', '0000-00-00', 'Monthly collection for payment.', '', 'NERRY C. LUBAT', NULL, 'Admin. Asst V', '2026-05-19', 'JUNEE ROSSE S. EMPALMADO', NULL, 'Acting Municipal Treasurer', '2026-05-19', 'NERRY C. LUBAT', NULL, 'Admin. Asst. V', '2026-05-19', '2026-07-27 01:58:28', '2026-07-27 01:58:28', 1, 'DRAFT', NULL, NULL),
(14, 'RIS-2026-07-00004', '', 'WATERWORKS', '8771', '2026-05-19', '0000-00-00', 'Monthly collection for payment.', '', 'NERRY C. LUBAT', NULL, 'Admin. Asst V', '2026-05-19', 'JUNEE ROSSE S. EMPALMADO', NULL, 'Acting Municipal Treasurer', '2026-05-19', 'NERRY C. LUBAT', NULL, 'Admin. Asst. V', '2026-05-19', '2026-07-27 01:58:29', '2026-07-27 01:58:29', 1, 'DRAFT', NULL, NULL),
(18, 'RIS-2026-07-00008', '', 'Treasury', '8771', '2026-07-28', '2026-07-28', 'Issuance of accountable form for the treasury payment collection with serial no. 7507801 - 7507850.', '', 'MIMI P. OBENA', NULL, 'Admin. Aide I', '2026-07-28', 'JUNEE ROSSE S. EMPALMADO', NULL, 'Acting Municipal Treasurer', '2026-07-28', 'MIMI P. OBENA', NULL, 'Admin. Aide I', '2026-07-28', '2026-07-28 00:21:49', '2026-07-28 00:21:49', 1, 'DRAFT', NULL, NULL),
(19, 'RIS-2026-07-00009', '', 'Market', '8811', '2026-07-28', '2026-07-28', 'Issuance of accountable form for the market tall fee', '', 'SAN A. HUSSAIN', NULL, 'ADAS', '2026-07-28', 'JUNEE ROSSE S. EMPALMADO', NULL, 'Acting Municipal Treasurer', '2026-07-28', 'SAN A. HUSSAIN', NULL, 'ADAS', '2026-07-28', '2026-07-28 04:31:45', '2026-07-28 04:31:45', 1, 'DRAFT', NULL, NULL),
(20, 'RIS-2026-09-00001', 'SAI2026-24-09', 'Treasury', '8811', '2026-09-24', '2026-09-24', 'Accountable Form No. 51 with serial no.', NULL, 'EPAFANIA O. SAWADJAAN', NULL, 'RCO', '2026-09-24', 'JUNEE ROSSE S. EMPALMADO', NULL, 'Acting Municipal Treasurer', '2026-09-24', 'EPIFANIA O. SAWADJAAN', NULL, 'RCO', '2026-09-24', '2026-09-24 06:12:46', '2026-09-24 06:12:46', 1, 'DRAFT', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ris_line_items`
--

CREATE TABLE `ris_line_items` (
  `id` int(11) NOT NULL,
  `ris_id` int(11) NOT NULL,
  `stock_number` varchar(50) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `descriptions` varchar(255) NOT NULL,
  `quantity_requested` int(11) NOT NULL,
  `quantity_received` int(11) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ris_line_items`
--

INSERT INTO `ris_line_items` (`id`, `ris_id`, `stock_number`, `unit`, `descriptions`, `quantity_requested`, `quantity_received`, `remarks`, `created_at`) VALUES
(11, 11, '50', 'Pad', 'AF #51', 50, 50, '', '2026-07-27 01:58:28'),
(12, 12, '50', 'Pad', 'AF #51', 50, 50, '', '2026-07-27 01:58:28'),
(14, 14, '50', 'Pad', 'AF #51', 50, 50, '', '2026-07-27 01:58:29'),
(18, 10, '44', 'Pad', 'AF #51', 1, 1, '', '2026-07-27 03:20:23'),
(19, 9, '43', 'Pad', 'AF #51', 1, 1, '', '2026-07-27 03:40:59'),
(20, 18, '30', 'pad', 'AF #51', 1, 1, '', '2026-07-28 00:21:49'),
(21, 19, '1263', 'pad', 'Cash Ticket', 1, 1, '', '2026-07-28 04:31:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `office_name` varchar(100) DEFAULT NULL,
  `role` enum('ADMIN','USER','APPROVER') DEFAULT 'USER',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `designation`, `office_name`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@lgu.gov', 'admin123', 'Administrator', 'Admin', 'LGU Main Office', 'ADMIN', 1, '2026-06-04 08:16:48', '2026-06-04 08:16:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ris_id` (`ris_id`);

--
-- Indexes for table `ris_forms`
--
ALTER TABLE `ris_forms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ris_number` (`ris_number`),
  ADD KEY `idx_ris_number` (`ris_number`),
  ADD KEY `idx_ris_date` (`ris_date`),
  ADD KEY `idx_office_name` (`office_name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `ris_line_items`
--
ALTER TABLE `ris_line_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ris_id` (`ris_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `ris_forms`
--
ALTER TABLE `ris_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `ris_line_items`
--
ALTER TABLE `ris_line_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `audit_log_ibfk_2` FOREIGN KEY (`ris_id`) REFERENCES `ris_forms` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ris_line_items`
--
ALTER TABLE `ris_line_items`
  ADD CONSTRAINT `ris_line_items_ibfk_1` FOREIGN KEY (`ris_id`) REFERENCES `ris_forms` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
