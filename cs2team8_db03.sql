-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 04, 2025 at 06:45 PM
-- Server version: 8.0.44-0ubuntu0.22.04.1
-- PHP Version: 8.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cs2team8_db03_2`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Home',
  `address_line1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default_shipping` tinyint(1) DEFAULT '0',
  `is_default_billing` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `user_id`, `label`, `address_line1`, `address_line2`, `city`, `state`, `postcode`, `country`, `phone`, `is_default_shipping`, `is_default_billing`, `created_at`) VALUES
(1, 2, 'Home', '1 Demo Street', NULL, 'London', NULL, 'SW1A 1AA', 'United Kingdom', '+441234567891', 1, 1, '2025-11-24 20:07:44');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `activity_id` bigint UNSIGNED NOT NULL,
  `admin_user_id` int UNSIGNED NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `cart_id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` bigint UNSIGNED NOT NULL,
  `cart_id` bigint UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `created_at`) VALUES
(1, 'GOLD CHAINS', 'Chains in gold: various link styles', '2025-11-24 20:07:44'),
(2, 'WHITE GOLD RINGS', 'White gold rings set with various gemstones', '2025-11-24 20:07:44'),
(3, 'ROPE STYLE ANKLETS', 'Anklets in rope style with various metals', '2025-11-24 20:07:44'),
(4, 'BELLY BUTTON DIAMOND PIERCING', 'Diamond belly button jewellery with cuts and clarity grades', '2025-11-24 20:07:44'),
(5, 'GEMSTONE GOLDEN EARRINGS', 'Gold earrings set with gemstones', '2025-11-24 20:07:44'),
(6, 'BELLY CHAINS', 'Decorative belly chains in various metals', '2025-11-24 20:07:44');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_queries`
--

