-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 01:16 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `barangay_sto_angel_payroll`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `name`, `email`, `created_at`, `updated_at`) VALUES
('admin_692c3079dfa522.21464338', 'admin', '$2y$10$JtbesmnHmTgqdQxPtwaEyObxDHEUokjeQFh9OC2Amp6XGMX.b7Eyu', 'Administrator', 'admin@barangaystoangel.com', '2025-11-30 11:54:34', '2025-11-30 11:55:17'),
('admin_692c4fc64d49d5.44906349', 'halaman', '$2y$10$kxQ9ads8j8aI5qc33hF9G.tnQoEirXeW3ANmstmMHUdatXIvgFFlm', 'jayvee maddalora', 'maddalorajayvee@gmail.com', '2025-11-30 14:08:06', '2025-11-30 14:08:06');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` varchar(255) NOT NULL,
  `employee_id` varchar(100) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `position` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `employment_type` varchar(50) NOT NULL,
  `base_salary` decimal(10,2) NOT NULL,
  `date_hired` date NOT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `first_name`, `last_name`, `middle_name`, `email`, `phone`, `address`, `position`, `department`, `employment_type`, `base_salary`, `date_hired`, `status`, `created_at`, `updated_at`) VALUES
('emp_692c51bfbde254.32375681', '231231', 'Jayvee', 'Maddalora', 'Lacsamana', 'maddalorajayvee@gmail.com', 'maddalorajayvee@gmail.com', 'Bayabas street', 'Tanod', 'Barangay Hall', 'Part-time', 100.00, '2025-11-30', 'Active', '2025-11-30 14:16:31', '2025-11-30 14:16:31'),
('emp_692d11497f9097.89657009', '43247658940', 'Ellaijah', 'Cicno', 'Jimbo', 'jimbo@gmail.com', '094327423440', '4734 kamatis st', 'Secretary', 'office', 'Full-time', 540.00, '2025-02-07', 'Active', '2025-12-01 03:53:45', '2025-12-01 03:53:45'),
('emp_692d1190b41eb6.63322977', '656789765432', 'Mars Rowen', 'Coronado', 'Pogi', 'marscoronado21@gmail.com', '0932458996775', '555 Corned tuna', 'Punong Barangay', 'Barangay Hall', 'Full-time', 3000.00, '2025-01-08', 'Active', '2025-12-01 03:54:56', '2025-12-01 04:01:35'),
('emp_692d123c76b887.35127304', '1', 'Jesler', 'Banayo', 'Fule', 'jesler@gmail.com', '09101232987', 'Bayabas street', 'Kagawad', 'Barangay Hall', 'Full-time', 500.00, '2025-11-30', 'Active', '2025-12-01 03:57:48', '2025-12-01 03:57:48'),
('emp_692d127a32e7d1.76249675', '2', 'Eco', 'Faustino', 'Caricot', 'eco@gmail.com', '09278654768', 'Bayabas street', 'Kagawad', 'Barangay Hall', 'Full-time', 540.00, '2025-11-30', 'Active', '2025-12-01 03:58:50', '2025-12-01 03:58:50'),
('emp_692d7fa253d305.40324097', '345834953485', 'Kevin', 'Durant', 'S', 'kevin2@gmail.com', '98342904239', '7432 halaman', 'kagawad', 'peace and order', 'Full-time', 549.00, '2025-12-01', 'Active', '2025-12-01 11:44:34', '2025-12-01 11:44:34'),
('emp_692d8677225cd3.37906419', '12345678', 'Jimbo', 'Cruz', 'C', 'Jimbo2@gmail.com', '09092314567', '586 Halaman st', 'Tanod', 'peace and order', 'Full-time', 400.00, '2024-12-01', 'Active', '2025-12-01 12:13:43', '2025-12-01 12:17:52');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` varchar(255) NOT NULL,
  `admin_id` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `admin_id`, `token`, `expires_at`, `used`, `created_at`) VALUES
