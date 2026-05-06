-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 06, 2026 at 04:33 AM
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

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`contact_id`, `deceased_id`, `contact_person`, `contact_number`) VALUES
(1, 24, 'jolo', '090777899876'),
(3, 26, 'koi', NULL),
(5, 28, 'Niel Francis Benedict', '09566632253'),
(6, 29, 'Niel Francis Benedict', '09566632253');

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
-- Dumping data for table `deceased`
--

INSERT INTO `deceased` (`deceased_id`, `full_name`, `date_of_death`, `date_of_burial`, `gender`, `address`, `plot_id`, `burial_type`, `created_by`, `birth_date`) VALUES
(24, 'marga', '2026-05-05', '2026-03-20', 'Female', 'fff', 1013, 'Ground', NULL, '2005-05-05'),
(26, 'aaa', '2022-06-06', '2022-06-14', 'Female', 'qqq', 1030, 'Ground', NULL, '2006-06-06'),
(28, 'Feelings mo', '2026-05-05', '2026-05-05', 'Male', '0403, Sitio palico, Bilaran', 804, 'Ground', NULL, '2003-02-07'),
(29, 'Niel Francis Benedict', '2024-05-01', '2025-05-01', 'Male', '0403, Sitio palico, Bilaran', 805, 'Ground', NULL, '2006-06-06');

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
DELIMITER $$
CREATE TRIGGER `after_deceased_insert` AFTER INSERT ON `deceased` FOR EACH ROW BEGIN
    IF NEW.plot_id IS NOT NULL THEN
        -- Only create a rental if none exists yet for this deceased
        IF NOT EXISTS (
            SELECT 1 FROM rentals r
            WHERE r.deceased_id = NEW.deceased_id
            LIMIT 1
        ) THEN
            INSERT INTO `rentals` (
                `deceased_id`,
                `plot_id`,
                `rental_start`,
                `rental_end`,
                `amount`,
                `status`
            ) VALUES (
                NEW.deceased_id,
                NEW.plot_id,
                COALESCE(NEW.date_of_burial, CURDATE()),
                DATE_ADD(COALESCE(NEW.date_of_burial, CURDATE()), INTERVAL 3 YEAR),
                2000.00,
                'Unpaid'
            );
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_deceased_insert_limit_plot_capacity` BEFORE INSERT ON `deceased` FOR EACH ROW BEGIN
    DECLARE plotCount INT DEFAULT 0;

    IF NEW.plot_id IS NOT NULL THEN
        SELECT COUNT(*) INTO plotCount
        FROM deceased
        WHERE plot_id = NEW.plot_id;

        IF plotCount >= 5 THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Plot capacity exceeded: maximum 5 burial records per plot.';
        END IF;
    END IF;
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
  `status` enum('Paid','Pending') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `rental_id`, `payment_date`, `amount`, `status`) VALUES
(3, 4, '2026-05-05', 777.00, 'Paid'),
(4, 6, '2026-05-05', 788.00, 'Paid'),
(5, 4, '2026-05-05', 3000.00, 'Paid'),
(6, 8, '2026-05-05', NULL, 'Paid'),
(7, 8, '2026-05-05', 10000.00, 'Paid'),
(9, 6, '2026-05-06', 1800.00, 'Paid'),
(10, 8, '2026-05-06', 3000.00, 'Paid'),
(17, 4, '2026-05-06', 2000.00, 'Paid');

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

--
-- Dumping data for table `plots`
--

INSERT INTO `plots` (`plot_id`, `block`, `section`, `lot`, `type`, `status`, `date_added`) VALUES
(804, 'A', '1', '1', 'Single', 'Occupied', '2026-05-05 18:05:22'),
(805, 'A', '1', '2', 'Single', 'Occupied', '2026-05-05 18:05:22'),
(806, 'A', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(807, 'A', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(808, 'A', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(809, 'A', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(810, 'A', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(811, 'A', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(812, 'A', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(813, 'A', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(814, 'A', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(815, 'A', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(816, 'A', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(817, 'A', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(818, 'A', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(819, 'A', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(820, 'A', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(821, 'A', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(822, 'A', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(823, 'A', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(824, 'B', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(825, 'B', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(826, 'B', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(827, 'B', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(828, 'B', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(829, 'B', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(830, 'B', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(831, 'B', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(832, 'B', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(833, 'B', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(834, 'B', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(835, 'B', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(836, 'B', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(837, 'B', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(838, 'B', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(839, 'B', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(840, 'B', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(841, 'B', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(842, 'B', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(843, 'B', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(844, 'C', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(845, 'C', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(846, 'C', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(847, 'C', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(848, 'C', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(849, 'C', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(850, 'C', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(851, 'C', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(852, 'C', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(853, 'C', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(854, 'C', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(855, 'C', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(856, 'C', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(857, 'C', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(858, 'C', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(859, 'C', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(860, 'C', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(861, 'C', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(862, 'C', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(863, 'C', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(864, 'D', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(865, 'D', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(866, 'D', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(867, 'D', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(868, 'D', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(869, 'D', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(870, 'D', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(871, 'D', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(872, 'D', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(873, 'D', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(874, 'D', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(875, 'D', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(876, 'D', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(877, 'D', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(878, 'D', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(879, 'D', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(880, 'D', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(881, 'D', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(882, 'D', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(883, 'D', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(884, 'E', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(885, 'E', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(886, 'E', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(887, 'E', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(888, 'E', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(889, 'E', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(890, 'E', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(891, 'E', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(892, 'E', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(893, 'E', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(894, 'E', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(895, 'E', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(896, 'E', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(897, 'E', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(898, 'E', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(899, 'E', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(900, 'E', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(901, 'E', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(902, 'E', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(903, 'E', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(904, 'F', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(905, 'F', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(906, 'F', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(907, 'F', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(908, 'F', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(909, 'F', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(910, 'F', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(911, 'F', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(912, 'F', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(913, 'F', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(914, 'F', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(915, 'F', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(916, 'F', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(917, 'F', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(918, 'F', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(919, 'F', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(920, 'F', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(921, 'F', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(922, 'F', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(923, 'F', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(924, 'G', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(925, 'G', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(926, 'G', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(927, 'G', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(928, 'G', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(929, 'G', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(930, 'G', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(931, 'G', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(932, 'G', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(933, 'G', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(934, 'G', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(935, 'G', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(936, 'G', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(937, 'G', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(938, 'G', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(939, 'G', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(940, 'G', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(941, 'G', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(942, 'G', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(943, 'G', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(944, 'H', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(945, 'H', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(946, 'H', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(947, 'H', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(948, 'H', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(949, 'H', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(950, 'H', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(951, 'H', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(952, 'H', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(953, 'H', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(954, 'H', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(955, 'H', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(956, 'H', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(957, 'H', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(958, 'H', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(959, 'H', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(960, 'H', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(961, 'H', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(962, 'H', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(963, 'H', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(964, 'I', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(965, 'I', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(966, 'I', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(967, 'I', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(968, 'I', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(969, 'I', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(970, 'I', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(971, 'I', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(972, 'I', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(973, 'I', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(974, 'I', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(975, 'I', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(976, 'I', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(977, 'I', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(978, 'I', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(979, 'I', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(980, 'I', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(981, 'I', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(982, 'I', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(983, 'I', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(984, 'T', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(985, 'T', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(986, 'T', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(987, 'T', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(988, 'T', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(989, 'T', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(990, 'T', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(991, 'T', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(992, 'T', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(993, 'T', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(994, 'T', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(995, 'T', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(996, 'T', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(997, 'T', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(998, 'T', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(999, 'T', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1000, 'T', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1001, 'T', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1002, 'T', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1003, 'T', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1004, 'U', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1005, 'U', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1006, 'U', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1007, 'U', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1008, 'U', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1009, 'U', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1010, 'U', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1011, 'U', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1012, 'U', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1013, 'U', '1', '10', 'Single', 'Occupied', '2026-05-05 18:05:22'),
(1014, 'U', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1015, 'U', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1016, 'U', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1017, 'U', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1018, 'U', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1019, 'U', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1020, 'U', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1021, 'U', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1022, 'U', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1023, 'U', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1024, 'V', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1025, 'V', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1026, 'V', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1027, 'V', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1028, 'V', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1029, 'V', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1030, 'V', '1', '7', 'Single', 'Occupied', '2026-05-05 18:05:22'),
(1031, 'V', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1032, 'V', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1033, 'V', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1034, 'V', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1035, 'V', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1036, 'V', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1037, 'V', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1038, 'V', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1039, 'V', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1040, 'V', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1041, 'V', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1042, 'V', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1043, 'V', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1044, 'W', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1045, 'W', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1046, 'W', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1047, 'W', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1048, 'W', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1049, 'W', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1050, 'W', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1051, 'W', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1052, 'W', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1053, 'W', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1054, 'W', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1055, 'W', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1056, 'W', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1057, 'W', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1058, 'W', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1059, 'W', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1060, 'W', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1061, 'W', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1062, 'W', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1063, 'W', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1064, 'X', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1065, 'X', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1066, 'X', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1067, 'X', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1068, 'X', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1069, 'X', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1070, 'X', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1071, 'X', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1072, 'X', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1073, 'X', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1074, 'X', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1075, 'X', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1076, 'X', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1077, 'X', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1078, 'X', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1079, 'X', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1080, 'X', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1081, 'X', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1082, 'X', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1083, 'X', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1084, 'Y', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1085, 'Y', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1086, 'Y', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1087, 'Y', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1088, 'Y', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1089, 'Y', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1090, 'Y', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1091, 'Y', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1092, 'Y', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1093, 'Y', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1094, 'Y', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1095, 'Y', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1096, 'Y', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1097, 'Y', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1098, 'Y', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1099, 'Y', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1100, 'Y', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1101, 'Y', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1102, 'Y', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1103, 'Y', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1104, 'Z', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1105, 'Z', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1106, 'Z', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1107, 'Z', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1108, 'Z', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1109, 'Z', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1110, 'Z', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1111, 'Z', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1112, 'Z', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1113, 'Z', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1114, 'Z', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1115, 'Z', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1116, 'Z', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1117, 'Z', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1118, 'Z', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1119, 'Z', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1120, 'Z', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1121, 'Z', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1122, 'Z', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1123, 'Z', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1124, 'AA', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1125, 'AA', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1126, 'AA', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1127, 'AA', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1128, 'AA', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1129, 'AA', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1130, 'AA', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1131, 'AA', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1132, 'AA', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1133, 'AA', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1134, 'AA', '1', '11', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1135, 'AA', '1', '12', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1136, 'AA', '1', '13', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1137, 'AA', '1', '14', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1138, 'AA', '1', '15', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1139, 'AA', '1', '16', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1140, 'AA', '1', '17', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1141, 'AA', '1', '18', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1142, 'AA', '1', '19', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1143, 'AA', '1', '20', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1144, 'AAA', '1', '1', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1145, 'AAA', '1', '2', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1146, 'AAA', '1', '3', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1147, 'AAA', '1', '4', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1148, 'AAA', '1', '5', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1149, 'AAA', '1', '6', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1150, 'AAA', '1', '7', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1151, 'AAA', '1', '8', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1152, 'AAA', '1', '9', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1153, 'AAA', '1', '10', 'Single', 'Vacant', '2026-05-05 18:05:22'),
(1154, 'AA', '2', '1', 'Single', 'Vacant', NOW()),
(1155, 'AA', '2', '2', 'Single', 'Vacant', NOW()),
(1156, 'AA', '2', '3', 'Single', 'Vacant', NOW()),
(1157, 'AA', '2', '4', 'Single', 'Vacant', NOW()),
(1158, 'AA', '2', '5', 'Single', 'Vacant', NOW()),
(1159, 'AA', '2', '6', 'Single', 'Vacant', NOW()),
(1160, 'AA', '2', '7', 'Single', 'Vacant', NOW()),
(1161, 'AA', '2', '8', 'Single', 'Vacant', NOW()),
(1162, 'AA', '2', '9', 'Single', 'Vacant', NOW()),
(1163, 'AA', '2', '10', 'Single', 'Vacant', NOW()),
(1164, 'AA', '2', '11', 'Single', 'Vacant', NOW()),
(1165, 'AA', '2', '12', 'Single', 'Vacant', NOW()),
(1166, 'AA', '2', '13', 'Single', 'Vacant', NOW()),
(1167, 'AA', '2', '14', 'Single', 'Vacant', NOW()),
(1168, 'AA', '2', '15', 'Single', 'Vacant', NOW()),
(1169, 'AA', '2', '16', 'Single', 'Vacant', NOW()),
(1170, 'AA', '2', '17', 'Single', 'Vacant', NOW()),
(1171, 'AA', '2', '18', 'Single', 'Vacant', NOW()),
(1172, 'AA', '2', '19', 'Single', 'Vacant', NOW()),
(1173, 'AA', '2', '20', 'Single', 'Vacant', NOW()),
(1174, 'AA', '3', '1', 'Single', 'Vacant', NOW()),
(1175, 'AA', '3', '2', 'Single', 'Vacant', NOW()),
(1176, 'AA', '3', '3', 'Single', 'Vacant', NOW()),
(1177, 'AA', '3', '4', 'Single', 'Vacant', NOW()),
(1178, 'AA', '3', '5', 'Single', 'Vacant', NOW()),
(1179, 'AA', '3', '6', 'Single', 'Vacant', NOW()),
(1180, 'AA', '3', '7', 'Single', 'Vacant', NOW()),
(1181, 'AA', '3', '8', 'Single', 'Vacant', NOW()),
(1182, 'AA', '3', '9', 'Single', 'Vacant', NOW()),
(1183, 'AA', '3', '10', 'Single', 'Vacant', NOW()),
(1184, 'AA', '3', '11', 'Single', 'Vacant', NOW()),
(1185, 'AA', '3', '12', 'Single', 'Vacant', NOW()),
(1186, 'AA', '3', '13', 'Single', 'Vacant', NOW()),
(1187, 'AA', '3', '14', 'Single', 'Vacant', NOW()),
(1188, 'AA', '3', '15', 'Single', 'Vacant', NOW()),
(1189, 'AA', '3', '16', 'Single', 'Vacant', NOW()),
(1190, 'AA', '3', '17', 'Single', 'Vacant', NOW()),
(1191, 'AA', '3', '18', 'Single', 'Vacant', NOW()),
(1192, 'AA', '3', '19', 'Single', 'Vacant', NOW()),
(1193, 'AA', '3', '20', 'Single', 'Vacant', NOW());

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
  `status` enum('Active','Expired','Paid','Unpaid') NOT NULL DEFAULT 'Unpaid',
  `processed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`rental_id`, `deceased_id`, `plot_id`, `rental_start`, `rental_end`, `amount`, `status`, `processed_by`) VALUES
(4, 24, 1013, '2026-03-20', '2029-03-20', 2000.00, 'Paid', NULL),
(6, 26, 1030, '2022-06-14', '2025-06-14', 2000.00, 'Paid', NULL),
(8, 28, 804, '2007-05-09', '2025-05-02', 2000.00, 'Paid', NULL),
(9, 29, 805, '2025-05-14', '2025-05-23', 2000.00, 'Unpaid', NULL);

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
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `deceased`
--
ALTER TABLE `deceased`
  MODIFY `deceased_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `plots`
--
ALTER TABLE `plots`
  MODIFY `plot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1154;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
