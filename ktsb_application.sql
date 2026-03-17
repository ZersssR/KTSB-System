-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 17, 2026 at 03:05 AM
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
-- Database: `ktsb_application`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `user_id`, `activity_type`, `description`, `reference_id`, `ip_address`, `created_at`) VALUES
(1, 2, 'create', 'Created new customer: Test Company Ltd', 1, '::1', '2026-01-07 03:44:48'),
(2, 2, 'create', 'Created new customer: Verification Corp', 2, '::1', '2026-01-07 04:11:59'),
(3, 2, 'create', 'Created new customer: Sync Test Co', 3, '::1', '2026-01-07 04:43:49'),
(4, 2, 'delete', 'Deleted customer: Sync Test Co', 3, '::1', '2026-01-07 04:50:54'),
(5, 2, 'delete', 'Deleted customer: Test Company Ltd', 1, '::1', '2026-01-07 04:54:15'),
(6, 2, 'update', 'Updated vessel: Alkahfi Gentleee', 1, '::1', '2026-01-08 03:20:26'),
(7, 2, 'update', 'Updated vessel: Alkahfi Care', 2, '::1', '2026-01-08 03:47:49'),
(8, 2, 'update', 'Updated vessel: Alkahfi Care', 2, '::1', '2026-01-08 03:47:57'),
(9, 2, 'update', 'Updated vessel: Alkahfi Care', 2, '::1', '2026-01-08 03:52:24'),
(10, 2, 'update', 'Updated vessel: Alkahfi Care', 2, '::1', '2026-01-08 03:52:37'),
(11, 2, 'update', 'Updated vessel: Alkahfi Gentle', 1, '::1', '2026-01-08 06:26:35'),
(12, 2, 'create', 'Created new vessel: Testing 1', 18, '::1', '2026-01-08 08:33:18'),
(13, 2, 'delete', 'Deleted vessel: Testing 1', 18, '::1', '2026-01-08 09:14:55'),
(14, 3, 'endorsement', 'Marine request endorsed', 0, NULL, '2026-01-22 08:10:43'),
(15, NULL, 'endorsement', 'Marine request endorsed', 0, NULL, '2026-01-22 08:44:40'),
(16, NULL, 'amendment_request', 'Amendment requested for marine request', 0, NULL, '2026-01-22 08:45:06'),
(17, 3, 'update', 'Updated customer: Verification Corp', 2, '::1', '2026-01-27 04:26:40'),
(18, 3, 'delete', 'Deleted agent: afiq', 11, '::1', '2026-01-27 07:51:31'),
(19, 3, 'create', 'Created new customer: TEST CORP', 4, '::1', '2026-01-27 08:12:57'),
(20, 3, 'create', 'Created new customer: TEST CORP', 5, '::1', '2026-01-27 08:21:59'),
(21, 3, 'delete', 'Deleted customer: TEST CORP', 5, '::1', '2026-01-27 08:22:16'),
(22, 3, 'endorsement', 'Marine request endorsed', 0, NULL, '2026-02-12 02:22:15'),
(23, 3, 'endorsement', 'Marine request endorsed', 0, NULL, '2026-02-16 06:38:01'),
(24, NULL, 'endorsement', 'Marine request endorsed', 0, NULL, '2026-02-16 07:35:52'),
(25, 3, 'endorsement', 'Marine request endorsed', 0, NULL, '2026-02-23 04:14:34'),
(26, 3, 'endorsement', 'Marine request endorsed', 0, NULL, '2026-02-23 04:15:32'),
(27, 3, 'amendment_request', 'Amendment requested for marine request', 0, NULL, '2026-02-23 04:16:05'),
(28, 3, 'amendment_request', 'Amendment requested for marine request', 0, NULL, '2026-02-23 04:50:40'),
(29, 3, 'endorsement', 'Marine request endorsed', 0, NULL, '2026-02-23 04:54:29'),
(30, 3, 'create', 'Created new customer: PETRONAS with ID: CUS014', 6, '::1', '2026-03-14 07:59:14'),
(31, 3, 'create', 'Created new customer: PETRONAS with ID: CUS015', 7, '::1', '2026-03-14 08:02:56'),
(32, 3, 'create', 'Created new user: ORANGTEST with ID: USR008', 8, '::1', '2026-03-14 08:29:25'),
(33, 3, 'update', 'Updated user: ORANGTEST', 8, '::1', '2026-03-14 08:42:53'),
(34, 3, 'update', 'Updated user: vcorp_user', 7, '::1', '2026-03-14 08:43:22'),
(35, 3, 'create', 'Created new vessel: TEST with ID: VSL018', 19, '::1', '2026-03-14 09:03:41'),
(36, 3, 'update', 'Updated vessel: Alkahfi Gentle', 1, '::1', '2026-03-14 09:10:32'),
(37, 3, 'update', 'Updated vessel: TEST', 19, '::1', '2026-03-14 09:10:40'),
(38, 3, 'endorsement', 'Marine request endorsed', 0, NULL, '2026-03-15 00:38:27');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `email`, `is_active`, `created_at`) VALUES
(2, 'admin', '$2y$10$SnayhwT1CP4yxQRJAInro..kUahC0dIqsQXimNOAx5q7h42AuF1da', 'admin_account@ktsb.com', 1, '2025-12-02 05:55:21'),
(3, 'chu12', '$2y$10$FIHCi7dujzcbblOOLDXMTeCxq3pJKSF8MSvl11L4HAA0NyGU4d.xa', 'syuhada@email.com', 1, '2026-01-27 01:39:25');

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `agent_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `customer_code` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`agent_id`, `username`, `password_hash`, `full_name`, `email`, `phone_number`, `company_name`, `customer_code`, `created_by`, `status`, `created_at`, `last_login`) VALUES
(2, 'farish', '$2y$10$Ah1LYyPT09Yo9pLNcYJVDuy7EPC90C.Ij6T.f1al1XQrcmY0XNysK', 'Farish Bin Rashdan', 'farish@gmail.com', '0123456789', 'EPIC', 'KTSB', 3, 'active', '2025-12-02 07:31:53', '2025-12-11 08:00:10'),
(4, 'test', '$2y$10$qG4PneCitMj8TWIDN56F2OQ8bJp8NzrkBFzEpFgPmmYndJqOt.I5q', 'test', 'test@gmail.com', '123', 'KTSB', 'KTSB', 3, 'active', '2025-12-02 08:21:05', NULL),
(5, 'fatin_comel', '$2y$10$fBjNkW8SIAQUjdbiD9C6tuJCqujTceGffybcQPbXUx.xyQNlWmbau', 'Nur Fatin', 'fatin@gmail.com', '+6047406233', 'EPIC', 'KTSB', 3, 'active', '2025-12-02 09:27:01', NULL),
(6, 'jai', '$2y$10$dmdxgWuo3XH8X0h8GhJ6p.OfyOR2VPxUgCSQHx.yN8A3fhNiv0vSm', 'Ahmad Jailani bin Hussin', 'jailani@gmail.com', '123456789', 'PETRONAS', 'KTSB', 3, 'active', '2025-12-04 04:46:00', NULL),
(7, 'kamal', '$2y$10$Z1dQ5zo9889JvHfwwQqOfOHoTx9k45uhsqNuU19Mq.6uBatKeyBoK', 'Kamal Bin Jamil', 'kamal@gmail.com', '123456789', 'PETRONAS', 'KTSB', 3, 'active', '2025-12-04 05:03:37', NULL),
(8, 'syed', '$2y$10$ghCJVTiJ93pwrpWIOzuIjuDnm5OmE4hmrKYMoTk3H3kWTBMO8eJo6', 'syed ahmad bin syahril', 'syed@gmail.com', '123456789', 'PETRONAS', 'KTSB', 3, 'active', '2025-12-04 05:04:36', NULL),
(9, 'jamal', '$2y$10$jeE4fihoeAKy.FUFSpjGseHmnw6hK07BPuD/3kvOqIxm0SXrpOI/m', 'Jamal Tetsu', 'jamal@gmail.com', '123456789', 'PETRONAS', 'KTSB', 3, 'active', '2025-12-04 05:07:10', NULL),
(10, 'syamil', '$2y$10$E2DV1Pwyr9KhvnZJKJDdMuJE5GPeKY.cegCvjccsXikxyOZeP34uG', 'Syamil Riduan Bin Syed', 'syamil@gmail.com', '123456789', 'PETRONAS', 'KTSB', 3, 'active', '2025-12-04 05:18:44', NULL),
(12, 'iffah123', '$2y$10$18C.lnxeLWVI5FgzQtP6XeOIx9A1JITDbT5HGceiZnga/RsW87Bo6', 'Nur Iffah', 'iffah123@gmail.com', '+609545091', 'PETRONAS', 'KTSB', 6, 'active', '2025-12-04 07:56:33', NULL),
(13, 'ABU123', '$2y$10$cFi771SkAWjPxLvvsD3aOuC1BW3BlfdryMwoXDm/FKQGChu64fxWm', 'Muhammad Abu', 'ABU@GMAIL.COM', '0174658392', 'EPIC', 'KTSB', 3, 'active', '2026-02-12 02:15:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `created_at`, `status`) VALUES
(1, 'Scheduled Maintenance', 'Servers will be down for upgrades this Sunday from 2AM to 4AM UTC. Please save your work beforehand.', '2025-12-04 16:39:26', 'active'),
(2, 'New Dashboard Features', 'Check out the new dark mode and customizable widgets in your settings! You can now drag and drop items.', '2025-12-03 16:39:26', 'active'),
(3, 'Q3 Town Hall Meeting', 'Join us for the quarterly all-hands meeting. We will be discussing Q4 goals and new benefit packages.', '2025-12-02 16:39:26', 'active'),
(4, 'Safety Protocol Update', 'All personnel must review the updated safety guidelines regarding dock operations by Friday.', '2025-12-01 16:39:26', 'active'),
(5, 'Annual Dinner', 'Registration for the annual dinner is now open. Please RSVP by the end of the month.', '2025-11-30 16:39:26', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `berths`
--

CREATE TABLE `berths` (
  `id` int(11) NOT NULL,
  `berth_code` varchar(10) NOT NULL,
  `berth_name` varchar(100) NOT NULL,
  `length` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `berths`
--

INSERT INTO `berths` (`id`, `berth_code`, `berth_name`, `length`, `status`, `created_at`, `updated_at`) VALUES
(1, 'B1', 'Berth 1', 150.00, 'active', '2026-01-27 03:37:12', '2026-01-27 03:37:12'),
(2, 'B2', 'Berth 2', 120.00, 'active', '2026-01-27 03:37:12', '2026-01-27 03:37:12'),
(3, 'B3', 'Berth 3', 180.00, 'active', '2026-01-27 03:37:12', '2026-01-27 03:37:12'),
(4, 'B4', 'Berth 4', 200.00, 'active', '2026-01-27 03:37:12', '2026-01-27 03:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `event_type` varchar(50) DEFAULT 'holiday',
  `is_ramadhan` tinyint(1) DEFAULT 0,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calendar_events`
--

INSERT INTO `calendar_events` (`id`, `title`, `start_date`, `end_date`, `event_type`, `is_ramadhan`, `user_id`, `created_at`) VALUES
(1, 'Ramadhan', '2026-01-06 16:24:00', '2026-01-06 16:24:00', 'holiday', 1, 2, '2026-01-06 16:24:48'),
(3, 'Faisal Korban', '2026-01-07 10:56:00', '2026-01-07 10:56:00', 'holiday', 0, 2, '2026-01-07 10:56:49');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `company_code` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `company_name`, `company_code`, `created_at`) VALUES
(1, 'Kuala Terengganu Support Base', 'KTSB', '2025-12-03 04:05:13'),
(2, 'EPIC', 'EPIC', '2025-12-03 04:05:13'),
(3, 'PETRONAS', 'PETRONAS', '2025-12-03 04:05:13'),
(4, 'HALLIBURTON', 'HALLIBURTON', '2025-12-03 04:05:13');

-- --------------------------------------------------------

--
-- Table structure for table `crew_sign_off_details`
--

