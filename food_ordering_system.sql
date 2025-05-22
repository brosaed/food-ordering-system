-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2025 at 10:49 AM
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
-- Database: `food_ordering_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image_path`) VALUES
(1, 'Desserts', 'Dessertss', NULL),
(3, 'Main Courses', '', NULL),
(4, 'Pizza', 'Pizza', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_available`) VALUES
(1, 3, 'Chicken Burgers', 'text dhadhan fiican leh', 15.50, 'assets/images/menu/680a229163ddc.webp', 1),
(2, 1, 'Single Chicken Burger', 'Single Chicken Burger', 40.00, 'assets/images/menu/680a237096f1e.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_role` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_role`, `message`, `is_read`, `created_at`) VALUES
(1, 'admin', 'Order #ORD-6813231CD6EB8 is ready for delivery.', 0, '2025-05-08 18:33:35'),
(2, 'delivery', 'Order #ORD-6813231CD6EB8 is ready for delivery.', 0, '2025-05-08 18:33:35'),
(3, 'admin', 'Order #ORD-6811D6B708F41 is ready for delivery.', 0, '2025-05-08 18:33:38'),
(4, 'delivery', 'Order #ORD-6811D6B708F41 is ready for delivery.', 0, '2025-05-08 18:33:38'),
(5, 'admin', 'Order #ORD-682043DC4AFA3 is ready for delivery.', 0, '2025-05-11 09:32:02'),
(6, 'delivery', 'Order #ORD-682043DC4AFA3 is ready for delivery.', 0, '2025-05-11 09:32:02');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(20) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_address` text NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `status` enum('pending','confirmed','preparing','ready','out_for_delivery','delivered','cancelled') DEFAULT 'pending',
  `status_updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_email` varchar(255) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL,
  `promo_code_id` int(11) DEFAULT NULL,
  `public_access_code` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `customer_name`, `customer_phone`, `customer_address`, `special_instructions`, `status`, `status_updated_at`, `total_amount`, `created_at`, `customer_email`, `discount_amount`, `final_amount`, `promo_code_id`, `public_access_code`) VALUES
