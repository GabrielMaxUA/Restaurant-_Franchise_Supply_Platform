-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 30, 2025 at 08:26 PM
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
(2, 1, 'MaxiCo', '478 Mortimer Ave', 'Toronto', 'ON', 'M4J 2G5', '1234567890', 'admian@example.com', 'www.restaurantfranchisesupply.com', 'company-logos/lFCONwMuMZOZJ8Y8IqA7wtRa4oQ8bjhHGCWechXX.png', '1', 'admin', '2025-05-08 13:03:09', '2025-05-31 00:12:00');

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
(7, 'butchery', 'beef and pork', '2025-05-07 12:26:48', NULL),
(8, 'spices', NULL, '2025-05-07 12:26:54', NULL),
(9, 'dry goods', NULL, '2025-05-07 12:27:04', NULL),
(10, 'flour', NULL, '2025-05-10 14:43:56', NULL),
(34, 'poultry', NULL, '2025-05-27 00:26:10', NULL);

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
(3, 16, 'Max and Company', '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', 'Max Gabriel', 'franchisee_logos/company_logo_16_1748048084.jpg', '2025-05-08 15:28:59', '2025-05-31 00:20:32', 'admin');

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
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
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

INSERT INTO `orders` (`id`, `user_id`, `status`, `shipped_at`, `delivered_at`, `total_amount`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_zip`, `delivery_date`, `delivery_time`, `delivery_preference`, `shipping_cost`, `notes`, `manager_name`, `contact_phone`, `purchase_order`, `created_at`, `updated_at`, `approved_at`, `qb_invoice_id`, `invoice_number`) VALUES
(65, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-05-30', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-28 02:33:37', '2025-05-28 02:34:15', '2025-05-28 02:34:15', NULL, 'INV-65-202505'),
(66, 16, 'packed', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-05-31', 'morning', 'standard', 0.00, 'leave at the door and call supervisor', 'Default Manager', '4168560684', NULL, '2025-05-28 12:30:00', '2025-05-28 12:37:31', '2025-05-28 12:30:45', NULL, 'INV-66-202505'),
(67, 16, 'approved', NULL, NULL, 147.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-05-31', 'morning', 'express', 15.00, '', 'Default Manager', '4168560684', NULL, '2025-05-28 12:40:43', '2025-05-28 12:41:01', '2025-05-28 12:41:01', NULL, 'INV-67-202505'),
(68, 16, 'approved', NULL, NULL, 147.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-05-31', 'morning', 'express', 15.00, '', 'Default Manager', '4168560684', NULL, '2025-05-28 12:48:09', '2025-05-28 12:48:30', '2025-05-28 12:48:30', NULL, 'INV-68-202505'),
(69, 16, 'delivered', NULL, '2025-05-29 01:48:12', 147.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-05-31', 'morning', 'express', 15.00, '', 'Default Manager', '4168560684', NULL, '2025-05-28 13:07:36', '2025-05-29 01:48:12', '2025-05-28 13:08:31', NULL, 'INV-69-202505'),
(70, 16, 'packed', NULL, NULL, 13299.00, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-05-31', 'morning', 'express', 15.00, '', 'Default Manager', '4168560684', NULL, '2025-05-28 13:53:43', '2025-05-29 00:49:20', '2025-05-28 13:55:23', NULL, 'INV-70-202505'),
(71, 16, 'approved', NULL, NULL, 27.96, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-05-31', 'morning', 'express', 15.00, '', 'Default Manager', '4168560684', NULL, '2025-05-29 00:54:03', '2025-05-29 00:57:29', '2025-05-29 00:57:29', NULL, 'INV-71-202505'),
(72, 16, 'packed', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-29 22:35:19', '2025-05-29 23:17:28', '2025-05-29 23:13:21', NULL, 'INV-72-202505'),
(73, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-29 23:53:58', '2025-05-29 23:54:33', '2025-05-29 23:54:33', NULL, 'INV-73-202505'),
(74, 16, 'approved', NULL, NULL, 147.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'express', 15.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 00:07:33', '2025-05-30 01:27:01', '2025-05-30 01:27:01', NULL, 'INV-74-202505'),
(75, 16, 'approved', NULL, NULL, 147.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'express', 15.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 00:12:51', '2025-05-30 01:24:51', '2025-05-30 01:24:51', NULL, 'INV-75-202505'),
(76, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 00:17:25', '2025-05-30 01:23:27', '2025-05-30 01:23:27', NULL, 'INV-76-202505'),
(77, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 00:22:50', '2025-05-30 01:19:25', '2025-05-30 01:19:25', NULL, 'INV-77-202505'),
(78, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 00:24:27', '2025-05-30 00:57:20', '2025-05-30 00:57:20', NULL, 'INV-78-202505'),
(79, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 00:24:34', '2025-05-30 00:29:38', '2025-05-30 00:29:38', NULL, 'INV-79-202505'),
(80, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 00:32:09', '2025-05-30 01:15:38', '2025-05-30 01:15:38', NULL, 'INV-80-202505'),
(81, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 00:37:36', '2025-05-30 00:40:57', '2025-05-30 00:40:57', NULL, 'INV-81-202505'),
(82, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 00:52:58', '2025-05-30 01:11:14', '2025-05-30 01:11:14', NULL, 'INV-82-202505'),
(83, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 00:59:06', '2025-05-30 01:05:13', '2025-05-30 01:05:13', NULL, 'INV-83-202505'),
(84, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 01:28:33', '2025-05-30 01:30:48', '2025-05-30 01:30:48', NULL, 'INV-84-202505'),
(85, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 01:36:28', '2025-05-30 01:46:26', '2025-05-30 01:46:26', NULL, 'INV-85-202505'),
(86, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 01:36:40', '2025-05-30 01:37:18', '2025-05-30 01:37:18', NULL, 'INV-86-202505'),
(87, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 01:46:14', '2025-05-30 01:58:19', '2025-05-30 01:58:19', NULL, 'INV-87-202505'),
(88, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 01:59:18', '2025-05-30 02:00:01', '2025-05-30 02:00:01', NULL, 'INV-88-202505'),
(89, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 02:04:17', '2025-05-30 02:04:49', '2025-05-30 02:04:49', NULL, 'INV-89-202505'),
(90, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 02:12:26', '2025-05-30 02:12:59', '2025-05-30 02:12:59', NULL, 'INV-90-202505'),
(91, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 02:12:36', '2025-05-30 02:16:30', '2025-05-30 02:16:30', NULL, 'INV-91-202505'),
(92, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 02:21:14', '2025-05-30 02:22:28', '2025-05-30 02:22:28', NULL, 'INV-92-202505'),
(93, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 02:21:27', '2025-05-30 02:26:34', '2025-05-30 02:26:34', NULL, 'INV-93-202505'),
(94, 16, 'rejected', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 02:33:12', '2025-05-30 02:35:33', NULL, NULL, NULL),
(95, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 02:33:24', '2025-05-30 02:33:54', '2025-05-30 02:33:54', NULL, 'INV-95-202505'),
(96, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 02:36:11', '2025-05-30 02:41:49', '2025-05-30 02:41:49', NULL, 'INV-96-202505'),
(97, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 02:38:52', '2025-05-30 02:39:15', '2025-05-30 02:39:15', NULL, 'INV-97-202505'),
(98, 16, 'shipped', '2025-05-30 03:13:22', NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 02:43:40', '2025-05-30 03:13:22', '2025-05-30 02:44:50', NULL, 'INV-98-202505'),
(99, 16, 'packed', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 02:45:56', '2025-05-30 03:23:55', '2025-05-30 02:46:12', NULL, 'INV-99-202505'),
(100, 16, 'delivered', '2025-05-30 03:07:30', '2025-05-30 03:09:22', 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 02:46:58', '2025-05-30 03:09:22', '2025-05-30 02:50:18', NULL, 'INV-100-202505'),
(101, 16, 'delivered', '2025-05-30 03:00:25', '2025-05-30 03:00:58', 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 02:55:28', '2025-05-30 03:00:58', '2025-05-30 02:56:15', NULL, 'INV-101-202505'),
(102, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 03:25:33', '2025-05-30 03:26:05', '2025-05-30 03:26:05', NULL, 'INV-102-202505'),
(103, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-01', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 03:49:31', '2025-05-30 03:49:55', '2025-05-30 03:49:55', NULL, 'INV-103-202505'),
(104, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-02', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 12:56:42', '2025-05-30 13:03:20', '2025-05-30 13:03:20', NULL, 'INV-104-202505'),
(105, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-02', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 13:09:45', '2025-05-30 13:13:00', '2025-05-30 13:13:00', NULL, 'INV-105-202505'),
(106, 16, 'packed', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-02', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 13:14:52', '2025-05-30 13:20:54', '2025-05-30 13:15:44', NULL, 'INV-106-202505'),
(107, 16, 'packed', NULL, NULL, 147.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-02', 'morning', 'express', 15.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 13:28:20', '2025-05-30 14:01:11', '2025-05-30 13:32:15', NULL, 'INV-107-202505'),
(108, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-02', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 14:06:25', '2025-05-30 21:25:54', '2025-05-30 21:25:54', NULL, 'INV-108-202505'),
(109, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-02', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 14:17:00', '2025-05-30 14:45:23', '2025-05-30 14:45:23', NULL, 'INV-109-202505'),
(110, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-02', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 14:54:47', '2025-05-30 21:22:21', '2025-05-30 21:22:21', NULL, 'INV-110-202505'),
(111, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-02', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 15:17:10', '2025-05-30 21:17:26', '2025-05-30 21:17:26', NULL, 'INV-111-202505'),
(112, 16, 'rejected', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-02', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 21:28:11', '2025-05-30 21:48:14', NULL, NULL, NULL),
(113, 16, 'packed', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-02', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 21:28:22', '2025-05-30 21:47:56', '2025-05-30 21:30:31', NULL, 'INV-113-202505'),
(114, 16, 'pending', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-02', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 22:02:05', '2025-05-30 22:02:05', NULL, NULL, NULL),
(115, 16, 'approved', NULL, NULL, 132.84, '922 Greenwood avenue', 'Toronto', 'ON', 'M4J 2G5', '2025-06-02', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-30 22:09:11', '2025-05-30 22:10:39', '2025-05-30 22:10:39', NULL, 'INV-115-202505');

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
(76, 65, 70, NULL, 1, 123.00, '2025-05-28 02:33:37', '2025-05-28 02:33:37'),
(77, 66, 70, NULL, 1, 123.00, '2025-05-28 12:30:00', '2025-05-28 12:30:00'),
(78, 67, 70, NULL, 1, 123.00, '2025-05-28 12:40:43', '2025-05-28 12:40:43'),
(79, 68, 70, NULL, 1, 123.00, '2025-05-28 12:48:09', '2025-05-28 12:48:09'),
(80, 69, 70, NULL, 1, 123.00, '2025-05-28 13:07:36', '2025-05-28 13:07:36'),
(81, 70, 70, NULL, 100, 123.00, '2025-05-28 13:53:43', '2025-05-28 13:53:43'),
(82, 71, 69, NULL, 1, 12.00, '2025-05-29 00:54:03', '2025-05-29 00:54:03'),
(83, 72, 70, NULL, 1, 123.00, '2025-05-29 22:35:20', '2025-05-29 22:35:20'),
(84, 73, 70, NULL, 1, 123.00, '2025-05-29 23:53:58', '2025-05-29 23:53:58'),
(85, 74, 70, NULL, 1, 123.00, '2025-05-30 00:07:33', '2025-05-30 00:07:33'),
(86, 75, 70, NULL, 1, 123.00, '2025-05-30 00:12:51', '2025-05-30 00:12:51'),
(87, 76, 70, NULL, 1, 123.00, '2025-05-30 00:17:25', '2025-05-30 00:17:25'),
(88, 77, 70, NULL, 1, 123.00, '2025-05-30 00:22:52', '2025-05-30 00:22:52'),
(89, 78, 70, NULL, 1, 123.00, '2025-05-30 00:24:28', '2025-05-30 00:24:28'),
(90, 79, 70, NULL, 1, 123.00, '2025-05-30 00:24:35', '2025-05-30 00:24:35'),
(91, 80, 70, NULL, 1, 123.00, '2025-05-30 00:32:10', '2025-05-30 00:32:10'),
(92, 81, 70, NULL, 1, 123.00, '2025-05-30 00:37:37', '2025-05-30 00:37:37'),
(93, 82, 70, NULL, 1, 123.00, '2025-05-30 00:52:59', '2025-05-30 00:52:59'),
(94, 83, 70, NULL, 1, 123.00, '2025-05-30 00:59:07', '2025-05-30 00:59:07'),
(95, 84, 70, NULL, 1, 123.00, '2025-05-30 01:28:34', '2025-05-30 01:28:34'),
(96, 85, 70, NULL, 1, 123.00, '2025-05-30 01:36:30', '2025-05-30 01:36:30'),
(97, 86, 70, NULL, 1, 123.00, '2025-05-30 01:36:41', '2025-05-30 01:36:41'),
(98, 87, 70, NULL, 1, 123.00, '2025-05-30 01:46:16', '2025-05-30 01:46:16'),
(99, 88, 70, NULL, 1, 123.00, '2025-05-30 01:59:19', '2025-05-30 01:59:19'),
(100, 89, 70, NULL, 1, 123.00, '2025-05-30 02:04:18', '2025-05-30 02:04:18'),
(101, 90, 70, NULL, 1, 123.00, '2025-05-30 02:12:28', '2025-05-30 02:12:28'),
(102, 91, 70, NULL, 1, 123.00, '2025-05-30 02:12:37', '2025-05-30 02:12:37'),
(103, 92, 70, NULL, 1, 123.00, '2025-05-30 02:21:15', '2025-05-30 02:21:15'),
(104, 93, 70, NULL, 1, 123.00, '2025-05-30 02:21:28', '2025-05-30 02:21:28'),
(105, 94, 70, NULL, 1, 123.00, '2025-05-30 02:33:13', '2025-05-30 02:33:13'),
(106, 95, 70, NULL, 1, 123.00, '2025-05-30 02:33:25', '2025-05-30 02:33:25'),
(107, 96, 70, NULL, 1, 123.00, '2025-05-30 02:36:12', '2025-05-30 02:36:12'),
(108, 97, 70, NULL, 1, 123.00, '2025-05-30 02:38:53', '2025-05-30 02:38:53'),
(109, 98, 70, NULL, 1, 123.00, '2025-05-30 02:43:41', '2025-05-30 02:43:41'),
(110, 99, 70, NULL, 1, 123.00, '2025-05-30 02:45:57', '2025-05-30 02:45:57'),
(111, 100, 70, NULL, 1, 123.00, '2025-05-30 02:46:59', '2025-05-30 02:46:59'),
(112, 101, 70, NULL, 1, 123.00, '2025-05-30 02:55:31', '2025-05-30 02:55:31'),
(113, 102, 70, NULL, 1, 123.00, '2025-05-30 03:25:33', '2025-05-30 03:25:33'),
(114, 103, 70, NULL, 1, 123.00, '2025-05-30 03:49:32', '2025-05-30 03:49:32'),
(115, 104, 70, NULL, 1, 123.00, '2025-05-30 12:56:42', '2025-05-30 12:56:42'),
(116, 105, 70, NULL, 1, 123.00, '2025-05-30 13:09:45', '2025-05-30 13:09:45'),
(117, 106, 70, NULL, 1, 123.00, '2025-05-30 13:14:54', '2025-05-30 13:14:54'),
(118, 107, 70, NULL, 1, 123.00, '2025-05-30 13:28:23', '2025-05-30 13:28:23'),
(119, 108, 70, NULL, 1, 123.00, '2025-05-30 14:06:27', '2025-05-30 14:06:27'),
(120, 109, 70, NULL, 1, 123.00, '2025-05-30 14:17:01', '2025-05-30 14:17:01'),
(121, 110, 70, NULL, 1, 123.00, '2025-05-30 14:54:49', '2025-05-30 14:54:49'),
(122, 111, 70, NULL, 1, 123.00, '2025-05-30 15:17:12', '2025-05-30 15:17:12'),
(123, 112, 70, NULL, 1, 123.00, '2025-05-30 21:28:12', '2025-05-30 21:28:12'),
(124, 113, 70, NULL, 1, 123.00, '2025-05-30 21:28:23', '2025-05-30 21:28:23'),
(125, 114, 70, NULL, 1, 123.00, '2025-05-30 22:02:06', '2025-05-30 22:02:06'),
(126, 115, 70, NULL, 1, 123.00, '2025-05-30 22:09:12', '2025-05-30 22:09:12');

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
(318, 1, 69, 'delivered', 0, '2025-05-29 01:48:13', '2025-05-29 01:48:13'),
(319, 1, 72, 'pending', 0, '2025-05-29 22:35:19', '2025-05-29 22:35:19'),
(320, 4, 72, 'pending', 0, '2025-05-29 22:35:19', '2025-05-29 22:35:19'),
(322, 4, 72, 'approved', 0, '2025-05-29 23:13:22', '2025-05-29 23:13:22'),
(324, 1, 72, 'packed', 0, '2025-05-29 23:17:28', '2025-05-29 23:17:28'),
(325, 1, 73, 'pending', 0, '2025-05-29 23:53:58', '2025-05-29 23:53:58'),
(326, 4, 73, 'pending', 0, '2025-05-29 23:53:58', '2025-05-29 23:53:58'),
(328, 4, 73, 'approved', 0, '2025-05-29 23:54:34', '2025-05-29 23:54:34'),
(329, 1, 74, 'pending', 0, '2025-05-30 00:07:33', '2025-05-30 00:07:33'),
(330, 4, 74, 'pending', 0, '2025-05-30 00:07:33', '2025-05-30 00:07:33'),
(331, 1, 75, 'pending', 0, '2025-05-30 00:12:51', '2025-05-30 00:12:51'),
(332, 4, 75, 'pending', 0, '2025-05-30 00:12:51', '2025-05-30 00:12:51'),
(333, 1, 76, 'pending', 0, '2025-05-30 00:17:25', '2025-05-30 00:17:25'),
(334, 4, 76, 'pending', 0, '2025-05-30 00:17:25', '2025-05-30 00:17:25'),
(335, 1, 77, 'pending', 0, '2025-05-30 00:22:50', '2025-05-30 00:22:50'),
(336, 4, 77, 'pending', 0, '2025-05-30 00:22:50', '2025-05-30 00:22:50'),
(337, 1, 78, 'pending', 0, '2025-05-30 00:24:27', '2025-05-30 00:24:27'),
(338, 4, 78, 'pending', 0, '2025-05-30 00:24:27', '2025-05-30 00:24:27'),
(339, 1, 79, 'pending', 0, '2025-05-30 00:24:34', '2025-05-30 00:24:34'),
(340, 4, 79, 'pending', 0, '2025-05-30 00:24:34', '2025-05-30 00:24:34'),
(342, 4, 79, 'approved', 0, '2025-05-30 00:29:39', '2025-05-30 00:29:39'),
(343, 1, 80, 'pending', 0, '2025-05-30 00:32:09', '2025-05-30 00:32:09'),
(344, 4, 80, 'pending', 0, '2025-05-30 00:32:09', '2025-05-30 00:32:09'),
(345, 1, 81, 'pending', 0, '2025-05-30 00:37:36', '2025-05-30 00:37:36'),
(346, 4, 81, 'pending', 0, '2025-05-30 00:37:36', '2025-05-30 00:37:36'),
(348, 4, 81, 'approved', 0, '2025-05-30 00:40:58', '2025-05-30 00:40:58'),
(349, 1, 82, 'pending', 0, '2025-05-30 00:52:58', '2025-05-30 00:52:58'),
(350, 4, 82, 'pending', 0, '2025-05-30 00:52:58', '2025-05-30 00:52:58'),
(352, 4, 78, 'approved', 0, '2025-05-30 00:57:21', '2025-05-30 00:57:21'),
(353, 1, 83, 'pending', 0, '2025-05-30 00:59:06', '2025-05-30 00:59:06'),
(354, 4, 83, 'pending', 0, '2025-05-30 00:59:06', '2025-05-30 00:59:06'),
(356, 4, 83, 'approved', 0, '2025-05-30 01:05:14', '2025-05-30 01:05:14'),
(358, 4, 82, 'approved', 0, '2025-05-30 01:11:15', '2025-05-30 01:11:15'),
(360, 4, 80, 'approved', 0, '2025-05-30 01:15:40', '2025-05-30 01:15:40'),
(362, 4, 77, 'approved', 0, '2025-05-30 01:19:26', '2025-05-30 01:19:26'),
(364, 4, 76, 'approved', 0, '2025-05-30 01:23:30', '2025-05-30 01:23:30'),
(366, 4, 75, 'approved', 0, '2025-05-30 01:24:52', '2025-05-30 01:24:52'),
(368, 4, 74, 'approved', 0, '2025-05-30 01:27:02', '2025-05-30 01:27:02'),
(369, 1, 84, 'pending', 0, '2025-05-30 01:28:33', '2025-05-30 01:28:33'),
(370, 4, 84, 'pending', 0, '2025-05-30 01:28:33', '2025-05-30 01:28:33'),
(372, 4, 84, 'approved', 0, '2025-05-30 01:30:50', '2025-05-30 01:30:50'),
(373, 1, 85, 'pending', 0, '2025-05-30 01:36:28', '2025-05-30 01:36:28'),
(374, 4, 85, 'pending', 0, '2025-05-30 01:36:28', '2025-05-30 01:36:28'),
(375, 1, 86, 'pending', 0, '2025-05-30 01:36:40', '2025-05-30 01:36:40'),
(376, 4, 86, 'pending', 0, '2025-05-30 01:36:40', '2025-05-30 01:36:40'),
(378, 4, 86, 'approved', 0, '2025-05-30 01:37:19', '2025-05-30 01:37:19'),
(379, 1, 87, 'pending', 0, '2025-05-30 01:46:14', '2025-05-30 01:46:14'),
(380, 4, 87, 'pending', 0, '2025-05-30 01:46:14', '2025-05-30 01:46:14'),
(382, 4, 85, 'approved', 0, '2025-05-30 01:46:27', '2025-05-30 01:46:27'),
(384, 4, 87, 'approved', 0, '2025-05-30 01:58:21', '2025-05-30 01:58:21'),
(385, 1, 88, 'pending', 0, '2025-05-30 01:59:18', '2025-05-30 01:59:18'),
(386, 4, 88, 'pending', 0, '2025-05-30 01:59:18', '2025-05-30 01:59:18'),
(388, 4, 88, 'approved', 0, '2025-05-30 02:00:01', '2025-05-30 02:00:01'),
(389, 1, 89, 'pending', 0, '2025-05-30 02:04:17', '2025-05-30 02:04:17'),
(390, 4, 89, 'pending', 0, '2025-05-30 02:04:17', '2025-05-30 02:04:17'),
(392, 4, 89, 'approved', 0, '2025-05-30 02:04:50', '2025-05-30 02:04:50'),
(393, 1, 90, 'pending', 0, '2025-05-30 02:12:26', '2025-05-30 02:12:26'),
(394, 4, 90, 'pending', 0, '2025-05-30 02:12:26', '2025-05-30 02:12:26'),
(395, 1, 91, 'pending', 0, '2025-05-30 02:12:36', '2025-05-30 02:12:36'),
(396, 4, 91, 'pending', 0, '2025-05-30 02:12:36', '2025-05-30 02:12:36'),
(398, 4, 90, 'approved', 0, '2025-05-30 02:13:00', '2025-05-30 02:13:00'),
(400, 4, 91, 'approved', 0, '2025-05-30 02:16:31', '2025-05-30 02:16:31'),
(401, 1, 92, 'pending', 0, '2025-05-30 02:21:14', '2025-05-30 02:21:14'),
(402, 4, 92, 'pending', 0, '2025-05-30 02:21:14', '2025-05-30 02:21:14'),
(403, 1, 93, 'pending', 0, '2025-05-30 02:21:27', '2025-05-30 02:21:27'),
(404, 4, 93, 'pending', 0, '2025-05-30 02:21:27', '2025-05-30 02:21:27'),
(406, 4, 92, 'approved', 0, '2025-05-30 02:22:29', '2025-05-30 02:22:29'),
(408, 4, 93, 'approved', 0, '2025-05-30 02:26:35', '2025-05-30 02:26:35'),
(409, 1, 94, 'pending', 0, '2025-05-30 02:33:12', '2025-05-30 02:33:12'),
(410, 4, 94, 'pending', 0, '2025-05-30 02:33:12', '2025-05-30 02:33:12'),
(411, 1, 95, 'pending', 0, '2025-05-30 02:33:24', '2025-05-30 02:33:24'),
(412, 4, 95, 'pending', 0, '2025-05-30 02:33:24', '2025-05-30 02:33:24'),
(414, 4, 95, 'approved', 0, '2025-05-30 02:33:55', '2025-05-30 02:33:55'),
(416, 1, 96, 'pending', 0, '2025-05-30 02:36:11', '2025-05-30 02:36:11'),
(417, 4, 96, 'pending', 0, '2025-05-30 02:36:11', '2025-05-30 02:36:11'),
(418, 1, 97, 'pending', 0, '2025-05-30 02:38:52', '2025-05-30 02:38:52'),
(419, 4, 97, 'pending', 0, '2025-05-30 02:38:52', '2025-05-30 02:38:52'),
(421, 4, 97, 'approved', 0, '2025-05-30 02:39:16', '2025-05-30 02:39:16'),
(423, 4, 96, 'approved', 0, '2025-05-30 02:41:50', '2025-05-30 02:41:50'),
(424, 1, 98, 'pending', 0, '2025-05-30 02:43:40', '2025-05-30 02:43:40'),
(425, 4, 98, 'pending', 0, '2025-05-30 02:43:40', '2025-05-30 02:43:40'),
(427, 4, 98, 'approved', 0, '2025-05-30 02:44:52', '2025-05-30 02:44:52'),
(428, 1, 99, 'pending', 0, '2025-05-30 02:45:56', '2025-05-30 02:45:56'),
(429, 4, 99, 'pending', 0, '2025-05-30 02:45:56', '2025-05-30 02:45:56'),
(431, 4, 99, 'approved', 0, '2025-05-30 02:46:13', '2025-05-30 02:46:13'),
(432, 1, 100, 'pending', 0, '2025-05-30 02:46:58', '2025-05-30 02:46:58'),
(433, 4, 100, 'pending', 0, '2025-05-30 02:46:58', '2025-05-30 02:46:58'),
(435, 4, 100, 'approved', 0, '2025-05-30 02:50:20', '2025-05-30 02:50:20'),
(436, 1, 101, 'pending', 0, '2025-05-30 02:55:28', '2025-05-30 02:55:28'),
(437, 4, 101, 'pending', 0, '2025-05-30 02:55:28', '2025-05-30 02:55:28'),
(439, 4, 101, 'approved', 0, '2025-05-30 02:56:16', '2025-05-30 02:56:16'),
(441, 1, 101, 'packed', 0, '2025-05-30 02:59:25', '2025-05-30 02:59:25'),
(443, 1, 101, 'shipped', 0, '2025-05-30 03:00:25', '2025-05-30 03:00:25'),
(445, 1, 101, 'delivered', 0, '2025-05-30 03:00:58', '2025-05-30 03:00:58'),
(447, 1, 100, 'packed', 0, '2025-05-30 03:04:47', '2025-05-30 03:04:47'),
(449, 1, 100, 'shipped', 0, '2025-05-30 03:07:31', '2025-05-30 03:07:31'),
(451, 1, 100, 'delivered', 0, '2025-05-30 03:09:22', '2025-05-30 03:09:22'),
(453, 1, 98, 'packed', 0, '2025-05-30 03:10:56', '2025-05-30 03:10:56'),
(455, 1, 98, 'shipped', 0, '2025-05-30 03:13:23', '2025-05-30 03:13:23'),
(457, 1, 99, 'packed', 0, '2025-05-30 03:23:55', '2025-05-30 03:23:55'),
(458, 1, 102, 'pending', 0, '2025-05-30 03:25:33', '2025-05-30 03:25:33'),
(459, 4, 102, 'pending', 0, '2025-05-30 03:25:33', '2025-05-30 03:25:33'),
(461, 4, 102, 'approved', 0, '2025-05-30 03:26:05', '2025-05-30 03:26:05'),
(462, 1, 103, 'pending', 0, '2025-05-30 03:49:31', '2025-05-30 03:49:31'),
(463, 4, 103, 'pending', 0, '2025-05-30 03:49:32', '2025-05-30 03:49:32'),
(465, 4, 103, 'approved', 0, '2025-05-30 03:49:56', '2025-05-30 03:49:56'),
(466, 1, 104, 'pending', 0, '2025-05-30 12:56:42', '2025-05-30 12:56:42'),
(467, 4, 104, 'pending', 0, '2025-05-30 12:56:42', '2025-05-30 12:56:42'),
(469, 4, 104, 'approved', 0, '2025-05-30 13:03:21', '2025-05-30 13:03:21'),
(470, 1, 105, 'pending', 0, '2025-05-30 13:09:45', '2025-05-30 13:09:45'),
(471, 4, 105, 'pending', 0, '2025-05-30 13:09:45', '2025-05-30 13:09:45'),
(473, 4, 105, 'approved', 0, '2025-05-30 13:13:00', '2025-05-30 13:13:00'),
(474, 1, 106, 'pending', 0, '2025-05-30 13:14:52', '2025-05-30 13:14:52'),
(475, 4, 106, 'pending', 0, '2025-05-30 13:14:52', '2025-05-30 13:14:52'),
(477, 4, 106, 'approved', 0, '2025-05-30 13:15:47', '2025-05-30 13:15:47'),
(479, 1, 106, 'packed', 0, '2025-05-30 13:20:56', '2025-05-30 13:20:56'),
(480, 1, 107, 'pending', 0, '2025-05-30 13:28:20', '2025-05-30 13:28:20'),
(481, 4, 107, 'pending', 0, '2025-05-30 13:28:20', '2025-05-30 13:28:20'),
(483, 4, 107, 'approved', 0, '2025-05-30 13:32:18', '2025-05-30 13:32:18'),
(485, 1, 107, 'packed', 0, '2025-05-30 14:01:11', '2025-05-30 14:01:11'),
(486, 1, 108, 'pending', 0, '2025-05-30 14:06:25', '2025-05-30 14:06:25'),
(487, 4, 108, 'pending', 0, '2025-05-30 14:06:25', '2025-05-30 14:06:25'),
(488, 1, 109, 'pending', 0, '2025-05-30 14:17:00', '2025-05-30 14:17:00'),
(489, 4, 109, 'pending', 0, '2025-05-30 14:17:00', '2025-05-30 14:17:00'),
(491, 4, 109, 'approved', 0, '2025-05-30 14:45:24', '2025-05-30 14:45:24'),
(492, 1, 110, 'pending', 0, '2025-05-30 14:54:47', '2025-05-30 14:54:47'),
(493, 4, 110, 'pending', 0, '2025-05-30 14:54:47', '2025-05-30 14:54:47'),
(494, 1, 111, 'pending', 0, '2025-05-30 15:17:10', '2025-05-30 15:17:10'),
(495, 4, 111, 'pending', 0, '2025-05-30 15:17:10', '2025-05-30 15:17:10'),
(497, 4, 111, 'approved', 0, '2025-05-30 21:17:27', '2025-05-30 21:17:27'),
(499, 4, 110, 'approved', 0, '2025-05-30 21:22:22', '2025-05-30 21:22:22'),
(501, 4, 108, 'approved', 0, '2025-05-30 21:25:56', '2025-05-30 21:25:56'),
(502, 1, 112, 'pending', 0, '2025-05-30 21:28:11', '2025-05-30 21:28:11'),
(503, 4, 112, 'pending', 0, '2025-05-30 21:28:11', '2025-05-30 21:28:11'),
(504, 1, 113, 'pending', 0, '2025-05-30 21:28:22', '2025-05-30 21:28:22'),
(505, 4, 113, 'pending', 0, '2025-05-30 21:28:22', '2025-05-30 21:28:22'),
(507, 4, 113, 'approved', 0, '2025-05-30 21:30:32', '2025-05-30 21:30:32'),
(509, 1, 113, 'packed', 0, '2025-05-30 21:47:56', '2025-05-30 21:47:56'),
(511, 1, 114, 'pending', 0, '2025-05-30 22:02:05', '2025-05-30 22:02:05'),
(512, 4, 114, 'pending', 0, '2025-05-30 22:02:05', '2025-05-30 22:02:05'),
(513, 1, 115, 'pending', 0, '2025-05-30 22:09:11', '2025-05-30 22:09:11'),
(514, 4, 115, 'pending', 0, '2025-05-30 22:09:11', '2025-05-30 22:09:11'),
(516, 4, 115, 'approved', 0, '2025-05-30 22:10:40', '2025-05-30 22:10:40');

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
(65, 'chicken', 'organic, free run', 10.00, 34, 12533, '2025-05-07 16:28:21', '2025-05-27 11:41:49'),
(66, 'ground beef', 'canadian farm direct supplier (pack of 1kg)', 14.00, 7, 4243, '2025-05-07 16:30:25', '2025-05-27 11:41:39'),
(67, 'whole pepper mix', 'mix of whole pepper (white, red, black, green) pack of 400gr', 32.00, 8, 2338, '2025-05-07 16:32:13', '2025-05-28 00:55:53'),
(68, 'chicken wings', 'Bulk (Min order 1kg)', 12.00, 34, 232, '2025-05-08 23:45:21', '2025-05-27 11:31:57'),
(69, 'wheet flour', '3 kg pck', 12.00, 10, 118, '2025-05-10 14:45:56', '2025-05-29 00:54:03'),
(70, 'piano', NULL, 123.00, 7, 87657814, '2025-05-13 03:56:42', '2025-05-30 22:09:12');

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
(15, 16, 65),
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
(104, 70, 'product-images/682b6939049da_1747675449.JPG'),
(105, 69, 'product-images/682b694d63938_1747675469.JPG'),
(106, 68, 'product-images/682b696c736f9_1747675500.JPG'),
(107, 67, 'product-images/682b6986771f0_1747675526.JPG'),
(108, 66, 'product-images/682b699c81f2b_1747675548.JPG'),
(109, 65, 'product-images/682b69ad0e1d1_1747675565.JPG');

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
(17, 65, 'pack of 10', 90.00, 10432, '2025-05-07 16:28:21', '2025-05-27 11:41:49'),
(18, 66, '2kg', 22.00, 9242, '2025-05-07 16:30:25', '2025-05-27 11:41:39'),
(19, 66, '5kg', 45.00, 343, '2025-05-07 16:30:25', '2025-05-27 11:41:39'),
(20, 67, 'pack of 800gr', 89.00, 234, '2025-05-07 16:32:13', '2025-05-27 11:32:09'),
(21, 68, 'chicken wings (packed/vacuum). 3kg/pkg', 14.00, 232, '2025-05-08 23:45:21', '2025-05-27 11:31:57'),
(22, 70, 'box of 10', 1234.00, 123123, '2025-05-13 03:56:42', '2025-05-27 11:31:31'),
(23, 69, 'well', 123.00, 123, '2025-05-13 03:57:56', '2025-05-27 11:31:46');

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
('ESDB7ZAmRLWPIfu8R8OTpXMCo6rEnHRrpsZONQf5', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'YTo2OntzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0ODoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL25vdGlmaWNhdGlvbnMvdW5yZWFkLWNvdW50Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo2OiJfdG9rZW4iO3M6NDA6IkFoTnJHaVR5allrUkV1M2c5aG9OV3lPcEtSbGttUDVhclhyNTJxdzgiO3M6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxMjoid2VsY29tZV9iYWNrIjtiOjE7czo5OiJ1c2VyX25hbWUiO3M6NToiYWRtaW4iO30=', 1748651158),
('H0wse3IfLgRF6MteHRoIursok8iF1OZIsddjtGYp', NULL, '10.0.0.55', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUUVGTlFvcDZkVDNMWlNIckdTdE5rVnU3Z2NJOVBDM3ZzR0x4bDVGUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjQwOiJodHRwOi8vMTAuMC4wLjEwL1Jlc3RhdXJhbnQtX0ZyYW5jaGlzZV9TdXBwbHlfUGxhdGZvcm0vZnJhbmNoaXNlLXN1cHBseS1wbGF0Zm9ybS9wdWJsaWMvbG9naW4/aW50ZW5kZWQ9aHR0cCUzQSUyRiUyRjEwLjAuMC4xMCUyRlJlc3RhdXJhbnQtX0ZyYW5jaGlzZV9TdXBwbHlfUGxhdGZvcm0lMkZmcmFuY2hpc2Utc3VwcGx5LXBsYXRmb3JtJTJGcHVibGljJTJGZnJhbmNoaXNlZSUyRm9yZGVycyUyRjExNSUyRmRldGFpbHMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1748649993);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fcm_token` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_by` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = active, 0 = blocked',
  `email_notifications_enabled` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `fcm_token`, `phone`, `role_id`, `created_at`, `updated_at`, `updated_by`, `status`, `email_notifications_enabled`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', NULL, '1234567890', 1, '2025-05-02 22:22:44', '2025-05-31 00:19:11', 'admin', 1, 1),
(4, 'maximUSCan', '$2y$12$C9mhE38rqq9fyZ1jFt.LI.s0H5XJooxpzamlFAVDuf5tf8VhbhYxu', 'maxim.don.mg@gmail.com2', NULL, '4168560684', 2, '2025-05-04 23:09:20', '2025-05-30 01:24:02', 'maximUSCan', 1, 1),
(16, 'gabriel max', '$2y$12$jv3sIK/Ih/rk.66xliHfz.EKh2MDXBM2GL3ikqs7ehwTaP6VsNLsq', 'user@franchisee.com', NULL, '4168560684', 3, '2025-05-08 15:28:59', '2025-05-31 00:20:32', 'admin', 1, 0);

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
(71, 22, 'variant-images/682b6938f3ba2_1747675448.JPG'),
(72, 23, 'variant-images/682b694d59c74_1747675469.JPG'),
(73, 21, 'variant-images/682b696c6a9c6_1747675500.JPG'),
(74, 20, 'variant-images/682b69866d634_1747675526.JPG'),
(75, 18, 'variant-images/682b699c6e3a4_1747675548.JPG'),
(76, 19, 'variant-images/682b699c78ab2_1747675548.JPG'),
(77, 17, 'variant-images/682b69ad09a1f_1747675565.JPG');

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=180;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `order_notifications`
--
ALTER TABLE `order_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=517;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `product_favorites`
--
ALTER TABLE `product_favorites`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

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