CREATE TABLE `crew_sign_off_details` (
  `id` int(11) NOT NULL,
  `crew_signoff_id` varchar(20) NOT NULL,
  `crew_name` varchar(100) NOT NULL,
  `ic_passport` varchar(50) NOT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crew_sign_off_details`
--

INSERT INTO `crew_sign_off_details` (`id`, `crew_signoff_id`, `crew_name`, `ic_passport`, `mobile_number`, `nationality`, `passport_expiry`, `company`, `destination`) VALUES
(1, 'CRF25000001', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(2, 'CRF25000001', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(3, 'CRF25000001', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(4, 'CRF25000001', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(5, 'CRF25000001', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(6, 'CRF25000001', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(7, 'CRF25000001', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(8, 'CRF25000001', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(9, 'CRF25000001', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(10, 'CRF25000001', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(11, 'CRF25000001', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(12, 'CRF25000001', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(13, 'CRF25000002', 'muhammad salman bin salmin', '9000008901', '+609545098', 'Malay', '2025-12-25', 'Kuala Terengganu Support Base', 'Besut'),
(14, 'CRF25000003', 'muhammad salman bin salmin', '9000008901', '+609545098', 'Malay', '2025-12-25', 'Kuala Terengganu Support Base', 'Besut'),
(15, 'CRF25000004', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(16, 'CRF25000004', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(17, 'CRF25000004', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(18, 'CRF25000004', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(19, 'CRF25000004', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(20, 'CRF25000004', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(21, 'CRF25000004', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(22, 'CRF25000004', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(23, 'CRF25000004', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(24, 'CRF25000004', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(25, 'CRF25000004', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(26, 'CRF25000004', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(27, 'CRF25000005', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(28, 'CRF25000005', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(29, 'CRF25000005', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(30, 'CRF25000005', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(31, 'CRF25000005', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(32, 'CRF25000005', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(33, 'CRF25000005', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(34, 'CRF25000005', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(35, 'CRF25000005', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(36, 'CRF25000005', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(37, 'CRF25000005', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(38, 'CRF25000005', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(39, 'CRF25000006', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(40, 'CRF25000006', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(41, 'CRF25000006', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(42, 'CRF25000006', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(43, 'CRF25000006', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(44, 'CRF25000006', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(45, 'CRF25000006', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(46, 'CRF25000006', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(47, 'CRF25000006', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(48, 'CRF25000006', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(49, 'CRF25000006', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(50, 'CRF25000006', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(51, 'CRF25000007', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(52, 'CRF25000007', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(53, 'CRF25000007', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(54, 'CRF25000007', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(55, 'CRF25000007', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(56, 'CRF25000007', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(57, 'CRF25000007', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(58, 'CRF25000007', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(59, 'CRF25000007', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(60, 'CRF25000007', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(61, 'CRF25000007', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(62, 'CRF25000007', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(63, 'CRF25000008', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(64, 'CRF25000008', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(65, 'CRF25000008', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(66, 'CRF25000008', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(67, 'CRF25000008', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(68, 'CRF25000008', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(69, 'CRF25000008', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(70, 'CRF25000008', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(71, 'CRF25000008', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(72, 'CRF25000008', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(73, 'CRF25000008', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(74, 'CRF25000008', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(75, 'CRF25000009', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(76, 'CRF25000009', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(77, 'CRF25000009', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(78, 'CRF25000009', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(79, 'CRF25000009', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(80, 'CRF25000009', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(81, 'CRF25000009', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(82, 'CRF25000009', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(83, 'CRF25000009', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(84, 'CRF25000009', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(85, 'CRF25000009', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(86, 'CRF25000009', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(87, 'CRF25000010', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(88, 'CRF25000010', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(89, 'CRF25000010', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(90, 'CRF25000010', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(91, 'CRF25000010', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(92, 'CRF25000010', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(93, 'CRF25000010', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(94, 'CRF25000010', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(95, 'CRF25000010', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(96, 'CRF25000010', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(97, 'CRF25000010', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(98, 'CRF25000010', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(99, 'CRF25000011', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(100, 'CRF25000011', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(101, 'CRF25000011', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(102, 'CRF25000011', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(103, 'CRF25000011', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(104, 'CRF25000011', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(105, 'CRF25000011', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(106, 'CRF25000011', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(107, 'CRF25000011', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(108, 'CRF25000011', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(109, 'CRF25000011', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(110, 'CRF25000011', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(111, 'CRF25000012', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(112, 'CRF25000012', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(113, 'CRF25000012', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(114, 'CRF25000012', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(115, 'CRF25000012', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(116, 'CRF25000012', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(117, 'CRF25000012', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(118, 'CRF25000012', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(119, 'CRF25000012', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(120, 'CRF25000012', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(121, 'CRF25000012', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(122, 'CRF25000012', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(123, 'CRF25000013', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(124, 'CRF25000013', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(125, 'CRF25000013', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(126, 'CRF25000013', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(127, 'CRF25000013', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(128, 'CRF25000013', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(129, 'CRF25000013', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(130, 'CRF25000013', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(131, 'CRF25000013', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(132, 'CRF25000013', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(133, 'CRF25000013', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(134, 'CRF25000013', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(135, 'CRF25000014', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(136, 'CRF25000014', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(137, 'CRF25000014', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(138, 'CRF25000014', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(139, 'CRF25000014', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(140, 'CRF25000014', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(141, 'CRF25000014', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(142, 'CRF25000014', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(143, 'CRF25000014', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(144, 'CRF25000014', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(145, 'CRF25000014', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(146, 'CRF25000014', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(147, 'CRF25000015', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(148, 'CRF25000015', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(149, 'CRF25000015', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(150, 'CRF25000015', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(151, 'CRF25000015', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(152, 'CRF25000015', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(153, 'CRF25000015', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(154, 'CRF25000015', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(155, 'CRF25000015', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(156, 'CRF25000015', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(157, 'CRF25000015', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(158, 'CRF25000015', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(159, 'CRF25000016', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(160, 'CRF25000016', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(161, 'CRF25000016', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(162, 'CRF25000016', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(163, 'CRF25000016', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(164, 'CRF25000016', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(165, 'CRF25000016', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(166, 'CRF25000016', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(167, 'CRF25000016', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(168, 'CRF25000016', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(169, 'CRF25000016', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(170, 'CRF25000016', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(171, 'CRF25000017', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(172, 'CRF25000017', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(173, 'CRF25000017', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(174, 'CRF25000017', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(175, 'CRF25000017', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(176, 'CRF25000017', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(177, 'CRF25000017', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(178, 'CRF25000017', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(179, 'CRF25000017', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(180, 'CRF25000017', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(181, 'CRF25000017', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(182, 'CRF25000017', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(183, 'CRF25000018', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(184, 'CRF25000018', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(185, 'CRF25000018', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(186, 'CRF25000018', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(187, 'CRF25000018', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(188, 'CRF25000018', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(189, 'CRF25000018', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(190, 'CRF25000018', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(191, 'CRF25000018', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(192, 'CRF25000018', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(193, 'CRF25000018', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(194, 'CRF25000018', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(195, 'CRF25000019', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(196, 'CRF25000019', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(197, 'CRF25000019', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(198, 'CRF25000019', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(199, 'CRF25000019', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(200, 'CRF25000019', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(201, 'CRF25000019', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(202, 'CRF25000019', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(203, 'CRF25000019', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(204, 'CRF25000019', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(205, 'CRF25000019', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(206, 'CRF25000019', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya');

-- --------------------------------------------------------

--
-- Table structure for table `crew_sign_off_requests`
--

CREATE TABLE `crew_sign_off_requests` (
  `crew_signoff_id` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_agent_id` int(11) DEFAULT NULL,
  `vessel_name` varchar(100) NOT NULL,
  `po_number` varchar(50) DEFAULT NULL,
  `request_date` date NOT NULL,
  `request_time` time NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `takeaway_quantity` int(11) DEFAULT 0,
  `baggage_handling_quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crew_sign_off_requests`
--

INSERT INTO `crew_sign_off_requests` (`crew_signoff_id`, `user_id`, `assigned_agent_id`, `vessel_name`, `po_number`, `request_date`, `request_time`, `remarks`, `status`, `created_at`, `updated_at`, `takeaway_quantity`, `baggage_handling_quantity`) VALUES
('CRF25000001', 3, 2, 'Blue Petra 2', 'BG63636', '2025-12-03', '01:00:00', 'test for sign off', 'pending', '2025-12-02 16:53:48', '2025-12-02 16:53:48', 12, 12),
('CRF25000002', 6, 8, 'Tegas Madani', 'PO6677', '2025-12-31', '12:00:00', '', 'pending', '2025-12-04 07:41:55', '2025-12-04 07:41:55', NULL, NULL),
('CRF25000003', 6, 8, 'Tegas Madani', 'PO6677', '2025-12-31', '12:00:00', '', 'pending', '2025-12-04 07:42:08', '2025-12-04 07:42:08', NULL, 1),
('CRF25000004', 3, 10, 'Blue Petra 2', '122', '2025-12-07', '10:00:00', '', 'pending', '2025-12-07 01:38:15', '2025-12-07 01:38:15', 12, NULL),
('CRF25000005', 3, 10, 'Blue Petra 2', '122', '2025-12-07', '10:00:00', '', 'pending', '2025-12-07 01:38:22', '2025-12-07 01:38:22', 12, NULL),
('CRF25000006', 3, 10, 'Alkahfi Asura', '122', '2025-12-07', '11:00:00', 'test for notifications', 'pending', '2025-12-07 01:43:23', '2025-12-07 01:43:23', 12, NULL),
('CRF25000007', 3, 10, 'Alkahfi Asura', '122', '2025-12-07', '11:00:00', 'test for notifications', 'pending', '2025-12-07 01:43:27', '2025-12-07 01:43:27', 12, NULL),
('CRF25000008', 3, 7, 'Alkahfi Asura', '122', '2025-12-07', '10:00:00', '', 'pending', '2025-12-07 01:52:43', '2025-12-07 01:52:43', 12, NULL),
('CRF25000009', 3, 6, 'Marine Success', '1222', '2025-12-07', '10:00:00', 'test notificaiton', 'pending', '2025-12-07 01:57:24', '2025-12-07 01:57:24', 12, NULL),
('CRF25000010', 3, 6, 'Marine Success', '1222', '2025-12-07', '11:00:00', '', 'pending', '2025-12-07 02:08:54', '2025-12-07 02:08:54', 12, NULL),
('CRF25000011', 3, 10, 'Blue Petra 2', '122', '2025-12-07', '11:00:00', '', 'pending', '2025-12-07 02:13:49', '2025-12-07 02:13:49', 12, NULL),
('CRF25000012', 3, 2, 'Marine Success', '122', '2025-12-07', '11:00:00', '', 'pending', '2025-12-07 02:14:23', '2025-12-07 02:14:23', 12, NULL),
('CRF25000013', 3, 10, 'Alkahfi Asura', '', '2025-12-07', '11:00:00', '', 'pending', '2025-12-07 02:15:59', '2025-12-07 02:15:59', NULL, NULL),
('CRF25000014', 3, 10, 'Alkahfi Asura', '122', '2025-12-07', '11:00:00', '', 'pending', '2025-12-07 02:16:13', '2025-12-07 02:16:13', NULL, NULL),
('CRF25000015', 3, 9, 'Blue Petra 2', '122', '2025-12-07', '11:00:00', '', 'pending', '2025-12-07 02:21:01', '2025-12-07 02:21:01', NULL, NULL),
('CRF25000016', 3, 7, 'Alkahfi Asura', '4455', '2025-12-07', '11:00:00', '', 'pending', '2025-12-07 02:22:39', '2025-12-07 02:22:39', NULL, NULL),
('CRF25000017', 3, 6, 'Gen 4 One', '', '2025-12-07', '11:00:00', '', 'pending', '2025-12-07 02:30:37', '2025-12-07 02:30:37', 12, NULL),
('CRF25000018', 3, 6, 'Gen 4 One', '', '2025-12-07', '11:00:00', '', 'pending', '2025-12-07 02:30:48', '2025-12-07 02:30:48', 12, NULL),
('CRF25000019', 3, 8, 'Alkahfi Courage', '1254', '2025-12-07', '11:00:00', '123456', 'pending', '2025-12-07 02:39:41', '2025-12-07 02:39:41', 12, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `crew_sign_on_details`
--

CREATE TABLE `crew_sign_on_details` (
  `id` int(11) NOT NULL,
  `crew_signon_id` varchar(20) NOT NULL,
  `crew_name` varchar(100) NOT NULL,
  `ic_passport` varchar(50) NOT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crew_sign_on_details`
--

INSERT INTO `crew_sign_on_details` (`id`, `crew_signon_id`, `crew_name`, `ic_passport`, `mobile_number`, `nationality`, `passport_expiry`, `company`, `destination`) VALUES
(13, 'CRN25000001', 'Muhammad Alif Bin Satar', '031111111111', '+609545098', 'MALAY', '2025-12-10', 'Kuala Terengganu Support Base', 'UK (ulu klang)'),
(14, 'CRN25000001', 'Muhammad Abu Bin Sudin', '010203110931', '0147406233', 'Malay', '2025-12-26', 'Kuala Terengganu Support Base', 'UK (ulu klang)'),
(15, 'CRN25000002', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(16, 'CRN25000002', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(17, 'CRN25000002', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(18, 'CRN25000002', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(19, 'CRN25000002', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(20, 'CRN25000002', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(21, 'CRN25000002', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(22, 'CRN25000002', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(23, 'CRN25000002', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(24, 'CRN25000002', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(25, 'CRN25000002', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(26, 'CRN25000002', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(27, 'CRN25000003', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(28, 'CRN25000003', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(29, 'CRN25000003', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(30, 'CRN25000003', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(31, 'CRN25000003', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(32, 'CRN25000003', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(33, 'CRN25000003', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(34, 'CRN25000003', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(35, 'CRN25000003', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(36, 'CRN25000003', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(37, 'CRN25000003', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(38, 'CRN25000003', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(39, 'CRN25000004', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(40, 'CRN25000004', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(41, 'CRN25000004', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(42, 'CRN25000004', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(43, 'CRN25000004', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(44, 'CRN25000004', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(45, 'CRN25000004', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(46, 'CRN25000004', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(47, 'CRN25000004', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(48, 'CRN25000004', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(49, 'CRN25000004', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(50, 'CRN25000004', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(51, 'CRN25000005', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(52, 'CRN25000005', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(53, 'CRN25000005', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(54, 'CRN25000005', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(55, 'CRN25000005', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(56, 'CRN25000005', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(57, 'CRN25000005', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(58, 'CRN25000005', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(59, 'CRN25000005', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(60, 'CRN25000005', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(61, 'CRN25000005', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(62, 'CRN25000005', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(63, 'CRN25000006', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(64, 'CRN25000006', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(65, 'CRN25000006', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(66, 'CRN25000006', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(67, 'CRN25000006', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(68, 'CRN25000006', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(69, 'CRN25000006', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(70, 'CRN25000006', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(71, 'CRN25000006', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(72, 'CRN25000006', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(73, 'CRN25000006', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(74, 'CRN25000006', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(75, 'CRN25000007', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(76, 'CRN25000007', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(77, 'CRN25000007', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(78, 'CRN25000007', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(79, 'CRN25000007', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(80, 'CRN25000007', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(81, 'CRN25000007', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(82, 'CRN25000007', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(83, 'CRN25000007', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(84, 'CRN25000007', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(85, 'CRN25000007', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(86, 'CRN25000007', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(87, 'CRN25000008', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(88, 'CRN25000008', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(89, 'CRN25000008', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(90, 'CRN25000008', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(91, 'CRN25000008', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(92, 'CRN25000008', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(93, 'CRN25000008', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(94, 'CRN25000008', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(95, 'CRN25000008', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(96, 'CRN25000008', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(97, 'CRN25000008', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(98, 'CRN25000008', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(99, 'CRN25000009', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(100, 'CRN25000009', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(101, 'CRN25000009', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(102, 'CRN25000009', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(103, 'CRN25000009', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(104, 'CRN25000009', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(105, 'CRN25000009', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(106, 'CRN25000009', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(107, 'CRN25000009', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(108, 'CRN25000009', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(109, 'CRN25000009', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(110, 'CRN25000009', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(111, 'CRN25000010', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(112, 'CRN25000010', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(113, 'CRN25000010', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(114, 'CRN25000010', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(115, 'CRN25000010', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(116, 'CRN25000010', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(117, 'CRN25000010', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(118, 'CRN25000010', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(119, 'CRN25000010', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(120, 'CRN25000010', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(121, 'CRN25000010', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(122, 'CRN25000010', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(123, 'CRN25000011', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(124, 'CRN25000011', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(125, 'CRN25000011', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(126, 'CRN25000011', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(127, 'CRN25000011', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(128, 'CRN25000011', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(129, 'CRN25000011', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(130, 'CRN25000011', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(131, 'CRN25000011', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(132, 'CRN25000011', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(133, 'CRN25000011', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(134, 'CRN25000011', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(135, 'CRN25000012', 'Nurain Syuhada', '031111111111', '0189045510', 'MaLAY', '2025-12-19', 'KSB', 'UK (ulu klang)'),
(136, 'CRN25000013', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(137, 'CRN25000013', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(138, 'CRN25000013', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(139, 'CRN25000013', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(140, 'CRN25000013', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(141, 'CRN25000013', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(142, 'CRN25000013', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(143, 'CRN25000013', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(144, 'CRN25000013', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(145, 'CRN25000013', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(146, 'CRN25000013', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(147, 'CRN25000013', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(148, 'CRN25000014', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(149, 'CRN25000014', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(150, 'CRN25000014', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(151, 'CRN25000014', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(152, 'CRN25000014', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(153, 'CRN25000014', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(154, 'CRN25000014', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(155, 'CRN25000014', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(156, 'CRN25000014', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(157, 'CRN25000014', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(158, 'CRN25000014', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(159, 'CRN25000014', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(160, 'CRN25000015', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(161, 'CRN25000015', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(162, 'CRN25000015', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(163, 'CRN25000015', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(164, 'CRN25000015', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(165, 'CRN25000015', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(166, 'CRN25000015', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(167, 'CRN25000015', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(168, 'CRN25000015', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(169, 'CRN25000015', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(170, 'CRN25000015', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(171, 'CRN25000015', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(172, 'CRN25000016', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(173, 'CRN25000016', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(174, 'CRN25000016', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(175, 'CRN25000016', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(176, 'CRN25000016', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(177, 'CRN25000016', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(178, 'CRN25000016', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(179, 'CRN25000016', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(180, 'CRN25000016', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(181, 'CRN25000016', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(182, 'CRN25000016', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(183, 'CRN25000016', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya'),
(184, 'CRN25000017', 'Ahmad bin Razali', '850101-11-5023', '012-3456789', 'Malaysian', NULL, 'Petra Energy', 'Platform Bekok A'),
(185, 'CRN25000017', 'Tan Wei Ling', '900520-10-6622', '019-8765432', 'Malaysian', NULL, 'Dialog Group', 'Vessel SK Pilot'),
(186, 'CRN25000017', 'Muthu a/l Kumar', '921215-08-7733', '017-2233445', 'Malaysian', NULL, 'Sapura Energy', 'Platform Tapis B'),
(187, 'CRN25000017', 'John Smith', '558822113', '011-55566677', 'British', '2028-12-01', 'Shell', 'Rig 5'),
(188, 'CRN25000017', 'Supardi Nasir', 'B9876543', '011-33445566', 'Indonesian', '2026-05-01', 'Petra Energy', 'Vessel Setia'),
(189, 'CRN25000017', 'Nguyen Van Hung', 'C5544332', '018-9988776', 'Vietnamese', '2027-08-01', 'Barakah Offshore', 'Platform Pulai'),
(190, 'CRN25000017', 'Jose Rizal', 'P44556677', '016-4455667', 'Filipino', '2025-11-01', 'Sapura Energy', 'Vessel SK Deep'),
(191, 'CRN25000017', 'Li Wei', 'E99887766', '014-2233112', 'Chinese', '2029-03-01', 'COSL', 'Rig 2'),
(192, 'CRN25000017', 'Siti Sarah', 'A11223344', '013-5556666', 'Malaysian', '2030-07-01', 'Petronas', 'Platform Dulang'),
(193, 'CRN25000017', 'Robert Doe', 'US98765432', '010-9988112', 'American', '2026-01-01', 'ExxonMobil', 'Platform Guntong'),
(194, 'CRN25000017', 'Rajesh Patel', 'Z1234567', '015-6677889', 'Indian', '2027-09-01', 'Bumi Armada', 'Vessel Armada 1'),
(195, 'CRN25000017', 'Ahmed Al-Fayed', 'E1239876', '019-2233445', 'Emirati', '2026-06-01', 'Mubadala', 'Platform Raya');

-- --------------------------------------------------------

--
-- Table structure for table `crew_sign_on_requests`
--

CREATE TABLE `crew_sign_on_requests` (
  `crew_signon_id` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_agent_id` int(11) DEFAULT NULL,
  `vessel_name` varchar(100) NOT NULL,
  `po_number` varchar(50) DEFAULT NULL,
  `request_date` date NOT NULL,
  `request_time` time NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `packed_meals_quantity` int(11) DEFAULT 0,
  `snack_pack_quantity` int(11) DEFAULT 0,
  `baggage_details` text DEFAULT NULL,
  `bag_tagging_quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crew_sign_on_requests`
--

INSERT INTO `crew_sign_on_requests` (`crew_signon_id`, `user_id`, `assigned_agent_id`, `vessel_name`, `po_number`, `request_date`, `request_time`, `remarks`, `status`, `created_at`, `updated_at`, `packed_meals_quantity`, `snack_pack_quantity`, `baggage_details`, `bag_tagging_quantity`) VALUES
('CRN25000001', 3, 2, 'Ph Prestij', 'PO9090', '2025-12-18', '04:00:00', '', 'pending', '2025-12-02 09:08:06', '2025-12-02 09:08:06', 2, 2, '2', 2),
('CRN25000002', 5, 2, 'Blue Petra 2', '122', '2025-12-03', '16:00:00', '', 'pending', '2025-12-03 07:04:36', '2025-12-03 07:04:36', NULL, 12, NULL, NULL),
('CRN25000003', 5, 2, 'Alkahfi Asura', '1212', '2025-12-03', '16:00:00', '', 'pending', '2025-12-03 07:11:46', '2025-12-03 07:11:46', NULL, 12, NULL, NULL),
('CRN25000004', 5, 5, 'Alkahfi Asura', '1222', '2025-12-04', '12:00:00', '', 'pending', '2025-12-03 07:20:54', '2025-12-03 07:20:54', 12, 12, NULL, NULL),
('CRN25000005', 5, 2, 'Jati Four', '1455', '2025-12-03', '16:00:00', '', 'pending', '2025-12-03 07:54:16', '2025-12-03 07:54:16', 12, NULL, NULL, NULL),
('CRN25000006', 5, 2, 'Marine Success', '121', '2025-12-03', '18:00:00', '', 'pending', '2025-12-03 08:48:23', '2025-12-03 08:48:23', NULL, NULL, NULL, NULL),
('CRN25000007', 5, NULL, 'Alkahfi Gentle', '', '2025-12-04', '07:00:00', '', 'pending', '2025-12-03 09:01:35', '2025-12-03 09:01:35', 12, NULL, NULL, 12),
('CRN25000008', 3, 2, 'Alkahfi Asura', '122', '2025-12-04', '13:00:00', '', 'pending', '2025-12-04 02:03:54', '2025-12-04 02:03:54', NULL, NULL, NULL, NULL),
('CRN25000009', 3, 2, 'Blue Petra 2', '12122', '2025-12-04', '13:00:00', '', 'pending', '2025-12-04 03:08:20', '2025-12-04 03:08:20', NULL, NULL, NULL, NULL),
('CRN25000010', 3, 6, 'Marine Success', '1222', '2025-12-04', '13:00:00', '', 'pending', '2025-12-04 04:54:03', '2025-12-04 04:54:03', 12, NULL, NULL, NULL),
('CRN25000011', 3, 6, 'Alkahfi Asura', '1212', '2025-12-04', '16:00:00', '', 'pending', '2025-12-04 07:02:30', '2025-12-04 07:02:30', 12, NULL, NULL, NULL),
('CRN25000012', 6, 6, 'Ph Prestij', 'PO311111', '2026-01-01', '12:00:00', '', 'pending', '2025-12-04 07:40:12', '2025-12-04 07:40:12', 1, NULL, NULL, NULL),
('CRN25000013', 3, 2, 'Alkahfi Pride', '1212', '2025-12-08', '11:00:00', 'test for admin', 'pending', '2025-12-07 01:15:10', '2025-12-07 01:15:10', 12, 12, NULL, NULL),
('CRN25000014', 3, 7, 'Blue Petra 2', '122', '2025-12-07', '12:00:00', '', 'pending', '2025-12-07 02:54:24', '2025-12-07 02:54:24', 12, NULL, NULL, NULL),
('CRN25000015', 3, 11, 'Blue Petra 2', '1222', '2025-12-07', '13:00:00', '', 'pending', '2025-12-07 03:03:28', '2025-12-07 03:03:28', 12, NULL, NULL, NULL),
('CRN25000016', 3, 5, 'Blue Petra 2', '1222', '2025-12-07', '15:00:00', '', 'pending', '2025-12-07 06:52:10', '2025-12-07 06:52:10', 12, NULL, NULL, NULL),
('CRN25000017', 3, 2, 'Blue Petra 2', '122', '2025-12-08', '11:00:00', '', 'pending', '2025-12-08 03:01:19', '2025-12-08 03:01:19', 1222, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `customer_ifs_id` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `customer_full_name` varchar(255) DEFAULT NULL,
  `customer_code` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_person_designation` varchar(100) DEFAULT NULL,
  `contact_person_email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `business_address` text DEFAULT NULL,
  `business_phone` varchar(20) DEFAULT NULL,
  `business_contact_person` varchar(100) DEFAULT NULL,
  `business_contact_person_designation` varchar(100) DEFAULT NULL,
  `business_contact_person_email` varchar(100) DEFAULT NULL,
  `endorser_number_required` int(11) DEFAULT 1,
  `machinery_preferences` varchar(100) DEFAULT 'no_preferences',
  `customer_status` varchar(50) DEFAULT 'active',
  `customer_price_group` varchar(100) DEFAULT NULL,
  `email_notification` tinyint(1) DEFAULT 0,
  `account_status` varchar(50) DEFAULT 'released',
  `block_reason` text DEFAULT NULL,
  `new_request_status` varchar(50) DEFAULT 'allow',
  `internal_use` tinyint(1) DEFAULT 0,
  `is_agent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `customer_ifs_id`, `name`, `customer_full_name`, `customer_code`, `phone`, `contact_person`, `contact_person_designation`, `contact_person_email`, `address`, `postcode`, `location`, `business_address`, `business_phone`, `business_contact_person`, `business_contact_person_designation`, `business_contact_person_email`, `endorser_number_required`, `machinery_preferences`, `customer_status`, `customer_price_group`, `email_notification`, `account_status`, `block_reason`, `new_request_status`, `internal_use`, `is_agent`, `created_at`, `updated_at`) VALUES
(2, 'CUST12', 'Verification Corp', 'Verification Corp', 'V-CORP', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'no_preferences', 'active', '', 1, 'released', '', 'allow', 0, 0, '2026-01-06 20:11:59', '2026-01-27 04:26:40'),
(4, 'CUST13', 'TEST CORP', 'TEST CORP SDN BHD', 'TEST-CORP', '+609884411', 'siti test', 'Manager', 'siti@gmail.com', 'Jalan Testing 23,', '24000', 'Kemaman', '', '', '', '', '', 1, 'no_preferences', 'active', '', 1, 'released', '', 'allow', 0, 0, '2026-01-27 08:12:57', '2026-01-27 08:12:57'),
(6, 'CUS014', 'PETRONAS', 'PETRONAS SDN BHD', NULL, '+6047406233', 'muhammad salman bin salmin', 'Muhammad Alif Bin Satar', 'PETRONAS@GMAIL.COM', 'PT123 PETRONAS, KAWASAN PERINDUSTRIAN KEMAMAN TERENGGANU', '24000', 'Kemaman', NULL, NULL, NULL, NULL, NULL, 1, 'no_preferences', 'active', '', 1, 'released', '', 'allow', 0, 0, '2026-03-14 07:59:14', '2026-03-14 07:59:14'),
(7, 'CUS015', 'PETRONAS', 'PETRONAS SDN BHD', NULL, '+6047406233', 'muhammad salman bin salmin', 'Muhammad Alif Bin Satar', 'PETRONAS@GMAIL.COM', 'PT123 PETRONAS, KAWASAN PERINDUSTRIAN KEMAMAN TERENGGANU', '24000', 'Kemaman', NULL, NULL, NULL, NULL, NULL, 1, 'no_preferences', 'active', '', 1, 'released', '', 'allow', 0, 0, '2026-03-14 08:02:56', '2026-03-14 08:02:56');

-- --------------------------------------------------------

--
-- Table structure for table `document_templates`
--

CREATE TABLE `document_templates` (
  `id` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `template_type` varchar(50) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_templates`
--

INSERT INTO `document_templates` (`id`, `template_name`, `template_type`, `file_name`, `file_path`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Fuel & Water Form', 'fuel_water', 'Jobticket_template_KTSB.doc', '/uploads/templates/Jobticket_template_KTSB.doc', 1, '2026-02-24 03:30:21', '2026-02-24 03:30:21');

-- --------------------------------------------------------

--
-- Table structure for table `fuel_water_requests`
--

CREATE TABLE `fuel_water_requests` (
  `fuelwater_id` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_agent_id` int(11) DEFAULT NULL,
  `vessel_name` varchar(100) NOT NULL,
  `po_number` varchar(50) DEFAULT NULL,
  `request_date` date NOT NULL,
  `request_time` time NOT NULL,
  `fuel_quantity` decimal(10,2) DEFAULT NULL,
  `fuel_booking_time` time DEFAULT NULL,
  `fuel_remarks` text DEFAULT NULL,
  `water_quantity` decimal(10,2) DEFAULT NULL,
  `water_booking_time` time DEFAULT NULL,
  `water_remarks` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','supplied') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fuel_water_requests`
--

INSERT INTO `fuel_water_requests` (`fuelwater_id`, `user_id`, `assigned_agent_id`, `vessel_name`, `po_number`, `request_date`, `request_time`, `fuel_quantity`, `fuel_booking_time`, `fuel_remarks`, `water_quantity`, `water_booking_time`, `water_remarks`, `remarks`, `status`, `created_at`, `updated_at`) VALUES
('FW25000001', 3, 3, 'Alkahfi Care', 'PO9090', '2025-12-24', '09:16:25', 2.00, '13:00:00', NULL, 2.00, '14:00:00', NULL, NULL, 'pending', '2025-12-02 09:16:25', '2025-12-02 09:16:25'),
('FW25000002', 3, 2, 'Alkahfi Asura', '122', '2025-12-04', '03:58:16', 122.00, '12:00:00', NULL, 0.00, NULL, NULL, NULL, 'pending', '2025-12-04 03:58:16', '2025-12-04 03:58:16'),
('FW25000003', 6, 5, 'Alkahfi Care', 'PO3349', '2026-01-01', '07:49:18', 2.00, '11:00:00', NULL, 0.00, NULL, NULL, NULL, 'pending', '2025-12-04 07:49:18', '2025-12-04 07:49:18'),
('FW25000004', 3, 9, 'Pelican Cheer', '12222', '2025-12-07', '03:10:21', 212.00, '11:00:00', NULL, 0.00, NULL, NULL, NULL, 'pending', '2025-12-07 03:10:21', '2025-12-07 03:10:21');

-- --------------------------------------------------------

--
-- Table structure for table `generated_documents`
--

CREATE TABLE `generated_documents` (
  `id` int(11) NOT NULL,
  `request_id` varchar(20) NOT NULL,
  `request_type` varchar(50) NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `job_ticket` varchar(50) DEFAULT NULL,
  `generated_by` int(11) NOT NULL,
  `generated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `generated_documents`
--

INSERT INTO `generated_documents` (`id`, `request_id`, `request_type`, `document_type`, `file_name`, `file_path`, `job_ticket`, `generated_by`, `generated_at`) VALUES
(1, 'B26000009', 'marine', 'fuel_water', 'Fuel_Water_B26000009_20260224_043049.pdf', '/uploads/generated_documents/Fuel_Water_B26000009_20260224_043049.pdf', 'FW2602C307/001', 3, '2026-02-24 03:30:49'),
(2, 'B26000009', 'marine', 'fuel_water', 'Fuel_Water_B26000009_20260224_043421.pdf', '/uploads/generated_documents/Fuel_Water_B26000009_20260224_043421.pdf', 'FW2602C307/001', 3, '2026-02-24 03:34:21'),
(3, 'B26000010', 'marine', 'fuel_water', 'Fuel_Water_B26000010_20260224_043513.pdf', '/uploads/generated_documents/Fuel_Water_B26000010_20260224_043513.pdf', 'FW2602EB01/002', 3, '2026-02-24 03:35:13');

-- --------------------------------------------------------

--
-- Table structure for table `light_port_requests`
--

CREATE TABLE `light_port_requests` (
  `lightport_id` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vessel_name` varchar(255) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `request_type` enum('light_dues','port_dues') DEFAULT 'light_dues',
  `request_date` date NOT NULL,
  `request_time` time DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `services` text NOT NULL,
  `volume_unit` decimal(10,2) NOT NULL,
  `assigned_agent_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `light_port_requests`
--

INSERT INTO `light_port_requests` (`lightport_id`, `user_id`, `vessel_name`, `company_name`, `request_type`, `request_date`, `request_time`, `receipt_no`, `receipt_file`, `remarks`, `services`, `volume_unit`, `assigned_agent_id`, `status`, `created_at`, `updated_at`, `updated_by_user_id`) VALUES
('LP25000001', 3, 'Alkahfi Care', NULL, 'light_dues', '2026-01-08', NULL, NULL, NULL, NULL, 'TEST', 20.00, 2, 'pending', '2025-12-02 09:16:50', '2025-12-02 09:16:50', NULL),
('LP25000002', 6, 'Pelican Cheer', NULL, 'light_dues', '2026-01-01', NULL, NULL, NULL, NULL, 'test', 2.00, 8, 'pending', '2025-12-04 07:51:31', '2025-12-04 07:51:31', NULL),
('LPR-202601-001', 3, 'Jati Four', 'TEST CORP', 'port_dues', '2026-01-29', '11:27:00', 'BANK1234', 'uploads/receipts/PD_RECEIPT_1769563561_697965a9e4de3.pdf', 'EE', '', 0.00, NULL, 'pending', '2026-01-28 01:26:01', '2026-01-28 01:26:01', NULL),
('LPR-202601-002', 3, 'Marine Success', 'TEST CORP', 'light_dues', '2026-01-30', '00:13:00', 'BANK1232', 'uploads/receipts/LD_RECEIPT_1769566461_697970fd3a367.pdf', 'nj', '', 0.00, NULL, 'pending', '2026-01-28 02:14:21', '2026-01-28 02:14:21', NULL),
('LPR-202601-003', 3, 'Surya Halima', 'Verification Corp', 'port_dues', '2026-02-05', '18:18:00', 'BANK1267', 'uploads/receipts/PD_RECEIPT_1769671157_697b09f5401f9.pdf', 'TESTT', '', 0.00, NULL, 'pending', '2026-01-29 07:19:17', '2026-01-29 07:19:17', NULL),
('LPR-202602-001', 3, 'Surya Halima', 'Verification Corp', 'port_dues', '2026-02-21', '02:14:00', 'BANK6734', NULL, 'TEST', '', 0.00, NULL, 'pending', '2026-02-12 02:14:53', '2026-02-12 02:14:53', NULL),
('LPR-202602-002', 3, 'Gen 4 One', 'Verification Corp', 'port_dues', '2026-02-20', '22:00:00', 'BANK1344', 'uploads/receipts/PD_RECEIPT_1771227732_6992ca54b03e3.pdf', 'TEST', '', 0.00, NULL, 'pending', '2026-02-16 07:42:12', '2026-02-16 07:42:12', NULL),
('LPR-202602-003', 3, 'Marine Courage', 'TEST CORP', 'light_dues', '2026-02-25', '17:00:00', 'BANK6532', 'uploads/receipts/LD_RECEIPT_1771227782_6992ca86468ca.pdf', 'TEST', '', 0.00, NULL, 'pending', '2026-02-16 07:43:02', '2026-02-16 07:43:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `marine_crew_details`
--

CREATE TABLE `marine_crew_details` (
  `id` int(11) NOT NULL,
  `marine_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `passport_ic` varchar(50) NOT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `expiry` date DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL,
  `attendance_status` enum('show','no_show') DEFAULT 'show',
  `no_show_remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marine_crew_details`
--

INSERT INTO `marine_crew_details` (`id`, `marine_id`, `name`, `passport_ic`, `nationality`, `expiry`, `mobile`, `company`, `destination`, `attendance_status`, `no_show_remarks`) VALUES
(1, 'B25000001', 'Muhammad Abu Bin Ali', '970405110521', 'Malay', '2030-04-23', '0112345678', 'PETRONAS', 'LABUAN', 'show', NULL),
(2, 'B25000001', 'Muhammad Salman Bin Salmin', '900204110561', 'Malay', '2027-06-15', '0123456789', 'PETRONAS', 'LABUAN', 'show', NULL),
(3, 'B25000001', 'Muhammad Amir Aiman Bin Salman', '980303114531', 'Malay', '2030-12-15', '0145678912', 'HALLIBURTON', 'LABUAN', 'show', NULL),
(4, 'B25000001', 'Mohammad Aiman Hakim Bin Fairuz', '990903032341', 'Malay', '2030-12-13', '0156789123', 'HALLIBURTON', 'LABUAN', 'show', NULL),
(5, 'B25000004', 'Ahmad bin Razali', '850101-11-5023', 'Malaysian', NULL, '012-3456789', 'Petra Energy', 'Platform Bekok A', 'show', NULL),
(6, 'B25000004', 'Tan Wei Ling', '900520-10-6622', 'Malaysian', NULL, '019-8765432', 'Dialog Group', 'Vessel SK Pilot', 'show', NULL),
(7, 'B25000004', 'Muthu a/l Kumar', '921215-08-7733', 'Malaysian', NULL, '017-2233445', 'Sapura Energy', 'Platform Tapis B', 'show', NULL),
(8, 'B25000004', 'John Smith', '558822113', 'British', '2028-12-01', '011-55566677', 'Shell', 'Rig 5', 'show', NULL),
(9, 'B25000004', 'Supardi Nasir', 'B9876543', 'Indonesian', '2026-05-01', '011-33445566', 'Petra Energy', 'Vessel Setia', 'show', NULL),
(10, 'B25000004', 'Nguyen Van Hung', 'C5544332', 'Vietnamese', '2027-08-01', '018-9988776', 'Barakah Offshore', 'Platform Pulai', 'show', NULL),
(11, 'B25000004', 'Jose Rizal', 'P44556677', 'Filipino', '2025-11-01', '016-4455667', 'Sapura Energy', 'Vessel SK Deep', 'show', NULL),
(12, 'B25000004', 'Li Wei', 'E99887766', 'Chinese', '2029-03-01', '014-2233112', 'COSL', 'Rig 2', 'show', NULL),
(13, 'B25000004', 'Siti Sarah', 'A11223344', 'Malaysian', '2030-07-01', '013-5556666', 'Petronas', 'Platform Dulang', 'show', NULL),
(14, 'B25000004', 'Robert Doe', 'US98765432', 'American', '2026-01-01', '010-9988112', 'ExxonMobil', 'Platform Guntong', 'show', NULL),
(15, 'B25000004', 'Rajesh Patel', 'Z1234567', 'Indian', '2027-09-01', '015-6677889', 'Bumi Armada', 'Vessel Armada 1', 'show', NULL),
(16, 'B25000005', 'Ahmad bin Razali', '850101-11-5023', 'Malaysian', NULL, '012-3456789', 'Petra Energy', 'Platform Bekok A', 'show', NULL),
(17, 'B25000005', 'Tan Wei Ling', '900520-10-6622', 'Malaysian', NULL, '019-8765432', 'Dialog Group', 'Vessel SK Pilot', 'show', NULL),
(18, 'B25000005', 'Muthu a/l Kumar', '921215-08-7733', 'Malaysian', NULL, '017-2233445', 'Sapura Energy', 'Platform Tapis B', 'show', NULL),
(19, 'B25000005', 'John Smith', '558822113', 'British', '2028-12-01', '011-55566677', 'Shell', 'Rig 5', 'show', NULL),
(20, 'B25000005', 'Supardi Nasir', 'B9876543', 'Indonesian', '2026-05-01', '011-33445566', 'Petra Energy', 'Vessel Setia', 'show', NULL),
(21, 'B25000005', 'Nguyen Van Hung', 'C5544332', 'Vietnamese', '2027-08-01', '018-9988776', 'Barakah Offshore', 'Platform Pulai', 'show', NULL),
(22, 'B25000005', 'Jose Rizal', 'P44556677', 'Filipino', '2025-11-01', '016-4455667', 'Sapura Energy', 'Vessel SK Deep', 'show', NULL),
(23, 'B25000005', 'Li Wei', 'E99887766', 'Chinese', '2029-03-01', '014-2233112', 'COSL', 'Rig 2', 'show', NULL),
(24, 'B25000005', 'Siti Sarah', 'A11223344', 'Malaysian', '2030-07-01', '013-5556666', 'Petronas', 'Platform Dulang', 'show', NULL),
(25, 'B25000005', 'Robert Doe', 'US98765432', 'American', '2026-01-01', '010-9988112', 'ExxonMobil', 'Platform Guntong', 'show', NULL),
(26, 'B25000005', 'Rajesh Patel', 'Z1234567', 'Indian', '2027-09-01', '015-6677889', 'Bumi Armada', 'Vessel Armada 1', 'show', NULL),
(27, 'B25000005', 'Ahmed Al-Fayed', 'E1239876', 'Emirati', '2026-06-01', '019-2233445', 'Mubadala', 'Platform Raya', 'show', NULL),
(144, 'B25000007', 'Ahmad bin Razali', '850101-11-5023', 'Malaysian', '0000-00-00', '012-3456789', 'Petra Energy', 'Platform Bekok A', 'show', NULL),
(145, 'B25000007', 'Tan Wei Ling', '900520-10-6622', 'Malaysian', '0000-00-00', '019-8765432', 'Dialog Group', 'Vessel SK Pilot', 'show', NULL),
(146, 'B25000007', 'Muthu a/l Kumar', '921215-08-7733', 'Malaysian', '0000-00-00', '017-2233445', 'Sapura Energy', 'Platform Tapis B', 'show', NULL),
(147, 'B25000007', 'John Smith', '558822113', 'British', '2028-12-01', '011-55566677', 'Shell', 'Rig 5', 'show', NULL),
(148, 'B25000007', 'Supardi Nasir', 'B9876543', 'Indonesian', '2026-05-01', '011-33445566', 'Petra Energy', 'Vessel Setia', 'show', NULL),
(149, 'B25000007', 'Nguyen Van Hung', 'C5544332', 'Vietnamese', '2027-08-01', '018-9988776', 'Barakah Offshore', 'Platform Pulai', 'show', NULL),
(150, 'B25000007', 'Jose Rizal', 'P44556677', 'Filipino', '2025-11-01', '016-4455667', 'Sapura Energy', 'Vessel SK Deep', 'show', NULL),
(151, 'B25000007', 'Li Wei', 'E99887766', 'Chinese', '2029-03-01', '014-2233112', 'COSL', 'Rig 2', 'show', NULL),
(152, 'B25000007', 'Rajesh Patel', 'Z1234567', 'Indian', '2027-09-01', '015-6677889', 'Bumi Armada', 'Vessel Armada 1', 'show', NULL),
(153, 'B25000007', 'Ahmed Al-Fayed', 'E1239876', '', '2026-06-01', '019-2233445', 'Mubadala', 'Platform Raya', 'show', NULL),
(172, 'B25000006', 'Muhammad Shahrul Bin Ali', '970405110521', '', '2030-04-23', '0112345678', 'PETRONAS', 'LABUAN', 'show', NULL),
(173, 'B25000006', 'Muhammad Salmun Bin Ahmad', '900204110561', '', '2027-06-15', '0123456789', 'PETRONAS', 'LABUAN', 'show', NULL),
(174, 'B25000006', 'Muhammad Daniel Aiman Bin Salman', '980303114531', '', '2030-12-15', '0145678912', 'HALLIBURTON', 'LABUAN', 'show', NULL),
(175, 'B25000006', 'Mohammad Aiman Fauzan Bin Fairuz', '990111032341', '', '2030-12-17', '0156789123', 'HALLIBURTON', 'LABUAN', 'show', NULL),
(176, 'B25000006', 'Muhammad Asyraf Bin Ali', '990501112331', '', '2030-11-14', '0112345678', 'HALLIBURTON', 'SABAH', 'show', NULL),
(177, 'B25000006', 'Muhammad Salahuddin Bin Salmin', '990810110121', '', '2030-12-13', '0123345670', 'SOBENA', 'SARAWAK', 'show', NULL),
(191, 'B26000002', 'Muhammad Abu', '99010234451', 'Malaysian', '2040-09-18', '0177777777', 'PETRONAS', 'UK', 'show', NULL),
(192, 'B26000002', 'Muhammad Abi', '99329423471', 'Malaysian', '2040-09-19', '0177777778', 'PETRONAS', 'UK', 'show', NULL),
(193, 'B26000002', 'Muhammad Ali', '94637173831', 'Malaysian', '2040-09-20', '0177777779', 'PETRONAS', 'UK', 'show', NULL),
(194, 'B26000002', 'Muhammad Alo', '98742374211', 'Malaysian', '2040-09-21', '0177777780', 'PETRONAS', 'UK', 'show', NULL),
(195, 'B26000002', 'Muhammad Awie', '97563546351', 'Malaysian', '2040-09-22', '0177777781', 'PETRONAS', 'UK', 'show', NULL),
(196, 'B26000002', 'Muhammad Awang', '94428238481', 'Malaysian', '2040-09-23', '0177777782', 'HALLIBURTON', 'UK', 'show', NULL),
(197, 'B26000002', 'Muhammad Ahmad', '96372728371', 'Malaysian', '2040-09-24', '0177777783', 'HALLIBURTON', 'UK', 'show', NULL),
(198, 'B26000002', 'Muhammad rayyan', '93381212781', 'Malaysian', '2040-09-25', '0177777784', 'HALLIBURTON', 'UK', 'show', NULL),
(199, 'B26000002', 'Muhammad Alif', '92323831381', 'Malaysian', '2040-09-26', '0177777785', 'HALLIBURTON', 'UK', 'show', NULL),
(200, 'B26000002', 'Muhammad Asyraf', '93483834731', 'Malaysian', '2040-09-27', '0177777786', 'OCEAN', 'UK', 'show', NULL),
(201, 'B26000002', 'Muhammad Jang', '97898998981', 'Malaysian', '2040-09-28', '0177777787', 'OCEAN', 'UK', 'show', NULL),
(202, 'B26000002', 'Muhammad Frhan', '97776677671', 'Malaysian', '2040-09-29', '0177777788', '', 'UK', 'show', NULL),
(210, 'B26000004', 'muhammad salman bin salmin', '986754321', '', '2026-02-13', '018906775', 'PETRONAS', 'OFFSHORE PLATFORM 2', 'show', NULL),
(212, 'B26000005', 'Muhammad Alif Bin Satar', '095635612', '', '2030-11-20', '01986473', '', 'OFFFSHORE PLATFORM A', 'show', NULL),
(219, 'B26000006', 'MUHAMMAD SALMAN BIN SALMIN', '0786356473', 'MALAYSIAN', '2026-02-25', '01987654', 'EPIC', 'OFFSHORE PLATFORM A', 'show', ''),
(220, 'B26000006', 'MUHAMMAD SALMUN BIN AHMAD', '09977372', 'CHINESE', '2026-02-21', '0178834834', 'UNISZA', 'VESSEL SK PILOT', 'show', ''),
(221, 'B26000006', 'MUHAMMAD DANIEL AIMAN BIN SALMAN', '0387277237', 'FILIPINO', '2026-02-28', '07888882', 'UNISZA', 'PLATFORM TAPIS B', 'no_show', 'SAKIT UHUK'),
(222, 'B26000003', 'AHMAD FIRDAUS BIN ALI', '901010-11-1234', 'MALAYSIAN', '0000-00-00', '012-3456789', 'KTSB MARINE', 'OFFSHORE PLATFORM A', 'show', ''),
(223, 'B26000003', 'MUHAMMAD AIMAN BIN ROSLAN', '920505-08-5678', 'MALAYSIAN', '0000-00-00', '013-4567890', 'KTSB MARINE', 'OFFSHORE PLATFORM A', 'show', ''),
(224, 'B26000003', 'NUR SYAFIQAH BINTI HASSAN', '950808-06-4321', 'MALAYSIAN', '0000-00-00', '014-5678901', 'KTSB MARINE', 'OFFSHORE PLATFORM A', 'show', ''),
(225, 'B26000003', 'DANIEL LEE WEI JIAN', 'A12345678', 'MALAYSIAN', '2027-11-30', '016-6789012', 'KTSB MARINE', 'OFFSHORE PLATFORM A', 'show', ''),
(226, 'B26000003', 'ARIF HAKIM BIN ZAINAL', '880303-10-8765', 'MALAYSIAN', '0000-00-00', '017-7890123', 'KTSB MARINE', 'OFFSHORE PLATFORM A', 'show', ''),
(228, 'B26000007', 'NAMA', '0999112233', '', '2028-10-23', '0177282828', 'EPIC', 'UK (ULU KLANG)', 'show', ''),
(230, 'B26000008', 'MELO', '0342124567', '', '2030-03-13', '019764337', 'KUALA TERENGGANU SUPPORT BASE', 'PLATFORM B', 'show', ''),
(236, 'B26000011', 'MUHAMMAD FARIS BIN DAUD', '035674900', '', '2026-03-06', '017882334', 'EPIC', 'PLATFORM D', 'show', ''),
(240, 'B26000010', 'MUHAMMAD FARHAN BIN MOHD', '012341119071', '', '2031-07-25', '014567891', 'EPIC', 'PLATFORM C', 'show', ''),
(241, 'B26000010', 'MUHAMMAD SALMUN BIN SALMON', '0987654313', '', '2026-03-03', '0198653235', 'PETRONAS', 'PLATFORM C', 'show', ''),
(242, 'B26000010', 'MUHAMMAD ARSYAD BIN JAN', '99876532234', '', '2031-10-14', '0178866557', 'EPIC', 'PLATFORM C', 'show', ''),
(244, 'B26000009', 'MUHAMMAD SALMAN BIN SALMIN', '036898998', '', '2038-10-13', '01685795', 'KUALA TERENGGANU SUPPORT BASE', 'UK (ULU KLANG)', 'show', ''),
(248, 'B26000001', 'TT4FTR', '025534433', '', '2026-01-23', '0133334', 'UNISZA', 'UK (ULU KLANG)', 'show', ''),
(249, 'B26000013', 'Muhammad Badrul Bin Hisham', '991231110231', 'Malaysia', '2029-02-11', '0123456789', 'PETRONAS', 'OFFSHORE PLATFORM B', 'show', NULL),
(250, 'B26000013', 'Muhammad Najmi Bin Khairul', '990923030451', 'Malaysia', '2029-02-12', '0123456791', 'PETRONAS', 'OFFSHORE PLATFORM B', 'show', NULL),
(251, 'B26000013', 'Muhammad Tajul Bin Jufri', '990708110341', 'Malaysia', '2029-02-13', '0123456792', 'PETRONAS', 'OFFSHORE PLATFORM B', 'show', NULL),
(252, 'B26000013', 'Muhammad Rafi Bin Rahim', '990415069111', 'Malaysia', '2029-02-14', '0123456793', 'PETRONAS', 'OFFSHORE PLATFORM B', 'show', NULL),
(253, 'B26000013', 'Muhammad Asyraf Bin Mansor', '990918077121', 'Malaysia', '2029-02-15', '0123456794', 'PETRONAS', 'OFFSHORE PLATFORM B', 'show', NULL),
(254, 'B26000013', 'Muhammad Arsyad Bin Abdul Wahab', '980619112331', 'Malaysia', '2029-02-16', '0123456794', 'PETRONAS', 'OFFSHORE PLATFORM B', 'show', NULL),
(255, 'B26000013', 'Muhammad Sulaiman Bin Shahrul', '001122110661', 'Malaysia', '2029-02-17', '0123456795', 'TSM', 'OFFSHORE PLATFORM B', 'show', NULL),
(256, 'B26000013', 'Syed Aiman Bin Syed Hariz', '000122112211', 'Malaysia', '2029-02-18', '0123456796', 'TSM', 'OFFSHORE PLATFORM B', 'show', NULL),
(257, 'B26000013', 'Sheikh Faris Bih Sheikh Fadrul', '961213110231', 'Malaysia', '2029-02-19', '0193456797', 'TSM', 'OFFSHORE PLATFORM B', 'show', NULL),
(258, 'B26000013', 'Che Hasan Bin Che Ali', '010213067891', 'Malaysia', '2029-02-20', '0124456798', 'BINTULU OIL N GAS', 'OFFSHORE PLATFORM B', 'show', NULL),
(259, 'B26000013', 'Nik Rasyad Bin Nik Jasri', '970812031201', 'Malaysia', '2029-02-21', '0133456799', 'BINTULU OIL N GAS', 'OFFSHORE PLATFORM B', 'show', NULL),
(260, 'B26000014', 'MUHAMMAD TEST', '0190123456', 'Malaysia', '2029-06-12', '012345677', 'PETRONAS', 'PLATFORM C', 'show', NULL),
(261, 'B26000012', 'MUHAMMAD ALIF BIN SATAR', '031111111111', '', '2028-06-11', '098602247', 'UNISZA', 'UK (ULU KLANG)', 'show', ''),
(263, 'B26000015', 'MUHAMMAD ABU', '031111111111', 'MALAYSIAN', '2026-03-29', '+601111223344', 'EPIC', 'UK (ULU KLANG)', 'show', '');

-- --------------------------------------------------------

--
-- Table structure for table `marine_fuel_water_services`
--

CREATE TABLE `marine_fuel_water_services` (
  `id` int(11) NOT NULL,
  `marine_id` varchar(20) NOT NULL,
  `service_type` enum('fuel','water') NOT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `actual_quantity` decimal(10,2) DEFAULT NULL,
  `booking_time` time DEFAULT NULL,
  `actual_booking_time` time DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marine_fuel_water_services`
--

INSERT INTO `marine_fuel_water_services` (`id`, `marine_id`, `service_type`, `quantity`, `actual_quantity`, `booking_time`, `actual_booking_time`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 'B25000001', 'fuel', 10.00, NULL, '21:00:00', NULL, 'null', '2026-01-20 04:55:13', '2026-01-20 04:55:13'),
(4, 'B25000001', 'water', 10.00, NULL, '21:35:00', NULL, 'null', '2026-01-20 04:55:13', '2026-01-20 04:55:13'),
(28, 'B25000007', 'fuel', 122.00, NULL, '00:17:00', NULL, 'test for fuel', '2026-01-21 08:41:34', '2026-01-21 08:41:34'),
(29, 'B25000007', 'water', 122.00, NULL, '11:18:00', NULL, 'test for water', '2026-01-21 08:41:34', '2026-01-21 08:41:34'),
(33, 'B25000006', 'fuel', 2.00, NULL, '15:19:00', NULL, 'null', '2026-01-21 08:55:12', '2026-01-21 08:55:12'),
(38, 'B26000002', 'fuel', 4.00, NULL, '15:08:00', NULL, 'XX', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(39, 'B26000002', 'water', 4.00, NULL, '15:08:00', NULL, 'X', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(44, 'B26000004', 'water', 1000.00, NULL, '09:20:00', NULL, 'TEST', '2026-02-12 01:32:17', '2026-02-12 01:32:17'),
(47, 'B26000005', 'fuel', 100.00, NULL, '02:18:00', NULL, 'TEST', '2026-02-12 02:21:25', '2026-02-12 02:21:25'),
(48, 'B26000005', 'water', 100.00, NULL, '04:19:00', NULL, 'TEST', '2026-02-12 02:21:25', '2026-02-12 02:21:25'),
(52, 'B26000006', 'fuel', 100.00, NULL, '19:15:00', NULL, 'TEST', '2026-02-22 01:56:52', '2026-02-22 01:56:52'),
(53, 'B26000003', 'fuel', 7.00, NULL, '00:11:00', NULL, 'EOW', '2026-02-23 00:41:24', '2026-02-23 00:41:24'),
(54, 'B26000003', 'water', 5.00, NULL, '09:15:00', NULL, 'OW', '2026-02-23 00:41:24', '2026-02-23 00:41:24'),
(57, 'B26000007', 'fuel', 1.00, NULL, '12:51:00', NULL, '', '2026-02-23 04:50:25', '2026-02-23 04:50:25'),
(58, 'B26000007', 'water', 1.00, NULL, '12:51:00', NULL, '', '2026-02-23 04:50:25', '2026-02-23 04:50:25'),
(61, 'B26000008', 'fuel', 2.00, NULL, '12:55:00', NULL, '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(62, 'B26000008', 'water', 2.00, NULL, '12:56:00', NULL, '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(68, 'B26000011', 'fuel', 1.00, 0.00, '14:17:00', '00:00:00', '', '2026-02-23 06:56:49', '2026-02-23 06:56:49'),
(69, 'B26000011', 'water', 1.00, 0.00, '14:17:00', '00:00:00', '', '2026-02-23 06:56:49', '2026-02-23 06:56:49'),
(72, 'B26000010', 'fuel', 2.00, 3.00, '14:11:00', '14:59:00', '', '2026-02-23 06:59:19', '2026-02-23 06:59:19'),
(73, 'B26000010', 'water', 2.00, 3.00, '14:11:00', '14:00:00', '', '2026-02-23 06:59:19', '2026-02-23 06:59:19'),
(75, 'B26000009', 'water', 1.00, 2.00, '13:18:00', '19:10:00', '', '2026-02-23 07:16:10', '2026-02-23 07:16:10'),
(82, 'B26000001', 'fuel', 34.00, 44.00, '12:04:00', '17:26:00', 'FFF', '2026-02-23 07:26:53', '2026-02-23 07:26:53'),
(83, 'B26000001', 'water', 454.00, 455.00, '14:01:00', '15:31:00', 'GT', '2026-02-23 07:26:53', '2026-02-23 07:26:53'),
(84, 'B26000013', 'fuel', 3000.00, NULL, '11:53:00', NULL, 'POP', '2026-02-23 23:54:32', '2026-02-23 23:54:32'),
(85, 'B26000013', 'water', 3000.00, NULL, '07:55:00', NULL, 'POP', '2026-02-23 23:54:32', '2026-02-23 23:54:32'),
(86, 'B26000014', 'fuel', 100.00, NULL, '10:57:00', NULL, NULL, '2026-02-23 23:57:29', '2026-02-23 23:57:29'),
(87, 'B26000014', 'water', 100.00, NULL, '10:57:00', NULL, NULL, '2026-02-23 23:57:29', '2026-02-23 23:57:29'),
(88, 'B26000012', 'fuel', 2.00, 3.00, '15:19:00', '18:17:00', '', '2026-02-24 01:12:29', '2026-02-24 01:12:29'),
(89, 'B26000012', 'water', 2.00, 3.00, '03:21:00', '20:18:00', '', '2026-02-24 01:12:29', '2026-02-24 01:12:29'),
(91, 'B26000015', 'fuel', 3.00, 3.00, '07:00:00', '07:00:00', '', '2026-03-15 00:38:08', '2026-03-15 00:38:08');

-- --------------------------------------------------------

--
-- Table structure for table `marine_general_works`
--

CREATE TABLE `marine_general_works` (
  `id` int(11) NOT NULL,
  `marine_id` varchar(20) NOT NULL,
  `work_type` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marine_general_works`
--

INSERT INTO `marine_general_works` (`id`, `marine_id`, `work_type`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 'B25000001', 'Discharge', 'null', '2026-01-20 04:59:27', '2026-01-20 04:59:27'),
(4, 'B25000001', 'Maintenance', 'null', '2026-01-20 04:59:27', '2026-01-20 04:59:27'),
(6, 'B25000001', 'Unmooring', 'null', '2026-01-20 04:59:27', '2026-01-20 04:59:27'),
(38, 'B25000007', 'Discharge', 'test discharge', '2026-01-21 08:41:34', '2026-01-21 08:41:34'),
(39, 'B25000007', 'Loading', 'null', '2026-01-21 08:41:34', '2026-01-21 08:41:34'),
(40, 'B25000007', 'Inspection', 'null', '2026-01-21 08:41:34', '2026-01-21 08:41:34'),
(44, 'B25000006', 'Crew Change', 'null', '2026-01-21 08:55:12', '2026-01-21 08:55:12'),
(61, 'B26000002', 'Discharge', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(62, 'B26000002', 'Loading', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(63, 'B26000002', 'Inspection', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(64, 'B26000002', 'Maintenance', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(65, 'B26000002', 'Standby', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(66, 'B26000002', 'Touch   Go', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(67, 'B26000002', 'Mooring', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(68, 'B26000002', 'Unmooring', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(69, 'B26000002', 'Fire Fighter', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(70, 'B26000002', 'Pneumatic Rubber Fender', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(71, 'B26000002', 'Gangway 6 Meter', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(72, 'B26000002', 'Gangway 10 Meter', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(73, 'B26000002', 'Gangway 15 Meter', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(74, 'B26000002', 'Crew Change', '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(86, 'B26000004', 'Discharge', '', '2026-02-12 01:32:17', '2026-02-12 01:32:17'),
(87, 'B26000004', 'Loading', '', '2026-02-12 01:32:17', '2026-02-12 01:32:17'),
(88, 'B26000004', 'Inspection', '', '2026-02-12 01:32:17', '2026-02-12 01:32:17'),
(92, 'B26000005', 'Discharge', 'TEST', '2026-02-12 02:21:25', '2026-02-12 02:21:25'),
(93, 'B26000005', 'Loading', '', '2026-02-12 02:21:25', '2026-02-12 02:21:25'),
(94, 'B26000005', 'Inspection', '', '2026-02-12 02:21:25', '2026-02-12 02:21:25'),
(104, 'B26000006', 'Loading', '', '2026-02-22 01:56:52', '2026-02-22 01:56:52'),
(105, 'B26000006', 'Inspection', '', '2026-02-22 01:56:52', '2026-02-22 01:56:52'),
(106, 'B26000006', 'Standby', '', '2026-02-22 01:56:52', '2026-02-22 01:56:52'),
(107, 'B26000003', 'Discharge', '', '2026-02-23 00:41:24', '2026-02-23 00:41:24'),
(108, 'B26000003', 'Loading', '', '2026-02-23 00:41:24', '2026-02-23 00:41:24'),
(109, 'B26000003', 'Touch   Go', '', '2026-02-23 00:41:24', '2026-02-23 00:41:24'),
(110, 'B26000003', 'Gangway 10 Meter', '', '2026-02-23 00:41:24', '2026-02-23 00:41:24'),
(111, 'B26000003', 'Gangway 15 Meter', 'IOW', '2026-02-23 00:41:24', '2026-02-23 00:41:24'),
(115, 'B26000007', 'Discharge', '', '2026-02-23 04:50:25', '2026-02-23 04:50:25'),
(116, 'B26000007', 'Loading', '', '2026-02-23 04:50:25', '2026-02-23 04:50:25'),
(117, 'B26000007', 'Inspection', '', '2026-02-23 04:50:25', '2026-02-23 04:50:25'),
(132, 'B26000008', 'Discharge', '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(133, 'B26000008', 'Loading', '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(134, 'B26000008', 'Inspection', '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(135, 'B26000008', 'Maintenance', '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(136, 'B26000008', 'Standby', '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(137, 'B26000008', 'Touch   Go', '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(138, 'B26000008', 'Mooring', '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(139, 'B26000008', 'Unmooring', '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(140, 'B26000008', 'Fire Fighter', 'ILO', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(141, 'B26000008', 'Pneumatic Rubber Fender', '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(142, 'B26000008', 'Gangway 6 Meter', '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(143, 'B26000008', 'Gangway 10 Meter', '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(144, 'B26000008', 'Gangway 15 Meter', '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(145, 'B26000008', 'Crew Change', '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(152, 'B26000011', 'Loading', '', '2026-02-23 06:56:49', '2026-02-23 06:56:49'),
(153, 'B26000011', 'Gangway 15 Meter', '', '2026-02-23 06:56:49', '2026-02-23 06:56:49'),
(156, 'B26000010', 'Discharge', '', '2026-02-23 06:59:19', '2026-02-23 06:59:19'),
(157, 'B26000010', 'Loading', '', '2026-02-23 06:59:19', '2026-02-23 06:59:19'),
(160, 'B26000009', 'Loading', '', '2026-02-23 07:16:10', '2026-02-23 07:16:10'),
(161, 'B26000009', 'Inspection', '', '2026-02-23 07:16:10', '2026-02-23 07:16:10'),
(168, 'B26000001', 'Discharge', '', '2026-02-23 07:26:53', '2026-02-23 07:26:53'),
(169, 'B26000001', 'Mooring', '', '2026-02-23 07:26:53', '2026-02-23 07:26:53'),
(170, 'B26000013', 'Discharge', 'POL', '2026-02-23 23:54:32', '2026-02-23 23:54:32'),
(171, 'B26000013', 'Loading', NULL, '2026-02-23 23:54:32', '2026-02-23 23:54:32'),
(172, 'B26000013', 'Touch   Go', 'LIO', '2026-02-23 23:54:32', '2026-02-23 23:54:32'),
(173, 'B26000013', 'Mooring', NULL, '2026-02-23 23:54:32', '2026-02-23 23:54:32'),
(174, 'B26000013', 'Unmooring', NULL, '2026-02-23 23:54:32', '2026-02-23 23:54:32'),
(175, 'B26000013', 'Gangway 15 Meter', NULL, '2026-02-23 23:54:32', '2026-02-23 23:54:32'),
(176, 'B26000013', 'Crew Change', NULL, '2026-02-23 23:54:32', '2026-02-23 23:54:32'),
(177, 'B26000014', 'Unmooring', NULL, '2026-02-23 23:57:29', '2026-02-23 23:57:29'),
(178, 'B26000014', 'Fire Fighter', NULL, '2026-02-23 23:57:29', '2026-02-23 23:57:29'),
(179, 'B26000012', 'Discharge', '', '2026-02-24 01:12:29', '2026-02-24 01:12:29'),
(180, 'B26000012', 'Loading', '', '2026-02-24 01:12:29', '2026-02-24 01:12:29'),
(181, 'B26000012', 'maintenance', '', '2026-02-24 01:12:29', '2026-02-24 01:12:29'),
(185, 'B26000015', 'Loading', '', '2026-03-15 00:38:08', '2026-03-15 00:38:08'),
(186, 'B26000015', 'Inspection', '', '2026-03-15 00:38:08', '2026-03-15 00:38:08'),
(187, 'B26000015', 'Maintenance', '', '2026-03-15 00:38:08', '2026-03-15 00:38:08');

-- --------------------------------------------------------

--
-- Table structure for table `marine_other_services`
--

CREATE TABLE `marine_other_services` (
  `id` int(11) NOT NULL,
  `marine_id` varchar(20) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marine_other_services`
--

INSERT INTO `marine_other_services` (`id`, `marine_id`, `service_type`, `quantity`, `details`, `created_at`, `updated_at`) VALUES
(1, 'B25000001', 'packed_meals', 5, NULL, '2026-01-20 04:59:38', '2026-01-20 04:59:38'),
(2, 'B25000005', 'packed_meals', 12, NULL, '2026-01-20 04:59:38', '2026-01-20 04:59:38'),
(5, 'B25000001', 'snack_pack', 5, NULL, '2026-01-20 04:59:38', '2026-01-20 04:59:38'),
(7, 'B25000001', 'baggage', NULL, '5', '2026-01-20 04:59:38', '2026-01-20 04:59:38'),
(8, 'B25000001', 'bag_tagging', 5, NULL, '2026-01-20 04:59:38', '2026-01-20 04:59:38'),
(55, 'B25000007', 'packed_meals', 12, '', '2026-01-21 08:41:34', '2026-01-21 08:41:34'),
(56, 'B25000007', 'snack_pack', 12, '', '2026-01-21 08:41:34', '2026-01-21 08:41:34'),
(57, 'B25000007', 'baggage_handling', 12, '', '2026-01-21 08:41:34', '2026-01-21 08:41:34'),
(58, 'B25000007', 'bag_tagging', 12, '', '2026-01-21 08:41:34', '2026-01-21 08:41:34'),
(62, 'B25000006', 'takeaway', 6, '', '2026-01-21 08:55:12', '2026-01-21 08:55:12'),
(68, 'B26000002', 'packed_meals', 4, '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(69, 'B26000002', 'snack_pack', 4, '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(70, 'B26000002', 'baggage', 0, '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(71, 'B26000002', 'bag_tagging', 4, '', '2026-01-22 08:42:47', '2026-01-22 08:42:47'),
(81, 'B26000004', 'packed_meals', 0, '', '2026-02-12 01:32:16', '2026-02-12 01:32:16'),
(82, 'B26000004', 'snack_pack', 1, '', '2026-02-12 01:32:17', '2026-02-12 01:32:17'),
(83, 'B26000004', 'baggage', 1, '', '2026-02-12 01:32:17', '2026-02-12 01:32:17'),
(84, 'B26000004', 'bag_tagging', 1, '', '2026-02-12 01:32:17', '2026-02-12 01:32:17'),
(89, 'B26000005', 'packed_meals', 1, '', '2026-02-12 02:21:25', '2026-02-12 02:21:25'),
(90, 'B26000005', 'snack_pack', 1, '', '2026-02-12 02:21:25', '2026-02-12 02:21:25'),
(91, 'B26000005', 'baggage', 0, '', '2026-02-12 02:21:25', '2026-02-12 02:21:25'),
(92, 'B26000005', 'bag_tagging', 1, '', '2026-02-12 02:21:25', '2026-02-12 02:21:25'),
(99, 'B26000006', 'packed_meals', 2, '', '2026-02-22 01:56:52', '2026-02-22 01:56:52'),
(100, 'B26000006', 'snack_pack', 2, '', '2026-02-22 01:56:52', '2026-02-22 01:56:52'),
(101, 'B26000003', 'takeaway', 5, '', '2026-02-23 00:41:24', '2026-02-23 00:41:24'),
(102, 'B26000003', 'baggage_handling', 5, '', '2026-02-23 00:41:24', '2026-02-23 00:41:24'),
(107, 'B26000007', 'packed_meals', 1, '', '2026-02-23 04:50:25', '2026-02-23 04:50:25'),
(108, 'B26000007', 'snack_pack', 1, '', '2026-02-23 04:50:25', '2026-02-23 04:50:25'),
(109, 'B26000007', 'baggage_handling', 1, '', '2026-02-23 04:50:25', '2026-02-23 04:50:25'),
(110, 'B26000007', 'bag_tagging', 1, '', '2026-02-23 04:50:25', '2026-02-23 04:50:25'),
(113, 'B26000008', 'takeaway', 1, '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(114, 'B26000008', 'baggage_handling', 1, '', '2026-02-23 04:54:09', '2026-02-23 04:54:09'),
(120, 'B26000011', 'packed_meals', 1, '', '2026-02-23 06:56:49', '2026-02-23 06:56:49'),
(121, 'B26000011', 'snack_pack', 1, '', '2026-02-23 06:56:49', '2026-02-23 06:56:49'),
(124, 'B26000010', 'takeaway', 1, '', '2026-02-23 06:59:19', '2026-02-23 06:59:19'),
(125, 'B26000010', 'baggage_handling', 1, '', '2026-02-23 06:59:19', '2026-02-23 06:59:19'),
(127, 'B26000009', 'packed_meals', 1, '', '2026-02-23 07:16:10', '2026-02-23 07:16:10'),
(134, 'B26000001', 'packed_meals', 0, '', '2026-02-23 07:26:53', '2026-02-23 07:26:53'),
(135, 'B26000013', 'packed_meals', 11, NULL, '2026-02-23 23:54:32', '2026-02-23 23:54:32'),
(136, 'B26000013', 'snack_pack', 11, NULL, '2026-02-23 23:54:32', '2026-02-23 23:54:32'),
(137, 'B26000013', 'baggage_handling', 11, NULL, '2026-02-23 23:54:32', '2026-02-23 23:54:32'),
(138, 'B26000013', 'bag_tagging', 11, NULL, '2026-02-23 23:54:32', '2026-02-23 23:54:32'),
(139, 'B26000014', 'takeaway', 1, NULL, '2026-02-23 23:57:29', '2026-02-23 23:57:29'),
(140, 'B26000014', 'baggage_handling', 1, NULL, '2026-02-23 23:57:29', '2026-02-23 23:57:29'),
(141, 'B26000012', 'packed_meals', 0, '', '2026-02-24 01:12:29', '2026-02-24 01:12:29'),
(142, 'B26000012', 'snack_pack', 0, '', '2026-02-24 01:12:29', '2026-02-24 01:12:29'),
(147, 'B26000015', 'packed_meals', 1, '', '2026-03-15 00:38:08', '2026-03-15 00:38:08'),
(148, 'B26000015', 'snack_pack', 1, '', '2026-03-15 00:38:08', '2026-03-15 00:38:08'),
(149, 'B26000015', 'baggage_handling', 1, '', '2026-03-15 00:38:08', '2026-03-15 00:38:08'),
(150, 'B26000015', 'bag_tagging', 1, '', '2026-03-15 00:38:08', '2026-03-15 00:38:08');

-- --------------------------------------------------------

--
-- Table structure for table `marine_overtime_requests`
--

CREATE TABLE `marine_overtime_requests` (
  `overtime_id` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `request_date` date NOT NULL,
  `request_time` time NOT NULL,
  `vessels_data` longtext NOT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `receipt_files` longtext DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marine_overtime_requests`
--

INSERT INTO `marine_overtime_requests` (`overtime_id`, `user_id`, `company_name`, `request_date`, `request_time`, `vessels_data`, `receipt_no`, `receipt_files`, `remarks`, `status`, `created_at`, `updated_at`) VALUES
('MO-202601-001', 3, 'TEST CORP', '2026-01-29', '03:02:00', '[\"Surya Halima\",\"Alkahfi Chief\",\"Ph Prestij\"]', 'BANK1244', '[\"uploads\\/receipts\\/MO_RECEIPT_1769565644_69796dcc7e33b_0.pdf\"]', 'yy', 'pending', '2026-01-28 02:00:44', '2026-01-28 02:00:44');

-- --------------------------------------------------------

--
-- Table structure for table `marine_requests`
--

CREATE TABLE `marine_requests` (
  `marine_id` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_agent_id` int(11) DEFAULT NULL,
  `berth_id` varchar(10) DEFAULT NULL,
  `vessel_name` varchar(100) NOT NULL,
  `po_number` varchar(50) DEFAULT NULL,
  `eta` datetime NOT NULL,
  `etd` datetime NOT NULL,
  `actual_eta` datetime DEFAULT NULL,
  `actual_etd` datetime DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `crew_transfer_type` enum('sign_on','sign_off') DEFAULT NULL,
  `bod_no` varchar(20) DEFAULT NULL,
  `job_ticket` varchar(50) DEFAULT NULL,
  `log_no` varchar(20) DEFAULT NULL,
  `status` enum('pending','assign','in progress','pending endorsement','endorsed','request amendment','cancel') DEFAULT 'pending',
  `endorsed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `endorsed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` int(11) DEFAULT NULL,
  `amendment_requested_at` timestamp NULL DEFAULT NULL,
  `amendment_notes` text DEFAULT NULL,
  `amendment_requested_by` int(11) DEFAULT NULL,
  `berth_assigned_date` date DEFAULT NULL,
  `berth_assigned_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marine_requests`
--

INSERT INTO `marine_requests` (`marine_id`, `user_id`, `assigned_agent_id`, `berth_id`, `vessel_name`, `po_number`, `eta`, `etd`, `actual_eta`, `actual_etd`, `company`, `remarks`, `crew_transfer_type`, `bod_no`, `job_ticket`, `log_no`, `status`, `endorsed_by`, `created_at`, `updated_at`, `endorsed_at`, `cancelled_at`, `cancelled_by`, `amendment_requested_at`, `amendment_notes`, `amendment_requested_by`, `berth_assigned_date`, `berth_assigned_at`) VALUES
('B25000001', 3, 2, '0', 'Gen 4 One', 'PO8978', '2025-12-04 05:00:00', '2025-12-05 05:00:00', NULL, NULL, 'KTSB', '', 'sign_on', 'B25000001', NULL, NULL, '', NULL, '2025-12-02 09:03:22', '2026-01-20 08:40:52', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-20', '2026-01-20 16:40:52'),
('B25000003', 3, 2, 'Berth 2', 'Blue Petra 2', '122', '2025-12-04 12:00:00', '2025-12-04 13:00:00', '2026-02-27 20:30:00', '2026-03-01 20:31:00', 'Kuala Terengganu Support Base', '', NULL, 'B25000003', NULL, 'L22222', 'endorsed', 3, '2025-12-04 03:58:55', '2026-02-23 04:15:32', '2026-02-23 04:15:32', NULL, NULL, NULL, NULL, NULL, '2026-01-20', '2026-01-20 17:13:10'),
('B25000004', 3, 2, 'Berth 2', 'Blue Petra 2', '1222', '2025-12-04 16:00:00', '2025-12-04 17:00:00', NULL, NULL, 'Kuala Terengganu Support Base', '', 'sign_off', 'B25000004', NULL, NULL, 'assign', NULL, '2025-12-04 04:26:40', '2026-02-23 04:18:13', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-23', '2026-02-23 12:18:13'),
('B25000005', 3, 5, 'Berth 1', 'Blue Petra 2', '122', '2025-12-04 13:00:00', '2025-12-04 14:00:00', NULL, NULL, 'Kuala Terengganu Support Base', '', 'sign_on', 'B25000005', NULL, NULL, 'assign', NULL, '2025-12-04 04:44:44', '2026-02-23 04:18:13', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-23', '2026-02-23 12:18:13'),
('B25000006', 6, 10, NULL, 'Jati Four', 'PO3456', '2025-12-20 12:00:00', '2025-12-21 05:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Kuala Terengganu Support Base', '', 'sign_off', 'B25000006', NULL, 'L9990', 'request amendment', NULL, '2025-12-04 07:18:10', '2026-02-23 04:16:05', NULL, NULL, NULL, '2026-02-23 04:16:05', 'xse ah gtu', 3, NULL, NULL),
('B25000007', 3, 6, NULL, 'Alkahfi Chief', '12233', '2025-12-09 02:00:00', '2025-12-09 03:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Kuala Terengganu Support Base', 'test for berth', 'sign_on', 'B25000007', NULL, 'L5678', 'endorsed', NULL, '2025-12-08 03:40:26', '2026-01-22 08:10:43', '2026-01-22 08:10:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('B2500001', 3, 0, NULL, '', 'PO-TEST-479', '2025-12-03 13:02:00', '2025-12-05 13:02:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Kuala Terengganu Support Base', 'Test Remark', NULL, 'B2500001', NULL, 'L4567', 'request amendment', NULL, '2025-12-02 13:02:36', '2026-01-22 08:45:06', NULL, NULL, NULL, '2026-01-22 08:45:06', 'salah info', NULL, NULL, NULL),
('B2500002', 3, NULL, NULL, 'Test Vessel Marine', 'PO-TEST-M', '2025-12-02 13:04:16', '2025-12-02 13:04:16', NULL, NULL, 'Kuala Terengganu Support Base', 'Test Remark', NULL, 'B2500002', NULL, NULL, 'pending', NULL, '2025-12-02 13:04:16', '2025-12-02 13:04:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('B26000001', 3, 8, NULL, 'Alkahfi Courage', 'PO4521', '2026-01-23 13:00:00', '2026-01-24 14:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Kuala Terengganu Support Base', 'RTT', 'sign_on', 'B26000001', NULL, '', 'pending', NULL, '2026-01-22 04:02:10', '2026-02-23 00:26:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('B26000002', 3, 4, 'Berth 3', 'Jati Four', 'PO2134', '2026-01-31 16:00:00', '2026-02-05 16:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Kuala Terengganu Support Base', '4', 'sign_on', 'B26000002', NULL, 'L1238', 'endorsed', NULL, '2026-01-22 04:05:09', '2026-01-22 08:44:40', '2026-01-22 08:44:40', NULL, NULL, NULL, NULL, NULL, '2026-01-22', '2026-01-22 16:42:07'),
('B26000003', 3, 12, 'Berth 3', 'Gen 4 One', 'PO16800', '2026-01-28 10:00:00', '2026-01-29 09:00:00', '2026-02-25 00:45:00', '2026-02-26 00:45:00', 'Kuala Terengganu Support Base', 'mEOW', 'sign_off', 'B26000003', NULL, 'L2313', 'endorsed', 3, '2026-01-28 01:12:48', '2026-02-23 04:14:34', '2026-02-23 04:14:34', NULL, NULL, NULL, NULL, NULL, '2026-02-23', '2026-02-23 08:40:27'),
('B26000004', 3, 7, 'Berth 2', 'Alkahfi Care', 'PO3245', '2026-02-12 17:00:00', '2026-02-13 09:00:00', '2026-02-12 00:32:00', '2026-02-12 02:32:00', 'Kuala Terengganu Support Base', 'TEST', 'sign_on', 'B26000004', NULL, 'L2387', 'endorsed', NULL, '2026-02-12 01:17:13', '2026-02-16 06:38:01', '2026-02-16 06:38:01', NULL, NULL, NULL, NULL, NULL, '2026-02-12', '2026-02-12 09:17:54'),
('B26000005', 3, 7, 'Berth 2', 'Jati Four', 'PO3482', '2026-02-28 16:00:00', '2026-03-01 15:00:00', '2026-02-18 10:24:00', '2026-02-13 10:24:00', 'Kuala Terengganu Support Base', 'TEST', 'sign_on', 'B26000005', NULL, 'L6783', 'endorsed', NULL, '2026-02-12 02:13:50', '2026-02-12 02:22:15', '2026-02-12 02:22:15', NULL, NULL, NULL, NULL, NULL, '2026-02-12', '2026-02-12 10:19:26'),
('B26000006', 3, 13, 'Berth 3', 'Surya Halima', 'PO90876', '2026-02-20 09:00:00', '2026-02-21 09:00:00', '2026-02-20 12:00:00', '2026-02-21 08:00:00', 'Kuala Terengganu Support Base', 'TEST', 'sign_on', 'B26000006', NULL, 'L9976', 'endorsed', NULL, '2026-02-16 07:16:03', '2026-02-21 18:56:52', '2026-02-16 07:35:52', NULL, NULL, NULL, NULL, NULL, '2026-02-16', '2026-02-16 15:16:27'),
('B26000007', 3, 10, 'Berth 1', 'Marine Success', 'PO7321', '2026-02-28 15:00:00', '2026-03-01 16:00:00', '2026-02-28 16:49:00', '2026-03-01 17:56:00', 'Kuala Terengganu Support Base', '', 'sign_on', 'B26000007', NULL, 'L8889', 'request amendment', NULL, '2026-02-23 04:49:19', '2026-02-23 04:50:40', NULL, NULL, NULL, '2026-02-23 04:50:40', 'UHUK', 3, '2026-02-23', '2026-02-23 12:49:36'),
('B26000008', 3, 5, 'Berth 2', 'Ph Prestij', 'PO5432', '2026-03-01 16:00:00', '2026-03-02 14:00:00', '2026-03-02 12:53:00', '2026-03-03 12:54:00', 'Kuala Terengganu Support Base', '', 'sign_off', 'B26000008', NULL, 'L78553', 'endorsed', 3, '2026-02-23 04:53:18', '2026-02-23 04:54:29', '2026-02-23 04:54:29', NULL, NULL, NULL, NULL, NULL, '2026-02-23', '2026-02-23 12:53:32'),
('B26000009', 3, 5, 'Berth 3', 'Tegas Madani', 'PO4552', '2026-02-25 12:00:00', '2026-02-26 13:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Kuala Terengganu Support Base', '', 'sign_on', 'B26000009', 'FW2602C307/001', '', 'assign', NULL, '2026-02-23 05:15:20', '2026-02-24 03:30:48', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-23', '2026-02-23 15:17:40'),
('B26000010', 3, 13, 'Berth 2', 'Jati Four', 'PO4112', '2026-03-05 11:00:00', '2026-04-06 12:00:00', '2026-02-23 18:59:00', '2026-02-23 18:59:00', 'Kuala Terengganu Support Base', '', 'sign_off', 'B26000010', 'FW2602EB01/002', 'L3455', 'pending endorsement', NULL, '2026-02-23 06:07:39', '2026-02-24 03:35:13', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-23', '2026-02-23 14:57:50'),
('B26000011', 3, 12, NULL, 'Alkahfi Gentle', 'PO3541', '2026-03-06 12:00:00', '2026-03-07 13:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Kuala Terengganu Support Base', '', 'sign_on', 'B26000011', NULL, '', '', NULL, '2026-02-23 06:15:16', '2026-02-22 23:56:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-23 07:56:49'),
('B26000012', 3, 7, 'Berth 3', 'Pelican Cheer', 'PO5634', '2026-02-25 16:00:00', '2026-02-27 15:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Kuala Terengganu Support Base', '', 'sign_on', 'B26000012', NULL, '', 'in progress', NULL, '2026-02-23 07:17:25', '2026-02-23 18:12:29', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-23', '2026-02-23 15:17:40'),
('B26000013', 3, 5, NULL, 'Marine Success', 'PO3412', '2026-02-24 16:00:00', '2026-02-25 10:00:00', NULL, NULL, 'Kuala Terengganu Support Base', 'KAPAL', 'sign_on', 'B26000013', NULL, NULL, 'pending', NULL, '2026-02-23 23:54:32', '2026-02-23 23:54:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('B26000014', 3, 0, NULL, 'Alkahfi Chief', 'PO0987', '2026-02-25 11:00:00', '2026-02-26 12:00:00', NULL, NULL, 'Kuala Terengganu Support Base', '', 'sign_off', 'B26000014', NULL, NULL, 'pending', NULL, '2026-02-23 23:57:29', '2026-02-23 23:57:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('B26000015', 3, 4, 'Berth 2', 'Marine Success', 'PO77654', '2026-03-20 13:00:00', '2026-03-21 16:00:00', '2026-03-21 23:37:00', '2026-03-22 05:39:00', 'Kuala Terengganu Support Base', '', 'sign_on', 'B26000015', NULL, 'L8375', 'endorsed', 3, '2026-03-14 23:59:16', '2026-03-15 00:38:27', '2026-03-15 00:38:27', NULL, NULL, NULL, NULL, NULL, '2026-03-15', '2026-03-15 08:36:33'),
('TEST6194', 3, NULL, NULL, 'Test Vessel', 'PO-TEST', '2025-12-02 13:05:39', '2025-12-02 13:05:39', NULL, NULL, 'Kuala Terengganu Support Base', 'Test Remark', NULL, 'TEST6194', NULL, NULL, 'pending', NULL, '2025-12-02 13:05:39', '2025-12-02 13:05:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `nationalities`
--

CREATE TABLE `nationalities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nationalities`
--

INSERT INTO `nationalities` (`id`, `name`) VALUES
(1, 'Malaysian'),
(2, 'Indonesian'),
(3, 'Filipino'),
(4, 'Thai'),
(5, 'Vietnamese'),
(6, 'Singaporean'),
(7, 'Chinese'),
(8, 'Indian'),
(9, 'Bangladeshi'),
(10, 'Pakistani'),
(11, 'Sri Lankan'),
(12, 'Myanmarese'),
(13, 'Cambodian'),
(14, 'Laotian'),
(15, 'Bruneian'),
(16, 'Japanese'),
(17, 'South Korean'),
(18, 'Australian'),
(19, 'New Zealander'),
(20, 'British'),
(21, 'American'),
(22, 'Canadian'),
(23, 'French'),
(24, 'German'),
(25, 'Italian'),
(26, 'Spanish'),
(27, 'Dutch'),
(28, 'Russian'),
(29, 'Ukrainian'),
(30, 'Brazilian'),
(31, 'Mexican'),
(32, 'Egyptian'),
(33, 'South African'),
(34, 'Nigerian');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 'system', 'System notifG⬡\0\0??\0\0\0\0\0*\0\0\0\0\0\0ized.', '#', 1, '2025-12-07 01:36:23'),
(2, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000004', 1, '2025-12-07 01:38:15'),
(3, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000005', 1, '2025-12-07 01:38:22'),
(4, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000006', 1, '2025-12-07 01:43:23'),
(5, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000007', 1, '2025-12-07 01:43:27'),
(6, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000008', 1, '2025-12-07 01:52:43'),
(7, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000009', 1, '2025-12-07 01:57:24'),
(8, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000010', 1, '2025-12-07 02:08:54'),
(9, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000011', 1, '2025-12-07 02:13:49'),
(10, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000012', 1, '2025-12-07 02:14:23'),
(11, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000013', 1, '2025-12-07 02:15:59'),
(12, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000014', 1, '2025-12-07 02:16:13'),
(13, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000015', 1, '2025-12-07 02:21:01'),
(14, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000016', 1, '2025-12-07 02:22:39'),
(15, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000017', 1, '2025-12-07 02:30:37'),
(16, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000018', 1, '2025-12-07 02:30:48'),
(17, 'crew_sign_off', 'New Crew Sign Off Request from ktsb', 'sign-off-detail.php?id=CRF25000019', 1, '2025-12-07 02:39:41'),
(18, 'crew_sign_on', 'New Crew Sign On Request from ktsb', 'sign-on-detail.php?id=CRN25000014', 0, '2025-12-07 02:54:24'),
(19, 'crew_sign_on', 'New Crew Sign On Request from ktsb', 'sign-on-detail.php?id=CRN25000015', 0, '2025-12-07 03:03:28'),
(20, 'fuel_water', 'New Fuel & Water Request from ktsb', 'fuel-water-detail.php?id=FW25000004', 0, '2025-12-07 03:10:21'),
(21, 'crew_sign_on', 'New Crew Sign On Request from ktsb', 'sign-on-detail.php?id=CRN25000016', 0, '2025-12-07 06:52:10'),
(22, 'crew_sign_on', 'New Crew Sign On Request from ktsb', 'sign-on-detail.php?id=CRN25000017', 0, '2025-12-08 03:01:19'),
(23, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B25000007', 0, '2025-12-08 03:40:26'),
(24, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000001', 0, '2026-01-22 04:02:10'),
(25, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000002', 0, '2026-01-22 04:05:09'),
(26, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000003', 0, '2026-01-28 01:12:48'),
(27, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000004', 0, '2026-02-12 01:17:13'),
(28, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000005', 0, '2026-02-12 02:13:50'),
(29, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000006', 0, '2026-02-16 07:16:03'),
(30, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000007', 0, '2026-02-23 04:49:19'),
(31, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000008', 0, '2026-02-23 04:53:18'),
(32, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000009', 0, '2026-02-23 05:15:20'),
(33, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000010', 0, '2026-02-23 06:07:39'),
(34, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000011', 0, '2026-02-23 06:15:16'),
(35, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000012', 0, '2026-02-23 07:17:25'),
(36, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000013', 0, '2026-02-23 23:54:32'),
(37, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000014', 0, '2026-02-23 23:57:29'),
(38, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B26000015', 0, '2026-03-14 23:59:16');

-- --------------------------------------------------------

--
-- Table structure for table `numbers_table`
--

CREATE TABLE `numbers_table` (
  `n` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `numbers_table`
--

INSERT INTO `numbers_table` (`n`) VALUES
(0),
(1),
(2),
(3),
(4),
(5),
(6),
(7),
(8),
(9);

-- --------------------------------------------------------

--
-- Table structure for table `port_clearance_requests`
--

CREATE TABLE `port_clearance_requests` (
  `clearance_id` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `clearance_type` enum('inward','outward') NOT NULL,
  `vessel_name` varchar(255) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `request_date` date NOT NULL,
  `request_time` time NOT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `port_clearance_requests`
--

INSERT INTO `port_clearance_requests` (`clearance_id`, `user_id`, `clearance_type`, `vessel_name`, `company_name`, `request_date`, `request_time`, `receipt_no`, `receipt_file`, `remarks`, `status`, `created_at`, `updated_at`) VALUES
('PC-202601-001', 3, 'inward', 'Surya Halima', 'TEST CORP', '2026-01-31', '01:00:00', 'BANK1233', 'uploads/receipts/PC_RECEIPT_1769565492_69796d34dc5da.pdf', 'test', 'pending', '2026-01-28 01:58:12', '2026-01-28 01:58:12');

-- --------------------------------------------------------

--
-- Table structure for table `price_of_services`
--

CREATE TABLE `price_of_services` (
  `id` int(11) NOT NULL,
  `price_id` varchar(20) NOT NULL,
  `scope_of_work` text NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `effective_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `customer_group` varchar(100) DEFAULT 'All Customers',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_documents`
--

CREATE TABLE `request_documents` (
  `id` int(11) NOT NULL,
  `request_id` varchar(20) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploader_type` enum('user','agent','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_documents`
--

INSERT INTO `request_documents` (`id`, `request_id`, `file_name`, `file_path`, `uploaded_by`, `uploader_type`, `created_at`) VALUES
(2, 'FW25000001', 'Tester.pdf', 'uploads/Fuel & Water/FW25000001/Tester.pdf', 3, 'user', '2025-12-02 09:17:20'),
(3, 'FW25000001', 'Tester.pdf', 'uploads/Fuel & Water/FW25000001/Tester.pdf', 3, 'user', '2025-12-02 09:17:37'),
(4, 'LP25000001', 'Tester.pdf', 'uploads/Light Port/LP25000001/Tester.pdf', 3, 'user', '2025-12-02 09:25:03'),
(5, 'CRN25000001', 'MPI2025 ARE YOU READY.pdf', 'uploads/Crew Sign On/CRN25000001/MPI2025 ARE YOU READY.pdf', 3, 'user', '2025-12-02 09:25:49'),
(6, 'CRF25000001', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Crew Sign Off/CRF25000001/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 3, 'user', '2025-12-02 17:09:52'),
(7, 'CRF25000001', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Crew Sign Off/CRF25000001/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 3, 'user', '2025-12-02 17:20:29'),
(8, 'CRF25000001', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Crew Sign Off/CRF25000001/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-02 17:21:36'),
(9, 'CRF25000001', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Crew Sign Off/CRF25000001/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-02 17:23:47'),
(10, 'CRF25000001', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Crew Sign Off/CRF25000001/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-02 17:24:00'),
(11, 'CRF25000001', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Crew Sign Off/CRF25000001/KTSB_Crew_Sign_On_Template_20251120.xlsx', 3, 'user', '2025-12-02 17:30:43'),
(12, 'TEST6194', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Other/TEST6194/KTSB_Crew_Sign_On_Template_20251120.xlsx', 3, 'user', '2025-12-03 01:30:43'),
(13, 'TEST6194', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Other/TEST6194/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-03 01:31:11'),
(14, 'TEST6194', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Other/TEST6194/KTSB_Crew_Sign_On_Template_20251120.xlsx', 5, 'user', '2025-12-03 06:41:15'),
(15, 'CRN25000001', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Crew Sign On/CRN25000001/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 5, 'user', '2025-12-03 06:59:09'),
(16, 'CRN25000001', 'STUDEN JOINING REPORT.pdf', 'uploads/Crew Sign On/CRN25000001/STUDEN JOINING REPORT.pdf', 5, 'user', '2025-12-03 06:59:09'),
(17, 'FW25000001', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Fuel & Water/FW25000001/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 5, 'user', '2025-12-03 07:03:17'),
(18, 'FW25000001', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Fuel & Water/FW25000001/KTSB_Crew_Sign_On_Template_20251120.xlsx', 5, 'user', '2025-12-03 07:03:17'),
(19, 'FW25000001', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Fuel & Water/FW25000001/KTSB_Crew_Template_20251118.xlsx', 5, 'user', '2025-12-03 07:03:17'),
(20, 'LP25000001', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Light Port/LP25000001/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 5, 'user', '2025-12-03 07:03:40'),
(21, 'LP25000001', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Light Port/LP25000001/KTSB_Crew_Sign_On_Template_20251120.xlsx', 5, 'user', '2025-12-03 07:03:40'),
(22, 'LP25000001', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Light Port/LP25000001/KTSB_Crew_Template_20251118.xlsx', 5, 'user', '2025-12-03 07:03:40'),
(23, 'CRN25000007', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Crew Sign On/CRN25000007/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 5, 'user', '2025-12-03 09:23:24'),
(24, 'CRN25000007', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Crew Sign On/CRN25000007/KTSB_Crew_Sign_On_Template_20251120.xlsx', 5, 'user', '2025-12-03 09:23:24'),
(25, 'CRN25000007', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Crew Sign On/CRN25000007/KTSB_Crew_Template_20251118.xlsx', 5, 'user', '2025-12-03 09:23:24'),
(26, 'CRN25000006', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Crew Sign On/CRN25000006/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 5, 'user', '2025-12-03 09:23:43'),
(27, 'CRN25000006', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Crew Sign On/CRN25000006/KTSB_Crew_Sign_On_Template_20251120.xlsx', 5, 'user', '2025-12-03 09:23:43'),
(28, 'CRN25000006', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Crew Sign On/CRN25000006/KTSB_Crew_Template_20251118.xlsx', 5, 'user', '2025-12-03 09:23:43'),
(29, 'CRN25000005', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Crew Sign On/CRN25000005/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 5, 'user', '2025-12-03 09:27:36'),
(30, 'CRN25000005', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Crew Sign On/CRN25000005/KTSB_Crew_Sign_On_Template_20251120.xlsx', 5, 'user', '2025-12-03 09:27:36'),
(31, 'CRN25000005', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Crew Sign On/CRN25000005/KTSB_Crew_Template_20251118.xlsx', 5, 'user', '2025-12-03 09:27:36'),
(32, 'CRN25000003', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Crew Sign On/CRN25000003/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 3, 'user', '2025-12-04 00:34:51'),
(33, 'CRN25000003', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Crew Sign On/CRN25000003/KTSB_Crew_Sign_On_Template_20251120.xlsx', 3, 'user', '2025-12-04 00:34:51'),
(34, 'CRN25000003', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Crew Sign On/CRN25000003/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-04 00:34:51'),
(35, 'FW25000001', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Fuel & Water/FW25000001/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-04 01:14:33'),
(36, 'TEST6194', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Other/TEST6194/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 3, 'user', '2025-12-04 01:24:17'),
(37, 'TEST6194', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Other/TEST6194/KTSB_Crew_Sign_On_Template_20251120.xlsx', 3, 'user', '2025-12-04 01:24:17'),
(38, 'TEST6194', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Other/TEST6194/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-04 01:24:17'),
(39, 'TEST6194', 'wallpaper.PNG', 'uploads/Other/TEST6194/wallpaper.PNG', 3, 'user', '2025-12-04 01:25:05'),
(40, 'TEST6194', 'wallpaper.PNG', 'uploads/Other/TEST6194/wallpaper.PNG', 3, 'user', '2025-12-04 01:25:19'),
(41, 'TEST6194', 'wallpaper.PNG', 'uploads/Other/TEST6194/wallpaper.PNG', 3, 'user', '2025-12-04 01:26:03'),
(42, 'CRN25000007', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Crew Sign On/CRN25000007/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-04 01:50:08'),
(43, 'CRN25000008', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Crew Sign On/CRN25000008/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-04 02:09:04'),
(44, 'CRN25000009', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Crew Sign On/CRN25000009/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 3, 'user', '2025-12-04 03:10:41'),
(45, 'CRN25000009', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Crew Sign On/CRN25000009/KTSB_Crew_Sign_On_Template_20251120.xlsx', 3, 'user', '2025-12-04 03:10:41'),
(46, 'CRN25000009', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Crew Sign On/CRN25000009/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-04 03:10:41'),
(47, 'B25000005', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Marine/B25000005/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 3, 'user', '2025-12-04 04:45:09'),
(48, 'B25000005', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Marine/B25000005/KTSB_Crew_Sign_On_Template_20251120.xlsx', 3, 'user', '2025-12-04 04:45:09'),
(49, 'B25000005', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Marine/B25000005/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-04 04:45:09'),
(50, 'FW25000001', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Fuel & Water/FW25000001/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 3, 'user', '2025-12-04 05:22:37'),
(51, 'FW25000001', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Fuel & Water/FW25000001/KTSB_Crew_Sign_On_Template_20251120.xlsx', 3, 'user', '2025-12-04 05:22:37'),
(52, 'FW25000001', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Fuel & Water/FW25000001/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-04 05:22:37'),
(53, 'B25000006', 'KTSB_Marine_Crew_Template_20251202_test.xlsx', 'uploads/Marine/B25000006/KTSB_Marine_Crew_Template_20251202_test.xlsx', 6, 'user', '2025-12-04 07:52:20'),
(54, 'CRN25000012', 'midterm FM.pdf', 'uploads/Crew Sign On/CRN25000012/midterm FM.pdf', 6, 'user', '2025-12-04 07:53:18'),
(55, 'CRF25000002', 'past year answer question.pdf', 'uploads/Crew Sign Off/CRF25000002/past year answer question.pdf', 6, 'user', '2025-12-04 07:54:18'),
(56, 'FW25000001', 'Note 2 Nov 2025.pdf', 'uploads/Fuel & Water/FW25000001/Note 2 Nov 2025.pdf', 6, 'user', '2025-12-04 07:54:54'),
(57, 'LP25000002', 'Progress presentation 211025 Nl2 .pdf', 'uploads/Light Port/LP25000002/Progress presentation 211025 Nl2 .pdf', 6, 'user', '2025-12-04 07:56:22'),
(58, 'B25000006', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Marine/B25000006/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 3, 'user', '2025-12-08 03:01:51'),
(59, 'B25000006', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Marine/B25000006/KTSB_Crew_Sign_On_Template_20251120.xlsx', 3, 'user', '2025-12-08 03:01:51'),
(60, 'B25000006', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Marine/B25000006/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-08 03:01:51'),
(61, 'B25000007', 'KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 'uploads/Marine/B25000007/KTSB_Crew_Sign_On_Template_20251120 (1).xlsx', 3, 'user', '2025-12-11 03:36:49'),
(62, 'B25000007', 'KTSB_Crew_Sign_On_Template_20251120.xlsx', 'uploads/Marine/B25000007/KTSB_Crew_Sign_On_Template_20251120.xlsx', 3, 'user', '2025-12-11 03:36:50'),
(63, 'B25000007', 'KTSB_Crew_Template_20251118.xlsx', 'uploads/Marine/B25000007/KTSB_Crew_Template_20251118.xlsx', 3, 'user', '2025-12-11 03:36:50'),
(64, 'B25000007', 'STUDEN JOINING REPORT.pdf', 'uploads/Marine/B25000007/STUDEN JOINING REPORT.pdf', 3, 'user', '2025-12-11 03:36:50'),
(65, 'PC-202601-001', 'edit-request.php.pdf', 'assets/uploads/Port Clearance/PC-202601-001/edit-request.php.pdf', 3, 'user', '2026-01-28 01:59:01'),
(66, 'MO-202601-001', 'file.pdf', 'assets/uploads/Marine Overtime/MO-202601-001/file.pdf', 3, 'user', '2026-01-28 02:01:07'),
(67, 'LPR-202601-001', 'file.pdf', 'assets/uploads/Light Port/LPR-202601-001/file.pdf', 3, 'user', '2026-01-28 02:27:18'),
(68, 'LPR-202601-002', 'edit-request.php.pdf', 'assets/uploads/Light Port/LPR-202601-002/edit-request.php.pdf', 3, 'user', '2026-01-28 02:37:56'),
(69, 'LPR-202601-001', 'Permohon Slip-Cetak.pdf', 'assets/uploads/Light Port/LPR-202601-001/Permohon Slip-Cetak.pdf', 3, 'user', '2026-01-28 02:48:11'),
(70, 'LPR-202601-002', 'file.pdf', 'assets/uploads/Light Port/LPR-202601-002/file.pdf', 3, 'user', '2026-01-28 02:48:51'),
(71, 'B26000003', 'edit-request.php.pdf', 'assets/uploads/Marine/B26000003/edit-request.php.pdf', 3, 'admin', '2026-01-28 04:20:43'),
(72, 'B26000003', 'KTSB_Marine_Crew_Template_20260128.xlsx', 'assets/uploads/Marine/B26000003/KTSB_Marine_Crew_Template_20260128.xlsx', 3, 'admin', '2026-01-28 07:30:09'),
(73, 'B26000003', 'Permohon Slip-Cetak.pdf', 'assets/uploads/Marine/B26000003/Permohon Slip-Cetak.pdf', 3, 'admin', '2026-01-28 07:30:34'),
(74, 'B26000003', 'CSF47112-RubrikFinalReport.pdf', 'assets/uploads/Marine/B26000003/CSF47112-RubrikFinalReport.pdf', 3, 'user', '2026-01-29 00:42:10'),
(75, 'LPR-202601-001', 'gambar passport ain.pdf', 'assets/uploads/Light Port/LPR-202601-001/gambar passport ain.pdf', 3, 'admin', '2026-01-29 02:52:56'),
(76, 'LPR-202601-002', 'gambar passport ain.pdf', 'assets/uploads/Light Port/LPR-202601-002/gambar passport ain.pdf', 3, 'admin', '2026-01-29 03:07:50'),
(77, 'PC-202601-001', 'gambar passport ain.pdf', 'assets/uploads/Port Clearance/PC-202601-001/gambar passport ain.pdf', 3, 'admin', '2026-01-29 03:26:10'),
(78, 'MO-202601-001', 'gambar passport ain.pdf', 'assets/uploads/Marine Overtime/MO-202601-001/gambar passport ain.pdf', 3, 'admin', '2026-01-29 03:47:43'),
(79, 'B26000003', 'Industrial Training Final Report.docx', 'assets/uploads/Marine/B26000003/Industrial Training Final Report.docx', 3, 'admin', '2026-02-01 03:19:03'),
(80, 'B26000003', 'dfd&erd.pdf', 'assets/uploads/Marine/B26000003/dfd&erd.pdf', 3, 'user', '2026-02-01 03:27:36'),
(81, 'B26000003', '074579_ExerciseARandStatisticalMethods.pdf', 'assets/uploads/Marine/B26000003/074579_ExerciseARandStatisticalMethods.pdf', 3, 'admin', '2026-02-01 06:48:55'),
(82, 'MO-202601-001', '074579_ExerciseARandStatisticalMethods.pdf', 'assets/uploads/Marine Overtime/MO-202601-001/074579_ExerciseARandStatisticalMethods.pdf', 3, 'admin', '2026-02-01 06:58:39'),
(83, 'LPR-202601-003', 'DM.pdf', 'assets/uploads/Light Port/LPR-202601-003/DM.pdf', 3, 'admin', '2026-02-01 06:59:19'),
(84, 'B26000003', 'pradaftar_sem7_aina.pdf', 'assets/uploads/Marine/B26000003/pradaftar_sem7_aina.pdf', 3, 'admin', '2026-02-01 07:00:27'),
(85, 'LPR-202601-003', '074579_ExerciseARandStatisticalMethods.pdf', 'assets/uploads/Light Port/LPR-202601-003/074579_ExerciseARandStatisticalMethods.pdf', 3, 'user', '2026-02-01 07:21:58'),
(86, 'LPR-202601-003', 'Examination_Slip (2).pdf', 'assets/uploads/Light Port/LPR-202601-003/Examination_Slip (2).pdf', 3, 'admin', '2026-02-01 07:26:25'),
(87, 'B26000003', 'pradaftar_sem7_aina.pdf', 'assets/uploads/Marine/B26000003/pradaftar_sem7_aina.pdf', 3, 'admin', '2026-02-23 01:42:53');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('login_user_info_widget', '<h3>User Information</h3><p>Welcome to the KTSB Portal. Please log in to access your dashboard.</p><p>If you encounter any issues, please contact support.</p>', '2025-12-04 06:16:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_code` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `customer_code` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('company','user','admin') DEFAULT 'user',
  `phone_number` varchar(20) DEFAULT NULL,
  `is_endorser` tinyint(1) DEFAULT 0,
  `is_requisitioner` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_code`, `username`, `password_hash`, `company_name`, `customer_code`, `full_name`, `email`, `role`, `phone_number`, `is_endorser`, `is_requisitioner`, `created_by`, `status`, `created_at`, `last_login`) VALUES
(3, 'USR003', 'ktsb', '$2y$10$0R5OfV8PS5cfuPeP4tvM/.bABeiJBPOp81X9TkwBwdB/kTuH9GxAC', 'Kuala Terengganu Support Base', 'KTSB', NULL, 'admin@ktsb.com', 'user', NULL, 0, 0, NULL, 'active', '2025-12-02 06:01:21', '2026-03-14 23:57:05'),
(5, 'USR005', 'samad', '$2y$10$pCe3vhDs/rrYWclwU06LjOJmNC7l6vf2.KBZlJhkVvZz3TZKmsdN2', 'Kuala Terengganu Support Base', 'KTSB', NULL, 'samad@gmail.com', 'user', '123456789', 0, 0, 2, 'active', '2025-12-03 04:15:04', '2025-12-03 04:16:14'),
(6, 'USR006', 'ain', '$2y$10$NHGhBkXq8VqhUvEyXBPrO.5G0fEMgUMk2uHWCgVxdrpON0DNXraDO', 'Kuala Terengganu Support Base', 'KTSB', NULL, 'ain@gmail.com', 'user', '123456789', 0, 0, 2, 'active', '2025-12-04 06:34:46', '2025-12-07 06:50:35'),
(7, 'USR007', 'vcorp_user', '$2y$10$7f6D3O76tOHGYZkM1.h2UeJsysbkZQuIwP/E/8Rpael6JnhK/7tjG', 'EPIC', 'EPIC', 'Muhammad Ali', 'vcorp@test.com', 'user', '1234567890', 0, 1, 2, 'active', '2026-01-07 04:15:40', '2026-01-07 05:38:09'),
(8, 'USR008', 'ORANGTEST', '$2y$10$1ybjPC9SViCgUwPoM5ziG.eE2JJEPm1lymrBb5zVGR5cx62E33Ysm', 'EPIC', 'EPIC', 'muhammad abu', 'orangtest@gmail.com', 'user', '+601111223344', 1, 0, 3, 'active', '2026-03-14 08:29:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('user','agent','admin') NOT NULL DEFAULT 'user',
  `session_id` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `user_type`, `session_id`, `ip_address`, `user_agent`, `created_at`, `last_activity`) VALUES
(92, 3, 'user', 'cam751b866rk9hia2kb0rqmdii', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-02-16 06:37:17', '2026-02-16 08:01:18'),
(94, 3, 'user', 'j994jip6ss4u9llemd833m8sn4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-02-22 00:52:24', '2026-02-22 07:26:55'),
(95, 3, 'user', 'hgb6ek81ch0a2jugtddhl1n4hh', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-02-23 00:13:42', '2026-02-23 07:16:26'),
(96, 3, 'user', '8s6mk43ptsld69cdbmq5so1885', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-02-23 23:38:52', '2026-02-23 23:39:26'),
(97, 3, 'user', 'pnt3ute5rlip7ge94dbk4pn7sp', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-02-25 02:21:40', '2026-02-25 02:22:01'),
(98, 3, 'user', 'guv3ap1g2amtdadc7ocaekbsca', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-05 03:55:32', '2026-03-05 07:14:23'),
(99, 3, 'user', 'v5i972c22h45j8bb8sr10413k4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-10 07:10:04', '2026-03-10 07:23:02'),
(100, 3, 'user', 'q7i3fc1oaoijb7dvanvlr6g042', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-14 23:57:05', '2026-03-15 06:29:38');

-- --------------------------------------------------------

--
-- Table structure for table `vessels`
--

CREATE TABLE `vessels` (
  `id` int(11) NOT NULL,
  `vessel_name` varchar(255) DEFAULT NULL,
  `vessel_code` varchar(20) NOT NULL,
  `ship_type` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `draft_meters` decimal(10,2) DEFAULT NULL,
  `loa_meters` decimal(10,2) DEFAULT NULL,
  `vessel_df` decimal(10,2) DEFAULT NULL,
  `dwt_tonnage` decimal(10,2) DEFAULT NULL,
  `gt_tonnage` decimal(10,2) DEFAULT NULL,
  `nt_tonnage` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vessels`
--

INSERT INTO `vessels` (`id`, `vessel_name`, `vessel_code`, `ship_type`, `status`, `draft_meters`, `loa_meters`, `vessel_df`, `dwt_tonnage`, `gt_tonnage`, `nt_tonnage`, `created_at`, `updated_at`) VALUES
(1, 'Alkahfi Gentle', 'VSL001', 'Barge', 'active', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-01-08 03:12:03', '2026-03-14 09:10:32'),
(2, 'Alkahfi Care', 'KTSB0001', '', 'active', 0.10, 0.10, 0.10, 0.00, 1.00, 1.00, '2026-01-08 03:12:03', '2026-01-08 03:52:37'),
(3, 'Alkahfi Chief', 'VSL003', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(4, 'Alkahfi Pride', 'VSL004', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(5, 'Blue Petra 2', 'VSL005', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(6, 'Alkahfi Asura', 'VSL006', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(7, 'Marine Success', 'VSL007', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(8, 'Pelican Cheer', 'VSL008', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(9, 'Alkahfi Courage', 'VSL009', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(10, 'Marine Courage', 'VSL010', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(11, 'Surya Halima', 'VSL011', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(12, 'Gen 4 One', 'VSL012', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(13, 'Jati Four', 'VSL013', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(14, 'Tegas Madani', 'VSL014', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(15, 'Alkahfi Grace', 'VSL015', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(16, 'Ph Prestij', 'VSL016', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(17, 'Blue Petra 1', 'VSL017', NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 03:12:03', '2026-03-14 08:58:04'),
(19, 'TEST', 'VSL018', 'Commercial Vessel', 'active', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-03-14 09:03:41', '2026-03-14 09:10:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`agent_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `berths`
--
ALTER TABLE `berths`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `berth_code` (`berth_code`);

--
-- Indexes for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_code` (`company_code`);

--
-- Indexes for table `crew_sign_off_details`
--
ALTER TABLE `crew_sign_off_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `crew_signoff_id` (`crew_signoff_id`);

--
-- Indexes for table `crew_sign_off_requests`
--
ALTER TABLE `crew_sign_off_requests`
  ADD PRIMARY KEY (`crew_signoff_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_crew_sign_off_requests_agent` (`assigned_agent_id`);

--
-- Indexes for table `crew_sign_on_details`
--
ALTER TABLE `crew_sign_on_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `crew_signon_id` (`crew_signon_id`);

--
-- Indexes for table `crew_sign_on_requests`
--
ALTER TABLE `crew_sign_on_requests`
  ADD PRIMARY KEY (`crew_signon_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_crew_sign_on_requests_agent` (`assigned_agent_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document_templates`
--
ALTER TABLE `document_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fuel_water_requests`
--
ALTER TABLE `fuel_water_requests`
  ADD PRIMARY KEY (`fuelwater_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_fuel_water_requests_agent` (`assigned_agent_id`);

--
-- Indexes for table `generated_documents`
--
ALTER TABLE `generated_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request` (`request_id`),
  ADD KEY `idx_job_ticket` (`job_ticket`);

--
-- Indexes for table `light_port_requests`
--
ALTER TABLE `light_port_requests`
  ADD PRIMARY KEY (`lightport_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_assigned_agent_id` (`assigned_agent_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_request_date` (`request_date`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `updated_by_user_id` (`updated_by_user_id`);

--
-- Indexes for table `marine_crew_details`
--
ALTER TABLE `marine_crew_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `marine_id` (`marine_id`);

--
-- Indexes for table `marine_fuel_water_services`
--
ALTER TABLE `marine_fuel_water_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `marine_id` (`marine_id`);

--
-- Indexes for table `marine_general_works`
--
ALTER TABLE `marine_general_works`
  ADD PRIMARY KEY (`id`),
  ADD KEY `marine_id` (`marine_id`);

--
-- Indexes for table `marine_other_services`
--
ALTER TABLE `marine_other_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `marine_id` (`marine_id`);

--
-- Indexes for table `marine_overtime_requests`
--
ALTER TABLE `marine_overtime_requests`
  ADD PRIMARY KEY (`overtime_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `marine_requests`
--
ALTER TABLE `marine_requests`
  ADD PRIMARY KEY (`marine_id`),
  ADD UNIQUE KEY `bod_no` (`bod_no`),
  ADD UNIQUE KEY `job_ticket` (`job_ticket`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_marine_requests_agent` (`assigned_agent_id`),
  ADD KEY `idx_berth_id` (`berth_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_actual_times` (`actual_eta`,`actual_etd`),
  ADD KEY `idx_endorsed_by` (`endorsed_by`),
  ADD KEY `idx_amendment_requested_by` (`amendment_requested_by`);

--
-- Indexes for table `nationalities`
--
ALTER TABLE `nationalities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `numbers_table`
--
ALTER TABLE `numbers_table`
  ADD PRIMARY KEY (`n`);

--
-- Indexes for table `port_clearance_requests`
--
ALTER TABLE `port_clearance_requests`
  ADD PRIMARY KEY (`clearance_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `price_of_services`
--
ALTER TABLE `price_of_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `price_id_unique` (`price_id`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_customer_group` (`customer_group`),
  ADD KEY `idx_effective_date` (`effective_date`),
  ADD KEY `idx_expiry_date` (`expiry_date`);

--
-- Indexes for table `request_documents`
--
ALTER TABLE `request_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request` (`request_id`),
  ADD KEY `idx_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_code_unique` (`user_code`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_session_id` (`session_id`(50)),
  ADD KEY `idx_last_activity` (`last_activity`);

--
-- Indexes for table `vessels`
--
ALTER TABLE `vessels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vessel_code_unique` (`vessel_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `agent_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `berths`
--
ALTER TABLE `berths`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `crew_sign_off_details`
--
ALTER TABLE `crew_sign_off_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=207;

--
-- AUTO_INCREMENT for table `crew_sign_on_details`
--
ALTER TABLE `crew_sign_on_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `document_templates`
--
ALTER TABLE `document_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `generated_documents`
--
ALTER TABLE `generated_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `marine_crew_details`
--
ALTER TABLE `marine_crew_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=264;

--
-- AUTO_INCREMENT for table `marine_fuel_water_services`
--
ALTER TABLE `marine_fuel_water_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `marine_general_works`
--
ALTER TABLE `marine_general_works`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT for table `marine_other_services`
--
ALTER TABLE `marine_other_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `nationalities`
--
ALTER TABLE `nationalities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `price_of_services`
--
ALTER TABLE `price_of_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request_documents`
--
ALTER TABLE `request_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `vessels`
--
ALTER TABLE `vessels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `crew_sign_off_details`
--
ALTER TABLE `crew_sign_off_details`
  ADD CONSTRAINT `fk_crew_sign_off_details_id` FOREIGN KEY (`crew_signoff_id`) REFERENCES `crew_sign_off_requests` (`crew_signoff_id`) ON DELETE CASCADE;

--
-- Constraints for table `crew_sign_on_details`
--
ALTER TABLE `crew_sign_on_details`
  ADD CONSTRAINT `fk_crew_sign_on_details_id` FOREIGN KEY (`crew_signon_id`) REFERENCES `crew_sign_on_requests` (`crew_signon_id`) ON DELETE CASCADE;

--
-- Constraints for table `light_port_requests`
--
ALTER TABLE `light_port_requests`
  ADD CONSTRAINT `light_port_requests_ibfk_1` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `marine_crew_details`
--
ALTER TABLE `marine_crew_details`
  ADD CONSTRAINT `fk_marine_crew_marine_id` FOREIGN KEY (`marine_id`) REFERENCES `marine_requests` (`marine_id`) ON DELETE CASCADE;

--
-- Constraints for table `marine_fuel_water_services`
--
ALTER TABLE `marine_fuel_water_services`
  ADD CONSTRAINT `fk_fuel_water_marine` FOREIGN KEY (`marine_id`) REFERENCES `marine_requests` (`marine_id`) ON DELETE CASCADE;

--
-- Constraints for table `marine_general_works`
--
ALTER TABLE `marine_general_works`
  ADD CONSTRAINT `fk_general_works_marine` FOREIGN KEY (`marine_id`) REFERENCES `marine_requests` (`marine_id`) ON DELETE CASCADE;

--
-- Constraints for table `marine_other_services`
--
ALTER TABLE `marine_other_services`
  ADD CONSTRAINT `fk_other_services_marine` FOREIGN KEY (`marine_id`) REFERENCES `marine_requests` (`marine_id`) ON DELETE CASCADE;

--
-- Constraints for table `marine_overtime_requests`
--
ALTER TABLE `marine_overtime_requests`
  ADD CONSTRAINT `marine_overtime_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `port_clearance_requests`
--
ALTER TABLE `port_clearance_requests`
  ADD CONSTRAINT `port_clearance_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
