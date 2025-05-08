-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 08, 2025 at 05:25 AM
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
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
(9, 'dry goods', NULL, '2025-05-07 12:27:04', NULL);

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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `franchisee_details`
--

INSERT INTO `franchisee_details` (`id`, `user_id`, `company_name`, `address`, `city`, `state`, `postal_code`, `contact_name`, `created_at`, `updated_at`, `updated_by`) VALUES
(1, 14, 'Max and Company', '478 Mortimer Avenuea', 'Toronto', 'ON', 'M4J 2G5', 'Max Gabriellla', '2025-05-07 18:21:40', '2025-05-08 02:36:10', 14);

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
  `status` enum('pending','approved','rejected','packed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
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
  `qb_invoice_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `status`, `total_amount`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_zip`, `delivery_date`, `delivery_time`, `delivery_preference`, `shipping_cost`, `notes`, `manager_name`, `contact_phone`, `purchase_order`, `created_at`, `updated_at`, `qb_invoice_id`) VALUES
(7, 14, 'rejected', 90.60, '478 Mortimer Ave', 'Toronto', 'ON', 'M4J 2G5', '2025-05-10', 'morning', 'express', 15.00, 'leave it on the porch', 'Default Manager', '1234567890', NULL, '2025-05-07 18:59:34', '2025-05-07 15:21:55', NULL),
(8, 14, 'rejected', 794.88, '478 Mortimer Ave', 'Toronto', 'ON', 'M4J 2G5', '2025-05-10', 'morning', 'standard', 0.00, 'bleh bleh bleh', 'Default Manager', '1234567890', NULL, '2025-05-07 19:00:53', '2025-05-07 15:21:49', NULL),
(9, 14, 'rejected', 136.08, '478 Mortimer Ave', 'Toronto', 'ON', 'M4J 2G5', '2025-05-10', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-07 19:07:38', '2025-05-07 15:21:42', NULL),
(10, 14, 'rejected', 108.00, '478 Mortimer Ave', 'Toronto', 'ON', 'M4J 2G5', '2025-05-10', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-07 19:18:44', '2025-05-07 15:21:37', NULL),
(11, 14, 'rejected', 1105.92, '478 Mortimer Ave', 'Toronto', 'ON', 'M4J 2G5', '2025-05-10', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-07 15:23:55', '2025-05-07 15:24:23', NULL),
(16, 14, 'rejected', 653.40, '478 Mortimer Ave', 'Toronto', 'ON', 'M4J 2G5', '2025-05-10', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-07 22:05:42', '2025-05-07 22:35:06', NULL),
(17, 14, 'rejected', 466.56, '478 Mortimer Ave', 'Toronto', 'ON', 'M4J 2G5', '2025-05-10', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-07 22:09:29', '2025-05-07 22:36:21', NULL),
(18, 14, 'rejected', 1080.00, '478 Mortimer Ave', 'Toronto', 'ON', 'M4J 2G5', '2025-05-10', 'morning', 'standard', 0.00, '', 'Default Manager', '4168560684', NULL, '2025-05-07 22:15:19', '2025-05-07 22:36:14', NULL),
(19, 14, 'rejected', 278.64, '478 Mortimer Avenue', 'Toronto', 'AB', 'M4J 2G5', '2025-05-10', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-07 22:42:06', '2025-05-07 22:43:12', NULL),
(20, 14, 'rejected', 151.20, '478 Mortimer Avenue', 'Toronto', 'AB', 'M4J 2G5', '2025-05-10', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-07 22:47:51', '2025-05-07 22:48:21', NULL),
(21, 14, 'rejected', 407.04, '478 Mortimer Avenue', 'Toronto', 'AB', 'M4J 2G5', '2025-05-10', 'morning', 'express', 15.00, 'lets see', 'Default Manager', '416 8560684', NULL, '2025-05-07 23:22:30', '2025-05-07 23:24:37', NULL),
(22, 14, 'pending', 392.04, '478 Mortimer Avenue', 'Toronto', 'AB', 'M4J 2G5', '2025-05-10', 'morning', 'standard', 0.00, '', 'Default Manager', '416 8560684', NULL, '2025-05-07 23:41:29', '2025-05-07 23:41:29', NULL);

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
(6, 7, 66, NULL, 5, 14.00, '2025-05-07 18:59:34', '2025-05-07 18:59:34'),
(7, 8, 67, NULL, 23, 32.00, '2025-05-07 19:00:53', '2025-05-07 19:00:53'),
(8, 9, 66, NULL, 9, 14.00, '2025-05-07 19:07:38', '2025-05-07 19:07:38'),
(9, 10, 65, NULL, 10, 10.00, '2025-05-07 19:18:44', '2025-05-07 19:18:44'),
(10, 11, 67, NULL, 32, 32.00, '2025-05-07 15:23:55', '2025-05-07 15:23:55'),
(19, 16, 67, 20, 5, 121.00, '2025-05-07 22:05:42', '2025-05-07 22:05:42'),
(20, 17, 66, 18, 12, 36.00, '2025-05-07 22:09:29', '2025-05-07 22:09:29'),
(21, 18, 65, 17, 10, 100.00, '2025-05-07 22:15:19', '2025-05-07 22:15:19'),
(22, 19, 66, 19, 2, 59.00, '2025-05-07 22:42:06', '2025-05-07 22:42:06'),
(23, 19, 66, NULL, 10, 14.00, '2025-05-07 22:42:06', '2025-05-07 22:42:06'),
(24, 20, 66, NULL, 10, 14.00, '2025-05-07 22:47:51', '2025-05-07 22:47:51'),
(25, 21, 67, 20, 3, 121.00, '2025-05-07 23:22:30', '2025-05-07 23:22:30'),
(26, 22, 67, 20, 3, 121.00, '2025-05-07 23:41:29', '2025-05-07 23:41:29');

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
(65, 'checken', 'organic, free run', 10.00, 6, 248, '2025-05-07 16:28:21', '2025-05-07 15:22:53'),
(66, 'ground beef', 'canadian farm direct supplier (pack of 1kg)', 14.00, 7, 450, '2025-05-07 16:30:25', '2025-05-07 22:48:21'),
(67, 'whole pepper mix', 'mix of whole pepper (white, red, black, green) pack of 400gr', 32.00, 8, 123, '2025-05-07 16:32:13', '2025-05-08 00:15:21');

-- --------------------------------------------------------

--
-- Table structure for table `product_favorites`
--

CREATE TABLE `product_favorites` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(66, 67, 'product-images/681be0e0e0f29_1746657504.png'),
(67, 66, 'product-images/OYygNJL8g41h97MWY5aquBgdHehGliijoVhtVghq.svg'),
(68, 65, 'product-images/DxO7pnvhujNyJtOj5SUsm0AyXMe5mPwQVV75xr9m.svg');

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
(17, 65, 'pack of 10', 90.00, 0, '2025-05-07 16:28:21', '2025-05-07 22:15:19'),
(18, 66, '2kg', 22.00, 0, '2025-05-07 16:30:25', '2025-05-07 22:09:29'),
(19, 66, '5kg', 45.00, 12, '2025-05-07 16:30:25', '2025-05-07 22:43:12'),
(20, 67, 'pack of 800gr', 89.00, 15, '2025-05-07 16:32:13', '2025-05-07 23:41:29');

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
('pUUnQMVNhFiEwRPQKRcboTjgMrauyPS9Lr2ssZQl', 14, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiME9mMzNpRnVIUEo3cEY0aEZZVFgxb2hnV1ZsbzcyWTF1M2gxNjlDcyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9mcmFuY2hpc2VlL2Rhc2hib2FyZCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE0O3M6MTI6IndlbGNvbWVfYmFjayI7YjoxO3M6OToidXNlcl9uYW1lIjtzOjQ6InVzZXIiO30=', 1746656123),
('XXVarY3FVDzWMWhRuejSas48gTtDlGnuBJUWDGZu', NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRDRjYmZXQVhWVXExRldhNnAxYW1Ra0dKVWJlMzZoNUtrSzdrVFhuRyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MTI6IndlbGNvbWVfYmFjayI7YjoxO3M6OToidXNlcl9uYW1lIjtzOjU6ImFkbWluIjt9', 1746631474);

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
  `updated_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `phone`, `role_id`, `created_at`, `updated_at`, `updated_by`) VALUES
(1, 'adminMax', '$2y$12$xzm9Y2BrU19wF2JwlnitaObjm78dGQbtcrJgDT050wq6VE06dtywO', 'admin@example.com', '416-856-0684', 1, '2025-05-02 22:22:44', '2025-05-08 03:10:17', 1),
(4, 'maximUS', '$2y$12$PGoAZYIGCEBHw0Dny.FqxOoeWRrcyBu0D/e2izbbdbgHxFZMgPD..', 'maxim.don.mg@gmail.com', '(416) 856-0684', 2, '2025-05-04 23:09:20', '2025-05-08 03:06:44', 4),
(14, 'user1', '$2y$12$cv8j0HnNLjaBo7FKZPJGReVPoDOvUzRuYF4Jt1GLLUOMow5WgwCVa', 'user@franche.com', '416 8560684', 3, '2025-05-07 18:21:40', '2025-05-08 02:35:54', 14);

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
(46, 18, 'variant-images/681be13bdc36f_1746657595.jpg'),
(47, 19, 'variant-images/681be13be5223_1746657595.png'),
(48, 17, 'variant-images/681be1572337d_1746657623.png'),
(54, 20, 'variant-images/681bfa0379056_1746663939.png');

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
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `franchisee_details`
--
ALTER TABLE `franchisee_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `franchisee_details_user_id_unique` (`user_id`),
  ADD KEY `fk_franchisee_details_updated_by` (`updated_by`);

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
  ADD KEY `idx_user_role` (`role_id`),
  ADD KEY `fk_users_updated_by` (`updated_by`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `franchisee_details`
--
ALTER TABLE `franchisee_details`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `product_favorites`
--
ALTER TABLE `product_favorites`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `variant_images`
--
ALTER TABLE `variant_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_details`
--
ALTER TABLE `admin_details`
  ADD CONSTRAINT `admin_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `franchisee_details`
--
ALTER TABLE `franchisee_details`
  ADD CONSTRAINT `fk_franchisee_details_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
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
  ADD CONSTRAINT `fk_users_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
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
