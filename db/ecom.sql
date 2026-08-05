-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 24, 2026 at 05:15 PM
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
-- Database: `ecom`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `label` varchar(80) DEFAULT NULL,
  `contact_name` varchar(160) DEFAULT NULL,
  `contact_phone` varchar(32) DEFAULT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(120) NOT NULL,
  `state` varchar(120) DEFAULT NULL,
  `postal_code` varchar(32) DEFAULT NULL,
  `country_id` smallint(5) UNSIGNED NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `store_id`, `label`, `contact_name`, `contact_phone`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country_id`, `latitude`, `longitude`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 4, NULL, 'Home', 'Bob Customer', '01700000004', '123 Gulshan Avenue', 'Level 5, House 12', 'Dhaka', 'Dhaka', '1212', 4, NULL, NULL, 1, '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(2, NULL, 1, 'Head Office', 'Store Manager', '01711111111', '456 Motijheel C/A', NULL, 'Dhaka', 'Dhaka', '1000', 4, NULL, NULL, 1, '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(3, 4, NULL, 'Home', 'Bob Customer', '01700000004', '123 Gulshan Avenue', 'Level 5, House 12', 'Dhaka', 'Dhaka', '1212', 4, NULL, NULL, 1, '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(4, NULL, 1, 'Head Office', 'Store Manager', '01711111111', '456 Motijheel C/A', NULL, 'Dhaka', 'Dhaka', '1000', 4, NULL, NULL, 1, '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `announcement_bars`
--

CREATE TABLE `announcement_bars` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `left_text` varchar(500) DEFAULT NULL,
  `center_text` varchar(500) DEFAULT NULL,
  `right_text` varchar(500) DEFAULT NULL,
  `background_color` varchar(20) NOT NULL DEFAULT '#0F1115',
  `text_color` varchar(20) NOT NULL DEFAULT '#ffffff',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcement_bars`
--

INSERT INTO `announcement_bars` (`id`, `left_text`, `center_text`, `right_text`, `background_color`, `text_color`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '🚚  Free Shipping on Orders Over ৳99', 'Summer Sale is Live! Up to 60% OFF 🔥', '📞 Support: (800) 123-4567', '#0f1115', '#ffffff', 0, 'active', '2026-06-28 10:10:55', '2026-06-28 10:21:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `scope_type` enum('global','store','user') NOT NULL DEFAULT 'global',
  `scope_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `setting_key` varchar(120) NOT NULL,
  `setting_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`setting_value`)),
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`id`, `scope_type`, `scope_id`, `setting_key`, `setting_value`, `is_public`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'global', 0, 'storefront.enabled', '{\"enabled\":true}', 1, NULL, NULL, NULL),
(2, 'global', 0, 'checkout.tax_inclusive', '{\"enabled\":false}', 0, NULL, NULL, NULL),
(3, 'global', 0, 'delivery.default_provider', '{\"provider\":\"local_delivery\"}', 0, NULL, NULL, NULL),
(4, 'global', 0, 'pos.require_shift_for_sale', '{\"enabled\":true}', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(100) NOT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(500) DEFAULT NULL,
  `smtag` varchar(255) DEFAULT NULL,
  `primary_btn` varchar(255) DEFAULT NULL,
  `primary_btn_url` varchar(500) DEFAULT NULL,
  `primary_btn_color` varchar(50) DEFAULT NULL,
  `secondary_btn` varchar(255) DEFAULT NULL,
  `secondary_btn_url` varchar(500) DEFAULT NULL,
  `secondary_btn_color` varchar(50) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `banner_image`, `title`, `subtitle`, `smtag`, `primary_btn`, `primary_btn_url`, `primary_btn_color`, `secondary_btn`, `secondary_btn_url`, `secondary_btn_color`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'https://via.placeholder.com/1600x600?text=Best+Deals', 'Shop The Best Deals', 'Up to 50% off across categories', 'SALE', 'Shop Now', '/shop', NULL, 'View Offers', '/deals', NULL, 1, 'inactive', '2026-06-25 06:17:43', '2026-06-28 10:25:03', NULL),
