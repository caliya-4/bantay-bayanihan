-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2026 at 02:34 PM
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
-- Database: `bantay_bayanihan`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `created_by`, `created_at`) VALUES
(4, 'Error 500', 'what the helly', 1, '2026-02-01 16:51:44'),
(5, 'Opening of Panagbenga festival', 'Please be informed that the not so occasional soafer traffic will be experienced throughout Baguio this day. We ask all locals to bear with us as we again become congested with tourists :)', 1, '2026-02-03 05:59:33');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_stats`
--

CREATE TABLE `barangay_stats` (
  `id` int(11) NOT NULL,
  `barangay` varchar(255) NOT NULL,
  `total_users` int(11) DEFAULT 0,
  `active_users` int(11) DEFAULT 0,
  `total_points` int(11) DEFAULT 0,
  `avg_quiz_score` decimal(5,2) DEFAULT 0.00,
  `drill_participation_rate` decimal(5,2) DEFAULT 0.00,
  `preparedness_score` decimal(5,2) DEFAULT 0.00,
  `rank_position` int(11) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_stats`
--

INSERT INTO `barangay_stats` (`id`, `barangay`, `total_users`, `active_users`, `total_points`, `avg_quiz_score`, `drill_participation_rate`, `preparedness_score`, `rank_position`, `last_updated`) VALUES
(1, 'A. Bonifacio-Caguioa-Rimando (ABCR)', 58, 7, 1762, 74.54, 51.99, 85.37, 40, '2026-04-07 15:30:20'),
(2, 'Abanao-Zandueta-Kayong-Chugum-Otek (AZKCO)', 95, 13, 10672, 94.35, 85.77, 67.03, 94, '2026-04-07 15:30:20'),
(3, 'Alfonso Tabora', 38, 61, 1656, 82.38, 73.24, 78.24, 59, '2026-04-07 15:30:20'),
(4, 'Ambiong', 128, 62, 15248, 65.71, 40.98, 87.11, 34, '2026-04-07 15:30:20'),
(5, 'Andres Bonifacio (Lower Bokawkan)', 58, 33, 5090, 68.00, 67.83, 79.26, 54, '2026-04-07 15:30:20'),
(6, 'Apugan-Loakan', 51, 45, 8109, 79.64, 89.67, 56.04, 127, '2026-04-07 15:30:20'),
(7, 'Asin Road', 120, 5, 6150, 91.43, 47.91, 85.24, 42, '2026-04-07 15:30:20'),
(8, 'Atok Trail', 116, 6, 8822, 86.66, 91.51, 77.13, 60, '2026-04-07 15:30:20'),
(9, 'Aurora Hill Proper (Bgy. 4)', 133, 19, 4062, 79.04, 70.83, 85.36, 41, '2026-04-07 15:30:20'),
(10, 'Aurora Hill, North Central', 110, 52, 8377, 73.19, 81.26, 55.98, 128, '2026-04-07 15:30:20'),
(11, 'Aurora Hill, South Central', 45, 59, 12312, 71.07, 72.19, 80.65, 51, '2026-04-07 15:30:20'),
(12, 'Bakakeng Central', 44, 23, 12751, 69.57, 50.26, 54.85, 131, '2026-04-07 15:30:20'),
(13, 'Bakakeng Norte', 83, 37, 1020, 80.77, 64.12, 52.26, 138, '2026-04-07 15:30:20'),
(14, 'Bakakeng-Ilucan', 94, 14, 13012, 85.42, 88.56, 72.20, 79, '2026-04-07 15:30:20'),
(15, 'Bal-Marcoville', 88, 44, 7748, 77.84, 74.87, 58.04, 121, '2026-04-07 15:30:20'),
(16, 'Balsigan', 107, 24, 5863, 88.79, 88.00, 53.96, 133, '2026-04-07 15:30:20'),
(17, 'Bayan Park East', 103, 30, 14691, 78.16, 53.11, 72.16, 80, '2026-04-07 15:30:20'),
(18, 'Bayan Park Village', 43, 54, 6213, 78.26, 34.37, 50.48, 142, '2026-04-07 15:30:20'),
(19, 'Bayan Park West', 116, 17, 8272, 93.58, 43.56, 58.87, 119, '2026-04-07 15:30:20'),
(20, 'BGH Compound', 50, 61, 12413, 69.72, 56.29, 76.38, 66, '2026-04-07 15:30:20'),
(21, 'Brookside', 79, 6, 8414, 81.76, 43.62, 67.86, 89, '2026-04-07 15:30:20'),
(22, 'Brookspoint', 42, 7, 8421, 80.16, 91.28, 59.50, 116, '2026-04-07 15:30:20'),
(23, 'Bugnay', 34, 17, 9048, 71.54, 54.75, 61.97, 113, '2026-04-07 15:30:20'),
(24, 'Busol', 27, 51, 8850, 78.85, 71.47, 88.63, 26, '2026-04-07 15:30:20'),
(25, 'Cabinet Hill-Teacher\'s Camp', 28, 13, 5823, 75.89, 78.80, 81.89, 49, '2026-04-07 15:30:20'),
(26, 'Camp 7', 23, 26, 8914, 87.30, 32.20, 95.09, 11, '2026-04-07 15:30:20'),
(27, 'Camp 8', 86, 14, 15450, 80.45, 67.99, 68.11, 88, '2026-04-07 15:30:20'),
(28, 'Camp Allen', 30, 37, 4567, 87.60, 91.96, 74.29, 72, '2026-04-07 15:30:20'),
(29, 'Camp Dangwa', 95, 55, 3021, 74.96, 40.15, 87.65, 32, '2026-04-07 15:30:20'),
(30, 'Camp Henry Chapman', 69, 59, 3596, 73.78, 84.83, 66.32, 101, '2026-04-07 15:30:20'),
(31, 'Camp Holmes', 35, 54, 9777, 83.52, 45.00, 64.49, 105, '2026-04-07 15:30:20'),
(32, 'Camp John Hay', 113, 15, 7180, 85.67, 37.11, 73.01, 76, '2026-04-07 15:30:20'),
(33, 'Campo Filipino', 23, 59, 5430, 92.66, 70.56, 66.97, 96, '2026-04-07 15:30:20'),
(34, 'Camdas Subdivision', 122, 30, 6681, 89.00, 79.64, 70.09, 83, '2026-04-07 15:30:20'),
(35, 'City Camp Central', 111, 50, 5966, 81.80, 75.89, 90.82, 23, '2026-04-07 15:30:20'),
(36, 'City Camp Proper', 31, 12, 3355, 82.90, 56.79, 63.01, 109, '2026-04-07 15:30:20'),
(37, 'Country Club Village', 29, 51, 8698, 76.74, 50.56, 69.58, 85, '2026-04-07 15:30:20'),
(38, 'Cresencia Village', 25, 18, 13696, 86.22, 88.32, 67.49, 91, '2026-04-07 15:30:20'),
(39, 'Dagsian, Lower', 30, 38, 6478, 74.58, 56.05, 52.21, 139, '2026-04-07 15:30:20'),
(40, 'Dagsian, Upper', 18, 5, 14321, 83.13, 46.72, 72.70, 77, '2026-04-07 15:30:20'),
(41, 'Deepwater', 86, 37, 14805, 68.87, 80.90, 75.37, 68, '2026-04-07 15:30:20'),
(42, 'Dizon Subdivision', 50, 58, 8594, 66.36, 69.47, 93.22, 18, '2026-04-07 15:30:20'),
(43, 'Dominican Hill-Mirador', 96, 47, 7478, 71.66, 76.42, 93.43, 16, '2026-04-07 15:30:20'),
(44, 'Dontogan', 60, 16, 12918, 81.66, 49.04, 88.33, 29, '2026-04-07 15:30:20'),
(45, 'DPS Area', 28, 15, 8357, 68.00, 90.40, 66.61, 99, '2026-04-07 15:30:20'),
(46, 'East Guisad', 128, 45, 8493, 84.48, 72.13, 64.04, 108, '2026-04-07 15:30:20'),
(47, 'Engineers Hill', 77, 47, 562, 91.60, 57.30, 71.14, 81, '2026-04-07 15:30:20'),
(48, 'Fairview Village', 128, 28, 2352, 78.42, 86.29, 97.43, 2, '2026-04-07 15:30:20'),
(49, 'Ferdinand', 56, 50, 11351, 75.97, 72.73, 59.14, 117, '2026-04-07 15:30:20'),
(50, 'Fort Del Pilar', 132, 24, 10898, 79.61, 53.13, 65.25, 102, '2026-04-07 15:30:20'),
(51, 'Gabriela Silang', 77, 44, 11015, 81.39, 70.86, 74.20, 73, '2026-04-07 15:30:20'),
(52, 'General Emilio F. Aguinaldo (Lower QM)', 91, 44, 6683, 67.11, 37.47, 67.44, 92, '2026-04-07 15:30:20'),
(53, 'General Luna Road', 71, 21, 14639, 91.92, 72.80, 78.82, 56, '2026-04-07 15:30:20'),
(54, 'Gibraltar', 18, 24, 9270, 92.73, 86.36, 77.00, 62, '2026-04-07 15:30:20'),
(55, 'Greenwater Village', 40, 27, 3741, 94.11, 43.17, 54.90, 130, '2026-04-07 15:30:20'),
(56, 'Guisad Central', 123, 17, 5552, 66.69, 47.62, 58.95, 118, '2026-04-07 15:30:20'),
(57, 'Guisad South (New Lucban)', 29, 7, 12739, 94.29, 58.24, 61.66, 114, '2026-04-07 15:30:20'),
(58, 'Happy Hollow', 124, 54, 6505, 80.65, 56.46, 72.59, 78, '2026-04-07 15:30:20'),
(59, 'Happy Homes', 30, 19, 13177, 79.43, 86.51, 93.40, 17, '2026-04-07 15:30:20'),
(60, 'Harrison-Claudio Carantes', 124, 56, 8277, 66.15, 71.36, 53.20, 136, '2026-04-07 15:30:20'),
(61, 'Hillside', 65, 60, 5550, 92.53, 67.54, 56.48, 125, '2026-04-07 15:30:20'),
(62, 'Holy Ghost Extension', 128, 23, 11169, 83.99, 32.07, 62.46, 111, '2026-04-07 15:30:20'),
(63, 'Holyghost Proper', 39, 18, 8881, 67.83, 81.73, 83.39, 46, '2026-04-07 15:30:20'),
(64, 'Honeymoon', 25, 27, 8954, 86.49, 87.91, 64.68, 104, '2026-04-07 15:30:20'),
(65, 'Imelda R. Marcos (IRM)', 117, 26, 4224, 69.54, 30.80, 79.16, 55, '2026-04-07 15:30:20'),
(66, 'Imelda Village', 15, 15, 13858, 92.55, 89.79, 90.52, 24, '2026-04-07 15:30:20'),
(67, 'Irisan', 70, 51, 7925, 69.41, 46.22, 88.72, 25, '2026-04-07 15:30:20'),
(68, 'Kabayanihan', 49, 5, 3133, 90.77, 79.92, 62.62, 110, '2026-04-07 15:30:20'),
(69, 'Kagitingan', 16, 20, 4997, 85.98, 68.78, 92.50, 20, '2026-04-07 15:30:20'),
(70, 'Kayang Extension', 91, 36, 11529, 67.65, 45.36, 93.97, 13, '2026-04-07 15:30:20'),
(71, 'Kayang-Hilltop', 119, 41, 6928, 74.52, 49.59, 76.63, 64, '2026-04-07 15:30:20'),
(72, 'Kayang-Kuribot', 119, 46, 12641, 94.89, 65.92, 87.18, 33, '2026-04-07 15:30:20'),
(73, 'Kias', 40, 50, 2302, 75.22, 52.33, 83.38, 47, '2026-04-07 15:30:20'),
(74, 'Legarda-Burnham-Kisad', 68, 13, 6348, 80.36, 55.36, 69.93, 84, '2026-04-07 15:30:20'),
(75, 'Liwanag-Loakan', 123, 21, 10788, 82.66, 87.58, 81.83, 50, '2026-04-07 15:30:20'),
(76, 'Loakan Apugan', 93, 23, 8476, 87.65, 41.73, 80.58, 52, '2026-04-07 15:30:20'),
(77, 'Loakan Proper', 92, 23, 9542, 67.82, 72.99, 51.20, 140, '2026-04-07 15:30:20'),
(78, 'Lopez Jaena', 31, 42, 11268, 86.18, 54.42, 86.52, 37, '2026-04-07 15:30:20'),
(79, 'Lourdes Subdivision', 96, 11, 7791, 68.59, 39.13, 66.46, 100, '2026-04-07 15:30:20'),
(80, 'Lourdes Subdivision Extension', 50, 31, 5114, 71.78, 43.41, 66.99, 95, '2026-04-07 15:30:20'),
(81, 'Lualhati', 33, 46, 770, 65.21, 93.78, 92.47, 21, '2026-04-07 15:30:20'),
(82, 'Lucnab', 72, 49, 4942, 72.15, 49.74, 88.54, 27, '2026-04-07 15:30:20'),
(83, 'Magsaysay Private Road', 27, 11, 4024, 90.42, 64.58, 55.67, 129, '2026-04-07 15:30:20'),
(84, 'Malcolm Square-Perfecto (Bgy. 3)', 134, 42, 2171, 85.99, 40.69, 84.72, 44, '2026-04-07 15:30:20'),
(85, 'Manuel A. Roxas', 29, 31, 13277, 92.81, 35.21, 79.72, 53, '2026-04-07 15:30:20'),
(86, 'Manuel Roxas', 117, 30, 8605, 78.11, 66.68, 74.50, 71, '2026-04-07 15:30:20'),
(87, 'Market Subdivision', 118, 50, 4235, 93.46, 94.80, 56.63, 124, '2026-04-07 15:30:20'),
(88, 'Middle Rock Quarry', 98, 10, 5316, 75.60, 82.27, 96.10, 6, '2026-04-07 15:30:20'),
(89, 'Military Cut-off', 61, 9, 2990, 83.88, 72.12, 66.92, 97, '2026-04-07 15:30:20'),
(90, 'Mines View', 113, 7, 11343, 80.22, 53.87, 65.09, 103, '2026-04-07 15:30:20'),
(91, 'Modern Site East', 71, 29, 9870, 92.08, 71.55, 73.42, 74, '2026-04-07 15:30:20'),
(92, 'Modern Site West', 77, 13, 2963, 76.50, 57.50, 96.38, 4, '2026-04-07 15:30:20'),
(93, 'MRR-Queen of Peace', 82, 59, 13371, 81.96, 46.38, 77.08, 61, '2026-04-07 15:30:20'),
(94, 'New Lucban', 22, 42, 15053, 93.55, 85.12, 68.47, 87, '2026-04-07 15:30:20'),
(95, 'North Drive', 60, 49, 9185, 84.94, 68.06, 94.85, 12, '2026-04-07 15:30:20'),
(96, 'North San Antonio', 124, 51, 2203, 72.58, 90.01, 91.18, 22, '2026-04-07 15:30:20'),
(97, 'NPC Area', 77, 6, 8951, 87.31, 31.85, 93.73, 15, '2026-04-07 15:30:20'),
(98, 'Outlook Drive', 71, 41, 10509, 79.72, 59.37, 87.76, 31, '2026-04-07 15:30:20'),
(99, 'Pacdal', 84, 36, 14370, 65.82, 53.58, 85.13, 43, '2026-04-07 15:30:20'),
(100, 'Padre Burgos', 83, 44, 9230, 93.00, 89.80, 88.42, 28, '2026-04-07 15:30:20'),
(101, 'Padre Zamora', 44, 53, 5294, 70.12, 88.16, 96.17, 5, '2026-04-07 15:30:20'),
(102, 'Palma-Urbano', 29, 49, 5212, 75.74, 85.13, 57.98, 122, '2026-04-07 15:30:20'),
(103, 'Phil-Am', 49, 61, 12703, 72.91, 87.09, 78.80, 57, '2026-04-07 15:30:20'),
(104, 'Pinget', 58, 6, 1009, 67.76, 53.30, 74.76, 69, '2026-04-07 15:30:20'),
(105, 'Pinsao Pilot Project', 75, 63, 5751, 90.00, 37.53, 53.81, 134, '2026-04-07 15:30:20'),
(106, 'Pinsao Proper', 20, 5, 14059, 79.64, 77.28, 58.33, 120, '2026-04-07 15:30:20'),
(107, 'Poliwes', 97, 59, 7521, 83.80, 77.35, 86.62, 35, '2026-04-07 15:30:20'),
(108, 'Pucsusan', 90, 56, 6347, 76.51, 78.63, 78.33, 58, '2026-04-07 15:30:20'),
(109, 'Quezon Hill', 99, 50, 10876, 70.16, 80.96, 69.41, 86, '2026-04-07 15:30:20'),
(110, 'Quezon Hill Proper', 95, 13, 10664, 94.26, 84.85, 64.08, 107, '2026-04-07 15:30:20'),
(111, 'Quirino Hill East', 127, 52, 3002, 78.54, 79.19, 70.60, 82, '2026-04-07 15:30:20'),
(112, 'Quirino Hill Middle', 120, 10, 12982, 91.55, 90.33, 97.33, 3, '2026-04-07 15:30:20'),
(113, 'Quirino Hill West', 32, 51, 6721, 87.88, 67.02, 76.86, 63, '2026-04-07 15:30:20'),
(114, 'Quirino-Magsaysay-Recto-Gayano', 25, 51, 9126, 82.08, 37.88, 93.10, 19, '2026-04-07 15:30:20'),
(115, 'Rock Quarry', 30, 61, 5062, 86.34, 71.91, 54.31, 132, '2026-04-07 15:30:20'),
(116, 'Saint Joseph Village', 76, 23, 15082, 93.54, 84.66, 66.80, 98, '2026-04-07 15:30:20'),
(117, 'Salud Mitra', 42, 10, 11818, 80.31, 48.76, 93.76, 14, '2026-04-07 15:30:20'),
(118, 'San Antonio Village', 98, 48, 8890, 83.44, 55.70, 56.38, 126, '2026-04-07 15:30:20'),
(119, 'San Luis Village', 72, 64, 8511, 85.66, 84.66, 56.65, 123, '2026-04-07 15:30:20'),
(120, 'San Roque Village', 35, 31, 10455, 65.43, 35.19, 67.11, 93, '2026-04-07 15:30:20'),
(121, 'San Vicente', 80, 43, 9344, 65.54, 50.84, 76.37, 67, '2026-04-07 15:30:20'),
(122, 'Santa Escolastica', 109, 21, 986, 74.88, 65.75, 86.57, 36, '2026-04-07 15:30:20'),
(123, 'Santo Rosario-Assumption', 34, 35, 1640, 90.44, 30.74, 74.63, 70, '2026-04-07 15:30:20'),
(124, 'Santo Tomas Proper', 78, 12, 15485, 84.16, 42.77, 53.18, 137, '2026-04-07 15:30:20'),
(125, 'Scout Barrio', 104, 35, 5390, 67.98, 63.73, 64.23, 106, '2026-04-07 15:30:20'),
(126, 'Session Road Area', 126, 49, 14149, 75.15, 92.51, 88.10, 30, '2026-04-07 15:30:20'),
(127, 'Slaughter House Area', 25, 7, 14455, 81.30, 90.18, 97.96, 1, '2026-04-07 15:30:20'),
(128, 'SLU-SVP Housing Village', 41, 10, 12637, 88.04, 56.79, 86.34, 39, '2026-04-07 15:30:20'),
(129, 'South Drive', 80, 33, 11261, 70.07, 74.99, 95.81, 8, '2026-04-07 15:30:20'),
(130, 'South San Antonio', 98, 41, 15072, 65.72, 43.36, 95.88, 7, '2026-04-07 15:30:20'),
(131, 'Tadiangan', 34, 61, 4129, 76.15, 38.65, 76.40, 65, '2026-04-07 15:30:20'),
(132, 'Teodora Alonzo', 57, 11, 7447, 65.21, 71.95, 59.89, 115, '2026-04-07 15:30:20'),
(133, 'Trancoville', 26, 56, 15045, 73.95, 67.84, 50.77, 141, '2026-04-07 15:30:20'),
(134, 'Tuba', 55, 42, 2130, 85.30, 33.74, 62.37, 112, '2026-04-07 15:30:20'),
(135, 'Tuba-tuba', 28, 53, 10743, 94.89, 90.61, 82.32, 48, '2026-04-07 15:30:20'),
(136, 'Upper Dagsian', 83, 54, 6999, 85.43, 36.79, 73.04, 75, '2026-04-07 15:30:20'),
(137, 'Upper Magsaysay', 25, 64, 11006, 80.80, 64.54, 53.71, 135, '2026-04-07 15:30:20'),
(138, 'Upper QM', 110, 48, 4630, 70.55, 36.45, 95.16, 10, '2026-04-07 15:30:20'),
(139, 'Upper Rock Quarry', 63, 17, 12905, 80.22, 33.67, 86.47, 38, '2026-04-07 15:30:20'),
(140, 'Upper Tuba', 90, 57, 7293, 84.79, 91.07, 84.51, 45, '2026-04-07 15:30:20'),
(141, 'Victoria Village', 108, 48, 4771, 72.70, 57.83, 67.84, 90, '2026-04-07 15:30:20'),
(142, 'West Quirino Hill', 83, 50, 1058, 92.96, 65.66, 95.45, 9, '2026-04-07 15:30:20');

