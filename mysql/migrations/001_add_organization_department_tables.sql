-- Migration: Add Organization and Department Tables
-- This migration adds support for organization and department management

-- Create organization table
CREATE TABLE IF NOT EXISTS `organization` (
  `org_ID` int(11) NOT NULL AUTO_INCREMENT,
  `org_name` text NOT NULL,
  `org_code` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`org_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Create department table
CREATE TABLE IF NOT EXISTS `department` (
  `dept_ID` int(11) NOT NULL AUTO_INCREMENT,
  `dept_name` varchar(150) NOT NULL,
  `dept_code` varchar(50) DEFAULT NULL,
  `org_ID` int(11) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  PRIMARY KEY (`dept_ID`),
  FOREIGN KEY (`org_ID`) REFERENCES `organization`(`org_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Add org_ID and dept_ID columns to user table if they don't exist
ALTER TABLE `user` ADD COLUMN `org_ID` int(11) DEFAULT NULL AFTER `user_handphone_no`;
ALTER TABLE `user` ADD COLUMN `dept_ID` int(11) DEFAULT NULL AFTER `org_ID`;

-- Insert sample organizations
INSERT IGNORE INTO `organization` (`org_ID`, `org_name`, `org_code`, `status`) VALUES
(1, 'University Malaya', 'UM', 'Active'),
(2, 'Universiti Sains Malaysia', 'USM', 'Active'),
(3, 'Universiti Teknologi MARA', 'UiTM', 'Active'),
(4, 'Universiti Kebangsaan Malaysia', 'UKM', 'Active'),
(6, 'Telekom Malaysia', 'TM', 'Active'),
(7, 'Touch N Go', 'TNG', 'Active');

-- Insert sample departments
INSERT IGNORE INTO `department` (`dept_ID`, `dept_name`, `dept_code`, `org_ID`, `status`) VALUES
(1, 'Human Resource', 'HR', 3, 'Active'),
(2, 'Jabatan Bendahari', 'JB', 3, 'Active'),
(3, 'Information Technology', 'IT', 2, 'Active'),
(4, 'Faculty of Chemical Engineering', 'FKK', 3, 'Active'),
(5, 'Information Technology', 'IT', 6, 'Active'),
(6, 'Faculty of Science Computer and Mathematics', 'FSKM', 3, 'Active'),
(7, 'Information Technology', 'IT', 7, 'Active');