CREATE TABLE `chatbot_queries` (
  `query_id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `user_message` text COLLATE utf8mb4_unicode_ci,
  `bot_response` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_requests`
--

CREATE TABLE `contact_requests` (
  `request_id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_requests`
--

INSERT INTO `contact_requests` (`request_id`, `user_id`, `name`, `email`, `subject`, `message`, `created_at`, `resolved`) VALUES
(1, NULL, 'Alice Guest', 'alice@example.com', 'Question about ring sizes', 'Do your white gold rings come in size L?', '2025-11-24 20:07:44', 0);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `stock_quantity` int UNSIGNED NOT NULL DEFAULT '0',
  `threshold_level` int UNSIGNED NOT NULL DEFAULT '5',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `product_id`, `stock_quantity`, `threshold_level`, `last_updated`) VALUES
(1, 1, 20, 5, '2025-11-24 20:07:44'),
(2, 2, 20, 5, '2025-11-24 20:07:44'),
(3, 3, 20, 5, '2025-11-24 20:07:44'),
(4, 4, 20, 5, '2025-11-24 20:07:44'),
(5, 5, 20, 5, '2025-11-24 20:07:44'),
(6, 6, 20, 5, '2025-11-24 20:07:44'),
(7, 7, 20, 5, '2025-11-24 20:07:44'),
(8, 8, 20, 5, '2025-11-24 20:07:44'),
(9, 9, 20, 5, '2025-11-24 20:07:44'),
(10, 10, 20, 5, '2025-11-24 20:07:44'),
(11, 11, 20, 5, '2025-11-24 20:07:44'),
(12, 12, 20, 5, '2025-11-24 20:07:44'),
(13, 13, 20, 5, '2025-11-24 20:07:44'),
(14, 14, 20, 5, '2025-11-24 20:07:44'),
(15, 15, 20, 5, '2025-11-24 20:07:44'),
(16, 16, 20, 5, '2025-11-24 20:07:44'),
(17, 17, 20, 5, '2025-11-24 20:07:44'),
(18, 18, 20, 5, '2025-11-24 20:07:44'),
(19, 19, 20, 5, '2025-11-24 20:07:44'),
(20, 20, 20, 5, '2025-11-24 20:07:44'),
(21, 21, 20, 5, '2025-11-24 20:07:44'),
(22, 22, 20, 5, '2025-11-24 20:07:44'),
(23, 23, 20, 5, '2025-11-24 20:07:44'),
(24, 24, 20, 5, '2025-11-24 20:07:44'),
(25, 25, 20, 5, '2025-11-24 20:07:44'),
(26, 26, 20, 5, '2025-11-24 20:07:44'),
(27, 27, 20, 5, '2025-11-24 20:07:44'),
(28, 28, 20, 5, '2025-11-24 20:07:44'),
(29, 29, 20, 5, '2025-11-24 20:07:44'),
(30, 30, 20, 5, '2025-11-24 20:07:44');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `log_id` bigint UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `change_type` enum('incoming','outgoing','adjustment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_changed` int NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `performed_by` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `address_id` int UNSIGNED DEFAULT NULL,
  `payment_id` int UNSIGNED DEFAULT NULL,
  `order_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GBP',
  `order_status` enum('pending','processing','shipped','delivered','cancelled','returned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `placed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `notes` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `price_each` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(10,2) GENERATED ALWAYS AS ((`quantity` * `price_each`)) STORED,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `payment_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `payment_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last4` char(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_month` tinyint UNSIGNED DEFAULT NULL,
  `expiry_year` smallint UNSIGNED DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`payment_id`, `user_id`, `payment_type`, `provider`, `last4`, `token`, `expiry_month`, `expiry_year`, `is_default`, `created_at`) VALUES
(1, 2, 'Demo Visa', 'Visa', '4242', 'tok_demo_4242', 12, 2026, 1, '2025-11-24 20:07:44');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int UNSIGNED NOT NULL,
  `sku` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int UNSIGNED NOT NULL,
  `subcategory_id` int UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `long_description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `weight_grams` int UNSIGNED DEFAULT NULL,
  `material` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metal_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gemstone` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `main_image` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_order_qty` int UNSIGNED DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--
-- NOTE: Names, Descriptions and Images replaced with content from DB04. 
-- SKUs, Prices, Category mappings and technical specs remain from DB03.
--

INSERT INTO `products` (`product_id`, `sku`, `category_id`, `subcategory_id`, `name`, `short_description`, `long_description`, `price`, `weight_grams`, `material`, `metal_type`, `gemstone`, `main_image`, `min_order_qty`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'GC-CB-001', 1, 1, 'The Godfather Cuban Link', 'Heavy 18K Gold Plated Cuban.', 'This massive Cuban link is made with 5x PVD plating to ensure it never fades or tarnishes. It features a custom box lock clasp for maximum security and a heavy feel that makes you stand out more. It is perfect for wearing solo or layering with thinner chains.', '249.00', NULL, 'Gold', '18K Gold', NULL, 'images/chain_cuban.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(2, 'GC-FG-002', 1, 2, 'Figaro Legacy Chain', 'Classic Italian Figaro style.', 'An Italian design reimagined for the modern streetwear aesthetic. This chain features the classic pattern of three small circular links followed by one elongated oval link, with a diamond cut to catch the light from every angle. It is subtle enough for daily wear.', '179.00', NULL, 'Gold', '14K Gold', NULL, 'images/chain_figaro.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(3, 'GC-RP-003', 1, 3, 'Twisted Rope Drip', 'Thick 6mm twisted rope chain.', 'This diamond-cut rope chain reflects light from all angles, creating a sparkle that is built with a solid core. It is fully waterproof and sweatproof, making it the perfect everyday chain for the gym or the club.', '99.00', NULL, 'Gold', '9K Gold', NULL, 'images/chain_rope.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(4, 'GC-BX-004', 1, 4, 'Midnight Box Link', 'Sleek, geometric box chain.', 'Features square geometric links that provide a sturdy yet elegant look. The box design is known for its incredible strength, making this the safest option for holding heavy pendants.', '129.00', NULL, 'Gold', '14K Gold', NULL, 'images/chain_box.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(5, 'GC-SN-005', 1, 5, 'Liquid Gold Snake', 'Smooth finish that sits flat.', 'A round, smooth metal chain that moves like liquid gold on the skin. The seamless design offers a minimalist aesthetic that looks incredible when paired with a turtleneck for exampple. It is plated in 24K gold for that extra rich yellow tone.', '219.00', NULL, 'Gold', '18K Gold', NULL, 'images/chain_snake.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(6, 'WGR-SAP-001', 2, 6, 'Iced Sapphire Pinky Ring', 'Deep blue sapphire centerpiece.', 'It features a lab-created royal blue sapphire center stone, surrounded by a halo of hand-set VVS simulant. The 18K white gold finish provides a cold, icy aesthetic perfect for night outs.', '349.00', NULL, 'Gold', '18K White Gold', 'Sapphire', 'images/ring_sapphire.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(7, 'WGR-EMR-002', 2, 7, 'Emerald City Band', 'Square cut emerald set.', 'A heavy band design featuring a large square cut emerald. This ring can represent a lot of things such as wealth and status which combines the classic appeal of emeralds with a modern, chunky white gold setting. The interior is solid for a comfortable, premium fit.', '389.00', NULL, 'Gold', '18K White Gold', 'Emerald', 'images/ring_emerald.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(8, 'WGR-RBY-003', 2, 8, 'Bloodline Ruby Signet', 'Deep red ruby centerpiece.', 'A classic signet style ring upgraded with a bloody red ruby stone. The polished white gold band tapers towards the back for comfort while keeping the face large and bold.', '319.00', NULL, 'Gold', '14K White Gold', 'Ruby', 'images/ring_ruby.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(9, 'WGR-AMT-004', 2, 9, 'Purple Haze Amethyst', 'Royal purple stone with halo.', 'An elegant amethyst stone surrounded by a halo of small diamonds. The deep purple contrasts perfectly with the icy white gold. Each stone is prong set by hand to ensure durability and maximum light refraction.', '229.00', NULL, 'Gold', '14K White Gold', 'Amethyst', 'images/ring_amethyst.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(10, 'WGR-AQM-005', 2, 10, 'Arctic Aquamarine Ring', 'Ice cold blue stone.', 'This light blue aquamarine stone perfectly complements the white gold band for a frozen look. The stone is cut in a style to maximise sparkle even in low light conditions.', '279.00', NULL, 'Gold', '18K White Gold', 'Aquamarine', 'images/ring_aquamarine.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(11, 'RSANK-GLD-001', 3, 11, 'Golden Hour Anklet', 'Shines brightest in the sun.', 'Simple, elegant, and durable. This anklet is made for beach days and summer nights. The 18K gold bonding is resistant to salt water and chlorine, ensuring it keeps its shine all vacation long. Adjustable length fits all sizes.', '89.00', NULL, 'Gold', '18K Gold', NULL, 'images/anklet_gold.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(12, 'RSANK-RG-002', 3, 12, 'Rose Gold Drip', 'Subtle pink hue.', 'A softer aesthetic with our premium rose gold plating. The chain links are delicate yet strong, offering a feminine touch to your sneaker or heels.', '69.00', NULL, 'Gold', '18K Rose Gold', NULL, 'images/anklet_rose.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(13, 'RSANK-WG-003', 3, 13, 'White Gold Tennis Anklet', 'Full diamond-simulant wrap.', 'A line of diamond simulants set in white gold. This is the luxury anklet, offering 360 degrees of shine. Each stone is individually prong set, not glued that ensures high-end quality.', '99.00', NULL, 'Gold', '14K White Gold', NULL, 'images/anklet_white.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(14, 'RSANK-SS-004', 3, 14, 'Sterling Silver Essentials', 'Everyday wear. Waterproof.', 'High-quality 925 Sterling Silver. This piece will not rust or turn your skin green. It is the perfect daily driver for anyone who prefers the silver aesthetic.', '29.00', NULL, 'Silver', '925 Sterling', NULL, 'images/anklet_silver.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(15, 'RSANK-PT-005', 3, 15, 'Platinum Tier Anklet', 'Heavy weight premium metal.', 'The ultimate luxury. Crafted from solid platinum, this anklet is for the elite. It has a noticeable weight and a darker, richer tone than silver.', '399.00', NULL, 'Platinum', '950 Platinum', NULL, 'images/anklet_platinum.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(16, 'BB-D-VS1-001', 4, 16, 'VS1 Diamond Drop', 'Real diamonds, no fakes.', 'Certified VS1 clarity diamond that offers maximum sparkle. This belly ring features a lovely dangle design. Set in 14K solid gold to prevent any irritation or tarnishing.', '149.00', NULL, 'Gold', NULL, 'Diamond', 'images/belly_vs1.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(17, 'BB-D-VVS-002', 4, 17, 'VVS Clarity Bar', 'Near flawless diamonds.', 'Top-tier VVS diamonds set in a classic curved barbell. These stones are near flawless to the naked eye, providing an unmatched level of brilliance. It is the perfect upgrade for your piercing.', '199.00', NULL, 'Gold', NULL, 'Diamond', 'images/belly_vvs.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(18, 'BB-D-FL-003', 4, 18, 'The Flawless Flower', 'Intricate flower design.', 'A floral arrangement of flawless diamonds. Each petal is a marquise cut stone surrounding a brilliant round center. This is a high jewellery piece designed for special occasions.', '399.00', NULL, 'Gold', NULL, 'Diamond', 'images/belly_flawless.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(19, 'BB-D-FC-004', 4, 19, 'Fancy Cut Teardrop', 'Unique teardrop shape.', 'Features a rare pear shaped diamond cut for a unique radiance. The teardrop shape draws the eye and elongates the look of the torsso and is set in a minimal gold bezel.', '179.00', NULL, 'Gold', NULL, 'Diamond', 'images/belly_fancy.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(20, 'BB-D-PC-005', 4, 20, 'Princess Cut Solitaire', 'Sharp square cut diamond.', 'Modern princess cut diamond in a secure bezel setting. The sharp corners and sparkling features give this piece an edgy look compared to traditional round cuts.', '169.00', NULL, 'Gold', NULL, 'Diamond', 'images/belly_princess.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(21, 'GGE-SAP-001', 5, 21, 'Sapphire Ice Studs', 'Deep blue studs.', 'Minimalist studs featuring deep blue sapphires. These are the perfect size for daily wear that has a secure screw back post to ensure you never lose them.', '249.00', NULL, 'Gold', NULL, 'Sapphire', 'images/earring_sapphire.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(22, 'GGE-EMR-002', 5, 22, 'Emerald Hoops', 'Gold hoops with emeralds.', 'A special earring style hoops lined with genuine emeralds. They hug the earlobe closely for a comfortable fit. The green emeralds aligns beautifully against the yellow gold.', '219.00', NULL, 'Gold', NULL, 'Emerald', 'images/earring_emerald.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(23, 'GGE-RBY-003', 5, 23, 'Ruby Dangle Earrings', 'Statement piece.', 'Long dangle earrings featuring teardrop rubies. These are designed for evening wear and formal events where you need to make an impression.', '189.00', NULL, 'Gold', NULL, 'Ruby', 'images/earring_ruby.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(24, 'GGE-AMT-004', 5, 24, 'Amethyst Clusters', 'Raw crystal vibe.', 'A bundle of raw cut amethysts for a natural look with a luxury finish. Each pair is unique due to the variation in the stones. Moreover, it is plated in heavy gold to contrast the purple.', '149.00', NULL, 'Gold', NULL, 'Amethyst', 'images/earring_amethyst.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(25, 'GGE-AQM-005', 5, 25, 'Ocean Blue Studs', 'Aquamarine stones.', 'Bright blue stones reminiscent of the ocean. These studs use a 4 prong setting to let maximum light enter the stone. It is a refreshing pop of color for any outfit.', '179.00', NULL, 'Gold', NULL, 'Aquamarine', 'images/earring_aqua.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(26, 'BC-G-001', 6, 26, '18K Gold Waist Chain', 'Adjustable waist chain.', 'A alluring addition to any swimwear. This 18K Gold plated chain is adjustable to fit various waist sizes. The plating is extra thick to withstand sun, sand, and water.', '299.00', NULL, 'Gold', '18K Gold', NULL, 'images/bellychain_gold.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(27, 'BC-RG-002', 6, 27, 'Rose Gold Body Link', 'Delicate rose gold links.', 'Thin, feminine chain that highlights the waist. The rose gold color blends beautifully with warm skin tones. Features a small charm at the clasp for added detail.', '259.00', NULL, 'Gold', '18K Rose Gold', NULL, 'images/bellychain_rose.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(28, 'BC-WG-003', 6, 28, 'Iced White Gold Waist', 'White gold with crystals.', 'Dotted with crystals for extra shine at the club or festival. This waist chain is designed to catch stage lights and flash photography. Furthermore, it is made with rhodium plated silver.', '279.00', NULL, 'Gold', '14K White Gold', NULL, 'images/bellychain_white.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(29, 'BC-SS-004', 6, 29, 'Silver Snake Body Chain', 'Sleek silver snake chain.', 'Modern, minimalist and smooth. The snake chain design feels like water on the skin and has a staple piece for the summer.', '49.00', NULL, 'Silver', '925 Sterling', NULL, 'images/bellychain_silver.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1),
(30, 'BC-PT-005', 6, 30, 'Platinum Luxury Body Chain', 'The heaviest body chain.', 'Solid platinum links for the ultimate luxury accessory. This piece has significant weight and hangs perfectly around the hips. It is an investment piece that retains its value.', '599.00', NULL, 'Platinum', '950 Platinum', NULL, 'images/bellychain_platinum.jpg', 1, '2025-11-24 20:07:44', '2025-11-24 20:07:44', 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `image_url` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int UNSIGNED DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `return_id` bigint UNSIGNED NOT NULL,
  `order_item_id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `reason` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_status` enum('requested','approved','rejected','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requested',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` bigint UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `rating` tinyint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved` tinyint(1) DEFAULT '1'
) ;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `product_id`, `user_id`, `rating`, `title`, `body`, `created_at`, `approved`) VALUES
(1, 1, 2, 5, 'Beautiful chain', 'Great quality and finish. Exactly as pictured.', '2025-11-24 20:07:45', 1);

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `subcategory_id` int UNSIGNED NOT NULL,
  `category_id` int UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`subcategory_id`, `category_id`, `name`, `description`, `created_at`) VALUES
(1, 1, 'Cuban link', NULL, '2025-11-24 20:07:44'),
(2, 1, 'Figaro', NULL, '2025-11-24 20:07:44'),
(3, 1, 'Rope chain', NULL, '2025-11-24 20:07:44'),
(4, 1, 'Box chain', NULL, '2025-11-24 20:07:44'),
(5, 1, 'Snake chain', NULL, '2025-11-24 20:07:44'),
(6, 2, 'Sapphires', NULL, '2025-11-24 20:07:44'),
(7, 2, 'Emeralds', NULL, '2025-11-24 20:07:44'),
(8, 2, 'Rubies', NULL, '2025-11-24 20:07:44'),
(9, 2, 'Amethysts', NULL, '2025-11-24 20:07:44'),
(10, 2, 'Aquamarines', NULL, '2025-11-24 20:07:44'),
(11, 3, 'Gold', NULL, '2025-11-24 20:07:44'),
(12, 3, 'Rose Gold', NULL, '2025-11-24 20:07:44'),
(13, 3, 'White Gold', NULL, '2025-11-24 20:07:44'),
(14, 3, 'Sterling Silver 925', NULL, '2025-11-24 20:07:44'),
(15, 3, 'Platinum', NULL, '2025-11-24 20:07:44'),
(16, 4, 'VS1', NULL, '2025-11-24 20:07:44'),
(17, 4, 'VVS', NULL, '2025-11-24 20:07:44'),
(18, 4, 'FLAWLESS', NULL, '2025-11-24 20:07:44'),
(19, 4, 'FANCY CUTS', NULL, '2025-11-24 20:07:44'),
(20, 4, 'Princess cut', NULL, '2025-11-24 20:07:44'),
(21, 5, 'Sapphires', NULL, '2025-11-24 20:07:44'),
(22, 5, 'Emeralds', NULL, '2025-11-24 20:07:44'),
(23, 5, 'Rubies', NULL, '2025-11-24 20:07:44'),
(24, 5, 'Amethysts', NULL, '2025-11-24 20:07:44'),
(25, 5, 'Aquamarines', NULL, '2025-11-24 20:07:44'),
(26, 6, 'Gold', NULL, '2025-11-24 20:07:44'),
(27, 6, 'Rose Gold', NULL, '2025-11-24 20:07:44'),
(28, 6, 'White Gold', NULL, '2025-11-24 20:07:44'),
(29, 6, 'Sterling Silver 925', NULL, '2025-11-24 20:07:44'),
(30, 6, 'Platinum', NULL, '2025-11-24 20:07:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int UNSIGNED NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hashed_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('customer','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `hashed_password`, `role`, `phone`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin', 'User', 'admin@example.com', 'PASSWORD_HASH_PLACEHOLDER', 'admin', '+441234567890', '2025-11-24 20:07:44', '2025-11-24 20:07:44', NULL),
(2, 'Demo', 'Customer', 'demo@example.com', 'PASSWORD_HASH_PLACEHOLDER', 'customer', '+441234567891', '2025-11-24 20:07:44', '2025-11-24 20:07:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `admin_user_id` (`admin_user_id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD UNIQUE KEY `uk_cart_product` (`cart_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `chatbot_queries`
--
ALTER TABLE `chatbot_queries`
  ADD PRIMARY KEY (`query_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contact_requests`
--
ALTER TABLE `contact_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD UNIQUE KEY `product_id` (`product_id`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `address_id` (`address_id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `idx_user_order` (`user_id`),
  ADD KEY `idx_status` (`order_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD UNIQUE KEY `reset_token` (`reset_token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `order_item_id` (`order_item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_product_rating` (`product_id`,`rating`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`subcategory_id`),
  ADD UNIQUE KEY `uk_cat_sub` (`category_id`,`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `email_2` (`email`),
  ADD KEY `role` (`role`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `uk_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `activity_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `chatbot_queries`
--
ALTER TABLE `chatbot_queries`
  MODIFY `query_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_requests`
--
ALTER TABLE `contact_requests`
  MODIFY `request_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `log_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `subcategory_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD CONSTRAINT `admin_activity_log_ibfk_1` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT;

--
-- Constraints for table `chatbot_queries`
--
ALTER TABLE `chatbot_queries`
  ADD CONSTRAINT `chatbot_queries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `contact_requests`
--
ALTER TABLE `contact_requests`
  ADD CONSTRAINT `contact_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_logs_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`address_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `payment_methods` (`payment_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`subcategory_id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;