-- --------------------------------------------------------

--
-- Table structure for table `certifications`
--

CREATE TABLE `certifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `guest_barangay` varchar(255) DEFAULT NULL,
  `certificate_code` varchar(50) NOT NULL,
  `category` varchar(50) DEFAULT 'general',
  `score` int(11) DEFAULT 0,
  `total_questions` int(11) DEFAULT 0,
  `percentage` decimal(5,2) DEFAULT 0.00,
  `passed` tinyint(1) DEFAULT 0,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certifications`
--

INSERT INTO `certifications` (`id`, `user_id`, `guest_email`, `guest_barangay`, `certificate_code`, `category`, `score`, `total_questions`, `percentage`, `passed`, `issued_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, NULL, 'CERT-RESP-001', 'general', 28, 30, 93.33, 1, '2026-01-15 02:00:00', '2027-01-15', '2026-01-15 02:00:00', '2026-04-07 15:30:20'),
(2, 14, NULL, NULL, 'CERT-RES-002', 'earthquake', 14, 15, 93.33, 1, '2026-02-10 06:30:00', '2027-02-10', '2026-02-10 06:30:00', '2026-04-07 15:30:20'),
(3, 16, NULL, NULL, 'CERT-RES-003', 'fire', 13, 15, 86.67, 1, '2026-03-05 01:15:00', '2027-03-05', '2026-03-05 01:15:00', '2026-04-07 15:30:20'),
(4, 17, NULL, NULL, 'CERT-RES-004', 'typhoon', 15, 15, 100.00, 1, '2026-03-20 08:45:00', '2027-03-20', '2026-03-20 08:45:00', '2026-04-07 15:30:20'),
(5, 1, NULL, NULL, 'CERT-ADM-005', 'general', 30, 30, 100.00, 1, '2026-01-05 00:00:00', '2027-01-05', '2026-01-05 00:00:00', '2026-04-07 15:30:20');

-- --------------------------------------------------------

--
-- Table structure for table `checklist_items`
--

CREATE TABLE `checklist_items` (
  `id` int(11) NOT NULL,
  `category` enum('emergency_kit','family_plan','home_safety','communication','training') NOT NULL,
  `item_text` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('high','medium','low') DEFAULT 'medium',
  `points` int(11) DEFAULT 5,
  `display_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checklist_items`
--

INSERT INTO `checklist_items` (`id`, `category`, `item_text`, `description`, `priority`, `points`, `display_order`, `active`, `created_at`) VALUES
(1, 'emergency_kit', 'Store 3 days drinking water (4L per person/day)', 'Calculate total water for household', 'high', 10, 1, 1, '2026-02-14 04:28:33'),
(2, 'emergency_kit', 'Stock non-perishable food for 3 days', 'Canned goods, instant noodles, crackers', 'high', 10, 2, 1, '2026-02-14 04:28:33'),
(3, 'emergency_kit', 'Prepare flashlight and extra batteries', 'Test regularly, keep batteries fresh', 'high', 5, 3, 1, '2026-02-14 04:28:33'),
(4, 'emergency_kit', 'Pack first aid kit with medicines', 'Bandages, antiseptic, pain relievers', 'high', 10, 4, 1, '2026-02-14 04:28:33'),
(5, 'emergency_kit', 'Keep battery-powered radio', 'For emergency broadcasts', 'medium', 5, 5, 1, '2026-02-14 04:28:33'),
(6, 'emergency_kit', 'Store documents in waterproof container', 'IDs, insurance, medical records', 'high', 10, 6, 1, '2026-02-14 04:28:33'),
(7, 'emergency_kit', 'Pack extra clothes and blankets', 'Include rain gear, warm clothing', 'medium', 5, 7, 1, '2026-02-14 04:28:33'),
(8, 'emergency_kit', 'Keep cash in small bills', 'ATMs may not work', 'medium', 5, 8, 1, '2026-02-14 04:28:33'),
(9, 'emergency_kit', 'Include hygiene items', 'Toilet paper, soap, toothbrush', 'medium', 5, 9, 1, '2026-02-14 04:28:33'),
(10, 'emergency_kit', 'Pack whistle for signaling', 'Easier than shouting', 'low', 3, 10, 1, '2026-02-14 04:28:33'),
(11, 'family_plan', 'Create emergency contact list', 'All family and out-of-town contacts', 'high', 10, 1, 1, '2026-02-14 04:28:33'),
(12, 'family_plan', 'Identify evacuation routes', 'Plan primary and alternate routes', 'high', 10, 2, 1, '2026-02-14 04:28:33'),
(13, 'family_plan', 'Designate family meeting point', 'Choose known location', 'high', 10, 3, 1, '2026-02-14 04:28:33'),
(14, 'family_plan', 'Teach children to call for help', 'Emergency numbers and when to use', 'high', 10, 4, 1, '2026-02-14 04:28:33'),
(15, 'family_plan', 'Discuss disaster plans with family', 'Cover all disaster types', 'high', 10, 5, 1, '2026-02-14 04:28:33'),
(16, 'family_plan', 'Plan for special needs members', 'Elderly, disabled, infants, pets', 'medium', 5, 6, 1, '2026-02-14 04:28:33'),
(17, 'family_plan', 'Share plan with neighbors', 'Exchange info, agree to help', 'medium', 5, 7, 1, '2026-02-14 04:28:33'),
(18, 'home_safety', 'Secure heavy furniture to walls', 'Prevent toppling in earthquakes', 'high', 10, 1, 1, '2026-02-14 04:28:33'),
(19, 'home_safety', 'Know utility shut-off locations', 'Water, gas, electricity switches', 'high', 10, 2, 1, '2026-02-14 04:28:33'),
(20, 'home_safety', 'Install smoke detectors', 'Test monthly, replace batteries yearly', 'high', 10, 3, 1, '2026-02-14 04:28:33'),
(21, 'home_safety', 'Keep fire extinguisher accessible', 'Learn P.A.S.S. method', 'high', 10, 4, 1, '2026-02-14 04:28:33'),
(22, 'home_safety', 'Clear gutters and drainage', 'Prevent flooding', 'medium', 5, 5, 1, '2026-02-14 04:28:33'),
(23, 'home_safety', 'Trim trees near house', 'Remove branches over roof', 'medium', 5, 6, 1, '2026-02-14 04:28:33'),
(24, 'home_safety', 'Check for home hazards', 'Fix electrical, secure shelves', 'medium', 5, 7, 1, '2026-02-14 04:28:33'),
(25, 'communication', 'Save emergency numbers in phone', 'MDRRMO, police, fire, hospital', 'high', 5, 1, 1, '2026-02-14 04:28:33'),
(26, 'communication', 'Register for emergency alerts', 'SMS/email warnings', 'high', 10, 2, 1, '2026-02-14 04:28:33'),
(27, 'communication', 'Identify out-of-town contact', 'For relaying family messages', 'medium', 5, 3, 1, '2026-02-14 04:28:33'),
(28, 'communication', 'Know community warning signals', 'Learn what alarms mean', 'medium', 5, 4, 1, '2026-02-14 04:28:33'),
(29, 'communication', 'Join barangay emergency group', 'Community preparedness', 'low', 5, 5, 1, '2026-02-14 04:28:33'),
(30, 'training', 'Attend disaster drill', 'Earthquake, fire, or evacuation', 'high', 15, 1, 1, '2026-02-14 04:28:33'),
(31, 'training', 'Learn basic first aid', 'Take course or watch videos', 'high', 15, 2, 1, '2026-02-14 04:28:33'),
(32, 'training', 'Practice evacuation routes', 'Family drill twice a year', 'medium', 10, 3, 1, '2026-02-14 04:28:33'),
(33, 'training', 'Learn CPR', 'Take certified training', 'medium', 15, 4, 1, '2026-02-14 04:28:33'),
(34, 'training', 'Review emergency procedures', 'Refresh every 6 months', 'medium', 5, 5, 1, '2026-02-14 04:28:33'),
(35, 'training', 'Take community safety workshop', 'Learn advanced skills', 'low', 10, 6, 1, '2026-02-14 04:28:33');

-- --------------------------------------------------------

--
-- Table structure for table `drills`
--

CREATE TABLE `drills` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `instructions` text NOT NULL,
  `duration_minutes` int(11) DEFAULT 30,
  `status` enum('draft','published') DEFAULT 'draft',
  `is_archived` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `drill_date` date DEFAULT NULL,
  `drill_time` time DEFAULT NULL,
  `drill_place` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drills`
--

INSERT INTO `drills` (`id`, `title`, `description`, `barangay`, `instructions`, `duration_minutes`, `status`, `is_archived`, `created_by`, `created_at`, `drill_date`, `drill_time`, `drill_place`) VALUES
(1, 'go to schpo', 'siod', 'Dizon Subdivision', 'bdhdbe', 20, 'published', 0, 1, '2026-01-29 02:38:23', '2026-01-30', NULL, NULL),
(2, 'DRILL', 'go to evac area', 'Quezon Hill Proper', 'dsfv', 20, 'published', 1, 1, '2026-01-30 04:24:24', '2026-02-01', NULL, NULL),
(3, 'DRILL', 'go to evac area', 'Kagitingan', 'dsfv', 20, 'published', 1, 1, '2026-01-30 04:26:17', '2026-02-01', NULL, NULL),
(4, 'Earthquake Preparedness Drill', 'Promoting community resilience by participating in community drills', 'Dizon Subdivision', '1. Breathe\r\n2. Dapa\r\n3. Hold\r\netc.,', 30, 'published', 1, 1, '2026-02-04 07:27:12', '2026-02-06', NULL, NULL),
(5, 'fire drill', 'drop, stop , roll', 'Quezon Hill Proper', '1. set yourself on fire\r\n2.', 30, 'published', 1, 2, '2026-02-06 10:58:32', '2026-02-08', NULL, NULL),
(6, 'Fire Preparedness', 'Fire Preparedness month y\'all', 'Dizon Subdivision', 'stop\r\ndrop\r\nroll \r\njump', 30, 'published', 1, 17, '2026-04-04 08:22:58', '2026-04-05', NULL, NULL),
(7, 'Fire Preparedness', 'Fire Preparedness month y\'all', 'Kagitingan', 'stop\r\ndrop\r\nroll \r\njump', 30, 'published', 1, 17, '2026-04-04 08:23:04', '2026-04-06', NULL, NULL),
(8, 'Landslide Awareness', 'Let\'s talk about hazards and what to do when you feel like ...', 'Kagitingan', 'First stay calm \r\n....', 30, 'published', 1, 14, '2026-04-06 06:16:00', '2026-03-31', NULL, NULL),
(9, 'Earthquake preparedness drill', 'duck cover and hold', 'Kagitingan', '', 30, 'published', 0, 14, '2026-04-07 15:45:14', '2026-04-08', NULL, NULL),
(10, 'Earthquake Preparedness drill', 'duck cover and hold', 'Dizon Subdivision', '', 30, 'published', 0, 17, '2026-04-07 15:58:25', '2026-04-30', NULL, NULL),
(11, 'Fire Preparedness drill', 'duck cover and hold', 'Dizon Subdivision', '', 30, 'published', 0, 17, '2026-04-07 15:58:49', '2026-05-05', NULL, NULL),
(12, 'Flood Preparedness drill', 'duck cover and hold', 'Dizon Subdivision', '', 30, 'published', 1, 17, '2026-04-07 15:59:28', '2026-04-07', NULL, NULL),
(13, 'Earthquake Prep', 'drop', 'Quezon Hill Proper', '', 30, 'published', 0, 16, '2026-04-07 16:11:05', '2026-04-08', NULL, NULL),
(15, 'Flood Prepareness', 'swim swim swim', 'Quezon Hill Proper', 'the water\'s getting colder', 30, 'published', 0, 16, '2026-04-07 16:35:40', '2026-04-10', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `drill_participants`
--

CREATE TABLE `drill_participants` (
  `id` int(11) NOT NULL,
  `drill_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `entered` tinyint(1) DEFAULT 0,
  `status` enum('not_started','in_progress','completed','failed') DEFAULT 'not_started',
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drill_participants`
--

INSERT INTO `drill_participants` (`id`, `drill_id`, `name`, `email`, `barangay`, `phone`, `joined_at`, `entered`, `status`, `started_at`, `finished_at`) VALUES
(1, 1, 'adsw', 'as@gmail.com', 'Bonifacio-Caguioa-Rimando', '09445552323', '2026-01-29 02:45:38', 0, 'not_started', NULL, NULL),
(2, 1, 'darna', 'darna@gmail.com', 'Bayan Park Village', '09445552323', '2026-01-29 02:47:44', 0, 'not_started', NULL, NULL),
(3, 1, 'Ellen test', 'ellen@gmail.com', 'Bayan Park Village', '09445552323', '2026-01-29 03:00:27', 0, 'not_started', NULL, NULL),
(4, 1, 'shet', 'shet@m.c', 'BGH Compound', '09445552323', '2026-01-29 03:02:21', 0, 'not_started', NULL, NULL),
(5, 1, 'helly', 'h@gmail.com', 'BGH Compound', '09445552323', '2026-01-29 03:12:44', 0, 'not_started', NULL, NULL),
(6, 3, 'Coke', 'coke@gmail.com', 'Kayang Extension', '09445552323', '2026-01-31 04:00:58', 0, 'not_started', NULL, NULL),
(7, 3, 'Rose Latte', 'rl@gmail.com', 'Guisad Central', '09445552323', '2026-02-01 15:27:45', 0, 'not_started', NULL, NULL),
(8, 3, 'Sharla Aragon', 'sha@gmail.com', 'Guisad Central', '09445552323', '2026-02-02 05:35:36', 0, 'not_started', NULL, NULL),
(9, 3, 'Jasmin Estipular', 'jazz@gmail.com', 'Kayang Extension', '09445552323', '2026-02-03 04:49:37', 0, 'not_started', NULL, NULL),
(10, 3, 'Zaira', 'zai@gmail.com', 'Bayan Park East', '03964567894', '2026-02-04 07:07:20', 0, 'not_started', NULL, NULL),
(11, 4, 'Cadhla Zaira Cruz', 'czaile2404@gmail.com', 'Bakakeng Central', '09563907620', '2026-02-04 14:15:08', 0, 'not_started', NULL, NULL),
(12, 4, 'Zaira Kalea', 'cadhlazairakalea@gmail.com', 'Bayan Park Village', '0994238290', '2026-02-04 14:16:45', 0, 'not_started', NULL, NULL),
(13, 4, 'Cali Aquino', 'cali@gmail.com', 'Saint Joseph Village', '09445552323', '2026-02-05 13:50:24', 0, 'not_started', NULL, NULL),
(14, 1, 'Noemi Mae', 'nayumi@gmail.com', 'Saint Joseph Village', '09445552323', '2026-02-06 06:28:08', 0, 'not_started', NULL, NULL),
(15, 4, 'Kalea Cruz', 'czaile04@gmail.com', 'Abanao-Zandueta-Kayong-Chugum', '09076083994', '2026-02-06 10:34:15', 0, 'not_started', NULL, NULL),
(16, 5, 'claide dait', 'cd@gmail.com', 'Brookside', '09563907620', '2026-02-06 10:59:35', 0, 'not_started', NULL, NULL),
(17, 5, 'Pink Pantherese', 'pink@gmail.com', 'Pacdal', '09445552323', '2026-02-11 14:38:28', 0, 'not_started', NULL, NULL),
(18, 5, 'wer', 'wer@gmail.com', 'Abanao-Zandueta-Kayong-Chugum', '09563907620', '2026-02-14 02:01:03', 0, 'not_started', NULL, NULL),
(19, 5, 'kobe paras', 'kobe@gmail.com', 'Dizon Subdivision', '0912345689', '2026-02-24 15:19:20', 0, 'not_started', NULL, NULL),
(20, 7, 'Kitty XO', 'kitty@gmail.com', 'Dizon Subdivision', '09784561478', '2026-04-04 08:24:05', 1, 'not_started', NULL, NULL),
(21, 7, 'Min ho', 'minho@gmail.com', 'Dizon Subdivision', '039122583456', '2026-04-04 08:24:54', 1, 'not_started', NULL, NULL),
(22, 7, 'Gloria San Jose', 'gloria@gmail.com', 'Dizon Subdivision', '09782564298', '2026-04-04 08:26:02', 1, 'not_started', NULL, NULL),
(23, 7, 'Mitchell Pritchett', 'mitch@gmail.com', 'Dizon Subdivision', '09458532564', '2026-04-04 08:26:38', 1, 'not_started', NULL, NULL),
(24, 7, 'Joen John', 'john@gmail.com', 'Dizon Subdivision', '0956357945', '2026-04-04 08:27:14', 1, 'not_started', NULL, NULL),
(25, 7, 'Alex Dunphy', 'alex@gmail.com', 'Dizon Subdivision', '094565684562', '2026-04-04 08:27:50', 1, 'not_started', NULL, NULL),
(26, 8, 'Frank Ocean', 'sea@gmail.com', 'Kagitingan', '', '2026-04-06 06:16:52', 1, 'completed', NULL, '2026-04-06 06:33:00'),
(27, 8, 'Olivia Dean', 'dean@gmail.com', 'Kagitingan', '', '2026-04-06 06:17:20', 1, 'completed', '2026-04-06 07:17:44', '2026-04-06 07:17:52'),
(28, 8, 'Taylor Swift', 'tay@gmail.com', 'Kagitingan', '', '2026-04-06 06:17:36', 1, 'completed', '2026-04-06 07:17:42', '2026-04-06 07:17:55'),
(29, 8, 'Sabrina Carpenter', 'sabrina@gmail.com', 'Kagitingan', '', '2026-04-06 06:17:55', 1, 'completed', '2026-04-07 15:23:31', '2026-04-07 15:23:33'),
(30, 8, 'Leon thomas', 'leon@gmail.com', 'Kagitingan', '', '2026-04-06 06:18:11', 1, 'completed', '2026-04-06 07:17:31', '2026-04-06 07:17:32'),
(31, 8, 'lana del ray', 'lana@gmail.com', 'Kagitingan', '', '2026-04-06 06:55:31', 1, 'completed', '2026-04-06 07:17:27', '2026-04-06 07:17:29'),
(32, 8, 'Cinnamon', 'cinnamon@gmail.com', 'Kagitingan', '', '2026-04-06 06:56:13', 1, 'completed', '2026-04-06 07:17:14', '2026-04-06 07:17:15'),
(33, 8, 'Benee', 'benee@gmail.com', 'Kagitingan', '', '2026-04-06 06:56:30', 1, 'completed', '2026-04-06 07:17:21', '2026-04-06 07:17:22'),
(34, 8, 'Lany', 'lany@gmail.com', 'Kagitingan', '', '2026-04-07 15:31:22', 1, 'completed', '2026-04-07 15:31:51', '2026-04-07 15:31:55'),
(35, 9, 'mimasaur', 'raw@gmail.com', '', '', '2026-04-07 15:47:34', 0, 'not_started', NULL, NULL),
(36, 9, 'leon', 'leon@g.com', '', '', '2026-04-07 15:47:49', 1, 'completed', '2026-04-07 15:48:56', '2026-04-07 15:49:31'),
(37, 9, 'Redono', 'r@g.c', '', '', '2026-04-07 15:48:04', 1, 'completed', '2026-04-07 15:48:51', '2026-04-07 15:48:54'),
(38, 9, 'Sandra', 'sa@g.c', '', '', '2026-04-07 15:48:25', 1, 'completed', '2026-04-07 15:48:47', '2026-04-07 15:48:48'),
(39, 8, 'Princeton', 'pr@gm.com', 'Kagitingan', '', '2026-04-07 16:33:41', 0, 'not_started', NULL, NULL),
(40, 7, 'riane', 'r@g.com', '', '', '2026-04-07 16:34:06', 0, 'not_started', NULL, NULL),
(41, 9, 'Billy', 'b@g.com', '', '', '2026-04-07 16:36:56', 1, 'completed', '2026-04-07 16:39:38', '2026-04-07 16:39:39'),
(42, 13, 'Eilish', 'ei@gma.c', '', '', '2026-04-07 16:37:10', 0, 'not_started', NULL, NULL),
(43, 9, 'kian', 'kian@g.com', '', '', '2026-04-07 16:37:37', 1, 'completed', '2026-04-07 16:39:30', '2026-04-07 16:39:32'),
(44, 13, 'relish', 'rel@g.c', 'Kagitingan', '', '2026-04-07 16:38:03', 0, 'not_started', NULL, NULL),
(45, 13, 'yana', 'y@gmail.c', 'Dizon Subdivision', '', '2026-04-07 17:07:41', 0, 'not_started', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `drill_participations`
--

CREATE TABLE `drill_participations` (
  `id` int(11) NOT NULL,
  `drill_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `status` enum('not_started','in_progress','completed','failed') DEFAULT 'not_started',
  `score` int(11) DEFAULT 0,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `entered` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drill_participations`
--

INSERT INTO `drill_participations` (`id`, `drill_id`, `user_id`, `name`, `email`, `barangay`, `phone`, `started_at`, `finished_at`, `status`, `score`, `verified_by`, `verified_at`, `entered`) VALUES
(13, 1, 1, NULL, NULL, 'City Hall', NULL, '2026-01-29 03:18:32', '2026-01-29 03:18:41', 'completed', 100, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `emergencies`
--

CREATE TABLE `emergencies` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('pending','responding','resolved','handled','spam','false') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emergencies`
--

INSERT INTO `emergencies` (`id`, `user_id`, `type`, `description`, `latitude`, `longitude`, `address`, `photo`, `status`, `created_at`) VALUES
(1, 2, 'Flood', 'omg there\'s a pool inside the school', 16.40841059, 120.59782750, 'University of the Cordilleras, Governor Pack Road, Purok 8, Session Road, District 10, Central Business District, Baguio, Cordillera Administrative Region, 2600, Philippines', NULL, 'handled', '2026-01-30 04:28:59'),
(2, 2, 'Flood', 'oh no whats hafening', 16.40274857, 120.64721405, 'Baguio-Itogon Road, Tuap, Namnama, Upper Mangga, Tuding, Benguet, Cordillera Administrative Region, 2604, Philippines', NULL, 'handled', '2026-02-02 16:03:14'),
(3, 2, 'Flood', 'oh no whats hafening', 16.40274857, 120.64721405, 'Baguio-Itogon Road, Tuap, Namnama, Upper Mangga, Tuding, Benguet, Cordillera Administrative Region, 2604, Philippines', 'emergency_37_1770048236.jpg', 'handled', '2026-02-02 16:03:56'),
(4, 2, 'Flood', 'oh no whats hafening', 16.40274857, 120.64721405, 'Baguio-Itogon Road, Tuap, Namnama, Upper Mangga, Tuding, Benguet, Cordillera Administrative Region, 2604, Philippines', 'emergency_38_1770048305.jpg', 'handled', '2026-02-02 16:05:05'),
(5, 2, 'Landslide', 'the lupa is making guho', 16.40236248, 120.64788522, 'Session Rd., Baguio City', 'emergency_39_1770095532.png', 'handled', '2026-02-03 05:12:12'),
(6, 2, 'Landslide', 'nahulog', NULL, NULL, 'University of the Cordilleras, Governor Pack Road, Purok 8, Session Road, District 10, Central Business District, Baguio, Cordillera Administrative Region, 2600, Philippines', NULL, 'spam', '2026-02-04 03:12:01'),
(7, 17, 'Earthquake (Level 1 Weak)', 'rattle rattle', 16.40273498, 120.64713296, 'Baguio-Itogon Road, Namnama, Upper Mangga, Tuding, Benguet, Cordillera Administrative Region, 2604, Philippines', NULL, 'pending', '2026-04-04 08:18:39'),
(8, 17, 'Fire (Level 3 Out of Control)', 'soafer massive', 16.40280057, 120.64709733, 'Baguio-Itogon Road, Namnama, Upper Mangga, Tuding, Benguet, Cordillera Administrative Region, 2604, Philippines', NULL, 'pending', '2026-04-04 08:19:22'),
(9, 17, 'Flood (Knee height)', 'mini swimming pool na ang atake', 16.40279911, 120.64700259, 'Baguio-Itogon Road, Namnama, Upper Mangga, Tuding, Benguet, Cordillera Administrative Region, 2604, Philippines', NULL, 'pending', '2026-04-04 08:19:48'),
(10, 17, 'Landslide (Level 2 Significant)', 'test reports only not manifesting anything', 16.40273709, 120.64713886, 'Baguio-Itogon Road, Namnama, Upper Mangga, Tuding, Benguet, Cordillera Administrative Region, 2604, Philippines', NULL, 'pending', '2026-04-04 08:20:15'),
(11, 17, 'Road Blockage (Level 3 Total)', 'tree fell down', 16.40267989, 120.64710608, 'Bua, Gumatdang, Benguet, Cordillera Administrative Region, 2604, Philippines', NULL, 'pending', '2026-04-04 08:20:45'),
(12, 14, 'Road Blockage (Level 1 Partial)', 'Drama on the road', 16.40859058, 120.59778822, 'University of the Cordilleras Gymnasium, Harrison Road, Purok 8, Session Road, District 10, Central Business District, Baguio, Cordillera Administrative Region, 2600, Philippines', NULL, 'pending', '2026-04-06 07:19:14'),
(13, 14, 'Flood (Waist height)', 'Waist height water on the overpass, use your imagination', 16.40858858, 120.59778808, 'University of the Cordilleras Gymnasium, Harrison Road, Purok 8, Session Road, District 10, Central Business District, Baguio, Cordillera Administrative Region, 2600, Philippines', NULL, 'pending', '2026-04-06 07:19:53');

-- --------------------------------------------------------

--
-- Table structure for table `evacuation_centers`
--

CREATE TABLE `evacuation_centers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `barangay` varchar(100) DEFAULT 'Unknown Barangay',
  `type` varchar(50) DEFAULT 'other',
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evacuation_centers`
--

INSERT INTO `evacuation_centers` (`id`, `name`, `barangay`, `type`, `lat`, `lng`, `created_at`) VALUES
(1, 'Pines City Colleges', 'Happy Homes', 'school', 16.42645793, 120.59496490, '2026-01-05 23:57:46'),
(2, 'BSBT College, Inc.', 'Happy Homes', 'school', 16.42991521, 120.59659582, '2026-01-05 23:57:45'),
(3, 'Saint Louis University', 'A. Bonifacio-Caguioa-Rimando (ABCR)', 'school', 16.41849915, 120.59767112, '2026-01-05 23:57:46'),
(4, 'Baguio Central University', 'A. Bonifacio-Caguioa-Rimando (ABCR)', 'school', 16.41738785, 120.59651289, '2026-01-05 23:57:45'),
(5, 'Baguio Baptist Learning Center', 'Alfonso Tabora', 'school', 16.42521201, 120.59390396, '2026-01-05 23:57:45'),
(6, 'Logos Mission School', 'Andres Bonifacio (Lower Bokawkan)', 'school', 16.41866358, 120.58556284, '2026-01-05 23:57:45'),
(7, 'Joaquin Smith National High School', 'Asin Road', 'school', 16.40574800, 120.56312491, '2026-01-05 23:57:46'),
(8, 'Pacday Quirino Elementary School', 'Asin Road', 'school', 16.40415625, 120.56693926, '2026-01-05 23:57:45'),
(9, 'Irisan Children\'s Learning Center', 'Asin Road', 'school', 16.40942325, 120.56472010, '2026-01-05 23:57:45'),
(10, 'Glad Tidings Integrated School', 'Asin Road', 'school', 16.40187239, 120.56988182, '2026-01-05 23:57:45'),
(11, 'United Church of Christ In The Philippines', 'Abanao-Zandueta-Kayong-Chugum-Otek (AZKCO)', 'other', 16.41290354, 120.59308334, '2026-01-05 23:57:46'),
(12, 'San Pablo Major Seminary', 'Bakakeng Central', 'school', 16.39796195, 120.57704901, '2026-01-05 23:57:46'),
(13, 'St. Martin School, Inc.', 'Bakakeng Central', 'school', 16.40015143, 120.57901397, '2026-01-05 23:57:45'),
(14, 'Crystal Cave Elementary School', 'Bakakeng Central', 'school', 16.39735614, 120.57498738, '2026-01-05 23:57:46'),
(15, 'St. Louis University Mary Heights Campus', 'Bakakeng Norte', 'school', 16.38436785, 120.59345333, '2026-01-05 23:57:46'),
(16, 'Daily International School', 'Bakakeng Norte', 'school', 16.39011175, 120.59139104, '2026-01-05 23:57:45'),
(17, 'Bakakeng National High School', 'Bakakeng Norte', 'school', 16.39076753, 120.59409939, '2026-01-05 23:57:46'),
(18, 'Baguio City National High School (Main)', 'Legarda-Burnham-Kisad', 'school', 16.40744356, 120.59724422, '2026-01-05 23:57:46'),
(19, 'University of the Cordilleras', 'Legarda-Burnham-Kisad', 'school', 16.40873113, 120.59734517, '2026-01-05 23:57:46'),
(20, 'Lucban Elementary School', 'Camdas Subdivision', 'school', 16.42692939, 120.59362244, '2026-01-05 23:57:46'),
(21, 'Our Lady Of Mt. Carmel Montessori', 'Camdas Subdivision', 'school', 16.42704285, 120.59293773, '2026-01-05 23:57:45'),
(22, 'Small World Christian School Foundation', 'Camp 7', 'school', 16.38463484, 120.59753204, '2026-01-05 23:57:45'),
(23, 'Metropolitan Baguio Christian Academy', 'Camp 7', 'school', 16.38841989, 120.60324979, '2026-01-05 23:57:45'),
(24, 'The School of St. Isidorus', 'Camp 7', 'school', 16.39146921, 120.60467765, '2026-01-05 23:57:45'),
(25, 'Remant International College', 'Camp 7', 'school', 16.38902307, 120.59926335, '2026-01-05 23:57:46'),
(26, 'Doña Aurora H. Bueno Elementary School', 'Camp 8', 'school', 16.39806308, 120.60106805, '2026-01-05 23:57:46'),
(27, 'Saint Louis School, Inc. (Center) HS', 'Campo Filipino', 'school', 16.41451965, 120.58737022, '2026-01-05 23:57:45'),
(28, 'Happy Hallow Elementary School', 'Happy Hallow', 'school', 16.40247457, 120.62509626, '2026-01-05 23:57:46'),
(29, 'Andres Bonifacio Elementary School', 'Cresencia Village', 'school', 16.41964860, 120.58764522, '2026-01-05 23:57:46'),
(30, 'School', 'Cresencia Village', 'school', 16.41992493, 120.58768345, '2026-01-05 23:57:45'),
(31, 'Guisad Valley National High School', 'Cresencia Village', 'school', 16.41906701, 120.58721665, '2026-01-05 23:57:46'),
(32, 'School of Thoughts Integrated Learning Experience', 'Dizon Subdivision', 'school', 16.42613616, 120.59025125, '2026-01-05 23:57:45'),
(33, 'Christian Legacy Academy', 'Dominican Hill-Mirador', 'school', 16.40532055, 120.58461909, '2026-01-05 23:57:46'),
(34, 'Crescent Valley Christian Academy', 'Dontogan', 'school', 16.37702958, 120.57038156, '2026-01-05 23:57:46'),
(35, 'Dontogan Elementary School', 'Dontogan', 'school', 16.38423138, 120.57141764, '2026-01-05 23:57:46'),
(36, 'Doña Nicasia J. Puyat Elementary School', 'Modern Site, East', 'school', 16.42179112, 120.60484296, '2026-01-05 23:57:46'),
(37, 'Bright Hope Room for Growth Inc', 'Engineers\' Hill', 'school', 16.40878910, 120.60277078, '2026-01-05 23:57:45'),
(38, 'Baguio City Science Foundation', 'Cabinet Hill-Teacher\'s Camp', 'school', 16.40826937, 120.60363103, '2026-01-05 23:57:45'),
(39, 'Fairview Elementary School', 'Fairview Village', 'school', 16.41593112, 120.58375723, '2026-01-05 23:57:45'),
(40, 'STEP Learning Center', 'Ferdinand', 'school', 16.40160223, 120.59080427, '2026-01-05 23:57:45'),
(41, 'Philippine Military Academy', 'Fort Del Pilar', 'school', 16.36136300, 120.61999008, '2026-01-05 23:57:45'),
(42, 'Jose P. Rizal Elementary School', 'Gibraltar', 'school', 16.41662848, 120.61622660, '2026-01-05 23:57:46'),
(43, 'Gibraltar Elementary School', 'Gibraltar', 'school', 16.41859856, 120.62291564, '2026-01-05 23:57:46'),
(44, 'Easter College Inc.', 'Pinget', 'school', 16.42398755, 120.58704526, '2026-01-05 23:57:45'),
(45, 'Happy Hallow National High School', 'Happy Hallow', 'school', 16.39748851, 120.63008156, '2026-01-05 23:57:46'),
(46, 'Baguio College of Technology', 'Harrison-Claudio Carantes', 'school', 16.41222689, 120.59631001, '2026-01-05 23:57:45'),
(47, 'Data Center College of the Philippines of Baguio City, Inc.', 'Holyghost Proper', 'school', 16.41516630, 120.59948787, '2026-01-05 23:57:45'),
(48, 'Vision Educational Center', 'Holy Ghost Extension', 'school', 16.41754949, 120.60316097, '2026-01-05 23:57:45'),
(49, 'Holyghost Extension Elementary School', 'Holyghost Extension', 'school', 16.41804507, 120.60455739, '2026-01-05 23:57:45'),
(50, 'Don Mariano Marcos Elementary School', 'Brookside', 'school', 16.42122607, 120.60178314, '2026-01-05 23:57:46'),
(51, 'Roxas National High School', 'Imelda R. Marcos', 'school', 16.39977265, 120.58686257, '2026-01-05 23:57:46'),
(52, 'Brent International School', 'Imelda Village', 'school', 16.41451748, 120.60364142, '2026-01-05 23:57:45'),
(53, 'Irisan Elementary School', 'Irisan', 'school', 16.41072599, 120.56031529, '2026-01-05 23:57:46'),
(54, 'Philippine Science High School', 'Irisan', 'school', 16.41648212, 120.56216008, '2026-01-05 23:57:45'),
(55, 'Westville School', 'Irisan', 'school', 16.41692164, 120.55778684, '2026-01-05 23:57:45'),
(56, 'Living Epistle Christian Academy Baguio', 'Irisan', 'school', 16.41640052, 120.55532788, '2026-01-05 23:57:45'),
(57, 'Elpidio Quirino Elementary School', 'Irisan', 'school', 16.42942245, 120.54984802, '2026-01-05 23:57:46'),
(58, 'Irisan National High School', 'Irisan', 'school', 16.42940265, 120.54901080, '2026-01-05 23:57:46'),
(59, 'Educare Learning Center', 'Irisan', 'school', 16.42562288, 120.54994443, '2026-01-05 23:57:45'),
(60, 'Pines Faith Mountain Academy', 'Irisan', 'school', 16.41374047, 120.57198701, '2026-01-05 23:57:45'),
(61, 'San Carlos Heights Elementary School', 'Irisan', 'school', 16.41432483, 120.56348221, '2026-01-05 23:57:45'),
(62, 'Cypress Christian Foundation School', 'Irisan', 'school', 16.41004713, 120.56770196, '2026-01-05 23:57:45'),
(63, 'Androgynous Heritage School', 'Irisan', 'school', 16.41478315, 120.56880747, '2026-01-05 23:57:45'),
(64, 'Saint Louis University Laboratory Elementary School', 'Kabayanihan', 'school', 16.41373564, 120.59864612, '2026-01-05 23:57:46'),
(65, 'Saint Louis School, Inc. (Center) Elem', 'Kabayanihan', 'school', 16.41351726, 120.59796934, '2026-01-05 23:57:46'),
(66, 'St. Vincent de Ferrer Learning Center', 'Kayang Extension', 'school', 16.41453651, 120.58832758, '2026-01-05 23:57:45'),
(67, 'Josefa Carino Elementary School', 'Kayang Extension', 'school', 16.41387766, 120.59054667, '2026-01-05 23:57:45'),
(68, 'Baguio Central School', 'Kayang Extension', 'school', 16.41348543, 120.59012607, '2026-01-05 23:57:45'),
(69, 'Pines City National High School', 'Kayang Extension', 'school', 16.41367537, 120.58950898, '2026-01-05 23:57:45'),
(70, 'Baguio Higher Ground Christian School', 'Loakan Proper', 'school', 16.38032171, 120.61610255, '2026-01-05 23:57:45'),
(71, 'University of The Cordilleras Laboratory School', 'Lourdes Subdivision Extension', 'school', 16.41193888, 120.58585476, '2026-01-05 23:57:45'),
(72, 'University of Baguio', 'General Luna, Lower', 'school', 16.41553563, 120.59759715, '2026-01-05 23:57:46'),
(73, 'AMA Computer College-Baguio City', 'Magsaysay, Lower', 'school', 16.42266760, 120.59259294, '2026-01-05 23:57:45'),
(74, 'Baguio City Academy Colleges', 'Magsaysay, Lower', 'school', 16.42264563, 120.59345550, '2026-01-05 23:57:45'),
(75, 'Gen. Emilio Aguinaldo Elementary School', 'General Emilio F. Aguinaldo (Lower QM)', 'school', 16.40855350, 120.58917280, '2026-01-05 23:57:46'),
(76, 'Lindawan National High School', 'Lucnab', 'school', 16.40503404, 120.62881401, '2026-01-05 23:57:45'),
(77, 'Quezon Hill Elementary School', 'Middle Quezon Hill', 'school', 16.41550083, 120.57450576, '2026-01-05 23:57:45'),
(78, 'Quezon Hill National High School', 'Middle Quezon Hill', 'school', 16.41539128, 120.57380665, '2026-01-05 23:57:46'),
(79, 'Quirino Hill Elementary School', 'Quirino Hill, Middle', 'school', 16.42911116, 120.59124508, '2026-01-05 23:57:46'),
(80, 'Manuel Quezon Elementary School', 'Military Cut-off', 'school', 16.40385027, 120.60300311, '2026-01-05 23:57:46'),
(81, 'Baguio City SPED Center', 'Military Cut-off', 'school', 16.40309157, 120.60453231, '2026-01-05 23:57:46'),
(82, 'Ridgeview Academy of Baguio', 'MRR-Queen Of Peace', 'school', 16.41210458, 120.58660014, '2026-01-05 23:57:45'),
(83, 'Free Believers in Christ Academy, Inc.', 'New Lucban', 'school', 16.42286263, 120.59782059, '2026-01-05 23:57:45'),
(84, 'Magsaysay Elementary School', 'Slaughter House Area', 'school', 16.42207052, 120.59480144, '2026-01-05 23:57:46'),
(85, 'Magsaysay National High School', 'Slaughter House Area', 'school', 16.42204833, 120.59428587, '2026-01-05 23:57:46'),
(86, 'Alfonso Tabora Elementary School', 'Slaughter House Area', 'school', 16.42262696, 120.59455920, '2026-01-05 23:57:46'),
(87, 'Disciples for Christ Independent School Foundation, Inc.', 'Lucnab', 'school', 16.40937911, 120.62720252, '2026-01-05 23:57:45'),
(88, 'Grace Bible Academy', 'Pacdal', 'school', 16.41830906, 120.61628975, '2026-01-05 23:57:45'),
(89, 'BCU Growing-up Learning Center', 'Padre Burgos', 'school', 16.42056988, 120.59179332, '2026-01-05 23:57:45'),
(90, 'Victory Baptist Church / Victory Baptist Academy', 'Palma Urbano', 'school', 16.41238978, 120.58856608, '2026-01-05 23:57:45'),
(91, 'Pinget National High School', 'Pinget', 'school', 16.43182372, 120.58295602, '2026-01-05 23:57:46'),
(92, 'Pinget Elementary School', 'Pinget', 'school', 16.43191866, 120.58355495, '2026-01-05 23:57:46'),
(93, 'Pinsao Elementary School', 'Pinsao Pilot Project', 'school', 16.42790201, 120.58116036, '2026-01-05 23:57:46'),
(94, 'Pinsao National High School', 'Pinsao Pilot Project', 'school', 16.42755921, 120.58184509, '2026-01-05 23:57:45'),
(95, 'Baguio Siloam Christian Academy, Inc.', 'Pinsao Proper', 'school', 16.42395319, 120.57714209, '2026-01-05 23:57:45'),
(96, 'Baguio Siloam Christian Academy', 'Pinsao Proper', 'school', 16.42366747, 120.57713722, '2026-01-05 23:57:45'),
(97, 'St. John Paul II Learning Center', 'Quezon Hill Proper', 'school', 16.41387078, 120.57916069, '2026-01-05 23:57:45'),
(98, 'Phases Learning Center', 'Quezon Hill Proper', 'school', 16.41422334, 120.57944081, '2026-01-05 23:57:45'),
(99, 'YMCA Preschool', 'Salud Mitra', 'school', 16.41100167, 120.59989371, '2026-01-05 23:57:45'),
(100, 'Apolinario Mabini Elementary School', 'Salud Mitra', 'school', 16.41038499, 120.60106536, '2026-01-05 23:57:46'),
(101, 'St. Francis Learners School', 'San Vicente', 'school', 16.39769749, 120.59465708, '2026-01-05 23:57:45'),
(102, 'Manuel Roxas Elementary School', 'Santo Rosario', 'school', 16.40031807, 120.58696461, '2026-01-05 23:57:45'),
(103, 'Grace Baptist Church & Schools of Baguio City, Inc.', 'Santo Rosario', 'school', 16.40049608, 120.58526366, '2026-01-05 23:57:46'),
(104, 'Cherubim School Inc.', 'Bakakeng Norte', 'school', 16.38848487, 120.59091918, '2026-01-05 23:57:45'),
(105, 'Academia De Sophia International', 'South  Drive', 'school', 16.40678023, 120.60620094, '2026-01-05 23:57:45'),
(106, 'Berkeley School, Inc.', 'Saint Joseph Village', 'school', 16.41558635, 120.61059595, '2026-01-05 23:57:46'),
(107, 'Baguio Pines Family Learning Center', 'Santo Tomas Proper', 'school', 16.38956316, 120.57638039, '2026-01-05 23:57:45'),
(108, 'Adiwang Elementary School', 'Dontogan', 'school', 16.38071901, 120.57676363, '2026-01-05 23:57:45'),
(109, 'Don Bosco School of Baguio City, Inc.', 'Trancoville', 'school', 16.42594910, 120.59748035, '2026-01-05 23:57:45'),
(110, 'Pines City Baptist Academy', 'Trancoville', 'school', 16.42391896, 120.59919652, '2026-01-05 23:57:45'),
(111, 'Shalom International School', 'Trancoville', 'school', 16.42483459, 120.60162708, '2026-01-05 23:57:45'),
(112, 'Bridges Tutorial And Learning Center', 'General Luna, Upper', 'school', 16.41342333, 120.60067672, '2026-01-05 23:57:45'),
(113, 'Baguio Achievers Academy', 'General Luna, Upper', 'school', 16.41363278, 120.60039823, '2026-01-05 23:57:45'),
(114, 'St. Elizabeth Montessori School of Baguio City, Inc.', 'Salud Mitra', 'school', 16.41176500, 120.60242591, '2026-01-05 23:57:45'),
(115, 'Northridge Academy, Inc.', 'Quirino-Magsaysay, Upper', 'school', 16.40583593, 120.59073480, '2026-01-05 23:57:45'),
(116, '4th Watch Maranatha Christian Academy of Baguio City, Inc.', 'San Luis Village', 'school', 16.41358788, 120.57870200, '2026-01-05 23:57:45'),
(117, 'STI College - Baguio', 'New Lucban', 'school', 16.42105319, 120.59715523, '2026-01-05 23:57:45'),
(118, 'Fort Del Pilar Elementary School', 'Fort Del Pilar', 'school', 16.37298014, 120.62765933, '2026-01-05 23:57:46'),
(119, 'Sto. Tomas National High School', 'Santo Tomas School Area', 'school', 16.37180665, 120.57782698, '2026-01-05 23:57:46'),
(120, 'Sto. Tomas Elementary School', 'Santo Tomas School Area', 'school', 16.37112099, 120.57799974, '2026-01-05 23:57:46'),
(121, 'Loakan Elementary School', 'Loakan Proper', 'school', 16.37766868, 120.61270620, '2026-01-05 23:57:46'),
(122, 'Camp 7 Elementary School', 'Camp 7', 'school', 16.37937860, 120.60632425, '2026-01-05 23:57:46'),
(123, 'Baguio Patriotic Elementary School', 'Harrison-Claudio Carantes', 'school', 16.41150063, 120.59701667, '2026-01-05 23:57:46'),
(124, 'Northern Luzon School for Visually Impaired, Inc. (NLSVI)', 'Padre Burgos', 'school', 16.41885168, 120.58947961, '2026-01-05 23:57:46'),
(125, 'Baguio Seventh Day Adventist School Academy', 'Padre Burgos', 'school', 16.42071678, 120.59003180, '2026-01-05 23:57:46'),
(126, 'University of Baguio Laboratory Elementary School', 'Salud Mitra', 'school', 16.41171618, 120.60073481, '2026-01-05 23:57:46'),
(127, 'Our Lady of the Atonement Cathedral School, Inc..', 'Kabayanihan', 'school', 16.41345385, 120.59836891, '2026-01-05 23:57:46'),
(128, 'Union School International', 'Legarda-Burnham-Kisad', 'school', 16.40857177, 120.59241506, '2026-01-05 23:57:46'),
(129, 'Genext School of Leaders Foundation, Inc.', 'Loakan Proper', 'school', 16.37693125, 120.61475697, '2026-01-05 23:57:46'),
(130, 'Balay Sofia', 'Saint Joseph Village', 'school', 16.41685645, 120.60816697, '2026-01-05 23:57:46'),
(131, 'University of the Philippines - Baguio', 'Military Cut-off', 'school', 16.40580409, 120.59808943, '2026-01-05 23:57:46'),
(132, 'Kias Elementary School', 'Kias', 'school', 16.36712395, 120.63154124, '2026-01-05 23:57:46'),
(133, 'Lindawan Elementary School', 'Lucnab', 'school', 16.40531463, 120.62904234, '2026-01-05 23:57:46'),
(134, 'Mil-an National High School', 'Loakan Proper', 'school', 16.37767558, 120.61216674, '2026-01-05 23:57:46'),
(135, 'Rizal National High School', 'Gibraltar', 'school', 16.41695441, 120.61603976, '2026-01-05 23:57:46'),
(136, 'Baguio Country Club Elementary School', 'Country Club Village', 'school', 16.40514204, 120.62061927, '2026-01-05 23:57:46'),
(137, 'Jose P. Laurel Elementary School', 'Dagsian, Upper', 'school', 16.39457725, 120.60690239, '2026-01-05 23:57:46'),
(138, 'Hillside Annex', 'Hillside', 'school', 16.39703014, 120.60559533, '2026-01-05 23:57:46'),
(139, 'San Vicente National High School', 'San Vicente', 'school', 16.39473594, 120.59687676, '2026-01-05 23:57:46'),
(140, 'San Vicente Elementary School', 'San Vicente', 'school', 16.39505802, 120.59650732, '2026-01-05 23:57:46'),
(141, 'Fort Del Pilar Annex', 'Fort Del Pilar', 'school', 16.37346220, 120.62705274, '2026-01-05 23:57:46'),
(142, 'Bakakeng Elementary School', 'Bakakeng Norte', 'school', 16.39069359, 120.59442864, '2026-01-05 23:57:46'),
(143, 'Baguio City National Science High School', 'Irisan', 'school', 16.41572257, 120.56140336, '2026-01-05 23:57:46'),
(144, 'San Luis Elementary School', 'San Luis Village', 'school', 16.40567300, 120.57679867, '2026-01-05 23:57:46'),
(145, 'Dominican-Mirador Elementary School', 'Dominican Hill-Mirador', 'school', 16.40684257, 120.58323931, '2026-01-05 23:57:46'),
(146, 'Dominican-Mirador National High School', 'Dominican Hill-Mirador', 'school', 16.40672412, 120.58314117, '2026-01-05 23:57:46'),
(147, 'Doña Aurora National High School', 'Aurora Hill, North Central', 'school', 16.42481900, 120.60416140, '2026-01-05 23:57:46'),
(148, 'Doña Aurora Elementary School', 'Aurora Hill, North Central', 'school', 16.42492003, 120.60476724, '2026-01-05 23:57:46'),
(149, 'Yesu Maeul Christian Academy', 'Padre Burgos', 'school', 16.41980701, 120.59123073, '2026-01-05 23:57:46'),
(150, 'Baguio Central University (Main)', 'Magsaysay, Lower', 'school', 16.41929142, 120.59415591, '2026-01-05 23:57:46'),
(151, 'Spring Hills Elementary School', 'Apugan-Loakan', 'school', 16.38189541, 120.62451116, '2026-01-05 23:57:46'),
(152, 'Saint Louis University Laboratory High School', 'Saint Joseph Village', 'school', 16.41634686, 120.60899314, '2026-01-05 23:57:46'),
(153, 'University of Baguio Science High School', 'General Luna, Lower', 'school', 16.41623731, 120.59820231, '2026-01-05 23:57:46'),
(154, 'TESDA Regional Training Center Baguio', 'Loakan Proper', 'school', 16.37784727, 120.62150388, '2026-01-05 23:57:46'),
(155, 'Baguio City School of Arts and Trades', 'Military Cut-off', 'school', 16.40329422, 120.60394478, '2026-01-05 23:57:46'),
(156, 'Brookspoint Elementary School', 'Brookspoint', 'school', 16.42476059, 120.60877127, '2026-01-05 23:57:46'),
(157, 'Pines Montessori School', 'Lualhati', 'school', 16.41602342, 120.61985417, '2026-01-05 23:57:46'),
(158, 'Loakan Baptist Church Christian Education Center', 'Loakan Proper', 'school', 16.37603885, 120.61144069, '2026-01-05 23:57:46'),
(159, 'Saint Louis School of Aurora Hill Inc.', 'Modern Site, East', 'school', 16.42408995, 120.60588004, '2026-01-05 23:57:46'),
(160, 'Baguio Multicultural Institute', 'San Luis Village', 'school', 16.41272279, 120.57898857, '2026-01-05 23:57:46'),
(161, 'Baguio City Foursquare Heritage School, Inc.', 'Pinsao Proper', 'school', 16.42445937, 120.58185641, '2026-01-05 23:57:46'),
(162, 'Calvary Ministries Academy of Baguio', 'Guisad Central', 'school', 16.42165924, 120.58683754, '2026-01-05 23:57:46'),
(163, 'Friendly Homes Kindergarten, Inc.', 'Guisad Central', 'school', 16.42108865, 120.58799138, '2026-01-05 23:57:46'),
(164, 'San Luis King\'s Kids Christian Academy', 'San Luis Village', 'school', 16.40828071, 120.56876283, '2026-01-05 23:57:46'),
(165, 'Baguio Vision Christian Academy Foundation', 'Bakekeng Central', 'school', 16.39962126, 120.58352347, '2026-01-05 23:57:46'),
(166, 'Good News Academy, Inc.', 'Trancoville', 'school', 16.42404833, 120.59995961, '2026-01-05 23:57:46'),
(167, 'Bethesda Sky Learning Center, Inc.', 'Quirino Hill, East', 'school', 16.43054464, 120.59485232, '2026-01-05 23:57:46'),
(168, 'Atok Trail Covered Court', 'Atok Trail', 'court', 16.37911669, 120.63044892, '2026-01-05 23:57:46'),
(169, 'Balsigan Covered Court', 'Balsigan', 'court', 16.39934393, 120.59364461, '2026-01-05 23:57:46'),
(170, 'Camp 8 Covered Court', 'Camp 8', 'covered_court', 16.39816062, 120.60036400, '2026-01-05 23:57:46'),
(171, 'Camp Allen Covered Court', 'Camp Allen', 'court', 16.41476692, 120.59187907, '2026-01-05 23:57:46'),
(172, 'Loakan Apugan Covered Court', 'Apugan-Loakan', 'covered_court', 16.38369197, 120.62164158, '2026-01-05 23:57:46'),
(173, 'DPS Compound Covered Court', 'DPS Compound', 'covered_court', 16.40512696, 120.60434099, '2026-01-05 23:57:46'),
(174, 'Engineers Hill Basketball Court', 'Engineers\' Hill', 'covered_court', 16.40738674, 120.60232587, '2026-01-05 23:57:46'),
(175, 'Gibraltar Covered Court', 'Gibraltar', 'covered_court', 16.42049270, 120.62069415, '2026-01-05 23:57:46'),
(176, 'Holyghost Extension Covered Court', 'Holy Ghost Extension', 'covered_court', 16.41777819, 120.60437213, '2026-01-05 23:57:46'),
(177, 'Lourdes Proper Covered Court', 'Lourdes Subdivision, Proper', 'covered_court', 16.41083380, 120.58249812, '2026-01-05 23:57:46'),
(178, 'Lualhati Covered Court', 'Lualhati', 'covered_court', 16.41441079, 120.62077432, '2026-01-05 23:57:46'),
(179, 'Middle Quezon Hill Covered Court', 'Quezon Hill Proper', 'covered_court', 16.41596390, 120.57549311, '2026-01-05 23:57:46'),
(180, 'Quirino Hill Elementary School, Middle Quirino Hill Covered Court', 'Quirino Hill, Middle', 'covered_court', 16.42911116, 120.59124508, '2026-01-05 23:57:46'),
(181, 'Lower Rock Quarry Covered Court', 'Rock Quarry, Lower', 'court', 16.40939059, 120.58662324, '2026-01-05 23:57:46'),
(182, 'Pinsao Elementary Covered Court', 'Pinsao Pilot Project', 'covered_court', 16.42752780, 120.58160931, '2026-01-05 23:57:46'),
(183, 'Quezon Hill Covered Court', 'Quezon Hill Proper', 'covered_court', 16.41584194, 120.58013868, '2026-01-05 23:57:46'),
(184, 'Sanitary Camp Covered Court', 'Sanitary Camp, South', 'covered_court', 16.42744782, 120.59721128, '2026-01-05 23:57:46'),
(185, 'Slaughter House Area (Santo Niño Slaughter) Covered Court', 'Magsaysay, Lower', 'covered_court', 16.42048964, 120.59397368, '2026-01-05 23:57:46'),
(186, 'Victoria Village Covered Court', 'Victoria Village', 'court', 16.41423918, 120.57858347, '2026-01-05 23:57:46'),
(187, 'Dontogan Covered Court', 'Dontogan', 'covered_court', 16.37736920, 120.56890825, '2026-01-05 23:57:46'),
(188, 'Bal Marcoville Covered Court', 'Bal-Marcoville (Marcoville)', 'covered_court', 16.40644787, 120.60354664, '2026-01-05 23:57:46'),
(189, 'Bgh Covered Court', 'BGH Compound', 'covered_court', 16.39874088, 120.59752137, '2026-01-05 23:57:46'),
(190, 'Philam Covered Court', 'Phil-Am', 'covered_court', 16.39984180, 120.59461541, '2026-01-05 23:57:46'),
(191, 'Fort Del Pilar Covered Court', 'Fort del Pilar', 'court', 16.36972690, 120.62898209, '2026-01-05 23:57:46'),
(192, 'Loakan Apugan Half Court', 'Apugan-Loakan', 'court', 16.37873366, 120.62437632, '2026-01-05 23:57:46'),
(193, 'Saint Joseph Village Covered Court', 'Saint Joseph Village', 'covered_court', 16.41695010, 120.61106912, '2026-01-05 23:57:46'),
(194, 'Upper Dagsian Covered Court', 'Dagsian, Upper', 'covered_court', 16.39455216, 120.60740741, '2026-01-05 23:57:46'),
(195, 'PFVR Gymnasium', 'Military Cut-off', 'covered_court', 16.40376403, 120.60441696, '2026-01-05 23:57:46'),
(196, 'Guisad Sorong Covered Court', 'Guisad Sorong', 'covered_court', 16.42102433, 120.58394452, '2026-01-05 23:57:46'),
(197, 'Padre Burgos Covered Court', 'Padre Burgos', 'covered_court', 16.41950473, 120.59136593, '2026-01-05 23:57:46'),
(198, 'Kias Covered Court', 'Kias', 'covered_court', 16.36710984, 120.63162918, '2026-01-05 23:57:46'),
(199, 'Elpedio Quirino Elementary Covered Court', 'Irisan', 'covered_court', 16.42945711, 120.54954547, '2026-01-05 23:57:46'),
(200, 'Greenwater Covered Court', 'Greenwater', 'covered_court', 16.40132129, 120.60746274, '2026-01-05 23:57:46'),
(201, 'Happy Homes (Happy Homes-Lucban) Barangay Hall', 'Happy Homes', 'barangay_hall', 16.42580410, 120.59387570, '2026-01-05 23:57:46'),
(202, 'ABCR Barangay Hall', 'A. Bonifacio-Caguioa-Rimando (ABCR)', 'barangay_hall', 16.42041319, 120.59901241, '2026-01-05 23:57:46'),
(203, 'Ambiong Barangay Hall', 'Ambiong', 'barangay_hall', 16.42843341, 120.60781903, '2026-01-05 23:57:46'),
(204, 'Andres Bonifacio (Lower Bokawkan) Barangay Hall', 'Andres Bonifacio (Lower Bokawkan)', 'barangay_hall', 16.41875189, 120.58524685, '2026-01-05 23:57:46'),
(205, 'Asin Road Barangay Hall', 'Asin Road', 'barangay_hall', 16.40444161, 120.56709058, '2026-01-05 23:57:46'),
(206, 'Atok Trail Barangay Hall', 'Atok Trail', 'barangay_hall', 16.37898901, 120.63034361, '2026-01-05 23:57:46'),
(207, 'AZCKO Barangay Hall', 'Abanao-Zandueta-Kayang-Chugum-Otek (AZKCO)', 'barangay_hall', 16.41433745, 120.59331880, '2026-01-05 23:57:46'),
(208, 'Bakakeng Norte/Sur Barangay Hall', 'Bakakeng Norte', 'barangay_hall', 16.39171656, 120.59073001, '2026-01-05 23:57:46'),
(209, 'Balsigan Barangay Hall', 'Balsigan', 'barangay_hall', 16.39864225, 120.59365387, '2026-01-05 23:57:46'),
(210, 'Bayan Park Village Barangay Hall', 'Bayan Park Village', 'barangay_hall', 16.42738308, 120.60614036, '2026-01-05 23:57:46'),
(211, 'Brookspoint Barangay Hall', 'Brookspoint', 'barangay_hall', 16.42505266, 120.60893081, '2026-01-05 23:57:46'),
(212, 'Rizal Monument Barangay Hall', 'Legarda-Burnham-Kisad', 'barangay_hall', 16.41048711, 120.59304733, '2026-01-05 23:57:46'),
(213, 'Legarda-Burnham-Kisad Barangay Hall', 'Legarda-Burnham-Kisad', 'barangay_hall', 16.40861725, 120.59480744, '2026-01-05 23:57:46'),
(214, 'Cabinet Hill-Teacher\'s Camp Barangay Hall', 'Cabinet Hill-Teacher\'s Camp', 'barangay_hall', 16.41094861, 120.60358373, '2026-01-05 23:57:46'),
(215, 'Alfonso Tabora, Trancoville, Camdas  Barngay Hall', 'Camdas Subdivision', 'barangay_hall', 16.42535474, 120.59349221, '2026-01-05 23:57:46'),
(216, 'Camp 7 Barangay Hall', 'Camp 7', 'barangay_hall', 16.38178723, 120.60667206, '2026-01-05 23:57:46'),
(217, 'Camp 8 Barangay Hall', 'Camp 8', 'barangay_hall', 16.39847616, 120.60028341, '2026-01-05 23:57:46'),
(218, 'Camp Allen Barangay Hall', 'Camp Allen', 'barangay_hall', 16.41678162, 120.59036320, '2026-01-05 23:57:46'),
(219, 'City Camp Central Barangay Hall', 'City Camp Central', 'barangay_hall', 16.41125227, 120.58820965, '2026-01-05 23:57:46'),
(220, 'City Camp Proper Barangay Hall', 'City Camp Proper', 'barangay_hall', 16.41090607, 120.58933264, '2026-01-05 23:57:46'),
(221, 'Country Club Village Barangay Hall', 'Country Club Village', 'barangay_hall', 16.40522373, 120.62034214, '2026-01-05 23:57:46'),
(222, 'Happy Hollow Barangay Hall', 'Happy Hollow', 'barangay_hall', 16.39586864, 120.62403935, '2026-01-05 23:57:46'),
(223, 'Cresencia Village Barangay Hall', 'Cresencia Village', 'barangay_hall', 16.41868946, 120.58764453, '2026-01-05 23:57:46'),
(224, 'Dizon Subdivision Barangay Hall', 'Dizon Subdivision', 'barangay_hall', 16.42599056, 120.59145352, '2026-01-05 23:57:46'),
(225, 'Dominican Hill-Mirador Barangay Hall', 'Dominican Hill-Mirador', 'barangay_hall', 16.40881795, 120.58243447, '2026-01-05 23:57:46'),
(226, 'Dontogan Barangay Hall', 'Dontogan', 'barangay_hall', 16.37730166, 120.56871871, '2026-01-05 23:57:46'),
(227, 'DPS Compound Barangay Hall', 'DPS Compound', 'barangay_hall', 16.40521289, 120.60413587, '2026-01-05 23:57:46'),
(228, 'Bayan Park East Barangay Hall', 'Bayan Park East', 'barangay_hall', 16.42727608, 120.60810789, '2026-01-05 23:57:46'),
(229, 'Modern Site, East Barangay Hall', 'Modern Site, East', 'barangay_hall', 16.42198287, 120.60524366, '2026-01-05 23:57:46'),
(230, 'Quirino Hill, East Barangay Hall', 'Quirino Hill, East', 'barangay_hall', 16.43107491, 120.59341613, '2026-01-05 23:57:46'),
(231, 'Fairview Village Barangay Hall', 'Fairview Village', 'barangay_hall', 16.41583077, 120.58377007, '2026-01-05 23:57:46'),
(232, 'Fort Del Pilar Barangay Hall', 'Fort Del Pilar', 'barangay_hall', 16.37190676, 120.62892303, '2026-01-05 23:57:46'),
(233, 'Greenwater Village Barangay Hall', 'Greenwater Village', 'barangay_hall', 16.40102362, 120.60747491, '2026-01-05 23:57:46'),
(234, 'Guisad Central Barangay Hall', 'Guisad Central', 'barangay_hall', 16.42223447, 120.58547708, '2026-01-05 23:57:46'),
(235, 'Guisad Surong Barangay Hall', 'Guisad Surong', 'barangay_hall', 16.42114564, 120.58395812, '2026-01-05 23:57:46'),
(236, 'Hillside Barangay Hall', 'Hillside', 'barangay_hall', 16.39742168, 120.60438820, '2026-01-05 23:57:46'),
(237, 'Holyghost Extension Barangay Hall', 'Holy Ghost Extension', 'barangay_hall', 16.41770635, 120.60430265, '2026-01-05 23:57:46'),
(238, 'Honeymoon-Holyghost Barangay Hall', 'Honeymoon', 'barangay_hall', 16.41902603, 120.60039055, '2026-01-05 23:57:46'),
(239, 'Brookside Barangay Hall', 'Brookside', 'barangay_hall', 16.42047807, 120.60261867, '2026-01-05 23:57:46'),
(240, 'Kabayanihan Barangay Hall', 'Kabayanihan', 'barangay_hall', 16.41470925, 120.59679422, '2026-01-05 23:57:46'),
(241, 'Kayang Extension Barangay Hall', 'Kayang Extension', 'barangay_hall', 16.41413238, 120.58854166, '2026-01-05 23:57:46'),
(242, 'Bayan Park West (Leonila Hill) Barangay Hall', 'Bayan Park West (Leonila Hill)', 'barangay_hall', 16.42744093, 120.60342927, '2026-01-05 23:57:46'),
(243, 'Apugan-Loakan Barangay Hall', 'Apugan-Loakan', 'barangay_hall', 16.37744750, 120.62438275, '2026-01-05 23:57:46'),
(244, 'Lopez Jaena Barangay Hall', 'Lopez Jaena', 'barangay_hall', 16.42505278, 120.60408579, '2026-01-05 23:57:46'),
(245, 'Lourdes Subdivision Extension Barangay Hall', 'Lourdes Subdivision Extension', 'barangay_hall', 16.41133758, 120.58489683, '2026-01-05 23:57:46'),
(246, 'Lourdes Subdivision, Proper Barangay Hall', 'Lourdes Subdivision, Proper', 'barangay_hall', 16.41088700, 120.58210051, '2026-01-05 23:57:46'),
(247, 'General Luna, Lower Barangay Hall', 'General Luna, Lower', 'barangay_hall', 16.41359698, 120.59926177, '2026-01-05 23:57:46'),
(248, 'Lourdes Subdivision, Lower Barangay Hall', 'Lourdes Subdivision, Lower', 'barangay_hall', 16.40949480, 120.58484013, '2026-01-05 23:57:46'),
(249, 'Quirino Hill, Lower Barangay Hall', 'Quirino Hill, Lower', 'barangay_hall', 16.42805300, 120.59099449, '2026-01-05 23:57:46'),
(250, 'Lualhati Barangay Hall', 'Lualhati', 'barangay_hall', 16.41432494, 120.62063417, '2026-01-05 23:57:46'),
(251, 'Lucnab Barangay Hlall', 'Lucnab', 'barangay_hall', 16.40461698, 120.62959365, '2026-01-05 23:57:46'),
(252, 'Manuel A. Roxas Barangay Hall', 'Manuel A. Roxas', 'barangay_hall', 16.41503855, 120.60799327, '2026-01-05 23:57:46'),
(253, 'Middle Quezon Hill Subdivision Barangay Hall', 'Middle Quezon Hill Subdivision', 'barangay_hall', 16.41582600, 120.57551017, '2026-01-05 23:57:46'),
(254, 'Rock Quarry, Middle Barangay Hall', 'Rock Quarry, Middle', 'barangay_hall', 16.40860440, 120.58603588, '2026-01-05 23:57:46'),
(255, 'Rock Quarry, Lower Barangay Hall', 'Rock Quarry, Lower', 'barangay_hall', 16.40810949, 120.58793044, '2026-01-05 23:57:46'),
(256, 'Mines View Barangay Hall', 'Mines View', 'barangay_hall', 16.41993269, 120.62689086, '2026-01-05 23:57:46'),
(257, 'MRR-Queen of Peace Barangay Hall', 'MRR-Queen of Peace', 'barangay_hall', 16.41366797, 120.58801074, '2026-01-05 23:57:46'),
(258, 'New Lucban Barangay Hall', 'New Lucban', 'barangay_hall', 16.42099927, 120.59667184, '2026-01-05 23:57:46'),
(259, 'North Central Aurorahill Barangay Hall', 'Bayan Park Village', 'barangay_hall', 16.42627793, 120.60633269, '2026-01-05 23:57:46'),
(260, 'Aurora Hill, South Central Barangay Hall', 'Aurora Hill, South Central', 'barangay_hall', 16.42556254, 120.60647279, '2026-01-05 23:57:46'),
(261, 'Aurora Hill Proper Barangay Hall', 'Aurora Hill Proper', 'barangay_hall', 16.42442024, 120.60437642, '2026-01-05 23:57:46'),
(262, 'Sanitary Camp, North Barangay Hall', 'Sanitary Camp, North', 'barangay_hall', 16.42966330, 120.59804478, '2026-01-05 23:57:46'),
(263, 'Outlook Drive Barangay Hall', 'Outlook Drive', 'barangay_hall', 16.41344825, 120.62431052, '2026-01-05 23:57:46'),
(264, 'Padre Burgos Barangay Hall', 'Padre Burgos', 'barangay_hall', 16.41993325, 120.59195541, '2026-01-05 23:57:46'),
(265, 'Palma Urbano Barangay Hall', 'Palma Urbano', 'barangay_hall', 16.41282280, 120.59029693, '2026-01-05 23:57:46'),
(266, 'Pinget Barangay Hall', 'Pinget', 'barangay_hall', 16.42679363, 120.58535606, '2026-01-05 23:57:46'),
(267, 'Pinsao Pilot Project Barangay Hall', 'Pinsao Pilot Project', 'barangay_hall', 16.42547070, 120.58244589, '2026-01-05 23:57:46'),
(268, 'Kayang-Hilltop Barangay Hall', 'Kayang-Hilltop', 'barangay_hall', 16.41525351, 120.59351823, '2026-01-05 23:57:46'),
(269, 'Quezon Hill Proper Barangay Hall', 'Quezon Hill Proper', 'barangay_hall', 16.41577726, 120.58000728, '2026-01-05 23:57:46'),
(270, 'Salud Mitra Barangay Hall', 'Salud Mitra', 'barangay_hall', 16.40947817, 120.60113256, '2026-01-05 23:57:46'),
(271, 'San Antonio Village Barangay Hall', 'San Antonio Village', 'barangay_hall', 16.42603536, 120.60483298, '2026-01-05 23:57:46'),
(272, 'San Roque Village Barangay Hall', 'San Roque Village', 'barangay_hall', 16.41243052, 120.58186816, '2026-01-05 23:57:46'),
(273, 'Dagsian, Upper Barangay Hall', 'Dagsian, Upper', 'barangay_hall', 16.39467206, 120.60762015, '2026-01-05 23:57:46'),
(274, 'SLU-SVP Housing Village Barangay Hall', 'SLU-SVP Housing Village', 'barangay_hall', 16.39098310, 120.58901168, '2026-01-05 23:57:46'),
(275, 'Sanitary Camp, South Barangay Hall', 'Sanitary Camp, South', 'barangay_hall', 16.42739927, 120.59705707, '2026-01-05 23:57:46'),
(276, 'Magsaysay, Lower Barangay hall', 'Magsaysay, Lower', 'barangay_hall', 16.41992143, 120.59348231, '2026-01-05 23:57:46'),
(277, 'General Luna, Upper Barangay Hall', 'General Luna, Upper', 'barangay_hall', 16.41281908, 120.60146916, '2026-01-05 23:57:46'),
(278, 'Magsaysay, Upper Barangay hall', 'Magsaysay, Upper', 'barangay_hall', 16.41626685, 120.59587398, '2026-01-05 23:57:46'),
(279, 'Rock Quarry, Upper Barangay Hall', 'Rock Quarry, Upper', 'barangay_hall', 16.40757236, 120.58816480, '2026-01-05 23:57:46'),
(280, 'Victoria Village Barangay Hall', 'Victoria Village', 'barangay_hall', 16.41437125, 120.57868133, '2026-01-05 23:57:46'),
(281, 'Modern Site, West Barangay hall', 'Modern Site, West', 'barangay_hall', 16.42372203, 120.60389517, '2026-01-05 23:57:46'),
(282, 'Quirino Hill, West Barangay Hall', 'Quirino Hill, West', 'barangay_hall', 16.43092003, 120.58921164, '2026-01-05 23:57:46'),
(283, 'Pinsao Proper Barangay Hall', 'Pinsao Proper', 'barangay_hall', 16.42155008, 120.57748383, '2026-01-05 23:57:46'),
(284, 'Session Road Area Barangay Hall', 'Session Road Area', 'barangay_hall', 16.41040235, 120.59902409, '2026-01-05 23:57:46'),
(285, 'Santo Tomas Proper Barangay Hall', 'Santo Tomas Proper', 'barangay_hall', 16.38004208, 120.57993147, '2026-01-05 23:57:46'),
(286, 'Santo Tomas School Area Barangay Hall', 'Santo Tomas School Area', 'barangay_hall', 16.37091396, 120.57745734, '2026-01-05 23:57:46'),
(287, 'Sto. Rosario Barangay Hall', 'Santo Rosario', 'barangay_hall', 16.40167788, 120.58719865, '2026-01-05 23:57:46'),
(288, 'Bal-Marcoville Barangay Hall', 'Bal-Marcoville', 'barangay_hall', 16.40644787, 120.60364301, '2026-01-05 23:57:46'),
(289, 'BGH Compound Barangay Hall', 'BGH Compound', 'barangay_hall', 16.39859147, 120.59745978, '2026-01-05 23:57:46'),
(290, 'Phil-Am Barangay Hall', 'Phil-Am', 'barangay_hall', 16.39985694, 120.59441512, '2026-01-05 23:57:46'),
(291, 'Kias Barangay', 'Fort Del Pilar', 'barangay_hall', 16.36687676, 120.63155804, '2026-01-05 23:57:46'),
(292, 'Loakan Proper Barangay Hall', 'Loakan Proper', 'barangay_hall', 16.37676942, 120.61357515, '2026-01-05 23:57:46'),
(293, 'Saint Joseph Village Barangay Hall', 'Saint Joseph Village', 'barangay_hall', 16.41706990, 120.61110930, '2026-01-05 23:57:46'),
(294, 'South  Drive Barangay Hall', 'South  Drive', 'barangay_hall', 16.40914422, 120.61081494, '2026-01-05 23:57:46'),
(295, 'Dagsian, Lower Barangay Hall', 'Dagsian, Lower', 'barangay_hall', 16.39275282, 120.60757175, '2026-01-05 23:57:46'),
(296, 'Gabriela Silang Barangay Hall', 'Gabriela Silang', 'barangay_hall', 16.39392091, 120.60378027, '2026-01-05 23:57:46'),
(297, 'San Vicente Barangay Hall', 'San Vicente', 'barangay_hall', 16.39488676, 120.59723905, '2026-01-05 23:57:46'),
(298, 'Military Cut-off Barangay Hall', 'Military Cut-off', 'barangay_hall', 16.40265770, 120.60226696, '2026-01-05 23:57:46'),
(299, 'Holyghost Proper Barangay Hall', 'Holyghost Proper', 'barangay_hall', 16.41561101, 120.60076134, '2026-01-05 23:57:46'),
(300, 'Malcolm Square-Perfecto Barangay Hall', 'Malcolm Square-Perfecto', 'barangay_hall', 16.41327040, 120.59512574, '2026-01-05 23:57:46'),
(301, 'Imelda Village Barangay Hall', 'Imelda Village', 'barangay_hall', 16.41772033, 120.60721403, '2026-01-05 23:57:46'),
(302, 'Bakakeng Central Barangay Hall', 'Bakakeng Central', 'barangay_hall', 16.39638279, 120.58220960, '2026-01-05 23:57:46'),
(303, 'San Luis Barangya Hall', 'San Luis Village', 'barangay_hall', 16.40567419, 120.57666597, '2026-01-05 23:57:46'),
(304, 'Poliwes Barangay Hall', 'Poliwes', 'barangay_hall', 16.39604693, 120.59925876, '2026-01-05 23:57:46'),
(305, 'Quirino-Magsaysay, Upper Barangay Hall', 'Quirino-Magsaysay, Upper', 'barangay_hall', 16.40521645, 120.59123371, '2026-01-05 23:57:46'),
(306, 'General Emilio F. Aguinaldo (Lower QM) Barangay Hall', 'General Emilio F. Aguinaldo (Lower QM)', 'barangay_hall', 16.40951143, 120.58896527, '2026-01-05 23:57:46'),
(307, 'Santa Escolastica Barangay Hall', 'Santa Escolastica', 'barangay_hall', 16.39998429, 120.60364833, '2026-01-05 23:57:46'),
(308, 'Pacdal Barangay Hall', 'Pacdal', 'barangay_hall', 16.41671242, 120.61516823, '2026-01-05 23:57:46'),
(309, 'Quezon Hill, Upper Barangay hall', 'Quezon Hill, Upper', 'barangay_hall', 16.41692423, 120.57600220, '2026-01-05 23:57:46'),
(310, 'Kagitingan Barangay Hall', 'Kagitingan', 'barangay_hall', 16.41684571, 120.59626083, '2026-01-05 23:57:46'),
(311, 'Market Subdivision, Upper Barangay Hall', 'Market Subdivision, Upper', 'barangay_hall', 16.41614057, 120.59436326, '2026-01-05 23:57:46'),
(312, 'Quirino Hill, Middle Barangay Hall', 'Quirino Hill, Middle', 'barangay_hall', 16.42901350, 120.59130550, '2026-01-05 23:57:46'),
(313, 'Campo Filipino Barangay Hall', 'Campo Filipino', 'barangay_hall', 16.41483373, 120.58818717, '2026-01-05 23:57:46'),
(314, 'Padre Zamora Barangay Hall', 'Padre Zamora', 'barangay_hall', 16.41756686, 120.59293856, '2026-01-05 23:57:46'),
(315, 'Magsaysay Private Road Barangay Hall', 'Magsaysay Private Road', 'barangay_hall', 16.42440463, 120.59241530, '2026-01-05 23:57:46'),
(316, 'Teodora Alonzo Barangay Hall', 'Teodora Alonzo', 'barangay_hall', 16.42022249, 120.59700201, '2026-01-05 23:57:46'),
(317, 'Sto. Niño Slaughter Compound Barangay hall', 'Slaughter House Area', 'barangay_hall', 16.42022731, 120.59398874, '2026-01-05 23:57:46'),
(318, 'Scout Barrio Barangay Hall', 'Scout Barrio', 'barangay_hall', 16.39639766, 120.60860318, '2026-01-05 23:57:46'),
(319, 'Irisan Barangay Hall', 'Irisan', 'barangay_hall', 16.42036004, 120.55685092, '2026-01-05 23:57:46'),
(320, 'Liwanag-Loakan Barangay Hall', 'Liwanag-Loakan', 'barangay_hall', 16.37870931, 120.61171791, '2026-01-05 23:57:46'),
(321, 'Pucsusan Barangay hall', 'Pucsusan', 'barangay_hall', 16.41797873, 120.62883249, '2026-01-05 23:57:46'),
(322, 'Gibraltar Barangay Hall', 'Gibraltar', 'barangay_hall', 16.41809465, 120.62326286, '2026-01-05 23:57:46'),
(323, 'Imelda Marcos, Ferdinand Barangay Hall', 'Ferdinand', 'barangay_hall', 16.40162190, 120.59020437, '2026-01-05 23:57:46'),
(324, 'Harrison-Claudio Carantes Barangay Hall', 'Harrison-Claudio Carantes', 'barangay_hall', 16.41039412, 120.59779186, '2026-01-05 23:57:46'),
(325, 'Engineers Hill Barangay Hall', 'Engineer\'s Hill', 'barangay_hall', 16.40772921, 120.60232335, '2026-01-05 23:57:46'),
(326, 'Country Club open area(test site)', 'Country Club Village', 'other', 16.40609248, 120.60745786, '2026-02-03 14:43:16'),
(327, 'UC', 'Ambiong', 'barangay_hall', 16.41057974, 120.61490450, '2026-02-04 11:06:45'),
(328, 'test site 1', 'Balsigan', 'school', 16.40921711, 120.61539244, '2026-02-04 11:07:08'),
(329, 'OPEN AREA', 'A. Bonifacio-Caguioa-Rimando (ABCR)', 'court', 16.40115023, 120.64624486, '2026-04-07 23:36:10');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `url`, `is_read`, `created_at`) VALUES
(3, 1, 'New emergency reported (ID: 35) — Other by Responder Team', 'admin/manage-emergencies.php?id=1', 1, '2026-01-30 04:28:59'),
(4, 2, 'New emergency reported (ID: 35) — Other by Responder Team', 'admin/manage-emergencies.php?id=1', 1, '2026-01-30 04:28:59'),
(5, 2, 'Your emergency report (ID: 35) has been marked as handled by the response team.', 'resident/my-reports.php', 1, '2026-01-30 04:35:10'),
(6, 2, 'Your emergency report (ID: 35) has been marked as handled by the response team.', 'resident/my-reports.php', 1, '2026-02-01 16:51:52'),
(7, 2, 'Your emergency report (ID: 35) has been marked as handled by the response team.', 'resident/my-reports.php', 1, '2026-02-02 09:13:26'),
(8, 2, 'Your emergency report (ID: 35) has been marked as spam by the response team.', 'resident/my-reports.php', 1, '2026-02-02 09:13:28'),
(9, 2, 'Your emergency report (ID: 35) has been marked as spam by the response team.', 'resident/my-reports.php', 1, '2026-02-02 09:13:30'),
(10, 1, 'New emergency reported (ID: 37) — Flood by Responder Team', 'admin/manage-emergencies.php?id=3', 1, '2026-02-02 16:03:56'),
(11, 2, 'New emergency reported (ID: 37) — Flood by Responder Team', 'admin/manage-emergencies.php?id=3', 1, '2026-02-02 16:03:56'),
(12, 1, 'New emergency reported (ID: 38) — Flood by Responder Team', 'admin/manage-emergencies.php?id=4', 1, '2026-02-02 16:05:05'),
(13, 2, 'New emergency reported (ID: 38) — Flood by Responder Team', 'admin/manage-emergencies.php?id=4', 1, '2026-02-02 16:05:05'),
(14, 1, 'New emergency reported (ID: 39) — Landslide by Responder Team', 'admin/manage-emergencies.php?id=5', 1, '2026-02-03 05:12:12'),
(15, 2, 'New emergency reported (ID: 39) — Landslide by Responder Team', 'admin/manage-emergencies.php?id=5', 1, '2026-02-03 05:12:12'),
(16, 2, 'Your emergency report (ID: 39) is now being handled by the response team.', 'resident/my-reports.php', 1, '2026-02-03 05:12:49'),
(17, 2, 'Your emergency report (ID: 39) is now being handled by the response team.', 'resident/my-reports.php', 1, '2026-02-03 05:20:08'),
(18, 2, 'Your emergency report (ID: 39) is now being handled by the response team.', 'resident/my-reports.php', 1, '2026-02-03 05:26:29'),
(19, 2, 'Your emergency report (ID: 38) is now being handled by the response team.', 'resident/my-reports.php', 1, '2026-02-03 05:29:10'),
(20, 2, 'Your emergency report (ID: 37) is now being handled by the response team.', 'resident/my-reports.php', 1, '2026-02-03 05:32:39'),
(21, 2, 'Your emergency report (ID: 37) has been marked as handled by the response team.', 'resident/my-reports.php', 1, '2026-02-03 05:32:58'),
(22, 2, 'Your emergency report (ID: 35) is now being handled by the response team.', 'resident/my-reports.php', 1, '2026-02-03 06:00:07'),
(23, 2, 'Your emergency report (ID: 35) has been marked as handled by the response team.', 'resident/my-reports.php', 1, '2026-02-03 06:00:53'),
(24, 2, 'Your emergency report (ID: 36) has been marked as handled by the response team.', 'resident/my-reports.php', 1, '2026-02-03 06:00:56'),
(25, 2, 'Your emergency report (ID: 38) has been marked as handled by the response team.', 'resident/my-reports.php', 1, '2026-02-03 06:01:03'),
(26, 2, 'Your emergency report (ID: 39) has been marked as handled by the response team.', 'resident/my-reports.php', 1, '2026-02-03 06:01:05'),
(27, 1, 'New emergency reported (ID: 40) — Fire by Responder Team', 'admin/manage-emergencies.php?id=6', 0, '2026-02-04 03:09:07'),
(28, 2, 'New emergency reported (ID: 40) — Fire by Responder Team', 'admin/manage-emergencies.php?id=6', 0, '2026-02-04 03:09:07'),
(29, 2, 'Your emergency report (ID: 40) is now being handled by the response team.', 'resident/my-reports.php', 0, '2026-02-04 03:09:31'),
(30, 1, 'New emergency reported (ID: 41) — Landslide by Responder Team', 'admin/manage-emergencies.php?id=7', 0, '2026-02-04 03:12:01'),
(31, 2, 'New emergency reported (ID: 41) — Landslide by Responder Team', 'admin/manage-emergencies.php?id=7', 0, '2026-02-04 03:12:01'),
(32, 2, 'Your emergency report (ID: 41) is now being handled by the response team.', 'resident/my-reports.php', 0, '2026-02-04 03:12:18'),
(33, 2, 'Your emergency report (ID: 41) is now being handled by the response team.', 'resident/my-reports.php', 0, '2026-02-06 10:37:56'),
(34, 2, 'Your emergency report (ID: 39) is now being handled by the response team.', 'resident/my-reports.php', 0, '2026-02-06 10:38:40'),
(35, 2, 'Your emergency report (ID: 39) has been marked as handled by the response team.', 'resident/my-reports.php', 0, '2026-02-06 11:07:09'),
(36, 2, 'Your emergency report (ID: 41) is now being handled by the response team.', 'resident/my-reports.php', 0, '2026-02-06 11:07:51'),
(37, 2, 'Your emergency report (ID: 41) has been marked as handled by the response team.', 'resident/my-reports.php', 0, '2026-02-06 11:07:54'),
(38, 2, 'Your emergency report (ID: 41) has been marked as spam by the response team.', 'resident/my-reports.php', 0, '2026-02-06 11:08:16'),
(39, 1, 'New emergency reported (ID: 42) — Earthquake (Level 1 Weak) by doi ivan gayados', 'admin/manage-emergencies.php?id=8', 0, '2026-04-04 08:18:39'),
(40, 2, 'New emergency reported (ID: 42) — Earthquake (Level 1 Weak) by doi ivan gayados', 'admin/manage-emergencies.php?id=8', 0, '2026-04-04 08:18:39'),
(41, 14, 'New emergency reported (ID: 42) — Earthquake (Level 1 Weak) by doi ivan gayados', 'admin/manage-emergencies.php?id=8', 1, '2026-04-04 08:18:39'),
(42, 15, 'New emergency reported (ID: 42) — Earthquake (Level 1 Weak) by doi ivan gayados', 'admin/manage-emergencies.php?id=8', 0, '2026-04-04 08:18:39'),
(43, 16, 'New emergency reported (ID: 42) — Earthquake (Level 1 Weak) by doi ivan gayados', 'admin/manage-emergencies.php?id=8', 0, '2026-04-04 08:18:39'),
(44, 17, 'New emergency reported (ID: 42) — Earthquake (Level 1 Weak) by doi ivan gayados', 'admin/manage-emergencies.php?id=8', 0, '2026-04-04 08:18:39'),
(45, 1, 'New emergency reported (ID: 43) — Fire (Level 3 Out of Control) by doi ivan gayados', 'admin/manage-emergencies.php?id=9', 0, '2026-04-04 08:19:22'),
(46, 2, 'New emergency reported (ID: 43) — Fire (Level 3 Out of Control) by doi ivan gayados', 'admin/manage-emergencies.php?id=9', 0, '2026-04-04 08:19:22'),
(47, 14, 'New emergency reported (ID: 43) — Fire (Level 3 Out of Control) by doi ivan gayados', 'admin/manage-emergencies.php?id=9', 1, '2026-04-04 08:19:22'),
(48, 15, 'New emergency reported (ID: 43) — Fire (Level 3 Out of Control) by doi ivan gayados', 'admin/manage-emergencies.php?id=9', 0, '2026-04-04 08:19:22'),
(49, 16, 'New emergency reported (ID: 43) — Fire (Level 3 Out of Control) by doi ivan gayados', 'admin/manage-emergencies.php?id=9', 0, '2026-04-04 08:19:22'),
(50, 17, 'New emergency reported (ID: 43) — Fire (Level 3 Out of Control) by doi ivan gayados', 'admin/manage-emergencies.php?id=9', 0, '2026-04-04 08:19:22'),
(51, 1, 'New emergency reported (ID: 44) — Flood (Knee height) by doi ivan gayados', 'admin/manage-emergencies.php?id=10', 0, '2026-04-04 08:19:48'),
(52, 2, 'New emergency reported (ID: 44) — Flood (Knee height) by doi ivan gayados', 'admin/manage-emergencies.php?id=10', 0, '2026-04-04 08:19:48'),
(53, 14, 'New emergency reported (ID: 44) — Flood (Knee height) by doi ivan gayados', 'admin/manage-emergencies.php?id=10', 1, '2026-04-04 08:19:48'),
(54, 15, 'New emergency reported (ID: 44) — Flood (Knee height) by doi ivan gayados', 'admin/manage-emergencies.php?id=10', 0, '2026-04-04 08:19:48'),
(55, 16, 'New emergency reported (ID: 44) — Flood (Knee height) by doi ivan gayados', 'admin/manage-emergencies.php?id=10', 0, '2026-04-04 08:19:48'),
(56, 17, 'New emergency reported (ID: 44) — Flood (Knee height) by doi ivan gayados', 'admin/manage-emergencies.php?id=10', 0, '2026-04-04 08:19:48'),
(57, 1, 'New emergency reported (ID: 45) — Landslide (Level 2 Significant) by doi ivan gayados', 'admin/manage-emergencies.php?id=11', 0, '2026-04-04 08:20:16'),
(58, 2, 'New emergency reported (ID: 45) — Landslide (Level 2 Significant) by doi ivan gayados', 'admin/manage-emergencies.php?id=11', 0, '2026-04-04 08:20:16'),
(59, 14, 'New emergency reported (ID: 45) — Landslide (Level 2 Significant) by doi ivan gayados', 'admin/manage-emergencies.php?id=11', 1, '2026-04-04 08:20:16'),
(60, 15, 'New emergency reported (ID: 45) — Landslide (Level 2 Significant) by doi ivan gayados', 'admin/manage-emergencies.php?id=11', 0, '2026-04-04 08:20:16'),
(61, 16, 'New emergency reported (ID: 45) — Landslide (Level 2 Significant) by doi ivan gayados', 'admin/manage-emergencies.php?id=11', 0, '2026-04-04 08:20:16'),
(62, 17, 'New emergency reported (ID: 45) — Landslide (Level 2 Significant) by doi ivan gayados', 'admin/manage-emergencies.php?id=11', 0, '2026-04-04 08:20:16'),
(63, 1, 'New emergency reported (ID: 46) — Road Blockage (Level 3 Total) by doi ivan gayados', 'admin/manage-emergencies.php?id=12', 0, '2026-04-04 08:20:45'),
(64, 2, 'New emergency reported (ID: 46) — Road Blockage (Level 3 Total) by doi ivan gayados', 'admin/manage-emergencies.php?id=12', 0, '2026-04-04 08:20:45'),
(65, 14, 'New emergency reported (ID: 46) — Road Blockage (Level 3 Total) by doi ivan gayados', 'admin/manage-emergencies.php?id=12', 1, '2026-04-04 08:20:45'),
(66, 15, 'New emergency reported (ID: 46) — Road Blockage (Level 3 Total) by doi ivan gayados', 'admin/manage-emergencies.php?id=12', 0, '2026-04-04 08:20:45'),
(67, 16, 'New emergency reported (ID: 46) — Road Blockage (Level 3 Total) by doi ivan gayados', 'admin/manage-emergencies.php?id=12', 0, '2026-04-04 08:20:45'),
(68, 17, 'New emergency reported (ID: 46) — Road Blockage (Level 3 Total) by doi ivan gayados', 'admin/manage-emergencies.php?id=12', 0, '2026-04-04 08:20:45'),
(69, 14, 'New participant joined your drill: Frank Ocean (sea@gmail.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=8', 1, '2026-04-06 06:16:52'),
(70, 14, 'New participant joined your drill: Olivia Dean (dean@gmail.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=8', 1, '2026-04-06 06:17:20'),
(71, 14, 'New participant joined your drill: Taylor Swift (tay@gmail.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=8', 1, '2026-04-06 06:17:36'),
(72, 14, 'New participant joined your drill: Sabrina Carpenter (sabrina@gmail.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=8', 1, '2026-04-06 06:17:55'),
(73, 14, 'New participant joined your drill: Leon thomas (leon@gmail.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=8', 1, '2026-04-06 06:18:11'),
(74, 14, 'New participant joined your drill: lana del ray (lana@gmail.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=8', 1, '2026-04-06 06:55:31'),
(75, 14, 'New participant joined your drill: Cinnamon (cinnamon@gmail.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=8', 1, '2026-04-06 06:56:13'),
(76, 14, 'New participant joined your drill: Benee (benee@gmail.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=8', 1, '2026-04-06 06:56:31'),
(77, 1, 'New emergency reported (ID: 47) — Road Blockage (Level 1 Partial) by Zaira Kalea', 'admin/manage-emergencies.php?id=13', 0, '2026-04-06 07:19:14'),
(78, 2, 'New emergency reported (ID: 47) — Road Blockage (Level 1 Partial) by Zaira Kalea', 'admin/manage-emergencies.php?id=13', 0, '2026-04-06 07:19:14'),
(79, 14, 'New emergency reported (ID: 47) — Road Blockage (Level 1 Partial) by Zaira Kalea', 'admin/manage-emergencies.php?id=13', 0, '2026-04-06 07:19:14'),
(80, 15, 'New emergency reported (ID: 47) — Road Blockage (Level 1 Partial) by Zaira Kalea', 'admin/manage-emergencies.php?id=13', 0, '2026-04-06 07:19:14'),
(81, 16, 'New emergency reported (ID: 47) — Road Blockage (Level 1 Partial) by Zaira Kalea', 'admin/manage-emergencies.php?id=13', 0, '2026-04-06 07:19:14'),
(82, 17, 'New emergency reported (ID: 47) — Road Blockage (Level 1 Partial) by Zaira Kalea', 'admin/manage-emergencies.php?id=13', 0, '2026-04-06 07:19:14'),
(83, 1, 'New emergency reported (ID: 48) — Flood (Waist height) by Zaira Kalea', 'admin/manage-emergencies.php?id=13', 0, '2026-04-06 07:19:53'),
(84, 2, 'New emergency reported (ID: 48) — Flood (Waist height) by Zaira Kalea', 'admin/manage-emergencies.php?id=13', 0, '2026-04-06 07:19:53'),
(85, 14, 'New emergency reported (ID: 48) — Flood (Waist height) by Zaira Kalea', 'admin/manage-emergencies.php?id=13', 0, '2026-04-06 07:19:53'),
(86, 15, 'New emergency reported (ID: 48) — Flood (Waist height) by Zaira Kalea', 'admin/manage-emergencies.php?id=13', 0, '2026-04-06 07:19:53'),
(87, 16, 'New emergency reported (ID: 48) — Flood (Waist height) by Zaira Kalea', 'admin/manage-emergencies.php?id=13', 0, '2026-04-06 07:19:53'),
(88, 17, 'New emergency reported (ID: 48) — Flood (Waist height) by Zaira Kalea', 'admin/manage-emergencies.php?id=13', 0, '2026-04-06 07:19:53'),
(89, 14, 'New participant joined your drill: Lany (lany@gmail.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=8', 0, '2026-04-07 15:31:22'),
(90, 14, 'New participant joined your drill: mimasaur (raw@gmail.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=9', 0, '2026-04-07 15:47:34'),
(91, 14, 'New participant joined your drill: leon (leon@g.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=9', 0, '2026-04-07 15:47:49'),
(92, 14, 'New participant joined your drill: Redono (r@g.c)', 'barangay_responder/drills-and-trainings.php?leaderboard=9', 0, '2026-04-07 15:48:04'),
(93, 14, 'New participant joined your drill: Sandra (sa@g.c)', 'barangay_responder/drills-and-trainings.php?leaderboard=9', 0, '2026-04-07 15:48:25'),
(94, 14, 'New participant joined your drill: Princeton (pr@gm.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=8', 0, '2026-04-07 16:33:41'),
(95, 17, 'New participant joined your drill: riane (r@g.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=7', 0, '2026-04-07 16:34:06'),
(96, 14, 'New participant joined your drill: Billy (b@g.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=9', 0, '2026-04-07 16:36:56'),
(97, 16, 'New participant joined your drill: Eilish (ei@gma.c)', 'barangay_responder/drills-and-trainings.php?leaderboard=13', 0, '2026-04-07 16:37:10'),
(98, 14, 'New participant joined your drill: kian (kian@g.com)', 'barangay_responder/drills-and-trainings.php?leaderboard=9', 0, '2026-04-07 16:37:37'),
(99, 16, 'New participant joined your drill: relish (rel@g.c)', 'barangay_responder/drills-and-trainings.php?leaderboard=13', 0, '2026-04-07 16:38:03'),
(100, 16, 'New participant joined your drill: yana (y@gmail.c)', 'barangay_responder/drills-and-trainings.php?leaderboard=13', 0, '2026-04-07 17:07:41');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `question_id` int(11) NOT NULL,
  `selected_answer` enum('A','B','C','D') NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `points_earned` int(11) DEFAULT 0,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `user_id`, `question_id`, `selected_answer`, `is_correct`, `points_earned`, `completed_at`) VALUES
(1, 2, 1, 'B', 1, 10, '2026-04-05 02:12:00'),
(2, 2, 2, 'C', 1, 15, '2026-04-05 02:12:15'),
(3, 2, 11, 'B', 1, 10, '2026-04-05 02:15:30'),
(4, 14, 1, 'B', 1, 10, '2026-03-28 06:20:00'),
(5, 14, 6, 'C', 1, 15, '2026-03-28 06:22:00'),
(6, 16, 16, 'B', 1, 15, '2026-04-03 03:05:00'),
(7, 17, 26, 'A', 1, 10, '2026-04-05 11:40:00'),
(8, 17, 29, 'B', 1, 15, '2026-04-05 11:41:00');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `category` enum('earthquake','typhoon','flood','landslide','fire','general') NOT NULL,
  `question` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_answer` enum('A','B','C','D') NOT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `explanation` text DEFAULT NULL,
  `points` int(11) DEFAULT 10,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `category`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `difficulty`, `explanation`, `points`, `active`, `created_at`) VALUES
(1, 'earthquake', 'What is the first thing you should do when you feel an earthquake?', 'Run outside immediately', 'Drop, Cover, and Hold On', 'Open all windows', 'Call emergency services', 'B', 'easy', 'Drop to your hands and knees, take cover under a sturdy table, and hold on until the shaking stops.', 10, 1, '2026-02-14 04:28:33'),
(2, 'earthquake', 'During an earthquake, where is the safest place indoors?', 'Near windows', 'Under a doorway', 'Under a sturdy table', 'In the center of the room', 'C', 'medium', 'A sturdy table or desk provides the best protection from falling objects.', 15, 1, '2026-02-14 04:28:33'),
(3, 'earthquake', 'What should you do if you are outdoors during an earthquake?', 'Run into the nearest building', 'Move to an open area away from buildings', 'Lie flat on the ground', 'Hold onto a tree', 'B', 'medium', 'Move away from buildings, trees, streetlights, and utility wires.', 15, 1, '2026-02-14 04:28:33'),
(4, 'earthquake', 'How long should you expect aftershocks after a major earthquake?', 'Only within the first hour', 'Up to 24 hours', 'Days, weeks, or even months', 'Aftershocks do not occur', 'C', 'hard', 'Aftershocks can continue for extended periods after the main earthquake.', 20, 1, '2026-02-14 04:28:33'),
(5, 'earthquake', 'What percentage of earthquake injuries occur from falling objects?', '25%', '50%', '75%', '90%', 'C', 'hard', 'Most earthquake injuries are caused by falling or flying debris.', 20, 1, '2026-02-14 04:28:33'),
(6, 'typhoon', 'What is Signal No. 3 in typhoon warnings?', 'Winds of 30-60 kph', 'Winds of 61-120 kph', 'Winds of 121-170 kph', 'Winds above 220 kph', 'C', 'medium', 'Signal No. 3 indicates winds of 121-170 kph expected within 18 hours.', 15, 1, '2026-02-14 04:28:33'),
(7, 'typhoon', 'When should you evacuate before a typhoon?', 'When it starts raining', 'When authorities order evacuation', 'When the power goes out', 'After the typhoon passes', 'B', 'easy', 'Always follow official evacuation orders from authorities.', 10, 1, '2026-02-14 04:28:33'),
(8, 'typhoon', 'What should you do with outdoor items before a typhoon?', 'Leave them as they are', 'Tie them down with rope', 'Bring them inside or secure them', 'Cover them with tarp', 'C', 'easy', 'Secure all outdoor objects that could become projectiles.', 10, 1, '2026-02-14 04:28:33'),
(9, 'typhoon', 'How many days of emergency supplies should you prepare?', '1 day', '3 days', '7 days', '2 weeks', 'B', 'medium', 'Prepare at least 3 days of food, water, and supplies.', 15, 1, '2026-02-14 04:28:33'),
(10, 'typhoon', 'What is the eye of the typhoon?', 'The most dangerous part', 'A calm area in the center', 'The edge of the storm', 'The starting point', 'B', 'medium', 'The eye is calm but the storm will resume with equal force.', 15, 1, '2026-02-14 04:28:33'),
(11, 'fire', 'What should you do if your clothes catch fire?', 'Run to find water', 'Stop, Drop, and Roll', 'Remove clothes immediately', 'Wave your arms', 'B', 'easy', 'Stop, drop to the ground, and roll to smother the flames.', 10, 1, '2026-02-14 04:28:33'),
(12, 'fire', 'How should you escape a smoke-filled room?', 'Walk upright quickly', 'Crawl low under the smoke', 'Hold your breath and run', 'Cover face and walk', 'B', 'easy', 'Crawl low under the smoke where air is cleaner.', 10, 1, '2026-02-14 04:28:33'),
(13, 'fire', 'Before opening a door during a fire, you should:', 'Kick it open', 'Feel it with the back of your hand', 'Open it immediately', 'Wait for help', 'B', 'medium', 'Feel the door. If hot, there may be fire on the other side.', 15, 1, '2026-02-14 04:28:33'),
(14, 'fire', 'What does P.A.S.S. stand for when using a fire extinguisher?', 'Point, Aim, Spray, Sweep', 'Pull, Aim, Squeeze, Sweep', 'Push, Activate, Spray, Stop', 'Prepare, Aim, Shoot, Save', 'B', 'medium', 'Pull pin, Aim at base, Squeeze handle, Sweep side to side.', 15, 1, '2026-02-14 04:28:33'),
(15, 'fire', 'How often should smoke alarms be tested?', 'Once a year', 'Every 6 months', 'Monthly', 'Weekly', 'C', 'medium', 'Test smoke alarms monthly to ensure they work properly.', 15, 1, '2026-02-14 04:28:33'),
(16, 'flood', 'How many inches of flowing water can knock you off your feet?', '2 inches', '6 inches', '12 inches', '18 inches', 'B', 'medium', 'Just 6 inches of moving water can knock you down.', 15, 1, '2026-02-14 04:28:33'),
(17, 'flood', 'Should you drive through flooded roads?', 'Yes, if you drive slowly', 'Yes, if water looks shallow', 'No, never', 'Only in emergency', 'C', 'easy', 'Turn Around, Do not Drown. Never drive through floods.', 10, 1, '2026-02-14 04:28:33'),
(18, 'flood', 'Where should you go during a flash flood warning?', 'Basement', 'First floor', 'Higher ground', 'Stay where you are', 'C', 'easy', 'Move to higher ground immediately.', 10, 1, '2026-02-14 04:28:33'),
(19, 'flood', 'How much water per person per day during emergencies?', '1 liter', '2 liters', '4 liters', '6 liters', 'C', 'medium', 'Store at least 4 liters per person per day.', 15, 1, '2026-02-14 04:28:33'),
(20, 'flood', 'What if floodwater enters your home?', 'Try to pump it out', 'Turn off electricity', 'Open all windows', 'Continue normally', 'B', 'hard', 'Turn off electricity to prevent electrocution.', 20, 1, '2026-02-14 04:28:33'),
(21, 'landslide', 'Warning sign of an imminent landslide?', 'Heavy rain for hours', 'Cracks in ground', 'Both A and B', 'Clear blue skies', 'C', 'medium', 'Heavy rain and ground cracks warn of landslides.', 15, 1, '2026-02-14 04:28:33'),
(22, 'landslide', 'If you suspect a landslide, you should:', 'Wait and observe', 'Evacuate immediately', 'Go to basement', 'Take photos', 'B', 'easy', 'Leave the area immediately if you suspect landslide.', 10, 1, '2026-02-14 04:28:33'),
(23, 'landslide', 'Which areas are most prone to landslides?', 'Flat areas', 'Steep slopes', 'Coastal areas', 'Urban centers', 'B', 'easy', 'Steep slopes with loose soil are most vulnerable.', 10, 1, '2026-02-14 04:28:33'),
(24, 'landslide', 'Sound indicating an approaching landslide?', 'Complete silence', 'Birds chirping', 'Rumbling sound', 'Wind whistling', 'C', 'medium', 'Rumbling sound increasing in volume indicates landslide.', 15, 1, '2026-02-14 04:28:33'),
(25, 'landslide', 'When is it safe to return home after landslide?', 'Immediately after', 'When authorities declare safe', 'After 24 hours', 'When rain stops', 'B', 'hard', 'Only return when authorities confirm safety.', 20, 1, '2026-02-14 04:28:33'),
(26, 'general', 'Emergency hotline for Baguio MDRRMO?', '(074) 442-5377', '911', '(074) 444-1133', '(02) 8888-0000', 'A', 'easy', 'Baguio MDRRMO hotline is (074) 442-5377.', 10, 1, '2026-02-14 04:28:33'),
(27, 'general', 'How often should you update emergency kit?', 'Never', 'Once a year', 'Every 6 months', 'Every 5 years', 'C', 'medium', 'Check and update kit every 6 months.', 15, 1, '2026-02-14 04:28:33'),
(28, 'general', 'What should be in basic emergency kit?', 'Only food and water', 'Only first aid', 'Water, food, flashlight, radio, first aid', 'Just phone charger', 'C', 'medium', 'Kit needs water, food, light, communication, first aid.', 15, 1, '2026-02-14 04:28:33'),
(29, 'general', 'What does DRRM stand for?', 'Disaster Response Risk Management', 'Disaster Risk Reduction and Management', 'Disaster Relief Recovery Method', 'Direct Response Risk Mitigation', 'B', 'medium', 'DRRM is Disaster Risk Reduction and Management.', 15, 1, '2026-02-14 04:28:33'),
(30, 'general', 'How to help community prepare for disasters?', 'Keep info to yourself', 'Share tips with neighbors', 'Only help family', 'Wait for disasters', 'B', 'easy', 'Sharing knowledge strengthens community resilience.', 10, 1, '2026-02-14 04:28:33');

-- --------------------------------------------------------

--
-- Table structure for table `road_closures`
--

CREATE TABLE `road_closures` (
  `id` int(11) NOT NULL,
  `start_lat` decimal(10,8) NOT NULL,
  `start_lng` decimal(11,8) NOT NULL,
  `end_lat` decimal(10,8) NOT NULL,
  `end_lng` decimal(11,8) NOT NULL,
  `description` text NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('active','resolved','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `road_closures`
--

INSERT INTO `road_closures` (`id`, `start_lat`, `start_lng`, `end_lat`, `end_lng`, `description`, `severity`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 16.39780000, 120.60120000, 16.39890000, 120.60230000, 'Marcos Highway - Tree fell across road', 'high', 'active', 1, '2026-02-02 06:46:52', '2026-04-07 15:30:20'),
(2, 16.41920199, 120.60093087, 16.41840956, 120.60206854, 'Road closed due to emergency', 'high', 'active', 1, '2026-02-06 11:01:45', '2026-04-07 15:30:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','responder','resident') DEFAULT 'resident',
  `contact` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `id_photo` varchar(255) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `contact`, `address`, `barangay`, `id_photo`, `is_approved`, `created_at`) VALUES
(1, 'Administrator', 'admin@bantay.com', 'admin123', 'admin', '09171234567', 'City Hall', NULL, NULL, 1, '2025-12-02 07:41:36'),
(2, 'Responder Team', 'responder@bantay.com', 'resp123', 'responder', '09182345678', 'Brgy. City Hall, Baguio City', 'City Hall', NULL, 1, '2025-12-02 07:41:36'),
(14, 'Zaira Kalea', 'zk@bantay.com', '123456789', 'responder', '09563907620', '123 Brgy. Kagitingan, Baguio City', 'Kagitingan', NULL, 1, '2026-02-06 06:29:29'),
(15, 'Ellen test', 'ellen@gmail.com', 'ellen', 'responder', '09445552323', 'somewhere st', NULL, NULL, 0, '2026-02-06 06:35:48'),
(16, 'myles derick', 'myles@gmail.com', 'myles123', 'responder', '09123456789', '#13 quezon hill proper', 'Quezon Hill Proper', 'id_1771609883_6d1563cf.jpg', 1, '2026-02-20 17:51:23'),
(17, 'doi ivan gayados', 'ivan@gmail.com', 'ivan123', 'responder', '09123456789', 'dizon', 'Dizon Subdivision', NULL, 1, '2026-02-24 14:22:46');

-- --------------------------------------------------------

--
-- Table structure for table `user_checklist`
--

CREATE TABLE `user_checklist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_checklist`
--

INSERT INTO `user_checklist` (`id`, `user_id`, `item_id`, `completed`, `completed_at`, `notes`) VALUES
(1, 2, 1, 1, '2026-01-10 00:00:00', 'Bought 50L of water'),
(2, 2, 2, 1, '2026-01-11 04:30:00', 'Canned goods stocked'),
(3, 2, 3, 1, '2026-01-11 05:00:00', 'Checked flashlights'),
(4, 14, 1, 1, '2026-02-05 02:00:00', 'Prepared 24L'),
(5, 14, 11, 1, '2026-02-06 01:00:00', 'Contact list printed'),
(6, 16, 4, 1, '2026-03-10 08:00:00', 'Restocked first aid kit'),
(7, 17, 5, 1, '2026-03-15 10:20:00', 'Radio tested'),
(8, 17, 30, 1, '2026-04-03 23:30:00', 'Attended city drill');

-- --------------------------------------------------------

--
-- Table structure for table `user_stats`
--

CREATE TABLE `user_stats` (
  `user_id` int(11) NOT NULL,
  `total_points` int(11) DEFAULT 0,
  `quizzes_completed` int(11) DEFAULT 0,
  `drills_attended` int(11) DEFAULT 0,
  `checklist_completion_rate` decimal(5,2) DEFAULT 0.00,
  `preparedness_score` decimal(5,2) DEFAULT 0.00,
  `last_quiz_date` date DEFAULT NULL,
  `last_drill_date` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_stats`
--

INSERT INTO `user_stats` (`user_id`, `total_points`, `quizzes_completed`, `drills_attended`, `checklist_completion_rate`, `preparedness_score`, `last_quiz_date`, `last_drill_date`, `updated_at`) VALUES
(1, 1250, 12, 8, 85.71, 92.40, '2026-04-01', '2026-04-05', '2026-04-07 15:30:20'),
(2, 2450, 22, 15, 97.14, 98.10, '2026-04-06', '2026-04-06', '2026-04-07 15:30:20'),
(14, 850, 8, 5, 62.50, 74.30, '2026-03-28', '2026-04-02', '2026-04-07 15:30:20'),
(15, 150, 2, 1, 14.29, 31.20, '2026-02-15', '2026-02-20', '2026-04-07 15:30:20'),
(16, 1680, 16, 11, 80.00, 88.50, '2026-04-03', '2026-04-04', '2026-04-07 15:30:20'),
(17, 2100, 20, 14, 94.29, 95.80, '2026-04-05', '2026-04-06', '2026-04-07 15:30:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `barangay_stats`
--
ALTER TABLE `barangay_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barangay` (`barangay`);

--
-- Indexes for table `certifications`
--
ALTER TABLE `certifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_code` (`certificate_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `checklist_items`
--
ALTER TABLE `checklist_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drills`
--
ALTER TABLE `drills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `drill_participants`
--
ALTER TABLE `drill_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_drill_id` (`drill_id`);

--
-- Indexes for table `drill_participations`
--
ALTER TABLE `drill_participations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_participation` (`drill_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_verified_by` (`verified_by`);

--
-- Indexes for table `emergencies`
--
ALTER TABLE `emergencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `evacuation_centers`
--
ALTER TABLE `evacuation_centers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `road_closures`
--
ALTER TABLE `road_closures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_checklist`
--
ALTER TABLE `user_checklist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_item` (`user_id`,`item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `user_stats`
--
ALTER TABLE `user_stats`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `barangay_stats`
--
ALTER TABLE `barangay_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `certifications`
--
ALTER TABLE `certifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `checklist_items`
--
ALTER TABLE `checklist_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `drills`
--
ALTER TABLE `drills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `drill_participants`
--
ALTER TABLE `drill_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `drill_participations`
--
ALTER TABLE `drill_participations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `emergencies`
--
ALTER TABLE `emergencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `evacuation_centers`
--
ALTER TABLE `evacuation_centers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=330;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `road_closures`
--
ALTER TABLE `road_closures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `user_checklist`
--
ALTER TABLE `user_checklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `certifications`
--
ALTER TABLE `certifications`
  ADD CONSTRAINT `certifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `drills`
--
ALTER TABLE `drills`
  ADD CONSTRAINT `drills_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `drill_participants`
--
ALTER TABLE `drill_participants`
  ADD CONSTRAINT `drill_participants_ibfk_1` FOREIGN KEY (`drill_id`) REFERENCES `drills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `drill_participations`
--
ALTER TABLE `drill_participations`
  ADD CONSTRAINT `drill_participations_ibfk_1` FOREIGN KEY (`drill_id`) REFERENCES `drills` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `drill_participations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `emergencies`
--
ALTER TABLE `emergencies`
  ADD CONSTRAINT `emergencies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `road_closures`
--
ALTER TABLE `road_closures`
  ADD CONSTRAINT `fk_road_closures_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_checklist`
--
ALTER TABLE `user_checklist`
  ADD CONSTRAINT `user_checklist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_checklist_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `checklist_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_stats`
--
ALTER TABLE `user_stats`
  ADD CONSTRAINT `user_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
