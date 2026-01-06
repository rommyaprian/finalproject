-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 21, 2025 at 06:14 PM
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
-- Database: `apk_eventkonser`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_date` date DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `total_tickets` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `event_date`, `date`, `time`, `location`, `description`, `total_tickets`, `price`, `created_at`) VALUES
(9, 'New Year Festival', NULL, '2025-12-31', '19:17:00', 'Universitas Buana Perjuangan', NULL, 0, 200000.00, '2025-12-21 07:41:30');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `event_id` int(11) UNSIGNED NOT NULL,
  `ticket_code` varchar(16) NOT NULL,
  `transaction_code` varchar(32) DEFAULT NULL,
  `price` decimal(10,0) NOT NULL,
  `status` enum('PENDING','PAID','USED','CANCELED') NOT NULL DEFAULT 'PENDING',
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_date` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `reminder_sent_at` datetime DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `event_id`, `ticket_code`, `transaction_code`, `price`, `status`, `purchase_date`, `payment_date`, `used_at`, `reminder_sent`, `reminder_sent_at`, `payment_method`, `payment_proof`) VALUES
(31, 19, 8, 'EC8TJTEOTP2AFBS7', NULL, 5000000, 'PAID', '2025-12-21 05:15:21', '2025-12-21 12:15:25', NULL, 0, NULL, NULL, NULL),
(32, 20, 9, 'DG417Y8J8S941CV3', NULL, 2000000, 'PAID', '2025-12-21 10:42:26', '2025-12-21 17:42:30', NULL, 0, NULL, NULL, NULL),
(33, 20, 9, 'R2YHF78X0ELD7MPI', NULL, 2000000, 'PAID', '2025-12-21 11:57:28', '2025-12-21 18:57:33', NULL, 0, NULL, NULL, NULL),
(34, 21, 9, 'RYNL43MWI98U8FGL', NULL, 200000, 'USED', '2025-12-21 12:14:58', NULL, '2025-12-21 23:18:55', 1, '2025-12-21 19:20:00', NULL, NULL),
(35, 21, 9, 'SPX4BUMSEFOI62FL', NULL, 200000, 'PENDING', '2025-12-21 15:52:26', NULL, NULL, 0, NULL, NULL, NULL),
(36, 21, 9, 'CFB39UK73J0KE9D3', NULL, 200000, 'PENDING', '2025-12-21 15:52:38', NULL, NULL, 0, NULL, NULL, NULL),
(37, 21, 9, 'UA01B9E0BU2WA2KL', NULL, 200000, 'PENDING', '2025-12-21 15:53:42', NULL, NULL, 0, NULL, NULL, NULL),
(38, 21, 9, 'N25B3WP2ISRUOAY5', NULL, 200000, 'PENDING', '2025-12-21 16:05:28', NULL, NULL, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('user','admin','super_admin') NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(64) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `is_active`, `created_at`, `reset_token`, `token_expiry`) VALUES
(20, 'Toyfeinn', '$2y$10$TSS0BMCcML4dHHu8LcoP2./in/VISFzoS20.vLa7rcRTU5q30XVYS', 'rommyaprian@gmail.com', 'super_admin', 1, '2025-12-21 07:39:16', NULL, NULL),
(21, 'rmmy', '$2y$10$acV7iFDoOb9GoBxtmgTiuO7IsNm.ygeXWJ6jE0DynE4DGeAZJELZW', 'if24.rommyprasetyo@mhs.ubpkarawang.ac.id', 'user', 1, '2025-12-21 07:41:49', NULL, NULL),
(22, 'ry', '$2y$10$4xDIvFe59Vif9BJNflsRseLXG.8YBiKs7lTCeGKtzmK7//rZ5X86q', 'rommyaprian05@gmail.com', 'admin', 1, '2025-12-21 07:45:02', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_code` (`ticket_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
