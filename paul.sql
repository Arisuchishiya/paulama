-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2025 at 01:18 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `paul`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `client_name`, `phone`, `email`, `created_at`) VALUES
(1, 'kenah', '0714262934', 'kenedykiprotich00@gmail.com', '2025-03-28 11:01:27'),
(3, 'dede', '0714262933', 'AS310005524@kisiiuniversity.ac.ke', '2025-03-28 15:17:29'),
(5, 'Kennedy', '0714262937', 'tbag9934@gmail.com', '2025-03-28 20:16:04'),
(8, 'william', '0734567894', 'hepax43294@kindomd.com', '2025-04-18 10:12:53'),
(9, 'kilpog', '0734567896', 'kenedykioprotich00@gmail.com', '2025-04-18 10:41:19'),
(10, 'diss', '0756789099', 'kennedykiprotich00@gmail.com', '2025-04-18 10:55:20');

-- --------------------------------------------------------

--
-- Table structure for table `debts`
--

CREATE TABLE `debts` (
  `id` int(11) NOT NULL,
  `retailer_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `paid_date` timestamp NULL DEFAULT NULL,
  `payment_status` enum('pending','paid') DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `debts`
--

INSERT INTO `debts` (`id`, `retailer_id`, `sale_id`, `amount`, `paid_amount`, `payment_method`, `paid_date`, `payment_status`, `description`, `created_at`) VALUES
(11, 1, NULL, 4000.00, 4000.00, 'cash', '2025-04-02 08:35:28', 'paid', '', '2025-04-02 08:29:35'),
(12, 1, NULL, 4000.00, 4000.00, 'bank', '2025-04-02 08:37:42', 'paid', '', '2025-04-02 08:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `price`) VALUES
(1, 'broilers', 4000.00),
(2, 'chicks', 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `retailers`
--

CREATE TABLE `retailers` (
  `id` int(11) NOT NULL,
  `retailer_name` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `retailers`
--

INSERT INTO `retailers` (`id`, `retailer_name`, `phone`, `email`, `address`, `created_at`) VALUES
(1, 'lemmoh', '0723678213', 'lemmoh@gmail.com', 'nakuru', '2025-03-28 11:05:47'),
(2, 'stelah', '0714262934', 'tbag4934@gmail.com', '52400', '2025-03-28 14:52:22');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `historical_product_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','mpesa','bank','credit') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `client_id`, `product_id`, `historical_product_name`, `quantity`, `amount`, `payment_method`, `description`, `created_at`) VALUES
(19, 1, 1, NULL, 1, 4000.00, 'cash', NULL, '2025-04-02 08:35:13'),
(20, NULL, NULL, NULL, NULL, -4000.00, 'cash', 'Debt payment for retailer ID: 1', '2025-04-02 08:35:28'),
(21, 1, 1, NULL, 5, 20000.00, 'cash', NULL, '2025-04-02 08:35:51'),
(22, NULL, NULL, NULL, NULL, -4000.00, 'cash', 'Debt payment for retailer ID: 1', '2025-04-02 08:37:01'),
(23, NULL, NULL, NULL, NULL, -4000.00, '', 'Debt payment for retailer ID: 1', '2025-04-02 08:37:42'),
(24, 5, 2, NULL, 3, 3000.00, '', NULL, '2025-04-07 16:50:32'),
(25, 8, 1, NULL, 6, 24000.00, '', NULL, '2025-04-18 10:13:11'),
(26, 3, 2, NULL, 2, 2000.00, '', NULL, '2025-04-18 10:24:59'),
(27, 1, 1, NULL, 5, 20000.00, '', NULL, '2025-04-18 10:30:17'),
(28, 8, 1, NULL, 9, 36000.00, 'cash', NULL, '2025-04-18 10:32:43'),
(29, 1, 1, NULL, 2, 8000.00, '', NULL, '2025-04-18 10:37:05'),
(30, 1, 1, NULL, 7, 28000.00, '', NULL, '2025-04-18 10:41:38'),
(31, 9, 1, NULL, 3, 12000.00, '', NULL, '2025-04-18 10:42:05'),
(32, 9, 1, NULL, 2, 8000.00, '', NULL, '2025-04-18 10:45:33'),
(33, 9, 1, NULL, 1, 4000.00, '', NULL, '2025-04-18 10:46:02'),
(34, 8, 2, NULL, 3, 3000.00, 'cash', NULL, '2025-04-18 10:46:45'),
(35, 8, 1, NULL, 8, 32000.00, '', NULL, '2025-04-18 10:47:43'),
(36, 9, 1, NULL, 4, 16000.00, 'cash', NULL, '2025-04-18 10:48:14'),
(37, 9, 1, NULL, 6, 24000.00, '', NULL, '2025-04-18 10:54:06'),
(38, 10, 2, NULL, 2, 2000.00, '', NULL, '2025-04-18 10:55:37'),
(39, 10, 1, NULL, 7, 28000.00, 'cash', NULL, '2025-04-18 10:56:27'),
(40, 10, 1, NULL, 3, 12000.00, 'mpesa', NULL, '2025-04-18 11:03:38'),
(41, 9, 1, NULL, 5, 20000.00, '', NULL, '2025-04-18 11:04:28'),
(42, 9, 1, NULL, 4, 16000.00, '', NULL, '2025-04-18 11:08:59'),
(43, 9, 1, NULL, 2, 8000.00, '', NULL, '2025-04-18 11:13:44'),
(44, 3, 1, NULL, 78, 312000.00, '', NULL, '2025-04-18 11:15:25');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`