(2, 'banners/vVftOZaAZugnPS4GyzCuxrC8oNxPJZkd7jEAnKO7.png', 'Discover New Styles', 'Fresh collections for every season', 'NEW', 'Browse Now', '/new-arrivals', NULL, 'Shop Women', '/products/clothing', NULL, 2, 'active', '2026-06-25 06:17:43', '2026-06-29 10:39:39', NULL),
(3, 'banners/xXPMOoVBwO6bsJELSn2bgrGDW4RBFeTtAcQhhWpN.png', 'Fast Delivery Now Available', 'Free shipping on orders over $50', 'FREE', 'Start Shopping', '/products', NULL, 'Learn More', '/shipping', NULL, 3, 'active', '2026-06-25 06:17:43', '2026-06-28 10:25:29', NULL),
(4, 'https://via.placeholder.com/1600x600?text=Best+Deals', 'Shop The Best Deals', 'Up to 50% off across categories', 'SALE', 'Shop Now', '/shop', NULL, 'View Offers', '/deals', NULL, 1, 'inactive', '2026-06-26 06:05:44', '2026-06-28 10:24:52', NULL),
(5, 'https://via.placeholder.com/1600x600?text=New+Styles', 'Discover New Styles', 'Fresh collections for every season', 'NEW', 'Browse Now', '/new-arrivals', NULL, 'Shop Women', '/products/clothing', NULL, 2, 'inactive', '2026-06-26 06:05:44', '2026-06-28 10:24:43', NULL),
(6, 'banners/ow88ftf74Mhm86BtjUe8pMGTmKJsn7mtcgscZeqP.png', 'Fast Delivery Now Available', 'Free shipping on orders over $50', 'FREE', 'Start Shopping', '/products', '#1a462f', 'Learn More', '/shipping', '#f6f4f4', 3, 'active', '2026-06-26 06:05:44', '2026-06-30 13:11:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `logo_url`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Nike', 'nike', NULL, 'active', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(2, 'Adidas', 'adidas', NULL, 'active', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(3, 'Apple', 'apple', NULL, 'active', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(4, 'Samsung', 'samsung', NULL, 'active', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(5, 'Sony', 'sony', NULL, 'active', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(6, 'Local Brand', 'local-brand', NULL, 'active', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(7, 'EcoThreads', 'eco-threads', NULL, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(8, 'UrbanFit', 'urban-fit', NULL, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(9, 'Bengal Cotton', 'bengal-cotton', NULL, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(10, 'Deshi Threads', 'deshi-threads', NULL, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(11, 'Kazi Casuals', 'kazi-casuals', NULL, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(12, 'Nokshi Fashion', 'nokshi-fashion', NULL, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(13, 'Pride Garments', 'pride-garments', NULL, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(14, 'Dhaka Drape', 'dhaka-drape', NULL, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(15, 'BD Style House', 'bd-style-house', NULL, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(16, 'Rongdhonu Wear', 'rongdhonu-wear', NULL, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` varchar(120) DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('active','converted','abandoned') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `session_id`, `store_id`, `status`, `created_at`, `updated_at`, `expires_at`, `deleted_at`) VALUES
(1, 1, NULL, 1, 'active', '2026-06-25 06:17:44', '2026-06-25 06:17:44', '2026-07-02 12:17:44', NULL),
(2, 2, NULL, 1, 'active', '2026-06-25 06:17:44', '2026-06-25 06:17:44', '2026-07-02 12:17:44', NULL),
(3, 3, NULL, 2, 'abandoned', '2026-06-25 06:17:44', '2026-06-25 06:17:44', '2026-06-23 12:17:44', NULL),
(4, 1, NULL, 1, 'active', '2026-06-26 06:05:46', '2026-06-26 06:05:46', '2026-07-03 12:05:46', NULL),
(5, 2, NULL, 1, 'active', '2026-06-26 06:05:46', '2026-06-26 06:05:46', '2026-07-03 12:05:46', NULL),
(6, 3, NULL, 2, 'abandoned', '2026-06-26 06:05:46', '2026-06-26 06:05:46', '2026-06-24 12:05:46', NULL),
(7, 5, NULL, NULL, 'converted', '2026-06-26 13:16:27', '2026-06-26 13:27:35', '2026-07-03 19:16:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cart_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED NOT NULL,
  `variant_option_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `unit_price` decimal(19,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `variant_id`, `variant_option_id`, `quantity`, `unit_price`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, NULL, 2, 149999.0000, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(2, 1, 2, NULL, 1, 172499.0000, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(3, 2, 3, NULL, 3, 194999.0000, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(4, 3, 4, NULL, 1, 139999.0000, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(5, 4, 1, NULL, 2, 149999.0000, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(6, 4, 2, NULL, 1, 172499.0000, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(7, 5, 3, NULL, 3, 194999.0000, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(8, 6, 4, NULL, 1, 139999.0000, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(9, 7, 283, 3, 1, 2449.0000, '2026-06-26 13:16:28', '2026-06-26 13:27:35', '2026-06-26 13:27:35');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(160) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `image`, `description`, `image_url`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 'Electronics', 'electronics', 'categories/380SEpNOumgVRTkir4fNsSHLLfgLjQgwjOsscUis.png', 'Electronic devices and accessories', NULL, 1, 'active', '2026-06-25 06:17:40', '2026-06-30 10:58:44', NULL),
(2, NULL, 'Clothing', 'clothing', 'categories/Ae92PNgmnm0iZNKxFasGUaMuWL87lYUzjEbyuWbl.png', 'Apparel and fashion items', NULL, 2, 'active', '2026-06-25 06:17:40', '2026-06-30 11:03:02', NULL),
(3, NULL, 'Sports', 'sports', NULL, 'Sports equipment and gear', NULL, 3, 'active', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(4, NULL, 'Home & Kitchen', 'home-kitchen', NULL, 'Home appliances and kitchenware', NULL, 4, 'active', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(5, NULL, 'Books', 'books', NULL, 'Books and educational materials', NULL, 5, 'active', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(6, 1, 'Smartphones', 'smartphones', 'categories/MY3NntngWFn17Bxgt8DiIWTJN5pxVAUSpU66xnWp.png', 'Mobile phones and accessories', NULL, 1, 'active', '2026-06-25 06:17:40', '2026-06-30 11:00:44', NULL),
(7, 1, 'Laptops', 'laptops', NULL, 'Laptop computers', NULL, 2, 'active', '2026-06-25 06:17:40', '2026-06-25 06:41:13', NULL),
(8, 2, 'Men', 'men', 'categories/81Ftuy9Gq9aBBcgTFFlj9kNTTaBJQELFpOMEimMi.webp', 'Men\'s clothing', NULL, 1, 'active', '2026-06-25 06:17:40', '2026-06-30 10:59:22', NULL),
(9, 2, 'Women', 'women', NULL, 'Women\'s clothing', NULL, 2, 'active', '2026-06-25 06:17:40', '2026-06-25 06:41:13', NULL),
(10, NULL, 'Men\'s T-Shirts', 'men-tshirts', 'categories/d3Inv7NnjKxGtRnjpr7TKNNW7QCDcMjEsVAeJudN.jpg', 'Casual and formal t-shirts for men', NULL, 1, 'active', '2026-06-26 06:05:40', '2026-06-29 13:21:50', NULL),
(11, NULL, 'Men\'s Shirts', 'men-shirts', 'categories/4GV31lP9o80BWeDtp48BW6Ku2PtaRHq8tl91mKsH.webp', 'Formal and casual shirts for men', NULL, 2, 'active', '2026-06-26 06:05:40', '2026-06-29 13:24:22', NULL),
(12, NULL, 'Men\'s Panjabi', 'men-panjabi', NULL, 'Traditional panjabi for men', NULL, 3, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(13, NULL, 'Men\'s Pants', 'men-pants', NULL, 'Jeans, trousers, and casual pants', NULL, 4, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(14, NULL, 'Men\'s Suits', 'men-suits', NULL, 'Suits and blazers for men', NULL, 5, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(15, NULL, 'Men\'s Winter Wear', 'men-winter', NULL, 'Jackets, sweaters, hoodies for men', NULL, 6, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(16, NULL, 'Men\'s Undergarments', 'men-underwear', NULL, 'Underwear, socks, vests', NULL, 7, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(17, NULL, 'Men\'s Footwear', 'men-footwear', NULL, 'Shoes, sandals, boots for men', NULL, 8, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(18, NULL, 'Men\'s Traditional', 'men-traditional', NULL, 'Traditional attire for men', NULL, 9, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(19, NULL, 'Women\'s Sarees', 'women-sarees', NULL, 'Silk, cotton, and designer sarees', NULL, 10, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(20, NULL, 'Women\'s Salwar Kameez', 'women-salwar', NULL, 'Salwar kameez and Anarkali suits', NULL, 11, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(21, NULL, 'Women\'s Kurtis', 'women-kurtis', NULL, 'Kurtis and tunics for women', NULL, 12, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(22, NULL, 'Women\'s Dresses', 'women-dresses', NULL, 'Party and casual dresses', NULL, 13, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(23, NULL, 'Women\'s Tops', 'women-tops', NULL, 'Blouses, tops, and shirts', NULL, 14, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(24, NULL, 'Women\'s Jeans & Pants', 'women-jeans', NULL, 'Jeans, palazzos, and trousers', NULL, 15, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(25, NULL, 'Women\'s Hijabs', 'women-hijabs', NULL, 'Hijabs, scarves, and dupattas', NULL, 16, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(26, NULL, 'Women\'s Abayas', 'women-abayas', NULL, 'Abayas and burqas', NULL, 17, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(27, NULL, 'Women\'s Footwear', 'women-footwear', NULL, 'Shoes, sandals, heels for women', NULL, 18, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(28, NULL, 'Women\'s Winter', 'women-winter', NULL, 'Cardigans, shawls, winter wear', NULL, 19, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(29, NULL, 'Bags', 'bags', NULL, 'Handbags, backpacks, wallets', NULL, 20, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(30, NULL, 'Watches', 'watches', NULL, 'Analog and digital watches', NULL, 21, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(31, NULL, 'Jewelry', 'jewelry', NULL, 'Necklaces, earrings, rings, bangles', NULL, 22, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(32, NULL, 'Belts', 'belts', NULL, 'Leather and fabric belts', NULL, 23, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(33, NULL, 'Sunglasses', 'sunglasses', NULL, 'Sunglasses and eyewear', NULL, 24, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(34, NULL, 'Caps & Hats', 'caps-hats', NULL, 'Caps, hats, and headwear', NULL, 25, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(35, NULL, 'Perfumes', 'perfumes', NULL, 'Perfumes, deodorants, attars', NULL, 26, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(36, NULL, 'Kid\'s Wear', 'kids-wear', NULL, 'Clothing for children', NULL, 27, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(37, NULL, 'Couple Sets', 'couple-sets', NULL, 'Matching couple outfits', NULL, 28, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(38, NULL, 'Eid Collection', 'eid-collection', NULL, 'Special Eid outfits', NULL, 29, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(39, NULL, 'Sale Items', 'sale-items', NULL, 'Discounted items', NULL, 30, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `iso2` char(2) NOT NULL,
  `name` varchar(100) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `iso2`, `name`, `deleted_at`) VALUES
(1, 'US', 'United States', NULL),
(2, 'CA', 'Canada', NULL),
(3, 'GB', 'United Kingdom', NULL),
(4, 'BD', 'Bangladesh', NULL),
(5, 'IN', 'India', NULL),
(6, 'PK', 'Pakistan', NULL),
(7, 'AU', 'Australia', NULL),
(8, 'DE', 'Germany', NULL),
(9, 'FR', 'France', NULL),
(10, 'JP', 'Japan', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(80) NOT NULL,
  `discount_type` enum('percentage','fixed_amount','free_shipping') NOT NULL,
  `discount_value` decimal(19,4) NOT NULL DEFAULT 0.0000,
  `minimum_order_amount` decimal(19,4) NOT NULL DEFAULT 0.0000,
  `usage_limit` int(10) UNSIGNED DEFAULT NULL,
  `usage_limit_per_user` int(10) UNSIGNED DEFAULT NULL,
  `used_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_type`, `discount_value`, `minimum_order_amount`, `usage_limit`, `usage_limit_per_user`, `used_count`, `starts_at`, `ends_at`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'WELCOME10', 'percentage', 10.0000, 50.0000, 100, 1, 0, '2026-06-18 12:17:44', '2026-07-25 12:17:44', 'active', '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(2, 'FLAT20', 'fixed_amount', 20.0000, 100.0000, 50, 2, 5, '2026-06-22 12:17:44', '2026-08-24 12:17:44', 'active', '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(3, 'FREESHIP', 'free_shipping', 0.0000, 75.0000, 200, 5, 12, '2026-06-24 12:17:44', '2026-09-23 12:17:44', 'active', '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(4, 'SUMMER25', 'percentage', 25.0000, 150.0000, 30, 1, 0, '2026-06-26 12:17:44', '2026-07-25 12:17:44', 'active', '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(5, 'EXPIRED5', 'percentage', 5.0000, 25.0000, 100, 3, 88, '2026-04-26 12:17:44', '2026-06-15 12:17:44', 'inactive', '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_boy_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','assigned','picked','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `delivery_address` varchar(255) NOT NULL,
  `delivery_city` varchar(255) NOT NULL,
  `delivery_phone` varchar(255) NOT NULL,
  `delivery_notes` text DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `picked_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `order_id`, `user_id`, `delivery_boy_id`, `status`, `delivery_address`, `delivery_city`, `delivery_phone`, `delivery_notes`, `assigned_at`, `picked_at`, `delivered_at`, `cancelled_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 11, 5, NULL, 'pending', 'dfg', 'dfasfd', '01323814588', NULL, NULL, NULL, NULL, NULL, '2026-06-26 13:27:35', '2026-06-26 13:27:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_drivers`
--

CREATE TABLE `delivery_drivers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `delivery_zone_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_code` varchar(80) NOT NULL,
  `name` varchar(160) NOT NULL,
  `phone` varchar(40) NOT NULL,
  `email` varchar(160) DEFAULT NULL,
  `license_number` varchar(120) DEFAULT NULL,
  `vehicle_type` enum('walk','bike','motorbike','car','van','truck') NOT NULL DEFAULT 'motorbike',
  `vehicle_plate` varchar(80) DEFAULT NULL,
  `capacity_kg` decimal(8,2) DEFAULT NULL,
  `status` enum('available','busy','offline','inactive') NOT NULL DEFAULT 'available',
  `current_latitude` decimal(10,7) DEFAULT NULL,
  `current_longitude` decimal(10,7) DEFAULT NULL,
  `last_seen_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_zones`
--

CREATE TABLE `delivery_zones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(160) NOT NULL,
  `code` varchar(60) NOT NULL,
  `city` varchar(120) DEFAULT NULL,
  `state` varchar(120) DEFAULT NULL,
  `country_id` smallint(5) UNSIGNED DEFAULT NULL,
  `postal_codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`postal_codes`)),
  `base_fee` decimal(19,4) NOT NULL DEFAULT 0.0000,
  `per_km_fee` decimal(19,4) NOT NULL DEFAULT 0.0000,
  `free_shipping_min` decimal(19,4) DEFAULT NULL,
  `max_delivery_distance_km` decimal(8,2) DEFAULT NULL,
  `estimated_min_days` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `estimated_max_days` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `homepage_ctas`
--

CREATE TABLE `homepage_ctas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cta_style` varchar(50) NOT NULL DEFAULT 'style1',
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `button_text` varchar(255) DEFAULT NULL,
  `button_link` varchar(500) DEFAULT NULL,
  `background_color` varchar(20) NOT NULL DEFAULT '#f8f9fa',
  `text_color` varchar(20) NOT NULL DEFAULT '#1f2937',
  `button_color` varchar(20) NOT NULL DEFAULT '#1e3a8a',
  `button_text_color` varchar(20) NOT NULL DEFAULT '#ffffff',
  `button_position` varchar(50) DEFAULT 'center',
  `button_margin_top` int(11) DEFAULT NULL,
  `button_margin_bottom` int(11) DEFAULT NULL,
  `button_margin_left` int(11) DEFAULT NULL,
  `button_margin_right` int(11) DEFAULT NULL,
  `overlay_color` varchar(20) DEFAULT 'rgba(0,0,0,0.4)',
  `badge_text` varchar(100) DEFAULT NULL,
  `badge_color` varchar(20) DEFAULT '#ef4444',
  `secondary_button_text` varchar(255) DEFAULT NULL,
  `secondary_button_link` varchar(500) DEFAULT NULL,
  `secondary_button_color` varchar(20) DEFAULT '#ffffff',
  `secondary_button_text_color` varchar(20) DEFAULT '#1f2937',
  `secondary_button_position` varchar(50) DEFAULT 'center',
  `secondary_button_margin_top` int(11) DEFAULT NULL,
  `secondary_button_margin_bottom` int(11) DEFAULT NULL,
  `secondary_button_margin_left` int(11) DEFAULT NULL,
  `secondary_button_margin_right` int(11) DEFAULT NULL,
  `content_alignment` varchar(50) DEFAULT 'center',
  `content_margin_top` int(11) DEFAULT NULL,
  `content_margin_bottom` int(11) DEFAULT NULL,
  `content_margin_left` int(11) DEFAULT NULL,
  `content_margin_right` int(11) DEFAULT NULL,
  `feature_icon_1` varchar(100) DEFAULT NULL,
  `feature_text_1` varchar(255) DEFAULT NULL,
  `feature_icon_2` varchar(100) DEFAULT NULL,
  `feature_text_2` varchar(255) DEFAULT NULL,
  `feature_icon_3` varchar(100) DEFAULT NULL,
  `feature_text_3` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `homepage_ctas`
--

INSERT INTO `homepage_ctas` (`id`, `cta_style`, `title`, `subtitle`, `description`, `image`, `banner_image`, `button_text`, `button_link`, `background_color`, `text_color`, `button_color`, `button_text_color`, `button_position`, `button_margin_top`, `button_margin_bottom`, `button_margin_left`, `button_margin_right`, `overlay_color`, `badge_text`, `badge_color`, `secondary_button_text`, `secondary_button_link`, `secondary_button_color`, `secondary_button_text_color`, `secondary_button_position`, `secondary_button_margin_top`, `secondary_button_margin_bottom`, `secondary_button_margin_left`, `secondary_button_margin_right`, `content_alignment`, `content_margin_top`, `content_margin_bottom`, `content_margin_left`, `content_margin_right`, `feature_icon_1`, `feature_text_1`, `feature_icon_2`, `feature_text_2`, `feature_icon_3`, `feature_text_3`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'style1', 'Summer Deals For You', 'Discounts on top products', 'Shop our biggest sale of the year on electronics, fashion, and home essentials. Limited time offer!', 'homepage-ctas/LsaW8qVZi53ytmizWWpjGEmOV1boWmF95jWE7Ajq.png', NULL, 'Shop Now', '/sale/summer', '#fee2e2', '#991b1b', '#dc2626', '#ffffff', 'center', NULL, NULL, NULL, NULL, '#000000', 'SALES OFFER', '#ef4444', 'Buy Now', '/shop', '#ffffff', '#1f2937', 'center', NULL, NULL, NULL, NULL, 'center', NULL, NULL, NULL, NULL, '🔥', 'Feature text 1', '☀', 'Feature text 2', NULL, NULL, 3, 'inactive', '2026-07-16 16:40:21', '2026-07-17 13:14:53', NULL),
(2, 'style2', NULL, NULL, NULL, 'homepage-ctas/yVeLT078g6MB4a66m3uf7uoqFnVAItE6iWgoHIOj.png', 'homepage-ctas/banners/fCA5yIHTVQlPfT7VyAVmQepSa123N52I48JuGrWJ.png', 'Explore Collection', '/category/premium', '#1e1b4b', '#ffffff', '#f59e0b', '#1e1b4b', 'bottom-right', NULL, 70, NULL, 50, '#867474', 'Limited Edition', '#ef4444', NULL, NULL, '#ffffff', '#1f2937', 'bottom-right', NULL, 50, NULL, 50, 'center', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'active', '2026-07-16 16:40:21', '2026-07-17 12:56:37', NULL),
(3, 'style3', 'Flash Sale Weekend', 'Up to 70% off', 'Huge discounts on thousands of items. This weekend only — don\'t miss out on incredible savings!', NULL, NULL, 'Grab the Deals', '/sale/flash', '#fef3c7', '#92400e', '#f59e0b', '#ffffff', 'center', NULL, NULL, NULL, NULL, NULL, '🔥 Hot Sale', '#ef4444', NULL, NULL, NULL, NULL, 'center', NULL, NULL, NULL, NULL, 'center', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'active', '2026-07-16 16:40:21', '2026-07-16 16:40:21', NULL),
(4, 'style4', 'New Arrivals Are Here', 'Fresh styles, fresh vibes', 'Be the first to explore our newest collection. From casual wear to formal attire, find your perfect look.', 'homepage-ctas/gkso9nPqUl9MAjmCXTz2PGuz6wHkdF4uxojMeEqJ.png', NULL, 'Shop New In', '/new-arrivals', '#ecfdf5', '#065f46', '#059669', '#ffffff', 'center', NULL, NULL, NULL, NULL, '#000000', NULL, '#3b82f6', 'Learn More', '/about', '#ffffff', '#059669', 'center', NULL, NULL, NULL, NULL, 'center', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'active', '2026-07-16 16:40:21', '2026-07-17 13:00:02', NULL),
(5, 'style5', 'Why Shop With Us', 'The best shopping experience', 'We are committed to providing you with the best online shopping experience possible.', NULL, NULL, 'Start Shopping', '/products', '#f0f9ff', '#1e3a8a', '#1e3a8a', '#ffffff', 'center', NULL, NULL, NULL, NULL, NULL, 'Trusted by 10K+', '#8b5cf6', NULL, NULL, NULL, NULL, 'center', NULL, NULL, NULL, NULL, 'center', NULL, NULL, NULL, NULL, '🚚', 'Free Shipping on orders over $50', '🔒', 'Secure Payment with SSL encryption', '💬', '24/7 Customer Support', 4, 'active', '2026-07-16 16:40:21', '2026-07-16 16:40:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_locations`
--

CREATE TABLE `inventory_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `location_type` enum('warehouse','retail','delivery_hub') NOT NULL DEFAULT 'warehouse',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_locations`
--

INSERT INTO `inventory_locations` (`id`, `store_id`, `name`, `location_type`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Main Warehouse - Dhaka', 'warehouse', 'active', '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(2, 1, 'Retail Shop - Gulshan', 'retail', 'active', '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(3, 1, 'Delivery Hub - Mirpur', 'delivery_hub', 'active', '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(4, 3, 'Online Warehouse', 'warehouse', 'active', '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED NOT NULL,
  `movement_type` enum('purchase','sale','return','adjustment','transfer_in','transfer_out','reservation','release') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_type` varchar(60) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_movements`
--

INSERT INTO `inventory_movements` (`id`, `location_id`, `variant_id`, `movement_type`, `quantity`, `reference_type`, `reference_id`, `note`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'purchase', 100, 'PO', 1001, 'Initial stock purchase order', 1, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(2, 1, 1, 'purchase', 100, 'PO', 1001, 'Initial stock purchase order', 1, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_stock`
--

CREATE TABLE `inventory_stock` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED NOT NULL,
  `quantity_on_hand` int(11) NOT NULL DEFAULT 0,
  `quantity_reserved` int(11) NOT NULL DEFAULT 0,
  `reorder_point` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_stock`
--

INSERT INTO `inventory_stock` (`id`, `location_id`, `variant_id`, `quantity_on_hand`, `quantity_reserved`, `reorder_point`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 177, 8, 20, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(2, 2, 1, 5, 5, 5, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(3, 1, 2, 174, 18, 20, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(4, 2, 2, 24, 2, 5, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(5, 1, 3, 159, 5, 20, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(6, 2, 3, 6, 1, 5, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(7, 1, 4, 139, 14, 20, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(8, 2, 4, 23, 5, 5, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(9, 1, 5, 196, 14, 20, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(10, 2, 5, 10, 5, 5, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(11, 1, 6, 103, 10, 20, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(12, 2, 6, 16, 2, 5, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(13, 1, 7, 156, 5, 20, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(14, 2, 7, 21, 5, 5, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(15, 1, 8, 131, 15, 20, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(16, 2, 8, 7, 2, 5, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(17, 1, 9, 51, 15, 20, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(18, 2, 9, 28, 1, 5, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(19, 1, 10, 168, 6, 20, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(20, 2, 10, 11, 1, 5, '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(21, 1, 11, 191, 11, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(22, 2, 11, 16, 5, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(23, 1, 12, 56, 10, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(24, 2, 12, 22, 2, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(25, 1, 13, 79, 8, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(26, 2, 13, 21, 2, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(27, 1, 14, 129, 20, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(28, 2, 14, 9, 2, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(29, 1, 15, 101, 7, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(30, 2, 15, 12, 4, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(31, 1, 16, 142, 7, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(32, 2, 16, 6, 5, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(33, 1, 17, 195, 11, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(34, 2, 17, 22, 2, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(35, 1, 18, 104, 6, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(36, 2, 18, 24, 2, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(37, 1, 19, 126, 18, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(38, 2, 19, 16, 5, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(39, 1, 20, 163, 9, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(40, 2, 20, 22, 1, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(41, 1, 21, 185, 14, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(42, 2, 21, 9, 3, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(43, 1, 22, 94, 12, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(44, 2, 22, 27, 4, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(45, 1, 23, 122, 7, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(46, 2, 23, 28, 1, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(47, 1, 24, 108, 10, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(48, 2, 24, 14, 1, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(49, 1, 25, 97, 15, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(50, 2, 25, 8, 4, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(51, 1, 26, 126, 10, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(52, 2, 26, 14, 5, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(53, 1, 27, 191, 11, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(54, 2, 27, 21, 3, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(55, 1, 28, 69, 14, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(56, 2, 28, 12, 1, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(57, 1, 29, 54, 11, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(58, 2, 29, 5, 3, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(59, 1, 30, 171, 7, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(60, 2, 30, 25, 1, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(61, 1, 31, 165, 18, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(62, 2, 31, 9, 1, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(63, 1, 32, 105, 7, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(64, 2, 32, 19, 5, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(65, 1, 33, 175, 19, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(66, 2, 33, 8, 3, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(67, 1, 34, 84, 11, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(68, 2, 34, 30, 1, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(69, 1, 35, 168, 15, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(70, 2, 35, 26, 5, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(71, 1, 36, 51, 8, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(72, 2, 36, 16, 3, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(73, 1, 37, 82, 15, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(74, 2, 37, 25, 5, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(75, 1, 38, 187, 6, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(76, 2, 38, 28, 5, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(77, 1, 39, 136, 14, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(78, 2, 39, 9, 1, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(79, 1, 40, 108, 20, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(80, 2, 40, 20, 1, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(81, 1, 41, 118, 6, 20, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(82, 2, 41, 29, 4, 5, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(83, 1, 42, 189, 6, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(84, 2, 42, 7, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(85, 1, 43, 74, 10, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(86, 2, 43, 16, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(87, 1, 44, 175, 14, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(88, 2, 44, 11, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(89, 1, 45, 139, 5, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(90, 2, 45, 26, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(91, 1, 46, 166, 16, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(92, 2, 46, 24, 2, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(93, 1, 47, 192, 6, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(94, 2, 47, 26, 2, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(95, 1, 48, 68, 8, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(96, 2, 48, 15, 2, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(97, 1, 49, 102, 5, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(98, 2, 49, 30, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(99, 1, 50, 71, 15, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(100, 2, 50, 18, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(101, 1, 51, 120, 12, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(102, 2, 51, 14, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(103, 1, 52, 52, 18, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(104, 2, 52, 11, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(105, 1, 53, 84, 13, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(106, 2, 53, 24, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(107, 1, 54, 137, 8, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(108, 2, 54, 19, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(109, 1, 55, 104, 16, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(110, 2, 55, 12, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(111, 1, 56, 127, 9, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(112, 2, 56, 15, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(113, 1, 57, 165, 6, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(114, 2, 57, 13, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(115, 1, 58, 111, 8, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(116, 2, 58, 11, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(117, 1, 59, 73, 13, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(118, 2, 59, 16, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(119, 1, 60, 152, 18, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(120, 2, 60, 9, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(121, 1, 61, 121, 19, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(122, 2, 61, 13, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(123, 1, 62, 64, 13, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(124, 2, 62, 29, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(125, 1, 63, 155, 13, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(126, 2, 63, 23, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(127, 1, 64, 73, 11, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(128, 2, 64, 11, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(129, 1, 65, 90, 11, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(130, 2, 65, 8, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(131, 1, 66, 172, 18, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(132, 2, 66, 13, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(133, 1, 67, 81, 7, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(134, 2, 67, 22, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(135, 1, 68, 138, 20, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(136, 2, 68, 27, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(137, 1, 69, 60, 13, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(138, 2, 69, 6, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(139, 1, 70, 92, 18, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(140, 2, 70, 27, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(141, 1, 71, 142, 16, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(142, 2, 71, 9, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(143, 1, 72, 115, 6, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(144, 2, 72, 20, 2, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(145, 1, 73, 101, 12, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(146, 2, 73, 22, 2, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(147, 1, 74, 50, 18, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(148, 2, 74, 23, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(149, 1, 75, 147, 20, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(150, 2, 75, 7, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(151, 1, 76, 65, 12, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(152, 2, 76, 22, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(153, 1, 77, 152, 19, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(154, 2, 77, 27, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(155, 1, 78, 128, 11, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(156, 2, 78, 27, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(157, 1, 79, 54, 12, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(158, 2, 79, 13, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(159, 1, 80, 82, 20, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(160, 2, 80, 23, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(161, 1, 81, 75, 15, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(162, 2, 81, 16, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(163, 1, 82, 65, 7, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(164, 2, 82, 20, 2, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(165, 1, 83, 119, 6, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(166, 2, 83, 10, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(167, 1, 84, 170, 16, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(168, 2, 84, 16, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(169, 1, 85, 60, 17, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(170, 2, 85, 23, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(171, 1, 86, 158, 12, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(172, 2, 86, 14, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(173, 1, 87, 57, 18, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(174, 2, 87, 10, 2, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(175, 1, 88, 175, 7, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(176, 2, 88, 19, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(177, 1, 89, 69, 18, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(178, 2, 89, 14, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(179, 1, 90, 82, 13, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(180, 2, 90, 16, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(181, 1, 91, 174, 19, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(182, 2, 91, 14, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(183, 1, 92, 59, 19, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(184, 2, 92, 20, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(185, 1, 93, 194, 12, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(186, 2, 93, 22, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(187, 1, 94, 157, 17, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(188, 2, 94, 14, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(189, 1, 95, 141, 9, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(190, 2, 95, 25, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(191, 1, 96, 104, 5, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(192, 2, 96, 27, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(193, 1, 97, 183, 12, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(194, 2, 97, 24, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(195, 1, 98, 138, 18, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(196, 2, 98, 30, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(197, 1, 99, 170, 15, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(198, 2, 99, 5, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(199, 1, 100, 156, 8, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(200, 2, 100, 18, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(201, 1, 101, 199, 17, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(202, 2, 101, 9, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(203, 1, 102, 166, 17, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(204, 2, 102, 6, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(205, 1, 103, 172, 20, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(206, 2, 103, 27, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(207, 1, 104, 59, 8, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(208, 2, 104, 26, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(209, 1, 105, 65, 12, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(210, 2, 105, 9, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(211, 1, 106, 136, 11, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(212, 2, 106, 10, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(213, 1, 107, 193, 5, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(214, 2, 107, 9, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(215, 1, 108, 65, 13, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(216, 2, 108, 22, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(217, 1, 109, 143, 6, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(218, 2, 109, 8, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(219, 1, 110, 184, 20, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(220, 2, 110, 8, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(221, 1, 111, 66, 14, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(222, 2, 111, 30, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(223, 1, 112, 171, 18, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(224, 2, 112, 5, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(225, 1, 113, 119, 16, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(226, 2, 113, 21, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(227, 1, 114, 199, 15, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(228, 2, 114, 10, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(229, 1, 115, 81, 5, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(230, 2, 115, 16, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(231, 1, 116, 187, 18, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(232, 2, 116, 26, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(233, 1, 117, 87, 8, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(234, 2, 117, 13, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(235, 1, 118, 143, 14, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(236, 2, 118, 13, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(237, 1, 119, 68, 5, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(238, 2, 119, 13, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(239, 1, 120, 64, 7, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(240, 2, 120, 15, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(241, 1, 121, 200, 20, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(242, 2, 121, 12, 2, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(243, 1, 122, 109, 19, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(244, 2, 122, 13, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(245, 1, 123, 107, 20, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(246, 2, 123, 24, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(247, 1, 124, 174, 9, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(248, 2, 124, 15, 2, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(249, 1, 125, 125, 14, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(250, 2, 125, 19, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(251, 1, 126, 149, 18, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(252, 2, 126, 30, 3, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(253, 1, 127, 77, 6, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(254, 2, 127, 9, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(255, 1, 128, 101, 16, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(256, 2, 128, 12, 4, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(257, 1, 129, 175, 12, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(258, 2, 129, 17, 1, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(259, 1, 130, 113, 15, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(260, 2, 130, 27, 5, 5, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(261, 1, 131, 129, 8, 20, '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(262, 2, 131, 8, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(263, 1, 132, 118, 18, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(264, 2, 132, 14, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(265, 1, 133, 122, 11, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(266, 2, 133, 15, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(267, 1, 134, 123, 8, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(268, 2, 134, 19, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(269, 1, 135, 85, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(270, 2, 135, 7, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(271, 1, 136, 83, 12, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(272, 2, 136, 7, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(273, 1, 137, 89, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(274, 2, 137, 20, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(275, 1, 138, 99, 19, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(276, 2, 138, 10, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(277, 1, 139, 108, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(278, 2, 139, 23, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(279, 1, 140, 73, 15, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(280, 2, 140, 11, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(281, 1, 141, 83, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(282, 2, 141, 7, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(283, 1, 142, 93, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(284, 2, 142, 24, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(285, 1, 143, 138, 11, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(286, 2, 143, 16, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(287, 1, 144, 109, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(288, 2, 144, 15, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(289, 1, 145, 101, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(290, 2, 145, 14, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(291, 1, 146, 161, 7, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(292, 2, 146, 5, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(293, 1, 147, 185, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(294, 2, 147, 22, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(295, 1, 148, 102, 5, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(296, 2, 148, 20, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(297, 1, 149, 144, 12, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(298, 2, 149, 10, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(299, 1, 150, 102, 18, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(300, 2, 150, 19, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(301, 1, 151, 57, 10, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(302, 2, 151, 26, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(303, 1, 152, 136, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(304, 2, 152, 14, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(305, 1, 153, 168, 19, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(306, 2, 153, 29, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(307, 1, 154, 165, 12, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(308, 2, 154, 17, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(309, 1, 155, 79, 10, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(310, 2, 155, 27, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(311, 1, 156, 82, 19, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(312, 2, 156, 28, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(313, 1, 157, 53, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(314, 2, 157, 21, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(315, 1, 158, 157, 19, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(316, 2, 158, 6, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(317, 1, 159, 102, 10, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(318, 2, 159, 21, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(319, 1, 160, 63, 12, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(320, 2, 160, 23, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(321, 1, 161, 106, 14, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(322, 2, 161, 11, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(323, 1, 162, 96, 14, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(324, 2, 162, 8, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(325, 1, 163, 109, 19, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(326, 2, 163, 5, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(327, 1, 164, 184, 19, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(328, 2, 164, 11, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(329, 1, 165, 128, 12, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(330, 2, 165, 22, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(331, 1, 166, 120, 16, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(332, 2, 166, 7, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(333, 1, 167, 196, 18, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(334, 2, 167, 6, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(335, 1, 168, 83, 8, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(336, 2, 168, 11, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(337, 1, 169, 175, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(338, 2, 169, 19, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(339, 1, 170, 105, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(340, 2, 170, 7, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(341, 1, 171, 82, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(342, 2, 171, 12, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(343, 1, 172, 117, 12, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(344, 2, 172, 23, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(345, 1, 173, 106, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(346, 2, 173, 22, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(347, 1, 174, 187, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(348, 2, 174, 10, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(349, 1, 175, 105, 8, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(350, 2, 175, 8, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(351, 1, 176, 62, 8, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(352, 2, 176, 11, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(353, 1, 177, 142, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(354, 2, 177, 12, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(355, 1, 178, 111, 18, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(356, 2, 178, 16, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(357, 1, 179, 50, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(358, 2, 179, 12, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(359, 1, 180, 154, 19, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(360, 2, 180, 10, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(361, 1, 181, 93, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(362, 2, 181, 18, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(363, 1, 182, 114, 15, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(364, 2, 182, 20, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(365, 1, 183, 102, 12, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(366, 2, 183, 10, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(367, 1, 184, 171, 5, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(368, 2, 184, 8, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(369, 1, 185, 123, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(370, 2, 185, 30, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(371, 1, 186, 183, 15, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(372, 2, 186, 5, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(373, 1, 187, 92, 7, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(374, 2, 187, 21, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(375, 1, 188, 116, 11, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(376, 2, 188, 14, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(377, 1, 189, 88, 14, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(378, 2, 189, 21, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(379, 1, 190, 122, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(380, 2, 190, 6, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(381, 1, 191, 67, 11, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(382, 2, 191, 29, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(383, 1, 192, 67, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(384, 2, 192, 6, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(385, 1, 193, 100, 14, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(386, 2, 193, 27, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(387, 1, 194, 64, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(388, 2, 194, 30, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(389, 1, 195, 157, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(390, 2, 195, 23, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(391, 1, 196, 184, 18, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(392, 2, 196, 5, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(393, 1, 197, 184, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(394, 2, 197, 24, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(395, 1, 198, 70, 10, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(396, 2, 198, 13, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(397, 1, 199, 172, 19, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(398, 2, 199, 6, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(399, 1, 200, 82, 5, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(400, 2, 200, 25, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(401, 1, 201, 78, 10, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(402, 2, 201, 11, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(403, 1, 202, 161, 16, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(404, 2, 202, 15, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(405, 1, 203, 132, 10, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(406, 2, 203, 9, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(407, 1, 204, 94, 11, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(408, 2, 204, 18, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(409, 1, 205, 162, 15, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(410, 2, 205, 22, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(411, 1, 206, 128, 12, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(412, 2, 206, 15, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(413, 1, 207, 67, 7, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(414, 2, 207, 25, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(415, 1, 208, 86, 8, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(416, 2, 208, 30, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(417, 1, 209, 102, 10, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(418, 2, 209, 28, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(419, 1, 210, 131, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(420, 2, 210, 21, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(421, 1, 211, 178, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(422, 2, 211, 12, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(423, 1, 212, 72, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(424, 2, 212, 18, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(425, 1, 213, 194, 18, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(426, 2, 213, 29, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(427, 1, 214, 109, 19, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(428, 2, 214, 23, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(429, 1, 215, 72, 15, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(430, 2, 215, 30, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(431, 1, 216, 138, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(432, 2, 216, 15, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(433, 1, 217, 125, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(434, 2, 217, 13, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(435, 1, 218, 171, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(436, 2, 218, 28, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(437, 1, 219, 177, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(438, 2, 219, 11, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(439, 1, 220, 116, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(440, 2, 220, 13, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(441, 1, 221, 153, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(442, 2, 221, 27, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(443, 1, 222, 52, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(444, 2, 222, 5, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(445, 1, 223, 138, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(446, 2, 223, 8, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(447, 1, 224, 112, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(448, 2, 224, 21, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(449, 1, 225, 165, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(450, 2, 225, 6, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(451, 1, 226, 163, 11, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(452, 2, 226, 22, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(453, 1, 227, 130, 18, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(454, 2, 227, 23, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(455, 1, 228, 54, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(456, 2, 228, 15, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(457, 1, 229, 105, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(458, 2, 229, 7, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(459, 1, 230, 112, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(460, 2, 230, 14, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(461, 1, 231, 75, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(462, 2, 231, 24, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(463, 1, 232, 128, 19, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(464, 2, 232, 9, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(465, 1, 233, 113, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(466, 2, 233, 11, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(467, 1, 234, 55, 11, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(468, 2, 234, 10, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(469, 1, 235, 149, 15, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(470, 2, 235, 9, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(471, 1, 236, 114, 14, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(472, 2, 236, 16, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(473, 1, 237, 127, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(474, 2, 237, 21, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(475, 1, 238, 55, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(476, 2, 238, 25, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(477, 1, 239, 168, 7, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(478, 2, 239, 28, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(479, 1, 240, 50, 15, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(480, 2, 240, 7, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(481, 1, 241, 180, 18, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(482, 2, 241, 19, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(483, 1, 242, 88, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(484, 2, 242, 20, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(485, 1, 243, 104, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(486, 2, 243, 18, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(487, 1, 244, 128, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(488, 2, 244, 14, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(489, 1, 245, 188, 5, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(490, 2, 245, 21, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(491, 1, 246, 69, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(492, 2, 246, 24, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(493, 1, 247, 73, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(494, 2, 247, 5, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(495, 1, 248, 146, 16, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(496, 2, 248, 13, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(497, 1, 249, 170, 5, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(498, 2, 249, 9, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(499, 1, 250, 148, 8, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(500, 2, 250, 9, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(501, 1, 251, 121, 10, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(502, 2, 251, 10, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(503, 1, 252, 161, 8, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(504, 2, 252, 14, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(505, 1, 253, 130, 12, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(506, 2, 253, 28, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(507, 1, 254, 150, 16, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(508, 2, 254, 20, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(509, 1, 255, 62, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(510, 2, 255, 5, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(511, 1, 256, 95, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(512, 2, 256, 5, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(513, 1, 257, 122, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(514, 2, 257, 22, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(515, 1, 258, 144, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(516, 2, 258, 8, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(517, 1, 259, 55, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(518, 2, 259, 30, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(519, 1, 260, 99, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(520, 2, 260, 9, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(521, 1, 261, 69, 14, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(522, 2, 261, 26, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(523, 1, 262, 137, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(524, 2, 262, 13, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(525, 1, 263, 73, 15, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(526, 2, 263, 26, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(527, 1, 264, 138, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(528, 2, 264, 14, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(529, 1, 265, 192, 10, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(530, 2, 265, 13, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(531, 1, 266, 74, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(532, 2, 266, 5, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(533, 1, 267, 94, 14, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(534, 2, 267, 6, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(535, 1, 268, 72, 16, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(536, 2, 268, 9, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(537, 1, 269, 124, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(538, 2, 269, 26, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(539, 1, 270, 150, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(540, 2, 270, 30, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(541, 1, 271, 113, 15, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(542, 2, 271, 17, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(543, 1, 272, 174, 5, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(544, 2, 272, 28, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(545, 1, 273, 60, 8, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(546, 2, 273, 21, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(547, 1, 274, 200, 10, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(548, 2, 274, 10, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(549, 1, 275, 165, 11, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(550, 2, 275, 30, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(551, 1, 276, 53, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(552, 2, 276, 26, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(553, 1, 277, 190, 15, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(554, 2, 277, 9, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(555, 1, 278, 126, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(556, 2, 278, 10, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(557, 1, 279, 176, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(558, 2, 279, 21, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(559, 1, 280, 160, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(560, 2, 280, 11, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(561, 1, 281, 116, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(562, 2, 281, 17, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(563, 1, 282, 54, 14, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(564, 2, 282, 14, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(565, 1, 283, 180, 15, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(566, 2, 283, 15, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(567, 1, 284, 80, 16, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(568, 2, 284, 28, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(569, 1, 285, 149, 11, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(570, 2, 285, 6, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(571, 1, 286, 105, 18, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(572, 2, 286, 17, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(573, 1, 287, 125, 14, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(574, 2, 287, 10, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(575, 1, 288, 67, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(576, 2, 288, 21, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(577, 1, 289, 80, 15, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(578, 2, 289, 28, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(579, 1, 290, 171, 14, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(580, 2, 290, 26, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(581, 1, 291, 196, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(582, 2, 291, 8, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(583, 1, 292, 197, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(584, 2, 292, 29, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(585, 1, 293, 150, 7, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(586, 2, 293, 6, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(587, 1, 294, 73, 8, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(588, 2, 294, 15, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(589, 1, 295, 89, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(590, 2, 295, 30, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(591, 1, 296, 99, 17, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(592, 2, 296, 15, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(593, 1, 297, 178, 12, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(594, 2, 297, 15, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(595, 1, 298, 192, 19, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(596, 2, 298, 11, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(597, 1, 299, 160, 5, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(598, 2, 299, 14, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(599, 1, 300, 119, 14, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(600, 2, 300, 17, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(601, 1, 301, 105, 18, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(602, 2, 301, 5, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(603, 1, 302, 89, 18, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(604, 2, 302, 14, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(605, 1, 303, 128, 11, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(606, 2, 303, 21, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(607, 1, 304, 142, 8, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(608, 2, 304, 25, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(609, 1, 305, 191, 12, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(610, 2, 305, 20, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(611, 1, 306, 192, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(612, 2, 306, 13, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(613, 1, 307, 59, 11, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(614, 2, 307, 11, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(615, 1, 308, 109, 10, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(616, 2, 308, 11, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(617, 1, 309, 154, 18, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(618, 2, 309, 29, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(619, 1, 310, 140, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(620, 2, 310, 25, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(621, 1, 311, 139, 7, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(622, 2, 311, 18, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(623, 1, 312, 163, 18, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(624, 2, 312, 29, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(625, 1, 313, 163, 14, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(626, 2, 313, 5, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(627, 1, 314, 80, 20, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(628, 2, 314, 25, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(629, 1, 315, 134, 7, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(630, 2, 315, 9, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(631, 1, 316, 65, 16, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(632, 2, 316, 29, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(633, 1, 317, 116, 8, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(634, 2, 317, 24, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(635, 1, 318, 171, 12, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(636, 2, 318, 20, 5, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(637, 1, 319, 130, 16, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(638, 2, 319, 23, 3, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(639, 1, 320, 90, 6, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(640, 2, 320, 16, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(641, 1, 321, 115, 8, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(642, 2, 321, 21, 2, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(643, 1, 322, 125, 12, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(644, 2, 322, 14, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(645, 1, 323, 122, 16, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(646, 2, 323, 18, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(647, 1, 324, 132, 9, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(648, 2, 324, 10, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(649, 1, 325, 72, 14, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(650, 2, 325, 21, 4, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(651, 1, 326, 66, 13, 20, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(652, 2, 326, 13, 1, 5, '2026-06-26 06:05:45', '2026-06-26 06:05:45', NULL),
(653, 1, 327, 150, 11, 20, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(654, 2, 327, 11, 1, 5, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(655, 1, 328, 160, 12, 20, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(656, 2, 328, 7, 5, 5, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(657, 1, 329, 198, 5, 20, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(658, 2, 329, 11, 4, 5, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(659, 1, 330, 103, 9, 20, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL);
INSERT INTO `inventory_stock` (`id`, `location_id`, `variant_id`, `quantity_on_hand`, `quantity_reserved`, `reorder_point`, `created_at`, `updated_at`, `deleted_at`) VALUES
(660, 2, 330, 12, 2, 5, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(661, 1, 331, 139, 14, 20, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(662, 2, 331, 21, 1, 5, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(663, 1, 332, 138, 20, 20, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(664, 2, 332, 30, 3, 5, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(665, 1, 333, 144, 19, 20, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(666, 2, 333, 25, 1, 5, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(667, 1, 334, 117, 6, 20, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(668, 2, 334, 12, 1, 5, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(669, 1, 335, 133, 18, 20, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(670, 2, 335, 18, 3, 5, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2023_12_31_000000_create_stores_table', 1),
(5, '2023_12_31_000001_create_brands_table', 1),
(6, '2023_12_31_000002_create_countries_table', 1),
(7, '2023_12_31_000003_create_addresses_table', 1),
(8, '2023_12_31_000004_create_products_table', 1),
(9, '2023_12_31_000005_create_product_variants_table', 1),
(10, '2024_01_01_000001_create_coupons_table', 1),
(11, '2024_01_01_000001_create_delivery_zones_table', 1),
(12, '2024_01_01_000001_create_inventory_locations_table', 1),
(13, '2024_01_01_000001_create_orders_table', 1),
(14, '2024_01_01_000001_create_pos_registers_table', 1),
(15, '2024_01_01_000001_create_product_reviews_table', 1),
(16, '2024_01_01_000001_create_users_table', 1),
(17, '2024_01_01_000002_create_carts_table', 1),
(18, '2024_01_01_000002_create_delivery_drivers_table', 1),
(19, '2024_01_01_000002_create_inventory_stock_table', 1),
(20, '2024_01_01_000002_create_notifications_table', 1),
(21, '2024_01_01_000002_create_order_items_table', 1),
(22, '2024_01_01_000002_create_pos_shifts_table', 1),
(23, '2024_01_01_000002_create_roles_table', 1),
(24, '2024_01_01_000002_create_store_staff_table', 1),
(25, '2024_01_01_000003_create_audit_logs_table', 1),
(26, '2024_01_01_000003_create_inventory_movements_table', 1),
(27, '2024_01_01_000003_create_payments_table', 1),
(28, '2024_01_01_000003_create_permissions_table', 1),
(29, '2024_01_01_000003_create_pos_sales_table', 1),
(30, '2024_01_01_000003_create_shipments_table', 1),
(31, '2024_01_01_000004_create_pos_sale_items_table', 1),
(32, '2024_01_01_000004_create_refunds_table', 1),
(33, '2024_01_01_000004_create_role_permissions_table', 1),
(34, '2024_01_01_000004_create_shipment_events_table', 1),
(35, '2024_01_01_000004_create_suppliers_table', 1),
(36, '2024_01_01_000004_create_webhooks_table', 1),
(37, '2024_01_01_000005_create_app_settings_table', 1),
(38, '2024_01_01_000005_create_purchase_orders_table', 1),
(39, '2024_01_01_000005_create_user_roles_table', 1),
(40, '2024_01_01_000005_create_webhook_deliveries_table', 1),
(41, '2024_01_01_000006_create_purchase_order_items_table', 1),
(42, '2024_01_01_000006_create_user_sessions_table', 1),
(43, '2024_01_01_000007_create_product_supplier_table', 1),
(44, '2024_01_01_000008_create_purchase_returns_table', 1),
(45, '2026_06_08_000002_create_categories_table', 1),
(46, '2026_06_08_000005_create_product_images_table', 1),
(47, '2026_06_08_000006_add_is_main_to_product_images_table', 1),
(48, '2026_06_08_000006_create_cart_items_table', 1),
(49, '2026_06_08_000006_create_product_categories_table', 1),
(50, '2026_06_08_000007_create_units_table', 1),
(51, '2026_06_08_000007_create_wishlists_table', 1),
(52, '2026_06_08_000008_create_sizes_table', 1),
(53, '2026_06_08_000010_add_unit_and_size_to_products_table', 1),
(54, '2026_06_08_000011_create_tax_rates_table', 1),
(55, '2026_06_08_000012_add_tax_rate_id_to_products_table', 1),
(56, '2026_06_15_000001_create_navbar_items_table', 1),
(57, '2026_06_15_000002_create_subnavbar_items_table', 1),
(58, '2026_06_15_000003_create_banners_table', 1),
(59, '2026_06_17_000001_add_image_to_categories_table', 1),
(60, '2026_06_17_000001_create_homepage_ctas_table', 1),
(61, '2026_06_18_000001_add_category_id_to_products_table', 1),
(62, '2026_06_20_200601_create_personal_access_tokens_table', 1),
(63, '2026_06_26_090541_add_navbar_fields_to_products_table', 2),
(64, '2026_06_26_100000_create_product_requests_table', 3),
(65, '2026_06_26_110000_create_variant_options_table', 3),
(66, '2026_06_27_000001_add_variant_option_id_to_cart_items_table', 4),
(67, '2026_06_27_000001_create_deliveries_table', 5),
(68, '2026_06_27_000002_update_cart_items_unique_constraint', 5),
(69, '2026_06_28_000001_create_announcement_bars_table', 6),
(70, '2026_06_29_000001_create_settings_table', 7),
(71, '2026_07_01_000001_add_button_colors_to_banners_table', 8),
(72, '2026_07_16_000001_add_cta_style_and_banner_to_homepage_ctas', 9),
(73, '2026_07_16_000002_add_position_and_margin_to_homepage_ctas', 10),
(74, '2026_07_17_000001_add_is_homepage_to_products_table', 11);

-- --------------------------------------------------------

--
-- Table structure for table `navbar_items`
--

CREATE TABLE `navbar_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `url` varchar(500) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `navbar_items`
--

INSERT INTO `navbar_items` (`id`, `name`, `slug`, `url`, `icon`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Home', 'home', '/', 'fa-solid fa-home', 1, 'active', '2026-06-25 06:17:42', '2026-06-25 06:17:42', NULL),
(2, 'Shop', 'shop', '/shop', 'fa-solid fa-store', 2, 'active', '2026-06-25 06:17:42', '2026-06-25 06:17:42', NULL),
(3, 'Collections', 'collections', '/collections', 'fa-solid fa-tags', 3, 'active', '2026-06-25 06:17:42', '2026-06-25 06:17:42', NULL),
(4, 'Products', 'products', '/products', 'fa-solid fa-box', 4, 'active', '2026-06-25 06:17:42', '2026-06-25 06:17:42', NULL),
(5, 'Deals', 'deals', '/deals', 'fa-solid fa-gift', 5, 'active', '2026-06-25 06:17:42', '2026-06-25 06:17:42', NULL),
(10, 'Boys', 'boys', '/boys', 'fa-solid fa-person', 2, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(11, 'Women', 'women', '/women', 'fa-solid fa-person-dress', 3, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  `channel` varchar(50) NOT NULL DEFAULT 'in_app',
  `subject` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `read_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(40) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `source` enum('web','mobile','pos','admin','marketplace') NOT NULL DEFAULT 'web',
  `status` enum('pending','confirmed','processing','ready','completed','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','authorized','paid','partially_refunded','refunded','failed') NOT NULL DEFAULT 'unpaid',
  `fulfillment_status` enum('unfulfilled','partial','fulfilled','returned') NOT NULL DEFAULT 'unfulfilled',
  `currency_code` char(3) NOT NULL DEFAULT 'USD',
  `subtotal` decimal(19,4) NOT NULL DEFAULT 0.0000,
  `discount_total` decimal(19,4) NOT NULL DEFAULT 0.0000,
  `tax_total` decimal(19,4) NOT NULL DEFAULT 0.0000,
  `shipping_total` decimal(19,4) NOT NULL DEFAULT 0.0000,
  `grand_total` decimal(19,4) NOT NULL DEFAULT 0.0000,
  `coupon_id` bigint(20) UNSIGNED DEFAULT NULL,
  `billing_address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `shipping_address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_note` varchar(1000) DEFAULT NULL,
  `placed_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `store_id`, `source`, `status`, `payment_status`, `fulfillment_status`, `currency_code`, `subtotal`, `discount_total`, `tax_total`, `shipping_total`, `grand_total`, `coupon_id`, `billing_address_id`, `shipping_address_id`, `customer_note`, `placed_at`, `cancelled_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'ORD-6A3D1C68C3D15', 1, 1, 'web', 'pending', 'unpaid', 'unfulfilled', 'USD', 100.0000, 0.0000, 10.0000, 5.0000, 115.0000, NULL, NULL, NULL, 'Sample order 1', '2026-06-25 12:17:44', NULL, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(2, 'ORD-6A3D1C68C3D86', 1, 1, 'web', 'pending', 'unpaid', 'unfulfilled', 'USD', 200.0000, 0.0000, 20.0000, 5.0000, 230.0000, NULL, NULL, NULL, 'Sample order 2', '2026-06-25 12:17:44', NULL, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(3, 'ORD-6A3D1C68C3DC4', 1, 1, 'web', 'pending', 'unpaid', 'unfulfilled', 'USD', 300.0000, 0.0000, 30.0000, 5.0000, 345.0000, NULL, NULL, NULL, 'Sample order 3', '2026-06-25 12:17:44', NULL, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(4, 'ORD-6A3D1C68C3E04', 1, 1, 'web', 'pending', 'unpaid', 'unfulfilled', 'USD', 400.0000, 0.0000, 40.0000, 5.0000, 460.0000, NULL, NULL, NULL, 'Sample order 4', '2026-06-25 12:17:44', NULL, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(5, 'ORD-6A3D1C68C3E43', 1, 1, 'web', 'pending', 'unpaid', 'unfulfilled', 'USD', 500.0000, 0.0000, 50.0000, 5.0000, 575.0000, NULL, NULL, NULL, 'Sample order 5', '2026-06-25 12:17:44', NULL, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(6, 'ORD-6A3E6B1A29935', 1, 1, 'web', 'pending', 'unpaid', 'unfulfilled', 'USD', 100.0000, 0.0000, 10.0000, 5.0000, 115.0000, NULL, NULL, NULL, 'Sample order 1', '2026-06-26 12:05:46', NULL, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(7, 'ORD-6A3E6B1A29973', 1, 1, 'web', 'pending', 'unpaid', 'unfulfilled', 'USD', 200.0000, 0.0000, 20.0000, 5.0000, 230.0000, NULL, NULL, NULL, 'Sample order 2', '2026-06-26 12:05:46', NULL, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(8, 'ORD-6A3E6B1A29990', 1, 1, 'web', 'pending', 'unpaid', 'unfulfilled', 'USD', 300.0000, 0.0000, 30.0000, 5.0000, 345.0000, NULL, NULL, NULL, 'Sample order 3', '2026-06-26 12:05:46', NULL, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(9, 'ORD-6A3E6B1A299AC', 1, 1, 'web', 'pending', 'unpaid', 'unfulfilled', 'USD', 400.0000, 0.0000, 40.0000, 5.0000, 460.0000, NULL, NULL, NULL, 'Sample order 4', '2026-06-26 12:05:46', NULL, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(10, 'ORD-6A3E6B1A299C8', 1, 1, 'web', 'pending', 'unpaid', 'unfulfilled', 'USD', 500.0000, 0.0000, 50.0000, 5.0000, 575.0000, NULL, NULL, NULL, 'Sample order 5', '2026-06-26 12:05:46', NULL, '2026-06-26 06:05:46', '2026-06-26 06:05:46', NULL),
(11, 'ORD-6A3ED2A7462E8', 5, NULL, 'web', 'pending', 'unpaid', 'unfulfilled', 'BDT', 2449.0000, 0.0000, 0.0000, 0.0000, 2449.0000, NULL, NULL, NULL, NULL, '2026-06-26 19:27:35', NULL, '2026-06-26 13:27:35', '2026-06-26 13:27:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sku` varchar(100) NOT NULL,
  `product_name` varchar(220) NOT NULL,
  `variant_name` varchar(220) DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `unit_price` decimal(19,4) NOT NULL,
  `discount_total` decimal(19,4) NOT NULL DEFAULT 0.0000,
  `tax_total` decimal(19,4) NOT NULL DEFAULT 0.0000,
  `line_total` decimal(19,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `sku`, `product_name`, `variant_name`, `quantity`, `unit_price`, `discount_total`, `tax_total`, `line_total`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 11, 86, 283, 'BLUE-SKINNY-JEANS-WOMEN-1', 'Blue Skinny Jeans Women', '26 - Blue', 1, 2449.0000, 0.0000, 0.0000, 2449.0000, '2026-06-26 13:27:35', '2026-06-26 13:27:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `provider` varchar(80) NOT NULL,
  `provider_payment_id` varchar(180) DEFAULT NULL,
  `method` enum('card','cash','bank_transfer','wallet','cod','gift_card','other') NOT NULL,
  `status` enum('pending','authorized','captured','failed','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `amount` decimal(19,4) NOT NULL,
  `currency_code` char(3) NOT NULL DEFAULT 'USD',
  `paid_at` datetime DEFAULT NULL,
  `raw_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_response`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'users.view', 'View users', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 'users.create', 'Create users', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 'users.edit', 'Edit users', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(4, 'users.delete', 'Delete users', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(5, 'roles.view', 'View roles', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(6, 'roles.create', 'Create roles', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(7, 'roles.edit', 'Edit roles', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(8, 'roles.delete', 'Delete roles', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(9, 'permissions.view', 'View permissions', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(10, 'permissions.create', 'Create permissions', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(11, 'permissions.edit', 'Edit permissions', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(12, 'permissions.delete', 'Delete permissions', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(13, 'products.view', 'View products', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(14, 'products.create', 'Create products', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(15, 'products.edit', 'Edit products', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(16, 'products.delete', 'Delete products', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(17, 'categories.view', 'View categories', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(18, 'categories.create', 'Create categories', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(19, 'categories.edit', 'Edit categories', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(20, 'categories.delete', 'Delete categories', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(21, 'brands.view', 'View brands', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(22, 'brands.create', 'Create brands', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(23, 'brands.edit', 'Edit brands', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(24, 'brands.delete', 'Delete brands', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(25, 'inventory.view', 'View inventory', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(26, 'inventory.create', 'Create inventory', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(27, 'inventory.edit', 'Edit inventory', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(28, 'inventory.delete', 'Delete inventory', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(29, 'orders.view', 'View orders', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(30, 'orders.create', 'Create orders', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(31, 'orders.edit', 'Edit orders', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(32, 'orders.delete', 'Delete orders', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(33, 'stores.view', 'View stores', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(34, 'stores.edit', 'Edit stores', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(35, 'settings.view', 'View settings', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(36, 'settings.edit', 'Edit settings', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
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
(2, 'Modules\\Identity\\Models\\User', 5, 'auth_token', '6a4292710252ffc6add8278025b1644f67501ba638c5251ed691bc3ab54b7bdf', '[\"*\"]', '2026-06-29 08:55:48', NULL, '2026-06-26 12:10:24', '2026-06-29 08:55:48');

-- --------------------------------------------------------

--
-- Table structure for table `pos_registers`
--

CREATE TABLE `pos_registers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('counter','mobile','kiosk') NOT NULL DEFAULT 'counter',
  `status` enum('active','inactive','offline') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pos_registers`
--

INSERT INTO `pos_registers` (`id`, `store_id`, `name`, `code`, `type`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Main Counter - Gulshan', 'REG-001', 'counter', 'active', '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(2, 1, 'Mobile POS - Mirpur', 'REG-002', 'mobile', 'active', '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(3, 1, 'Kiosk - Uttara', 'REG-003', 'kiosk', 'active', '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pos_sales`
--

CREATE TABLE `pos_sales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `register_id` bigint(20) UNSIGNED NOT NULL,
  `shift_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cash_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `card_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `other_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `change_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('paid','partial','pending','refunded') NOT NULL DEFAULT 'paid',
  `status` enum('completed','voided','refunded') NOT NULL DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pos_sales`
--

INSERT INTO `pos_sales` (`id`, `register_id`, `shift_id`, `order_id`, `user_id`, `receipt_number`, `subtotal`, `tax_amount`, `discount_amount`, `total`, `cash_amount`, `card_amount`, `other_amount`, `change_amount`, `payment_status`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, NULL, 1, 'POS-SEED-001', 1500.00, 180.00, 50.00, 1630.00, 1630.00, 0.00, 0.00, 0.00, 'paid', 'completed', 'Seeded POS sale', '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pos_sale_items`
--

CREATE TABLE `pos_sale_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pos_sale_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(220) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `quantity` decimal(15,2) NOT NULL DEFAULT 1.00,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pos_shifts`
--

CREATE TABLE `pos_shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `register_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `opened_at` datetime NOT NULL,
  `closed_at` datetime DEFAULT NULL,
  `opening_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `closing_balance` decimal(15,2) DEFAULT NULL,
  `expected_balance` decimal(15,2) DEFAULT NULL,
  `cash_sales` decimal(15,2) NOT NULL DEFAULT 0.00,
  `card_sales` decimal(15,2) NOT NULL DEFAULT 0.00,
  `other_sales` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_sales` decimal(15,2) NOT NULL DEFAULT 0.00,
  `declared_cash` decimal(15,2) DEFAULT NULL,
  `discrepancy` decimal(15,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('open','closed','reconciled') NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pos_shifts`
--

INSERT INTO `pos_shifts` (`id`, `register_id`, `user_id`, `opened_at`, `closed_at`, `opening_balance`, `closing_balance`, `expected_balance`, `cash_sales`, `card_sales`, `other_sales`, `total_sales`, `declared_cash`, `discrepancy`, `notes`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, '2026-06-25 04:17:44', NULL, 5000.00, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, 'Morning shift - seeded data', 'open', '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `brand_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `navbar_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subnavbar_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `size_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tax_rate_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(220) NOT NULL,
  `slug` varchar(240) NOT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `product_type` enum('physical','digital','service','bundle') NOT NULL DEFAULT 'physical',
  `status` enum('draft','active','archived') NOT NULL DEFAULT 'draft',
  `visibility` enum('public','hidden','private') NOT NULL DEFAULT 'public',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` varchar(500) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `is_homepage` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `brand_id`, `category_id`, `navbar_item_id`, `subnavbar_item_id`, `unit_id`, `size_id`, `tax_rate_id`, `name`, `slug`, `short_description`, `description`, `product_type`, `status`, `visibility`, `seo_title`, `seo_description`, `published_at`, `is_homepage`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 1, NULL, NULL, NULL, NULL, NULL, 'iPhone 15 Pro Max', 'iphone-15-pro-max', 'dafasdfasdf', '<p>The most powerful iPhone ever. A17 Pro chip, 48MP camera system, titanium design.</p>', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:00', 1, '2026-06-25 06:17:40', '2026-07-19 22:56:38', NULL),
(2, 4, 1, NULL, NULL, NULL, NULL, NULL, 'Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24-ultra', 'Galaxy AI is here', '<p>Galaxy AI is here. Built with titanium, Galaxy S24 Ultra features a flat display.</p>', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:00', 1, '2026-06-25 06:17:40', '2026-07-24 12:05:37', NULL),
(3, 3, 1, NULL, NULL, NULL, NULL, NULL, 'MacBook Pro M3', 'macbook-pro-m3', 'Supercharged by M3 chip', '<p>Supercharged by M3 chip. Stunning Liquid Retina XDR display.</p>', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:00', 1, '2026-06-25 06:17:41', '2026-07-24 12:06:54', NULL),
(4, 1, 3, NULL, NULL, NULL, NULL, NULL, 'Nike Air Max 270', 'nike-air-max-270', 'Nike Air Max 270 Running Shoes', '<p>The Nike Air Max 270 delivers visible cushioning under the heel.</p>', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:00', 1, '2026-06-25 06:17:41', '2026-07-21 02:12:43', NULL),
(5, 1, 3, NULL, NULL, NULL, NULL, NULL, 'Nike Dri-FIT Sportswear', 'nike-dri-fit-sportswear', 'Performance Dri-FIT training top', 'Stay dry and comfortable during workouts with Nike Dri-FIT technology.', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:41', 0, '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(6, 1, 3, NULL, NULL, NULL, NULL, NULL, 'Nike Football Pro', 'nike-football-pro', 'Professional football/soccer ball', 'Official match ball with superior grip and durability for all weather conditions.', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:41', 0, '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(7, 6, 2, NULL, NULL, NULL, NULL, NULL, 'Handmade Cotton Kurta', 'handmade-cotton-kurta', 'Traditional handwoven cotton kurta', '<p>Premium quality handwoven cotton kurta, perfect for casual and festive wear.</p>', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:00', 1, '2026-06-25 06:17:41', '2026-07-21 02:17:45', NULL),
(8, 6, 2, NULL, NULL, NULL, NULL, NULL, 'Banglar Muslin Saree', 'banglar-muslin-saree', 'Traditional Bengali Muslin Saree', 'Exquisite handwoven muslin saree from Bangladesh.', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:41', 0, '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(9, 4, 4, NULL, NULL, NULL, NULL, NULL, 'Samsung 65\" 4K Smart TV', 'samsung-65-4k-smart-tv', '65-inch 4K UHD Smart TV', 'Experience stunning 4K resolution with vibrant colors and smart features.', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:41', 0, '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(10, 5, 4, NULL, NULL, NULL, NULL, NULL, 'Sony Home Theater System', 'sony-home-theater-system', '5.1ch Home Theater System', '<p>Immersive 5.1 channel surround sound system for your home entertainment.</p>', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:00', 1, '2026-06-25 06:17:41', '2026-07-21 01:21:20', NULL),
(11, 4, 4, NULL, NULL, NULL, NULL, NULL, 'Samsung Bespoke Refrigerator', 'samsung-bespoke-refrigerator', '4-Door Flex Refrigerator with AI', 'Customizable 4-door refrigerator with AI-powered cooling and smart features.', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:41', 0, '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(12, 6, 5, NULL, NULL, NULL, NULL, NULL, 'The Great Gatsby', 'the-great-gatsby', 'Classic novel by F. Scott Fitzgerald', 'A timeless story of wealth, love, and the American Dream in the Jazz Age.', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:41', 0, '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(13, 6, 5, NULL, NULL, NULL, NULL, NULL, 'To Kill a Mockingbird', 'to-kill-a-mockingbird', 'Pulitzer Prize-winning novel', 'Harper Lee\'s masterpiece about racial injustice and moral growth in the American South.', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:41', 0, '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(14, 6, 5, NULL, NULL, NULL, NULL, NULL, '1984', '1984', 'Dystopian novel by George Orwell', 'A chilling vision of a totalitarian future where Big Brother watches everything.', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:42', 0, '2026-06-25 06:17:42', '2026-06-25 06:17:42', NULL),
(15, 6, 2, 2, 3, NULL, NULL, NULL, 'Classic Cotton Tee', 'classic-tee', NULL, '<p>A versatile classic tee available in multiple colors and sizes.</p>', 'physical', 'active', 'public', NULL, NULL, '2026-06-25 06:17:00', 0, '2026-06-25 06:17:42', '2026-06-26 05:17:00', NULL),
(16, 9, NULL, NULL, 4, NULL, NULL, NULL, 'Classic White Cotton T-Shirt', 'classic-white-cotton-tshirt', 'Pure cotton white tee for men', 'Premium 100% cotton white t-shirt. Soft, breathable, and perfect for everyday wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:40', 0, '2026-06-26 06:05:40', '2026-06-26 06:31:25', NULL),
(17, 8, NULL, NULL, 4, NULL, NULL, NULL, 'Black Edition Premium Tee', 'black-edition-premium-tee', 'Sleek black t-shirt premium fit', 'Premium black t-shirt with a sleek finish. Comfortable fit for all occasions.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:40', 0, '2026-06-26 06:05:40', '2026-06-26 06:31:25', NULL),
(18, 7, NULL, NULL, 4, NULL, NULL, NULL, 'Striped Casual Blue Tee', 'striped-casual-blue-tee', 'Organic cotton striped tee', 'Navy blue striped t-shirt made from organic cotton. Stylish and eco-friendly.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:40', 0, '2026-06-26 06:05:40', '2026-06-26 06:31:25', NULL),
(19, 11, NULL, NULL, NULL, NULL, NULL, NULL, 'Graphic Printed T-Shirt', 'graphic-printed-tshirt', 'Trendy graphic print tee', 'Cool graphic printed t-shirt with modern design. Soft fabric for all-day comfort.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:40', 0, '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(20, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'Red Sports T-Shirt', 'red-sports-tshirt', 'Performance sports tee red', 'Moisture-wicking red sports t-shirt. Perfect for gym and outdoor activities.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:40', 0, '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(21, 10, NULL, NULL, NULL, NULL, NULL, NULL, 'Olive Green Oversized Tee', 'olive-green-oversized-tee', 'Oversized streetwear tee', 'Trendy oversized fit t-shirt in olive green. Relaxed streetwear style.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:40', 0, '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(22, 9, NULL, NULL, NULL, NULL, NULL, NULL, 'Pack of 3 Basic Tees', 'pack-3-basic-tees', '3-pack cotton basic tees', 'Value pack with 3 basic cotton t-shirts (White, Black, Grey). Wardrobe essentials.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:40', 0, '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(23, 8, NULL, NULL, 2, NULL, NULL, NULL, 'Navy Blue Polo T-Shirt', 'navy-blue-polo-tshirt', 'Classic polo t-shirt navy', 'Classic navy blue polo t-shirt with collar. Smart casual essential.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:40', 0, '2026-06-26 06:05:40', '2026-06-26 06:31:25', NULL),
(24, 13, NULL, NULL, 4, NULL, NULL, NULL, 'White Formal Shirt', 'white-formal-shirt', 'Classic white formal shirt', 'Crisp white formal shirt for office and formal events. Premium cotton fabric.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:40', 0, '2026-06-26 06:05:40', '2026-06-26 06:31:25', NULL),
(25, 11, NULL, NULL, 4, NULL, NULL, NULL, 'Blue Checkered Casual Shirt', 'blue-checkered-casual-shirt', 'Blue checkered casual shirt', 'Stylish blue checkered shirt perfect for casual outings and college.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:40', 0, '2026-06-26 06:05:40', '2026-06-26 06:31:25', NULL),
(26, 8, NULL, NULL, 2, NULL, NULL, NULL, 'Black Slim Fit Shirt', 'black-slim-fit-shirt', 'Slim fit black shirt', 'Slim fit black shirt. Modern cut with a sophisticated look.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:40', 0, '2026-06-26 06:05:40', '2026-06-26 06:31:25', NULL),
(27, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'Linen Summer Shirt', 'linen-summer-shirt', 'Breathable linen summer shirt', 'Breathable linen shirt perfect for summer. Lightweight and comfortable.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:40', 0, '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(28, 10, NULL, NULL, NULL, NULL, NULL, NULL, 'Pink Casual Shirt', 'pink-casual-shirt', 'Light pink shirt for men', 'Light pink casual shirt. Trendy color for modern men.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(29, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Denim Western Shirt', 'denim-western-shirt', 'Classic denim shirt', 'Classic denim shirt with western styling. Versatile for any wardrobe.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(30, 16, NULL, NULL, NULL, NULL, NULL, NULL, 'Printed Hawaiian Shirt', 'printed-hawaiian-shirt', 'Colorful Hawaiian print shirt', 'Vibrant printed Hawaiian shirt for vacation and beach vibes.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(31, 9, NULL, NULL, NULL, NULL, NULL, NULL, 'White Cotton Panjabi', 'white-cotton-panjabi', 'Pure white cotton panjabi', 'Pure white cotton panjabi. Essential for Jumuah and casual wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(32, 10, NULL, NULL, NULL, NULL, NULL, NULL, 'Royal Blue Panjabi with Button', 'royal-blue-panjabi-button', 'Royal blue designer panjabi', 'Royal blue panjabi with stylish button details. Perfect for Eid and events.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(33, 12, NULL, NULL, NULL, NULL, NULL, NULL, 'Maroon Silk Panjabi', 'maroon-silk-panjabi', 'Maroon silk embroidered panjabi', 'Rich maroon silk panjabi with golden embroidery. Premium wedding wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(34, 12, NULL, NULL, NULL, NULL, NULL, NULL, 'Black Pathor Panjabi', 'black-pathor-panjabi', 'Black stone work panjabi', 'Black panjabi with pathor (stone) work on collar. Exclusive party wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(35, 11, NULL, NULL, NULL, NULL, NULL, NULL, 'Off-White Kurta with Pocket', 'offwhite-kurta-pocket', 'Off-white cotton kurta', 'Comfortable off-white cotton kurta with chest pocket. Daily wear essential.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(36, 12, NULL, NULL, 2, NULL, NULL, NULL, 'Green Embroidered Panjabi Set', 'green-embroidered-panjabi-set', 'Green embroidered panjabi set', 'Green panjabi set with intricate embroidery. Includes panjabi and pajama.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:31:25', NULL),
(37, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'Slim Fit Blue Jeans', 'slim-fit-blue-jeans', 'Slim fit blue denim jeans', 'Classic slim fit blue jeans. Durable denim with comfortable stretch.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(38, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'Black Chino Trousers', 'black-chino-trousers', 'Black chino trousers', 'Smart black chino trousers. Perfect for office and semi-formal occasions.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(39, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'Grey Jogger Pants', 'grey-jogger-pants', 'Casual grey joggers', 'Comfortable grey jogger pants with elastic waist and cuffs. Loungewear essential.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(40, 11, NULL, NULL, NULL, NULL, NULL, NULL, 'Brown Cargo Pants', 'brown-cargo-pants', 'Multi-pocket cargo pants', 'Utility brown cargo pants with multiple pockets. Rugged and stylish.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(41, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'White Linen Trousers', 'white-linen-trousers', 'Summer linen trousers', 'Lightweight white linen trousers. Perfect for summer and formal events.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(42, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'Black Skinny Jeans', 'black-skinny-jeans', 'Black skinny denim jeans', 'Sleek black skinny jeans. Modern fit for a stylish look.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(43, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'Navy Blue Slim Fit Suit', 'navy-blue-slim-fit-suit', 'Navy blue 2-piece suit', 'Premium navy blue slim fit suit. Two-piece set with blazer and trousers.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(44, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Charcoal Grey Blazer', 'charcoal-grey-blazer', 'Charcoal grey blazer', 'Versatile charcoal grey blazer. Pairs well with jeans or trousers.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(45, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'Black Formal Suit', 'black-formal-suit', 'Classic black formal suit', 'Classic black formal suit for weddings and official events.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(46, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'Beige Linen Suit', 'beige-linen-suit', 'Summer linen suit beige', 'Summer-ready beige linen suit. Lightweight and breathable.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(47, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'Black Bomber Jacket', 'black-bomber-jacket', 'Black bomber jacket men', 'Stylish black bomber jacket. Ribbed cuffs and hem, zip closure.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(48, 11, NULL, NULL, NULL, NULL, NULL, NULL, 'Grey Hooded Sweatshirt', 'grey-hooded-sweatshirt', 'Grey pullover hoodie', 'Comfortable grey hoodie with kangaroo pocket. Casual winter essential.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(49, 9, NULL, NULL, NULL, NULL, NULL, NULL, 'Maroon Sweater', 'maroon-sweater', 'Maroon winter sweater', 'Warm maroon sweater with ribbed pattern. Perfect for winter layering.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(50, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Brown Leather Jacket', 'brown-leather-jacket', 'Brown faux leather jacket', 'Classic brown faux leather jacket. Timeless style with a rugged look.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(51, 16, NULL, NULL, NULL, NULL, NULL, NULL, 'Navy Blue Puffer Jacket', 'navy-puffer-jacket', 'Navy quilted puffer jacket', 'Warm navy blue puffer jacket with quilted design. Extreme cold protection.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(52, 9, NULL, NULL, NULL, NULL, NULL, NULL, 'Pack of 5 Cotton Boxers', 'pack-5-cotton-boxers', '5-pack cotton boxer briefs', 'Pack of 5 premium cotton boxer briefs. Assorted colors, elastic waistband.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(53, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'Pack of 6 Ankle Socks', 'pack-6-ankle-socks', '6-pair ankle socks pack', 'Comfortable ankle socks multipack. Breathable cotton blend for everyday use.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(54, 9, NULL, NULL, NULL, NULL, NULL, NULL, 'Cotton Vest (Banian) 3 Pack', 'cotton-vest-banian-3-pack', '3-pack cotton banian', 'Pure cotton vests (banian) pack of 3. Soft and comfortable undergarment.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(55, 10, NULL, NULL, NULL, NULL, NULL, NULL, 'White Pajama with Lungi', 'white-pajama-lungi', 'Cotton pajama lungi set', 'Traditional white cotton pajama and lungi set. Comfortable home wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(56, 10, NULL, NULL, NULL, NULL, NULL, NULL, 'Red Gamcha Towel Set', 'red-gamcha-towel-set', 'Traditional gamcha 3-pack', 'Traditional red gamcha towels set of 3. Bengali cultural essential.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(57, 10, NULL, NULL, NULL, NULL, NULL, NULL, 'White Tupi (Prayer Cap)', 'white-tupi-prayer-cap', 'Embroidered prayer cap', 'White embroidered prayer cap (tupi/topi). Cotton fabric for daily use.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(58, 12, NULL, NULL, NULL, NULL, NULL, NULL, 'Red Bridal Silk Saree', 'red-bridal-silk-saree', 'Red silk bridal saree', 'Stunning red bridal silk saree with golden zari work. Perfect for weddings.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(59, 14, NULL, NULL, NULL, NULL, NULL, NULL, 'Blue Tangail Cotton Saree', 'blue-tangail-cotton-saree', 'Tangail cotton saree blue', 'Authentic Tangail cotton saree in blue with traditional border designs.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(60, 14, NULL, NULL, NULL, NULL, NULL, NULL, 'Green Georgette Saree', 'green-georgette-saree', 'Green georgette saree', 'Elegant green georgette saree with floral embroidery. Party wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(61, 9, NULL, NULL, NULL, NULL, NULL, NULL, 'White Khadi Saree with Red Border', 'white-khadi-saree-red-border', 'White khadi red border saree', 'Classic white khadi saree with red border. Bengali cultural icon.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(62, 12, NULL, NULL, NULL, NULL, NULL, NULL, 'Pink Organza Saree', 'pink-organza-saree', 'Pink organza embroidered saree', 'Beautiful pink organza saree with delicate embroidery. Lightweight and elegant.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(63, 12, NULL, NULL, NULL, NULL, NULL, NULL, 'Purple Silk Saree', 'purple-silk-saree', 'Purple silk reception saree', 'Rich purple silk saree with resham work. Perfect for receptions.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:41', 0, '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(64, 9, NULL, NULL, NULL, NULL, NULL, NULL, 'Yellow Cotton Saree', 'yellow-cotton-saree', 'Yellow cotton casual saree', 'Bright yellow cotton saree. Comfortable for daily wear and casual events.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(65, 12, NULL, NULL, NULL, NULL, NULL, NULL, 'Blue Anarkali Salwar', 'blue-anarkali-salwar', 'Blue Anarkali suit', 'Royal blue Anarkali salwar kameez with embroidered neckline. Party wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(66, 10, NULL, NULL, NULL, NULL, NULL, NULL, 'Green Printed Salwar Kameez', 'green-printed-salwar', 'Green cotton printed salwar', 'Green printed cotton salwar kameez. Comfortable and stylish for daily wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(67, 12, NULL, NULL, NULL, NULL, NULL, NULL, 'Black Embroidered Salwar', 'black-embroidered-salwar', 'Black embroidered suit', 'Black salwar kameez with intricate embroidery. Elegant evening wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(68, 10, NULL, NULL, NULL, NULL, NULL, NULL, 'Orange Cotton Salwar', 'orange-cotton-salwar', 'Orange cotton suit', 'Bright orange cotton salwar kameez. Perfect for festive occasions.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(69, 14, NULL, NULL, NULL, NULL, NULL, NULL, 'White Pakistani Suit', 'white-pakistani-suit', 'White Pakistani suit', 'White Pakistani style salwar kameez with lace details. Elegant and modest.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(70, 16, NULL, NULL, NULL, NULL, NULL, NULL, 'Mint Green Anarkali', 'mint-green-anarkali', 'Mint green floral Anarkali', 'Mint green Anarkali suit with delicate floral pattern. Summer special.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(71, 11, NULL, NULL, 4, NULL, NULL, NULL, 'White Cotton Kurti with Embroidery', 'white-cotton-kurti-embroidered', 'White embroidered kurti', 'White cotton kurti with neck embroidery. Versatile for daily wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:31:25', NULL),
(72, 10, NULL, NULL, NULL, NULL, NULL, NULL, 'Maroon Rayon Kurti', 'maroon-rayon-kurti', 'Maroon printed kurti', 'Maroon rayon kurti with printed design. Flowing and comfortable fit.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(73, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Blue Denim Kurti', 'blue-denim-kurti', 'Denim style kurti', 'Trendy blue denim kurti with button placket. Modern fusion wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(74, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'Black Long Kurti with Side Slits', 'black-long-kurti-side-slits', 'Black long kurti', 'Black long kurti with side slits. Pairs well with leggings or jeans.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(75, 11, NULL, NULL, NULL, NULL, NULL, NULL, 'Pink Cotton A-Line Kurti', 'pink-cotton-aline-kurti', 'Pink printed A-line kurti', 'Pink A-line cotton kurti with colorful print. Casual and cheerful.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(76, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'Red Bodycon Dress', 'red-bodycon-dress', 'Red bodycon party dress', 'Stunning red bodycon dress. Perfect for parties and special occasions.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(77, 16, NULL, NULL, NULL, NULL, NULL, NULL, 'Black Lace Midi Dress', 'black-lace-midi-dress', 'Black lace midi dress', 'Elegant black lace midi dress. Sophisticated design for evening events.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(78, 16, NULL, NULL, 2, NULL, NULL, NULL, 'Floral Maxi Dress', 'floral-maxi-dress', 'Floral print maxi dress', 'Beautiful floral print maxi dress. Lightweight and flowy for summer.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:31:25', NULL),
(79, 11, NULL, NULL, NULL, NULL, NULL, NULL, 'Blue Shirt Dress', 'blue-shirt-dress', 'Blue shirt dress casual', 'Casual blue shirt dress with belt. Perfect for office and casual outings.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(80, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'White Summer Dress', 'white-summer-dress', 'White lace summer dress', 'White cotton summer dress with lace trim. Breezy and feminine.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(81, 9, NULL, NULL, NULL, NULL, NULL, NULL, 'White Cotton Blouse', 'white-cotton-blouse', 'White cotton blouse', 'Classic white cotton blouse versatile for office or casual wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(82, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'Printed Crop Top', 'printed-crop-top', 'Floral print crop top', 'Trendy printed crop top with ruffled sleeves. Summer fashion essential.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(83, 16, NULL, NULL, NULL, NULL, NULL, NULL, 'Black Peplum Top', 'black-peplum-top', 'Black peplum party top', 'Elegant black peplum top with flared hem. Perfect for parties.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:42', 0, '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(84, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'Striped Off-Shoulder Top', 'striped-off-shoulder-top', 'Striped off-shoulder top', 'Striped off-shoulder top with elastic neckline. Boho chic style.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(85, 12, NULL, NULL, NULL, NULL, NULL, NULL, 'Silk Camisole Top', 'silk-camisole-top', 'Silk lace camisole', 'Luxurious silk camisole top with lace trim. Layering essential.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(86, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'Blue Skinny Jeans Women', 'blue-skinny-jeans-women', NULL, '<p>Comfortable blue skinny jeans for women. High waist with stretch denim.</p>', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:00', 0, '2026-06-26 06:05:43', '2026-06-26 06:53:17', NULL),
(87, 10, NULL, NULL, 4, NULL, NULL, NULL, 'Black Palazzo Pants', 'black-palazzo-pants', 'Black palazzo pants', 'Flowing black palazzo pants with elastic waist. Comfortable ethnic wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:31:25', NULL),
(88, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'White Trousers Women', 'white-trousers-women', 'White formal trousers', 'Crisp white trousers for women. Office-approved and stylish.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(89, 11, NULL, NULL, NULL, NULL, NULL, NULL, 'Denim Shorts Women', 'denim-shorts-women', 'Blue denim shorts', 'Casual denim shorts for women. Perfect summer essential.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(90, 16, NULL, NULL, NULL, NULL, NULL, NULL, 'Printed Leggings', 'printed-leggings', 'Printed stretch leggings', 'Colorful printed leggings with high stretch. Comfortable daily wear.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(91, 14, NULL, NULL, NULL, NULL, NULL, NULL, 'Chiffon Hijab Set - Pink', 'chiffon-hijab-set-pink', 'Pink chiffon hijab set', 'Premium chiffon hijab set in pink. Includes inner cap and pins.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(92, 9, NULL, NULL, 4, NULL, NULL, NULL, 'Cotton Hijab Multipack', 'cotton-hijab-multipack', '5-pack cotton hijabs', 'Pack of 5 cotton hijabs in assorted colors. Lightweight and breathable.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:31:25', NULL),
(93, 12, NULL, NULL, NULL, NULL, NULL, NULL, 'Embroidered Net Hijab', 'embroidered-net-hijab', 'Net embroidered hijab', 'Elegant net hijab with embroidered edges. Perfect for special occasions.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(94, 14, NULL, NULL, NULL, NULL, NULL, NULL, 'Silk Satin Hijab - Navy', 'silk-satin-hijab-navy', 'Navy silk satin hijab', 'Luxurious silk satin hijab in navy blue. Smooth and elegant drape.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(95, 14, NULL, NULL, 2, NULL, NULL, NULL, 'Black Abaya with Embroidery', 'black-abaya-embroidered', 'Black embroidered abaya', 'Elegant black abaya with subtle embroidery on sleeves and border.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:31:25', NULL),
(96, 14, NULL, NULL, NULL, NULL, NULL, NULL, 'Burgundy Open Abaya', 'burgundy-open-abaya', 'Burgundy open abaya', 'Burgundy open-front abaya with belt. Modern and modest.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(97, 14, NULL, NULL, NULL, NULL, NULL, NULL, 'Navy Blue Burqa', 'navy-blue-burqa', 'Navy full coverage burqa', 'Full coverage navy blue burqa. Lightweight fabric for comfort.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(98, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Brown Leather Backpack', 'brown-leather-backpack', 'Brown leather backpack', 'Premium brown genuine leather backpack with multiple compartments.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(99, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Black Handbag Tote', 'black-handbag-tote', 'Black tote handbag', 'Elegant black tote handbag with gold hardware. Spacious interior.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(100, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'Silver Analog Watch', 'silver-analog-watch', 'Silver analog leather watch', 'Stylish silver analog watch with leather strap. Water resistant.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(101, 15, NULL, NULL, 2, NULL, NULL, NULL, 'Gold Digital Smart Watch', 'gold-digital-smart-watch', 'Gold smart fitness watch', 'Gold tone smart watch with fitness tracking. Bluetooth connectivity.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:31:25', NULL),
(102, 12, NULL, NULL, NULL, NULL, NULL, NULL, 'Gold Plated Necklace Set', 'gold-plated-necklace-set', 'Gold necklace earring set', 'Beautiful gold plated necklace set with earrings. Wedding jewelry.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(103, 12, NULL, NULL, NULL, NULL, NULL, NULL, 'Traditional Bangles Set', 'traditional-bangles-set', '6-piece bangles set', 'Set of 6 traditional glass bangles in assorted colors. Bengali tradition.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(104, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'Black Leather Belt', 'black-leather-belt', 'Black leather belt', 'Classic black leather belt with silver buckle. Universal fit.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(105, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Brown Reversible Belt', 'brown-reversible-belt', 'Reversible brown/black belt', '2-in-1 reversible belt (brown/black). Great value formal accessory.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(106, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'Aviator Sunglasses Gold', 'aviator-sunglasses-gold', 'Gold aviator shades', 'Classic aviator sunglasses with gold frame and UV protection.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(107, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'Wayfarer Sunglasses Black', 'wayfarer-sunglasses-black', NULL, '<p>Trendy wayfarer style sunglasses. Black frame with polarized lenses.</p>', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:00', 0, '2026-06-26 06:05:43', '2026-07-01 11:06:36', NULL),
(108, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Attar Perfume Gift Set', 'attar-perfume-gift-set', '3-pack attar perfume set', 'Premium attar perfume collection of 3 fragrances. Traditional scents.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(109, 13, NULL, NULL, 4, NULL, NULL, NULL, 'Deodorant Spray 200ml', 'deodorant-spray-200ml', 'Body spray deodorant', 'Long-lasting deodorant body spray. Fresh fragrance for everyday use.', 'physical', 'active', 'public', NULL, NULL, '2026-06-26 06:05:43', 0, '2026-06-26 06:05:43', '2026-06-26 06:31:25', NULL),
(110, 13, NULL, 4, NULL, NULL, NULL, NULL, 'Stephen Hawkins', 'Eius culpa magna la', 'Culpa reiciendis mo', NULL, 'physical', 'draft', 'public', 'Tempor commodi sint', 'Vero sed omnis offic', '1996-09-20 04:11:00', 0, '2026-07-01 11:54:47', '2026-07-01 11:54:47', NULL),
(111, 15, NULL, 5, NULL, NULL, NULL, NULL, 'Daniel Spence', 'Enim quia id velit v', NULL, NULL, 'physical', 'draft', 'public', 'Cumque dolorem sed a', NULL, '2021-09-20 05:34:00', 0, '2026-07-01 11:55:52', '2026-07-01 12:06:12', NULL),
(112, 2, NULL, 2, NULL, NULL, NULL, NULL, 'Xandra Glenn', 'Atque cupiditate iur', NULL, NULL, 'physical', 'draft', 'public', 'Cum do molestias tem', NULL, '2016-06-18 17:22:00', 0, '2026-07-01 12:07:08', '2026-07-01 12:08:10', NULL),
(113, 3, NULL, 2, NULL, NULL, NULL, NULL, 'Alfonso Stein', 'Eveniet aliquip err', 'Cumque officia fuga', NULL, 'physical', 'draft', 'public', 'Eum magna alias ulla', 'Ut sequi officia asp', '2024-09-26 23:25:00', 1, '2026-07-01 12:11:48', '2026-07-21 02:04:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`product_id`, `category_id`, `deleted_at`) VALUES
(1, 1, NULL),
(1, 6, NULL),
(2, 1, NULL),
(2, 6, NULL),
(3, 1, NULL),
(3, 7, NULL),
(4, 2, NULL),
(4, 3, NULL),
(4, 8, NULL),
(5, 2, NULL),
(5, 3, NULL),
(6, 3, NULL),
(7, 2, NULL),
(7, 8, NULL),
(8, 2, NULL),
(8, 9, NULL),
(9, 1, NULL),
(9, 4, NULL),
(10, 1, NULL),
(10, 4, NULL),
(11, 4, NULL),
(12, 5, NULL),
(13, 5, NULL),
(14, 5, NULL),
(15, 2, NULL),
(16, 10, NULL),
(17, 10, NULL),
(18, 10, NULL),
(19, 10, NULL),
(20, 10, NULL),
(21, 10, NULL),
(22, 10, NULL),
(23, 10, NULL),
(24, 11, NULL),
(25, 11, NULL),
(26, 11, NULL),
(27, 11, NULL),
(28, 11, NULL),
(29, 11, NULL),
(30, 11, NULL),
(31, 12, NULL),
(32, 12, NULL),
(33, 12, NULL),
(34, 12, NULL),
(35, 12, NULL),
(36, 12, NULL),
(37, 13, NULL),
(38, 13, NULL),
(39, 13, NULL),
(40, 13, NULL),
(41, 13, NULL),
(42, 13, NULL),
(43, 14, NULL),
(44, 14, NULL),
(45, 14, NULL),
(46, 14, NULL),
(47, 15, NULL),
(48, 15, NULL),
(49, 15, NULL),
(50, 15, NULL),
(51, 15, NULL),
(52, 16, NULL),
(53, 16, NULL),
(54, 16, NULL),
(55, 18, NULL),
(56, 18, NULL),
(57, 18, NULL),
(58, 19, NULL),
(59, 19, NULL),
(60, 19, NULL),
(61, 19, NULL),
(62, 19, NULL),
(63, 19, NULL),
(64, 19, NULL),
(65, 20, NULL),
(66, 20, NULL),
(67, 20, NULL),
(68, 20, NULL),
(69, 20, NULL),
(70, 20, NULL),
(71, 21, NULL),
(72, 21, NULL),
(73, 21, NULL),
(74, 21, NULL),
(75, 21, NULL),
(76, 22, NULL),
(77, 22, NULL),
(78, 22, NULL),
(79, 22, NULL),
(80, 22, NULL),
(81, 23, NULL),
(82, 23, NULL),
(83, 23, NULL),
(84, 23, NULL),
(85, 23, NULL),
(86, 24, NULL),
(87, 24, NULL),
(88, 24, NULL),
(89, 24, NULL),
(90, 24, NULL),
(91, 25, NULL),
(92, 25, NULL),
(93, 25, NULL),
(94, 25, NULL),
(95, 26, NULL),
(96, 26, NULL),
(97, 26, NULL),
(98, 29, NULL),
(99, 29, NULL),
(100, 30, NULL),
(101, 30, NULL),
(102, 31, NULL),
(103, 31, NULL),
(104, 32, NULL),
(105, 32, NULL),
(106, 33, NULL),
(107, 33, NULL),
(108, 35, NULL),
(109, 35, NULL),
(110, 1, NULL),
(110, 8, NULL),
(110, 9, NULL),
(110, 11, NULL),
(110, 13, NULL),
(110, 15, NULL),
(110, 16, NULL),
(110, 17, NULL),
(110, 20, NULL),
(110, 21, NULL),
(110, 23, NULL),
(110, 25, NULL),
(110, 26, NULL),
(110, 34, NULL),
(110, 37, NULL),
(110, 39, NULL),
(111, 1, NULL),
(111, 2, NULL),
(111, 5, NULL),
(111, 6, NULL),
(111, 7, NULL),
(111, 14, NULL),
(111, 16, NULL),
(111, 17, NULL),
(111, 19, NULL),
(111, 20, NULL),
(111, 22, NULL),
(111, 23, NULL),
(111, 24, NULL),
(111, 26, NULL),
(111, 27, NULL),
(111, 31, NULL),
(111, 33, NULL),
(111, 34, NULL),
(111, 35, NULL),
(111, 36, NULL),
(111, 37, NULL),
(111, 38, NULL),
(111, 39, NULL),
(112, 1, NULL),
(112, 2, NULL),
(112, 3, NULL),
(112, 5, NULL),
(112, 6, NULL),
(112, 7, NULL),
(112, 8, NULL),
(112, 10, NULL),
(112, 11, NULL),
(112, 12, NULL),
(112, 13, NULL),
(112, 14, NULL),
(112, 19, NULL),
(112, 20, NULL),
(112, 21, NULL),
(112, 22, NULL),
(112, 23, NULL),
(112, 24, NULL),
(112, 25, NULL),
(112, 27, NULL),
(112, 30, NULL),
(112, 32, NULL),
(112, 33, NULL),
(112, 34, NULL),
(112, 36, NULL),
(112, 38, NULL),
(113, 1, NULL),
(113, 4, NULL),
(113, 5, NULL),
(113, 6, NULL),
(113, 7, NULL),
(113, 9, NULL),
(113, 10, NULL),
(113, 11, NULL),
(113, 12, NULL),
(113, 13, NULL),
(113, 15, NULL),
(113, 17, NULL),
(113, 19, NULL),
(113, 20, NULL),
(113, 21, NULL),
(113, 22, NULL),
(113, 24, NULL),
(113, 30, NULL),
(113, 31, NULL),
(113, 34, NULL),
(113, 38, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_main` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `variant_id`, `image_url`, `alt_text`, `sort_order`, `is_main`, `created_at`, `updated_at`, `deleted_at`) VALUES
(113, 107, NULL, 'products/wayfarer-sunglasses-black-20260701170636-0.png', 'Wayfarer Sunglasses Black', 1, 1, '2026-07-01 11:06:37', '2026-07-01 11:06:37', NULL),
(114, 107, NULL, 'products/wayfarer-sunglasses-black-20260701170637-1.png', 'Wayfarer Sunglasses Black', 2, 0, '2026-07-01 11:06:37', '2026-07-01 11:06:37', NULL),
(115, 107, NULL, 'products/wayfarer-sunglasses-black-20260701170637-2.png', 'Wayfarer Sunglasses Black', 3, 0, '2026-07-01 11:06:37', '2026-07-01 11:06:37', NULL),
(116, 107, NULL, 'products/wayfarer-sunglasses-black-20260701170637-3.png', 'Wayfarer Sunglasses Black', 4, 0, '2026-07-01 11:06:37', '2026-07-01 11:06:37', NULL),
(117, 112, NULL, 'products/xandra-glenn-20260701180708-0.png', 'Xandra Glenn', 1, 1, '2026-07-01 12:07:08', '2026-07-01 12:07:08', NULL),
(118, 112, NULL, 'products/xandra-glenn-20260701180708-1.png', 'Xandra Glenn', 2, 0, '2026-07-01 12:07:08', '2026-07-01 12:07:08', NULL),
(119, 112, NULL, 'products/xandra-glenn-20260701180708-2.png', 'Xandra Glenn', 3, 0, '2026-07-01 12:07:08', '2026-07-01 12:07:08', NULL),
(120, 112, NULL, 'products/xandra-glenn-20260701180708-3.png', 'Xandra Glenn', 4, 0, '2026-07-01 12:07:08', '2026-07-01 12:07:08', NULL),
(121, 112, NULL, 'products/xandra-glenn-20260701180708-4.jpg', 'Xandra Glenn', 5, 0, '2026-07-01 12:07:08', '2026-07-01 12:07:08', NULL),
(122, 112, NULL, 'products/xandra-glenn-20260701180708-5.png', 'Xandra Glenn', 6, 0, '2026-07-01 12:07:08', '2026-07-01 12:07:08', NULL),
(123, 112, NULL, 'products/xandra-glenn-20260701180708-6.png', 'Xandra Glenn', 7, 0, '2026-07-01 12:07:08', '2026-07-01 12:07:08', NULL),
(124, 112, NULL, 'products/xandra-glenn-20260701180708-7.jpg', 'Xandra Glenn', 8, 0, '2026-07-01 12:07:08', '2026-07-01 12:07:08', NULL),
(125, 112, NULL, 'products/xandra-glenn-20260701180708-8.png', 'Xandra Glenn', 9, 0, '2026-07-01 12:07:08', '2026-07-01 12:07:08', NULL),
(126, 112, NULL, 'products/xandra-glenn-20260701180810-0.jpg', 'Xandra Glenn', 10, 0, '2026-07-01 12:08:10', '2026-07-01 12:08:10', NULL),
(127, 112, NULL, 'products/xandra-glenn-20260701180810-1.png', 'Xandra Glenn', 11, 0, '2026-07-01 12:08:10', '2026-07-01 12:08:10', NULL),
(128, 112, NULL, 'products/xandra-glenn-20260701180810-2.jpg', 'Xandra Glenn', 12, 0, '2026-07-01 12:08:10', '2026-07-01 12:08:10', NULL),
(129, 113, NULL, 'products/alfonso-stein-20260701181148-0.png', 'Alfonso Stein', 1, 1, '2026-07-01 12:11:48', '2026-07-01 12:11:48', NULL),
(130, 113, NULL, 'products/alfonso-stein-20260701181148-1.png', 'Alfonso Stein', 2, 0, '2026-07-01 12:11:48', '2026-07-01 12:11:48', NULL),
(131, 113, NULL, 'products/alfonso-stein-20260701181148-2.png', 'Alfonso Stein', 3, 0, '2026-07-01 12:11:49', '2026-07-01 12:11:49', NULL),
(132, 113, NULL, 'products/alfonso-stein-20260701181149-3.png', 'Alfonso Stein', 4, 0, '2026-07-01 12:11:49', '2026-07-01 12:11:49', NULL),
(133, 113, NULL, 'products/alfonso-stein-20260701181149-4.jpg', 'Alfonso Stein', 5, 0, '2026-07-01 12:11:49', '2026-07-01 12:11:49', NULL),
(134, 113, NULL, 'products/alfonso-stein-20260701181149-5.png', 'Alfonso Stein', 6, 0, '2026-07-01 12:11:49', '2026-07-01 12:11:49', NULL),
(135, 113, NULL, 'products/alfonso-stein-20260701181149-6.png', 'Alfonso Stein', 7, 0, '2026-07-01 12:11:49', '2026-07-01 12:11:49', NULL),
(136, 113, NULL, 'products/alfonso-stein-20260701181149-7.jpg', 'Alfonso Stein', 8, 0, '2026-07-01 12:11:49', '2026-07-01 12:11:49', NULL),
(137, 113, NULL, 'products/alfonso-stein-20260701181149-8.png', 'Alfonso Stein', 9, 0, '2026-07-01 12:11:49', '2026-07-01 12:11:49', NULL),
(138, 1, NULL, 'products/iphone-15-pro-max-20260701181303-0.png', 'iPhone 15 Pro Max', 1, 1, '2026-07-01 12:13:03', '2026-07-01 12:13:03', NULL),
(139, 1, NULL, 'products/iphone-15-pro-max-20260701181303-1.png', 'iPhone 15 Pro Max', 2, 0, '2026-07-01 12:13:03', '2026-07-01 12:13:03', NULL),
(140, 1, NULL, 'products/iphone-15-pro-max-20260701181303-2.png', 'iPhone 15 Pro Max', 3, 0, '2026-07-01 12:13:03', '2026-07-01 12:13:03', NULL),
(141, 1, NULL, 'products/iphone-15-pro-max-20260701181303-3.png', 'iPhone 15 Pro Max', 4, 0, '2026-07-01 12:13:03', '2026-07-01 12:13:03', NULL),
(142, 1, NULL, 'products/iphone-15-pro-max-20260701181303-4.jpg', 'iPhone 15 Pro Max', 5, 0, '2026-07-01 12:13:03', '2026-07-01 12:13:03', NULL),
(143, 1, NULL, 'products/iphone-15-pro-max-20260701181303-5.png', 'iPhone 15 Pro Max', 6, 0, '2026-07-01 12:13:03', '2026-07-01 12:13:03', NULL),
(144, 1, NULL, 'products/iphone-15-pro-max-20260701181303-6.png', 'iPhone 15 Pro Max', 7, 0, '2026-07-01 12:13:03', '2026-07-01 12:13:03', NULL),
(145, 1, NULL, 'products/iphone-15-pro-max-20260701181303-7.jpg', 'iPhone 15 Pro Max', 8, 0, '2026-07-01 12:13:03', '2026-07-01 12:13:03', NULL),
(146, 1, NULL, 'products/iphone-15-pro-max-20260701181303-8.png', 'iPhone 15 Pro Max', 9, 0, '2026-07-01 12:13:03', '2026-07-01 12:13:03', NULL),
(147, 2, NULL, 'products/samsung-galaxy-s24-ultra-20260701184011-0.png', 'Samsung Galaxy S24 Ultra', 1, 1, '2026-07-01 12:40:11', '2026-07-01 12:40:11', NULL),
(148, 2, NULL, 'products/samsung-galaxy-s24-ultra-20260701184011-1.png', 'Samsung Galaxy S24 Ultra', 2, 0, '2026-07-01 12:40:12', '2026-07-01 12:40:12', NULL),
(149, 2, NULL, 'products/samsung-galaxy-s24-ultra-20260701184012-2.png', 'Samsung Galaxy S24 Ultra', 3, 0, '2026-07-01 12:40:12', '2026-07-01 12:40:12', NULL),
(150, 2, NULL, 'products/samsung-galaxy-s24-ultra-20260701184012-3.jpg', 'Samsung Galaxy S24 Ultra', 4, 0, '2026-07-01 12:40:12', '2026-07-01 12:40:12', NULL),
(151, 2, NULL, 'products/samsung-galaxy-s24-ultra-20260701184012-4.png', 'Samsung Galaxy S24 Ultra', 5, 0, '2026-07-01 12:40:12', '2026-07-01 12:40:12', NULL),
(152, 2, NULL, 'products/samsung-galaxy-s24-ultra-20260701184012-5.png', 'Samsung Galaxy S24 Ultra', 6, 0, '2026-07-01 12:40:12', '2026-07-01 12:40:12', NULL),
(153, 3, NULL, 'products/macbook-pro-m3-20260701184524-0.png', 'MacBook Pro M3', 1, 1, '2026-07-01 12:45:24', '2026-07-01 12:45:24', NULL),
(154, 3, NULL, 'products/macbook-pro-m3-20260701184524-1.jpg', 'MacBook Pro M3', 2, 0, '2026-07-01 12:45:24', '2026-07-01 12:45:24', NULL),
(155, 3, NULL, 'products/macbook-pro-m3-20260701184524-2.png', 'MacBook Pro M3', 3, 0, '2026-07-01 12:45:24', '2026-07-01 12:45:24', NULL),
(156, 3, NULL, 'products/macbook-pro-m3-20260701184524-3.png', 'MacBook Pro M3', 4, 0, '2026-07-01 12:45:24', '2026-07-01 12:45:24', NULL),
(157, 3, NULL, 'products/macbook-pro-m3-20260701184524-4.jpg', 'MacBook Pro M3', 5, 0, '2026-07-01 12:45:24', '2026-07-01 12:45:24', NULL),
(158, 3, NULL, 'products/macbook-pro-m3-20260701184524-5.png', 'MacBook Pro M3', 6, 0, '2026-07-01 12:45:24', '2026-07-01 12:45:24', NULL),
(159, 10, NULL, 'products/sony-home-theater-system-20260720182120-0.png', 'Sony Home Theater System', 1, 1, '2026-07-21 01:21:21', '2026-07-21 01:49:46', '2026-07-21 01:49:46'),
(160, 10, NULL, 'products/sony-home-theater-system-20260720182121-1.png', 'Sony Home Theater System', 2, 0, '2026-07-21 01:21:21', '2026-07-21 01:49:46', '2026-07-21 01:49:46'),
(161, 10, NULL, 'products/sony-home-theater-system-20260720182121-2.png', 'Sony Home Theater System', 3, 0, '2026-07-21 01:21:21', '2026-07-21 01:49:46', '2026-07-21 01:49:46'),
(162, 10, NULL, 'products/sony-home-theater-system-20260720182121-3.png', 'Sony Home Theater System', 4, 0, '2026-07-21 01:21:21', '2026-07-21 01:49:46', '2026-07-21 01:49:46'),
(163, 10, NULL, 'products/sony-home-theater-system-20260720182121-4.png', 'Sony Home Theater System', 5, 0, '2026-07-21 01:21:21', '2026-07-21 01:49:46', '2026-07-21 01:49:46'),
(164, 10, NULL, 'products/sony-home-theater-system-20260720182121-5.png', 'Sony Home Theater System', 6, 0, '2026-07-21 01:21:21', '2026-07-21 01:49:46', '2026-07-21 01:49:46'),
(165, 10, NULL, 'products/sony-home-theater-system-20260720182121-6.png', 'Sony Home Theater System', 7, 0, '2026-07-21 01:21:21', '2026-07-21 01:49:46', '2026-07-21 01:49:46'),
(166, 10, NULL, 'products/sony-home-theater-system-20260720184946-0.webp', 'Sony Home Theater System', 1, 1, '2026-07-21 01:49:46', '2026-07-21 01:49:46', NULL),
(167, 10, NULL, 'products/sony-home-theater-system-20260720184946-1.webp', 'Sony Home Theater System', 2, 0, '2026-07-21 01:49:46', '2026-07-21 01:49:46', NULL),
(168, 4, NULL, 'products/nike-air-max-270-20260720191243-0.jpg', 'Nike Air Max 270', 1, 1, '2026-07-21 02:12:43', '2026-07-21 02:12:43', NULL),
(169, 4, NULL, 'products/nike-air-max-270-20260720191243-1.jpg', 'Nike Air Max 270', 2, 0, '2026-07-21 02:12:43', '2026-07-21 02:12:43', NULL),
(170, 4, NULL, 'products/nike-air-max-270-20260720191243-2.jpg', 'Nike Air Max 270', 3, 0, '2026-07-21 02:12:43', '2026-07-21 02:12:43', NULL),
(171, 4, NULL, 'products/nike-air-max-270-20260720191243-3.jpg', 'Nike Air Max 270', 4, 0, '2026-07-21 02:12:43', '2026-07-21 02:12:43', NULL),
(172, 7, NULL, 'products/handmade-cotton-kurta-20260720191745-0.webp', 'Handmade Cotton Kurta', 1, 1, '2026-07-21 02:17:45', '2026-07-21 02:17:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_requests`
--

CREATE TABLE `product_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(160) NOT NULL,
  `customer_email` varchar(160) NOT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `product_name` varchar(220) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `expected_price` decimal(12,4) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_requests`
--

INSERT INTO `product_requests` (`id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `product_name`, `product_description`, `product_image`, `product_id`, `quantity`, `expected_price`, `notes`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 'Josiah Mcgee', 'rewoburoly@mailinator.com', '+1 (476) 567-6559', 'Janna Cotton', 'Consequuntur dolorem', NULL, NULL, 116, 539.0000, 'Numquam quasi vel ve', 'pending', '2026-07-01 09:24:52', '2026-07-01 09:24:52', NULL),
(2, NULL, 'fgsdgsdfg', 'a@gmail.com', NULL, 'afdasf', NULL, NULL, NULL, 1, 1000.0000, 'asdfgasd', 'pending', '2026-07-01 09:38:24', '2026-07-01 09:38:24', NULL),
(3, NULL, 'Brett Pate', 'qydi@mailinator.com', '+1 (933) 185-6816', 'Stella Richard', 'Fugit excepteur odi', NULL, NULL, 197, 591.0000, 'Eu dolore quod volup', 'pending', '2026-07-01 09:40:27', '2026-07-01 09:40:27', NULL),
(4, NULL, 'Illiana Snider', 'fohubiduxy@mailinator.com', '+1 (736) 317-8082', 'Lilah Joseph', 'Rerum dolorem adipis', NULL, NULL, 240, 259.0000, 'Laboriosam magna co', 'pending', '2026-07-01 09:44:47', '2026-07-01 09:44:47', NULL),
(5, NULL, 'Logan Patel', 'dadume@mailinator.com', '+1 (894) 778-7052', 'Imani Joyner', 'Reprehenderit ullam', NULL, NULL, 561, 722.0000, 'Nisi vitae molestiae', 'pending', '2026-07-01 09:46:19', '2026-07-01 09:46:19', NULL),
(6, NULL, 'Daquan Middleton', 'baxuzuhiw@mailinator.com', '+1 (196) 665-7387', 'Owen Cook', 'Doloribus doloribus', NULL, NULL, 44, 250.0000, 'Laborum et non ea of', 'approved', '2026-07-01 09:50:24', '2026-07-01 10:08:07', NULL),
(7, NULL, 'Helen Hines', 'kujijil@mailinator.com', '+1 (405) 493-9868', 'Rama Pollard', 'Qui omnis amet ut r', NULL, NULL, 703, 486.0000, 'Ut excepteur est ma', 'pending', '2026-07-24 21:07:36', '2026-07-24 21:07:36', NULL),
(8, NULL, 'Kameko Price', 'jafesorina@mailinator.com', '+1 (431) 943-8779', 'Porter Rosales', 'Vel enim vero quam e', NULL, NULL, 349, 227.0000, 'Quos id debitis ear', 'pending', '2026-07-24 21:14:13', '2026-07-24 21:14:13', NULL),
(9, NULL, 'Andrew Mcclure', 'duja@mailinator.com', '+1 (317) 424-5154', 'Malcolm Frye', 'Eius voluptatem neq', NULL, NULL, 876, 93.0000, 'Tempor blanditiis cu', 'pending', '2026-07-24 21:22:50', '2026-07-24 21:22:50', NULL),
(10, NULL, 'Gil Hancock', 'fogux@mailinator.com', '+1 (494) 235-2183', 'Bo Richardson', 'Et voluptatibus inci', NULL, NULL, 981, 494.0000, 'Culpa eveniet venia', 'pending', '2026-07-24 21:51:02', '2026-07-24 21:51:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `title` text DEFAULT NULL,
  `body` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `is_verified_purchase` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `order_id`, `rating`, `title`, `body`, `status`, `is_verified_purchase`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, NULL, 5, 'Excellent product!', 'This is a fantastic product. Would highly recommend to everyone.', 'approved', 1, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(2, 1, 5, NULL, 5, 'sdadsa', 'fdasdf', 'approved', 1, '2026-06-27 11:37:12', '2026-06-27 11:37:12', NULL),
(3, 2, 5, NULL, 5, 'sadSAD', 'SDada', 'pending', 0, '2026-06-28 10:23:16', '2026-06-28 10:23:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_supplier`
--

CREATE TABLE `product_supplier` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_sku` varchar(100) DEFAULT NULL,
  `lead_time_days` int(10) UNSIGNED DEFAULT NULL,
  `minimum_order_qty` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `default_unit_cost` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(100) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `name` varchar(220) NOT NULL,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes`)),
  `cost_price` decimal(19,4) NOT NULL DEFAULT 0.0000,
  `sale_price` decimal(19,4) NOT NULL,
  `compare_at_price` decimal(19,4) DEFAULT NULL,
  `weight_grams` int(10) UNSIGNED DEFAULT NULL,
  `length_mm` int(10) UNSIGNED DEFAULT NULL,
  `width_mm` int(10) UNSIGNED DEFAULT NULL,
  `height_mm` int(10) UNSIGNED DEFAULT NULL,
  `track_inventory` tinyint(1) NOT NULL DEFAULT 1,
  `allow_backorder` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `name`, `attributes`, `cost_price`, `sale_price`, `compare_at_price`, `weight_grams`, `length_mm`, `width_mm`, `height_mm`, `track_inventory`, `allow_backorder`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'IPHONE-15-PRO-MAX-1', 'fe976236-f636-4365-a010-ba778eae5ed5', '256GB', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 104999.0000, 149999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:40', '2026-07-19 22:56:38', NULL),
(2, 1, 'IPHONE-15-PRO-MAX-2', '07d88c52-127a-492f-a470-ba27e2259d50', '512GB', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 120749.0000, 172499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:40', '2026-07-19 22:56:38', NULL),
(3, 1, 'IPHONE-15-PRO-MAX-3', 'dd436517-a8a7-483a-b488-51532921ab87', '1TB', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 136499.0000, 194999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:40', '2026-07-19 22:56:38', NULL),
(4, 2, 'SAMSUNG-GALAXY-S24-ULTRA-1', 'f0e86867-849e-4dce-b2d0-843b56d250b1', '256GB', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 97999.0000, 139999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-24 12:05:37', NULL),
(5, 2, 'SAMSUNG-GALAXY-S24-ULTRA-2', '9404c337-fcfe-4f3b-bc51-62cbd37ee3cb', '512GB', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 112699.0000, 160999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-24 12:05:37', NULL),
(6, 2, 'SAMSUNG-GALAXY-S24-ULTRA-3', '3d4b9863-776e-4587-a765-247b235ad77d', '1TB', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 127399.0000, 181999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-24 12:05:37', NULL),
(7, 3, 'MACBOOK-PRO-M3-1', '9a3743c3-b3a8-4684-bc82-173b430ea821', '14\" M3', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 139999.0000, 199999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-24 12:06:54', NULL),
(8, 3, 'MACBOOK-PRO-M3-2', '033a48dd-9db8-4f3e-a5c5-b5e3108b49dd', '16\" M3 Pro', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 188999.0000, 269999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-24 12:06:54', NULL),
(9, 3, 'MACBOOK-PRO-M3-3', '9aac8364-9f09-443f-9fac-3f72b6810324', '16\" M3 Max', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 223999.0000, 319998.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-24 12:06:54', NULL),
(10, 4, 'NIKE-AIR-MAX-270-1', 'ef5c0bad-cc91-4057-9394-7c2910abf88e', 'US 8', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 11199.0000, 15999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-21 02:12:43', NULL),
(11, 4, 'NIKE-AIR-MAX-270-2', '0df5d2dd-addc-431c-8740-74a10346b5db', 'US 9', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 11199.0000, 15999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-21 02:12:43', NULL),
(12, 4, 'NIKE-AIR-MAX-270-3', '0da5f6a7-ab17-421e-8e0a-78554ed36f73', 'US 10', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 11199.0000, 15999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-21 02:12:43', NULL),
(13, 4, 'NIKE-AIR-MAX-270-4', 'bda7fc99-2d31-4d2c-95b5-c49ec22cc22f', 'US 11', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 11199.0000, 15999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-21 02:12:43', NULL),
(14, 5, 'NIKE-DRI-FIT-SPORTSWEAR-1', '99e45a62-ae0b-42e0-b53e-2d7e9f662eb4', 'Default', NULL, 7000.0000, 10000.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(15, 6, 'NIKE-FOOTBALL-PRO-1', '16629885-7674-42b6-9afc-82e7cf6af123', 'Default', NULL, 7000.0000, 10000.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(16, 7, 'HANDMADE-COTTON-KURTA-1', 'beb81d4e-c3b1-4de8-b469-0da24fff41fb', 'S', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 1749.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-21 02:17:46', NULL),
(17, 7, 'HANDMADE-COTTON-KURTA-2', '9822cb1f-8cf9-48a0-9f18-8ee0eb42b56b', 'M', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 1749.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-21 02:17:46', NULL),
(18, 7, 'HANDMADE-COTTON-KURTA-3', '36d5b24f-b6c3-4afc-9af0-264dca17ecde', 'L', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 1749.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-21 02:17:46', NULL),
(19, 7, 'HANDMADE-COTTON-KURTA-4', '15ca0987-1cc6-406e-8241-4f6bf8b9c69e', 'XL', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 1749.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-21 02:17:46', NULL),
(20, 8, 'BANGLAR-MUSLIN-SAREE-1', 'c35d30af-033e-4b92-88d8-f6b0a8d4243a', '6 Yard', NULL, 6299.0000, 8999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(21, 8, 'BANGLAR-MUSLIN-SAREE-2', '5be8a6e1-c845-4bdf-a5a9-7186bbf4afb8', '5.5 Yard', NULL, 6299.0000, 8999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(22, 9, 'SAMSUNG-65-4K-SMART-TV-1', 'beb5108e-9585-4e76-8da1-f59480de23f5', '55\"', NULL, 50399.0000, 71999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(23, 9, 'SAMSUNG-65-4K-SMART-TV-2', 'd9c9649a-9cf7-42f7-ab95-b862371e7563', '65\"', NULL, 62999.0000, 89999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(24, 9, 'SAMSUNG-65-4K-SMART-TV-3', '1414e5e3-9f39-4054-9faa-d3cc46439f0a', '75\"', NULL, 78749.0000, 112499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(25, 10, 'SONY-HOME-THEATER-SYSTEM-1', 'd8b52cf2-388d-4d30-b67b-641e072a35c8', 'Default', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 38499.0000, 54999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-07-21 01:49:46', NULL),
(26, 11, 'SAMSUNG-BESPOKE-REFRIGERATOR-1', 'd40c3d7d-c2f8-4f9b-aa65-1ffe8d54315d', 'Default', NULL, 90999.0000, 129999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(27, 12, 'THE-GREAT-GATSBY-1', '6ba9e08c-4b80-49ae-af71-787fac54073c', 'Default', NULL, 559.0000, 799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:41', '2026-06-25 06:17:41', NULL),
(28, 13, 'TO-KILL-A-MOCKINGBIRD-1', '176055b5-3415-43c4-948d-178f7dd0e4a5', 'Default', NULL, 489.0000, 699.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:42', '2026-06-25 06:17:42', NULL),
(29, 14, '1984-1', 'b36a655a-e10d-41d5-8346-6e05811c2052', 'Default', NULL, 524.0000, 749.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-25 06:17:42', '2026-06-25 06:17:42', NULL),
(30, 15, 'CTEE-W-S', 'd7c37c94-3e4b-4567-a45a-f04a0ea6657c', 'White / S', '{\"color\":\"White\",\"size\":\"S\",\"color_hex\":\"#ffffff\"}', 700.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-06-25 06:17:42', '2026-06-26 05:17:00', NULL),
(31, 15, 'CTEE-W-M', 'e74049fa-2a90-40c6-ac5b-2ca56bec4cf4', 'White / M', '{\"color\":\"White\",\"size\":\"M\",\"color_hex\":\"#ffffff\"}', 700.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-06-25 06:17:42', '2026-06-26 05:17:00', NULL),
(32, 15, 'CTEE-W-L', '85c3a441-751a-468e-a4c2-a7c045196fb5', 'White / L', '{\"color\":\"White\",\"size\":\"L\",\"color_hex\":\"#ffffff\"}', 700.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-06-25 06:17:42', '2026-06-26 05:17:00', NULL),
(33, 15, 'CTEE-W-XL', '619f4b33-6666-43da-bdd4-7093ca89d938', 'White / XL', '{\"color\":\"White\",\"size\":\"XL\",\"color_hex\":\"#ffffff\"}', 700.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-06-25 06:17:42', '2026-06-26 05:17:00', NULL),
(34, 15, 'CTEE-B-S', 'f08d2292-e036-4d8c-bb74-86af954e3c59', 'Black / S', '{\"color\":\"Black\",\"size\":\"S\",\"color_hex\":\"#111827\"}', 700.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-06-25 06:17:42', '2026-06-26 05:17:00', NULL),
(35, 15, 'CTEE-B-M', '67ae1b8f-a729-4b34-8185-3ac85a1ba7fc', 'Black / M', '{\"color\":\"Black\",\"size\":\"M\",\"color_hex\":\"#111827\"}', 700.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-06-25 06:17:42', '2026-06-26 05:17:00', NULL),
(36, 15, 'CTEE-B-L', 'aff2158e-70b8-4fe9-ac40-91ff69b7ffef', 'Black / L', '{\"color\":\"Black\",\"size\":\"L\",\"color_hex\":\"#111827\"}', 700.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-06-25 06:17:42', '2026-06-26 05:17:00', NULL),
(37, 15, 'CTEE-B-XL', '9fb5c631-3fac-4de5-8f1c-c709dac4e52b', 'Black / XL', '{\"color\":\"Black\",\"size\":\"XL\",\"color_hex\":\"#111827\"}', 700.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-06-25 06:17:42', '2026-06-26 05:17:00', NULL),
(38, 15, 'CTEE-R-S', '1052c9e7-0a66-4b42-9657-87a54b8d127a', 'Red / S', '{\"color\":\"Red\",\"size\":\"S\",\"color_hex\":\"#ef4444\"}', 700.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-06-25 06:17:42', '2026-06-26 05:17:00', NULL),
(39, 15, 'CTEE-R-M', 'a8d37e89-8a4b-4d32-aa32-8d26b06d18f2', 'Red / M', '{\"color\":\"Red\",\"size\":\"M\",\"color_hex\":\"#ef4444\"}', 700.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-06-25 06:17:42', '2026-06-26 05:17:00', NULL),
(40, 15, 'CTEE-R-L', 'b7a3fdb8-5e12-46cd-94ac-99541e34737c', 'Red / L', '{\"color\":\"Red\",\"size\":\"L\",\"color_hex\":\"#ef4444\"}', 700.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-06-25 06:17:42', '2026-06-26 05:17:00', NULL),
(41, 15, 'CTEE-R-XL', '1d2876ff-0615-4bd0-87b3-dbe3017c21f5', 'Red / XL', '{\"color\":\"Red\",\"size\":\"XL\",\"color_hex\":\"#ef4444\"}', 700.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-06-25 06:17:42', '2026-06-26 05:17:00', NULL),
(42, 16, 'CLASSIC-WHITE-COTTON-TSHIRT-1', '7cbc9e57-e6ab-4b4b-81a5-14ca60deb868', 'S', NULL, 539.0000, 899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(43, 16, 'CLASSIC-WHITE-COTTON-TSHIRT-2', 'c287f099-8186-40d1-84e1-fe1e1c36d013', 'M', NULL, 539.0000, 899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(44, 16, 'CLASSIC-WHITE-COTTON-TSHIRT-3', '385deb4c-11db-4f0d-a106-f7161009dbb7', 'L', NULL, 539.0000, 899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(45, 16, 'CLASSIC-WHITE-COTTON-TSHIRT-4', '2a1b5ab6-3b44-4abe-8ca3-925f1aea8d61', 'XL', NULL, 539.0000, 899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(46, 16, 'CLASSIC-WHITE-COTTON-TSHIRT-5', '70fe0628-8ef3-44f6-969c-641041bc5816', 'XXL', NULL, 593.0000, 989.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(47, 17, 'BLACK-EDITION-PREMIUM-TEE-1', '8445e932-7519-4aff-ad4d-1fd657ef92c1', 'M', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(48, 17, 'BLACK-EDITION-PREMIUM-TEE-2', '22be3932-e081-4af4-8de6-170600fbc0ea', 'L', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(49, 17, 'BLACK-EDITION-PREMIUM-TEE-3', 'd7ae136c-766b-42de-b518-9cec5a35550f', 'XL', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(50, 17, 'BLACK-EDITION-PREMIUM-TEE-4', '7dd69d37-ec9b-468e-9202-2fd56b503c45', 'XXL', NULL, 659.0000, 1099.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(51, 18, 'STRIPED-CASUAL-BLUE-TEE-1', '77919b79-98d0-4de8-86a1-a12e682798d9', 'S', NULL, 659.0000, 1099.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(52, 18, 'STRIPED-CASUAL-BLUE-TEE-2', '3678741d-ed02-44bb-8467-b4fe0808a290', 'M', NULL, 659.0000, 1099.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(53, 18, 'STRIPED-CASUAL-BLUE-TEE-3', 'afacf0cd-1c7e-4fa1-96fd-9d47d8455043', 'L', NULL, 659.0000, 1099.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(54, 18, 'STRIPED-CASUAL-BLUE-TEE-4', '684e185d-7f36-45e0-9a07-f4bebd2c95d5', 'XL', NULL, 659.0000, 1099.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(55, 19, 'GRAPHIC-PRINTED-TSHIRT-1', '56c61c2e-dcb8-4d48-ba86-b27484014e3b', 'S', NULL, 719.0000, 1199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(56, 19, 'GRAPHIC-PRINTED-TSHIRT-2', '9869a659-5bff-4ea4-ba6a-13631ff14e8e', 'M', NULL, 719.0000, 1199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(57, 19, 'GRAPHIC-PRINTED-TSHIRT-3', '502f4c1f-0374-4265-bf31-ac5e655ac159', 'L', NULL, 719.0000, 1199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(58, 19, 'GRAPHIC-PRINTED-TSHIRT-4', 'd822751d-a6ba-47b1-aad9-4dc83b319510', 'XL', NULL, 719.0000, 1199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(59, 19, 'GRAPHIC-PRINTED-TSHIRT-5', 'e1ea9ff3-851b-4569-8415-38323d224de1', 'XXL', NULL, 791.0000, 1319.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(60, 20, 'RED-SPORTS-TSHIRT-1', '0e389287-9374-4a4c-8632-34f47e9a0715', 'M', NULL, 479.0000, 799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(61, 20, 'RED-SPORTS-TSHIRT-2', '560bbb2c-b6bd-442e-a33d-029913c44bd1', 'L', NULL, 479.0000, 799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(62, 20, 'RED-SPORTS-TSHIRT-3', '5028c79a-6018-4951-a52c-5268956dd71a', 'XL', NULL, 479.0000, 799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(63, 21, 'OLIVE-GREEN-OVERSIZED-TEE-1', 'daff7450-847b-4a18-9f66-3cc4d5108dfb', 'M', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(64, 21, 'OLIVE-GREEN-OVERSIZED-TEE-2', 'baeb601c-827b-4ed3-958b-70e8d22c4e85', 'L', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(65, 21, 'OLIVE-GREEN-OVERSIZED-TEE-3', 'bdcb5bd6-936c-406a-a047-8d890717cab1', 'XL', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(66, 22, 'PACK-3-BASIC-TEES-1', '2e9ce20a-daee-43ed-a352-7f57e465a3a3', 'M', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(67, 22, 'PACK-3-BASIC-TEES-2', 'c11cb00b-3f5b-47d4-86cc-f7685138cb50', 'L', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(68, 22, 'PACK-3-BASIC-TEES-3', '353af77d-61c0-4c64-b5d6-88ec35b4de37', 'XL', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(69, 23, 'NAVY-BLUE-POLO-TSHIRT-1', '77a2473d-4cb7-42af-8c96-9acb02d2e001', 'S', NULL, 899.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(70, 23, 'NAVY-BLUE-POLO-TSHIRT-2', '3aa13c61-2a2a-4244-bcfc-cc92417c75ac', 'M', NULL, 899.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(71, 23, 'NAVY-BLUE-POLO-TSHIRT-3', '5a9d5f1f-e24c-40ae-8552-ffe06d5ceeea', 'L', NULL, 899.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(72, 23, 'NAVY-BLUE-POLO-TSHIRT-4', 'fdeb9b6f-ea9a-40f9-abe7-1d193cca4185', 'XL', NULL, 899.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(73, 23, 'NAVY-BLUE-POLO-TSHIRT-5', 'b0f5a3ba-0fbd-44cd-8df0-fa8aeee60f82', 'XXL', NULL, 989.0000, 1649.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(74, 24, 'WHITE-FORMAL-SHIRT-1', '41941ae5-4ff0-4245-9376-ace15d411d89', 'S', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(75, 24, 'WHITE-FORMAL-SHIRT-2', '1c0de1f1-3900-4912-b6d4-1f5f39b1626e', 'M', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(76, 24, 'WHITE-FORMAL-SHIRT-3', '6ec43265-6bdd-4b76-b253-4a906afef070', 'L', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(77, 24, 'WHITE-FORMAL-SHIRT-4', '15a9560c-9204-4fd0-bd7c-0e3d42b1046c', 'XL', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(78, 24, 'WHITE-FORMAL-SHIRT-5', '4543c28e-2e64-4e15-b482-6610577fb29a', 'XXL', NULL, 1055.0000, 1759.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(79, 25, 'BLUE-CHECKERED-CASUAL-SHIRT-1', '48d27a8b-c54d-4cb9-b85f-aa487fe70d79', 'S', NULL, 839.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(80, 25, 'BLUE-CHECKERED-CASUAL-SHIRT-2', '2e7b9a5d-4269-4b51-b790-1e448df8312a', 'M', NULL, 839.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(81, 25, 'BLUE-CHECKERED-CASUAL-SHIRT-3', 'ce68f500-c5e7-4abb-b084-126f189a09b5', 'L', NULL, 839.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(82, 25, 'BLUE-CHECKERED-CASUAL-SHIRT-4', 'bc35ab32-7587-4c54-9353-dce94e1e078a', 'XL', NULL, 839.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(83, 26, 'BLACK-SLIM-FIT-SHIRT-1', '03c2f9c8-dea6-49f3-a823-34a60cbd4546', 'M', NULL, 1019.0000, 1699.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(84, 26, 'BLACK-SLIM-FIT-SHIRT-2', '2b0e1f2b-9034-4f1b-b7b8-71e59d5f079b', 'L', NULL, 1019.0000, 1699.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(85, 26, 'BLACK-SLIM-FIT-SHIRT-3', '8e79a57b-c27f-4e3d-ab64-4d68dfba539c', 'XL', NULL, 1019.0000, 1699.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(86, 27, 'LINEN-SUMMER-SHIRT-1', 'c383b824-1657-4c7d-a468-00e4c96302a6', 'S', NULL, 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(87, 27, 'LINEN-SUMMER-SHIRT-2', '4a055866-e197-448b-9107-447e6a593b79', 'M', NULL, 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(88, 27, 'LINEN-SUMMER-SHIRT-3', '100d8039-12c6-490e-9341-197ad2b3ce8b', 'L', NULL, 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(89, 27, 'LINEN-SUMMER-SHIRT-4', '0c1ed6a6-91af-46d4-a691-7c3b0b991a22', 'XL', NULL, 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:40', '2026-06-26 06:05:40', NULL),
(90, 28, 'PINK-CASUAL-SHIRT-1', 'd8d3c274-1512-4fff-a42e-036a08e469ff', 'M', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(91, 28, 'PINK-CASUAL-SHIRT-2', '24eca3d2-e29c-4d97-a009-f1b0b2f24e41', 'L', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(92, 28, 'PINK-CASUAL-SHIRT-3', '453d8f84-90b7-4053-a974-ba75ff2365a2', 'XL', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(93, 29, 'DENIM-WESTERN-SHIRT-1', '399b2bf8-9ca8-4b80-99b0-6197f9fe21bf', 'M', NULL, 1079.0000, 1799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(94, 29, 'DENIM-WESTERN-SHIRT-2', '35543282-4adb-41ba-8726-16fe296f980b', 'L', NULL, 1079.0000, 1799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(95, 29, 'DENIM-WESTERN-SHIRT-3', 'ddeef680-121c-4623-8c13-2c3182fc8cf0', 'XL', NULL, 1079.0000, 1799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(96, 29, 'DENIM-WESTERN-SHIRT-4', '9f7f6aba-3f06-4fc9-a870-7448798f8569', 'XXL', NULL, 1187.0000, 1979.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(97, 30, 'PRINTED-HAWAIIAN-SHIRT-1', '516540d1-8b5b-461e-a3f0-8d466d789864', 'S', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(98, 30, 'PRINTED-HAWAIIAN-SHIRT-2', '85f6763a-8ad6-457d-9f35-e2a7177e2e70', 'M', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(99, 30, 'PRINTED-HAWAIIAN-SHIRT-3', '0b20c733-761b-4290-8bb7-207fa99efe52', 'L', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(100, 30, 'PRINTED-HAWAIIAN-SHIRT-4', '7cfeaf0d-f217-4b21-8faa-0af660ad9f55', 'XL', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(101, 31, 'WHITE-COTTON-PANJABI-1', '37960c56-dec1-48f2-8b26-c7581cade6d7', 'M', NULL, 899.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(102, 31, 'WHITE-COTTON-PANJABI-2', 'edc008bc-af53-4b07-89a1-b92711ee81a2', 'L', NULL, 899.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(103, 31, 'WHITE-COTTON-PANJABI-3', 'fd3c8e32-a616-4049-a652-c84b123f87d6', 'XL', NULL, 899.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(104, 31, 'WHITE-COTTON-PANJABI-4', '2ef8e6fa-36f7-4d50-985a-9f543b04defb', 'XXL', NULL, 989.0000, 1649.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(105, 31, 'WHITE-COTTON-PANJABI-5', '9a8989f2-b6bd-4cc4-b6a8-cf08c9c1a9a5', '3XL', NULL, 989.0000, 1649.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(106, 32, 'ROYAL-BLUE-PANJABI-BUTTON-1', '229c2ac0-a65e-4b74-81ef-333dafcf3837', 'M', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(107, 32, 'ROYAL-BLUE-PANJABI-BUTTON-2', 'd269d62f-0782-4826-ae1a-1f998a4cb43b', 'L', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(108, 32, 'ROYAL-BLUE-PANJABI-BUTTON-3', '5b6f861e-6216-4e55-943d-7d5c62778b2f', 'XL', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(109, 32, 'ROYAL-BLUE-PANJABI-BUTTON-4', '49ee7583-f47b-46cd-bb56-33d95be1c4c4', 'XXL', NULL, 1649.0000, 2749.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(110, 33, 'MAROON-SILK-PANJABI-1', 'b6178552-2530-4c57-ad04-057f49eb4653', 'L', NULL, 2399.0000, 3999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(111, 33, 'MAROON-SILK-PANJABI-2', '89e4bfad-9a24-41d7-9057-5e5041dda8c8', 'XL', NULL, 2399.0000, 3999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(112, 33, 'MAROON-SILK-PANJABI-3', 'f7656e3c-bf25-4cf7-8d14-5cdfa9355734', 'XXL', NULL, 2639.0000, 4399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(113, 34, 'BLACK-PATHOR-PANJABI-1', 'a39e2e99-13ae-4cf1-8bae-c01006b5cd0c', 'L', NULL, 2759.0000, 4599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(114, 34, 'BLACK-PATHOR-PANJABI-2', '07745155-b3eb-479e-8fc6-ce980aaa4fb2', 'XL', NULL, 2759.0000, 4599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(115, 34, 'BLACK-PATHOR-PANJABI-3', '308bdb0b-3f49-46d5-b0a8-ea621217c280', 'XXL', NULL, 3035.0000, 5059.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(116, 35, 'OFFWHITE-KURTA-POCKET-1', '677a11e1-c8a0-4156-9355-984339c61250', 'M', NULL, 719.0000, 1199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(117, 35, 'OFFWHITE-KURTA-POCKET-2', '2562a950-9961-4083-b11b-08f76d42a342', 'L', NULL, 719.0000, 1199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(118, 35, 'OFFWHITE-KURTA-POCKET-3', '76241ad7-5769-4031-8006-6322bbd04709', 'XL', NULL, 719.0000, 1199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(119, 35, 'OFFWHITE-KURTA-POCKET-4', 'f0381069-59c1-4cca-a248-6215983e31ab', 'XXL', NULL, 791.0000, 1319.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(120, 36, 'GREEN-EMBROIDERED-PANJABI-SET-1', 'bb29c809-d793-4181-8995-845ae593147a', 'L', NULL, 2099.0000, 3499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(121, 36, 'GREEN-EMBROIDERED-PANJABI-SET-2', '4e12cf79-ce19-4130-a5f9-55804dd485e5', 'XL', NULL, 2099.0000, 3499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(122, 36, 'GREEN-EMBROIDERED-PANJABI-SET-3', '8d8f46cd-e102-4acf-929f-fff0cc8d1c56', 'XXL', NULL, 2309.0000, 3849.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(123, 37, 'SLIM-FIT-BLUE-JEANS-1', 'f5eeb647-61f8-4827-b050-6b7e919350cf', '28', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(124, 37, 'SLIM-FIT-BLUE-JEANS-2', '48301b30-6fcb-40ba-bc75-5e84effa7a9c', '30', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(125, 37, 'SLIM-FIT-BLUE-JEANS-3', 'c81de628-18b9-4fa6-8fd6-b4f25e1e4fcb', '32', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(126, 37, 'SLIM-FIT-BLUE-JEANS-4', '268e7e42-b353-4c55-b5a5-07331e12e0e6', '34', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(127, 37, 'SLIM-FIT-BLUE-JEANS-5', '1b8e8abf-b8ee-43cc-9518-9864d7e0c57c', '36', NULL, 1319.0000, 2199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(128, 38, 'BLACK-CHINO-TROUSERS-1', '0ed382f5-6264-4691-9fc8-5b258c73f4c8', '30', NULL, 1079.0000, 1799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(129, 38, 'BLACK-CHINO-TROUSERS-2', '3f133d3b-3932-4113-94d0-8a5a3f875d6b', '32', NULL, 1079.0000, 1799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(130, 38, 'BLACK-CHINO-TROUSERS-3', '3a81b8b8-db31-484e-b09a-999ddc63212e', '34', NULL, 1079.0000, 1799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(131, 38, 'BLACK-CHINO-TROUSERS-4', '88119c47-1c68-41e5-a035-0711339b2c21', '36', NULL, 1187.0000, 1979.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(132, 39, 'GREY-JOGGER-PANTS-1', '980b5e1f-6ffb-4a06-a25e-d3e567d10cf9', 'M', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(133, 39, 'GREY-JOGGER-PANTS-2', '5d3a8545-fbca-43b6-a61f-2ac34a693cca', 'L', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(134, 39, 'GREY-JOGGER-PANTS-3', 'cc626ffd-9674-46e6-9dac-261eaad10c3a', 'XL', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(135, 40, 'BROWN-CARGO-PANTS-1', '80e734ec-101c-4b7d-95d2-04a8a9e5533b', '30', NULL, 1019.0000, 1699.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(136, 40, 'BROWN-CARGO-PANTS-2', '81723f55-0010-4a52-81f4-911d10562928', '32', NULL, 1019.0000, 1699.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(137, 40, 'BROWN-CARGO-PANTS-3', 'b63f6a84-3626-4fb7-a04c-366006c17444', '34', NULL, 1019.0000, 1699.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(138, 40, 'BROWN-CARGO-PANTS-4', '7a6be724-a94b-422a-b40a-cfdfe10140e2', '36', NULL, 1121.0000, 1869.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(139, 41, 'WHITE-LINEN-TROUSERS-1', 'c1df377e-161e-49c8-a26f-b765c3078d29', '30', NULL, 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(140, 41, 'WHITE-LINEN-TROUSERS-2', '0946457c-6bae-4231-86b3-11a5d600ba5e', '32', NULL, 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(141, 41, 'WHITE-LINEN-TROUSERS-3', 'b0c5c3c0-1b20-4f95-9efe-084b997f2830', '34', NULL, 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(142, 42, 'BLACK-SKINNY-JEANS-1', 'd8229db8-7682-4af7-830b-af32366d3703', '28', NULL, 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(143, 42, 'BLACK-SKINNY-JEANS-2', 'cd5871d6-2037-488d-b307-c1aaad860acf', '30', NULL, 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(144, 42, 'BLACK-SKINNY-JEANS-3', '5476fdd4-5574-4d25-880b-2bb76c17746e', '32', NULL, 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(145, 42, 'BLACK-SKINNY-JEANS-4', '033485dc-81be-42aa-b795-0268c1cec3b2', '34', NULL, 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(146, 43, 'NAVY-BLUE-SLIM-FIT-SUIT-1', '12ca9c47-b8ce-4005-a00e-943ec9d483e3', '38', NULL, 5399.0000, 8999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(147, 43, 'NAVY-BLUE-SLIM-FIT-SUIT-2', 'dc749e4e-e137-4455-a1b1-0afd83b09606', '40', NULL, 5399.0000, 8999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(148, 43, 'NAVY-BLUE-SLIM-FIT-SUIT-3', 'b2152c15-b587-48d8-9159-f19e6077906b', '42', NULL, 5399.0000, 8999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(149, 43, 'NAVY-BLUE-SLIM-FIT-SUIT-4', '5032ca30-55b8-4a30-b582-3e79458a3558', '44', NULL, 5939.0000, 9899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(150, 44, 'CHARCOAL-GREY-BLAZER-1', '38eb631a-c2d2-4249-98ea-71d8d442ad80', '38', NULL, 3299.0000, 5499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(151, 44, 'CHARCOAL-GREY-BLAZER-2', '182978d3-7330-4ac9-b036-3563ffbf2b20', '40', NULL, 3299.0000, 5499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(152, 44, 'CHARCOAL-GREY-BLAZER-3', '5e4e2dbf-f43b-4dbb-8456-56c70c5e7e92', '42', NULL, 3299.0000, 5499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(153, 44, 'CHARCOAL-GREY-BLAZER-4', '26f3ddef-79a7-4496-8220-4ebe7da2a325', '44', NULL, 3629.0000, 6049.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(154, 45, 'BLACK-FORMAL-SUIT-1', '24129e5f-4a8b-498b-9ae9-908510b5c7a4', '38', NULL, 5999.0000, 9999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(155, 45, 'BLACK-FORMAL-SUIT-2', '7a59780f-1fdd-46ca-a7e3-636510d9b3e5', '40', NULL, 5999.0000, 9999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(156, 45, 'BLACK-FORMAL-SUIT-3', '47003de2-c73c-471f-81ae-3b3ce97a47c8', '42', NULL, 5999.0000, 9999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(157, 45, 'BLACK-FORMAL-SUIT-4', '7152ff7a-9bb1-47b0-bd91-a21ee2ea7285', '44', NULL, 6599.0000, 10999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(158, 46, 'BEIGE-LINEN-SUIT-1', '6079e4f9-5271-457c-9e9e-f74223502c09', '38', NULL, 4799.0000, 7999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(159, 46, 'BEIGE-LINEN-SUIT-2', '2697f211-a4ed-46fd-aad7-654811168f85', '40', NULL, 4799.0000, 7999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(160, 46, 'BEIGE-LINEN-SUIT-3', '490bb2c5-ccd6-4298-a60c-48a4c1e6abd5', '42', NULL, 4799.0000, 7999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(161, 47, 'BLACK-BOMBER-JACKET-1', '6c99cf85-bbf9-4082-8dad-ad942ea86385', 'M', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(162, 47, 'BLACK-BOMBER-JACKET-2', '069254a2-aac1-4376-b91b-8db6fbb2c887', 'L', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(163, 47, 'BLACK-BOMBER-JACKET-3', '2e8c4c9b-07f4-4c5e-abf8-fcf9df00a421', 'XL', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(164, 47, 'BLACK-BOMBER-JACKET-4', '0af3bea7-248e-498a-b04e-36e9708a87c8', 'XXL', NULL, 1979.0000, 3299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(165, 48, 'GREY-HOODED-SWEATSHIRT-1', 'b5642358-d8f7-4ff8-b7cb-2a85e740d18d', 'S', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(166, 48, 'GREY-HOODED-SWEATSHIRT-2', '56cec82c-d57a-4df7-993f-409e35a351ea', 'M', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(167, 48, 'GREY-HOODED-SWEATSHIRT-3', 'b79ab5f9-e7ed-46ee-b36d-d9af980b0492', 'L', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(168, 48, 'GREY-HOODED-SWEATSHIRT-4', 'b6686bf7-969a-4820-83b1-a5ce7ac909e2', 'XL', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(169, 48, 'GREY-HOODED-SWEATSHIRT-5', '62a97738-2788-4185-8fa6-2672b33ae25c', 'XXL', NULL, 1319.0000, 2199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(170, 49, 'MAROON-SWEATER-1', 'a61f0e50-207a-4dac-884b-74c5e9e56f36', 'M', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(171, 49, 'MAROON-SWEATER-2', '92094546-b404-4561-8bf1-28f19d7311a0', 'L', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(172, 49, 'MAROON-SWEATER-3', 'e66d18c1-7eaa-4c7e-a43c-e210d57e9b83', 'XL', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(173, 49, 'MAROON-SWEATER-4', '371412fc-9e58-4078-9bd1-e8e6c2d218b5', 'XXL', NULL, 1055.0000, 1759.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(174, 50, 'BROWN-LEATHER-JACKET-1', '2f8d98fc-bdfc-474e-96c2-b898d269d303', 'M', NULL, 2999.0000, 4999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(175, 50, 'BROWN-LEATHER-JACKET-2', 'b9d6bbad-a2ae-47e9-be6b-133b3dcd2316', 'L', NULL, 2999.0000, 4999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(176, 50, 'BROWN-LEATHER-JACKET-3', '596ee19f-4aae-4dc6-afa1-a25bb028d936', 'XL', NULL, 2999.0000, 4999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(177, 50, 'BROWN-LEATHER-JACKET-4', '5586adbd-1bb6-4338-9152-077fd047d02f', 'XXL', NULL, 3299.0000, 5499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(178, 51, 'NAVY-PUFFER-JACKET-1', '258c0bce-5b82-47f6-8bc8-4173ab45e549', 'M', NULL, 2399.0000, 3999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(179, 51, 'NAVY-PUFFER-JACKET-2', '4d28d459-48b6-4d4f-9987-1e2651ee041d', 'L', NULL, 2399.0000, 3999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(180, 51, 'NAVY-PUFFER-JACKET-3', 'f3247937-17ea-431c-ae74-b19360bededd', 'XL', NULL, 2399.0000, 3999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(181, 52, 'PACK-5-COTTON-BOXERS-1', '94cf6b7c-06a5-42e1-bda2-74e9094fc385', 'M', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(182, 52, 'PACK-5-COTTON-BOXERS-2', '5e8d3dbf-0770-48a5-9d20-9e81474dd01f', 'L', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(183, 52, 'PACK-5-COTTON-BOXERS-3', '8673adcb-ce70-468b-bedd-d9a0e64172de', 'XL', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(184, 52, 'PACK-5-COTTON-BOXERS-4', 'f1cc26d1-1fca-47a2-84bc-f5193d0728c1', 'XXL', NULL, 659.0000, 1099.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(185, 53, 'PACK-6-ANKLE-SOCKS-1', '3037793e-561a-4ee7-9f34-a2f8f34eb931', 'One Size', NULL, 299.0000, 499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(186, 54, 'COTTON-VEST-BANIAN-3-PACK-1', 'a6869808-0636-4d2f-b959-67caaefdb369', 'M', NULL, 359.0000, 599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(187, 54, 'COTTON-VEST-BANIAN-3-PACK-2', 'd151a82e-abe3-4be7-8f82-b2c840024b6c', 'L', NULL, 359.0000, 599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(188, 54, 'COTTON-VEST-BANIAN-3-PACK-3', 'e3d81188-c8ee-4627-9274-3f5258c2062f', 'XL', NULL, 359.0000, 599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(189, 54, 'COTTON-VEST-BANIAN-3-PACK-4', 'c46b4856-c4b8-4c5d-b94c-d94ae7d9e4c3', 'XXL', NULL, 395.0000, 659.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(190, 55, 'WHITE-PAJAMA-LUNGI-1', '02016759-6ef6-4790-8815-a8272565f0b2', 'M', NULL, 539.0000, 899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(191, 55, 'WHITE-PAJAMA-LUNGI-2', 'e1a13e18-b230-491a-b906-75f97f065bdf', 'L', NULL, 539.0000, 899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(192, 55, 'WHITE-PAJAMA-LUNGI-3', 'c545e8b8-c57e-41a0-91c1-a6f871a14b5e', 'XL', NULL, 539.0000, 899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(193, 55, 'WHITE-PAJAMA-LUNGI-4', 'dfdd6fd4-4579-440c-b44b-898d66119726', 'XXL', NULL, 593.0000, 989.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(194, 56, 'RED-GAMCHA-TOWEL-SET-1', 'a3894ca2-f4ce-4f55-8892-340c5f642b0b', 'One Size', NULL, 239.0000, 399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(195, 57, 'WHITE-TUPI-PRAYER-CAP-1', 'c0796212-c4e8-44fc-9926-889d065be7cb', 'One Size', NULL, 119.0000, 199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(196, 58, 'RED-BRIDAL-SILK-SAREE-1', '870937be-2313-4f34-a6cb-9bce1f3aa75c', '5.5 Yards', NULL, 5399.0000, 8999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(197, 58, 'RED-BRIDAL-SILK-SAREE-2', 'b41c8bd3-e927-47a7-8f89-99807390611f', '6 Yards', NULL, 5399.0000, 8999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(198, 59, 'BLUE-TANGAIL-COTTON-SAREE-1', '9bb564b3-19e6-44dc-bf2a-00b6eef70e34', '5.5 Yards', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(199, 59, 'BLUE-TANGAIL-COTTON-SAREE-2', 'e2c44e75-fc83-403e-bc0e-4e421284a526', '6 Yards', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(200, 60, 'GREEN-GEORGETTE-SAREE-1', '700b09c6-0641-4a6a-b1d2-8ffe52407e36', '5.5 Yards', NULL, 2399.0000, 3999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(201, 61, 'WHITE-KHADI-SAREE-RED-BORDER-1', 'a8eb6c40-50f1-42ca-baaf-a12b951993bc', '5.5 Yards', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(202, 62, 'PINK-ORGANZA-SAREE-1', 'c663c742-ba13-48e6-9b6c-5e6f1594aab0', '5.5 Yards', NULL, 3299.0000, 5499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(203, 63, 'PURPLE-SILK-SAREE-1', '297ad5ba-a608-46e3-8f79-0299f81905de', '5.5 Yards', NULL, 4199.0000, 6999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(204, 63, 'PURPLE-SILK-SAREE-2', 'bb0d1fb5-960e-45f7-b61e-aef54fe216c9', '6 Yards', NULL, 4199.0000, 6999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:41', '2026-06-26 06:05:41', NULL),
(205, 64, 'YELLOW-COTTON-SAREE-1', 'da53a2a9-3627-43f5-af26-344ac2481672', '5.5 Yards', NULL, 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(206, 65, 'BLUE-ANARKALI-SALWAR-1', '9d6f2fba-1ba4-4356-8862-586897016755', 'S', NULL, 2399.0000, 3999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(207, 65, 'BLUE-ANARKALI-SALWAR-2', 'c24cab88-54bf-49dd-91be-bbd31ece9948', 'M', NULL, 2399.0000, 3999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(208, 65, 'BLUE-ANARKALI-SALWAR-3', 'ba6751c4-ec9c-41f3-be72-b86ebd0b8fef', 'L', NULL, 2399.0000, 3999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(209, 65, 'BLUE-ANARKALI-SALWAR-4', '6355ef0a-8af8-40f9-bd46-8ec3bd6d08b7', 'XL', NULL, 2399.0000, 3999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(210, 65, 'BLUE-ANARKALI-SALWAR-5', '40c2c50a-3519-4253-8130-73aac6bd8468', 'XXL', NULL, 2639.0000, 4399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(211, 66, 'GREEN-PRINTED-SALWAR-1', '3651c1d1-eff7-4e15-9333-3a5bd9f242d2', 'S', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(212, 66, 'GREEN-PRINTED-SALWAR-2', '0d9bb399-b8d0-4144-995a-c1560703539a', 'M', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(213, 66, 'GREEN-PRINTED-SALWAR-3', 'd789c122-8234-4835-9bf1-45826750969d', 'L', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(214, 66, 'GREEN-PRINTED-SALWAR-4', 'b388d716-4c6e-42fc-a517-d55d701f4b76', 'XL', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(215, 67, 'BLACK-EMBROIDERED-SALWAR-1', 'c22de5ba-cf46-41f9-9191-1014799167ad', 'S', NULL, 2699.0000, 4499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(216, 67, 'BLACK-EMBROIDERED-SALWAR-2', 'e049966f-4344-4158-afbf-852419792566', 'M', NULL, 2699.0000, 4499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(217, 67, 'BLACK-EMBROIDERED-SALWAR-3', 'b35d5709-8ca4-4ad3-8b91-00108825c5cb', 'L', NULL, 2699.0000, 4499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(218, 67, 'BLACK-EMBROIDERED-SALWAR-4', '0f2624e2-0e3f-4517-b41f-31ed63c52380', 'XL', NULL, 2699.0000, 4499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(219, 68, 'ORANGE-COTTON-SALWAR-1', 'ce3cbcf8-660b-4413-be51-eea80e3a76f8', 'M', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(220, 68, 'ORANGE-COTTON-SALWAR-2', '4c1c4e01-62d5-4166-ba75-2966b17b6337', 'L', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(221, 68, 'ORANGE-COTTON-SALWAR-3', '77910dc7-8462-4396-8177-a846cf2af855', 'XL', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(222, 69, 'WHITE-PAKISTANI-SUIT-1', 'd40cfc75-5bac-4714-938d-fcda7dedd694', 'S', NULL, 2099.0000, 3499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(223, 69, 'WHITE-PAKISTANI-SUIT-2', '60055afb-27a8-4ce2-9c8a-d48aa64ab0b3', 'M', NULL, 2099.0000, 3499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(224, 69, 'WHITE-PAKISTANI-SUIT-3', '9f36d1fe-98bd-44fc-8209-da4393d2c5da', 'L', NULL, 2099.0000, 3499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(225, 69, 'WHITE-PAKISTANI-SUIT-4', '559bcdb8-9018-4916-867e-d487bcce8bf2', 'XL', NULL, 2099.0000, 3499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(226, 70, 'MINT-GREEN-ANARKALI-1', '27867997-439a-4d60-95a4-9cbba03d206e', 'S', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(227, 70, 'MINT-GREEN-ANARKALI-2', '82c6a1a9-b38e-47dd-8743-5df27739b9c3', 'M', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(228, 70, 'MINT-GREEN-ANARKALI-3', '34e2d57d-af50-46f6-a05c-0fa3a1f88751', 'L', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(229, 70, 'MINT-GREEN-ANARKALI-4', '52662f9f-dc5e-4681-8f56-d21030741710', 'XL', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(230, 71, 'WHITE-COTTON-KURTI-EMBROIDERED-1', '879f8f73-c762-4080-88a8-105abf48a225', 'S', NULL, 899.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(231, 71, 'WHITE-COTTON-KURTI-EMBROIDERED-2', 'f9e7edf9-e147-4626-8647-cf6b7af7d37f', 'M', NULL, 899.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(232, 71, 'WHITE-COTTON-KURTI-EMBROIDERED-3', '148610bf-afac-47cc-a8b6-ee15b9749536', 'L', NULL, 899.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL);
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `name`, `attributes`, `cost_price`, `sale_price`, `compare_at_price`, `weight_grams`, `length_mm`, `width_mm`, `height_mm`, `track_inventory`, `allow_backorder`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(233, 71, 'WHITE-COTTON-KURTI-EMBROIDERED-4', '1fe7d2ff-d5e5-4ef8-bf74-abd356d597db', 'XL', NULL, 899.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(234, 72, 'MAROON-RAYON-KURTI-1', '2b13b78d-9915-42cd-b873-a6220de61ebe', 'S', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(235, 72, 'MAROON-RAYON-KURTI-2', '14596001-53ba-40e3-a317-9644258b7051', 'M', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(236, 72, 'MAROON-RAYON-KURTI-3', '549bf595-ecee-4d0f-9e46-b91160167438', 'L', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(237, 72, 'MAROON-RAYON-KURTI-4', '523df97d-384e-449c-96e0-7dd12c1c4b86', 'XL', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(238, 73, 'BLUE-DENIM-KURTI-1', '6c40ee85-2259-45d6-a9ea-b2d0c211211c', 'M', NULL, 1079.0000, 1799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(239, 73, 'BLUE-DENIM-KURTI-2', '4da36273-b9e8-4601-a640-013121b2f201', 'L', NULL, 1079.0000, 1799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(240, 73, 'BLUE-DENIM-KURTI-3', 'c9add7fc-d1bf-4a78-a3b3-6d5f53d984f7', 'XL', NULL, 1079.0000, 1799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(241, 74, 'BLACK-LONG-KURTI-SIDE-SLITS-1', 'd084e569-c7c8-4058-b88b-2f16614c464f', 'M', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(242, 74, 'BLACK-LONG-KURTI-SIDE-SLITS-2', '1086b840-d8b7-4b1a-abd5-f0afd7b6d532', 'L', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(243, 74, 'BLACK-LONG-KURTI-SIDE-SLITS-3', '162516e1-4a15-4a64-a1e7-951a6c5f1b96', 'XL', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(244, 75, 'PINK-COTTON-ALINE-KURTI-1', '0e858018-ea4e-46c0-93dd-17951e94ccb5', 'S', NULL, 839.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(245, 75, 'PINK-COTTON-ALINE-KURTI-2', '0adb6a02-f315-4632-a598-dfa075694a6b', 'M', NULL, 839.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(246, 75, 'PINK-COTTON-ALINE-KURTI-3', 'da04d5f1-6537-4999-b5d3-8acd369a3e57', 'L', NULL, 839.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(247, 75, 'PINK-COTTON-ALINE-KURTI-4', '9768dc2a-ab30-4ea0-9cfd-f2acb78f0ec9', 'XL', NULL, 839.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(248, 76, 'RED-BODYCON-DRESS-1', '76035af3-b40c-41f9-9788-142a42a6570a', 'S', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(249, 76, 'RED-BODYCON-DRESS-2', '300b69f5-dc1a-4248-bc77-eb0a120c01ca', 'M', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(250, 76, 'RED-BODYCON-DRESS-3', 'c9f44e09-3cef-4caa-8f7e-9fcb06c627d7', 'L', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(251, 76, 'RED-BODYCON-DRESS-4', '799e3ba6-9577-4039-a37a-6437131ea00f', 'XL', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(252, 77, 'BLACK-LACE-MIDI-DRESS-1', 'f77b92bd-4d10-44e4-bf45-600dd8fa8fbe', 'S', NULL, 2099.0000, 3499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(253, 77, 'BLACK-LACE-MIDI-DRESS-2', '5a075c08-ce90-4b74-b578-fc7d090569f9', 'M', NULL, 2099.0000, 3499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(254, 77, 'BLACK-LACE-MIDI-DRESS-3', '414ad737-ebff-4730-b1eb-3c5aa8e2454a', 'L', NULL, 2099.0000, 3499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(255, 78, 'FLORAL-MAXI-DRESS-1', 'eba1133e-bce2-465f-ae22-1ba32ef63087', 'S', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(256, 78, 'FLORAL-MAXI-DRESS-2', '29be94a1-7972-456d-80e4-9af82ad0ab8a', 'M', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(257, 78, 'FLORAL-MAXI-DRESS-3', 'a87fd69b-6b95-4258-910e-d261b1004727', 'L', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(258, 78, 'FLORAL-MAXI-DRESS-4', 'b7452deb-d258-44e1-aad2-34d71a577b8d', 'XL', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(259, 79, 'BLUE-SHIRT-DRESS-1', '1e35481e-9b8d-4294-88de-a853c5ab5575', 'S', NULL, 1319.0000, 2199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(260, 79, 'BLUE-SHIRT-DRESS-2', '543656ac-950a-41e6-84b9-c8880b3b4855', 'M', NULL, 1319.0000, 2199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(261, 79, 'BLUE-SHIRT-DRESS-3', '404f9261-2fbe-4315-9427-dbcf2dec38b7', 'L', NULL, 1319.0000, 2199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(262, 79, 'BLUE-SHIRT-DRESS-4', '3274cf6e-ac95-4345-9e27-26f0dd2d3d6d', 'XL', NULL, 1319.0000, 2199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(263, 80, 'WHITE-SUMMER-DRESS-1', 'f37d6bc5-34b9-45e2-a5f0-13a8138ec29a', 'S', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(264, 80, 'WHITE-SUMMER-DRESS-2', '36965447-cab7-4073-a2b3-e25fcd192530', 'M', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(265, 80, 'WHITE-SUMMER-DRESS-3', 'a054a5e5-614a-4983-8954-dd86f7791f00', 'L', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(266, 81, 'WHITE-COTTON-BLOUSE-1', '68109c54-867f-466d-8d4e-346019ded608', 'S', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(267, 81, 'WHITE-COTTON-BLOUSE-2', '14d5a446-b5bb-4fd6-9e5e-a749f0d47ce1', 'M', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(268, 81, 'WHITE-COTTON-BLOUSE-3', 'd0079478-717d-4323-8397-39bb79cb1a49', 'L', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(269, 81, 'WHITE-COTTON-BLOUSE-4', '8fd14844-a311-46d2-953c-7d28f8406608', 'XL', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(270, 82, 'PRINTED-CROP-TOP-1', '3fa6a246-9bee-4ab3-9916-d35ad410f559', 'S', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(271, 82, 'PRINTED-CROP-TOP-2', 'e49007c7-94a8-4366-91cc-7965de451a10', 'M', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(272, 82, 'PRINTED-CROP-TOP-3', '8a2ffe77-bed7-4bc7-87f1-9297e41240f0', 'L', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(273, 83, 'BLACK-PEPLUM-TOP-1', '690736ba-1599-498e-a0da-893f54a4dcce', 'S', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(274, 83, 'BLACK-PEPLUM-TOP-2', '1a87aa83-2d27-4791-97d3-665767cc3f87', 'M', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(275, 83, 'BLACK-PEPLUM-TOP-3', '7934df68-5536-43de-bc17-3b14cbfd8dd8', 'L', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(276, 83, 'BLACK-PEPLUM-TOP-4', '612f49b9-5af1-453f-9b21-baa67a1907ee', 'XL', NULL, 959.0000, 1599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:42', '2026-06-26 06:05:42', NULL),
(277, 84, 'STRIPED-OFF-SHOULDER-TOP-1', '1d3f28dd-e2a8-4781-94fc-61a487c9117b', 'S', NULL, 719.0000, 1199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(278, 84, 'STRIPED-OFF-SHOULDER-TOP-2', '8ad1d595-a18b-44cc-96c7-2a07ee5c9059', 'M', NULL, 719.0000, 1199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(279, 84, 'STRIPED-OFF-SHOULDER-TOP-3', '73dbb475-32bc-4002-b232-59423003ef21', 'L', NULL, 719.0000, 1199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(280, 85, 'SILK-CAMISOLE-TOP-1', '9f984e12-3201-43df-984e-f6f1621aad65', 'S', NULL, 1079.0000, 1799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(281, 85, 'SILK-CAMISOLE-TOP-2', '8889dbee-b90f-4c69-b40e-a5dc2017f9b8', 'M', NULL, 1079.0000, 1799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(282, 85, 'SILK-CAMISOLE-TOP-3', 'b2d9fa63-c75e-4f35-86b1-8c928f70520e', 'L', NULL, 1079.0000, 1799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(283, 86, 'BLUE-SKINNY-JEANS-WOMEN-1', '6ded9abb-10ae-479d-a177-5cf4be5bc141', '26', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 08:36:16', NULL),
(284, 86, 'BLUE-SKINNY-JEANS-WOMEN-2', 'e378f1d6-5f91-4bde-8738-4b75219cbd5b', '28', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 08:36:17', NULL),
(285, 86, 'BLUE-SKINNY-JEANS-WOMEN-3', '77799ae3-e45b-4c74-a3cc-388fef4b3e74', '30', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 08:36:17', NULL),
(286, 86, 'BLUE-SKINNY-JEANS-WOMEN-4', 'a5f71df1-1f2d-4822-acb9-9fbbf83e910e', '32', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 1139.0000, 1899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 08:36:17', NULL),
(287, 87, 'BLACK-PALAZZO-PANTS-1', '50a2ff7f-4c8f-4b34-b928-33aba4d50916', 'S', NULL, 839.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(288, 87, 'BLACK-PALAZZO-PANTS-2', '185c4878-299a-4368-80b8-6542d2636d1b', 'M', NULL, 839.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(289, 87, 'BLACK-PALAZZO-PANTS-3', '97669311-fd38-473c-b816-a83f9770909d', 'L', NULL, 839.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(290, 87, 'BLACK-PALAZZO-PANTS-4', 'f21f450a-36b0-4286-9fa0-40b8b1dd820a', 'XL', NULL, 839.0000, 1399.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(291, 88, 'WHITE-TROUSERS-WOMEN-1', '4efe9eb6-0b89-4a35-ace4-6fc981a85d46', '26', NULL, 1019.0000, 1699.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(292, 88, 'WHITE-TROUSERS-WOMEN-2', '6fb8686e-af46-4406-9996-72e99ed5893e', '28', NULL, 1019.0000, 1699.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(293, 88, 'WHITE-TROUSERS-WOMEN-3', 'c554a6b2-03b2-41db-aa9d-da7bdec17cc1', '30', NULL, 1019.0000, 1699.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(294, 88, 'WHITE-TROUSERS-WOMEN-4', '66cc83ad-4f1d-4246-81f6-1843aff86c4a', '32', NULL, 1019.0000, 1699.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(295, 89, 'DENIM-SHORTS-WOMEN-1', '4650ad95-6583-4254-b86f-01a50be7e236', 'S', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(296, 89, 'DENIM-SHORTS-WOMEN-2', '0e84c66a-8639-48a8-a839-dc8bb4765abd', 'M', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(297, 89, 'DENIM-SHORTS-WOMEN-3', 'ba819de9-d2a7-4f60-ae23-e2e166c6616a', 'L', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(298, 90, 'PRINTED-LEGGINGS-1', '4531cd5f-6b8d-44a1-8867-0f9396233470', 'S', NULL, 479.0000, 799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(299, 90, 'PRINTED-LEGGINGS-2', '6ef7ddbf-c8c4-4ffc-8c86-8d533e4fc0aa', 'M', NULL, 479.0000, 799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(300, 90, 'PRINTED-LEGGINGS-3', '9840bfdc-ac6e-44c7-aafc-d07b9e59db47', 'L', NULL, 479.0000, 799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(301, 90, 'PRINTED-LEGGINGS-4', '3d666b31-9de5-47ab-a80b-a27295b678c2', 'XL', NULL, 479.0000, 799.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(302, 91, 'CHIFFON-HIJAB-SET-PINK-1', '29b81c90-015a-4814-82d5-63530c5c61ea', 'One Size', NULL, 539.0000, 899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(303, 92, 'COTTON-HIJAB-MULTIPACK-1', 'ca4cfeb5-b71a-4e19-915c-936c64f6213a', 'One Size', NULL, 899.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(304, 93, 'EMBROIDERED-NET-HIJAB-1', '1d631210-1eb9-4935-bf01-4764ef922d5a', 'One Size', NULL, 419.0000, 699.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(305, 94, 'SILK-SATIN-HIJAB-NAVY-1', 'a0e79c36-954d-4267-b44c-f5822f873afd', 'One Size', NULL, 659.0000, 1099.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(306, 95, 'BLACK-ABAYA-EMBROIDERED-1', 'f8877f43-5c34-41c1-b0ae-93c68b65119a', 'S', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(307, 95, 'BLACK-ABAYA-EMBROIDERED-2', '69a7ac9a-a6b5-406b-8b38-387f14a9986f', 'M', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(308, 95, 'BLACK-ABAYA-EMBROIDERED-3', '4b42a3fd-515c-4a53-aa55-0d67e30dc7c9', 'L', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(309, 95, 'BLACK-ABAYA-EMBROIDERED-4', 'c872b867-3b1d-453f-be04-3b5666bbd269', 'XL', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(310, 96, 'BURGUNDY-OPEN-ABAYA-1', '3639f0a8-c16b-49e2-bd3c-7225d791022a', 'M', NULL, 2099.0000, 3499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(311, 96, 'BURGUNDY-OPEN-ABAYA-2', '12e8234f-f784-4b21-8f0f-9f84e82132b9', 'L', NULL, 2099.0000, 3499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(312, 96, 'BURGUNDY-OPEN-ABAYA-3', 'cb748d21-c907-4325-8c49-25eeec584bbe', 'XL', NULL, 2099.0000, 3499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(313, 97, 'NAVY-BLUE-BURQA-1', '9b8fb98f-f8f1-47f4-92ea-7166651b7689', 'M', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(314, 97, 'NAVY-BLUE-BURQA-2', 'e4f3cc77-9283-4736-aa8f-b57b0a572e31', 'L', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(315, 97, 'NAVY-BLUE-BURQA-3', '3ed3b1e1-6c7b-4ea0-af4d-bb703ec27a80', 'XL', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(316, 97, 'NAVY-BLUE-BURQA-4', 'b9d14f93-0153-46d3-b13a-c3761457fb9e', 'XXL', NULL, 1649.0000, 2749.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(317, 98, 'BROWN-LEATHER-BACKPACK-1', 'b145752a-d693-47a1-8e66-3fb4b8f40566', 'One Size', NULL, 2099.0000, 3499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(318, 99, 'BLACK-HANDBAG-TOTE-1', 'b2bea5f9-ae46-4933-994c-114e350c4a2e', 'One Size', NULL, 1499.0000, 2499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(319, 100, 'SILVER-ANALOG-WATCH-1', '4ea657e9-87fd-437f-9479-435ff5344369', 'One Size', NULL, 1199.0000, 1999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(320, 101, 'GOLD-DIGITAL-SMART-WATCH-1', 'b0741ecf-e552-4bb9-b5da-6775cfd2731b', 'One Size', NULL, 2399.0000, 3999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(321, 102, 'GOLD-PLATED-NECKLACE-SET-1', '9b6a61f6-4246-42da-a0be-788dcf045615', 'One Size', NULL, 1799.0000, 2999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(322, 103, 'TRADITIONAL-BANGLES-SET-1', 'e949a7b5-fda1-4b86-aa81-3e16694c89fe', 'One Size', NULL, 299.0000, 499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(323, 104, 'BLACK-LEATHER-BELT-1', '891e2a0e-33f4-450b-bca8-ba9672d7ebeb', '28', NULL, 539.0000, 899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(324, 104, 'BLACK-LEATHER-BELT-2', 'aaf64e68-ca58-4d09-b572-cb80a7b6ac53', '30', NULL, 539.0000, 899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(325, 104, 'BLACK-LEATHER-BELT-3', 'cfb52800-1895-4762-bc30-43249fceebbe', '32', NULL, 539.0000, 899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(326, 104, 'BLACK-LEATHER-BELT-4', 'e71ffa21-6f45-4c97-964c-815c2d57d0db', '34', NULL, 539.0000, 899.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(327, 104, 'BLACK-LEATHER-BELT-5', 'e59a54ae-f37e-4cea-815f-0c3ef132426f', '36', NULL, 593.0000, 989.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(328, 105, 'BROWN-REVERSIBLE-BELT-1', 'b152c1c6-c66b-44f4-8436-c6e2c9132d15', '30', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(329, 105, 'BROWN-REVERSIBLE-BELT-2', 'd8873346-6f66-4fc7-9116-c92595c01d83', '32', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(330, 105, 'BROWN-REVERSIBLE-BELT-3', '99fe69c0-a263-4700-a1a6-9f8418a82a64', '34', NULL, 779.0000, 1299.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(331, 105, 'BROWN-REVERSIBLE-BELT-4', '275d71e2-7fe3-4770-8ef7-a2fbb310f323', '36', NULL, 857.0000, 1429.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(332, 106, 'AVIATOR-SUNGLASSES-GOLD-1', '99f60ef8-9d2d-47b4-afbb-12a9b637f6bd', 'One Size', NULL, 899.0000, 1499.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(333, 107, 'WAYFARER-SUNGLASSES-BLACK-1', '99241751-3d3f-4f78-832e-37a5056f319f', 'One Size', '{\"color\":null,\"size\":null,\"color_hex\":\"#000000\"}', 719.0000, 1199.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-07-01 11:06:37', NULL),
(334, 108, 'ATTAR-PERFUME-GIFT-SET-1', '7fbb6ea6-0ce7-4b7c-9df6-55f9b43e0af1', 'One Size', NULL, 599.0000, 999.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(335, 109, 'DEODORANT-SPRAY-200ML-1', '50f70990-450e-4b2c-8a90-1f65ac343585', 'One Size', NULL, 359.0000, 599.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 'active', '2026-06-26 06:05:43', '2026-06-26 06:05:43', NULL),
(336, 110, 'Voluptatem velit do', 'Obcaecati impedit n', 'Hiroko Roberson', '{\"color\":\"Maiores culpa odio s\",\"size\":\"Assumenda ea id pari\",\"color_hex\":\"#42c786\"}', 613.0000, 235.0000, 423.0000, 16, NULL, NULL, NULL, 0, 1, 'active', '2026-07-01 11:54:47', '2026-07-01 11:54:47', NULL),
(337, 110, 'Veniam veniam odit', 'Sit quasi accusantiu', 'Deborah Santiago', '{\"color\":\"Sed adipisci non qui\",\"size\":\"Eu et fugit ex ulla\",\"color_hex\":\"#8da3a7\"}', 37.0000, 729.0000, 294.0000, 58, NULL, NULL, NULL, 0, 1, 'active', '2026-07-01 11:54:47', '2026-07-01 11:54:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `po_number` varchar(255) NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('draft','ordered','partially_received','received','cancelled') NOT NULL DEFAULT 'draft',
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `shipping_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  `order_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `po_number`, `supplier_id`, `store_id`, `status`, `total_amount`, `shipping_cost`, `tax_amount`, `discount_amount`, `payment_status`, `order_date`, `expected_delivery_date`, `received_date`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'PO-2026-0001', 1, 1, 'received', 5000.00, 200.00, 0.00, 0.00, 'paid', '2026-05-26', '2026-06-10', '2026-06-09', 'Initial electronics stock', 1, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(2, 'PO-2026-0002', 2, 1, 'ordered', 3500.00, 150.00, 0.00, 0.00, 'unpaid', '2026-06-20', '2026-07-05', NULL, 'Fashion items restocking', 1, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(3, 'PO-2026-0003', 3, 1, 'draft', 0.00, 0.00, 0.00, 0.00, 'unpaid', '2026-06-25', '2026-07-15', NULL, 'Draft - awaiting approval', 1, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_order_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `received_quantity` int(11) NOT NULL DEFAULT 0,
  `unit_cost` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `variant_id`, `quantity`, `received_quantity`, `unit_cost`, `subtotal`, `tax`, `discount`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 100, 100, 50.00, 5000.00, 0.00, 0.00, 'Bulk order', '2026-06-25 06:17:44', '2026-06-25 06:17:44'),
(2, 2, 2, 50, 0, 70.00, 3500.00, 0.00, 0.00, NULL, '2026-06-25 06:17:44', '2026-06-25 06:17:44'),
(3, 3, 3, 200, 0, 25.00, 5000.00, 0.00, 0.00, 'Packaging materials', '2026-06-25 06:17:44', '2026-06-25 06:17:44');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_returns`
--

CREATE TABLE `purchase_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `return_number` varchar(255) NOT NULL,
  `purchase_order_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `status` enum('draft','returned','partially_refunded','refunded','cancelled') NOT NULL DEFAULT 'draft',
  `refund_status` enum('pending','partial','full') NOT NULL DEFAULT 'pending',
  `total_refund_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `return_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payment_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(19,4) NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `status` enum('pending','processed','failed') NOT NULL DEFAULT 'pending',
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Super Admin', 'Full system access', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 'Admin', 'Administrative access', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 'Manager', 'Day-to-day management', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(4, 'Staff', 'Limited staff access', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(5, 'Customer', 'Customer account', '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 2, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 3, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 4, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 5, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 6, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 7, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 8, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 9, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 10, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 11, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 12, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 13, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 14, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 15, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 16, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 17, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 18, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 19, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 20, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 21, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 22, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 23, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 24, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 25, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 26, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 27, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 28, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 29, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 30, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 31, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 32, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 33, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 34, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 35, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(1, 36, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 1, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 2, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 3, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 4, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 13, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 14, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 15, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 16, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 17, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 18, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 19, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 20, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 21, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 22, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 23, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 24, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 25, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 26, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 27, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 28, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 29, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 30, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 31, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 32, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 33, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 34, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 35, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(2, 36, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 13, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 14, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 15, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 17, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 18, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 19, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 21, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 22, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 23, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 25, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 26, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 27, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 29, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 30, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 31, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(3, 33, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(4, 13, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(4, 17, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(4, 21, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(4, 25, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL),
(4, 29, '2026-06-25 06:17:38', '2026-06-25 06:17:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'text',
  `label` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `group`, `key`, `value`, `type`, `label`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'general', 'site_name', 'Shopio', 'text', 'Site Name', 1, '2026-06-29 09:54:21', '2026-07-16 18:58:07'),
(2, 'general', 'site_logo', NULL, 'image', 'Site Logo', 2, '2026-06-29 09:54:21', '2026-06-29 09:54:21'),
(3, 'general', 'site_description', 'Your premium online shopping destination.', 'textarea', 'Site Description', 3, '2026-06-29 09:54:21', '2026-07-16 18:58:07'),
(4, 'social', 'facebook_url', NULL, 'url', 'Facebook URL', 1, '2026-06-29 09:54:21', '2026-07-16 18:58:07'),
(5, 'social', 'twitter_url', NULL, 'url', 'Twitter URL', 2, '2026-06-29 09:54:21', '2026-07-16 18:58:07'),
(6, 'social', 'instagram_url', NULL, 'url', 'Instagram URL', 3, '2026-06-29 09:54:21', '2026-07-16 18:58:07'),
(7, 'social', 'youtube_url', NULL, 'url', 'Youtube URL', 4, '2026-06-29 09:54:21', '2026-07-16 18:58:07'),
(8, 'social', 'whatsapp_number', '+8801234567890', 'tel', 'WhatsApp Number', 5, '2026-06-29 09:54:21', '2026-07-16 18:58:07'),
(9, 'contact', 'phone', '+880 123-456-7890', 'tel', 'Phone Number', 1, '2026-06-29 09:54:21', '2026-07-16 18:58:07'),
(10, 'contact', 'email', 'support@shopio.com', 'email', 'Email Address', 2, '2026-06-29 09:54:21', '2026-07-16 18:58:07'),
(11, 'contact', 'address', '123 Commerce Ave, Dhaka, Bangladesh', 'textarea', 'Address', 3, '2026-06-29 09:54:21', '2026-07-16 18:58:07'),
(12, 'seo', 'meta_title', 'Shopio - Premium E-Commerce', 'text', 'Default Meta Title', 1, '2026-06-29 09:54:21', '2026-07-16 18:58:07'),
(13, 'seo', 'meta_description', 'Shopio is your premium online shopping destination.', 'textarea', 'Default Meta Description', 2, '2026-06-29 09:54:21', '2026-07-16 18:58:07'),
(14, 'general', 'primary_color', '#22C55E', 'color', 'Primary Color', 4, '2026-06-29 12:04:56', '2026-07-16 18:58:07');

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `delivery_zone_id` bigint(20) UNSIGNED DEFAULT NULL,
  `driver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `shipping_address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tracking_number` varchar(80) NOT NULL,
  `carrier_name` varchar(120) DEFAULT NULL,
  `service_level` enum('standard','express','same_day','pickup') NOT NULL DEFAULT 'standard',
  `delivery_type` enum('home_delivery','store_pickup','third_party') NOT NULL DEFAULT 'home_delivery',
  `status` enum('pending','packed','ready_for_pickup','out_for_delivery','delivered','failed','returned','cancelled') NOT NULL DEFAULT 'pending',
  `shipping_cost` decimal(19,4) NOT NULL DEFAULT 0.0000,
  `package_weight_kg` decimal(8,2) DEFAULT NULL,
  `package_count` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `recipient_name` varchar(160) DEFAULT NULL,
  `recipient_phone` varchar(40) DEFAULT NULL,
  `delivery_instructions` varchar(1000) DEFAULT NULL,
  `scheduled_delivery_date` date DEFAULT NULL,
  `eta_at` datetime DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipment_events`
--

CREATE TABLE `shipment_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `shipment_id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `event_type` enum('status_update','assignment','pickup','location','delivery_attempt','exception','note') NOT NULL DEFAULT 'status_update',
  `status` enum('pending','packed','ready_for_pickup','out_for_delivery','delivered','failed','returned','cancelled') DEFAULT NULL,
  `title` varchar(160) NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `occurred_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sizes`
--

CREATE TABLE `sizes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `group_name` varchar(160) NOT NULL,
  `sizes` text NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  `currency_code` char(3) NOT NULL DEFAULT 'USD',
  `timezone` varchar(64) NOT NULL DEFAULT 'UTC',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `name`, `slug`, `email`, `phone`, `status`, `currency_code`, `timezone`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Main Store (Dhaka)', 'main-store-dhaka', 'store.dhaka@example.com', '01711111111', 'active', 'BDT', 'Asia/Dhaka', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(2, 'Chittagong Branch', 'chittagong-branch', 'store.ctg@example.com', '01711111112', 'active', 'BDT', 'Asia/Dhaka', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(3, 'Online Store', 'online-store', 'online@example.com', NULL, 'active', 'USD', 'UTC', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `store_staff`
--

CREATE TABLE `store_staff` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `staff_code` varchar(40) DEFAULT NULL,
  `status` enum('active','inactive','terminated') NOT NULL DEFAULT 'active',
  `hired_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `store_staff`
--

INSERT INTO `store_staff` (`id`, `store_id`, `user_id`, `staff_code`, `status`, `hired_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'ADMIN-001', 'active', '2025-12-25', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(2, 1, 2, 'MGR-001', 'active', '2026-03-25', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(3, 1, 3, 'STF-001', 'active', '2026-05-25', '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subnavbar_items`
--

CREATE TABLE `subnavbar_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `navbar_item_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `url` varchar(500) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subnavbar_items`
--

INSERT INTO `subnavbar_items` (`id`, `navbar_item_id`, `name`, `slug`, `url`, `icon`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 2, 'New Arrivals', 'new-arrivals', '/new-arrivals', 'fa-solid fa-clock', 2, 'active', '2026-06-25 06:17:42', '2026-06-25 06:17:42', NULL),
(3, 2, 'Best Sellers', 'best-sellers', '/best-sellers', 'fa-solid fa-fire', 3, 'active', '2026-06-25 06:17:42', '2026-06-25 06:17:42', NULL),
(4, 2, 'Sale', 'sale', '/sale', 'fa-solid fa-percent', 4, 'active', '2026-06-25 06:17:42', '2026-06-25 06:17:42', NULL),
(5, 2, 'Top Deals', 'top-deals', '/top-deals', 'fa-solid fa-bolt', 5, 'active', '2026-06-25 06:17:42', '2026-06-25 06:17:42', NULL),
(10, 5, 'Flash Sales', 'flash-sales', '/deals/flash-sales', 'fa-solid fa-bolt', 1, 'active', '2026-06-25 06:17:42', '2026-06-25 06:17:42', NULL),
(13, 4, 'Electronics', 'electronics', '/products/electronics', 'fa-solid fa-laptop', 1, 'active', '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(14, 4, 'Clothing', 'clothing', '/products/clothing', 'fa-solid fa-tshirt', 2, 'active', '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(15, 4, 'Sports', 'sports', '/products/sports', 'fa-solid fa-football', 3, 'active', '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(16, 4, 'Home & Kitchen', 'home-kitchen', '/products/home-kitchen', 'fa-solid fa-kitchen-set', 4, 'active', '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(17, 4, 'New Arrivals', 'products-new-arrivals', '/products/new-arrivals', 'fa-solid fa-star', 5, 'active', '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(18, 4, 'Featured', 'products-featured', '/products/featured', 'fa-solid fa-heart', 6, 'active', '2026-06-25 06:17:43', '2026-06-25 06:17:43', NULL),
(23, 10, 'T-Shirts', 'boys-tshirts', '/subnavbar/boys-tshirts', 'fa-solid fa-shirt', 1, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(24, 10, 'Shirts', 'boys-shirts', '/subnavbar/boys-shirts', 'fa-solid fa-shirt', 2, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(25, 10, 'Pants & Jeans', 'boys-pants', '/subnavbar/boys-pants', 'fa-solid fa-shirt', 3, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(26, 10, 'Panjabi & Kurta', 'boys-panjabi', '/subnavbar/boys-panjabi', 'fa-solid fa-shirt', 4, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(27, 10, 'Suits & Blazers', 'boys-suits', '/subnavbar/boys-suits', 'fa-solid fa-shirt', 5, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(28, 10, 'Winter Wear', 'boys-winter', '/subnavbar/boys-winter', 'fa-solid fa-snowflake', 6, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(29, 10, 'Underwear & Socks', 'boys-underwear', '/subnavbar/boys-underwear', 'fa-solid fa-shirt', 7, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(30, 10, 'Traditional Wear', 'boys-traditional', '/subnavbar/boys-traditional', 'fa-solid fa-shirt', 8, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(31, 11, 'Sarees', 'women-sarees', '/subnavbar/women-sarees', 'fa-solid fa-person-dress', 1, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(32, 11, 'Salwar Kameez', 'women-salwar', '/subnavbar/women-salwar', 'fa-solid fa-person-dress', 2, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(33, 11, 'Kurtis & Tunics', 'women-kurtis', '/subnavbar/women-kurtis', 'fa-solid fa-person-dress', 3, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(34, 11, 'Dresses', 'women-dresses', '/subnavbar/women-dresses', 'fa-solid fa-person-dress', 4, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(35, 11, 'Tops & Blouses', 'women-tops', '/subnavbar/women-tops', 'fa-solid fa-person-dress', 5, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(36, 11, 'Jeans & Pants', 'women-jeans', '/subnavbar/women-jeans', 'fa-solid fa-person-dress', 6, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(37, 11, 'Hijabs & Scarves', 'women-hijabs', '/subnavbar/women-hijabs', 'fa-solid fa-hand', 7, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(38, 11, 'Abayas & Burqas', 'women-abayas', '/subnavbar/women-abayas', 'fa-solid fa-person-dress', 8, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(47, 3, 'Summer Collection', 'summer-collection', '/subnavbar/summer-collection', 'fa-solid fa-sun', 3, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL),
(48, 3, 'Eid Collection', 'eid-collection', '/subnavbar/eid-collection', 'fa-solid fa-moon', 4, 'active', '2026-06-26 06:05:44', '2026-06-26 06:05:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(220) NOT NULL,
  `slug` varchar(240) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `contact_person` varchar(220) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `tax_number` varchar(100) DEFAULT NULL,
  `payment_terms` varchar(220) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `slug`, `email`, `phone`, `contact_person`, `address`, `city`, `country`, `tax_number`, `payment_terms`, `notes`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Dhaka Electronics Wholesale', 'dhaka-electronics-wholesale', 'info@dhakaelectronics.com', '+880-1712-345678', 'Rahman Ahmed', '123 Electronics Market, Bongshal', 'Dhaka', 'Bangladesh', 'BD-TAX-12345', 'Net 30', 'Primary electronics supplier', 'active', '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(2, 'Global Fashion Imports', 'global-fashion-imports', 'orders@globalfashion.com', '+880-1812-987654', 'Fatima Khan', '456 Garment Street, Gazipur', 'Gazipur', 'Bangladesh', 'BD-TAX-67890', 'Net 15', 'Clothing and fashion items', 'active', '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(3, 'Chittagong raw Materials Co.', 'chittagong-raw-materials', 'sales@ctgmaterials.com', '+880-1912-555666', 'Karim Uddin', '789 Port Road, Chittagong', 'Chittagong', 'Bangladesh', 'BD-TAX-11223', 'Net 45', 'Raw materials and packaging', 'active', '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tax_rates`
--

CREATE TABLE `tax_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(220) NOT NULL,
  `rate` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `applies_to` enum('all','products','services','digital') NOT NULL DEFAULT 'all',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `short_name` varchar(30) NOT NULL,
  `type` enum('quantity','weight','volume','length','area','time') NOT NULL DEFAULT 'quantity',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `public_id` char(36) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('active','inactive','blocked','deleted') NOT NULL DEFAULT 'active',
  `email_verified_at` datetime DEFAULT NULL,
  `phone_verified_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `public_id`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `status`, `email_verified_at`, `phone_verified_at`, `last_login_at`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '27b03fad-8ebc-471d-92bf-c29c217b4e03', 'Super', 'Admin', 'admin@example.com', '01700000001', '$2y$12$SvRMUAGmB2GcYoSa.CDKG.CEeXcGre9.KyWEJXafYMlsNATuu6bgC', 'active', NULL, NULL, NULL, 'N8rUqyUV1PHVCdzNTL1Ky72ZW8hRDaHNHsxxTv4fqd3LlAmd0ZCmKKqP7Dn3', '2026-06-25 06:17:39', '2026-06-25 06:17:39', NULL),
(2, 'c1e09b4e-6d04-423a-a263-edd8fcc66fd7', 'John', 'Manager', 'manager@example.com', '01700000002', '$2y$12$SvRMUAGmB2GcYoSa.CDKG.CEeXcGre9.KyWEJXafYMlsNATuu6bgC', 'active', NULL, NULL, NULL, NULL, '2026-06-25 06:17:39', '2026-06-25 06:17:39', NULL),
(3, 'a689a84e-e7cb-4d00-a357-addf1f187908', 'Jane', 'Staff', 'staff@example.com', '01700000003', '$2y$12$SvRMUAGmB2GcYoSa.CDKG.CEeXcGre9.KyWEJXafYMlsNATuu6bgC', 'active', NULL, NULL, NULL, NULL, '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(4, '1b58e072-9149-4f04-b9af-cd50788e698d', 'Bob', 'Customer', 'customer@example.com', '01700000004', '$2y$12$SvRMUAGmB2GcYoSa.CDKG.CEeXcGre9.KyWEJXafYMlsNATuu6bgC', 'active', NULL, NULL, NULL, NULL, '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(5, 'f7538f7b-7fc7-4e1c-8faf-04a78710e9e8', 'Abrar', 'fahim', 'a@gmail.com', NULL, '$2y$12$0Z806DD7V2wkd96antUORedTMfR7ANaiP4pIsdljHgdSr8BPrT8XK', 'active', NULL, NULL, '2026-06-26 18:10:24', NULL, '2026-06-26 02:24:57', '2026-06-26 12:10:24', NULL),
(6, 'e2821549-589d-4342-af3e-fc8e6616ea23', 'Abrar', 'Fahim', 'abcd@gmail.com', NULL, '$2y$12$s.q2z/R4yRzlP4NMbl7ExOgWJ5F6VOCv5H3wkiVuFVo1jX5uInvyW', 'active', NULL, NULL, '2026-07-24 14:34:52', NULL, '2026-07-24 13:42:58', '2026-07-24 21:34:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '2026-06-25 06:17:39', '2026-06-25 06:17:39', NULL),
(2, 3, '2026-06-25 06:17:39', '2026-06-25 06:17:39', NULL),
(3, 4, '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL),
(4, 5, '2026-06-25 06:17:40', '2026-06-25 06:17:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `ip_address` blob DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `variant_options`
--

CREATE TABLE `variant_options` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `color_name` varchar(100) NOT NULL,
  `color_code` varchar(20) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `price_adjustment` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `stock` int(11) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `variant_options`
--

INSERT INTO `variant_options` (`id`, `product_variant_id`, `color_name`, `color_code`, `image_url`, `price_adjustment`, `stock`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 286, 'Red', '#f20707', NULL, 1139.0000, 10, 0, 'active', '2026-06-26 07:09:46', '2026-06-26 07:09:46', NULL),
(2, 283, 'Red', '#f70808', NULL, 550.0000, 10, 0, 'active', '2026-06-26 08:36:16', '2026-06-26 08:36:16', NULL),
(3, 283, 'Blue', '#135d09', NULL, 550.0000, 10, 0, 'active', '2026-06-26 08:36:17', '2026-06-26 08:36:17', NULL),
(4, 2, 'Red', '#f40b0b', NULL, 100000.0000, 10, 0, 'active', '2026-06-26 09:59:35', '2026-06-26 09:59:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `webhooks`
--

CREATE TABLE `webhooks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `url` varchar(500) NOT NULL,
  `secret` varchar(255) DEFAULT NULL,
  `events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`events`)),
  `status` enum('active','inactive','failed') NOT NULL DEFAULT 'active',
  `retry_count` int(10) UNSIGNED NOT NULL DEFAULT 3,
  `timeout_seconds` int(10) UNSIGNED NOT NULL DEFAULT 30,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `webhook_deliveries`
--

CREATE TABLE `webhook_deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `webhook_id` bigint(20) UNSIGNED NOT NULL,
  `event` varchar(100) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response`)),
  `response_status` smallint(5) UNSIGNED DEFAULT NULL,
  `attempt` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `product_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(2, 1, 2, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(3, 2, 3, '2026-06-25 06:17:44', '2026-06-25 06:17:44', NULL),
(4, 5, 1, '2026-06-26 02:27:52', '2026-06-26 02:27:52', NULL),
(5, 5, 2, '2026-06-26 02:27:54', '2026-06-26 02:27:54', NULL),
(6, 5, 3, '2026-06-26 02:54:47', '2026-06-26 02:54:47', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addresses_user_id_is_default_index` (`user_id`,`is_default`),
  ADD KEY `addresses_store_id_index` (`store_id`),
  ADD KEY `addresses_country_id_city_postal_code_index` (`country_id`,`city`,`postal_code`),
  ADD KEY `addresses_latitude_longitude_index` (`latitude`,`longitude`);

--
-- Indexes for table `announcement_bars`
--
ALTER TABLE `announcement_bars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcement_bars_status_deleted_at_index` (`status`,`deleted_at`);

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `app_settings_scope_type_scope_id_setting_key_unique` (`scope_type`,`scope_id`,`setting_key`),
  ADD KEY `app_settings_is_public_setting_key_index` (`is_public`,`setting_key`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  ADD KEY `audit_logs_user_id_index` (`user_id`),
  ADD KEY `audit_logs_action_index` (`action`),
  ADD KEY `audit_logs_created_at_index` (`created_at`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `banners_status_deleted_at_index` (`status`,`deleted_at`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brands_slug_unique` (`slug`),
  ADD KEY `brands_status_deleted_at_index` (`status`,`deleted_at`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carts_user_id_status_deleted_at_updated_at_index` (`user_id`,`status`,`deleted_at`,`updated_at`),
  ADD KEY `carts_session_id_status_index` (`session_id`,`status`),
  ADD KEY `carts_expires_at_index` (`expires_at`),
  ADD KEY `carts_store_id_foreign` (`store_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_items_cart_id_variant_id_option_unique` (`cart_id`,`variant_id`,`variant_option_id`),
  ADD KEY `cart_items_variant_id_index` (`variant_id`),
  ADD KEY `cart_items_variant_option_id_index` (`variant_option_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`),
  ADD KEY `categories_parent_id_sort_order_index` (`parent_id`,`sort_order`),
  ADD KEY `categories_status_deleted_at_sort_order_index` (`status`,`deleted_at`,`sort_order`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `countries_iso2_unique` (`iso2`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`),
  ADD KEY `coupons_status_deleted_at_starts_at_ends_at_index` (`status`,`deleted_at`,`starts_at`,`ends_at`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deliveries_order_id_index` (`order_id`),
  ADD KEY `deliveries_user_id_index` (`user_id`),
  ADD KEY `deliveries_delivery_boy_id_index` (`delivery_boy_id`),
  ADD KEY `deliveries_status_index` (`status`);

--
-- Indexes for table `delivery_drivers`
--
ALTER TABLE `delivery_drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `delivery_drivers_employee_code_unique` (`employee_code`),
  ADD KEY `delivery_drivers_user_id_foreign` (`user_id`),
  ADD KEY `delivery_drivers_store_id_status_index` (`store_id`,`status`),
  ADD KEY `delivery_drivers_delivery_zone_id_status_index` (`delivery_zone_id`,`status`);

--
-- Indexes for table `delivery_zones`
--
ALTER TABLE `delivery_zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `delivery_zones_code_unique` (`code`),
  ADD KEY `delivery_zones_store_id_status_index` (`store_id`,`status`),
  ADD KEY `delivery_zones_city_state_index` (`city`,`state`),
  ADD KEY `delivery_zones_country_id_foreign` (`country_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `homepage_ctas`
--
ALTER TABLE `homepage_ctas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `homepage_ctas_status_deleted_at_index` (`status`,`deleted_at`);

--
-- Indexes for table `inventory_locations`
--
ALTER TABLE `inventory_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_locations_store_id_status_deleted_at_index` (`store_id`,`status`,`deleted_at`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_movements_created_by_foreign` (`created_by`),
  ADD KEY `inv_mov_variant_created_idx` (`variant_id`,`created_at`),
  ADD KEY `inv_mov_location_created_idx` (`location_id`,`created_at`),
  ADD KEY `inv_mov_reference_idx` (`reference_type`,`reference_id`);

--
-- Indexes for table `inventory_stock`
--
ALTER TABLE `inventory_stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventory_stock_location_id_variant_id_unique` (`location_id`,`variant_id`),
  ADD KEY `inventory_stock_variant_id_index` (`variant_id`),
  ADD KEY `inv_stock_avail_idx` (`location_id`,`quantity_on_hand`,`quantity_reserved`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `navbar_items`
--
ALTER TABLE `navbar_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `navbar_items_slug_unique` (`slug`),
  ADD KEY `navbar_items_status_deleted_at_index` (`status`,`deleted_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_read_at_deleted_at_index` (`user_id`,`read_at`,`deleted_at`),
  ADD KEY `notifications_type_index` (`type`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_coupon_id_foreign` (`coupon_id`),
  ADD KEY `orders_billing_address_id_foreign` (`billing_address_id`),
  ADD KEY `orders_shipping_address_id_foreign` (`shipping_address_id`),
  ADD KEY `orders_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `orders_store_id_status_deleted_at_created_at_index` (`store_id`,`status`,`deleted_at`,`created_at`),
  ADD KEY `orders_status_payment_status_deleted_at_created_at_index` (`status`,`payment_status`,`deleted_at`,`created_at`),
  ADD KEY `orders_source_created_at_index` (`source`,`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`),
  ADD KEY `order_items_order_id_index` (`order_id`),
  ADD KEY `order_items_variant_id_created_at_index` (`variant_id`,`created_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_order_id_status_index` (`order_id`,`status`),
  ADD KEY `payments_provider_provider_payment_id_index` (`provider`,`provider_payment_id`),
  ADD KEY `payments_status_created_at_index` (`status`,`created_at`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `pos_registers`
--
ALTER TABLE `pos_registers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pos_registers_code_unique` (`code`),
  ADD KEY `pos_registers_store_id_status_deleted_at_index` (`store_id`,`status`,`deleted_at`);

--
-- Indexes for table `pos_sales`
--
ALTER TABLE `pos_sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pos_sales_receipt_number_unique` (`receipt_number`),
  ADD KEY `pos_sales_register_id_shift_id_user_id_status_deleted_at_index` (`register_id`,`shift_id`,`user_id`,`status`,`deleted_at`),
  ADD KEY `pos_sales_receipt_number_index` (`receipt_number`),
  ADD KEY `pos_sales_shift_id_foreign` (`shift_id`),
  ADD KEY `pos_sales_order_id_foreign` (`order_id`),
  ADD KEY `pos_sales_user_id_foreign` (`user_id`);

--
-- Indexes for table `pos_sale_items`
--
ALTER TABLE `pos_sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pos_sale_items_pos_sale_id_foreign` (`pos_sale_id`),
  ADD KEY `pos_sale_items_product_id_foreign` (`product_id`),
  ADD KEY `pos_sale_items_variant_id_foreign` (`variant_id`);

--
-- Indexes for table `pos_shifts`
--
ALTER TABLE `pos_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pos_shifts_register_id_user_id_status_deleted_at_index` (`register_id`,`user_id`,`status`,`deleted_at`),
  ADD KEY `pos_shifts_user_id_foreign` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD KEY `products_brand_id_status_index` (`brand_id`,`status`),
  ADD KEY `products_status_deleted_at_visibility_published_at_index` (`status`,`deleted_at`,`visibility`,`published_at`),
  ADD KEY `products_unit_id_index` (`unit_id`),
  ADD KEY `products_size_id_index` (`size_id`),
  ADD KEY `products_tax_rate_id_foreign` (`tax_rate_id`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_navbar_item_id_foreign` (`navbar_item_id`),
  ADD KEY `products_subnavbar_item_id_foreign` (`subnavbar_item_id`),
  ADD KEY `products_is_homepage_index` (`is_homepage`);
ALTER TABLE `products` ADD FULLTEXT KEY `ft_products_search` (`name`,`short_description`,`description`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `product_categories_category_id_product_id_index` (`category_id`,`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_sort_order_index` (`product_id`,`sort_order`),
  ADD KEY `product_images_variant_id_index` (`variant_id`);

--
-- Indexes for table `product_requests`
--
ALTER TABLE `product_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_requests_user_id_foreign` (`user_id`),
  ADD KEY `product_requests_product_id_foreign` (`product_id`),
  ADD KEY `product_requests_status_index` (`status`),
  ADD KEY `product_requests_customer_email_index` (`customer_email`),
  ADD KEY `product_requests_created_at_index` (`created_at`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_reviews_product_id_status_deleted_at_index` (`product_id`,`status`,`deleted_at`),
  ADD KEY `product_reviews_user_id_index` (`user_id`);

--
-- Indexes for table `product_supplier`
--
ALTER TABLE `product_supplier`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_supplier_product_id_supplier_id_unique` (`product_id`,`supplier_id`),
  ADD KEY `product_supplier_supplier_id_foreign` (`supplier_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_variants_sku_unique` (`sku`),
  ADD UNIQUE KEY `product_variants_barcode_unique` (`barcode`),
  ADD KEY `product_variants_product_id_status_deleted_at_index` (`product_id`,`status`,`deleted_at`),
  ADD KEY `product_variants_sale_price_index` (`sale_price`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_orders_po_number_unique` (`po_number`),
  ADD KEY `purchase_orders_created_by_foreign` (`created_by`),
  ADD KEY `purchase_orders_status_deleted_at_created_at_index` (`status`,`deleted_at`,`created_at`),
  ADD KEY `purchase_orders_supplier_id_created_at_index` (`supplier_id`,`created_at`),
  ADD KEY `purchase_orders_store_id_status_index` (`store_id`,`status`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_items_purchase_order_id_index` (`purchase_order_id`),
  ADD KEY `purchase_order_items_variant_id_index` (`variant_id`);

--
-- Indexes for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_returns_return_number_unique` (`return_number`),
  ADD KEY `purchase_returns_store_id_foreign` (`store_id`),
  ADD KEY `purchase_returns_created_by_foreign` (`created_by`),
  ADD KEY `purchase_returns_status_deleted_at_created_at_index` (`status`,`deleted_at`,`created_at`),
  ADD KEY `purchase_returns_supplier_id_created_at_index` (`supplier_id`,`created_at`),
  ADD KEY `purchase_returns_purchase_order_id_status_index` (`purchase_order_id`,`status`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `refunds_order_id_created_at_index` (`order_id`,`created_at`),
  ADD KEY `refunds_payment_id_index` (`payment_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `role_permissions_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`),
  ADD KEY `settings_group_index` (`group`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shipments_tracking_number_unique` (`tracking_number`),
  ADD KEY `shipments_shipping_address_id_foreign` (`shipping_address_id`),
  ADD KEY `shipments_order_id_index` (`order_id`),
  ADD KEY `shipments_store_id_status_created_at_index` (`store_id`,`status`,`created_at`),
  ADD KEY `shipments_delivery_zone_id_status_index` (`delivery_zone_id`,`status`),
  ADD KEY `shipments_driver_id_status_index` (`driver_id`,`status`);

--
-- Indexes for table `shipment_events`
--
ALTER TABLE `shipment_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipment_events_created_by_foreign` (`created_by`),
  ADD KEY `shipment_events_shipment_id_occurred_at_index` (`shipment_id`,`occurred_at`),
  ADD KEY `shipment_events_driver_id_occurred_at_index` (`driver_id`,`occurred_at`),
  ADD KEY `shipment_events_event_type_occurred_at_index` (`event_type`,`occurred_at`);

--
-- Indexes for table `sizes`
--
ALTER TABLE `sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sizes_status_deleted_at_index` (`status`,`deleted_at`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stores_slug_unique` (`slug`),
  ADD KEY `stores_status_deleted_at_index` (`status`,`deleted_at`);

--
-- Indexes for table `store_staff`
--
ALTER TABLE `store_staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `store_staff_store_id_user_id_unique` (`store_id`,`user_id`),
  ADD UNIQUE KEY `store_staff_store_id_staff_code_unique` (`store_id`,`staff_code`),
  ADD KEY `store_staff_user_id_foreign` (`user_id`),
  ADD KEY `store_staff_store_id_status_index` (`store_id`,`status`);

--
-- Indexes for table `subnavbar_items`
--
ALTER TABLE `subnavbar_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subnavbar_items_slug_unique` (`slug`),
  ADD KEY `subnavbar_items_status_deleted_at_index` (`status`,`deleted_at`),
  ADD KEY `subnavbar_items_navbar_item_id_index` (`navbar_item_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `suppliers_slug_unique` (`slug`),
  ADD KEY `suppliers_status_deleted_at_index` (`status`,`deleted_at`);

--
-- Indexes for table `tax_rates`
--
ALTER TABLE `tax_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `units_slug_unique` (`slug`),
  ADD KEY `units_status_deleted_at_index` (`status`,`deleted_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_public_id_unique` (`public_id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD KEY `users_status_deleted_at_created_at_index` (`status`,`deleted_at`,`created_at`),
  ADD KEY `users_last_login_at_index` (`last_login_at`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `user_roles_role_id_foreign` (`role_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_sessions_token_hash_unique` (`token_hash`),
  ADD KEY `user_sessions_user_id_revoked_at_expires_at_index` (`user_id`,`revoked_at`,`expires_at`),
  ADD KEY `user_sessions_expires_at_index` (`expires_at`);

--
-- Indexes for table `variant_options`
--
ALTER TABLE `variant_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variant_options_product_variant_id_color_name_index` (`product_variant_id`,`color_name`),
  ADD KEY `variant_options_status_index` (`status`);

--
-- Indexes for table `webhooks`
--
ALTER TABLE `webhooks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `webhook_deliveries`
--
ALTER TABLE `webhook_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `webhook_deliveries_webhook_id_success_index` (`webhook_id`,`success`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlists_user_id_product_id_unique` (`user_id`,`product_id`),
  ADD KEY `wishlists_product_id_index` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `announcement_bars`
--
ALTER TABLE `announcement_bars`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `app_settings`
--
ALTER TABLE `app_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `delivery_drivers`
--
ALTER TABLE `delivery_drivers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_zones`
--
ALTER TABLE `delivery_zones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `homepage_ctas`
--
ALTER TABLE `homepage_ctas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inventory_locations`
--
ALTER TABLE `inventory_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_stock`
--
ALTER TABLE `inventory_stock`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=671;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `navbar_items`
--
ALTER TABLE `navbar_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pos_registers`
--
ALTER TABLE `pos_registers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pos_sales`
--
ALTER TABLE `pos_sales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pos_sale_items`
--
ALTER TABLE `pos_sale_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pos_shifts`
--
ALTER TABLE `pos_shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `product_requests`
--
ALTER TABLE `product_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_supplier`
--
ALTER TABLE `product_supplier`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=338;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipment_events`
--
ALTER TABLE `shipment_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sizes`
--
ALTER TABLE `sizes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `store_staff`
--
ALTER TABLE `store_staff`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subnavbar_items`
--
ALTER TABLE `subnavbar_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tax_rates`
--
ALTER TABLE `tax_rates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `variant_options`
--
ALTER TABLE `variant_options`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `webhooks`
--
ALTER TABLE `webhooks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `webhook_deliveries`
--
ALTER TABLE `webhook_deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  ADD CONSTRAINT `addresses_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_variant_option_id_foreign` FOREIGN KEY (`variant_option_id`) REFERENCES `variant_options` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_delivery_boy_id_foreign` FOREIGN KEY (`delivery_boy_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deliveries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deliveries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_drivers`
--
ALTER TABLE `delivery_drivers`
  ADD CONSTRAINT `delivery_drivers_delivery_zone_id_foreign` FOREIGN KEY (`delivery_zone_id`) REFERENCES `delivery_zones` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `delivery_drivers_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `delivery_drivers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `delivery_zones`
--
ALTER TABLE `delivery_zones`
  ADD CONSTRAINT `delivery_zones_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `delivery_zones_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_locations`
--
ALTER TABLE `inventory_locations`
  ADD CONSTRAINT `inventory_locations_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_movements_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `inventory_locations` (`id`),
  ADD CONSTRAINT `inventory_movements_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`);

--
-- Constraints for table `inventory_stock`
--
ALTER TABLE `inventory_stock`
  ADD CONSTRAINT `inventory_stock_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `inventory_locations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_stock_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_billing_address_id_foreign` FOREIGN KEY (`billing_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_shipping_address_id_foreign` FOREIGN KEY (`shipping_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pos_registers`
--
ALTER TABLE `pos_registers`
  ADD CONSTRAINT `pos_registers_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pos_sales`
--
ALTER TABLE `pos_sales`
  ADD CONSTRAINT `pos_sales_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pos_sales_register_id_foreign` FOREIGN KEY (`register_id`) REFERENCES `pos_registers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pos_sales_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `pos_shifts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pos_sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pos_sale_items`
--
ALTER TABLE `pos_sale_items`
  ADD CONSTRAINT `pos_sale_items_pos_sale_id_foreign` FOREIGN KEY (`pos_sale_id`) REFERENCES `pos_sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pos_sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pos_sale_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pos_shifts`
--
ALTER TABLE `pos_shifts`
  ADD CONSTRAINT `pos_shifts_register_id_foreign` FOREIGN KEY (`register_id`) REFERENCES `pos_registers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pos_shifts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_navbar_item_id_foreign` FOREIGN KEY (`navbar_item_id`) REFERENCES `navbar_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_size_id_foreign` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_subnavbar_item_id_foreign` FOREIGN KEY (`subnavbar_item_id`) REFERENCES `subnavbar_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_tax_rate_id_foreign` FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_categories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_images_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_requests`
--
ALTER TABLE `product_requests`
  ADD CONSTRAINT `product_requests_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_supplier`
--
ALTER TABLE `product_supplier`
  ADD CONSTRAINT `product_supplier_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_supplier_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `purchase_orders_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`),
  ADD CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD CONSTRAINT `purchase_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `purchase_returns_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`),
  ADD CONSTRAINT `purchase_returns_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`),
  ADD CONSTRAINT `purchase_returns_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `refunds`
--
ALTER TABLE `refunds`
  ADD CONSTRAINT `refunds_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `refunds_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_delivery_zone_id_foreign` FOREIGN KEY (`delivery_zone_id`) REFERENCES `delivery_zones` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipments_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `delivery_drivers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipments_shipping_address_id_foreign` FOREIGN KEY (`shipping_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipments_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `shipment_events`
--
ALTER TABLE `shipment_events`
  ADD CONSTRAINT `shipment_events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipment_events_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `delivery_drivers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipment_events_shipment_id_foreign` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `store_staff`
--
ALTER TABLE `store_staff`
  ADD CONSTRAINT `store_staff_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `store_staff_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subnavbar_items`
--
ALTER TABLE `subnavbar_items`
  ADD CONSTRAINT `subnavbar_items_navbar_item_id_foreign` FOREIGN KEY (`navbar_item_id`) REFERENCES `navbar_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `variant_options`
--
ALTER TABLE `variant_options`
  ADD CONSTRAINT `variant_options_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `webhook_deliveries`
--
ALTER TABLE `webhook_deliveries`
  ADD CONSTRAINT `webhook_deliveries_webhook_id_foreign` FOREIGN KEY (`webhook_id`) REFERENCES `webhooks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
