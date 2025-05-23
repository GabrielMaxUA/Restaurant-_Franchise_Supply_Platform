-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 13, 2025 at 07:28 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `franchise_supply_platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_details`
--

CREATE TABLE `admin_details` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `logo_path` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_details`
--

INSERT INTO `admin_details` (`id`, `user_id`, `company_name`, `address`, `city`, `state`, `postal_code`, `phone`, `email`, `website`, `logo_path`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(2, 1, 'MaxiCo', '478 Mortimer Ave', 'Toronto', 'ON', 'M4J 2G5', '1234567890', 'admin@example.com', 'www.restaurantfranchisesupply.com', 'company-logos/lFCONwMuMZOZJ8Y8IqA7wtRa4oQ8bjhHGCWechXX.png', '1', 'admin', '2025-05-08 13:03:09', '2025-05-13 03:37:05');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 14, '2025-05-08 15:56:33', '2025-05-08 15:56:33'),
(2, 16, '2025-05-08 22:59:06', '2025-05-08 22:59:06');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `cart_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(6, 'poultry', 'farm meats', '2025-05-07 12:26:19', NULL),
(7, 'butchery', 'beef and pork', '2025-05-07 12:26:48', NULL),
(8, 'spices', NULL, '2025-05-07 12:26:54', NULL),
(9, 'dry goods', NULL, '2025-05-07 12:27:04', NULL),
(10, 'flour', NULL, '2025-05-10 14:43:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `franchisee_details`
--

CREATE TABLE `franchisee_details` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `franchisee_details`
--

INSERT INTO `franchisee_details` (`id`, `user_id`, `company_name`, `address`, `city`, `state`, `postal_code`, `contact_name`, `logo_path`, `created_at`, `updated_at`, `updated_by`) VALUES
(2, 14, 'Max and Company eno', '478 Mortimer Avenue', 'Toronto', 'ON', 'M4J 2G5', 'Max Gabriellla', 'franchisee_logos/company_logo_14_1747101083.png', '2025-05-08 15:14:15', '2025-05-13 02:36:26', 'admin'),
(3, 16, 'Max and Company', '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', 'Max Gabriel', 'franchisee_logos/company_logo_16_1747146327.png', '2025-05-08 15:28:59', '2025-05-13 14:26:40', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected','packed','shipped','delivered') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_address` varchar(255) NOT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(20) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_time` varchar(20) DEFAULT NULL,
  `delivery_preference` varchar(20) DEFAULT 'standard',
  `shipping_cost` decimal(8,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `manager_name` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `purchase_order` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `qb_invoice_id` varchar(100) DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `status`, `total_amount`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_zip`, `delivery_date`, `delivery_time`, `delivery_preference`, `shipping_cost`, `notes`, `manager_name`, `contact_phone`, `purchase_order`, `created_at`, `updated_at`, `approved_at`, `qb_invoice_id`, `invoice_number`) VALUES
(23, 14, 'delivered', 12.96, '478 Mortimer Avenuea', 'Toronto', 'ON', 'M4J 2G5', '2025-05-12', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-09 23:05:10', '2025-05-10 00:41:58', NULL, NULL, NULL),
(24, 14, 'rejected', 12.96, '478 Mortimer Avenuea', 'Toronto', 'ON', 'M4J 2G5', '2025-05-12', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-09 23:53:50', '2025-05-10 00:07:28', NULL, NULL, NULL),
(25, 14, 'rejected', 12.96, '478 Mortimer Avenuea', 'Toronto', 'ON', 'M4J 2G5', '2025-05-12', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-10 01:20:43', '2025-05-10 01:50:26', NULL, NULL, NULL),
(26, 14, 'delivered', 12.96, '478 Mortimer Avenuea', 'Toronto', 'ON', 'M4J 2G5', '2025-05-12', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-10 02:13:01', '2025-05-13 04:41:12', NULL, 'QB-INV-68689', NULL),
(27, 14, 'delivered', 64.80, '478 Mortimer Avenuea', 'Toronto', 'ON', 'M4J 2G5', '2025-05-12', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-10 02:19:51', '2025-05-13 00:11:07', NULL, 'QB-INV-57901', NULL),
(28, 14, 'delivered', 12.96, '478 Mortimer Avenuea', 'Toronto', 'ON', 'M4J 2G5', '2025-05-15', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-12 23:52:10', '2025-05-13 00:48:29', NULL, NULL, NULL),
(29, 14, 'delivered', 142.56, '478 Mortimer Avenuea', 'Toronto', 'ON', 'M4J 2G5', '2025-05-15', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-13 01:14:57', '2025-05-13 11:59:44', NULL, NULL, NULL),
(30, 14, 'delivered', 12.96, '478 Mortimer Avenuea', 'Toronto', 'ON', 'M4J 2G5', '2025-05-15', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-13 01:25:19', '2025-05-13 11:59:40', '2025-05-13 01:25:38', NULL, 'INV-30-202505'),
(31, 14, 'packed', 1328.40, '478 Mortimer Avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-05-15', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-13 02:11:48', '2025-05-13 11:59:34', '2025-05-13 02:42:31', NULL, 'INV-31-202505'),
(32, 14, 'packed', 84.12, '478 Mortimer Avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-05-15', 'morning', 'express', 15.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-13 02:24:06', '2025-05-13 11:59:08', '2025-05-13 02:42:16', NULL, 'INV-32-202505'),
(33, 14, 'packed', 38.88, '478 Mortimer Avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-05-15', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-13 02:27:27', '2025-05-13 11:59:07', '2025-05-13 02:42:06', NULL, 'INV-33-202505'),
(34, 16, 'packed', 196.44, '922 Greenwood', 'Toronto', 'ON', 'M4J 2G5', '2025-05-15', 'morning', 'express', 15.00, '', 'Default Manager', '4168560684', NULL, '2025-05-13 02:44:09', '2025-05-13 11:59:05', '2025-05-13 11:58:05', NULL, 'INV-34-202505'),
(35, 16, 'packed', 34.56, '922 Greenwood', 'Toronto', 'ON', 'M4J 2G5', '2025-05-15', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-13 03:08:55', '2025-05-13 11:59:04', '2025-05-13 11:57:57', NULL, 'INV-35-202505'),
(36, 16, 'packed', 12.96, '922 Greenwood', 'Toronto', 'ON', 'M4J 2G5', '2025-05-15', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-13 03:11:52', '2025-05-13 11:59:02', '2025-05-13 11:57:48', NULL, 'INV-36-202505'),
(37, 16, 'packed', 16007.64, '922 Greenwood', 'Toronto', 'ON', 'M4J 2G5', '2025-05-16', 'morning', 'express', 15.00, 'rear loading dock door. please call prior arrival', 'Default Manager', '4168560684', NULL, '2025-05-13 11:23:59', '2025-05-13 11:59:01', '2025-05-13 11:57:41', NULL, 'INV-37-202505'),
(38, 14, 'packed', 16352.28, '478 Mortimer Avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-05-16', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-13 11:56:46', '2025-05-13 11:58:59', '2025-05-13 11:57:13', NULL, 'INV-38-202505');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(26, 23, 68, NULL, 1, 12.00, '2025-05-09 23:05:10', '2025-05-09 23:05:10'),
(27, 24, 68, NULL, 1, 12.00, '2025-05-09 23:53:50', '2025-05-09 23:53:50'),
(28, 25, 68, NULL, 1, 12.00, '2025-05-10 01:20:43', '2025-05-10 01:20:43'),
(29, 26, 68, NULL, 1, 12.00, '2025-05-10 02:13:01', '2025-05-10 02:13:01'),
(30, 27, 68, NULL, 5, 12.00, '2025-05-10 02:19:51', '2025-05-10 02:19:51'),
(31, 28, 68, NULL, 1, 12.00, '2025-05-12 23:52:10', '2025-05-12 23:52:10'),
(32, 29, 69, NULL, 11, 12.00, '2025-05-13 01:14:57', '2025-05-13 01:14:57'),
(33, 30, 69, NULL, 1, 12.00, '2025-05-13 01:25:19', '2025-05-13 01:25:19'),
(34, 31, 65, NULL, 123, 10.00, '2025-05-13 02:11:48', '2025-05-13 02:11:48'),
(35, 32, 67, NULL, 2, 32.00, '2025-05-13 02:24:06', '2025-05-13 02:24:06'),
(36, 33, 68, NULL, 3, 12.00, '2025-05-13 02:27:27', '2025-05-13 02:27:27'),
(37, 34, 68, 21, 12, 14.00, '2025-05-13 02:44:09', '2025-05-13 02:44:09'),
(38, 35, 67, NULL, 1, 32.00, '2025-05-13 03:08:55', '2025-05-13 03:08:55'),
(39, 36, 68, NULL, 1, 12.00, '2025-05-13 03:11:52', '2025-05-13 03:11:52'),
(40, 37, 70, 22, 12, 1234.00, '2025-05-13 11:23:59', '2025-05-13 11:23:59'),
(41, 38, 70, NULL, 123, 123.00, '2025-05-13 11:56:46', '2025-05-13 11:56:46'),
(42, 38, 69, NULL, 1, 12.00, '2025-05-13 11:56:46', '2025-05-13 11:56:46');

-- --------------------------------------------------------

--
-- Table structure for table `order_notifications`
--

CREATE TABLE `order_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_notifications`
--

INSERT INTO `order_notifications` (`id`, `user_id`, `order_id`, `status`, `is_read`, `created_at`, `updated_at`) VALUES
(7, 4, 28, 'pending', 1, '2025-05-12 23:52:10', '2025-05-12 23:53:46'),
(21, 4, 28, 'approved', 1, '2025-05-13 00:23:48', '2025-05-13 00:24:48'),
(23, 1, 28, 'packed', 1, '2025-05-13 00:24:51', '2025-05-13 00:30:17'),
(24, 14, 26, 'shipped', 1, '2025-05-13 00:32:20', '2025-05-13 00:32:31'),
(25, 1, 26, 'shipped', 1, '2025-05-13 00:32:20', '2025-05-13 00:34:05'),
(26, 14, 28, 'shipped', 1, '2025-05-13 00:34:50', '2025-05-13 01:14:22'),
(27, 1, 28, 'shipped', 1, '2025-05-13 00:34:50', '2025-05-13 00:46:09'),
(28, 14, 28, 'delivered', 1, '2025-05-13 00:48:29', '2025-05-13 01:14:22'),
(29, 1, 28, 'delivered', 1, '2025-05-13 00:48:29', '2025-05-13 00:58:44'),
(30, 1, 29, 'pending', 1, '2025-05-13 01:14:57', '2025-05-13 01:18:25'),
(31, 4, 29, 'pending', 1, '2025-05-13 01:14:57', '2025-05-13 01:28:30'),
(32, 14, 29, 'approved', 1, '2025-05-13 01:18:19', '2025-05-13 01:24:35'),
(33, 4, 29, 'approved', 1, '2025-05-13 01:18:19', '2025-05-13 01:28:30'),
(34, 1, 30, 'pending', 1, '2025-05-13 01:25:19', '2025-05-13 01:25:42'),
(35, 4, 30, 'pending', 1, '2025-05-13 01:25:19', '2025-05-13 01:28:30'),
(36, 14, 30, 'approved', 1, '2025-05-13 01:25:38', '2025-05-13 01:25:54'),
(37, 4, 30, 'approved', 1, '2025-05-13 01:25:38', '2025-05-13 01:28:30'),
(38, 14, 30, 'packed', 1, '2025-05-13 01:30:36', '2025-05-13 01:31:37'),
(39, 1, 30, 'packed', 1, '2025-05-13 01:30:36', '2025-05-13 01:31:20'),
(40, 14, 29, 'packed', 1, '2025-05-13 01:47:26', '2025-05-13 01:49:17'),
(41, 1, 29, 'packed', 1, '2025-05-13 01:47:26', '2025-05-13 02:43:21'),
(42, 1, 31, 'pending', 1, '2025-05-13 02:11:48', '2025-05-13 02:43:21'),
(43, 4, 31, 'pending', 1, '2025-05-13 02:11:48', '2025-05-13 03:39:07'),
(44, 1, 32, 'pending', 1, '2025-05-13 02:24:06', '2025-05-13 02:43:21'),
(45, 4, 32, 'pending', 1, '2025-05-13 02:24:06', '2025-05-13 03:39:07'),
(46, 1, 33, 'pending', 1, '2025-05-13 02:27:27', '2025-05-13 02:43:21'),
(47, 4, 33, 'pending', 1, '2025-05-13 02:27:27', '2025-05-13 03:39:07'),
(48, 14, 33, 'approved', 1, '2025-05-13 02:42:06', '2025-05-13 11:56:36'),
(49, 4, 33, 'approved', 1, '2025-05-13 02:42:06', '2025-05-13 03:39:07'),
(50, 14, 32, 'approved', 1, '2025-05-13 02:42:16', '2025-05-13 11:56:36'),
(51, 4, 32, 'approved', 1, '2025-05-13 02:42:16', '2025-05-13 03:39:07'),
(52, 14, 31, 'approved', 1, '2025-05-13 02:42:31', '2025-05-13 11:56:36'),
(53, 4, 31, 'approved', 1, '2025-05-13 02:42:31', '2025-05-13 03:39:07'),
(54, 1, 34, 'pending', 1, '2025-05-13 02:44:09', '2025-05-13 11:58:10'),
(55, 4, 34, 'pending', 1, '2025-05-13 02:44:09', '2025-05-13 03:39:07'),
(56, 16, 34, 'Order placed', 1, '2025-05-13 02:44:09', '2025-05-13 02:44:15'),
(57, 1, 34, 'New order requires attention', 1, '2025-05-13 02:44:09', '2025-05-13 11:58:10'),
(58, 4, 34, 'New order needs processing', 1, '2025-05-13 02:44:09', '2025-05-13 03:39:07'),
(59, 1, 35, 'pending', 1, '2025-05-13 03:08:55', '2025-05-13 11:58:10'),
(60, 4, 35, 'pending', 1, '2025-05-13 03:08:55', '2025-05-13 03:39:07'),
(61, 16, 35, 'Order placed', 1, '2025-05-13 03:08:59', '2025-05-13 03:11:24'),
(62, 1, 35, 'New order requires attention', 1, '2025-05-13 03:08:59', '2025-05-13 11:58:10'),
(63, 4, 35, 'New order needs processing', 1, '2025-05-13 03:08:59', '2025-05-13 03:39:07'),
(64, 1, 36, 'pending', 1, '2025-05-13 03:11:52', '2025-05-13 11:58:10'),
(65, 4, 36, 'pending', 1, '2025-05-13 03:11:52', '2025-05-13 03:39:07'),
(66, 16, 36, 'Order placed', 1, '2025-05-13 03:11:55', '2025-05-13 11:21:42'),
(67, 1, 36, 'New order requires attention', 1, '2025-05-13 03:11:55', '2025-05-13 11:58:10'),
(68, 4, 36, 'New order needs processing', 1, '2025-05-13 03:11:55', '2025-05-13 03:39:07'),
(69, 14, 30, 'shipped', 1, '2025-05-13 03:40:01', '2025-05-13 11:56:36'),
(70, 1, 30, 'shipped', 1, '2025-05-13 03:40:01', '2025-05-13 11:58:10'),
(71, 14, 29, 'shipped', 1, '2025-05-13 03:40:09', '2025-05-13 11:56:36'),
(72, 1, 29, 'shipped', 1, '2025-05-13 03:40:09', '2025-05-13 11:58:10'),
(73, 1, 37, 'pending', 1, '2025-05-13 11:23:59', '2025-05-13 11:58:10'),
(74, 4, 37, 'pending', 0, '2025-05-13 11:23:59', '2025-05-13 11:23:59'),
(75, 16, 37, 'Order placed', 1, '2025-05-13 11:23:59', '2025-05-13 11:24:05'),
(76, 1, 37, 'New order requires attention', 1, '2025-05-13 11:23:59', '2025-05-13 11:58:10'),
(77, 4, 37, 'New order needs processing', 0, '2025-05-13 11:23:59', '2025-05-13 11:23:59'),
(78, 1, 38, 'pending', 1, '2025-05-13 11:56:46', '2025-05-13 11:58:10'),
(79, 4, 38, 'pending', 0, '2025-05-13 11:56:46', '2025-05-13 11:56:46'),
(80, 14, 38, 'Order placed', 1, '2025-05-13 11:56:46', '2025-05-13 11:56:50'),
(81, 1, 38, 'New order requires attention', 1, '2025-05-13 11:56:46', '2025-05-13 11:58:10'),
(82, 4, 38, 'New order needs processing', 0, '2025-05-13 11:56:46', '2025-05-13 11:56:46'),
(83, 14, 38, 'approved', 0, '2025-05-13 11:57:13', '2025-05-13 11:57:13'),
(84, 4, 38, 'approved', 0, '2025-05-13 11:57:13', '2025-05-13 11:57:13'),
(85, 16, 37, 'approved', 0, '2025-05-13 11:57:41', '2025-05-13 11:57:41'),
(86, 4, 37, 'approved', 0, '2025-05-13 11:57:41', '2025-05-13 11:57:41'),
(87, 16, 36, 'approved', 0, '2025-05-13 11:57:48', '2025-05-13 11:57:48'),
(88, 4, 36, 'approved', 0, '2025-05-13 11:57:48', '2025-05-13 11:57:48'),
(89, 16, 35, 'approved', 0, '2025-05-13 11:57:57', '2025-05-13 11:57:57'),
(90, 4, 35, 'approved', 0, '2025-05-13 11:57:57', '2025-05-13 11:57:57'),
(91, 16, 34, 'approved', 0, '2025-05-13 11:58:05', '2025-05-13 11:58:05'),
(92, 4, 34, 'approved', 0, '2025-05-13 11:58:05', '2025-05-13 11:58:05'),
(93, 14, 38, 'packed', 0, '2025-05-13 11:58:59', '2025-05-13 11:58:59'),
(94, 1, 38, 'packed', 1, '2025-05-13 11:58:59', '2025-05-13 14:26:12'),
(95, 16, 37, 'packed', 0, '2025-05-13 11:59:01', '2025-05-13 11:59:01'),
(96, 1, 37, 'packed', 1, '2025-05-13 11:59:01', '2025-05-13 14:26:12'),
(97, 16, 36, 'packed', 0, '2025-05-13 11:59:02', '2025-05-13 11:59:02'),
(98, 1, 36, 'packed', 1, '2025-05-13 11:59:02', '2025-05-13 14:26:12'),
(99, 16, 35, 'packed', 0, '2025-05-13 11:59:04', '2025-05-13 11:59:04'),
(100, 1, 35, 'packed', 1, '2025-05-13 11:59:04', '2025-05-13 14:26:12'),
(101, 16, 34, 'packed', 0, '2025-05-13 11:59:05', '2025-05-13 11:59:05'),
(102, 1, 34, 'packed', 1, '2025-05-13 11:59:05', '2025-05-13 14:26:12'),
(103, 14, 33, 'packed', 0, '2025-05-13 11:59:07', '2025-05-13 11:59:07'),
(104, 1, 33, 'packed', 1, '2025-05-13 11:59:07', '2025-05-13 14:26:12'),
(105, 14, 32, 'packed', 0, '2025-05-13 11:59:08', '2025-05-13 11:59:08'),
(106, 1, 32, 'packed', 1, '2025-05-13 11:59:08', '2025-05-13 14:26:12'),
(107, 14, 31, 'packed', 0, '2025-05-13 11:59:34', '2025-05-13 11:59:34'),
(108, 1, 31, 'packed', 1, '2025-05-13 11:59:34', '2025-05-13 14:26:12'),
(109, 14, 30, 'delivered', 0, '2025-05-13 11:59:40', '2025-05-13 11:59:40'),
(110, 1, 30, 'delivered', 1, '2025-05-13 11:59:40', '2025-05-13 14:26:12'),
(111, 14, 29, 'delivered', 0, '2025-05-13 11:59:44', '2025-05-13 11:59:44'),
(112, 1, 29, 'delivered', 1, '2025-05-13 11:59:44', '2025-05-13 14:26:12');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 3, 'auth-token', '62322e9b73798c39d21c8e208aabd4b09f518ca1d45c3e7fe0c09b4a9072bd7c', '[\"*\"]', NULL, NULL, '2025-05-03 18:07:51', '2025-05-03 18:07:51'),
(2, 'App\\Models\\User', 3, 'auth-token', '6341b57a33160574cf4fc055758302909ee4fbb1b16557678841823f099ec298', '[\"*\"]', NULL, NULL, '2025-05-03 18:09:01', '2025-05-03 18:09:01'),
(3, 'App\\Models\\User', 3, 'auth-token', '787d4926171eebe0325d16968be8b363aebb20f5197e91f9f50f1045e91d4a96', '[\"*\"]', NULL, NULL, '2025-05-03 18:09:11', '2025-05-03 18:09:11'),
(4, 'App\\Models\\User', 3, 'auth-token', '7c3872f6ed98836fc7e71c0fbe1bf05f6300ea0c01f5260d9a64aecb1d9ca2c8', '[\"*\"]', NULL, NULL, '2025-05-03 18:10:51', '2025-05-03 18:10:51'),
(5, 'App\\Models\\User', 1, 'auth-token', '02580686fcda32bced067d8de1c7ea8271c51da3546dbb582434113a0ec47f86', '[\"*\"]', NULL, NULL, '2025-05-03 18:12:09', '2025-05-03 18:12:09');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `inventory_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `base_price`, `category_id`, `inventory_count`, `created_at`, `updated_at`) VALUES
(65, 'checken', 'organic, free run', 10.00, 6, 125, '2025-05-07 16:28:21', '2025-05-13 02:11:48'),
(66, 'ground beef', 'canadian farm direct supplier (pack of 1kg)', 14.00, 7, 42, '2025-05-07 16:30:25', '2025-05-11 13:43:46'),
(67, 'whole pepper mix', 'mix of whole pepper (white, red, black, green) pack of 400gr', 32.00, 8, 117, '2025-05-07 16:32:13', '2025-05-13 03:08:55'),
(68, 'chicken wings', 'Bulk (Min order 1kg)', 12.00, 6, 47, '2025-05-08 23:45:21', '2025-05-13 03:11:52'),
(69, 'wheet flour', '3 kg pck', 12.00, 10, 122, '2025-05-10 14:45:56', '2025-05-13 11:56:46'),
(70, 'piano', NULL, 123.00, 7, 123, '2025-05-13 03:56:42', '2025-05-13 12:00:21');

-- --------------------------------------------------------

--
-- Table structure for table `product_favorites`
--

CREATE TABLE `product_favorites` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_favorites`
--

INSERT INTO `product_favorites` (`id`, `user_id`, `product_id`) VALUES
(11, 14, 67),
(12, 16, 70);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`) VALUES
(63, 65, 'product-images/681b51e530662_1746620901.png'),
(64, 66, 'product-images/681b526136824_1746621025.png'),
(65, 67, 'product-images/681b52cd1eb0f_1746621133.png'),
(67, 66, 'product-images/OYygNJL8g41h97MWY5aquBgdHehGliijoVhtVghq.svg'),
(68, 65, 'product-images/DxO7pnvhujNyJtOj5SUsm0AyXMe5mPwQVV75xr9m.svg'),
(69, 67, 'product-images/681cd7117aef4_1746720529.JPG'),
(70, 68, 'product-images/681d4211c2ec1_1746747921.png'),
(72, 69, 'product-images/6822c18f8cd46_1747108239.png'),
(74, 69, 'product-images/6822c3458b5c0_1747108677.png'),
(75, 69, 'product-images/6822c3479fd29_1747108679.png'),
(78, 70, 'product-images/6822c597c4890_1747109271.png'),
(97, 70, 'product-images/682334559a69b_1747137621.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `price_adjustment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `inventory_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `name`, `price_adjustment`, `inventory_count`, `created_at`, `updated_at`) VALUES
(17, 65, 'pack of 10', 90.00, 10, '2025-05-07 16:28:21', '2025-05-08 16:08:07'),
(18, 66, '2kg', 22.00, 92, '2025-05-07 16:30:25', '2025-05-11 13:43:54'),
(19, 66, '5kg', 45.00, 3, '2025-05-07 16:30:25', '2025-05-10 16:38:20'),
(20, 67, 'pack of 800gr', 89.00, 20, '2025-05-07 16:32:13', '2025-05-09 02:26:57'),
(21, 68, 'chicken wings (packed/vacuum). 3kg/pkg', 14.00, 323, '2025-05-08 23:45:21', '2025-05-13 03:50:04'),
(22, 70, 'box of 10', 1234.00, 123, '2025-05-13 03:56:42', '2025-05-13 12:00:21'),
(23, 69, 'well', 123.00, 123, '2025-05-13 03:57:56', '2025-05-13 03:57:56');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `permissions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `permissions`) VALUES
(1, 'admin', 'all'),
(2, 'warehouse', 'view_orders,update_orders,view_products'),
(3, 'franchisee', 'place_orders,view_products');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('FH5QyuM1cCEXOqQ5Hs0el9HjdvjXjpxARM77gqQa', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiS002MjlUdXg4RDhYaG5qcVliYjBnTEkxMjFlb1FwaVo5ME95bEhOUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ub3RpZmljYXRpb25zL3VucmVhZC1jb3VudCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxMjoid2VsY29tZV9iYWNrIjtiOjE7czo5OiJ1c2VyX25hbWUiO3M6NToiYWRtaW4iO3M6MTc6Imhhc19vcmRlcl91cGRhdGVzIjtiOjE7czoxMjoiaGlkZV93ZWxjb21lIjtiOjA7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyODoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2xvZ291dCI7fX0=', 1747150770);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_by` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = active, 0 = blocked'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `phone`, `role_id`, `created_at`, `updated_at`, `updated_by`, `status`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', '1234567890', 1, '2025-05-02 22:22:44', '2025-05-13 03:37:05', 'admin', 1),
(4, 'maximUSCan', '$2y$12$hd15.smpVHhhq0.yRNu3YeW9kSOBVomoDVJ5/VmzeGBkrJJvu3gyi', 'maxim.don.mg@gmail.com', '4168560684', 2, '2025-05-04 23:09:20', '2025-05-11 14:34:24', 'adminMax', 1),
(14, 'user1', '$2y$12$wEfP8y4N0eRt2X/deuIxpuUQQr4fI.SP5kM0U4hjzetQ.JF8jLzcy', 'user@franche.com', '416 8560684', 3, '2025-05-07 18:21:40', '2025-05-13 02:36:26', 'admin', 1),
(16, 'gabriel max', '$2y$12$.mz8V4c8I.XXcnnGqgCUZOQyU94S/fYmeRip9PiC3AbEb2wVNyuH.', 'maxim.don.mg@gmail.com1', '4168560684', 3, '2025-05-08 15:28:59', '2025-05-13 14:38:33', 'admin', 1);

-- --------------------------------------------------------

--
-- Table structure for table `variant_images`
--

CREATE TABLE `variant_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `variant_id` int(10) UNSIGNED NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `variant_images`
--

INSERT INTO `variant_images` (`id`, `variant_id`, `image_url`) VALUES
(41, 17, 'variant-images/681b51e52ff4a_1746620901.png'),
(42, 18, 'variant-images/681b5261359ed_1746621025.png'),
(43, 20, 'variant-images/681b52cd193e7_1746621133.png'),
(46, 18, 'variant-images/681be13bdc36f_1746657595.jpg'),
(47, 19, 'variant-images/681be13be5223_1746657595.png'),
(48, 17, 'variant-images/681be1572337d_1746657623.png'),
(55, 21, 'variant-images/681d4211bea9b_1746747921.png'),
(57, 23, 'variant-images/6822c34486427_1747108676.png'),
(61, 22, 'variant-images/vnEVCjoo4DV5taZ0jf61iw3JR0Y7Ga0jwjw8dsEN.svg'),
(62, 22, 'variant-images/6822c59796464_1747109271.png'),
(63, 22, 'variant-images/6822c597a12ba_1747109271.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_details`
--
ALTER TABLE `admin_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_details_user_id_unique` (`user_id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `franchisee_details`
--
ALTER TABLE `franchisee_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `franchisee_details_user_id_unique` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_user` (`user_id`),
  ADD KEY `idx_order_status` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_order` (`order_id`),
  ADD KEY `idx_item_product` (`product_id`),
  ADD KEY `idx_item_variant` (`variant_id`);

--
-- Indexes for table `order_notifications`
--
ALTER TABLE `order_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_notifications_user_id_is_read_index` (`user_id`,`is_read`),
  ADD KEY `order_notifications_order_id_index` (`order_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_category` (`category_id`);

--
-- Indexes for table `product_favorites`
--
ALTER TABLE `product_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_image_product` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_variant_product` (`product_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_role` (`role_id`);

--
-- Indexes for table `variant_images`
--
ALTER TABLE `variant_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variant_images_variant_id_foreign` (`variant_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_details`
--
ALTER TABLE `admin_details`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `franchisee_details`
--
ALTER TABLE `franchisee_details`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `order_notifications`
--
ALTER TABLE `order_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `product_favorites`
--
ALTER TABLE `product_favorites`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `variant_images`
--
ALTER TABLE `variant_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `franchisee_details`
--
ALTER TABLE `franchisee_details`
  ADD CONSTRAINT `franchisee_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_notifications`
--
ALTER TABLE `order_notifications`
  ADD CONSTRAINT `order_notifications_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_favorites`
--
ALTER TABLE `product_favorites`
  ADD CONSTRAINT `product_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `variant_images`
--
ALTER TABLE `variant_images`
  ADD CONSTRAINT `variant_images_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
