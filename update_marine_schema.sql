-- Update marine_requests table to match the new schema
-- Remove JSON columns and add missing columns

ALTER TABLE `marine_requests`
DROP COLUMN `fuel_water_data`,
DROP COLUMN `general_works_data`,
DROP COLUMN `other_services_data`,
ADD COLUMN `actual_eta` datetime DEFAULT NULL,
ADD COLUMN `actual_etd` datetime DEFAULT NULL,
ADD COLUMN `berth_id` varchar(10) DEFAULT NULL,
ADD COLUMN `endorsed_at` timestamp NULL DEFAULT NULL,
ADD COLUMN `cancelled_at` timestamp NULL DEFAULT NULL,
ADD COLUMN `cancelled_by` int(11) DEFAULT NULL,
ADD COLUMN `amendment_requested_at` timestamp NULL DEFAULT NULL,
ADD COLUMN `amendment_notes` text DEFAULT NULL,
ADD COLUMN `berth_assigned_date` date DEFAULT NULL,
ADD COLUMN `berth_assigned_at` datetime DEFAULT NULL;

-- Update indexes to match new schema
ALTER TABLE `marine_requests`
ADD KEY `idx_berth_id` (`berth_id`),
ADD KEY `idx_status` (`status`),
ADD KEY `idx_created_at` (`created_at`),
ADD KEY `idx_actual_times` (`actual_eta`,`actual_etd`);

-- Ensure marine_other_services table exists and has correct structure
CREATE TABLE IF NOT EXISTS `marine_other_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marine_id` varchar(20) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `marine_id` (`marine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ensure marine_fuel_water_services table exists and has correct structure
CREATE TABLE IF NOT EXISTS `marine_fuel_water_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marine_id` varchar(20) NOT NULL,
  `service_type` enum('fuel','water') NOT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `booking_time` time DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `marine_id` (`marine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ensure marine_general_works table exists and has correct structure
CREATE TABLE IF NOT EXISTS `marine_general_works` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marine_id` varchar(20) NOT NULL,
  `work_type` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `marine_id` (`marine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add foreign key constraints
ALTER TABLE `marine_other_services`
ADD CONSTRAINT `fk_other_services_marine` FOREIGN KEY (`marine_id`) REFERENCES `marine_requests` (`marine_id`) ON DELETE CASCADE;

ALTER TABLE `marine_fuel_water_services`
ADD CONSTRAINT `fk_fuel_water_marine` FOREIGN KEY (`marine_id`) REFERENCES `marine_requests` (`marine_id`) ON DELETE CASCADE;

ALTER TABLE `marine_general_works`
ADD CONSTRAINT `fk_general_works_marine` FOREIGN KEY (`marine_id`) REFERENCES `marine_requests` (`marine_id`) ON DELETE CASCADE;
