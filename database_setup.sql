-- phpMyAdmin SQL Dump
-- Database: `gravetrack_db`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create Database
CREATE DATABASE IF NOT EXISTS `gravetrack_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gravetrack_db`;

-- Table structure for table `users`
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Engineer','Treasurer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default users
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
('testdummy1', 'test@gmail.com', '12345', 'Treasurer'),
('engineer1', 'engineer@gmail.com', '12345', 'Engineer');

-- Table structure for table `plots`
CREATE TABLE `plots` (
  `plot_id` int(11) NOT NULL AUTO_INCREMENT,
  `block` varchar(50) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `lot` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `status` enum('Vacant','Occupied','Reserved') DEFAULT 'Vacant',
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`plot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample plots
INSERT INTO `plots` (`block`, `section`, `lot`, `type`, `status`) VALUES
('A', '1', '1', 'Single', 'Occupied'),
('B', '2', '1', 'Single', 'Occupied'),
('A', '2', '2', 'Apartment', 'Occupied'),
('A', '1', '2', 'Single', 'Vacant'),
('A', '1', '3', 'Single', 'Vacant'),
('B', '1', '1', 'Apartment', 'Vacant'),
('C', '1', '1', 'Mausoleum', 'Vacant');

-- Table structure for table `deceased`
CREATE TABLE `deceased` (
  `deceased_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `date_of_death` date DEFAULT NULL,
  `date_of_burial` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `plot_id` int(11) DEFAULT NULL,
  `burial_type` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  PRIMARY KEY (`deceased_id`),
  KEY `plot_id` (`plot_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `deceased_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`plot_id`) ON DELETE SET NULL,
  CONSTRAINT `deceased_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample deceased
INSERT INTO `deceased` (`full_name`, `date_of_death`, `date_of_burial`, `gender`, `address`, `plot_id`, `burial_type`, `created_by`, `birth_date`) VALUES
('Maria Santos', '2024-01-10', '2024-01-15', 'Female', 'Lipa City, Batangas', 3, 'Family Burial', 1, '2016-05-03'),
('Jose Mendoza', '2023-11-05', '2023-11-10', 'Male', 'Batangas City', 2, 'Family Burial', 1, '2016-05-05'),
('WATATA', '2026-05-14', '2026-05-15', 'Male', '0403, Sitio palico, Bilaran', 1, 'Ground', NULL, '2026-05-08');

-- Trigger to update plot status after burial insert
DELIMITER $$
CREATE TRIGGER `after_burial_insert` AFTER INSERT ON `deceased` FOR EACH ROW
BEGIN
    UPDATE plots
    SET status = 'Occupied'
    WHERE plot_id = NEW.plot_id;
END
$$
DELIMITER ;

-- Table structure for table `contacts`
CREATE TABLE `contacts` (
  `contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `deceased_id` int(11) DEFAULT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`contact_id`),
  KEY `deceased_id` (`deceased_id`),
  CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`deceased_id`) REFERENCES `deceased` (`deceased_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample contacts
INSERT INTO `contacts` (`deceased_id`, `contact_person`, `contact_number`) VALUES
(1, 'Pedro Santos', '09123456789'),
(2, 'Luisa Mendoza', '09987654321'),
(3, 'Niel Francis Benedict', '09566632253');

-- Table structure for table `rentals`
CREATE TABLE `rentals` (
  `rental_id` int(11) NOT NULL AUTO_INCREMENT,
  `deceased_id` int(11) DEFAULT NULL,
  `plot_id` int(11) DEFAULT NULL,
  `rental_start` date DEFAULT NULL,
  `rental_end` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Active','Expired','Paid','Unpaid') DEFAULT 'Unpaid',
  `processed_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`rental_id`),
  KEY `deceased_id` (`deceased_id`),
  KEY `plot_id` (`plot_id`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`deceased_id`) REFERENCES `deceased` (`deceased_id`) ON DELETE SET NULL,
  CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`plot_id`) ON DELETE SET NULL,
  CONSTRAINT `rentals_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample rentals
INSERT INTO `rentals` (`deceased_id`, `plot_id`, `rental_start`, `rental_end`, `amount`, `status`, `processed_by`) VALUES
(1, 1, '2024-01-15', '2025-01-15', 5000.00, 'Unpaid', 1),
(2, 2, '2023-11-10', '2024-11-10', 7000.00, 'Paid', 1);

-- Table structure for table `payments`
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `rental_id` int(11) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Paid','Pending') DEFAULT 'Pending',
  PRIMARY KEY (`payment_id`),
  KEY `rental_id` (`rental_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample payments
INSERT INTO `payments` (`rental_id`, `payment_date`, `amount`, `status`) VALUES
(1, '2024-01-16', 5000.00, 'Paid'),
(2, '2023-11-11', 7000.00, 'Paid');

-- Table structure for table `transactions`
CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `transaction_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `deceased_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `user_id` (`user_id`),
  KEY `fk_transactions_deceased` (`deceased_id`),
  CONSTRAINT `fk_transactions_deceased` FOREIGN KEY (`deceased_id`) REFERENCES `deceased` (`deceased_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample transactions
INSERT INTO `transactions` (`name`, `transaction_date`, `amount`, `type`, `user_id`, `deceased_id`) VALUES
('Maria Santos', '2024-01-16', 5000.00, 'Payment', 1, 1),
('Jose Mendoza', '2023-11-11', 7000.00, 'Payment', 1, 3);

-- View: burial_records_view
CREATE VIEW `burial_records_view` AS
SELECT
    d.full_name,
    d.date_of_death,
    d.date_of_burial,
    d.gender,
    c.contact_person,
    c.contact_number,
    d.address,
    CONCAT(p.block, ' - ', p.section, ' - ', p.lot) AS plot_location,
    d.burial_type
FROM deceased d
LEFT JOIN contacts c ON d.deceased_id = c.deceased_id
LEFT JOIN plots p ON d.plot_id = p.plot_id;

-- View: plot_phase_view
CREATE VIEW `plot_phase_view` AS
SELECT
    plot_id,
    block,
    section,
    lot,
    type,
    status,
    date_added,
    CASE
        WHEN block REGEXP '^[A-I]' THEN 'Phase 1'
        WHEN block REGEXP '^[T-Z]' THEN 'Phase 2'
        WHEN block = 'AA' THEN 'Phase 3'
        ELSE 'Unassigned'
    END AS phase
FROM plots;

-- View: transaction_summary_view
CREATE VIEW `transaction_summary_view` AS
SELECT
    t.transaction_id,
    d.full_name AS `Deceased Name`,
    CONCAT(p.block, ' - ', p.section, ' - ', p.lot) AS `Plot Location`,
    t.transaction_date AS `Date of Transaction`,
    c.contact_person AS `Contact Person`,
    c.contact_number AS `Contact Number`,
    t.amount AS `Amount`,
    CASE
        WHEN pay.status = 'Paid' THEN 'Paid'
        WHEN pay.status = 'Pending' THEN 'Pending'
        WHEN pay.payment_id IS NULL AND r.rental_end < CURDATE() THEN 'Overdue'
        WHEN pay.payment_id IS NULL THEN 'Unpaid'
        ELSE 'Unpaid'
    END AS `Status`
FROM transactions t
LEFT JOIN deceased d ON t.deceased_id = d.deceased_id
LEFT JOIN plots p ON d.plot_id = p.plot_id
LEFT JOIN contacts c ON d.deceased_id = c.deceased_id
LEFT JOIN rentals r ON d.deceased_id = r.deceased_id
LEFT JOIN payments pay ON r.rental_id = pay.rental_id;

COMMIT;