('reset_692c4fd2bb9575.59674967', 'admin_692c4fc64d49d5.44906349', '04319cec542023696ff9bf55a01f43642c20017ea04cbf485edf2db6903131b3', '2025-11-30 23:08:18', 1, '2025-11-30 14:08:18'),
('reset_692c5094f20305.03186818', 'admin_692c4fc64d49d5.44906349', '17c4144eb810b4f724ed1ef10b48122eda64bc747d7d718fec68c070ffddbc5f', '2025-11-30 23:11:32', 1, '2025-11-30 14:11:32'),
('reset_692c527e5a9587.20002487', 'admin_692c4fc64d49d5.44906349', '6081fd5ae1c74b5c265fb82d383421086f136f879e36bbf2b4ccb48502c78787', '2025-11-30 23:19:42', 1, '2025-11-30 14:19:42'),
('reset_692c528cc3bd10.94058178', 'admin_692c4fc64d49d5.44906349', 'f27ec2b6be24e06a3e41d4d61ffdb2fce7a678d4bd18665b34a42b191881ddad', '2025-11-30 23:19:56', 1, '2025-11-30 14:19:56'),
('reset_692d8ecdaa2480.86346143', 'admin_692c4fc64d49d5.44906349', '73d4549b646da535774d6a3a37235936807f2eaf1dd85b1bfc16f94c708c068f', '2025-12-01 21:49:17', 1, '2025-12-01 12:49:17'),
('reset_692d8ed5328d18.07488254', 'admin_692c3079dfa522.21464338', '57940d48a80ee2ab5bd1dd1808de5c5f1f0830d31c68040cb066df200e449d94', '2025-12-01 21:49:25', 1, '2025-12-01 12:49:25'),
('reset_692d8eeeb6eac2.31580372', 'admin_692c3079dfa522.21464338', '3aa50355a9653b742d52f7f592808aefad07a4766fe75e2dec1adfd34a3a7d1d', '2025-12-01 21:49:50', 0, '2025-12-01 12:49:50'),
('reset_692d8ef4c2c4f0.16518162', 'admin_692c4fc64d49d5.44906349', '0d79250d8e651680a74b73429d471b1fded9ebfb5b8be88d0266a80a1c94e4ac', '2025-12-01 21:49:56', 1, '2025-12-01 12:49:56'),
('reset_692d8f1867cc24.74181481', 'admin_692c4fc64d49d5.44906349', '4a33e6725401820b247345d4d76f8a74aa916c50c3e2cf29dd287e9ebc7554f4', '2025-12-01 21:50:32', 0, '2025-12-01 12:50:32');

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `id` varchar(255) NOT NULL,
  `employee_id` varchar(255) NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `base_salary` decimal(10,2) NOT NULL,
  `overtime_hours` decimal(10,2) DEFAULT 0.00,
  `overtime_pay` decimal(10,2) DEFAULT 0.00,
  `allowances` decimal(10,2) DEFAULT 0.00,
  `bonuses` decimal(10,2) DEFAULT 0.00,
  `deductions` decimal(10,2) DEFAULT 0.00,
  `gross_pay` decimal(10,2) NOT NULL,
  `net_pay` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payrolls`
--

INSERT INTO `payrolls` (`id`, `employee_id`, `pay_period_start`, `pay_period_end`, `base_salary`, `overtime_hours`, `overtime_pay`, `allowances`, `bonuses`, `deductions`, `gross_pay`, `net_pay`, `status`, `notes`, `created_at`, `updated_at`) VALUES
('pay_692c5210583887.67348801', 'emp_692c51bfbde254.32375681', '2025-11-30', '2025-11-30', 100.00, 5.00, 7.10, 50.00, 10.00, 157.00, 167.10, 10.10, 'Paid', '', '2025-11-30 14:17:52', '2025-11-30 14:17:52'),
('pay_692d12b1dd4e57.62456645', 'emp_692d127a32e7d1.76249675', '2025-11-30', '2025-11-30', 540.00, 19.00, 582.95, 500.00, 0.00, 400.00, 1622.95, 1222.95, 'Paid', '', '2025-12-01 03:59:45', '2025-12-01 03:59:45'),
('pay_692d12e6cdfde2.62914925', 'emp_692d11497f9097.89657009', '2025-11-30', '2025-11-30', 540.00, 19.00, 582.95, 500.00, 0.00, 400.00, 1622.95, 1222.95, 'Approved', '', '2025-12-01 04:00:38', '2025-12-01 04:00:38'),
('pay_692d1345559454.77020470', 'emp_692d1190b41eb6.63322977', '2025-11-30', '2025-11-30', 3000.00, 20.00, 6818.18, 1000.00, 1000.00, 500.00, 11818.18, 11318.18, 'Approved', '', '2025-12-01 04:02:13', '2025-12-01 04:02:13'),
('pay_692d136b2b2ac9.20594491', 'emp_692d123c76b887.35127304', '2025-11-30', '2025-11-30', 500.00, 10.00, 284.09, 500.00, 0.00, 0.00, 1284.09, 1284.09, 'Paid', '', '2025-12-01 04:02:51', '2025-12-01 04:02:51'),
('pay_692d871984fd50.49653267', 'emp_692d8677225cd3.37906419', '2024-12-01', '2025-12-01', 400.00, 0.00, 0.00, 0.00, 0.00, 0.00, 400.00, 400.00, 'Approved', '', '2025-12-01 12:16:25', '2025-12-01 12:16:25'),
('pay_692d8aafea73e7.05025252', 'emp_692c51bfbde254.32375681', '2025-11-30', '2025-12-01', 500.00, 5.00, 738.64, 0.00, 500.00, 200.00, 1738.64, 1538.64, 'Pending', '', '2025-12-01 12:31:43', '2025-12-01 12:56:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_employee_id` (`employee_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_admin_id` (`admin_id`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payroll_employee` (`employee_id`),
  ADD KEY `idx_payroll_status` (`status`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD CONSTRAINT `payrolls_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
