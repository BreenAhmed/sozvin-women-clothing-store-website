-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 11, 2026 at 12:12 AM
-- Server version: 11.8.9-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u100517965_sozvin`
--

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--

CREATE TABLE `collections` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_ar` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `description_ar` text DEFAULT NULL,
  `slug` varchar(100) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `collection_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `name_ar` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `description_ar` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `additional_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_images`)),
  `sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sizes`)),
  `colors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`colors`)),
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `products_with_images`
-- (See below for the actual view)
--
CREATE TABLE `products_with_images` (
`id` int(11)
,`collection_id` int(11)
,`name` varchar(200)
,`name_ar` varchar(200)
,`description` text
,`description_ar` text
,`price` decimal(10,2)
,`sale_price` decimal(10,2)
,`sku` varchar(100)
,`stock_quantity` int(11)
,`is_active` tinyint(1)
,`is_featured` tinyint(1)
,`image_url` varchar(255)
,`additional_images` longtext
,`sizes` longtext
,`colors` longtext
,`tags` longtext
,`created_at` timestamp
,`updated_at` timestamp
,`all_images` longtext
,`primary_image` varchar(255)
,`image_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `image_order` int(11) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `alt_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
(2, 'admin', 'admin@sozvin.com', '$2y$10$Bnf2Rv1rgHv/pKYYOv27KuHTB7MCX1JiP/Zkeo1USsA7TYP5a0ky2', 'admin', '2025-12-11 22:24:13', '2025-12-11 22:27:09');

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `title_ar` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `description_ar` text DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `video_file` varchar(500) DEFAULT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `video_type` enum('youtube','vimeo','direct','embed','upload') DEFAULT 'youtube',
  `duration` int(11) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_collections_active` (`is_active`),
  ADD KEY `idx_collections_slug` (`slug`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_collection` (`collection_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_order` (`product_id`,`image_order`),
  ADD KEY `idx_primary` (`product_id`,`is_primary`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_sort` (`sort_order`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `collections`
--
ALTER TABLE `collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- --------------------------------------------------------

--
-- Structure for view `products_with_images`
--
DROP TABLE IF EXISTS `products_with_images`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u100517965_sozvin`@`localhost` SQL SECURITY DEFINER VIEW `products_with_images`  AS SELECT `p`.`id` AS `id`, `p`.`collection_id` AS `collection_id`, `p`.`name` AS `name`, `p`.`name_ar` AS `name_ar`, `p`.`description` AS `description`, `p`.`description_ar` AS `description_ar`, `p`.`price` AS `price`, `p`.`sale_price` AS `sale_price`, `p`.`sku` AS `sku`, `p`.`stock_quantity` AS `stock_quantity`, `p`.`is_active` AS `is_active`, `p`.`is_featured` AS `is_featured`, `p`.`image_url` AS `image_url`, `p`.`additional_images` AS `additional_images`, `p`.`sizes` AS `sizes`, `p`.`colors` AS `colors`, `p`.`tags` AS `tags`, `p`.`created_at` AS `created_at`, `p`.`updated_at` AS `updated_at`, (select group_concat(`pi`.`image_url` order by `pi`.`image_order` ASC separator ',') from `product_images` `pi` where `pi`.`product_id` = `p`.`id`) AS `all_images`, (select `pi`.`image_url` from `product_images` `pi` where `pi`.`product_id` = `p`.`id` and `pi`.`is_primary` = 1 limit 1) AS `primary_image`, (select count(0) from `product_images` `pi` where `pi`.`product_id` = `p`.`id`) AS `image_count` FROM `products` AS `p` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
