-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 24, 2025 at 08:32 AM
-- Server version: 9.1.0
-- PHP Version: 8.0.30

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
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `email`, `is_active`, `created_at`) VALUES
(2, 'admin', '$2y$10$l/vhCQ96vkQgv9xt6DJgj.pLfDCkXOHdgg9a4QeQ.bHbMEHIVaSBy', 'admin_account@ktsb.com', 1, '2025-12-02 05:55:21');

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

DROP TABLE IF EXISTS `agents`;
CREATE TABLE IF NOT EXISTS `agents` (
  `agent_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `customer_code` varchar(50) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`agent_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(11, 'afiq', '$2y$10$uF7YhTbvRF5oAhYtn2efV.vnzB0gy71ku2ZsOQRpXg8rQdncxwwvO', 'Afiq Bin Abdullah', 'afiq@gmail.com', '123456789', 'PETRONAS', 'KTSB', 3, 'active', '2025-12-04 07:55:43', NULL),
(12, 'iffah123', '$2y$10$18C.lnxeLWVI5FgzQtP6XeOIx9A1JITDbT5HGceiZnga/RsW87Bo6', 'Nur Iffah', 'iffah123@gmail.com', '+609545091', 'PETRONAS', 'KTSB', 6, 'active', '2025-12-04 07:56:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
CREATE TABLE IF NOT EXISTS `companies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(100) NOT NULL,
  `company_code` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_code` (`company_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

DROP TABLE IF EXISTS `crew_sign_off_details`;
CREATE TABLE IF NOT EXISTS `crew_sign_off_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `crew_signoff_id` varchar(20) NOT NULL,
  `crew_name` varchar(100) NOT NULL,
  `ic_passport` varchar(50) NOT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `crew_signoff_id` (`crew_signoff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

DROP TABLE IF EXISTS `crew_sign_off_requests`;
CREATE TABLE IF NOT EXISTS `crew_sign_off_requests` (
  `crew_signoff_id` varchar(20) NOT NULL,
  `user_id` int NOT NULL,
  `assigned_agent_id` int DEFAULT NULL,
  `vessel_name` varchar(100) NOT NULL,
  `po_number` varchar(50) DEFAULT NULL,
  `request_date` date NOT NULL,
  `request_time` time NOT NULL,
  `remarks` text,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `takeaway_quantity` int DEFAULT '0',
  `baggage_handling_quantity` int DEFAULT '0',
  PRIMARY KEY (`crew_signoff_id`),
  KEY `user_id` (`user_id`),
  KEY `fk_crew_sign_off_requests_agent` (`assigned_agent_id`)
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

DROP TABLE IF EXISTS `crew_sign_on_details`;
CREATE TABLE IF NOT EXISTS `crew_sign_on_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `crew_signon_id` varchar(20) NOT NULL,
  `crew_name` varchar(100) NOT NULL,
  `ic_passport` varchar(50) NOT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `crew_signon_id` (`crew_signon_id`)
) ENGINE=InnoDB AUTO_INCREMENT=196 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

DROP TABLE IF EXISTS `crew_sign_on_requests`;
CREATE TABLE IF NOT EXISTS `crew_sign_on_requests` (
  `crew_signon_id` varchar(20) NOT NULL,
  `user_id` int NOT NULL,
  `assigned_agent_id` int DEFAULT NULL,
  `vessel_name` varchar(100) NOT NULL,
  `po_number` varchar(50) DEFAULT NULL,
  `request_date` date NOT NULL,
  `request_time` time NOT NULL,
  `remarks` text,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `packed_meals_quantity` int DEFAULT '0',
  `snack_pack_quantity` int DEFAULT '0',
  `baggage_details` text,
  `bag_tagging_quantity` int DEFAULT '0',
  PRIMARY KEY (`crew_signon_id`),
  KEY `user_id` (`user_id`),
  KEY `fk_crew_sign_on_requests_agent` (`assigned_agent_id`)
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
-- Table structure for table `fuel_water_requests`
--

DROP TABLE IF EXISTS `fuel_water_requests`;
CREATE TABLE IF NOT EXISTS `fuel_water_requests` (
  `fuelwater_id` varchar(20) NOT NULL,
  `user_id` int NOT NULL,
  `assigned_agent_id` int DEFAULT NULL,
  `vessel_name` varchar(100) NOT NULL,
  `po_number` varchar(50) DEFAULT NULL,
  `request_date` date NOT NULL,
  `request_time` time NOT NULL,
  `fuel_quantity` decimal(10,2) DEFAULT NULL,
  `fuel_booking_time` time DEFAULT NULL,
  `fuel_remarks` text,
  `water_quantity` decimal(10,2) DEFAULT NULL,
  `water_booking_time` time DEFAULT NULL,
  `water_remarks` text,
  `remarks` text,
  `status` enum('pending','approved','rejected','supplied') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fuelwater_id`),
  KEY `user_id` (`user_id`),
  KEY `fk_fuel_water_requests_agent` (`assigned_agent_id`)
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
-- Table structure for table `light_port_requests`
--

DROP TABLE IF EXISTS `light_port_requests`;
CREATE TABLE IF NOT EXISTS `light_port_requests` (
  `lightport_id` varchar(20) NOT NULL,
  `user_id` int NOT NULL,
  `vessel_name` varchar(255) NOT NULL,
  `request_date` date NOT NULL,
  `services` text NOT NULL,
  `volume_unit` decimal(10,2) NOT NULL,
  `assigned_agent_id` int DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`lightport_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_assigned_agent_id` (`assigned_agent_id`),
  KEY `idx_status` (`status`),
  KEY `idx_request_date` (`request_date`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `light_port_requests`
--

INSERT INTO `light_port_requests` (`lightport_id`, `user_id`, `vessel_name`, `request_date`, `services`, `volume_unit`, `assigned_agent_id`, `status`, `created_at`, `updated_at`) VALUES
('LP25000001', 3, 'Alkahfi Care', '2026-01-08', 'TEST', 20.00, 2, 'pending', '2025-12-02 09:16:50', '2025-12-02 09:16:50'),
('LP25000002', 6, 'Pelican Cheer', '2026-01-01', 'test', 2.00, 8, 'pending', '2025-12-04 07:51:31', '2025-12-04 07:51:31');

-- --------------------------------------------------------

--
-- Table structure for table `marine_crew_details`
--

DROP TABLE IF EXISTS `marine_crew_details`;
CREATE TABLE IF NOT EXISTS `marine_crew_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `marine_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `passport_ic` varchar(50) NOT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `expiry` date DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marine_id` (`marine_id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marine_crew_details`
--

INSERT INTO `marine_crew_details` (`id`, `marine_id`, `name`, `passport_ic`, `nationality`, `expiry`, `mobile`, `company`, `destination`) VALUES
(1, 'B25000001', 'Muhammad Abu Bin Ali', '970405110521', 'Malay', '2030-04-23', '0112345678', 'PETRONAS', 'LABUAN'),
(2, 'B25000001', 'Muhammad Salman Bin Salmin', '900204110561', 'Malay', '2027-06-15', '0123456789', 'PETRONAS', 'LABUAN'),
(3, 'B25000001', 'Muhammad Amir Aiman Bin Salman', '980303114531', 'Malay', '2030-12-15', '0145678912', 'HALLIBURTON', 'LABUAN'),
(4, 'B25000001', 'Mohammad Aiman Hakim Bin Fairuz', '990903032341', 'Malay', '2030-12-13', '0156789123', 'HALLIBURTON', 'LABUAN'),
(5, 'B25000004', 'Ahmad bin Razali', '850101-11-5023', 'Malaysian', NULL, '012-3456789', 'Petra Energy', 'Platform Bekok A'),
(6, 'B25000004', 'Tan Wei Ling', '900520-10-6622', 'Malaysian', NULL, '019-8765432', 'Dialog Group', 'Vessel SK Pilot'),
(7, 'B25000004', 'Muthu a/l Kumar', '921215-08-7733', 'Malaysian', NULL, '017-2233445', 'Sapura Energy', 'Platform Tapis B'),
(8, 'B25000004', 'John Smith', '558822113', 'British', '2028-12-01', '011-55566677', 'Shell', 'Rig 5'),
(9, 'B25000004', 'Supardi Nasir', 'B9876543', 'Indonesian', '2026-05-01', '011-33445566', 'Petra Energy', 'Vessel Setia'),
(10, 'B25000004', 'Nguyen Van Hung', 'C5544332', 'Vietnamese', '2027-08-01', '018-9988776', 'Barakah Offshore', 'Platform Pulai'),
(11, 'B25000004', 'Jose Rizal', 'P44556677', 'Filipino', '2025-11-01', '016-4455667', 'Sapura Energy', 'Vessel SK Deep'),
(12, 'B25000004', 'Li Wei', 'E99887766', 'Chinese', '2029-03-01', '014-2233112', 'COSL', 'Rig 2'),
(13, 'B25000004', 'Siti Sarah', 'A11223344', 'Malaysian', '2030-07-01', '013-5556666', 'Petronas', 'Platform Dulang'),
(14, 'B25000004', 'Robert Doe', 'US98765432', 'American', '2026-01-01', '010-9988112', 'ExxonMobil', 'Platform Guntong'),
(15, 'B25000004', 'Rajesh Patel', 'Z1234567', 'Indian', '2027-09-01', '015-6677889', 'Bumi Armada', 'Vessel Armada 1'),
(16, 'B25000005', 'Ahmad bin Razali', '850101-11-5023', 'Malaysian', NULL, '012-3456789', 'Petra Energy', 'Platform Bekok A'),
(17, 'B25000005', 'Tan Wei Ling', '900520-10-6622', 'Malaysian', NULL, '019-8765432', 'Dialog Group', 'Vessel SK Pilot'),
(18, 'B25000005', 'Muthu a/l Kumar', '921215-08-7733', 'Malaysian', NULL, '017-2233445', 'Sapura Energy', 'Platform Tapis B'),
(19, 'B25000005', 'John Smith', '558822113', 'British', '2028-12-01', '011-55566677', 'Shell', 'Rig 5'),
(20, 'B25000005', 'Supardi Nasir', 'B9876543', 'Indonesian', '2026-05-01', '011-33445566', 'Petra Energy', 'Vessel Setia'),
(21, 'B25000005', 'Nguyen Van Hung', 'C5544332', 'Vietnamese', '2027-08-01', '018-9988776', 'Barakah Offshore', 'Platform Pulai'),
(22, 'B25000005', 'Jose Rizal', 'P44556677', 'Filipino', '2025-11-01', '016-4455667', 'Sapura Energy', 'Vessel SK Deep'),
(23, 'B25000005', 'Li Wei', 'E99887766', 'Chinese', '2029-03-01', '014-2233112', 'COSL', 'Rig 2'),
(24, 'B25000005', 'Siti Sarah', 'A11223344', 'Malaysian', '2030-07-01', '013-5556666', 'Petronas', 'Platform Dulang'),
(25, 'B25000005', 'Robert Doe', 'US98765432', 'American', '2026-01-01', '010-9988112', 'ExxonMobil', 'Platform Guntong'),
(26, 'B25000005', 'Rajesh Patel', 'Z1234567', 'Indian', '2027-09-01', '015-6677889', 'Bumi Armada', 'Vessel Armada 1'),
(27, 'B25000005', 'Ahmed Al-Fayed', 'E1239876', 'Emirati', '2026-06-01', '019-2233445', 'Mubadala', 'Platform Raya'),
(28, 'B25000006', 'Muhammad Shahrul Bin Ali', '970405110521', 'Malay', '2030-04-23', '0112345678', 'PETRONAS', 'LABUAN'),
(29, 'B25000006', 'Muhammad Salmun Bin Ahmad', '900204110561', 'Malay', '2027-06-15', '0123456789', 'PETRONAS', 'LABUAN'),
(30, 'B25000006', 'Muhammad Daniel Aiman Bin Salman', '980303114531', 'Malay', '2030-12-15', '0145678912', 'HALLIBURTON', 'LABUAN'),
(31, 'B25000006', 'Mohammad Aiman Fauzan Bin Fairuz', '990111032341', 'Malay', '2030-12-17', '0156789123', 'HALLIBURTON', 'LABUAN'),
(32, 'B25000006', 'Muhammad Asyraf Bin Ali', '990501112331', 'Malay', '2030-11-14', '0112345678', 'HALLIBURTON', 'SABAH'),
(33, 'B25000006', 'Muhammad Salahuddin Bin Salmin', '990810110121', 'Malay', '2030-12-13', '0123345670', 'SOBENA', 'SARAWAK'),
(34, 'B25000007', 'Ahmad bin Razali', '850101-11-5023', 'Malaysian', NULL, '012-3456789', 'Petra Energy', 'Platform Bekok A'),
(35, 'B25000007', 'Tan Wei Ling', '900520-10-6622', 'Malaysian', NULL, '019-8765432', 'Dialog Group', 'Vessel SK Pilot'),
(36, 'B25000007', 'Muthu a/l Kumar', '921215-08-7733', 'Malaysian', NULL, '017-2233445', 'Sapura Energy', 'Platform Tapis B'),
(37, 'B25000007', 'John Smith', '558822113', 'British', '2028-12-01', '011-55566677', 'Shell', 'Rig 5'),
(38, 'B25000007', 'Supardi Nasir', 'B9876543', 'Indonesian', '2026-05-01', '011-33445566', 'Petra Energy', 'Vessel Setia'),
(39, 'B25000007', 'Nguyen Van Hung', 'C5544332', 'Vietnamese', '2027-08-01', '018-9988776', 'Barakah Offshore', 'Platform Pulai'),
(40, 'B25000007', 'Jose Rizal', 'P44556677', 'Filipino', '2025-11-01', '016-4455667', 'Sapura Energy', 'Vessel SK Deep'),
(41, 'B25000007', 'Li Wei', 'E99887766', 'Chinese', '2029-03-01', '014-2233112', 'COSL', 'Rig 2'),
(42, 'B25000007', 'Rajesh Patel', 'Z1234567', 'Indian', '2027-09-01', '015-6677889', 'Bumi Armada', 'Vessel Armada 1'),
(43, 'B25000007', 'Ahmed Al-Fayed', 'E1239876', 'Emirati', '2026-06-01', '019-2233445', 'Mubadala', 'Platform Raya');

-- --------------------------------------------------------

--
-- Table structure for table `marine_requests`
--

DROP TABLE IF EXISTS `marine_requests`;
CREATE TABLE IF NOT EXISTS `marine_requests` (
  `marine_id` varchar(20) NOT NULL,
  `user_id` int NOT NULL,
  `assigned_agent_id` int DEFAULT NULL,
  `vessel_name` varchar(100) NOT NULL,
  `po_number` varchar(50) DEFAULT NULL,
  `eta` datetime NOT NULL,
  `etd` datetime NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `remarks` text,
  `crew_transfer_type` enum('sign_on','sign_off') DEFAULT NULL,
  `fuel_water_data` json DEFAULT NULL,
  `general_works_data` json DEFAULT NULL,
  `bod_no` varchar(20) DEFAULT NULL,
  `log_no` varchar(20) DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `other_services_data` longtext,
  PRIMARY KEY (`marine_id`),
  UNIQUE KEY `bod_no` (`bod_no`),
  KEY `user_id` (`user_id`),
  KEY `fk_marine_requests_agent` (`assigned_agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marine_requests`
--

INSERT INTO `marine_requests` (`marine_id`, `user_id`, `assigned_agent_id`, `vessel_name`, `po_number`, `eta`, `etd`, `company`, `remarks`, `crew_transfer_type`, `fuel_water_data`, `general_works_data`, `bod_no`, `log_no`, `status`, `created_at`, `updated_at`, `other_services_data`) VALUES
('B25000001', 3, 2, 'Gen 4 One', 'PO8978', '2025-12-04 05:00:00', '2025-12-05 05:00:00', 'KTSB', '', 'sign_on', '[{\"type\": \"fuel\", \"remarks\": null, \"quantity\": \"10\", \"bookingTime\": \"21:00\"}, {\"type\": \"water\", \"remarks\": null, \"quantity\": \"10\", \"bookingTime\": \"21:35\"}]', '[{\"work\": \"Discharge\", \"remarks\": null}, {\"work\": \"Maintenance\", \"remarks\": null}, {\"work\": \"Unmooring\", \"remarks\": null}]', 'B25000001', NULL, 'pending', '2025-12-02 09:03:22', '2025-12-02 09:03:22', '[{\"service\":\"packed_meals\",\"quantity\":5},{\"service\":\"snack_pack\",\"quantity\":5},{\"service\":\"baggage\",\"details\":\"5\"},{\"service\":\"bag_tagging\",\"quantity\":5}]'),
('B25000003', 3, 2, 'Blue Petra 2', '122', '2025-12-04 12:00:00', '2025-12-04 13:00:00', 'Kuala Terengganu Support Base', '', NULL, NULL, NULL, 'B25000003', NULL, 'pending', '2025-12-04 03:58:55', '2025-12-04 03:58:55', NULL),
('B25000004', 3, 2, 'Blue Petra 2', '1222', '2025-12-04 16:00:00', '2025-12-04 17:00:00', 'Kuala Terengganu Support Base', '', 'sign_off', NULL, NULL, 'B25000004', NULL, 'pending', '2025-12-04 04:26:40', '2025-12-04 04:26:40', NULL),
('B25000005', 3, 5, 'Blue Petra 2', '122', '2025-12-04 13:00:00', '2025-12-04 14:00:00', 'Kuala Terengganu Support Base', '', 'sign_on', NULL, NULL, 'B25000005', NULL, 'pending', '2025-12-04 04:44:44', '2025-12-04 04:44:44', '[{\"service\":\"packed_meals\",\"quantity\":12}]'),
('B25000006', 6, 10, 'Jati Four', 'PO3456', '2025-12-20 12:00:00', '2025-12-21 05:00:00', 'Kuala Terengganu Support Base', '', 'sign_off', '[{\"type\": \"fuel\", \"remarks\": null, \"quantity\": \"2\", \"bookingTime\": \"15:19\"}]', '[{\"work\": \"Crew Change\", \"remarks\": null}]', 'B25000006', NULL, 'pending', '2025-12-04 07:18:10', '2025-12-04 07:18:10', '[{\"service\":\"takeaway\",\"quantity\":6}]'),
('B25000007', 3, 7, 'Blue Petra 2', '12233', '2025-12-09 02:00:00', '2025-12-09 03:00:00', 'Kuala Terengganu Support Base', 'test for berth', 'sign_on', '[{\"type\": \"fuel\", \"remarks\": \"test for fuel\", \"quantity\": \"122\", \"bookingTime\": \"00:17\"}, {\"type\": \"water\", \"remarks\": \"test for water\", \"quantity\": \"122\", \"bookingTime\": \"11:18\"}]', '[{\"work\": \"Discharge\", \"remarks\": \"test discharge\"}, {\"work\": \"Loading\", \"remarks\": null}, {\"work\": \"Inspection\", \"remarks\": null}]', 'B25000007', NULL, 'pending', '2025-12-08 03:40:26', '2025-12-08 03:40:26', '[{\"service\":\"packed_meals\",\"quantity\":12},{\"service\":\"snack_pack\",\"quantity\":12}]'),
('B2500001', 3, NULL, 'Test Vessel 907', 'PO-TEST-479', '2025-12-03 13:02:36', '2025-12-05 13:02:36', 'Kuala Terengganu Support Base', 'Test Remark', NULL, NULL, NULL, 'B2500001', NULL, 'pending', '2025-12-02 13:02:36', '2025-12-02 13:02:36', NULL),
('B2500002', 3, NULL, 'Test Vessel Marine', 'PO-TEST-M', '2025-12-02 13:04:16', '2025-12-02 13:04:16', 'Kuala Terengganu Support Base', 'Test Remark', NULL, NULL, NULL, 'B2500002', NULL, 'pending', '2025-12-02 13:04:16', '2025-12-02 13:04:16', NULL),
('TEST6194', 3, NULL, 'Test Vessel', 'PO-TEST', '2025-12-02 13:05:39', '2025-12-02 13:05:39', 'Kuala Terengganu Support Base', 'Test Remark', NULL, NULL, NULL, 'TEST6194', NULL, 'pending', '2025-12-02 13:05:39', '2025-12-02 13:05:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `nationalities`
--

DROP TABLE IF EXISTS `nationalities`;
CREATE TABLE IF NOT EXISTS `nationalities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 'system', 'System notification system initialized.', '#', 1, '2025-12-07 01:36:23'),
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
(23, 'marine', 'New Marine Request from ktsb', 'marine-detail.php?id=B25000007', 0, '2025-12-08 03:40:26');

-- --------------------------------------------------------

--
-- Table structure for table `request_documents`
--

DROP TABLE IF EXISTS `request_documents`;
CREATE TABLE IF NOT EXISTS `request_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` varchar(20) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int NOT NULL,
  `uploader_type` enum('user','agent','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_request` (`request_id`),
  KEY `idx_uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(64, 'B25000007', 'STUDEN JOINING REPORT.pdf', 'uploads/Marine/B25000007/STUDEN JOINING REPORT.pdf', 3, 'user', '2025-12-11 03:36:50');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
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

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `customer_code` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('company','user','admin') DEFAULT 'user',
  `phone_number` varchar(20) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `company_name`, `customer_code`, `full_name`, `email`, `role`, `phone_number`, `created_by`, `status`, `created_at`, `last_login`) VALUES
(3, 'ktsb', '$2y$10$0R5OfV8PS5cfuPeP4tvM/.bABeiJBPOp81X9TkwBwdB/kTuH9GxAC', 'Kuala Terengganu Support Base', 'KTSB', NULL, 'admin@ktsb.com', 'user', NULL, NULL, 'active', '2025-12-02 06:01:21', '2025-12-24 08:30:01'),
(5, 'samad', '$2y$10$pCe3vhDs/rrYWclwU06LjOJmNC7l6vf2.KBZlJhkVvZz3TZKmsdN2', 'Kuala Terengganu Support Base', 'KTSB', NULL, 'samad@gmail.com', 'user', '123456789', 2, 'active', '2025-12-03 04:15:04', '2025-12-03 04:16:14'),
(6, 'ain', '$2y$10$NHGhBkXq8VqhUvEyXBPrO.5G0fEMgUMk2uHWCgVxdrpON0DNXraDO', 'Kuala Terengganu Support Base', 'KTSB', NULL, 'ain@gmail.com', 'user', '123456789', 2, 'active', '2025-12-04 06:34:46', '2025-12-07 06:50:35');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `user_type` enum('user','agent','admin') NOT NULL DEFAULT 'user',
  `session_id` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_id` (`session_id`(50)),
  KEY `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `user_type`, `session_id`, `ip_address`, `user_agent`, `created_at`, `last_activity`) VALUES
(15, 5, 'user', '41vadv6l4pm0heqbht0p1u7op0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 04:16:14', '2025-12-03 09:32:09'),
(19, 6, 'user', 'mesfqcv2jgm6is5hdqkrc909pt', '172.31.7.86', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-04 06:45:41', '2025-12-04 08:03:51'),
(20, 6, 'user', '65ekaln80q4gtqdv0ufn8bk6a8', '172.31.7.86', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-04 08:31:39', '2025-12-04 08:31:52'),
(27, 6, 'user', 'ma9tf524p9u6gtpdc3aclno4qb', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 06:50:35', '2025-12-07 06:50:41'),
(41, 3, 'user', '6mmi0pkedrlmev4mjsaok7m4tu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-08 09:12:54', '2025-12-08 09:28:39'),
(42, 3, 'user', 'qph5i1g2uqg8m9am0voe8ecp1k', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 02:32:28', '2025-12-09 02:47:00'),
(43, 3, 'user', '0affiu66i4vavnv40lu5478o53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 04:38:34', '2025-12-09 09:31:09'),
(44, 3, 'user', 'i3v3gqssumj54v23p9dbgc6b88', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-10 07:18:28', '2025-12-10 09:20:08'),
(46, 2, 'user', 't8ksp7eas1g621si7act173ks4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-11 08:00:10', '2025-12-11 08:00:19'),
(47, 3, 'user', 'etqtgkim48sm2eco1c08n5mpe5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 00:36:38', '2025-12-14 01:48:15'),
(48, 3, 'user', 'vhol97p1556lthodmfdtnu6p5s', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 07:10:13', '2025-12-17 07:17:19'),
(49, 3, 'user', 't7oeruqm0fkd1f8kpi1fvtdcsj', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 01:45:23', '2025-12-21 01:45:23'),
(50, 3, 'user', '4eg1fe2j6ulae3debdvgcccn6r', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 01:14:45', '2025-12-22 01:14:45'),
(51, 3, 'user', 'ivsc61d3sd9njctghp0kraskhm', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 01:15:32', '2025-12-22 04:54:46'),
(53, 3, 'user', '7aeiu70nakhe8vqv263hqqfshq', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 08:30:01', '2025-12-24 08:30:02');

-- --------------------------------------------------------

--
-- Table structure for table `vessels`
--

DROP TABLE IF EXISTS `vessels`;
CREATE TABLE IF NOT EXISTS `vessels` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vessels`
--

INSERT INTO `vessels` (`id`, `name`) VALUES
(1, 'Alkahfi Gentle'),
(2, 'Alkahfi Care'),
(3, 'Alkahfi Chief'),
(4, 'Alkahfi Pride'),
(5, 'Blue Petra 2'),
(6, 'Alkahfi Asura'),
(7, 'Marine Success'),
(8, 'Pelican Cheer'),
(9, 'Alkahfi Courage'),
(10, 'Marine Courage'),
(11, 'Surya Halima'),
(12, 'Gen 4 One'),
(13, 'Jati Four'),
(14, 'Tegas Madani'),
(15, 'Alkahfi Grace'),
(16, 'Ph Prestij'),
(17, 'Blue Petra 1');

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
-- Constraints for table `marine_crew_details`
--
ALTER TABLE `marine_crew_details`
  ADD CONSTRAINT `fk_marine_crew_marine_id` FOREIGN KEY (`marine_id`) REFERENCES `marine_requests` (`marine_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
