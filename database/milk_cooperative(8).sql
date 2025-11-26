-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2025 at 03:02 PM
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
-- Database: `milk_cooperative`
--

-- --------------------------------------------------------

--
-- Table structure for table `coop_account`
--

CREATE TABLE `coop_account` (
  `id` int(11) NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coop_account`
--

INSERT INTO `coop_account` (`id`, `balance`, `last_updated`) VALUES
(1, 932000.00, '2025-06-11 14:20:14');

-- --------------------------------------------------------

--
-- Table structure for table `coop_transactions`
--

CREATE TABLE `coop_transactions` (
  `id` int(11) NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coop_transactions`
--

INSERT INTO `coop_transactions` (`id`, `type`, `amount`, `description`, `related_id`, `created_at`) VALUES
(1, 'debit', 34000.00, 'Payment to farmer/industry (Payment ID: 6)', 6, '2025-06-11 14:16:38'),
(2, 'debit', 34000.00, 'Payment to farmer/industry (Payment ID: 6)', 6, '2025-06-11 14:20:14');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_shifts`
--

CREATE TABLE `delivery_shifts` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_shifts`
--

INSERT INTO `delivery_shifts` (`id`, `name`, `start_time`, `end_time`, `created_at`) VALUES
(1, 'Morning', '06:00:00', '12:00:00', '2025-05-26 09:30:06'),
(2, 'Afternoon', '12:00:00', '18:00:00', '2025-05-26 09:30:06'),
(3, 'Evening', '18:00:00', '23:59:59', '2025-05-26 09:30:06');

-- --------------------------------------------------------

--
-- Table structure for table `farmers`
--

CREATE TABLE `farmers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `farmer_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `quota` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_delivery_date` datetime DEFAULT NULL,
  `last_delivery_quantity` decimal(10,2) DEFAULT NULL,
  `last_delivery_quality` enum('A','B','C') DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmers`
--

INSERT INTO `farmers` (`id`, `user_id`, `farmer_id`, `full_name`, `phone`, `email`, `address`, `quota`, `created_at`, `last_delivery_date`, `last_delivery_quantity`, `last_delivery_quality`, `account_name`, `account_number`) VALUES
(15, 21, 'FZOLIRAQN', 'haha', '0628030877', 'haha@gmail.com', 'Kilimanjaro, Tanzania', 34.00, '2025-06-11 09:51:18', NULL, NULL, NULL, NULL, NULL),
(16, 22, 'FCWX35G2E', 'haha', '8489434324', 'h@gmail.com', 'Kunduchimtongani\r\n1232', 77.97, '2025-06-11 10:14:52', NULL, NULL, NULL, NULL, NULL),
(17, 24, 'FGZ9V5R7O', 'mn', '2992929229', 'mn@gmail.com', 'Moshi', 12.00, '2025-06-20 12:28:07', NULL, NULL, NULL, NULL, NULL),
(18, 25, 'FRKT5FEVQ', 'caca-FRKT5FEVQ', '756775664', 'caca@gmail.com', 'Dar es salaam', 320.00, '2025-06-23 07:17:01', NULL, NULL, NULL, NULL, NULL),
(19, 28, 'FJ9I10ZVU', 'gaga-FJ9I10ZVU', '844378375', 'gaga@gmail.com', 'Bagamoyo-Mapinga-Pwani', 42.00, '2025-06-23 08:36:46', NULL, NULL, NULL, NULL, NULL),
(20, 29, 'FCXK10DA2', 'vava-FCXK10DA2', '3423423455', 'vava@gmail.com', 'Tanga', 4033.00, '2025-06-23 09:25:21', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `industries`
--

CREATE TABLE `industries` (
  `id` int(11) NOT NULL,
  `industry_id` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `company_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `industry_type` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `industries`
--

INSERT INTO `industries` (`id`, `industry_id`, `user_id`, `company_name`, `contact_person`, `phone`, `email`, `address`, `created_at`, `industry_type`, `created_by`, `account_name`, `account_number`) VALUES
(1, 'I00000001', NULL, 'Chipmunk Expeditions', 'Alvin Calvin', '0628030877', 'alvinchipmunk196@gmail.com', 'Kunduchimtongani\r\n1232', '2025-05-27 09:46:09', 'Other', NULL, NULL, NULL),
(3, 'I00000003', 7, 'Morisson Africa Tour And Safari', 'JOSEPH KESSY', '0621963494', 'wildkingsafrica@gmail.com', 'Moshi\r\nRau', '2025-05-27 09:57:15', '', NULL, NULL, NULL),
(4, 'I00000004', 11, 'Morisson Africa Tour And Safari', 'Alvin Calvin', '0628030877', 'momo@gmail.com', 'Moshi', '2025-05-30 14:46:36', 'Exporter', NULL, NULL, NULL),
(5, 'I00000005', 13, 'west wild', 'Alvin Calvin', '0628030877', 'haha@gmail.com', 'Moshi', '2025-06-10 15:03:34', '', NULL, NULL, NULL),
(6, 'I00000006', 23, 'gb', 'gb', '1234567890', 'gb@gmail.com', 'Moshi', '2025-06-20 12:25:10', 'Dairy Processor', 1, NULL, NULL),
(7, 'ICYTQEG05', 27, 'nana company', 'nana conna', '38247784368', 'naco@gmail.com', 'Moshi Urban', '2025-06-23 07:49:35', 'Exporter', 1, NULL, NULL),
(8, 'IVQ075YMH', 30, 'hahah', 'hahaha', '324534534', 'klm@gmail.com', 'Moshi', '2025-06-23 12:17:44', 'Local Distributor', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `milk_deliveries`
--

CREATE TABLE `milk_deliveries` (
  `id` int(11) NOT NULL,
  `farmer_id` varchar(20) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `quality_grade` enum('A','B','C') NOT NULL,
  `delivery_date` datetime NOT NULL,
  `delivery_time` time DEFAULT NULL,
  `shift` varchar(50) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `quality_score` decimal(3,1) DEFAULT NULL,
  `ph_level` decimal(4,2) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `fat_content` decimal(4,2) DEFAULT NULL,
  `quality_notes` text DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL COMMENT 'Admin/Data clerk who recorded the delivery'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `milk_deliveries`
--

INSERT INTO `milk_deliveries` (`id`, `farmer_id`, `quantity`, `quality_grade`, `delivery_date`, `delivery_time`, `shift`, `status`, `created_at`, `quality_score`, `ph_level`, `temperature`, `fat_content`, `quality_notes`, `recorded_by`) VALUES
(16, 'FCWX35G2E', 21.00, 'A', '2025-06-11 00:00:00', NULL, 'evening', 'approved', '2025-06-11 12:10:07', NULL, NULL, NULL, NULL, 'None', NULL),
(17, 'FRKT5FEVQ', 32.00, 'A', '2025-06-24 00:00:00', '11:07:00', 'Morning', 'approved', '2025-06-23 08:04:29', NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'FJ9I10ZVU', 32.00, 'A', '2025-06-24 00:00:00', NULL, 'morning', 'approved', '2025-06-23 09:16:37', NULL, NULL, NULL, NULL, 'not now', NULL),
(19, 'FCXK10DA2', 222.00, 'A', '2025-06-23 00:00:00', NULL, 'evening', 'approved', '2025-06-23 09:30:03', NULL, NULL, NULL, NULL, 'Top Knotvh', NULL),
(20, 'FCXK10DA2', 10.00, 'A', '2025-06-23 00:00:00', NULL, 'evening', 'approved', '2025-06-23 09:46:36', NULL, NULL, NULL, NULL, 'NOne', NULL),
(21, 'FCXK10DA2', 10.00, 'A', '2025-06-23 00:00:00', NULL, 'morning', 'rejected', '2025-06-23 09:47:05', NULL, NULL, NULL, NULL, 'wi', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `industry_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `order_date` datetime NOT NULL,
  `delivery_date` datetime NOT NULL,
  `status` enum('pending','approved','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `industry_id`, `quantity`, `order_date`, `delivery_date`, `status`, `created_at`) VALUES
(1, 8, 33.00, '0000-00-00 00:00:00', '2025-06-23 00:00:00', 'approved', '2025-06-23 12:42:36');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `industry_id` int(11) DEFAULT NULL,
  `farmer_id` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `payment_method` enum('cash','bank_transfer') NOT NULL,
  `status` enum('unpaid','paid') DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `industry_id`, `farmer_id`, `amount`, `payment_date`, `payment_method`, `status`, `created_at`, `notes`, `account_name`, `account_number`) VALUES
(2, NULL, 'FZOLIRAQN', 129000.00, '2025-05-29 00:00:00', 'cash', 'unpaid', '2025-05-29 10:46:00', '40 Litres', NULL, NULL),
(3, NULL, 'FCWX35G2E', 212121.00, '2025-05-29 00:00:00', 'cash', 'unpaid', '2025-05-29 10:50:05', '32 Litres', NULL, NULL),
(6, NULL, 'FCWX35G2E', 34000.00, '2025-06-11 00:00:00', 'cash', 'paid', '2025-06-11 10:20:49', 'Paid', NULL, NULL),
(8, NULL, 'FRKT5FEVQ', 121.00, '2025-06-23 00:00:00', '', 'unpaid', '2025-06-23 07:59:50', 'noen', 'COope bank', '3242343243'),
(9, NULL, 'FJ9I10ZVU', 48000.00, '2025-06-23 00:00:00', '', 'paid', '2025-06-23 09:18:29', 'The payment of 10 Litres', 'BBICT3 Coop Milk Society', '99883774334'),
(10, NULL, 'FCXK10DA2', 366434.00, '2025-06-23 00:00:00', '', 'paid', '2025-06-23 10:11:12', 'None', 'Exim Coop', '14234324324');

-- --------------------------------------------------------

--
-- Table structure for table `quality_standards`
--

CREATE TABLE `quality_standards` (
  `id` int(11) NOT NULL,
  `grade` enum('A','B','C') NOT NULL,
  `min_ph` decimal(4,2) NOT NULL,
  `max_ph` decimal(4,2) NOT NULL,
  `min_temperature` decimal(4,1) NOT NULL,
  `max_temperature` decimal(4,1) NOT NULL,
  `min_fat_content` decimal(4,2) NOT NULL,
  `max_fat_content` decimal(4,2) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quality_standards`
--

INSERT INTO `quality_standards` (`id`, `grade`, `min_ph`, `max_ph`, `min_temperature`, `max_temperature`, `min_fat_content`, `max_fat_content`, `description`, `created_at`) VALUES
(1, 'A', 6.50, 6.80, 2.0, 4.0, 3.20, 4.50, 'Premium quality milk with excellent characteristics', '2025-05-26 09:30:06'),
(2, 'B', 6.30, 6.90, 2.0, 5.0, 2.80, 3.50, 'Good quality milk with acceptable characteristics', '2025-05-26 09:30:06'),
(3, 'C', 6.00, 7.00, 2.0, 6.0, 2.50, 3.20, 'Standard quality milk with basic characteristics', '2025-05-26 09:30:06');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `sale_date` date NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `price_per_liter` decimal(10,2) NOT NULL,
  `total_profit` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `farmer_id`, `sale_date`, `quantity`, `price_per_liter`, `total_profit`, `created_at`) VALUES
(1, 20, '2025-06-23', 300.00, 10000.00, 3000000.00, '2025-06-23 09:41:08');

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','farmer','industry','data_clerk') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$tQf7KFntPFkFc2JaLee/KevK6Wsq6E39WXelXY1PQX7ZyzhzEJEBW', 'admin', '2025-05-26 09:30:06'),
(4, 'alvin', '$2y$10$eyWlnR4B3FQkohdnUBAcA.C874ZRojDFogB284.B5QQ88fVITyw6m', 'farmer', '2025-05-26 12:13:57'),
(5, 'calvin', '$2y$10$Ui7ReChancbFzIubruG0l.Afqga/hnoB80MgxkpoR46kJzvim7j1W', 'farmer', '2025-05-26 12:35:50'),
(6, 'dulle', '$2y$10$/aKFPN5O2uQuCVPW1WAqWOkou3SXHucLcMVEMtf5dje68ENiVcyM2', 'farmer', '2025-05-26 12:53:54'),
(7, 'morissonafricatourandsafari', '$2y$10$5R6FMa.TXz1kcgfzLqBMD.ahzfTSB1T2YNfQDZUVpl.NPx.vxgTD.', 'industry', '2025-05-27 09:57:15'),
(8, 'josephkessy', '$2y$10$zsXBXpMqnF.cwBOgYeESg.enRU.Q6nTjX2Z/PAhikNOLcRC7977Iq', 'farmer', '2025-05-27 09:59:44'),
(9, 'kelvindulle', '$2y$10$TfVMS63IopSwkoDLRlWWjeJU2tsPoOS0x5gtRCDfaF8Xf6nf82u/6', 'farmer', '2025-05-27 10:01:22'),
(10, 'boniphaceedaudi', '$2y$10$HkW2NMkmJS74hgXlQAoCx.W8jhhqWp0o/6cdRoAlwZaWIxIrUUz2m', 'farmer', '2025-05-30 14:44:59'),
(11, 'morissonafricatourandsafari359', '$2y$10$DVWUsD.OBMrmjw3LdBnKieR/aozgwn44XwSa8gcQs7rfZxP9V6Dou', 'industry', '2025-05-30 14:46:36'),
(12, 'fafa', '$2y$10$G6LH4aJhlTyb46VC/Jmkoe9LRMbFkyBCROXk76RJgSCclYNXXLI02', 'farmer', '2025-06-10 15:02:38'),
(13, 'westwild', '$2y$10$EFVTuVCkDDaBjumPu7nWL.btZcL2PmXXCE.b/wny3PK.MYwfJzeiG', 'industry', '2025-06-10 15:03:34'),
(14, 'ada', '$2y$10$5MqheqAuaDzCzc76rm9CTuMb8.0PaDT20vlzMQc6872TjL2bj5/PG', 'farmer', '2025-06-11 09:15:23'),
(15, 'alvin1', '$2y$10$49GWslpGSTctkMQXhkl3BugHI/X7OPdl/H/VSyyPDEhzfzFR/F86u', 'farmer', '2025-06-11 09:27:07'),
(21, 'haha', '$2y$10$RB/bhW7ZI01HYfwRiqGXbuE.dDH.Md2dz2xsrpraM9ajLQG12.iFu', 'farmer', '2025-06-11 09:51:18'),
(22, 'haha1', '$2y$10$nT8/eWJLUo5ugUUKs.tkp.0sGZDPYsq9qn9PUJ8XDaky21DmZOAzy', 'farmer', '2025-06-11 10:14:52'),
(23, 'gb', '$2y$10$QOCQG46VhW6MjLR4.dwhPOpW6ffBEqJAmcF8V22F64tcBD0XSeHU.', 'industry', '2025-06-20 12:25:10'),
(24, 'mn', '$2y$10$eohZEKeIuNgHXCdLJdWyc.zHe7ZSDcTR2IBDOXgfdnEf6sAWxabmq', 'farmer', '2025-06-20 12:28:07'),
(25, 'caca', '$2y$10$SHw0H8y/lyU2FHOyz5pG9.wFrYoXpHNAFczOKCjZj1afGQW//3rKu', 'farmer', '2025-06-23 07:17:01'),
(27, 'nanacompany', '$2y$10$W6mKjNhdbaELxRFaF8Y6.eTjIPW4d27Oei0WCKCKd0GDPj6mzAM12', 'industry', '2025-06-23 07:49:35'),
(28, 'gaga', '$2y$10$SKPJD3je5NFqYxHHJGgsGO9xhhigizcCjHzvsduce1crgnZXBT6jK', 'farmer', '2025-06-23 08:36:46'),
(29, 'vava', '$2y$10$l5DYgsl6hb695FDYWA3nbO2xBDQ3jN9GURps0ztxeBhbIftezDsay', 'farmer', '2025-06-23 09:25:21'),
(30, 'hahah', '$2y$10$0K82Kl9QavOqDtRHhf0On.UaW.ZQ3jdpTbShSolN8xrkMZ8hKils6', 'industry', '2025-06-23 12:17:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `coop_account`
--
ALTER TABLE `coop_account`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coop_transactions`
--
ALTER TABLE `coop_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_shifts`
--
ALTER TABLE `delivery_shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `farmers`
--
ALTER TABLE `farmers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `farmer_id` (`farmer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `industries`
--
ALTER TABLE `industries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `industry_id` (`industry_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_created_by` (`created_by`);

--
-- Indexes for table `milk_deliveries`
--
ALTER TABLE `milk_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`),
  ADD KEY `milk_deliveries_ibfk_2` (`recorded_by`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `industry_id` (`industry_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`),
  ADD KEY `idx_payments_farmer_id` (`farmer_id`),
  ADD KEY `idx_payments_payment_date` (`payment_date`),
  ADD KEY `industry_id` (`industry_id`);

--
-- Indexes for table `quality_standards`
--
ALTER TABLE `quality_standards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grade` (`grade`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `coop_account`
--
ALTER TABLE `coop_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `coop_transactions`
--
ALTER TABLE `coop_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `delivery_shifts`
--
ALTER TABLE `delivery_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `farmers`
--
ALTER TABLE `farmers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `industries`
--
ALTER TABLE `industries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `milk_deliveries`
--
ALTER TABLE `milk_deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `quality_standards`
--
ALTER TABLE `quality_standards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `farmers`
--
ALTER TABLE `farmers`
  ADD CONSTRAINT `farmers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `industries`
--
ALTER TABLE `industries`
  ADD CONSTRAINT `fk_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `industries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `milk_deliveries`
--
ALTER TABLE `milk_deliveries`
  ADD CONSTRAINT `milk_deliveries_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`),
  ADD CONSTRAINT `milk_deliveries_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`industry_id`) REFERENCES `industries` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`industry_id`) REFERENCES `industries` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