(1, 'ORD-6810C3575EF90', 'Saed Ahmed', '0618804513', 'aaa', 'axaa', 'pending', '2025-04-30 07:34:55', 80.00, '2025-04-29 12:17:27', 'saed@prime-techs.com', 0.00, 80.00, NULL, NULL),
(2, 'ORD-6810D9A9E13E6', 'Saed Ahmed', '0618804513', 'dszdsg', 'svdsaads', 'pending', '2025-04-30 07:34:55', 160.00, '2025-04-29 13:52:41', 'saed@prime-techs.com', 0.00, 160.00, NULL, NULL),
(3, 'ORD-6810EA990506B', 'Abdisalam Haji Muse', '0618804513', 'test', 'axbajh', 'delivered', '2025-05-08 13:43:06', 40.00, '2025-04-29 15:04:57', 'info@prime-techs.com', 0.00, 40.00, NULL, NULL),
(4, 'ORD-6810ECA99CA24', 'Abdisalam Haji Muse', '0618804513', 'axax', NULL, 'delivered', '2025-04-30 07:34:55', 55.50, '2025-04-29 15:13:45', 'info@prime-techs.com', 0.00, 55.50, NULL, NULL),
(5, 'ORD-6811D6B708F41', 'Saed Mohamed', '0618804513', 'Delivery Address *', 'Delivery Address *', 'delivered', '2025-05-08 12:49:08', 555.00, '2025-04-30 07:52:23', 'a@gmail.com', 0.00, 555.00, NULL, NULL),
(6, 'ORD-6813231CD6EB8', 'Ikram', '0618804513', 'ikram@gmail.com', 'ikram@gmail.com', 'delivered', '2025-05-08 15:56:53', 111.00, '2025-05-01 07:30:36', 'ikram@gmail.com', 0.00, 111.00, NULL, NULL),
(7, 'ORD-681C594DC442C', 'Saed Mohamed', '0618804513', 'axaa', 'axaa', 'delivered', '2025-05-08 13:41:03', 40.00, '2025-05-08 07:12:13', 'saed@gmail.com', 0.00, 40.00, NULL, NULL),
(8, 'ORD-681CC1FB9A266', 'Abdisalam Haji Muse', '0618804513', 'sdfs', NULL, 'delivered', '2025-05-08 14:39:32', 111.00, '2025-05-08 14:38:51', 'info@prime-techs.com', 0.00, 111.00, NULL, NULL),
(9, 'ORD-681CD58999B43', 'Haji Muse', '0618804513', 'aaxa', 'xaaxa', 'pending', '2025-05-08 16:02:17', 197.50, '2025-05-08 16:02:17', 'info@prime-techs.com', 0.00, 197.50, NULL, NULL),
(10, 'ORD-681CD5B9AF3BD', 'Haji Muse', '0618804513', 'aaxa', 'xaaxa', 'pending', '2025-05-08 16:03:05', 197.50, '2025-05-08 16:03:05', 'info@prime-techs.com', 0.00, 197.50, NULL, NULL),
(11, 'ORD-681CD7C3AE42D', 'Faisal', '0618804513', 'axax', 'xaa', 'pending', '2025-05-08 16:11:47', 15.50, '2025-05-08 16:11:47', 'info@prime-techs.com', 0.00, 15.50, NULL, NULL),
(12, 'ORD-681CD8E5E6437', 'Faisal', '0618804513', 'axa', NULL, 'pending', '2025-05-08 16:16:37', 55.50, '2025-05-08 16:16:37', 'info@prime-techs.com', 0.00, 55.50, NULL, NULL),
(13, 'ORD-681CDA987C73B', 'Faisal', '0618804513', 'sad', NULL, 'pending', '2025-05-08 16:23:52', 317.50, '2025-05-08 16:23:52', 'info@prime-techs.com', 0.00, 317.50, NULL, NULL),
(14, 'ORD-681CDDCBAFC9A', 'Abdisalam Haji Muse', '0618804513', 'axa', NULL, 'pending', '2025-05-08 16:37:31', 31.00, '2025-05-08 16:37:31', 'info@prime-techs.com', 0.00, 31.00, NULL, NULL),
(15, 'ORD-682043DC4AFA3', 'Saed Mohamed Ahmed', '0618804513', 'Korontada Street, Wadajir District', NULL, 'delivered', '2025-05-11 06:30:18', 40.00, '2025-05-11 06:29:48', 'saed.madkaash@gmail.com', 0.00, 40.00, NULL, NULL),
(16, 'ORD-6825B734CB186', 'ALi', '0618804513', 'axa', 'axa', 'confirmed', '2025-05-15 09:43:51', 40.00, '2025-05-15 09:43:16', 'saed@gmail.com', 0.00, 40.00, NULL, NULL),
(17, 'ORD-682B2236E1BC4', 'Saed Mohamed', '0618804513', 'axaxa', NULL, 'pending', '2025-05-19 12:21:10', 40.00, '2025-05-19 12:21:10', 'saed@gmail.com', 0.00, 40.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `quantity`, `price`) VALUES
(1, 1, 2, 2, 40.00),
(2, 2, 2, 4, 40.00),
(3, 3, 2, 1, 40.00),
(4, 4, 1, 1, 15.50),
(5, 4, 2, 1, 40.00),
(6, 5, 1, 10, 15.50),
(7, 5, 2, 10, 40.00),
(8, 6, 1, 2, 15.50),
(9, 6, 2, 2, 40.00),
(10, 7, 2, 1, 40.00),
(11, 8, 1, 2, 15.50),
(12, 8, 2, 2, 40.00),
(13, 9, 1, 5, 15.50),
(14, 9, 2, 3, 40.00),
(15, 10, 1, 5, 15.50),
(16, 10, 2, 3, 40.00),
(17, 11, 1, 1, 15.50),
(18, 12, 1, 1, 15.50),
(19, 12, 2, 1, 40.00),
(20, 13, 1, 5, 15.50),
(21, 13, 2, 6, 40.00),
(22, 14, 1, 2, 15.50),
(23, 15, 2, 1, 40.00),
(24, 16, 2, 1, 40.00),
(25, 17, 2, 1, 40.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('fixed','percentage') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `use_limit` int(11) DEFAULT NULL,
  `use_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `valid_from`, `valid_until`, `use_limit`, `use_count`) VALUES
(1, 'SAVE10', 'percentage', 10.00, 2.00, '2025-05-09 10:10:00', '2025-05-22 20:10:00', 1, 0),
(2, 'TEST10', 'fixed', 10.00, 1.00, '2025-05-08 20:00:00', '2025-05-09 09:00:00', 1, 0),
(3, 'F&S', 'fixed', 10.00, 1.00, '2025-05-07 10:10:00', '2025-05-08 22:10:00', 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kitchen','delivery') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `full_name`, `created_at`) VALUES
(1, 'admin', '$2y$10$PK6bMx90Lqzy058aRAOoVOD1RAwsPcFf5m3CMqZqhVQIEbVd3X/YW', 'admin', 'Saed Ahmed', '2025-04-25 05:51:52'),
(4, 'saagar', '$2y$10$hTx9Zh3PVEpcM/bDLQuvvuiO2J9nC2nzOhb6mBS7TQWQhmlvMGw2O', 'kitchen', 'saagar', '2025-04-25 06:38:09'),
(5, 'alii', '$2y$10$nSvt1eKrLDOU8F6qvhuvguryDOxswfXGDfMHng.1ZD7zWkuSD2gtq', 'delivery', 'ali', '2025-04-25 06:40:27'),
(6, 'Saed', '$2y$10$G5cAxQ3/T05gLDfbqIx9eurze6zjpjEOODvN9bBkA5QnE9ohbTgtC', 'admin', 'Saed', '2025-05-08 17:17:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD UNIQUE KEY `public_access_code` (`public_access_code`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
