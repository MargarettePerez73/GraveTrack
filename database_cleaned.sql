-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2026 at 10:02 AM
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
-- Database: `gravetrack_db`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `burial_records_view`
-- (See below for the actual view)
--
CREATE TABLE `burial_records_view` (
`full_name` varchar(150)
,`date_of_death` date
,`date_of_burial` date
,`gender` enum('Male','Female','Other')
,`contact_person` varchar(150)
,`contact_number` varchar(20)
,`address` text
,`plot_location` varchar(156)
,`burial_type` varchar(50)
);

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `contact_id` int(11) NOT NULL,
  `deceased_id` int(11) DEFAULT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deceased`
--

CREATE TABLE `deceased` (
  `deceased_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `date_of_death` date DEFAULT NULL,
  `date_of_burial` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `plot_id` int(11) DEFAULT NULL,
  `burial_type` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `birth_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `deceased`
--
DELIMITER $$
CREATE TRIGGER `after_burial_insert` AFTER INSERT ON `deceased` FOR EACH ROW BEGIN
    UPDATE plots
    SET status = 'Occupied'
    WHERE plot_id = NEW.plot_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `rental_id` int(11) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Paid','Pending') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plots`
--

CREATE TABLE `plots` (
  `plot_id` int(11) NOT NULL,
  `block` varchar(50) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `lot` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `status` enum('Vacant','Occupied','Reserved') DEFAULT 'Vacant',
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `plot_phase_view`
-- (See below for the actual view)
--
CREATE TABLE `plot_phase_view` (
`plot_id` int(11)
,`block` varchar(50)
,`section` varchar(50)
,`lot` varchar(50)
,`type` varchar(50)
,`status` enum('Vacant','Occupied','Reserved')
,`date_added` timestamp
,`phase` varchar(10)
);

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `rental_id` int(11) NOT NULL,
  `deceased_id` int(11) DEFAULT NULL,
  `plot_id` int(11) DEFAULT NULL,
  `rental_start` date DEFAULT NULL,
  `rental_end` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Active','Expired','Paid','Unpaid') DEFAULT 'Unpaid',
  `processed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `transaction_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `deceased_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `transaction_summary_view`
-- (See below for the actual view)
--
CREATE TABLE `transaction_summary_view` (
`transaction_id` int(11)
,`Deceased Name` varchar(150)
,`Plot Location` varchar(156)
,`Date of Transaction` date
,`Contact Person` varchar(150)
,`Contact Number` varchar(20)
,`Amount` decimal(10,2)
,`Status` varchar(7)
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Engineer','Treasurer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(3, 'testdummy1', 'test@gmail.com', '12345', 'Treasurer', '2026-05-05 08:01:50'),
(4, 'engineer1', 'engineer@gmail.com', '12345', 'Engineer', '2026-05-05 08:01:50');

-- --------------------------------------------------------

--
-- Structure for view `burial_records_view`
--
DROP TABLE IF EXISTS `burial_records_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `burial_records_view`  AS SELECT `d`.`full_name` AS `full_name`, `d`.`date_of_death` AS `date_of_death`, `d`.`date_of_burial` AS `date_of_burial`, `d`.`gender` AS `gender`, `c`.`contact_person` AS `contact_person`, `c`.`contact_number` AS `contact_number`, `d`.`address` AS `address`, concat(`p`.`block`,' - ',`p`.`section`,' - ',`p`.`lot`) AS `plot_location`, `d`.`burial_type` AS `burial_type` FROM ((`deceased` `d` left join `contacts` `c` on(`d`.`deceased_id` = `c`.`deceased_id`)) left join `plots` `p` on(`d`.`plot_id` = `p`.`plot_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `plot_phase_view`
--
DROP TABLE IF EXISTS `plot_phase_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `plot_phase_view`  AS SELECT `p`.`plot_id` AS `plot_id`, `p`.`block` AS `block`, `p`.`section` AS `section`, `p`.`lot` AS `lot`, `p`.`type` AS `type`, `p`.`status` AS `status`, `p`.`date_added` AS `date_added`, CASE WHEN `p`.`block` regexp '^[A-I]$' THEN 'Phase 1' WHEN `p`.`block` regexp '^[T-Z]$' THEN 'Phase 2' WHEN `p`.`block` = 'AA' THEN 'Phase 3' ELSE 'Unassigned' END AS `phase` FROM `plots` AS `p` ;

-- --------------------------------------------------------

--
-- Structure for view `transaction_summary_view`
--
DROP TABLE IF EXISTS `transaction_summary_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `transaction_summary_view`  AS SELECT `t`.`transaction_id` AS `transaction_id`, `d`.`full_name` AS `Deceased Name`, concat(`p`.`block`,' - ',`p`.`section`,' - ',`p`.`lot`) AS `Plot Location`, `t`.`transaction_date` AS `Date of Transaction`, `c`.`contact_person` AS `Contact Person`, `c`.`contact_number` AS `Contact Number`, `t`.`amount` AS `Amount`, CASE WHEN `pay`.`status` = 'Paid' THEN 'Paid' WHEN `pay`.`status` = 'Pending' THEN 'Pending' WHEN `pay`.`payment_id` is null AND `r`.`rental_end` < curdate() THEN 'Overdue' WHEN `pay`.`payment_id` is null THEN 'Unpaid' ELSE 'Unpaid' END AS `Status` FROM (((((`transactions` `t` left join `deceased` `d` on(`t`.`deceased_id` = `d`.`deceased_id`)) left join `plots` `p` on(`d`.`plot_id` = `p`.`plot_id`)) left join `contacts` `c` on(`d`.`deceased_id` = `c`.`deceased_id`)) left join `rentals` `r` on(`d`.`deceased_id` = `r`.`deceased_id`)) left join `payments` `pay` on(`r`.`rental_id` = `pay`.`rental_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `deceased_id` (`deceased_id`);

--
-- Indexes for table `deceased`
--
ALTER TABLE `deceased`
  ADD PRIMARY KEY (`deceased_id`),
  ADD KEY `plot_id` (`plot_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `plots`
--
ALTER TABLE `plots`
  ADD PRIMARY KEY (`plot_id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `deceased_id` (`deceased_id`),
  ADD KEY `plot_id` (`plot_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_transactions_deceased` (`deceased_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deceased`
--
ALTER TABLE `deceased`
  MODIFY `deceased_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `plots`
--
ALTER TABLE `plots`
  MODIFY `plot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=804;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`deceased_id`) REFERENCES `deceased` (`deceased_id`) ON DELETE CASCADE;

--
-- Constraints for table `deceased`
--
ALTER TABLE `deceased`
  ADD CONSTRAINT `deceased_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`plot_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deceased_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`) ON DELETE CASCADE;

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`deceased_id`) REFERENCES `deceased` (`deceased_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`plot_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rentals_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_deceased` FOREIGN KEY (`deceased_id`) REFERENCES `deceased` (`deceased_id`),
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Insert all plots for the cemetery (Phase 1, Phase 2, and Phase 3)
-- Total: 350 plots

-- PHASE 1: Blocks A-I (9 blocks × 20 lots = 180 plots)
INSERT INTO `plots` (`block`, `section`, `lot`, `type`, `status`) VALUES
-- Block A
('A', '1', '1', 'Single', 'Vacant'),
('A', '1', '2', 'Single', 'Vacant'),
('A', '1', '3', 'Single', 'Vacant'),
('A', '1', '4', 'Single', 'Vacant'),
('A', '1', '5', 'Single', 'Vacant'),
('A', '2', '6', 'Single', 'Vacant'),
('A', '2', '7', 'Single', 'Vacant'),
('A', '2', '8', 'Single', 'Vacant'),
('A', '2', '9', 'Single', 'Vacant'),
('A', '2', '10', 'Single', 'Vacant'),
('A', '3', '11', 'Single', 'Vacant'),
('A', '3', '12', 'Single', 'Vacant'),
('A', '3', '13', 'Single', 'Vacant'),
('A', '3', '14', 'Single', 'Vacant'),
('A', '3', '15', 'Single', 'Vacant'),
('A', '4', '16', 'Single', 'Vacant'),
('A', '4', '17', 'Single', 'Vacant'),
('A', '4', '18', 'Single', 'Vacant'),
('A', '4', '19', 'Single', 'Vacant'),
('A', '4', '20', 'Single', 'Vacant'),

-- Block B
('B', '1', '1', 'Single', 'Vacant'),
('B', '1', '2', 'Single', 'Vacant'),
('B', '1', '3', 'Single', 'Vacant'),
('B', '1', '4', 'Single', 'Vacant'),
('B', '1', '5', 'Single', 'Vacant'),
('B', '2', '6', 'Single', 'Vacant'),
('B', '2', '7', 'Single', 'Vacant'),
('B', '2', '8', 'Single', 'Vacant'),
('B', '2', '9', 'Single', 'Vacant'),
('B', '2', '10', 'Single', 'Vacant'),
('B', '3', '11', 'Single', 'Vacant'),
('B', '3', '12', 'Single', 'Vacant'),
('B', '3', '13', 'Single', 'Vacant'),
('B', '3', '14', 'Single', 'Vacant'),
('B', '3', '15', 'Single', 'Vacant'),
('B', '4', '16', 'Single', 'Vacant'),
('B', '4', '17', 'Single', 'Vacant'),
('B', '4', '18', 'Single', 'Vacant'),
('B', '4', '19', 'Single', 'Vacant'),
('B', '4', '20', 'Single', 'Vacant'),

-- Block C
('C', '1', '1', 'Single', 'Vacant'),
('C', '1', '2', 'Single', 'Vacant'),
('C', '1', '3', 'Single', 'Vacant'),
('C', '1', '4', 'Single', 'Vacant'),
('C', '1', '5', 'Single', 'Vacant'),
('C', '2', '6', 'Single', 'Vacant'),
('C', '2', '7', 'Single', 'Vacant'),
('C', '2', '8', 'Single', 'Vacant'),
('C', '2', '9', 'Single', 'Vacant'),
('C', '2', '10', 'Single', 'Vacant'),
('C', '3', '11', 'Single', 'Vacant'),
('C', '3', '12', 'Single', 'Vacant'),
('C', '3', '13', 'Single', 'Vacant'),
('C', '3', '14', 'Single', 'Vacant'),
('C', '3', '15', 'Single', 'Vacant'),
('C', '4', '16', 'Single', 'Vacant'),
('C', '4', '17', 'Single', 'Vacant'),
('C', '4', '18', 'Single', 'Vacant'),
('C', '4', '19', 'Single', 'Vacant'),
('C', '4', '20', 'Single', 'Vacant'),

-- Block D
('D', '1', '1', 'Single', 'Vacant'),
('D', '1', '2', 'Single', 'Vacant'),
('D', '1', '3', 'Single', 'Vacant'),
('D', '1', '4', 'Single', 'Vacant'),
('D', '1', '5', 'Single', 'Vacant'),
('D', '2', '6', 'Single', 'Vacant'),
('D', '2', '7', 'Single', 'Vacant'),
('D', '2', '8', 'Single', 'Vacant'),
('D', '2', '9', 'Single', 'Vacant'),
('D', '2', '10', 'Single', 'Vacant'),
('D', '3', '11', 'Single', 'Vacant'),
('D', '3', '12', 'Single', 'Vacant'),
('D', '3', '13', 'Single', 'Vacant'),
('D', '3', '14', 'Single', 'Vacant'),
('D', '3', '15', 'Single', 'Vacant'),
('D', '4', '16', 'Single', 'Vacant'),
('D', '4', '17', 'Single', 'Vacant'),
('D', '4', '18', 'Single', 'Vacant'),
('D', '4', '19', 'Single', 'Vacant'),
('D', '4', '20', 'Single', 'Vacant'),

-- Block E
('E', '1', '1', 'Single', 'Vacant'),
('E', '1', '2', 'Single', 'Vacant'),
('E', '1', '3', 'Single', 'Vacant'),
('E', '1', '4', 'Single', 'Vacant'),
('E', '1', '5', 'Single', 'Vacant'),
('E', '2', '6', 'Single', 'Vacant'),
('E', '2', '7', 'Single', 'Vacant'),
('E', '2', '8', 'Single', 'Vacant'),
('E', '2', '9', 'Single', 'Vacant'),
('E', '2', '10', 'Single', 'Vacant'),
('E', '3', '11', 'Single', 'Vacant'),
('E', '3', '12', 'Single', 'Vacant'),
('E', '3', '13', 'Single', 'Vacant'),
('E', '3', '14', 'Single', 'Vacant'),
('E', '3', '15', 'Single', 'Vacant'),
('E', '4', '16', 'Single', 'Vacant'),
('E', '4', '17', 'Single', 'Vacant'),
('E', '4', '18', 'Single', 'Vacant'),
('E', '4', '19', 'Single', 'Vacant'),
('E', '4', '20', 'Single', 'Vacant'),

-- Block F
('F', '1', '1', 'Single', 'Vacant'),
('F', '1', '2', 'Single', 'Vacant'),
('F', '1', '3', 'Single', 'Vacant'),
('F', '1', '4', 'Single', 'Vacant'),
('F', '1', '5', 'Single', 'Vacant'),
('F', '2', '6', 'Single', 'Vacant'),
('F', '2', '7', 'Single', 'Vacant'),
('F', '2', '8', 'Single', 'Vacant'),
('F', '2', '9', 'Single', 'Vacant'),
('F', '2', '10', 'Single', 'Vacant'),
('F', '3', '11', 'Single', 'Vacant'),
('F', '3', '12', 'Single', 'Vacant'),
('F', '3', '13', 'Single', 'Vacant'),
('F', '3', '14', 'Single', 'Vacant'),
('F', '3', '15', 'Single', 'Vacant'),
('F', '4', '16', 'Single', 'Vacant'),
('F', '4', '17', 'Single', 'Vacant'),
('F', '4', '18', 'Single', 'Vacant'),
('F', '4', '19', 'Single', 'Vacant'),
('F', '4', '20', 'Single', 'Vacant'),

-- Block G
('G', '1', '1', 'Single', 'Vacant'),
('G', '1', '2', 'Single', 'Vacant'),
('G', '1', '3', 'Single', 'Vacant'),
('G', '1', '4', 'Single', 'Vacant'),
('G', '1', '5', 'Single', 'Vacant'),
('G', '2', '6', 'Single', 'Vacant'),
('G', '2', '7', 'Single', 'Vacant'),
('G', '2', '8', 'Single', 'Vacant'),
('G', '2', '9', 'Single', 'Vacant'),
('G', '2', '10', 'Single', 'Vacant'),
('G', '3', '11', 'Single', 'Vacant'),
('G', '3', '12', 'Single', 'Vacant'),
('G', '3', '13', 'Single', 'Vacant'),
('G', '3', '14', 'Single', 'Vacant'),
('G', '3', '15', 'Single', 'Vacant'),
('G', '4', '16', 'Single', 'Vacant'),
('G', '4', '17', 'Single', 'Vacant'),
('G', '4', '18', 'Single', 'Vacant'),
('G', '4', '19', 'Single', 'Vacant'),
('G', '4', '20', 'Single', 'Vacant'),

-- Block H
('H', '1', '1', 'Single', 'Vacant'),
('H', '1', '2', 'Single', 'Vacant'),
('H', '1', '3', 'Single', 'Vacant'),
('H', '1', '4', 'Single', 'Vacant'),
('H', '1', '5', 'Single', 'Vacant'),
('H', '2', '6', 'Single', 'Vacant'),
('H', '2', '7', 'Single', 'Vacant'),
('H', '2', '8', 'Single', 'Vacant'),
('H', '2', '9', 'Single', 'Vacant'),
('H', '2', '10', 'Single', 'Vacant'),
('H', '3', '11', 'Single', 'Vacant'),
('H', '3', '12', 'Single', 'Vacant'),
('H', '3', '13', 'Single', 'Vacant'),
('H', '3', '14', 'Single', 'Vacant'),
('H', '3', '15', 'Single', 'Vacant'),
('H', '4', '16', 'Single', 'Vacant'),
('H', '4', '17', 'Single', 'Vacant'),
('H', '4', '18', 'Single', 'Vacant'),
('H', '4', '19', 'Single', 'Vacant'),
('H', '4', '20', 'Single', 'Vacant'),

-- Block I
('I', '1', '1', 'Single', 'Vacant'),
('I', '1', '2', 'Single', 'Vacant'),
('I', '1', '3', 'Single', 'Vacant'),
('I', '1', '4', 'Single', 'Vacant'),
('I', '1', '5', 'Single', 'Vacant'),
('I', '2', '6', 'Single', 'Vacant'),
('I', '2', '7', 'Single', 'Vacant'),
('I', '2', '8', 'Single', 'Vacant'),
('I', '2', '9', 'Single', 'Vacant'),
('I', '2', '10', 'Single', 'Vacant'),
('I', '3', '11', 'Single', 'Vacant'),
('I', '3', '12', 'Single', 'Vacant'),
('I', '3', '13', 'Single', 'Vacant'),
('I', '3', '14', 'Single', 'Vacant'),
('I', '3', '15', 'Single', 'Vacant'),
('I', '4', '16', 'Single', 'Vacant'),
('I', '4', '17', 'Single', 'Vacant'),
('I', '4', '18', 'Single', 'Vacant'),
('I', '4', '19', 'Single', 'Vacant'),
('I', '4', '20', 'Single', 'Vacant'),

-- PHASE 2: Blocks T-Z (7 blocks × 20 lots = 140 plots)
-- Block T
('T', '1', '1', 'Single', 'Vacant'),
('T', '1', '2', 'Single', 'Vacant'),
('T', '1', '3', 'Single', 'Vacant'),
('T', '1', '4', 'Single', 'Vacant'),
('T', '1', '5', 'Single', 'Vacant'),
('T', '2', '6', 'Single', 'Vacant'),
('T', '2', '7', 'Single', 'Vacant'),
('T', '2', '8', 'Single', 'Vacant'),
('T', '2', '9', 'Single', 'Vacant'),
('T', '2', '10', 'Single', 'Vacant'),
('T', '3', '11', 'Single', 'Vacant'),
('T', '3', '12', 'Single', 'Vacant'),
('T', '3', '13', 'Single', 'Vacant'),
('T', '3', '14', 'Single', 'Vacant'),
('T', '3', '15', 'Single', 'Vacant'),
('T', '4', '16', 'Single', 'Vacant'),
('T', '4', '17', 'Single', 'Vacant'),
('T', '4', '18', 'Single', 'Vacant'),
('T', '4', '19', 'Single', 'Vacant'),
('T', '4', '20', 'Single', 'Vacant'),

-- Block U
('U', '1', '1', 'Single', 'Vacant'),
('U', '1', '2', 'Single', 'Vacant'),
('U', '1', '3', 'Single', 'Vacant'),
('U', '1', '4', 'Single', 'Vacant'),
('U', '1', '5', 'Single', 'Vacant'),
('U', '2', '6', 'Single', 'Vacant'),
('U', '2', '7', 'Single', 'Vacant'),
('U', '2', '8', 'Single', 'Vacant'),
('U', '2', '9', 'Single', 'Vacant'),
('U', '2', '10', 'Single', 'Vacant'),
('U', '3', '11', 'Single', 'Vacant'),
('U', '3', '12', 'Single', 'Vacant'),
('U', '3', '13', 'Single', 'Vacant'),
('U', '3', '14', 'Single', 'Vacant'),
('U', '3', '15', 'Single', 'Vacant'),
('U', '4', '16', 'Single', 'Vacant'),
('U', '4', '17', 'Single', 'Vacant'),
('U', '4', '18', 'Single', 'Vacant'),
('U', '4', '19', 'Single', 'Vacant'),
('U', '4', '20', 'Single', 'Vacant'),

-- Block V
('V', '1', '1', 'Single', 'Vacant'),
('V', '1', '2', 'Single', 'Vacant'),
('V', '1', '3', 'Single', 'Vacant'),
('V', '1', '4', 'Single', 'Vacant'),
('V', '1', '5', 'Single', 'Vacant'),
('V', '2', '6', 'Single', 'Vacant'),
('V', '2', '7', 'Single', 'Vacant'),
('V', '2', '8', 'Single', 'Vacant'),
('V', '2', '9', 'Single', 'Vacant'),
('V', '2', '10', 'Single', 'Vacant'),
('V', '3', '11', 'Single', 'Vacant'),
('V', '3', '12', 'Single', 'Vacant'),
('V', '3', '13', 'Single', 'Vacant'),
('V', '3', '14', 'Single', 'Vacant'),
('V', '3', '15', 'Single', 'Vacant'),
('V', '4', '16', 'Single', 'Vacant'),
('V', '4', '17', 'Single', 'Vacant'),
('V', '4', '18', 'Single', 'Vacant'),
('V', '4', '19', 'Single', 'Vacant'),
('V', '4', '20', 'Single', 'Vacant'),

-- Block W
('W', '1', '1', 'Single', 'Vacant'),
('W', '1', '2', 'Single', 'Vacant'),
('W', '1', '3', 'Single', 'Vacant'),
('W', '1', '4', 'Single', 'Vacant'),
('W', '1', '5', 'Single', 'Vacant'),
('W', '2', '6', 'Single', 'Vacant'),
('W', '2', '7', 'Single', 'Vacant'),
('W', '2', '8', 'Single', 'Vacant'),
('W', '2', '9', 'Single', 'Vacant'),
('W', '2', '10', 'Single', 'Vacant'),
('W', '3', '11', 'Single', 'Vacant'),
('W', '3', '12', 'Single', 'Vacant'),
('W', '3', '13', 'Single', 'Vacant'),
('W', '3', '14', 'Single', 'Vacant'),
('W', '3', '15', 'Single', 'Vacant'),
('W', '4', '16', 'Single', 'Vacant'),
('W', '4', '17', 'Single', 'Vacant'),
('W', '4', '18', 'Single', 'Vacant'),
('W', '4', '19', 'Single', 'Vacant'),
('W', '4', '20', 'Single', 'Vacant'),

-- Block X
('X', '1', '1', 'Single', 'Vacant'),
('X', '1', '2', 'Single', 'Vacant'),
('X', '1', '3', 'Single', 'Vacant'),
('X', '1', '4', 'Single', 'Vacant'),
('X', '1', '5', 'Single', 'Vacant'),
('X', '2', '6', 'Single', 'Vacant'),
('X', '2', '7', 'Single', 'Vacant'),
('X', '2', '8', 'Single', 'Vacant'),
('X', '2', '9', 'Single', 'Vacant'),
('X', '2', '10', 'Single', 'Vacant'),
('X', '3', '11', 'Single', 'Vacant'),
('X', '3', '12', 'Single', 'Vacant'),
('X', '3', '13', 'Single', 'Vacant'),
('X', '3', '14', 'Single', 'Vacant'),
('X', '3', '15', 'Single', 'Vacant'),
('X', '4', '16', 'Single', 'Vacant'),
('X', '4', '17', 'Single', 'Vacant'),
('X', '4', '18', 'Single', 'Vacant'),
('X', '4', '19', 'Single', 'Vacant'),
('X', '4', '20', 'Single', 'Vacant'),

-- Block Y
('Y', '1', '1', 'Single', 'Vacant'),
('Y', '1', '2', 'Single', 'Vacant'),
('Y', '1', '3', 'Single', 'Vacant'),
('Y', '1', '4', 'Single', 'Vacant'),
('Y', '1', '5', 'Single', 'Vacant'),
('Y', '2', '6', 'Single', 'Vacant'),
('Y', '2', '7', 'Single', 'Vacant'),
('Y', '2', '8', 'Single', 'Vacant'),
('Y', '2', '9', 'Single', 'Vacant'),
('Y', '2', '10', 'Single', 'Vacant'),
('Y', '3', '11', 'Single', 'Vacant'),
('Y', '3', '12', 'Single', 'Vacant'),
('Y', '3', '13', 'Single', 'Vacant'),
('Y', '3', '14', 'Single', 'Vacant'),
('Y', '3', '15', 'Single', 'Vacant'),
('Y', '4', '16', 'Single', 'Vacant'),
('Y', '4', '17', 'Single', 'Vacant'),
('Y', '4', '18', 'Single', 'Vacant'),
('Y', '4', '19', 'Single', 'Vacant'),
('Y', '4', '20', 'Single', 'Vacant'),

-- Block Z
('Z', '1', '1', 'Single', 'Vacant'),
('Z', '1', '2', 'Single', 'Vacant'),
('Z', '1', '3', 'Single', 'Vacant'),
('Z', '1', '4', 'Single', 'Vacant'),
('Z', '1', '5', 'Single', 'Vacant'),
('Z', '2', '6', 'Single', 'Vacant'),
('Z', '2', '7', 'Single', 'Vacant'),
('Z', '2', '8', 'Single', 'Vacant'),
('Z', '2', '9', 'Single', 'Vacant'),
('Z', '2', '10', 'Single', 'Vacant'),
('Z', '3', '11', 'Single', 'Vacant'),
('Z', '3', '12', 'Single', 'Vacant'),
('Z', '3', '13', 'Single', 'Vacant'),
('Z', '3', '14', 'Single', 'Vacant'),
('Z', '3', '15', 'Single', 'Vacant'),
('Z', '4', '16', 'Single', 'Vacant'),
('Z', '4', '17', 'Single', 'Vacant'),
('Z', '4', '18', 'Single', 'Vacant'),
('Z', '4', '19', 'Single', 'Vacant'),
('Z', '4', '20', 'Single', 'Vacant'),

-- PHASE 3: Block AA (20 lots) + Overflow (10 lots) = 30 plots
-- Block AA
('AA', '1', '1', 'Single', 'Vacant'),
('AA', '1', '2', 'Single', 'Vacant'),
('AA', '1', '3', 'Single', 'Vacant'),
('AA', '1', '4', 'Single', 'Vacant'),
('AA', '1', '5', 'Single', 'Vacant'),
('AA', '2', '6', 'Single', 'Vacant'),
('AA', '2', '7', 'Single', 'Vacant'),
('AA', '2', '8', 'Single', 'Vacant'),
('AA', '2', '9', 'Single', 'Vacant'),
('AA', '2', '10', 'Single', 'Vacant'),
('AA', '3', '11', 'Single', 'Vacant'),
('AA', '3', '12', 'Single', 'Vacant'),
('AA', '3', '13', 'Single', 'Vacant'),
('AA', '3', '14', 'Single', 'Vacant'),
('AA', '3', '15', 'Single', 'Vacant'),
('AA', '4', '16', 'Single', 'Vacant'),
('AA', '4', '17', 'Single', 'Vacant'),
('AA', '4', '18', 'Single', 'Vacant'),
('AA', '4', '19', 'Single', 'Vacant'),
('AA', '4', '20', 'Single', 'Vacant'),

-- Overflow (Unnamed) - 10 plots for Phase 3
('AAA', '1', '1', 'Single', 'Vacant'),
('AAA', '1', '2', 'Single', 'Vacant'),
('AAA', '1', '3', 'Single', 'Vacant'),
('AAA', '1', '4', 'Single', 'Vacant'),
('AAA', '1', '5', 'Single', 'Vacant'),
('AAA', '2', '6', 'Single', 'Vacant'),
('AAA', '2', '7', 'Single', 'Vacant'),
('AAA', '2', '8', 'Single', 'Vacant'),
('AAA', '2', '9', 'Single', 'Vacant'),
('AAA', '2', '10', 'Single', 'Vacant');
