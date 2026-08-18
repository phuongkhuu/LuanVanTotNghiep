-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th8 18, 2026 lúc 12:51 PM
-- Phiên bản máy phục vụ: 9.1.0
-- Phiên bản PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `web_ba_lo`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banners`
--

DROP TABLE IF EXISTS `banners`;
CREATE TABLE IF NOT EXISTS `banners` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `banners_campaign_id_foreign` (`campaign_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `banners`
--

INSERT INTO `banners` (`id`, `campaign_id`, `title`, `image`, `link`, `status`, `order`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Banner mùa hè 2024', 'https://bizweb.dktcdn.net/thumb/2048x2048/100/044/266/themes/838180/assets/slider-img3.jpg?1785821424556', 'http://127.0.0.1:8000/san-pham/tui-deo-cheo-thoi-trang-unisex-hier-everyday-messenger', 1, 0, '2026-07-26 01:32:30', '2026-08-18 11:56:40'),
(2, NULL, 'Banner khuyến mãi lớn', 'https://bizweb.dktcdn.net/thumb/2048x2048/100/044/266/themes/838180/assets/slider-img4.jpg?1785821424556', 'https://example.com/big-sale', 0, 1, '2026-07-26 01:32:30', '2026-08-16 10:45:34'),
(3, NULL, 'Banner sắp ra mắt', 'https://bizweb.dktcdn.net/thumb/2048x2048/100/044/266/themes/838180/assets/slider-img8.jpg?1785821424556', 'https://example.com/coming-soon', 0, 2, '2026-07-26 01:32:30', '2026-08-16 10:45:34'),
(4, NULL, 'Banner cũ đã khóa', 'https://bizweb.dktcdn.net/thumb/2048x2048/100/044/266/themes/838180/assets/slider-img10.jpg?1785821424556', 'https://example.com/old-campaign', 0, 3, '2026-07-26 01:32:30', '2026-08-16 10:45:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `brands`
--

DROP TABLE IF EXISTS `brands`;
CREATE TABLE IF NOT EXISTS `brands` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `logo`, `description`, `created_at`, `updated_at`) VALUES
(1, 'BigBag', 'bigbag', '/image/6a841cb3be974.png', 'Thương hiệu chính', '2026-07-26 01:32:28', '2026-08-18 08:49:55'),
(2, 'Solo', 'solo', '/image/6a6675f752062.jpeg', 'Solo Professional', '2026-07-26 01:32:28', '2026-07-26 21:02:47'),
(3, 'KingBag', 'kingbag', '/image/6a6675df50bb3.png', 'KingBag', '2026-07-26 01:32:28', '2026-07-26 21:02:23'),
(4, 'Everki', 'everki', '/image/6a6675cf2624f.png', 'Balo cao cấp Mỹ', '2026-07-26 01:32:28', '2026-07-26 21:02:07'),
(5, 'Targus', 'targus', '/image/6a6675b510789.png', 'Chuyên gia balo doanh nghiệp', '2026-07-26 01:32:28', '2026-07-26 21:01:41'),
(6, 'Samsonite', 'samsonite', '/image/6a66762851e29.png', 'Thương hiệu du lịch toàn cầu', '2026-07-26 01:32:28', '2026-07-26 21:03:36'),
(7, 'MARK RYDEN', 'mark-ryden', '/image/6a75686121eec.jpeg', 'Thương hiệu balo và túi xách công nghệ nổi tiếng gốc Anh Quốc', '2026-08-07 05:08:49', '2026-08-07 05:09:20'),
(8, 'BOPAI LADY', 'bopai-lady', '/image/6a756acb84e22.jpeg', 'Thương hiệu đẳng cấp đến từ nước Ý', '2026-08-07 05:19:07', '2026-08-07 05:19:42'),
(9, 'ROKIN', 'rokin', '/image/6a7ff502b65e5.png', 'Rokin', '2026-08-15 05:11:30', '2026-08-18 09:12:12'),
(10, 'HIER', 'hier', '/image/6a7ffb5c46a41.png', 'Hier', '2026-08-15 05:37:41', '2026-08-18 09:12:02'),
(11, 'Tomtoc', 'tomtoc', '/image/6a83e757d7055.jpeg', 'Tomtoc', '2026-08-18 05:02:15', '2026-08-18 09:11:53');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-tax_info_0111606029', 'a:5:{s:12:\"company_name\";s:56:\"Trường Tiểu học và Trung học cơ sở Vân Hà\";s:5:\"email\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:6:\"status\";s:24:\"NNT đang hoạt động\";s:9:\"is_active\";b:1;}', 1787022417),
('laravel-cache-tax_info_0300588569', 'a:5:{s:12:\"company_name\";s:37:\"CÔNG TY CỔ PHẦN SỮA VIỆT NAM\";s:5:\"email\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:6:\"status\";s:24:\"NNT đang hoạt động\";s:9:\"is_active\";b:1;}', 1787132118),
('laravel-cache-tax_info_0319677692', 'a:5:{s:12:\"company_name\";s:37:\"CÔNG TY TNHH BLOSSOM CREATIVE SAIGON\";s:5:\"email\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:6:\"status\";s:24:\"NNT đang hoạt động\";s:9:\"is_active\";b:1;}', 1787042677),
('laravel-cache-tax_info_1700494236', 'a:5:{s:12:\"company_name\";s:35:\"DOANH NGHIỆP TƯ NHÂN LOAN PHÁT\";s:5:\"email\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:6:\"status\";s:24:\"NNT đang hoạt động\";s:9:\"is_active\";b:1;}', 1786959747),
('laravel-cache-tax_info_3801358804', 'a:5:{s:12:\"company_name\";s:23:\"CÔNG TY TNHH AGRI 1986\";s:5:\"email\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:6:\"status\";s:24:\"NNT đang hoạt động\";s:9:\"is_active\";b:1;}', 1787053407);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
CREATE TABLE IF NOT EXISTS `campaigns` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'seasonal',
  `code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_type` enum('retail','wholesale','preorder','all') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `discount_type` enum('fixed','percent','freeship') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_value` decimal(15,0) NOT NULL DEFAULT '0',
  `min_order` decimal(15,0) NOT NULL DEFAULT '0',
  `limit` int NOT NULL DEFAULT '100',
  `used` int NOT NULL DEFAULT '0',
  `expiry` date DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `banner` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_id` bigint UNSIGNED DEFAULT NULL,
  `priority` int NOT NULL DEFAULT '0',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `tiers` json DEFAULT NULL,
  `current_buyers` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `campaigns_code_unique` (`code`),
  KEY `campaigns_banner_id_foreign` (`banner_id`),
  KEY `campaigns_product_id_foreign` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `campaigns`
--

INSERT INTO `campaigns` (`id`, `name`, `type`, `code`, `target_type`, `discount_type`, `discount_value`, `min_order`, `limit`, `used`, `expiry`, `description`, `start_time`, `end_time`, `status`, `banner`, `banner_id`, `priority`, `featured`, `created_at`, `updated_at`, `product_id`, `tiers`, `current_buyers`) VALUES
(2, 'Chiến dịch 01/07/2025 - 31/07/2025', 'seasonal', NULL, 'all', NULL, 0, 0, 100, 0, NULL, NULL, '2025-06-30 17:00:00', '2025-07-30 17:00:00', 'ended', NULL, NULL, 0, 0, '2026-07-26 01:32:30', '2026-08-18 09:17:22', NULL, NULL, 0),
(4, 'abc', 'preorder', NULL, 'all', NULL, 0, 0, 100, 0, NULL, 'Giảm giá theo số lượt đặt trước', '2026-08-05 17:00:00', '2026-09-04 17:00:00', 'active', NULL, NULL, 0, 0, '2026-08-05 08:48:38', '2026-08-18 09:30:19', 3, '[{\"to\": 10, \"from\": 1, \"discount\": 20}, {\"to\": 20, \"from\": 11, \"discount\": 10}, {\"to\": 30, \"from\": 21, \"discount\": 5}]', 1),
(7, 'test2', 'anniversary', NULL, 'all', NULL, 0, 0, 100, 0, NULL, '', '2026-08-23 17:00:00', '2026-08-26 17:00:00', 'scheduled', NULL, NULL, 0, 0, '2026-08-16 10:53:37', '2026-08-16 10:53:44', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `campaign_configs`
--

DROP TABLE IF EXISTS `campaign_configs`;
CREATE TABLE IF NOT EXISTS `campaign_configs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint UNSIGNED NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `discount_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_configs_campaign_id_foreign` (`campaign_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `campaign_configs`
--

INSERT INTO `campaign_configs` (`id`, `campaign_id`, `quantity`, `discount_percent`, `created_at`, `updated_at`) VALUES
(4, 2, 0, 10.00, '2026-07-26 01:32:30', '2026-08-18 09:17:22'),
(5, 2, 50, 5.00, '2026-07-26 01:32:30', '2026-07-26 01:32:30'),
(6, 2, 100, 10.00, '2026-07-26 01:32:30', '2026-07-26 01:32:30'),
(9, 7, 0, 1.00, '2026-08-16 10:53:37', '2026-08-16 10:53:37');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `campaign_products`
--

DROP TABLE IF EXISTS `campaign_products`;
CREATE TABLE IF NOT EXISTS `campaign_products` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint UNSIGNED NOT NULL,
  `product_variant_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_products_campaign_id_foreign` (`campaign_id`),
  KEY `campaign_products_product_variant_id_foreign` (`product_variant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `campaign_products`
--

INSERT INTO `campaign_products` (`id`, `campaign_id`, `product_variant_id`, `created_at`, `updated_at`) VALUES
(3, 2, 1, '2026-07-26 01:32:30', '2026-07-26 01:32:30'),
(4, 2, 2, '2026-07-26 01:32:30', '2026-07-26 01:32:30'),
(10, 7, 30, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Balo Laptop', 'balo-laptop', 'Balo chuyên dụng cho laptop các size', '/image/6a756f89da55e.jpeg', '2026-07-26 01:32:28', '2026-08-07 05:39:21'),
(2, 'Balo Du lịch', 'balo-du-lich', 'Balo đi phượt, du lịch dung tích lớn', '/image/6a756f7503f05.jpeg', '2026-07-26 01:32:28', '2026-08-07 05:39:01'),
(3, 'Túi đeo chéo', 'tui-deo-cheo', 'Túi đeo chéo thời trang', '/image/6a756f6400aa2.jpeg', '2026-07-26 01:32:28', '2026-08-07 05:38:44'),
(4, 'Balo thời trang', 'balo-thoi-trang', 'Balo thiết kế đẹp, dùng hàng ngày', '/image/6a667210a505f.jpeg', '2026-07-26 01:32:28', '2026-07-26 20:46:08'),
(5, 'Balo chống sốc', 'balo-chong-soc', 'Balo có đệm bảo vệ laptop, máy tính bảng', '/image/6a6671eec9893.jpeg', '2026-07-26 01:32:28', '2026-07-26 20:45:34'),
(6, 'Túi Thể Thao', 'tui-the-thao', 'Balo có đệm bảo vệ laptop, máy tính bảng', '/image/6a7ff9c6d988b.jpeg', '2026-08-15 05:31:50', '2026-08-18 11:52:21'),
(7, 'Cặp xách chống sốc', 'cap-xach-chong-soc', 'Balo có đệm bảo vệ laptop, máy tính bảng', '/image/6a83e26fc029f.png', '2026-08-18 04:41:19', '2026-08-18 11:52:15'),
(8, 'Túi chống sốc', 'tui-chong-soc', 'Balo có đệm bảo vệ laptop, máy tính bảng', '/image/6a83e6486a951.jpeg', '2026-08-18 04:57:44', '2026-08-18 11:52:07');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chatbot_messages`
--

DROP TABLE IF EXISTS `chatbot_messages`;
CREATE TABLE IF NOT EXISTS `chatbot_messages` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender` enum('user','bot') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chatbot_messages_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chatbot_messages`
--

INSERT INTO `chatbot_messages` (`id`, `user_id`, `message`, `sender`, `created_at`, `updated_at`) VALUES
(1, 2, 'Balo này có chống nước không?', 'user', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(2, 2, 'Có, sản phẩm được làm từ vải Ballistic Nylon chống nước tuyệt đối.', 'bot', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(3, NULL, 'Hãy gợi ý một số sản phẩm', 'user', '2026-08-06 14:36:33', '2026-08-06 14:36:33'),
(4, NULL, 'Xin lỗi, không thể tổng hợp kết quả. Vui lòng thử lại sau.', 'bot', '2026-08-06 14:36:34', '2026-08-06 14:36:34'),
(5, 5, 'Hãy tư vấn cho tôi vài mẫu sản phẩm', 'user', '2026-08-07 03:19:16', '2026-08-07 03:19:16'),
(6, 5, 'Chào bạn, mình rất vui được hỗ trợ bạn chọn mẫu balo ưng ý nhé. Hiện tại cửa hàng mình đang có vài mẫu rất chất lượng để bạn tham khảo đây.\n\nĐầu tiên là chiếc Balo Laptop BigBag Pro 15.6 inch của thương hiệu BigBag có giá 1.450.000 đồng và hiện không có khuyến mãi, mẫu này có chất liệu vải dù rất bền với các màu đen và xám, bạn có thể xem qua hình ảnh sản phẩm để thấy rõ thiết kế đa năng tích hợp cổng USB của nó. Nếu bạn thường xuyên đi du lịch hay leo núi thì có mẫu Solo Adventure 40L từ nhà Solo với giá 2.100.000 đồng, đây là dòng balo dung tích lớn có nhiều ngăn tiện lợi và ảnh chụp thực tế cho thấy ngăn chứa cực kỳ rộng rãi. Với những ai tìm kiếm sự nhỏ gọn để dạo phố thì KingBag Crossbody Mini làm từ da PU cao cấp là lựa chọn tuyệt vời với mức giá chỉ 450.000 đồng, nhìn trong ảnh bạn sẽ thấy phong cách tối giản rất thời trang và hiện đại. Dành riêng cho những bạn dùng laptop cỡ lớn thì mình có Everki Atlas 17.3 inch với khả năng chống sốc cao cấp, giá của em này là 2.350.000 đồng và hình ảnh sản phẩm thể hiện rất rõ sự chuyên nghiệp với nhiều ngăn đựng phụ kiện riêng biệt. Cuối cùng là mẫu balo thời trang Targus CitySmart 15.6 inch màu xanh navy trẻ trung có giá 1.250.000 đồng, mẫu này qua ảnh trông rất nhẹ nhàng và thanh lịch, cực kỳ phù hợp cho các bạn làm việc trong môi trường văn phòng.\n\nBạn thấy ưng ý mẫu nào nhất trong số này không, hãy nhắn cho mình biết để mình tư vấn kỹ hơn về màu sắc và kích thước cho bạn nhé.', 'bot', '2026-08-07 03:19:16', '2026-08-07 03:19:16'),
(7, 5, 'Hãy tư vấn cho tôi vài mẫu sản phẩm', 'user', '2026-08-07 03:22:28', '2026-08-07 03:22:28'),
(8, 5, 'Đã có lỗi xảy ra khi tổng hợp kết quả.', 'bot', '2026-08-07 03:22:28', '2026-08-07 03:22:28'),
(9, 5, 'Hãy tư vấn cho tôi vài mẫu sản phẩm', 'user', '2026-08-07 03:24:29', '2026-08-07 03:24:29'),
(10, 5, 'Chào bạn nha, mình rất vui được hỗ trợ bạn tìm hiểu về các mẫu sản phẩm đang có tại cửa hàng. Đầu tiên là chiếc Balo Laptop BigBag Pro 15.6 inch cực kỳ bền bỉ nhờ chất liệu Ballistic Nylon 1680D với mức giá là 1.450.000 đồng. Nếu bạn thường xuyên đi xa thì mẫu Solo Adventure 40L với dung tích lớn và nhiều ngăn tiện lợi sẽ là người bạn đồng hành lý tưởng với giá 2.100.000 đồng. Đối với những ai yêu thích sự tối giản và thời trang thì túi đeo chéo KingBag Crossbody Mini làm từ da PU cao cấp có giá chỉ 450.000 đồng là một lựa chọn rất đáng cân nhắc. Bên cạnh đó mình còn có mẫu Everki Atlas 17.3 inch chuyên dụng để chống sốc cho các dòng laptop cỡ lớn với mức giá 2.350.000 đồng. Cuối cùng là chiếc Targus CitySmart 15.6 inch mang phong cách văn phòng thanh lịch và gọn nhẹ có giá 1.250.000 đồng. Bạn cảm thấy ấn tượng với mẫu nào nhất thì nhắn mình để mình tư vấn kỹ hơn nhé.', 'bot', '2026-08-07 03:24:29', '2026-08-07 03:24:29'),
(11, 5, 'Hãy tư vấn cho tôi vài mẫu sản phẩm', 'user', '2026-08-07 03:28:27', '2026-08-07 03:28:27'),
(12, 5, 'Đã có lỗi xảy ra khi tổng hợp kết quả.', 'bot', '2026-08-07 03:28:27', '2026-08-07 03:28:27'),
(13, 5, 'Hãy tư vấn cho tôi vài mẫu sản phẩm', 'user', '2026-08-07 03:41:28', '2026-08-07 03:41:28'),
(14, 5, 'Chào bạn, BigBag rất vui được hỗ trợ bạn chọn lựa sản phẩm phù hợp nhé. Hiện tại cửa hàng đang có mẫu Balo Laptop BigBag Pro 15.6 inch với giá 1.450.000 VND, đây là dòng balo thiết kế đa năng có tích hợp cổng USB rất tiện lợi cho việc sạc điện thoại khi di chuyển. Nếu bạn đang tìm kiếm một chiếc balo cho những chuyến đi xa thì Solo Adventure 40L giá 2.100.000 VND là lựa chọn tuyệt vời với dung tích lớn và nhiều ngăn chứa đồ tiện dụng. Đối với những bạn yêu thích sự gọn nhẹ và thời trang thì túi đeo chéo KingBag Crossbody Mini giá 450.000 VND với phong cách tối giản sẽ rất hợp lý. Ngoài ra, nếu bạn sở hữu một chiếc laptop cỡ lớn thì mẫu Everki Atlas 17.3 inch giá 2.350.000 VND là dòng cao cấp với khả năng chống sốc vượt trội và nhiều ngăn phụ kiện đi kèm. Cuối cùng, chiếc Targus CitySmart 15.6 inch giá 1.250.000 VND mang phong cách văn phòng hiện đại, trọng lượng nhẹ và có khả năng chống nước nhẹ cũng là một gợi ý rất đáng cân nhắc. Hy vọng những thông tin này sẽ giúp bạn tìm được sản phẩm ưng ý, nếu cần tư vấn thêm bạn cứ nhắn mình nha.', 'bot', '2026-08-07 03:41:28', '2026-08-07 03:41:28'),
(15, 5, 'Tư vấn vài mẫu balo', 'user', '2026-08-07 03:46:18', '2026-08-07 03:46:18'),
(16, 5, 'Chào bạn nhé, hiện tại cửa hàng mình đang có một vài mẫu balo rất được ưa chuộng để bạn tham khảo đây ạ. Nếu bạn cần một chiếc balo để đi làm hoặc đi học thì mẫu Balo Laptop BigBag Pro 15.6 inch với mức giá 1.450.000 VND là lựa chọn tuyệt vời vì nó có thiết kế đa năng và tích hợp cả cổng USB tiện lợi. Đối với các bạn nữ yêu thích sự trẻ trung thì bên mình có mẫu Balo thời trang nữ BigBag giá chỉ 550.000 VND với nhiều màu sắc xinh xắn và đựng vừa laptop 14.1 inch. Còn nếu bạn đang chuẩn bị cho một chuyến đi chơi xa dài ngày thì đừng bỏ qua chiếc Balo du lịch lớn Samsonite 50L có giá 2.850.000 VND với không gian chứa đồ cực kỳ rộng rãi. Bạn ưng ý mẫu nào thì nhắn mình hỗ trợ thêm nhé.', 'bot', '2026-08-07 03:46:18', '2026-08-07 03:46:18'),
(17, 5, 'Tư vấn vài mẫu balo', 'user', '2026-08-07 03:47:29', '2026-08-07 03:47:29'),
(18, 5, 'Chào bạn! Dưới đây là 3 mẫu balo với các phong cách khác nhau (laptop, thời trang, du lịch) để bạn tham khảo:\n\n---\n\n### 1. Balo Laptop BigBag Pro 15.6 inch\n<img src=\'https://bizweb.dktcdn.net/100/044/266/products/balo-laptop-du-lich-cao-cap-thiet-ke-da-nang-tich-hop-cong-usb-fix-laptop-15-6-inch-mark-ryden-compacto-pro-12.png?v=1754064998880\' alt=\'Balo Laptop BigBag Pro 15.6 inch\' style=\'max-width:120px; height:auto; border-radius:8px;\'>\n\n*   **Thương hiệu:** BigBag\n*   **Giá:** 1.450.000 VND\n*   **Khuyến mãi:** Không có\n*   **Đặc điểm:** Chất liệu Ballistic Nylon 1680D siêu bền, thiết kế chuyên dụng cho laptop 15.6 inch, phù hợp cho công sở và đi làm hàng ngày.\n\n---\n\n### 2. Balo thời trang nữ BigBag\n<img src=\'https://bizweb.dktcdn.net/100/044/266/files/balo-thoi-trang-nu-tinh-fix-laptop-mong-nhe-14-1-inch-bopai-lady-pink-4.png?v=1746292069590\' alt=\'Balo thời trang nữ BigBag\' style=\'max-width:120px; height:auto; border-radius:8px;\'>\n\n*   **Thương hiệu:** BigBag\n*   **Giá:** 550.000 VND\n*   **Khuyến mãi:** Không có\n*   **Đặc điểm:** Chất liệu vải Canvas trẻ trung, nhiều màu sắc (Đỏ, Trắng), kiểu dáng nhỏ gọn, nữ tính.\n\n---\n\n### 3. Balo du lịch lớn Samsonite 50L\n<img src=\'https://cdn.hstatic.net/products/200000321545/xanhla_31x24x44cm_1_6e936579337d4369a507ace92ee90955_master.jpg\' alt=\'Balo du lịch lớn Samsonite 50L\' style=\'max-width:120px; height:auto; border-radius:8px;\'>\n\n*   **Thương hiệu:** Samsonite\n*   **Giá:** 2.850.000 VND\n*   **Khuyến mãi:** Không có\n*   **Đặc điểm:** Dung tích lớn 50L, chất liệu Nylon 600D chắc chắn, cực kỳ phù hợp cho những chuyến đi du lịch hoặc dã ngoại dài ngày.\n\n---\n\nNếu bạn cần thêm thông tin chi tiết về màu sắc hoặc kích thước cụ thể của từng mẫu, đừng ngần ngại hỏi mình nhé!', 'bot', '2026-08-07 03:47:29', '2026-08-07 03:47:29'),
(19, 5, 'Tư vấn vài mẫu balo', 'user', '2026-08-07 03:50:47', '2026-08-07 03:50:47'),
(20, 5, 'Xin lỗi, không thể tổng hợp kết quả. Vui lòng thử lại sau.', 'bot', '2026-08-07 03:50:47', '2026-08-07 03:50:47'),
(21, 5, 'Tư vấn vài mẫu balo', 'user', '2026-08-07 04:01:19', '2026-08-07 04:01:19'),
(22, 5, 'Đã có lỗi xảy ra khi tổng hợp kết quả.', 'bot', '2026-08-07 04:01:19', '2026-08-07 04:01:19'),
(23, 5, 'Tư vấn vài mẫu balo', 'user', '2026-08-07 04:03:34', '2026-08-07 04:03:34'),
(24, 5, 'Chào bạn, mình rất vui được gợi ý cho bạn một vài mẫu balo chất lượng đang có sẵn nhé. Đầu tiên là chiếc Balo Laptop BigBag Pro 15.6 inch đến từ thương hiệu BigBag với mức giá 1.450.000 VND. <img src=\"https://bizweb.dktcdn.net/100/044/266/products/balo-laptop-du-lich-cao-cap-thiet-ke-da-nang-tich-hop-cong-usb-fix-laptop-15-6-inch-mark-ryden-compacto-pro-12.png?v=1754064998880\" alt=\"Balo Laptop BigBag Pro 15.6 inch\" style=\"max-width:120px; height:auto; border-radius:8px;\"> Mẫu này cực kỳ bền bỉ nhờ chất liệu Ballistic Nylon 1680D cao cấp, thiết kế rất chuyên nghiệp và chắc chắn, cực kỳ phù hợp để bạn bảo vệ máy tính khi đi làm hay đi công tác. Nếu bạn đang tìm kiếm sự nhẹ nhàng và trẻ trung thì đừng bỏ qua mẫu Balo thời trang nữ BigBag có giá chỉ 550.000 VND. <img src=\"https://bizweb.dktcdn.net/100/044/266/files/balo-thoi-trang-nu-tinh-fix-laptop-mong-nhe-14-1-inch-bopai-lady-pink-4.png?v=1746292069590\" alt=\"Balo thời trang nữ BigBag\" style=\"max-width:120px; height:auto; border-radius:8px;\"> Đây là dòng balo làm từ vải Canvas với nhiều màu sắc tươi tắn như đỏ và trắng, mang lại vẻ ngoài năng động và nữ tính cho các bạn gái khi đi học hay dạo phố. Cuối cùng, dành cho những chuyến đi xa thì chiếc Balo du lịch lớn Samsonite 50L của thương hiệu Samsonite danh tiếng là lựa chọn không thể bỏ qua với giá 2.850.000 VND. <img src=\"https://cdn.hstatic.net/products/200000321545/xanhla_31x24x44cm_1_6e936579337d4369a507ace92ee90955_master.jpg\" alt=\"Balo du lịch lớn Samsonite 50L\" style=\"max-width:120px; height:auto; border-radius:8px;\"> Với dung tích cực lớn lên đến 50L và chất liệu Nylon 600D bền bỉ, chiếc balo này sẽ là người bạn đồng hành lý tưởng giúp bạn thoải mái sắp xếp đồ đạc cho những hành trình dài ngày. Hy vọng những gợi ý này sẽ giúp bạn tìm được chiếc balo ưng ý nhất!', 'bot', '2026-08-07 04:03:34', '2026-08-07 04:03:34'),
(25, NULL, 'Hãy liệt kê các chương trình khuyến mãi đang hiện hành', 'user', '2026-08-08 12:43:59', '2026-08-08 12:43:59'),
(26, NULL, 'Dưới đây là thông tin chi tiết về các chương trình khuyến mãi đang diễn ra mà bạn có thể tham khảo:\n\n1.  **Chiến dịch 01/06/2025 - 30/06/2025**:\n    *   **Mức ưu đãi:** Giảm giá cực sâu lên đến **50.00%**.\n    *   **Thời gian áp dụng:** Chương trình bắt đầu từ ngày 01/06/2025 và kết thúc vào ngày 30/06/2025.\n    *   **Điều kiện:** Ưu đãi này được áp dụng cho các đơn hàng phát sinh trong suốt tháng 6 năm 2025. Đây là cơ hội lớn để mua sắm với mức giá chỉ bằng một nửa so với thông thường.\n\n2.  **Campaign Summer 2024**:\n    *   **Mức ưu đãi:** Hiện tại hệ thống ghi nhận mức giảm giá là **0 VND**.\n    *   **Đặc điểm:** Đây là chương trình dành riêng cho mùa hè năm 2024. \n    *   **Lưu ý:** Với mức giảm 0 VND, chương trình này có thể đang tập trung vào các hình thức ưu đãi khác không phải giảm giá trực tiếp bằng tiền (ví dụ: tặng quà kèm theo, tích điểm thành viên, hoặc miễn phí vận chuyển). Bạn nên kiểm tra thêm chi tiết về quà tặng đi kèm nếu có.\n\nNếu bạn cần biết thêm điều kiện cụ thể cho từng nhóm sản phẩm, hãy cho tôi biết nhé!', 'bot', '2026-08-08 12:43:59', '2026-08-08 12:43:59'),
(27, NULL, 'Hãy liệt kê các chương trình khuyến mãi đang hiện hành', 'user', '2026-08-08 12:49:51', '2026-08-08 12:49:51'),
(28, NULL, 'Chào bạn, hiện tại cửa hàng BigBag đang triển khai hai chương trình khuyến mãi đặc biệt để tri ân khách hàng. Chương trình đầu tiên là Chiến dịch diễn ra từ ngày 01/06/2025 đến ngày 30/06/2025 mang đến ưu đãi giảm giá cực khủng lên tới 50.00% cho các sản phẩm tại cửa hàng. Ngoài ra chúng mình còn có chương trình Campaign Summer 2024 đang được áp dụng với mức giảm giá là 0 VND dành cho các khách hàng tham gia. Bạn hãy tranh thủ thời gian này để ghé thăm BigBag và lựa chọn cho mình những mẫu balo chất lượng với mức giá ưu đãi nhất nhé.', 'bot', '2026-08-08 12:49:51', '2026-08-08 12:49:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `colors`
--

DROP TABLE IF EXISTS `colors`;
CREATE TABLE IF NOT EXISTS `colors` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `colors`
--

INSERT INTO `colors` (`id`, `name`, `code`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Đen', '#000000', 1, '2026-07-26 01:32:28', '2026-07-26 01:35:23'),
(2, 'Xám', '#808080', 1, '2026-07-26 01:32:28', '2026-07-26 01:35:21'),
(3, 'Xanh Navy', '#000080', 1, '2026-07-26 01:32:28', '2026-07-26 01:35:20'),
(4, 'Nâu', '#8B4513', 1, '2026-07-26 01:32:28', '2026-07-26 01:35:17'),
(5, 'Đỏ', '#FF0000', 1, '2026-07-26 01:32:28', '2026-07-26 01:35:15'),
(6, 'Xanh Lá', '#008000', 1, '2026-07-26 01:32:28', '2026-07-26 01:35:13'),
(7, 'Cam', '#FFA500', 1, '2026-07-26 01:32:28', '2026-07-26 01:35:12'),
(8, 'Trắng', '#FFFFFF', 1, '2026-07-26 01:32:28', '2026-07-26 01:35:09'),
(9, 'Hồng', '#E2A6B0', 1, '2026-08-07 05:20:07', '2026-08-18 09:11:27'),
(10, 'Xanh Ô-Liu', '#BAB86C', 1, '2026-08-15 05:17:34', '2026-08-15 05:17:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `discounts`
--

DROP TABLE IF EXISTS `discounts`;
CREATE TABLE IF NOT EXISTS `discounts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `min_quantity` int NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `discounts`
--

INSERT INTO `discounts` (`id`, `min_quantity`, `discount_percent`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 50, 5.00, 1, '2026-07-26 01:32:30', '2026-08-18 09:33:40'),
(2, 100, 10.00, 1, '2026-07-26 01:32:30', '2026-08-18 09:33:46'),
(3, 200, 15.00, 1, '2026-07-26 01:32:30', '2026-08-18 09:33:50');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `logo_print_requests`
--

DROP TABLE IF EXISTS `logo_print_requests`;
CREATE TABLE IF NOT EXISTS `logo_print_requests` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_detail_id` bigint UNSIGNED NOT NULL,
  `logo_image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `print_position` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `print_size` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `logo_print_requests_order_detail_id_foreign` (`order_detail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `logo_print_requests`
--

INSERT INTO `logo_print_requests` (`id`, `order_detail_id`, `logo_image`, `print_position`, `print_size`, `note`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'https://example.com/logos/logo1.png', 'front', 'medium', 'In màu trắng, kích thước 5x5cm', 'pending', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(2, 224, 'logos/d4a5c781-6ebe-415b-be96-604025692145.jpg', 'front', 'medium', 'abc\n\n---\nKhách hàng: Thanh Phương Khưu\nEmail: thanhphuongkhuu@gmail.com\nSĐT: 12345677890', 'pending', '2026-08-05 18:10:11', '2026-08-05 18:10:11'),
(3, 234, 'logos/2d6fa7c8-d248-4798-b8ed-6935f9a7d8aa.png', 'back', 'small', '\n\n---\nKhách hàng: Thanh Phương Khưu\nEmail: thanhphuongkhuu@gmail.com\nSĐT: 0123456789', 'pending', '2026-08-15 09:29:19', '2026-08-15 09:29:19'),
(4, 235, 'logos/853e6648-bed5-439f-9eaf-8e340764e0fc.jpg', 'front', 'large', '\n\n---\nKhách hàng: P\nEmail: thanhphuongkhuu@gmail.com\nSĐT: 0123456789', 'pending', '2026-08-15 10:20:48', '2026-08-15 10:20:48'),
(5, 236, 'logos/853e6648-bed5-439f-9eaf-8e340764e0fc.jpg', 'front', 'large', '\n\n---\nKhách hàng: P\nEmail: thanhphuongkhuu@gmail.com\nSĐT: 0123456789', 'pending', '2026-08-15 10:31:13', '2026-08-15 10:31:13'),
(6, 239, 'logos/bc7b483e-0403-4a9a-bbde-e05389dbf1e8.jpg', 'back', 'small', '\n\n---\nKhách hàng: P\nEmail: thanhphuongkhuu@gmail.com\nSĐT: 0123456789', 'approved', '2026-08-16 10:09:47', '2026-08-17 08:21:37'),
(7, 247, 'logos/9dd6853c-0fbc-4398-8a7b-dc2266aefb8c.png', 'front', 'small', '\n\n---\nKhách hàng: 123\nEmail: thanhphuongkhuu@gmail.com\nSĐT: 123', 'approved', '2026-08-17 07:59:10', '2026-08-17 08:14:44'),
(8, 248, 'logos/8f3415b4-72da-4ad2-8d97-332d713ed95e.jpg', 'back', 'small', '\n\n---\nKhách hàng: 1\nEmail: thanhphuongkhuu@gmail.com\nSĐT: 0123456789', 'pending', '2026-08-17 08:29:51', '2026-08-17 08:29:51'),
(9, 249, 'logos/8f3415b4-72da-4ad2-8d97-332d713ed95e.jpg', 'back', 'small', '\n\n---\nKhách hàng: 1\nEmail: thanhphuongkhuu@gmail.com\nSĐT: 0123456789', 'approved', '2026-08-17 08:31:42', '2026-08-17 08:32:20'),
(10, 251, 'logos/3cac51d6-430a-4ad3-b1a0-42d0b97b9aa9.png', 'back', 'medium', '\n\n---\nKhách hàng: Thanh Phuong Khuu\nEmail: thanhphuongkhuu@gmail.com\nSĐT: 0123456789', 'pending', '2026-08-17 08:48:16', '2026-08-17 08:48:16'),
(11, 252, 'logos/3cac51d6-430a-4ad3-b1a0-42d0b97b9aa9.png', 'back', 'medium', '\n\n---\nKhách hàng: Thanh Phuong Khuu\nEmail: thanhphuongkhuu@gmail.com\nSĐT: 0123456789', 'approved', '2026-08-17 08:48:43', '2026-08-17 10:24:40'),
(12, 253, 'logos/3cac51d6-430a-4ad3-b1a0-42d0b97b9aa9.png', 'back', 'medium', '\n\n---\nKhách hàng: Thanh Phuong Khuu\nEmail: thanhphuongkhuu@gmail.com\nSĐT: 0123456789', 'rejected', '2026-08-17 08:51:37', '2026-08-17 08:52:05'),
(13, 254, 'logos/dd57bfa0-9c66-4f45-9389-c46104dcc2f4.png', 'front', 'large', '\n\n---\nKhách hàng: Thanh Phuong Khuu\nEmail: thanhphuongkhuu@gmail.com\nSĐT: 0123456789', 'approved', '2026-08-17 08:52:50', '2026-08-17 08:53:03'),
(14, 261, 'logos/47366785-f767-49db-8561-fc33b3a9a029.png', 'front', 'medium', '\n\n---\nKhách hàng: Py\nEmail: trucmy10042004@gmail.com\nSĐT: 0907868956', 'approved', '2026-08-18 09:53:15', '2026-08-18 09:54:14');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_04_163751_add_columns_to_users_table', 1),
(5, '2026_06_04_164515_create_categories_table', 1),
(6, '2026_06_04_164531_create_brands_table', 1),
(7, '2026_06_04_164541_create_products_table', 1),
(8, '2026_06_04_164616_create_colors_table', 1),
(9, '2026_06_04_164625_create_product_variants_table', 1),
(10, '2026_06_04_164639_create_discounts_table', 1),
(11, '2026_06_04_164648_create_banners_table', 1),
(12, '2026_06_04_164649_create_campaigns_table', 1),
(13, '2026_06_04_164658_create_campaign_products_table', 1),
(14, '2026_06_04_164706_create_campaign_configs_table', 1),
(15, '2026_06_04_164719_create_orders_table', 1),
(16, '2026_06_04_164745_create_order_details_table', 1),
(17, '2026_06_04_164755_create_payments_table', 1),
(18, '2026_06_04_164807_create_reviews_table', 1),
(19, '2026_06_04_164817_create_news_table', 1),
(20, '2026_06_04_164837_create_quote_requests_table', 1),
(21, '2026_06_04_164848_create_quote_request_details_table', 1),
(22, '2026_06_04_164901_create_logo_print_requests_table', 1),
(23, '2026_06_04_164918_create_chatbot_messages_table', 1),
(24, '2026_06_07_130856_update_orders_column_type', 1),
(25, '2026_06_07_131700_rename_type_to_order_code_in_orders_table', 1),
(26, '2026_06_08_143322_change_image_column_to_text_in_categories_table', 1),
(27, '2026_06_09_024229_add_slug_to_brands_table', 1),
(28, '2026_06_09_084119_rename_color_column_to_name_in_colors_table', 1),
(29, '2026_06_09_091352_drop_sizes_table', 1),
(30, '2026_06_09_150113_create_settings_table', 1),
(31, '2026_06_10_163246_add_code_to_colors_table', 1),
(32, '2026_06_11_fix_color_names', 1),
(33, '2026_06_20_111820_add_customer_fields_to_orders_table', 1),
(34, '2026_06_27_020426_update_image_url_to_json_in_products_table', 1),
(35, '2026_07_06_195039_add_fields_to_campaigns_table', 1),
(36, '2026_07_08_030713_add_campaigns_id_to_banners_table', 1),
(37, '2026_07_09_120012_add_promotion_fields_to_campaigns_table', 1),
(38, '2026_07_11_092453_add_is_active_to_colors_table', 1),
(39, '2026_07_15_021924_add_promo_code_to_orders_table', 1),
(40, '2026_07_16_062124_add_sale_fields_to_product_variants_table', 1),
(41, '2026_07_21_104739_add_order_number_to_orders_table', 1),
(42, '2026_07_24_053334_add_deposit_fields_to_orders_table', 1),
(43, '2026_08_08_205737_add_import_columns_to_product_variants_table', 2),
(45, '2026_08_15_155359_add_confirmation_token_to_orders_table', 3),
(46, '2026_08_17_145844_update_order_code_enum_add_customize', 4),
(47, '2026_08_17_152912_add_customer_email_to_orders_table', 5);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE IF NOT EXISTS `news` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `author_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `campaign_id` bigint UNSIGNED DEFAULT NULL,
  `banner_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `news_slug_unique` (`slug`),
  KEY `news_campaign_id_foreign` (`campaign_id`),
  KEY `news_banner_id_foreign` (`banner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `thumbnail`, `content`, `status`, `author_name`, `campaign_id`, `banner_id`, `created_at`, `updated_at`) VALUES
(1, 'BigBag ra mắt dòng sản phẩm mới - Balo chống sốc cao cấp', 'bigbag-ra-mat-dong-san-pham-moi', 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=1200&h=585&fit=crop', '<p>BigBag vừa cho ra mắt dòng balo chống sốc mới với công nghệ đệm tổ ong, bảo vệ laptop tối đa...</p>', 0, 'BigBag Admin', NULL, 1, '2026-07-26 01:32:41', '2026-08-18 09:39:13'),
(2, 'Chương trình khuyến mãi hè: Giảm giá lên đến 30% cho đơn hàng sỉ', 'chuong-trinh-khuyen-mai-he', 'https://bizweb.dktcdn.net/thumb/2048x2048/100/044/266/themes/838180/assets/slider-img3.jpg?1785821424556', '<p>Áp dụng cho tất cả khách hàng doanh nghiệp từ ngày 1/6 đến 30/6...</p>', 0, 'BigBag Admin', NULL, 1, '2026-07-26 01:32:41', '2026-08-18 11:56:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmation_token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expires_at` timestamp NULL DEFAULT NULL,
  `is_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `customer_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_id` bigint UNSIGNED DEFAULT NULL,
  `campaign_id` bigint UNSIGNED DEFAULT NULL,
  `order_code` enum('retail','wholesale','preorder','customize') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'retail',
  `receiver_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `receiver_phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_fee` decimal(12,0) NOT NULL DEFAULT '0',
  `total_amount` decimal(12,0) NOT NULL,
  `discount_amount` decimal(12,0) NOT NULL DEFAULT '0',
  `promo_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_amount` decimal(12,0) NOT NULL,
  `deposit_amount` decimal(15,0) NOT NULL DEFAULT '0',
  `remaining_amount` decimal(15,0) NOT NULL DEFAULT '0',
  `payment_status` enum('pending','deposit_paid','paid','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `order_status` tinyint NOT NULL DEFAULT '0',
  `shipping_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  UNIQUE KEY `orders_confirmation_token_unique` (`confirmation_token`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_discount_id_foreign` (`discount_id`),
  KEY `orders_campaign_id_foreign` (`campaign_id`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `confirmation_token`, `token_expires_at`, `is_confirmed`, `user_id`, `customer_name`, `customer_phone`, `customer_email`, `discount_id`, `campaign_id`, `order_code`, `receiver_name`, `receiver_phone`, `shipping_fee`, `total_amount`, `discount_amount`, `promo_code`, `final_amount`, `deposit_amount`, `remaining_amount`, `payment_status`, `order_status`, `shipping_address`, `note`, `created_at`, `updated_at`) VALUES
(1, 'P21072600001', NULL, NULL, 0, 1, 'Phạm Thị D', '0945678901', NULL, NULL, NULL, 'preorder', 'Lê Văn C', '0934567890', 0, 5700000, 0, NULL, 5700000, 0, 0, 'pending', 4, 'Địa chỉ 76, Quận 5, TP.HCM', NULL, '2026-07-21 04:12:48', '2026-07-21 04:12:48'),
(2, 'S22072600001', NULL, NULL, 0, 1, 'Công ty TNHH Tech', '0978901234', NULL, NULL, NULL, 'wholesale', 'Lê Văn C', '0934567890', 0, 15050000, 0, NULL, 15050000, 0, 0, 'pending', 1, 'Địa chỉ 8, Quận 4, TP.HCM', NULL, '2026-07-22 02:10:03', '2026-07-22 02:10:03'),
(3, 'P11072600001', NULL, NULL, 0, 1, 'Lê Văn C', '0934567890', NULL, NULL, NULL, 'preorder', 'Doanh nghiệp XYZ', '0956789012', 0, 5650000, 0, NULL, 5650000, 0, 0, 'pending', 3, 'Địa chỉ 35, Quận 12, TP.HCM', 'Ghi chú đơn hàng 2', '2026-07-11 06:22:16', '2026-07-11 06:22:16'),
(4, 'L02072600001', NULL, NULL, 0, 1, 'Công ty TNHH Tech', '0978901234', NULL, NULL, NULL, 'retail', 'Lê Văn C', '0934567890', 25884, 2700000, 0, NULL, 2725884, 0, 0, 'pending', 0, 'Địa chỉ 48, Quận 7, TP.HCM', 'Ghi chú đơn hàng 3', '2026-07-02 03:03:52', '2026-07-02 03:03:52'),
(5, 'P26072600001', NULL, NULL, 0, 1, 'Ngô Văn E', '0967890123', NULL, NULL, NULL, 'preorder', 'Lê Văn C', '0934567890', 0, 1980000, 0, NULL, 1980000, 0, 0, 'pending', 5, 'Địa chỉ 33, Quận 7, TP.HCM', 'Ghi chú đơn hàng 4', '2026-07-26 06:31:47', '2026-08-05 09:04:56'),
(6, 'S16072600001', NULL, NULL, 0, 1, 'Doanh nghiệp XYZ', '0956789012', NULL, NULL, NULL, 'wholesale', 'Công ty TNHH Tech', '0978901234', 0, 13500000, 0, NULL, 13500000, 0, 0, 'pending', 3, 'Địa chỉ 93, Quận 4, TP.HCM', NULL, '2026-07-16 06:39:46', '2026-07-16 06:39:46'),
(7, 'S22072600002', NULL, NULL, 0, 1, 'Lê Văn C', '0934567890', NULL, NULL, NULL, 'wholesale', 'Phạm Thị D', '0945678901', 0, 2200000, 0, NULL, 2200000, 0, 0, 'pending', 2, 'Địa chỉ 90, Quận 4, TP.HCM', 'Ghi chú đơn hàng 6', '2026-07-22 02:39:44', '2026-07-22 02:39:44'),
(8, 'S18072600001', NULL, NULL, 0, 1, 'Phạm Thị D', '0945678901', NULL, NULL, NULL, 'wholesale', 'Nguyễn Văn A', '0901234567', 0, 5000000, 0, NULL, 5000000, 0, 0, 'pending', 1, 'Địa chỉ 63, Quận 7, TP.HCM', 'Ghi chú đơn hàng 7', '2026-07-18 10:09:25', '2026-07-18 10:09:25'),
(9, 'S29062600001', NULL, NULL, 0, 1, 'Lê Văn C', '0934567890', NULL, NULL, NULL, 'wholesale', 'Lê Văn C', '0934567890', 0, 1400000, 0, NULL, 1400000, 0, 0, 'pending', 1, 'Địa chỉ 10, Quận 8, TP.HCM', NULL, '2026-06-29 09:17:54', '2026-06-29 09:17:54'),
(10, 'P03072600001', NULL, NULL, 0, 1, 'Công ty TNHH Tech', '0978901234', NULL, NULL, NULL, 'preorder', 'Phạm Thị D', '0945678901', 0, 4300000, 0, NULL, 4300000, 0, 0, 'pending', 3, 'Địa chỉ 49, Quận 4, TP.HCM', NULL, '2026-07-03 15:42:42', '2026-07-03 15:42:42'),
(11, 'S21072600001', NULL, NULL, 0, 1, 'Phạm Thị D', '0945678901', NULL, NULL, NULL, 'wholesale', 'Nguyễn Văn A', '0901234567', 0, 9350000, 0, NULL, 9350000, 0, 0, 'pending', 3, 'Địa chỉ 49, Quận 2, TP.HCM', 'Ghi chú đơn hàng 10', '2026-07-21 04:37:14', '2026-07-21 04:37:14'),
(12, 'P30062600001', NULL, NULL, 0, 1, 'Công ty TNHH Tech', '0978901234', NULL, NULL, NULL, 'preorder', 'Phạm Thị D', '0945678901', 0, 10390000, 0, NULL, 10390000, 0, 0, 'pending', 2, 'Địa chỉ 68, Quận 8, TP.HCM', 'Ghi chú đơn hàng 11', '2026-06-30 02:28:01', '2026-06-30 02:28:01'),
(13, 'S12072600001', NULL, NULL, 0, 1, 'Nguyễn Văn A', '0901234567', NULL, NULL, NULL, 'wholesale', 'Ngô Văn E', '0967890123', 0, 18560000, 0, NULL, 18560000, 0, 0, 'pending', 0, 'Địa chỉ 5, Quận 6, TP.HCM', 'Ghi chú đơn hàng 12', '2026-07-12 14:33:32', '2026-07-12 14:33:32'),
(14, 'L08072600001', NULL, NULL, 0, 1, 'Trần Thị B', '0912345678', NULL, NULL, NULL, 'retail', 'Công ty TNHH Tech', '0978901234', 20451, 8700000, 0, NULL, 8720451, 0, 0, 'pending', 1, 'Địa chỉ 13, Quận 10, TP.HCM', 'Ghi chú đơn hàng 13', '2026-07-08 06:02:58', '2026-07-08 06:02:58'),
(15, 'P22072600001', NULL, NULL, 0, 1, 'Lê Văn C', '0934567890', NULL, NULL, NULL, 'preorder', 'Công ty TNHH Tech', '0978901234', 0, 2970000, 0, NULL, 2970000, 0, 0, 'pending', 1, 'Địa chỉ 80, Quận 11, TP.HCM', 'Ghi chú đơn hàng 14', '2026-07-22 04:54:18', '2026-07-22 04:54:18'),
(16, 'L21072600001', NULL, NULL, 0, 1, 'Lê Văn C', '0934567890', NULL, NULL, NULL, 'retail', 'Công ty TNHH Tech', '0978901234', 44764, 13150000, 0, NULL, 13194764, 0, 0, 'pending', 0, 'Địa chỉ 77, Quận 10, TP.HCM', 'Ghi chú đơn hàng 15', '2026-07-21 03:32:30', '2026-07-21 03:32:30'),
(17, 'L30062600001', NULL, NULL, 0, 1, 'Doanh nghiệp XYZ', '0956789012', NULL, NULL, NULL, 'retail', 'Doanh nghiệp XYZ', '0956789012', 33102, 6250000, 0, NULL, 6283102, 0, 0, 'pending', 2, 'Địa chỉ 42, Quận 4, TP.HCM', NULL, '2026-06-30 13:23:01', '2026-06-30 13:23:01'),
(18, 'L02072600002', NULL, NULL, 0, 1, 'Trần Thị B', '0912345678', NULL, NULL, NULL, 'retail', 'Trần Thị B', '0912345678', 47836, 8700000, 0, NULL, 8747836, 0, 0, 'pending', 1, 'Địa chỉ 90, Quận 3, TP.HCM', 'Ghi chú đơn hàng 17', '2026-07-02 09:51:45', '2026-07-02 09:51:45'),
(19, 'S06072600001', NULL, NULL, 0, 1, 'Công ty TNHH ABC', '0987654321', NULL, NULL, NULL, 'wholesale', 'Lê Văn C', '0934567890', 0, 7750000, 0, NULL, 7750000, 0, 0, 'pending', 0, 'Địa chỉ 95, Quận 4, TP.HCM', 'Ghi chú đơn hàng 18', '2026-07-06 04:12:11', '2026-07-06 04:12:11'),
(20, 'S25072600001', NULL, NULL, 0, 1, 'Trần Thị B', '0912345678', NULL, NULL, NULL, 'wholesale', 'Trần Thị B', '0912345678', 0, 9770000, 0, NULL, 9770000, 0, 0, 'pending', 1, 'Địa chỉ 63, Quận 2, TP.HCM', NULL, '2026-07-25 02:40:36', '2026-07-25 02:40:36'),
(21, 'L21072600002', NULL, NULL, 0, 1, 'Công ty TNHH ABC', '0987654321', NULL, NULL, NULL, 'retail', 'Ngô Văn E', '0967890123', 48847, 7320000, 0, NULL, 7368847, 0, 0, 'pending', 2, 'Địa chỉ 81, Quận 4, TP.HCM', NULL, '2026-07-21 05:43:22', '2026-07-21 05:43:22'),
(22, 'L13072600001', NULL, NULL, 0, 1, 'Lê Văn C', '0934567890', NULL, NULL, NULL, 'retail', 'Ngô Văn E', '0967890123', 37223, 350000, 0, NULL, 387223, 0, 0, 'pending', 0, 'Địa chỉ 6, Quận 3, TP.HCM', NULL, '2026-07-13 05:48:20', '2026-07-13 05:48:20'),
(23, 'S02072600001', NULL, NULL, 0, 1, 'Ngô Văn E', '0967890123', NULL, NULL, NULL, 'wholesale', 'Nguyễn Văn A', '0901234567', 0, 3250000, 0, NULL, 3250000, 0, 0, 'pending', 4, 'Địa chỉ 63, Quận 9, TP.HCM', NULL, '2026-07-02 14:55:15', '2026-07-02 14:55:15'),
(24, 'L10072600001', NULL, NULL, 0, 1, 'Công ty TNHH ABC', '0987654321', NULL, NULL, NULL, 'retail', 'Ngô Văn E', '0967890123', 25782, 13640000, 0, NULL, 13665782, 0, 0, 'pending', 4, 'Địa chỉ 100, Quận 3, TP.HCM', 'Ghi chú đơn hàng 23', '2026-07-10 14:18:31', '2026-07-10 14:18:31'),
(25, 'L20072600001', NULL, NULL, 0, 1, 'Doanh nghiệp XYZ', '0956789012', NULL, NULL, NULL, 'retail', 'Nguyễn Văn A', '0901234567', 20027, 7550000, 0, NULL, 7570027, 0, 0, 'pending', 3, 'Địa chỉ 66, Quận 7, TP.HCM', 'Ghi chú đơn hàng 24', '2026-07-20 14:20:57', '2026-07-20 14:20:57'),
(26, 'L26062600001', NULL, NULL, 0, 1, 'Doanh nghiệp XYZ', '0956789012', NULL, NULL, NULL, 'retail', 'Ngô Văn E', '0967890123', 43936, 10150000, 0, NULL, 10193936, 0, 0, 'pending', 0, 'Địa chỉ 76, Quận 5, TP.HCM', NULL, '2026-06-26 01:52:08', '2026-06-26 01:52:08'),
(27, 'L21072600003', NULL, NULL, 0, 1, 'Lê Văn C', '0934567890', NULL, NULL, NULL, 'retail', 'Ngô Văn E', '0967890123', 31157, 2700000, 0, NULL, 2731157, 0, 0, 'pending', 2, 'Địa chỉ 68, Quận 2, TP.HCM', 'Ghi chú đơn hàng 26', '2026-07-21 14:32:45', '2026-07-21 14:32:45'),
(28, 'P26062600001', NULL, NULL, 0, 1, 'Công ty TNHH ABC', '0987654321', NULL, NULL, NULL, 'preorder', 'Nguyễn Văn A', '0901234567', 0, 4250000, 0, NULL, 4250000, 0, 0, 'pending', 1, 'Địa chỉ 43, Quận 8, TP.HCM', 'Ghi chú đơn hàng 27', '2026-06-26 12:05:11', '2026-06-26 12:05:11'),
(29, 'L19072600001', NULL, NULL, 0, 1, 'Phạm Thị D', '0945678901', NULL, NULL, NULL, 'retail', 'Doanh nghiệp XYZ', '0956789012', 30994, 4300000, 0, NULL, 4330994, 0, 0, 'pending', 1, 'Địa chỉ 80, Quận 5, TP.HCM', 'Ghi chú đơn hàng 28', '2026-07-19 02:34:21', '2026-07-19 02:34:21'),
(30, 'S15072600001', NULL, NULL, 0, 1, 'Nguyễn Văn A', '0901234567', NULL, NULL, NULL, 'wholesale', 'Công ty TNHH ABC', '0987654321', 0, 2600000, 0, NULL, 2600000, 0, 0, 'pending', 0, 'Địa chỉ 56, Quận 7, TP.HCM', NULL, '2026-07-15 07:03:56', '2026-07-15 07:03:56'),
(31, 'L05072600001', NULL, NULL, 0, 1, 'Phạm Thị D', '0945678901', NULL, NULL, NULL, 'retail', 'Phạm Thị D', '0945678901', 31026, 11340000, 0, NULL, 11371026, 0, 0, 'pending', 4, 'Địa chỉ 31, Quận 12, TP.HCM', 'Ghi chú đơn hàng 30', '2026-07-05 01:26:25', '2026-07-05 01:26:25'),
(32, 'P25072600001', NULL, NULL, 0, 1, 'Doanh nghiệp XYZ', '0956789012', NULL, NULL, NULL, 'preorder', 'Ngô Văn E', '0967890123', 0, 1300000, 0, NULL, 1300000, 0, 0, 'pending', 1, 'Địa chỉ 91, Quận 12, TP.HCM', NULL, '2026-07-25 06:43:52', '2026-07-25 06:43:52'),
(33, 'S26072600001', NULL, NULL, 0, 1, 'Phạm Thị D', '0945678901', NULL, NULL, NULL, 'wholesale', 'Lê Văn C', '0934567890', 0, 14270000, 0, NULL, 14270000, 0, 0, 'pending', 2, 'Địa chỉ 57, Quận 7, TP.HCM', 'Ghi chú đơn hàng 32', '2026-07-26 12:50:41', '2026-07-26 12:50:41'),
(34, 'L26062600002', NULL, NULL, 0, 1, 'Phạm Thị D', '0945678901', NULL, NULL, NULL, 'retail', 'Nguyễn Văn A', '0901234567', 44864, 12450000, 0, NULL, 12494864, 0, 0, 'pending', 1, 'Địa chỉ 60, Quận 9, TP.HCM', 'Ghi chú đơn hàng 33', '2026-06-26 01:40:11', '2026-06-26 01:40:11'),
(35, 'L08072600002', NULL, NULL, 0, 1, 'Nguyễn Văn A', '0901234567', NULL, NULL, NULL, 'retail', 'Trần Thị B', '0912345678', 28816, 4050000, 0, NULL, 4078816, 0, 0, 'pending', 0, 'Địa chỉ 83, Quận 1, TP.HCM', 'Ghi chú đơn hàng 34', '2026-07-08 13:49:46', '2026-07-08 13:49:46'),
(36, 'S15072600002', NULL, NULL, 0, 1, 'Công ty TNHH Tech', '0978901234', NULL, NULL, NULL, 'wholesale', 'Doanh nghiệp XYZ', '0956789012', 0, 7380000, 0, NULL, 7380000, 0, 0, 'pending', 4, 'Địa chỉ 62, Quận 10, TP.HCM', NULL, '2026-07-15 08:00:41', '2026-07-15 08:00:41'),
(37, 'P21072600002', NULL, NULL, 0, 1, 'Nguyễn Văn A', '0901234567', NULL, NULL, NULL, 'preorder', 'Nguyễn Văn A', '0901234567', 0, 5400000, 0, NULL, 5400000, 0, 0, 'pending', 3, 'Địa chỉ 39, Quận 11, TP.HCM', NULL, '2026-07-21 09:14:49', '2026-07-21 09:14:49'),
(38, 'S02072600002', NULL, NULL, 0, 1, 'Ngô Văn E', '0967890123', NULL, NULL, NULL, 'wholesale', 'Doanh nghiệp XYZ', '0956789012', 0, 7240000, 0, NULL, 7240000, 0, 0, 'pending', 4, 'Địa chỉ 9, Quận 4, TP.HCM', NULL, '2026-07-02 13:28:44', '2026-07-02 13:28:44'),
(39, 'P09072600001', NULL, NULL, 0, 1, 'Công ty TNHH Tech', '0978901234', NULL, NULL, NULL, 'preorder', 'Phạm Thị D', '0945678901', 0, 4200000, 0, NULL, 4200000, 0, 0, 'pending', 4, 'Địa chỉ 6, Quận 7, TP.HCM', 'Ghi chú đơn hàng 38', '2026-07-09 08:35:53', '2026-07-09 08:35:53'),
(40, 'S22072600003', NULL, NULL, 0, 1, 'Công ty TNHH ABC', '0987654321', NULL, NULL, NULL, 'wholesale', 'Nguyễn Văn A', '0901234567', 0, 2840000, 0, NULL, 2840000, 0, 0, 'pending', 0, 'Địa chỉ 8, Quận 11, TP.HCM', 'Ghi chú đơn hàng 39', '2026-07-22 03:02:23', '2026-07-22 03:02:23'),
(41, 'P12072600001', NULL, NULL, 0, 1, 'Trần Thị B', '0912345678', NULL, NULL, NULL, 'preorder', 'Ngô Văn E', '0967890123', 0, 3520000, 0, NULL, 3520000, 0, 0, 'pending', 3, 'Địa chỉ 53, Quận 10, TP.HCM', 'Ghi chú đơn hàng 40', '2026-07-12 12:19:32', '2026-07-12 12:19:32'),
(42, 'L02072600003', NULL, NULL, 0, 1, 'Trần Thị B', '0912345678', NULL, NULL, NULL, 'retail', 'Phạm Thị D', '0945678901', 23688, 3750000, 0, NULL, 3773688, 0, 0, 'pending', 2, 'Địa chỉ 24, Quận 4, TP.HCM', 'Ghi chú đơn hàng 41', '2026-07-02 06:51:36', '2026-07-02 06:51:36'),
(43, 'L05072600002', NULL, NULL, 0, 1, 'Phạm Thị D', '0945678901', NULL, NULL, NULL, 'retail', 'Công ty TNHH ABC', '0987654321', 44513, 5800000, 0, NULL, 5844513, 0, 0, 'pending', 1, 'Địa chỉ 72, Quận 7, TP.HCM', 'Ghi chú đơn hàng 42', '2026-07-05 09:39:23', '2026-07-05 09:39:23'),
(44, 'S29062600002', NULL, NULL, 0, 1, 'Lê Văn C', '0934567890', NULL, NULL, NULL, 'wholesale', 'Ngô Văn E', '0967890123', 0, 8400000, 0, NULL, 8400000, 0, 0, 'pending', 4, 'Địa chỉ 21, Quận 6, TP.HCM', NULL, '2026-06-29 12:24:58', '2026-06-29 12:24:58'),
(45, 'L29062600001', NULL, NULL, 0, 1, 'Công ty TNHH ABC', '0987654321', NULL, NULL, NULL, 'retail', 'Trần Thị B', '0912345678', 36703, 7050000, 0, NULL, 7086703, 0, 0, 'pending', 0, 'Địa chỉ 68, Quận 8, TP.HCM', NULL, '2026-06-29 01:09:20', '2026-06-29 01:09:20'),
(46, 'L11072600001', NULL, NULL, 0, 1, 'Công ty TNHH ABC', '0987654321', NULL, NULL, NULL, 'retail', 'Công ty TNHH Tech', '0978901234', 26258, 9700000, 0, NULL, 9726258, 0, 0, 'pending', 0, 'Địa chỉ 68, Quận 10, TP.HCM', NULL, '2026-07-11 01:59:52', '2026-07-11 01:59:52'),
(47, 'L08072600003', NULL, NULL, 0, 1, 'Ngô Văn E', '0967890123', NULL, NULL, NULL, 'retail', 'Trần Thị B', '0912345678', 42481, 20700000, 0, NULL, 20742481, 0, 0, 'pending', 2, 'Địa chỉ 13, Quận 5, TP.HCM', NULL, '2026-07-08 14:55:05', '2026-07-08 14:55:05'),
(48, 'L30062600002', NULL, NULL, 0, 1, 'Lê Văn C', '0934567890', NULL, NULL, NULL, 'retail', 'Ngô Văn E', '0967890123', 32750, 5100000, 0, NULL, 5132750, 0, 0, 'pending', 0, 'Địa chỉ 83, Quận 3, TP.HCM', 'Ghi chú đơn hàng 47', '2026-06-30 02:30:09', '2026-06-30 02:30:09'),
(49, 'L24072600001', NULL, NULL, 0, 1, 'Lê Văn C', '0934567890', NULL, NULL, NULL, 'retail', 'Nguyễn Văn A', '0901234567', 46690, 4800000, 0, NULL, 4846690, 0, 0, 'pending', 1, 'Địa chỉ 17, Quận 5, TP.HCM', NULL, '2026-07-24 03:24:51', '2026-07-24 03:24:51'),
(50, 'L14072600001', NULL, NULL, 0, 1, 'Trần Thị B', '0912345678', NULL, NULL, NULL, 'retail', 'Ngô Văn E', '0967890123', 26101, 2850000, 0, NULL, 2876101, 0, 0, 'pending', 4, 'Địa chỉ 43, Quận 2, TP.HCM', 'Ghi chú đơn hàng 49', '2026-07-14 02:30:33', '2026-07-14 02:30:33'),
(51, 'L2607202600051', NULL, NULL, 0, 5, 'Thanh Phuong Khuu', '0123456789', NULL, NULL, NULL, 'retail', 'Thanh Phuong Khuu', '0123456789', 0, 1190000, 0, NULL, 1190000, 0, 0, 'pending', 0, '99/178/2a Định Bộ Lĩnh, Phường 6, Quận 1, TP. Hồ Chí Minh', NULL, '2026-07-26 01:39:16', '2026-07-26 01:39:16'),
(52, 'L2607202600052', NULL, NULL, 0, 5, 'Thanh Phuong Khuu', '0123456789', NULL, NULL, NULL, 'retail', 'Thanh Phuong Khuu', '0123456789', 0, 1190000, 0, NULL, 1190000, 0, 0, 'pending', 0, '1, 1, 1, TP. Hồ Chí Minh', NULL, '2026-07-26 01:39:53', '2026-07-26 01:39:53'),
(53, 'L2607202600053', NULL, NULL, 0, 5, 'Thanh Phuong Khuu', '0123456789', NULL, NULL, NULL, 'retail', 'Thanh Phuong Khuu', '0123456789', 0, 1190000, 0, NULL, 1190000, 0, 0, 'pending', 0, '1, 1, 1, TP. Hồ Chí Minh', NULL, '2026-07-26 01:44:33', '2026-07-26 01:44:33'),
(54, 'L2607202600054', NULL, NULL, 0, 5, 'Thanh Phuong Khuu', '0123456789', NULL, NULL, NULL, 'retail', 'Thanh Phuong Khuu', '0123456789', 0, 1190000, 0, NULL, 1190000, 0, 0, 'pending', 0, '1, 1, 1, TP. Hồ Chí Minh', NULL, '2026-07-26 01:44:49', '2026-07-26 01:44:49'),
(55, 'L2607202600055', NULL, NULL, 0, 5, 'Thanh Phuong Khuu', '0123456789', NULL, NULL, NULL, 'retail', 'Thanh Phuong Khuu', '0123456789', 0, 1190000, 0, NULL, 1190000, 0, 0, 'pending', 0, '1, 1, 1, Hà Nội', NULL, '2026-07-26 01:45:00', '2026-07-26 01:45:00'),
(56, 'L2607202600056', NULL, NULL, 0, 5, 'Thanh Phuong Khuu', '0123456789', NULL, NULL, NULL, 'retail', 'Thanh Phuong Khuu', '0123456789', 0, 1190000, 0, NULL, 1190000, 0, 0, 'pending', 0, '1, 1, 1, TP. Hồ Chí Minh', NULL, '2026-07-26 02:36:10', '2026-07-26 02:36:10'),
(57, 'L2607202600057', NULL, NULL, 0, 5, 'Thanh Phuong Khuu', '0123456789', NULL, NULL, NULL, 'retail', 'Thanh Phuong Khuu', '0123456789', 0, 1190000, 0, NULL, 1190000, 0, 0, 'pending', 0, '1, 1, 1, TP. Hồ Chí Minh', NULL, '2026-07-26 03:12:17', '2026-07-26 03:12:17'),
(58, 'L2707202600058', NULL, NULL, 0, 5, 'Thanh Phuong Khuu', '0123456789', NULL, NULL, NULL, 'retail', 'Thanh Phuong Khuu', '0123456789', 0, 1450000, 0, NULL, 1450000, 0, 0, 'pending', 0, '1, 1, 1, TP. Hồ Chí Minh', NULL, '2026-07-26 20:40:10', '2026-07-26 20:40:10'),
(59, 'S0408202600059', NULL, NULL, 0, 5, 'abc', '1234567890', NULL, NULL, NULL, 'wholesale', 'abc', '1234567890', 0, 72500000, 0, NULL, 72500000, 0, 0, 'pending', 0, '124', NULL, '2026-08-04 09:42:18', '2026-08-04 09:42:18'),
(60, 'S0408202600060', NULL, NULL, 0, 5, 'abc', '123', NULL, NULL, NULL, 'wholesale', 'abc', '123', 0, 72500000, 0, NULL, 72500000, 0, 0, 'pending', 0, '123', '--- THÔNG TIN BỔ SUNG ---\nNgày cần nhận: 2026-08-12\nĐịa chỉ giao hàng: 123, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh\n-------------------------', '2026-08-04 12:19:31', '2026-08-04 12:19:31'),
(61, 'S0408202600061', NULL, NULL, 0, 5, 'abc', '123', NULL, NULL, NULL, 'wholesale', 'abc', '123', 0, 72500000, 0, NULL, 72500000, 0, 0, 'pending', 0, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: abc@gmail\nNgày cần nhận: 2026-08-12\nĐịa chỉ giao hàng: 123, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh\n-------------------------', '2026-08-04 12:46:52', '2026-08-04 12:46:52'),
(62, 'S0408202600062', NULL, NULL, 0, 5, 'a', '44444', NULL, NULL, NULL, 'wholesale', 'a', '44444', 0, 72500000, 0, NULL, 72500000, 0, 0, 'pending', 0, 'aaaa', '--- THÔNG TIN BỔ SUNG ---\nEmail: a@gmail.com\nMã số thuế: 111111\nNgày cần nhận: 2026-08-12\nĐịa chỉ giao hàng: aaaa, Phường Bến Nghé, Phú Nhuận, Hà Nội\n-------------------------', '2026-08-04 14:39:33', '2026-08-04 14:39:33'),
(63, 'S0408202600063', NULL, NULL, 0, 5, 'aaaaa', '111111111', NULL, NULL, NULL, 'wholesale', 'aaaaa', '111111111', 0, 72500000, 0, NULL, 72500000, 0, 0, 'pending', 0, 'aaaaa', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nNgày cần nhận: 2026-09-03\nĐịa chỉ giao hàng: aaaaa, Phường Bến Nghé, Tân Bình, Đồng Nai\n-------------------------', '2026-08-04 14:44:13', '2026-08-04 14:44:13'),
(64, 'S0508202600064', NULL, NULL, 0, 5, 'abcd', '1234567890', NULL, NULL, NULL, 'wholesale', 'abcd', '1234567890', 0, 72500000, 0, NULL, 72500000, 0, 0, 'pending', 4, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: abcd@gmail.com\nNgày cần nhận: 2026-08-28\nĐịa chỉ giao hàng: 123, Phường Cầu Kho, Quận 10, TP. Hồ Chí Minh\n-------------------------', '2026-08-05 07:45:14', '2026-08-07 04:33:21'),
(65, 'L0608202600065', NULL, NULL, 0, 5, 'Thanh Phuong Khuu', '0123456789', NULL, NULL, NULL, 'retail', 'Thanh Phuong Khuu', '0123456789', 0, 2867000, 0, NULL, 2867000, 0, 0, 'pending', 0, '123, 13, 123, TP. Hồ Chí Minh', NULL, '2026-08-05 18:10:11', '2026-08-05 18:10:11'),
(66, 'L0708202600066', NULL, NULL, 0, 5, 'Thanh Phuong Khuu', '0123456789', NULL, NULL, NULL, 'retail', 'Thanh Phuong Khuu', '0123456789', 0, 6050000, 0, NULL, 6050000, 0, 0, 'pending', 3, '123, 123, 123, Đà Nẵng', NULL, '2026-08-07 04:42:10', '2026-08-07 04:42:27'),
(67, 'L0708202600067', NULL, NULL, 0, 5, 'Thanh Phuong Khuu', '0123456789', NULL, NULL, NULL, 'retail', 'Thanh Phuong Khuu', '0123456789', 0, 2750000, 0, NULL, 2750000, 0, 0, 'pending', 3, '132, 123, 123, TP. Hồ Chí Minh', NULL, '2026-08-07 04:50:56', '2026-08-07 04:51:08'),
(68, 'S1008202600068', NULL, NULL, 0, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', NULL, NULL, NULL, 'wholesale', 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', 0, 36250000, 0, NULL, 36250000, 0, 0, 'pending', 0, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nMã số thuế: 0319669204\nNgày cần nhận: 2026-08-26\nĐịa chỉ giao hàng: 123, Phường Bến Nghé, Quận 1, Bình Dương\n-------------------------', '2026-08-10 06:25:50', '2026-08-10 06:25:50'),
(69, 'S1008202600069', NULL, NULL, 0, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', NULL, NULL, NULL, 'wholesale', 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', 0, 36250000, 0, NULL, 36250000, 0, 0, 'pending', 3, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: ryukianami@gmail.com\nMã số thuế: 0319669204\nNgày cần nhận: 2026-09-02\nĐịa chỉ giao hàng: 123, Phường Bến Nghé, Quận 3, TP. Hồ Chí Minh\n-------------------------', '2026-08-10 06:30:09', '2026-08-10 07:11:36'),
(70, 'S1008202600070', NULL, NULL, 0, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', NULL, NULL, NULL, 'wholesale', 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', 0, 36250000, 0, NULL, 36250000, 0, 0, 'pending', 3, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: ryukianami@gmail.com\nMã số thuế: 0319669204\nNgày cần nhận: 2026-08-13\nĐịa chỉ giao hàng: 123, Phường Bến Thành, Quận 3, Đà Nẵng\n-------------------------', '2026-08-10 06:54:34', '2026-08-10 07:11:33'),
(71, 'S1008202600071', NULL, NULL, 0, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', NULL, NULL, NULL, 'wholesale', 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', 0, 36250000, 0, NULL, 36250000, 0, 0, 'pending', 0, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: ryukianami@gmail.com\nMã số thuế: 0319669204\nNgày cần nhận: 2026-08-25\nĐịa chỉ giao hàng: 123, Phường Bến Nghé, Quận 2, Bình Dương\n-------------------------', '2026-08-10 07:53:10', '2026-08-10 07:53:11'),
(72, 'S1008202600072', NULL, NULL, 0, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', NULL, NULL, NULL, 'wholesale', 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', 0, 36250000, 0, NULL, 36250000, 0, 0, 'pending', 0, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nMã số thuế: 0319669204\nNgày cần nhận: 2026-08-21\nĐịa chỉ giao hàng: 123, Phường Bến Thành, Quận 2, Hà Nội\n-------------------------', '2026-08-10 07:57:16', '2026-08-10 07:57:16'),
(73, 'S1008202600073', NULL, NULL, 0, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', NULL, NULL, NULL, 'wholesale', 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', 0, 36250000, 0, NULL, 36250000, 0, 0, 'pending', 0, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: ryukianami@gmail.com\nMã số thuế: 0319669204\nNgày cần nhận: 2026-08-15\nĐịa chỉ giao hàng: 123, Phường Bến Nghé, Quận 3, TP. Hồ Chí Minh\n-------------------------', '2026-08-10 07:58:32', '2026-08-10 07:58:32'),
(74, 'S1008202600074', NULL, NULL, 0, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', NULL, NULL, NULL, 'wholesale', 'Văn phòng luật sư Ngọc Tuấn Legal', '0123456789', 0, 52500000, 0, NULL, 52500000, 0, 0, 'pending', 0, '1234', '--- THÔNG TIN BỔ SUNG ---\nEmail: ryukianami@gmail.com\nMã số thuế: 0319669204\nNgày cần nhận: 2026-08-25\nĐịa chỉ giao hàng: 1234, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh\n-------------------------', '2026-08-10 08:03:28', '2026-08-10 08:03:28'),
(75, 'L1508202600075', NULL, NULL, 0, 5, 'Thanh Phuong Khuu', '0123456789', NULL, NULL, NULL, 'retail', 'Thanh Phuong Khuu', '0123456789', 0, 1410000, 0, NULL, 1410000, 0, 0, 'pending', 0, '1, 1, 1, Đà Nẵng', NULL, '2026-08-15 09:29:18', '2026-08-15 09:29:18'),
(76, 'L1508202600076', NULL, NULL, 0, 5, 'P', '0123456789', NULL, NULL, NULL, 'retail', 'P', '0123456789', 0, 1344000, 0, NULL, 1344000, 0, 0, 'pending', 0, '1, 1, 1, TP. Hồ Chí Minh', NULL, '2026-08-15 10:20:48', '2026-08-15 10:20:48'),
(77, 'L1508202600077', NULL, NULL, 0, 5, 'P', '0123456789', NULL, NULL, NULL, 'retail', 'P', '0123456789', 0, 1344000, 0, NULL, 1344000, 0, 0, 'pending', 0, '2, 2, 2, TP. Hồ Chí Minh', NULL, '2026-08-15 10:31:13', '2026-08-15 10:31:13'),
(78, 'S1608202600078', NULL, NULL, 0, 5, 'DOANH NGHIỆP TƯ NHÂN LOAN PHÁT', '0123456789', NULL, NULL, NULL, 'wholesale', 'DOANH NGHIỆP TƯ NHÂN LOAN PHÁT', '0123456789', 0, 36250000, 0, NULL, 36250000, 0, 0, 'pending', 0, '13', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nMã số thuế: 1700494236\nNgày cần nhận: 2026-08-26\nĐịa chỉ giao hàng: 13, Phường Bến Nghé, Quận 1, Hà Nội\n-------------------------', '2026-08-16 09:43:32', '2026-08-16 09:43:33'),
(79, 'S1608202600079', 'P7Tqjj964tJkrX4gwtXD6Wixt6xaoh8N4FYmJlfVcPI6fXMgXMvOuBVZU57GWvmX', '2026-08-23 10:05:50', 1, 5, 'DOANH NGHIỆP TƯ NHÂN LOAN PHÁT', '0123456789', NULL, NULL, NULL, 'wholesale', 'DOANH NGHIỆP TƯ NHÂN LOAN PHÁT', '0123456789', 0, 36250000, 0, NULL, 36250000, 0, 0, 'pending', 1, '12', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nMã số thuế: 1700494236\nNgày cần nhận: 2026-08-24\nĐịa chỉ giao hàng: 12, Phường Bến Nghé, Quận 3, TP. Hồ Chí Minh\n-------------------------', '2026-08-16 10:05:50', '2026-08-16 10:06:26'),
(80, 'L1608202600080', NULL, NULL, 0, 5, 'P', '0123456789', NULL, NULL, NULL, 'retail', 'P', '0123456789', 0, 4350000, 0, NULL, 4350000, 0, 0, 'pending', 1, '1, 1, 1, Hà Nội', NULL, '2026-08-16 10:09:47', '2026-08-17 08:21:37'),
(81, 'S1708202600081', NULL, '2026-08-24 03:07:13', 0, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456789', NULL, NULL, NULL, 'wholesale', 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456789', 0, 52500000, 0, NULL, 52500000, 0, 0, 'pending', 5, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nMã số thuế: 0111606029\nNgày cần nhận: 2026-08-20\nĐịa chỉ giao hàng: 123, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh\n-------------------------\n[Hệ thống] Yêu cầu hoàn tiền do hủy đơn từ trạng thái hoàn thành.', '2026-08-17 03:07:12', '2026-08-17 03:07:58'),
(82, 'S1708202600082', NULL, '2026-08-24 07:17:13', 0, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456789', NULL, NULL, NULL, 'wholesale', 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456789', 0, 58750000, 0, NULL, 58750000, 0, 0, 'pending', 5, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nMã số thuế: 0111606029\nNgày cần nhận: 2026-08-26\nĐịa chỉ giao hàng: 123, Khác, Quận 7, TP. Hồ Chí Minh\n-------------------------', '2026-08-17 07:17:13', '2026-08-17 07:17:52'),
(83, 'S1708202600083', NULL, '2026-08-24 07:18:40', 1, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456789', NULL, NULL, NULL, 'wholesale', 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456789', 0, 52500000, 0, NULL, 52500000, 0, 0, 'pending', 1, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nMã số thuế: 0111606029\nNgày cần nhận: 2026-08-23\nĐịa chỉ giao hàng: 123\n-------------------------', '2026-08-17 07:18:40', '2026-08-17 07:19:12'),
(84, 'S1708202600084', 'qJJo8UzxoU25cryIMpUkEB9FIulEkHZKunhfE9S2BaSkKEapt2EEVAOEyRNsEAUP', '2026-08-24 07:23:43', 0, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456780', NULL, NULL, NULL, 'wholesale', 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456780', 0, 52500000, 0, NULL, 52500000, 26250000, 26250000, 'pending', 0, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nMã số thuế: 0111606029\nNgày cần nhận: 2026-08-26\nĐịa chỉ giao hàng: 123, Phường Bến Nghé, Quận 1, Đà Nẵng\n-------------------------', '2026-08-17 07:23:43', '2026-08-17 07:23:43'),
(85, 'S1708202600085', NULL, '2026-08-24 07:25:34', 1, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456789', NULL, NULL, NULL, 'wholesale', 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456789', 0, 52500000, 0, NULL, 52500000, 26250000, 26250000, 'pending', 1, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nMã số thuế: 0111606029\nNgày cần nhận: 2026-08-26\nĐịa chỉ giao hàng: 123, Phường Bến Nghé, Quận 2, Hà Nội\n-------------------------', '2026-08-17 07:25:34', '2026-08-17 07:26:19'),
(86, 'S1708202600086', NULL, '2026-08-24 07:28:46', 1, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456789', NULL, NULL, NULL, 'wholesale', 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456789', 0, 36250000, 0, NULL, 36250000, 18125000, 18125000, 'pending', 1, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nMã số thuế: 0111606029\nNgày cần nhận: 2026-08-20\nĐịa chỉ giao hàng: 123, Phường Bến Nghé, Quận 2, Hà Nội\n-------------------------', '2026-08-17 07:28:46', '2026-08-17 07:29:13'),
(87, 'S1708202600087', NULL, '2026-08-24 07:39:12', 1, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456789', NULL, NULL, NULL, 'wholesale', 'Trường Tiểu học và Trung học cơ sở Vân Hà', '0123456789', 0, 36250000, 0, NULL, 36250000, 18125000, 18125000, 'pending', 1, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nMã số thuế: 0111606029\nNgày cần nhận: 2026-08-24\nĐịa chỉ giao hàng: 123, Phường Bến Thành, Quận 2, TP. Hồ Chí Minh\n-------------------------', '2026-08-17 07:39:12', '2026-08-17 07:39:29'),
(88, 'C1708202600088', NULL, NULL, 0, 5, '123', '123', NULL, NULL, NULL, 'customize', '123', '123', 0, 855500, 0, NULL, 855500, 855500, 0, 'pending', 0, '1, 1, 1, Hà Nội', NULL, '2026-08-17 07:59:09', '2026-08-17 07:59:09'),
(89, 'C1708202600089', 'uJZKywKpQR1CUCqaSe77xlRJUl99HIw7GnwoUiShd1cDsulgvw8FccHLDlSE9lPx', '2026-08-24 08:29:51', 0, 5, '1', '0123456789', 'thanhphuongkhuu@gmail.com', NULL, NULL, 'customize', '1', '0123456789', 0, 870000, 0, NULL, 870000, 870000, 0, 'pending', 0, '1, 1, 1, TP. Hồ Chí Minh', NULL, '2026-08-17 08:29:51', '2026-08-17 08:29:51'),
(90, 'C1708202600090', 'BmYOreFtEDW9U2h0FKbgYxUv9YcizbvN7h9Y99Wwf97SkqxNqDAygw8s4DodGczO', '2026-08-24 08:31:42', 0, 5, '1', '0123456789', 'thanhphuongkhuu@gmail.com', NULL, NULL, 'customize', '1', '0123456789', 0, 870000, 0, NULL, 870000, 870000, 0, 'pending', 1, '1, 1, 1, TP. Hồ Chí Minh', NULL, '2026-08-17 08:31:42', '2026-08-17 08:32:20'),
(91, 'S1708202600091', NULL, '2026-08-24 08:44:52', 1, 5, 'CÔNG TY TNHH BLOSSOM CREATIVE SAIGON', '0123456789', 'thanhphuongkhuu@gmail.com', NULL, NULL, 'wholesale', 'CÔNG TY TNHH BLOSSOM CREATIVE SAIGON', '0123456789', 0, 36250000, 0, NULL, 36250000, 18125000, 18125000, 'pending', 1, '123', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nMã số thuế: 0319677692\nNgày cần nhận: 2026-08-29\nĐịa chỉ giao hàng: 123, Phường Bến Thành, Quận 2, TP. Hồ Chí Minh\n-------------------------', '2026-08-17 08:44:52', '2026-08-17 08:45:23'),
(92, 'C1708202600092', 'cFjvgsFOiLjrcpJnhHcKuY13rGtXiDyeP5M2s1Gu9DNu0128PCekQxzDBmmry4TH', '2026-08-24 08:48:16', 0, 5, 'Thanh Phuong Khuu', '0123456789', 'thanhphuongkhuu@gmail.com', NULL, NULL, 'customize', 'Thanh Phuong Khuu', '0123456789', 0, 899000, 0, NULL, 899000, 899000, 0, 'pending', 0, '1, 1, 1, Hà Nội', NULL, '2026-08-17 08:48:16', '2026-08-17 08:48:16'),
(93, 'C1708202600093', 'gVWny5mkOljH5s8gRWUpPtope6aF4Qg1naPrrqMaTeQtoQMmwI6kYpSmtCFpYY3Z', '2026-08-24 08:48:43', 0, 5, 'Thanh Phuong Khuu', '0123456789', 'thanhphuongkhuu@gmail.com', NULL, NULL, 'customize', 'Thanh Phuong Khuu', '0123456789', 0, 899000, 0, NULL, 899000, 899000, 0, 'pending', 1, '1, 1, 1, Hà Nội', NULL, '2026-08-17 08:48:43', '2026-08-17 10:24:40'),
(94, 'C1708202600094', 'jhRwau3qqosKBqjvBTe0KzCc7L50lj4yyuVQTEILgyW3KjD8jrrJexehBFxgrIgL', '2026-08-24 08:51:37', 0, 5, 'Thanh Phuong Khuu', '0123456789', 'thanhphuongkhuu@gmail.com', NULL, NULL, 'customize', 'Thanh Phuong Khuu', '0123456789', 0, 899000, 0, NULL, 899000, 899000, 0, 'pending', 0, '1, 1, 1, Hải Phòng', NULL, '2026-08-17 08:51:37', '2026-08-17 08:51:37'),
(95, 'C1708202600095', '3arxzxnpAkDZwBPROwoj9rKtRE01w1hKCthWcnAuZzZ1WFN4kQdTenZD3WC8yhwk', '2026-08-24 08:52:50', 0, 5, 'Thanh Phuong Khuu', '0123456789', 'thanhphuongkhuu@gmail.com', NULL, NULL, 'customize', 'Thanh Phuong Khuu', '0123456789', 0, 928000, 0, NULL, 928000, 928000, 0, 'pending', 1, '1, 1, 1, Đà Nẵng', NULL, '2026-08-17 08:52:50', '2026-08-17 08:53:03'),
(96, 'L1708202600096', 'XM9KGCIlRUkr2eTAmWAa3orw8Ih2Gm7C5ro0P2yjSEq1ZUT1UpjkGPyCXnGCjXmk', '2026-08-24 09:11:00', 0, 5, 'Thanh Phuong Khuu', '0123456789', 'thanhphuongkhuu@gmail.com', NULL, NULL, 'retail', 'Thanh Phuong Khuu', '0123456789', 0, 725000, 0, NULL, 725000, 725000, 0, 'pending', 0, '1, 1, 11, Hải Phòng', NULL, '2026-08-17 09:11:00', '2026-08-17 09:11:00'),
(98, 'S1708202600098', 'o0N5E5nNiXs3UEORV3UwZwLDQYJxAlxeRe2gjrYDBldoVtCAYoo8GRachZ9vxWFp', '2026-08-24 11:43:42', 0, 5, 'Thanh Phuong Khuu', '0123456789', 'thanhphuongkhuu@gmail.com', NULL, NULL, 'wholesale', 'CÔNG TY TNHH AGRI 1986', '0123456789', 0, 52500000, 0, NULL, 52500000, 26250000, 26250000, 'pending', 0, '1', '--- THÔNG TIN BỔ SUNG ---\nEmail: thanhphuongkhuu@gmail.com\nMã số thuế: 3801358804\nNgày cần nhận: 2026-08-31\nĐịa chỉ giao hàng: 1, 1, 1, A\n-------------------------', '2026-08-17 11:43:42', '2026-08-17 11:43:42'),
(99, 'P1808202600099', 'fusbTDUBNSeN5gxvg0Xu5mNEEzmuTevbbIY0uNkLlPLJJaAywr4kccm4gvb41m5F', '2026-08-25 09:30:19', 0, 6, 'Py', '0907868956', 'trucmy10042004@gmail.com', NULL, 4, 'preorder', 'Py', '0907868956', 0, 360000, 0, NULL, 360000, 360000, 0, 'pending', 0, '89, TP. Hồ Chí Minh', NULL, '2026-08-18 09:30:19', '2026-08-18 09:30:19'),
(100, 'S1808202600100', 'YzxGMbXYzvJhk7f8bl8w7j8LoEQTmGjK96fZLGuQr8DgmlfAoFxDlZmpxyJLLHn3', '2026-08-25 09:36:34', 0, 6, 'Py', '0907868956', 'trucmy10042004@gmail.com', 1, NULL, 'wholesale', 'CÔNG TY CỔ PHẦN SỮA VIỆT NAM', '0906758927', 0, 32450000, 1622500, NULL, 30827500, 15413750, 15413750, 'pending', 0, '1', '--- THÔNG TIN BỔ SUNG ---\nEmail: a@gmail.com\nMã số thuế: 0300588569\nNgày cần nhận: 2026-09-03\nĐịa chỉ giao hàng: 1, 1, 1, HCM\n-------------------------', '2026-08-18 09:36:34', '2026-08-18 09:36:34'),
(101, 'S1808202600101', NULL, '2026-08-25 09:47:03', 1, 6, 'Py', '0907868956', 'trucmy10042004@gmail.com', 1, NULL, 'wholesale', 'CÔNG TY CỔ PHẦN SỮA VIỆT NAM', '0907864587', 0, 130000000, 6500000, NULL, 123500000, 61750000, 61750000, 'pending', 1, '1', '--- THÔNG TIN BỔ SUNG ---\nEmail: trucmy10042004@gmail.com\nMã số thuế: 0300588569\nNgày cần nhận: 2026-09-10\nĐịa chỉ giao hàng: 1, 1, 1, 1\n-------------------------', '2026-08-18 09:47:03', '2026-08-18 09:47:49'),
(102, 'C1808202600102', 'APNRBEXLGdk15yEv99HEGgZYip3X4t7Sa4qhNHrSrBesX4LUIdWQpGwQkXIWjwsy', '2026-08-25 09:53:15', 0, 6, 'Py', '0907868956', 'trucmy10042004@gmail.com', NULL, NULL, 'customize', 'Py', '0907868956', 0, 3172000, 0, NULL, 3172000, 3172000, 0, 'pending', 1, '1, TP. Hồ Chí Minh', NULL, '2026-08-18 09:53:15', '2026-08-18 09:54:14');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_details`
--

DROP TABLE IF EXISTS `order_details`;
CREATE TABLE IF NOT EXISTS `order_details` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_variant_id` bigint UNSIGNED NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(12,0) NOT NULL,
  `subtotal` decimal(12,0) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_details_order_id_foreign` (`order_id`),
  KEY `order_details_product_variant_id_foreign` (`product_variant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=262 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_variant_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 20, 3, 650000, 1950000, '2026-07-21 04:12:48', '2026-07-21 04:12:48'),
(2, 1, 7, 3, 1250000, 3750000, '2026-07-21 04:12:48', '2026-07-21 04:12:48'),
(3, 2, 2, 4, 1450000, 5800000, '2026-07-22 02:10:03', '2026-07-22 02:10:03'),
(4, 2, 13, 5, 1850000, 9250000, '2026-07-22 02:10:03', '2026-07-22 02:10:03'),
(5, 3, 1, 2, 1450000, 2900000, '2026-07-11 06:22:16', '2026-07-11 06:22:16'),
(6, 3, 10, 5, 550000, 2750000, '2026-07-11 06:22:16', '2026-07-11 06:22:16'),
(7, 4, 15, 1, 350000, 350000, '2026-07-02 03:03:52', '2026-07-02 03:03:52'),
(8, 4, 5, 1, 2350000, 2350000, '2026-07-02 03:03:52', '2026-07-02 03:03:52'),
(9, 5, 12, 2, 990000, 1980000, '2026-07-26 06:31:47', '2026-07-26 06:31:47'),
(10, 6, 2, 5, 1450000, 7250000, '2026-07-16 06:39:46', '2026-07-16 06:39:46'),
(11, 6, 7, 5, 1250000, 6250000, '2026-07-16 06:39:46', '2026-07-16 06:39:46'),
(12, 7, 10, 4, 550000, 2200000, '2026-07-22 02:39:44', '2026-07-22 02:39:44'),
(13, 8, 7, 4, 1250000, 5000000, '2026-07-18 10:09:25', '2026-07-18 10:09:25'),
(14, 9, 14, 4, 350000, 1400000, '2026-06-29 09:17:54', '2026-06-29 09:17:54'),
(15, 10, 7, 1, 1250000, 1250000, '2026-07-03 15:42:42', '2026-07-03 15:42:42'),
(16, 10, 15, 5, 350000, 1750000, '2026-07-03 15:42:42', '2026-07-03 15:42:42'),
(17, 10, 20, 2, 650000, 1300000, '2026-07-03 15:42:42', '2026-07-03 15:42:42'),
(18, 11, 2, 3, 1450000, 4350000, '2026-07-21 04:37:14', '2026-07-21 04:37:14'),
(19, 11, 7, 4, 1250000, 5000000, '2026-07-21 04:37:14', '2026-07-21 04:37:14'),
(20, 12, 1, 1, 1450000, 1450000, '2026-06-30 02:28:01', '2026-06-30 02:28:01'),
(21, 12, 8, 1, 1890000, 1890000, '2026-06-30 02:28:01', '2026-06-30 02:28:01'),
(22, 12, 6, 3, 2350000, 7050000, '2026-06-30 02:28:01', '2026-06-30 02:28:01'),
(23, 13, 12, 4, 990000, 3960000, '2026-07-12 14:33:32', '2026-07-12 14:33:32'),
(24, 13, 16, 5, 2850000, 14250000, '2026-07-12 14:33:32', '2026-07-12 14:33:32'),
(25, 13, 14, 1, 350000, 350000, '2026-07-12 14:33:32', '2026-07-12 14:33:32'),
(26, 14, 13, 4, 1850000, 7400000, '2026-07-08 06:02:58', '2026-07-08 06:02:58'),
(27, 14, 19, 2, 650000, 1300000, '2026-07-08 06:02:58', '2026-07-08 06:02:58'),
(28, 15, 12, 3, 990000, 2970000, '2026-07-22 04:54:18', '2026-07-22 04:54:18'),
(29, 16, 13, 2, 1850000, 3700000, '2026-07-21 03:32:30', '2026-07-21 03:32:30'),
(30, 16, 9, 5, 1890000, 9450000, '2026-07-21 03:32:30', '2026-07-21 03:32:30'),
(31, 17, 17, 1, 2850000, 2850000, '2026-06-30 13:23:01', '2026-06-30 13:23:01'),
(32, 17, 3, 1, 2100000, 2100000, '2026-06-30 13:23:01', '2026-06-30 13:23:01'),
(33, 17, 20, 2, 650000, 1300000, '2026-06-30 13:23:01', '2026-06-30 13:23:01'),
(34, 18, 11, 3, 550000, 1650000, '2026-07-02 09:51:45', '2026-07-02 09:51:45'),
(35, 18, 5, 3, 2350000, 7050000, '2026-07-02 09:51:45', '2026-07-02 09:51:45'),
(36, 19, 2, 1, 1450000, 1450000, '2026-07-06 04:12:11', '2026-07-06 04:12:11'),
(37, 19, 3, 3, 2100000, 6300000, '2026-07-06 04:12:11', '2026-07-06 04:12:11'),
(38, 20, 12, 3, 990000, 2970000, '2026-07-25 02:40:36', '2026-07-25 02:40:36'),
(39, 20, 19, 4, 650000, 2600000, '2026-07-25 02:40:36', '2026-07-25 02:40:36'),
(40, 20, 3, 2, 2100000, 4200000, '2026-07-25 02:40:36', '2026-07-25 02:40:36'),
(41, 21, 2, 3, 1450000, 4350000, '2026-07-21 05:43:22', '2026-07-21 05:43:22'),
(42, 21, 12, 3, 990000, 2970000, '2026-07-21 05:43:22', '2026-07-21 05:43:22'),
(43, 22, 14, 1, 350000, 350000, '2026-07-13 05:48:20', '2026-07-13 05:48:20'),
(44, 23, 20, 5, 650000, 3250000, '2026-07-02 14:55:15', '2026-07-02 14:55:15'),
(45, 24, 9, 1, 1890000, 1890000, '2026-07-10 14:18:31', '2026-07-10 14:18:31'),
(46, 24, 13, 4, 1850000, 7400000, '2026-07-10 14:18:31', '2026-07-10 14:18:31'),
(47, 24, 2, 3, 1450000, 4350000, '2026-07-10 14:18:31', '2026-07-10 14:18:31'),
(48, 25, 15, 1, 350000, 350000, '2026-07-20 14:20:57', '2026-07-20 14:20:57'),
(49, 25, 7, 4, 1250000, 5000000, '2026-07-20 14:20:57', '2026-07-20 14:20:57'),
(50, 25, 11, 4, 550000, 2200000, '2026-07-20 14:20:57', '2026-07-20 14:20:57'),
(51, 26, 3, 1, 2100000, 2100000, '2026-06-26 01:52:08', '2026-06-26 01:52:08'),
(52, 26, 13, 3, 1850000, 5550000, '2026-06-26 01:52:08', '2026-06-26 01:52:08'),
(53, 26, 7, 2, 1250000, 2500000, '2026-06-26 01:52:08', '2026-06-26 01:52:08'),
(54, 27, 7, 1, 1250000, 1250000, '2026-07-21 14:32:45', '2026-07-21 14:32:45'),
(55, 27, 1, 1, 1450000, 1450000, '2026-07-21 14:32:45', '2026-07-21 14:32:45'),
(56, 28, 19, 4, 650000, 2600000, '2026-06-26 12:05:11', '2026-06-26 12:05:11'),
(57, 28, 10, 3, 550000, 1650000, '2026-06-26 12:05:11', '2026-06-26 12:05:11'),
(58, 29, 1, 2, 1450000, 2900000, '2026-07-19 02:34:21', '2026-07-19 02:34:21'),
(59, 29, 14, 4, 350000, 1400000, '2026-07-19 02:34:21', '2026-07-19 02:34:21'),
(60, 30, 20, 4, 650000, 2600000, '2026-07-15 07:03:56', '2026-07-15 07:03:56'),
(61, 31, 8, 5, 1890000, 9450000, '2026-07-05 01:26:25', '2026-07-05 01:26:25'),
(62, 31, 9, 1, 1890000, 1890000, '2026-07-05 01:26:25', '2026-07-05 01:26:25'),
(63, 32, 19, 2, 650000, 1300000, '2026-07-25 06:43:52', '2026-07-25 06:43:52'),
(64, 33, 18, 4, 1190000, 4760000, '2026-07-26 12:50:41', '2026-07-26 12:50:41'),
(65, 33, 9, 4, 1890000, 7560000, '2026-07-26 12:50:41', '2026-07-26 12:50:41'),
(66, 33, 20, 3, 650000, 1950000, '2026-07-26 12:50:41', '2026-07-26 12:50:41'),
(67, 34, 6, 4, 2350000, 9400000, '2026-06-26 01:40:11', '2026-06-26 01:40:11'),
(68, 34, 19, 2, 650000, 1300000, '2026-06-26 01:40:11', '2026-06-26 01:40:11'),
(69, 34, 15, 5, 350000, 1750000, '2026-06-26 01:40:11', '2026-06-26 01:40:11'),
(70, 35, 11, 4, 550000, 2200000, '2026-07-08 13:49:46', '2026-07-08 13:49:46'),
(71, 35, 13, 1, 1850000, 1850000, '2026-07-08 13:49:46', '2026-07-08 13:49:46'),
(72, 36, 20, 5, 650000, 3250000, '2026-07-15 08:00:41', '2026-07-15 08:00:41'),
(73, 36, 15, 1, 350000, 350000, '2026-07-15 08:00:41', '2026-07-15 08:00:41'),
(74, 36, 8, 2, 1890000, 3780000, '2026-07-15 08:00:41', '2026-07-15 08:00:41'),
(75, 37, 13, 2, 1850000, 3700000, '2026-07-21 09:14:49', '2026-07-21 09:14:49'),
(76, 37, 15, 3, 350000, 1050000, '2026-07-21 09:14:49', '2026-07-21 09:14:49'),
(77, 37, 20, 1, 650000, 650000, '2026-07-21 09:14:49', '2026-07-21 09:14:49'),
(78, 38, 20, 5, 650000, 3250000, '2026-07-02 13:28:44', '2026-07-02 13:28:44'),
(79, 38, 3, 1, 2100000, 2100000, '2026-07-02 13:28:44', '2026-07-02 13:28:44'),
(80, 38, 9, 1, 1890000, 1890000, '2026-07-02 13:28:44', '2026-07-02 13:28:44'),
(81, 39, 3, 2, 2100000, 4200000, '2026-07-09 08:35:53', '2026-07-09 08:35:53'),
(82, 40, 13, 1, 1850000, 1850000, '2026-07-22 03:02:23', '2026-07-22 03:02:23'),
(83, 40, 12, 1, 990000, 990000, '2026-07-22 03:02:23', '2026-07-22 03:02:23'),
(84, 41, 12, 3, 990000, 2970000, '2026-07-12 12:19:32', '2026-07-12 12:19:32'),
(85, 41, 11, 1, 550000, 550000, '2026-07-12 12:19:32', '2026-07-12 12:19:32'),
(86, 42, 7, 3, 1250000, 3750000, '2026-07-02 06:51:36', '2026-07-02 06:51:36'),
(87, 43, 2, 4, 1450000, 5800000, '2026-07-05 09:39:23', '2026-07-05 09:39:23'),
(88, 44, 3, 4, 2100000, 8400000, '2026-06-29 12:24:58', '2026-06-29 12:24:58'),
(89, 45, 18, 5, 1190000, 5950000, '2026-06-29 01:09:20', '2026-06-29 01:09:20'),
(90, 45, 10, 2, 550000, 1100000, '2026-06-29 01:09:20', '2026-06-29 01:09:20'),
(91, 46, 1, 3, 1450000, 4350000, '2026-07-11 01:59:52', '2026-07-11 01:59:52'),
(92, 46, 10, 5, 550000, 2750000, '2026-07-11 01:59:52', '2026-07-11 01:59:52'),
(93, 46, 19, 4, 650000, 2600000, '2026-07-11 01:59:52', '2026-07-11 01:59:52'),
(94, 47, 2, 1, 1450000, 1450000, '2026-07-08 14:55:05', '2026-07-08 14:55:05'),
(95, 47, 7, 4, 1250000, 5000000, '2026-07-08 14:55:05', '2026-07-08 14:55:05'),
(96, 47, 16, 5, 2850000, 14250000, '2026-07-08 14:55:05', '2026-07-08 14:55:05'),
(97, 48, 1, 2, 1450000, 2900000, '2026-06-30 02:30:09', '2026-06-30 02:30:09'),
(98, 48, 11, 4, 550000, 2200000, '2026-06-30 02:30:09', '2026-06-30 02:30:09'),
(99, 49, 20, 4, 650000, 2600000, '2026-07-24 03:24:51', '2026-07-24 03:24:51'),
(100, 49, 11, 4, 550000, 2200000, '2026-07-24 03:24:51', '2026-07-24 03:24:51'),
(101, 50, 14, 4, 350000, 1400000, '2026-07-14 02:30:33', '2026-07-14 02:30:33'),
(102, 50, 4, 2, 450000, 900000, '2026-07-14 02:30:33', '2026-07-14 02:30:33'),
(103, 50, 10, 1, 550000, 550000, '2026-07-14 02:30:33', '2026-07-14 02:30:33'),
(104, 1, 15, 1, 350000, 350000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(105, 1, 2, 3, 1450000, 4350000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(106, 2, 18, 1, 1190000, 1190000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(107, 2, 1, 1, 1450000, 1450000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(108, 2, 8, 3, 1890000, 5670000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(109, 3, 16, 2, 2850000, 5700000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(110, 3, 2, 2, 1450000, 2900000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(111, 3, 15, 1, 350000, 350000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(112, 4, 19, 3, 650000, 1950000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(113, 5, 8, 3, 1890000, 5670000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(114, 5, 4, 1, 450000, 450000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(115, 5, 18, 2, 1190000, 2380000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(116, 6, 16, 2, 2850000, 5700000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(117, 6, 2, 3, 1450000, 4350000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(118, 6, 14, 1, 350000, 350000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(119, 7, 2, 3, 1450000, 4350000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(120, 7, 17, 1, 2850000, 2850000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(121, 7, 20, 2, 650000, 1300000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(122, 8, 5, 1, 2350000, 2350000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(123, 8, 14, 1, 350000, 350000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(124, 8, 15, 3, 350000, 1050000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(125, 9, 10, 1, 550000, 550000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(126, 10, 12, 2, 990000, 1980000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(127, 10, 7, 1, 1250000, 1250000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(128, 10, 8, 1, 1890000, 1890000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(129, 11, 17, 3, 2850000, 8550000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(130, 11, 9, 1, 1890000, 1890000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(131, 12, 13, 1, 1850000, 1850000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(132, 13, 15, 2, 350000, 700000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(133, 13, 1, 3, 1450000, 4350000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(134, 13, 14, 2, 350000, 700000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(135, 14, 16, 3, 2850000, 8550000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(136, 15, 13, 3, 1850000, 5550000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(137, 15, 6, 1, 2350000, 2350000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(138, 16, 3, 2, 2100000, 4200000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(139, 16, 4, 3, 450000, 1350000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(140, 17, 20, 3, 650000, 1950000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(141, 17, 4, 1, 450000, 450000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(142, 18, 10, 3, 550000, 1650000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(143, 19, 12, 2, 990000, 1980000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(144, 19, 4, 1, 450000, 450000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(145, 20, 9, 3, 1890000, 5670000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(146, 20, 8, 3, 1890000, 5670000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(147, 21, 5, 2, 2350000, 4700000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(148, 21, 16, 3, 2850000, 8550000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(149, 21, 18, 1, 1190000, 1190000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(150, 22, 6, 3, 2350000, 7050000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(151, 22, 15, 2, 350000, 700000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(152, 22, 4, 3, 450000, 1350000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(153, 23, 10, 2, 550000, 1100000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(154, 23, 5, 3, 2350000, 7050000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(155, 24, 3, 1, 2100000, 2100000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(156, 25, 2, 2, 1450000, 2900000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(157, 25, 8, 1, 1890000, 1890000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(158, 25, 7, 1, 1250000, 1250000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(159, 26, 2, 1, 1450000, 1450000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(160, 26, 1, 3, 1450000, 4350000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(161, 26, 10, 3, 550000, 1650000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(162, 27, 16, 2, 2850000, 5700000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(163, 28, 3, 1, 2100000, 2100000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(164, 28, 12, 2, 990000, 1980000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(165, 29, 8, 3, 1890000, 5670000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(166, 29, 16, 2, 2850000, 5700000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(167, 30, 6, 3, 2350000, 7050000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(168, 31, 18, 3, 1190000, 3570000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(169, 31, 17, 2, 2850000, 5700000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(170, 32, 18, 3, 1190000, 3570000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(171, 32, 11, 2, 550000, 1100000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(172, 33, 4, 1, 450000, 450000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(173, 33, 18, 2, 1190000, 2380000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(174, 34, 12, 1, 990000, 990000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(175, 34, 12, 2, 990000, 1980000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(176, 35, 13, 1, 1850000, 1850000, '2026-07-26 01:32:40', '2026-07-26 01:32:40'),
(177, 35, 9, 1, 1890000, 1890000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(178, 36, 8, 1, 1890000, 1890000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(179, 36, 10, 3, 550000, 1650000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(180, 36, 6, 3, 2350000, 7050000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(181, 37, 11, 3, 550000, 1650000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(182, 38, 10, 3, 550000, 1650000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(183, 38, 2, 1, 1450000, 1450000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(184, 39, 6, 1, 2350000, 2350000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(185, 40, 15, 1, 350000, 350000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(186, 40, 6, 3, 2350000, 7050000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(187, 41, 9, 3, 1890000, 5670000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(188, 41, 13, 3, 1850000, 5550000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(189, 41, 18, 2, 1190000, 2380000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(190, 42, 6, 1, 2350000, 2350000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(191, 43, 10, 1, 550000, 550000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(192, 43, 9, 2, 1890000, 3780000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(193, 43, 9, 3, 1890000, 5670000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(194, 44, 6, 3, 2350000, 7050000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(195, 45, 8, 3, 1890000, 5670000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(196, 45, 6, 1, 2350000, 2350000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(197, 46, 15, 1, 350000, 350000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(198, 46, 13, 1, 1850000, 1850000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(199, 46, 19, 1, 650000, 650000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(200, 47, 10, 2, 550000, 1100000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(201, 47, 8, 1, 1890000, 1890000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(202, 48, 20, 3, 650000, 1950000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(203, 48, 3, 2, 2100000, 4200000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(204, 48, 10, 1, 550000, 550000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(205, 49, 9, 2, 1890000, 3780000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(206, 49, 3, 3, 2100000, 6300000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(207, 49, 19, 2, 650000, 1300000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(208, 50, 16, 2, 2850000, 5700000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(209, 50, 3, 3, 2100000, 6300000, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(210, 51, 18, 1, 1190000, 1190000, '2026-07-26 01:39:16', '2026-07-26 01:39:16'),
(211, 52, 18, 1, 1190000, 1190000, '2026-07-26 01:39:53', '2026-07-26 01:39:53'),
(212, 53, 18, 1, 1190000, 1190000, '2026-07-26 01:44:33', '2026-07-26 01:44:33'),
(213, 54, 18, 1, 1190000, 1190000, '2026-07-26 01:44:49', '2026-07-26 01:44:49'),
(214, 55, 18, 1, 1190000, 1190000, '2026-07-26 01:45:00', '2026-07-26 01:45:00'),
(215, 56, 18, 1, 1190000, 1190000, '2026-07-26 02:36:10', '2026-07-26 02:36:10'),
(216, 57, 18, 1, 1190000, 1190000, '2026-07-26 03:12:17', '2026-07-26 03:12:17'),
(217, 58, 1, 1, 1450000, 1450000, '2026-07-26 20:40:10', '2026-07-26 20:40:10'),
(218, 59, 1, 50, 1450000, 72500000, '2026-08-04 09:42:18', '2026-08-04 09:42:18'),
(219, 60, 1, 50, 1450000, 72500000, '2026-08-04 12:19:31', '2026-08-04 12:19:31'),
(220, 61, 1, 50, 1450000, 72500000, '2026-08-04 12:46:52', '2026-08-04 12:46:52'),
(221, 62, 1, 50, 1450000, 72500000, '2026-08-04 14:39:33', '2026-08-04 14:39:33'),
(222, 63, 1, 50, 1450000, 72500000, '2026-08-04 14:44:13', '2026-08-04 14:44:13'),
(223, 64, 1, 50, 1450000, 72500000, '2026-08-05 07:45:14', '2026-08-05 07:45:14'),
(224, 65, 5, 1, 2867000, 2867000, '2026-08-05 18:10:11', '2026-08-05 18:10:11'),
(225, 66, 10, 11, 550000, 6050000, '2026-08-07 04:42:10', '2026-08-07 04:42:10'),
(226, 67, 10, 5, 550000, 2750000, '2026-08-07 04:50:56', '2026-08-07 04:50:56'),
(227, 68, 1, 50, 725000, 36250000, '2026-08-10 06:25:50', '2026-08-10 06:25:50'),
(228, 69, 1, 50, 725000, 36250000, '2026-08-10 06:30:09', '2026-08-10 06:30:09'),
(229, 70, 1, 50, 725000, 36250000, '2026-08-10 06:54:34', '2026-08-10 06:54:34'),
(230, 71, 1, 50, 725000, 36250000, '2026-08-10 07:53:11', '2026-08-10 07:53:11'),
(231, 72, 1, 50, 725000, 36250000, '2026-08-10 07:57:16', '2026-08-10 07:57:16'),
(232, 73, 1, 50, 725000, 36250000, '2026-08-10 07:58:32', '2026-08-10 07:58:32'),
(233, 74, 3, 50, 1050000, 52500000, '2026-08-10 08:03:28', '2026-08-10 08:03:28'),
(234, 75, 5, 1, 1410000, 1410000, '2026-08-15 09:29:18', '2026-08-15 09:29:18'),
(235, 76, 3, 1, 1344000, 1344000, '2026-08-15 10:20:48', '2026-08-15 10:20:48'),
(236, 77, 3, 1, 1344000, 1344000, '2026-08-15 10:31:13', '2026-08-15 10:31:13'),
(237, 78, 1, 50, 725000, 36250000, '2026-08-16 09:43:33', '2026-08-16 09:43:33'),
(238, 79, 1, 50, 725000, 36250000, '2026-08-16 10:05:50', '2026-08-16 10:05:50'),
(239, 80, 1, 5, 870000, 4350000, '2026-08-16 10:09:47', '2026-08-16 10:09:47'),
(240, 81, 3, 50, 1050000, 52500000, '2026-08-17 03:07:13', '2026-08-17 03:07:13'),
(241, 82, 5, 50, 1175000, 58750000, '2026-08-17 07:17:13', '2026-08-17 07:17:13'),
(242, 83, 3, 50, 1050000, 52500000, '2026-08-17 07:18:40', '2026-08-17 07:18:40'),
(243, 84, 3, 50, 1050000, 52500000, '2026-08-17 07:23:43', '2026-08-17 07:23:43'),
(244, 85, 3, 50, 1050000, 52500000, '2026-08-17 07:25:34', '2026-08-17 07:25:34'),
(245, 86, 1, 50, 725000, 36250000, '2026-08-17 07:28:46', '2026-08-17 07:28:46'),
(246, 87, 1, 50, 725000, 36250000, '2026-08-17 07:39:12', '2026-08-17 07:39:12'),
(247, 88, 1, 1, 855500, 855500, '2026-08-17 07:59:09', '2026-08-17 07:59:09'),
(248, 89, 1, 1, 870000, 870000, '2026-08-17 08:29:51', '2026-08-17 08:29:51'),
(249, 90, 1, 1, 870000, 870000, '2026-08-17 08:31:42', '2026-08-17 08:31:42'),
(250, 91, 1, 50, 725000, 36250000, '2026-08-17 08:44:52', '2026-08-17 08:44:52'),
(251, 92, 1, 1, 899000, 899000, '2026-08-17 08:48:16', '2026-08-17 08:48:16'),
(252, 93, 1, 1, 899000, 899000, '2026-08-17 08:48:43', '2026-08-17 08:48:43'),
(253, 94, 1, 1, 899000, 899000, '2026-08-17 08:51:37', '2026-08-17 08:51:37'),
(254, 95, 1, 1, 928000, 928000, '2026-08-17 08:52:50', '2026-08-17 08:52:50'),
(255, 96, 1, 1, 725000, 725000, '2026-08-17 09:11:00', '2026-08-17 09:11:00'),
(257, 98, 3, 50, 1050000, 52500000, '2026-08-17 11:43:42', '2026-08-17 11:43:42'),
(258, 99, 4, 1, 360000, 360000, '2026-08-18 09:30:19', '2026-08-18 09:30:19'),
(259, 100, 8, 50, 649000, 32450000, '2026-08-18 09:36:34', '2026-08-18 09:36:34'),
(260, 101, 46, 50, 2600000, 130000000, '2026-08-18 09:47:03', '2026-08-18 09:47:03'),
(261, 102, 46, 1, 3172000, 3172000, '2026-08-18 09:53:15', '2026-08-18 09:53:15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint UNSIGNED NOT NULL,
  `transaction_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,0) NOT NULL,
  `payment_date` datetime NOT NULL,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_transaction_code_unique` (`transaction_code`),
  KEY `payments_order_id_foreign` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `transaction_code`, `payment_method`, `amount`, `payment_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'TXN00000001', 'bank_transfer', 5700000, '2026-07-21 11:12:48', 'pending', '2026-07-21 04:12:48', '2026-07-21 04:12:48'),
(2, 5, 'TXN00000005', 'vnpay', 1980000, '2026-07-26 13:31:47', 'pending', '2026-07-26 06:31:47', '2026-07-26 06:31:47'),
(3, 6, 'TXN00000006', 'vnpay', 13500000, '2026-07-16 13:39:46', 'success', '2026-07-16 06:39:46', '2026-07-16 06:39:46'),
(4, 8, 'TXN00000008', 'momo', 5000000, '2026-07-18 17:09:25', 'pending', '2026-07-18 10:09:25', '2026-07-18 10:09:25'),
(5, 12, 'TXN00000012', 'cod', 10390000, '2026-06-30 09:28:01', 'pending', '2026-06-30 02:28:01', '2026-06-30 02:28:01'),
(6, 13, 'TXN00000013', 'cod', 18560000, '2026-07-12 21:33:32', 'failed', '2026-07-12 14:33:32', '2026-07-12 14:33:32'),
(7, 15, 'TXN00000015', 'bank_transfer', 2970000, '2026-07-22 11:54:18', 'pending', '2026-07-22 04:54:18', '2026-07-22 04:54:18'),
(8, 17, 'TXN00000017', 'momo', 6283102, '2026-06-30 20:23:01', 'pending', '2026-06-30 13:23:01', '2026-06-30 13:23:01'),
(9, 20, 'TXN00000020', 'cod', 9770000, '2026-07-25 09:40:36', 'failed', '2026-07-25 02:40:36', '2026-07-25 02:40:36'),
(10, 21, 'TXN00000021', 'vnpay', 7368847, '2026-07-21 12:43:22', 'failed', '2026-07-21 05:43:22', '2026-07-21 05:43:22'),
(11, 24, 'TXN00000024', 'momo', 13665782, '2026-07-10 21:18:31', 'failed', '2026-07-10 14:18:31', '2026-07-10 14:18:31'),
(12, 25, 'TXN00000025', 'bank_transfer', 7570027, '2026-07-20 21:20:57', 'failed', '2026-07-20 14:20:57', '2026-07-20 14:20:57'),
(13, 27, 'TXN00000027', 'bank_transfer', 2731157, '2026-07-21 21:32:45', 'failed', '2026-07-21 14:32:45', '2026-07-21 14:32:45'),
(14, 29, 'TXN00000029', 'bank_transfer', 4330994, '2026-07-19 09:34:21', 'success', '2026-07-19 02:34:21', '2026-07-19 02:34:21'),
(15, 31, 'TXN00000031', 'cod', 11371026, '2026-07-05 08:26:25', 'pending', '2026-07-05 01:26:25', '2026-07-05 01:26:25'),
(16, 32, 'TXN00000032', 'bank_transfer', 1300000, '2026-07-25 13:43:52', 'pending', '2026-07-25 06:43:52', '2026-07-25 06:43:52'),
(17, 34, 'TXN00000034', 'vnpay', 12494864, '2026-06-26 08:40:11', 'success', '2026-06-26 01:40:11', '2026-06-26 01:40:11'),
(18, 36, 'TXN00000036', 'bank_transfer', 7380000, '2026-07-15 15:00:41', 'failed', '2026-07-15 08:00:41', '2026-07-15 08:00:41'),
(19, 37, 'TXN00000037', 'bank_transfer', 5400000, '2026-07-21 16:14:49', 'pending', '2026-07-21 09:14:49', '2026-07-21 09:14:49'),
(20, 38, 'TXN00000038', 'bank_transfer', 7240000, '2026-07-02 20:28:44', 'pending', '2026-07-02 13:28:44', '2026-07-02 13:28:44'),
(21, 39, 'TXN00000039', 'momo', 4200000, '2026-07-09 15:35:53', 'success', '2026-07-09 08:35:53', '2026-07-09 08:35:53'),
(22, 40, 'TXN00000040', 'momo', 2840000, '2026-07-22 10:02:23', 'success', '2026-07-22 03:02:23', '2026-07-22 03:02:23'),
(23, 41, 'TXN00000041', 'bank_transfer', 3520000, '2026-07-12 19:19:32', 'pending', '2026-07-12 12:19:32', '2026-07-12 12:19:32'),
(24, 42, 'TXN00000042', 'bank_transfer', 3773688, '2026-07-02 13:51:36', 'pending', '2026-07-02 06:51:36', '2026-07-02 06:51:36'),
(25, 43, 'TXN00000043', 'momo', 5844513, '2026-07-05 16:39:23', 'success', '2026-07-05 09:39:23', '2026-07-05 09:39:23'),
(26, 45, 'TXN00000045', 'vnpay', 7086703, '2026-06-29 08:09:20', 'pending', '2026-06-29 01:09:20', '2026-06-29 01:09:20'),
(27, 47, 'TXN00000047', 'cod', 20742481, '2026-07-08 21:55:05', 'failed', '2026-07-08 14:55:05', '2026-07-08 14:55:05'),
(28, 48, 'TXN00000048', 'momo', 5132750, '2026-06-30 09:30:09', 'pending', '2026-06-30 02:30:09', '2026-06-30 02:30:09'),
(29, 49, 'TXN00000049', 'bank_transfer', 4846690, '2026-07-24 10:24:51', 'success', '2026-07-24 03:24:51', '2026-07-24 03:24:51'),
(30, 51, 'PAY-51-1785029956', 'bank_transfer', 1190000, '2026-07-26 08:39:16', 'pending', '2026-07-26 01:39:16', '2026-07-26 01:39:16'),
(31, 52, 'PAY-52-1785029993', 'bank_transfer', 1190000, '2026-07-26 08:39:53', 'pending', '2026-07-26 01:39:53', '2026-07-26 01:39:53'),
(32, 53, 'PAY-53-1785030273', 'bank_transfer', 1190000, '2026-07-26 08:44:33', 'pending', '2026-07-26 01:44:33', '2026-07-26 01:44:33'),
(33, 54, 'PAY-54-1785030289', 'bank_transfer', 1190000, '2026-07-26 08:44:49', 'pending', '2026-07-26 01:44:49', '2026-07-26 01:44:49'),
(34, 55, 'PAY-55-1785030300', 'bank_transfer', 1190000, '2026-07-26 08:45:00', 'pending', '2026-07-26 01:45:00', '2026-07-26 01:45:00'),
(35, 56, 'PAY-56-1785033371', 'bank_transfer', 1190000, '2026-07-26 09:36:11', 'pending', '2026-07-26 02:36:11', '2026-07-26 02:36:11'),
(36, 57, 'PAY-57-1785035537', 'bank_transfer', 1190000, '2026-07-26 10:12:17', 'pending', '2026-07-26 03:12:17', '2026-07-26 03:12:17'),
(37, 58, 'PAY-58-1785098410', 'bank_transfer', 1450000, '2026-07-27 03:40:10', 'pending', '2026-07-26 20:40:10', '2026-07-26 20:40:10'),
(38, 61, 'PAY-WS-61-1785847612', 'bank_transfer', 0, '2026-08-04 19:46:52', 'pending', '2026-08-04 12:46:52', '2026-08-04 12:46:52'),
(39, 62, 'PAY-WS-62-1785854373', 'bank_transfer', 0, '2026-08-04 21:39:33', 'pending', '2026-08-04 14:39:33', '2026-08-04 14:39:33'),
(40, 63, 'PAY-WS-63-1785854653', 'bank_transfer', 0, '2026-08-04 21:44:13', 'pending', '2026-08-04 14:44:13', '2026-08-04 14:44:13'),
(41, 64, 'PAY-WS-64-1785915914', 'bank_transfer', 0, '2026-08-05 14:45:14', 'pending', '2026-08-05 07:45:14', '2026-08-05 07:45:14'),
(42, 65, 'PAY-65-1785953411', 'cod', 2867000, '2026-08-06 01:10:11', 'pending', '2026-08-05 18:10:11', '2026-08-05 18:10:11'),
(43, 66, 'PAY-66-1786077730', 'cod', 6050000, '2026-08-07 11:42:10', 'pending', '2026-08-07 04:42:10', '2026-08-07 04:42:10'),
(44, 67, 'PAY-67-1786078256', 'cod', 2750000, '2026-08-07 11:50:56', 'pending', '2026-08-07 04:50:56', '2026-08-07 04:50:56'),
(45, 68, 'PAY-WS-68-1786343151', 'bank_transfer', 0, '2026-08-10 13:25:51', 'pending', '2026-08-10 06:25:51', '2026-08-10 06:25:51'),
(46, 69, 'PAY-WS-69-1786343409', 'bank_transfer', 0, '2026-08-10 13:30:09', 'pending', '2026-08-10 06:30:09', '2026-08-10 06:30:09'),
(47, 70, 'PAY-WS-70-1786344874', 'bank_transfer', 0, '2026-08-10 13:54:34', 'pending', '2026-08-10 06:54:34', '2026-08-10 06:54:34'),
(48, 71, 'PAY-WS-71-1786348391', 'bank_transfer', 0, '2026-08-10 14:53:11', 'pending', '2026-08-10 07:53:11', '2026-08-10 07:53:11'),
(49, 72, 'PAY-WS-72-1786348636', 'bank_transfer', 0, '2026-08-10 14:57:16', 'pending', '2026-08-10 07:57:16', '2026-08-10 07:57:16'),
(50, 73, 'PAY-WS-73-1786348712', 'bank_transfer', 0, '2026-08-10 14:58:32', 'pending', '2026-08-10 07:58:32', '2026-08-10 07:58:32'),
(51, 74, 'PAY-WS-74-1786349008', 'bank_transfer', 0, '2026-08-10 15:03:28', 'pending', '2026-08-10 08:03:28', '2026-08-10 08:03:28'),
(52, 75, 'PAY-75-1786786158', 'cod', 1410000, '2026-08-15 16:29:18', 'pending', '2026-08-15 09:29:18', '2026-08-15 09:29:18'),
(53, 76, 'PAY-76-1786789248', 'cod', 1344000, '2026-08-15 17:20:48', 'pending', '2026-08-15 10:20:48', '2026-08-15 10:20:48'),
(54, 77, 'PAY-77-1786789873', 'cod', 1344000, '2026-08-15 17:31:13', 'pending', '2026-08-15 10:31:13', '2026-08-15 10:31:13'),
(55, 78, 'PAY-WS-78-1786873415', 'bank_transfer', 0, '2026-08-16 16:43:35', 'pending', '2026-08-16 09:43:35', '2026-08-16 09:43:35'),
(56, 79, '79', 'bank_transfer', 0, '2026-08-16 17:05:50', 'pending', '2026-08-16 10:05:50', '2026-08-16 10:06:30'),
(57, 80, '80', 'bank_transfer', 4350000, '2026-08-16 17:09:47', 'pending', '2026-08-16 10:09:47', '2026-08-17 08:21:38'),
(58, 81, 'PAY-WS-81-1786936033', 'bank_transfer', 0, '2026-08-17 10:07:13', 'pending', '2026-08-17 03:07:13', '2026-08-17 03:07:13'),
(59, 82, 'PAY-WS-82-1786951033', 'bank_transfer', 0, '2026-08-17 14:17:13', 'pending', '2026-08-17 07:17:13', '2026-08-17 07:17:13'),
(60, 83, '83', 'bank_transfer', 0, '2026-08-17 14:18:40', 'pending', '2026-08-17 07:18:40', '2026-08-17 07:19:07'),
(61, 84, 'PAY-WS-84-1786951423', 'bank_transfer', 0, '2026-08-17 14:23:43', 'pending', '2026-08-17 07:23:43', '2026-08-17 07:23:43'),
(62, 85, '85', 'bank_transfer', 0, '2026-08-17 14:25:34', 'pending', '2026-08-17 07:25:34', '2026-08-17 07:26:15'),
(63, 86, 'PAY-WS-86-1786951726', 'bank_transfer', 0, '2026-08-17 14:28:46', 'pending', '2026-08-17 07:28:46', '2026-08-17 07:28:46'),
(64, 87, '87', 'bank_transfer', 0, '2026-08-17 14:39:12', 'pending', '2026-08-17 07:39:12', '2026-08-17 07:39:24'),
(65, 88, 'PAY-88-1786953549', 'bank_transfer', 855500, '2026-08-17 14:59:09', 'pending', '2026-08-17 07:59:09', '2026-08-17 07:59:09'),
(66, 89, 'PAY-89-1786955391', 'bank_transfer', 870000, '2026-08-17 15:29:51', 'pending', '2026-08-17 08:29:51', '2026-08-17 08:29:51'),
(67, 90, '90', 'bank_transfer', 870000, '2026-08-17 15:31:42', 'pending', '2026-08-17 08:31:42', '2026-08-17 08:32:21'),
(68, 91, '91', 'bank_transfer', 0, '2026-08-17 15:44:52', 'pending', '2026-08-17 08:44:52', '2026-08-17 08:45:18'),
(69, 92, 'PAY-92-1786956496', 'bank_transfer', 899000, '2026-08-17 15:48:16', 'pending', '2026-08-17 08:48:16', '2026-08-17 08:48:16'),
(70, 93, '93', 'bank_transfer', 899000, '2026-08-17 15:48:43', 'pending', '2026-08-17 08:48:43', '2026-08-17 10:24:41'),
(71, 94, 'PAY-94-1786956697', 'bank_transfer', 899000, '2026-08-17 15:51:37', 'pending', '2026-08-17 08:51:37', '2026-08-17 08:51:37'),
(72, 95, '95', 'bank_transfer', 928000, '2026-08-17 15:52:50', 'pending', '2026-08-17 08:52:50', '2026-08-17 08:53:04'),
(73, 96, '96', 'bank_transfer', 725000, '2026-08-17 16:11:00', 'pending', '2026-08-17 09:11:00', '2026-08-17 09:11:08'),
(75, 98, 'PAY-WS-98-1786967022', 'bank_transfer', 0, '2026-08-17 18:43:42', 'pending', '2026-08-17 11:43:42', '2026-08-17 11:43:42'),
(76, 99, 'PAY-99-1787045419', 'cod', 360000, '2026-08-18 16:30:19', 'pending', '2026-08-18 09:30:19', '2026-08-18 09:30:19'),
(77, 100, 'PAY-WS-100-1787045794', 'bank_transfer', 0, '2026-08-18 16:36:34', 'pending', '2026-08-18 09:36:34', '2026-08-18 09:36:34'),
(78, 101, '101', 'bank_transfer', 0, '2026-08-18 16:47:03', 'pending', '2026-08-18 09:47:03', '2026-08-18 09:47:45'),
(79, 102, '102', 'bank_transfer', 3172000, '2026-08-18 16:53:15', 'pending', '2026-08-18 09:53:15', '2026-08-18 09:54:15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` bigint UNSIGNED NOT NULL,
  `brand_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `material` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` json DEFAULT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `thumbnail` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_preorder` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_brand_id_foreign` (`brand_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `category_id`, `brand_id`, `name`, `slug`, `material`, `image_url`, `description`, `thumbnail`, `is_featured`, `is_preorder`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Balo Laptop BigBag Pro 15.6 inch', 'balo-laptop-bigbag-pro-156-inch', 'Ballistic Nylon 1680D', '[\"https://bizweb.dktcdn.net/100/044/266/products/balo-laptop-du-lich-cao-cap-thiet-ke-da-nang-tich-hop-cong-usb-fix-laptop-15-6-inch-mark-ryden-compacto-pro-12.png?v=1754064998880\", \"https://bizweb.dktcdn.net/thumb/small/100/044/266/products/balo-laptop-du-lich-cao-cap-thiet-ke-da-nang-tich-hop-cong-usb-fix-laptop-15-6-inch-mark-ryden-compacto-pro-7.png?v=1762592453077\", \"https://bizweb.dktcdn.net/thumb/small/100/044/266/products/balo-laptop-du-lich-cao-cap-thiet-ke-da-nang-tich-hop-cong-usb-ngan-laptop-rieng-mark-ryden-compacto-pro-1.jpg?v=1762592489583\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/balo-laptop-du-lich-cao-cap-thiet-ke-da-nang-tich-hop-cong-usb-fix-laptop-15-6-inch-mark-ryden-compacto-pro-12.png?v=1754064998880', 1, 0, 1, '2026-07-26 01:32:28', '2026-08-07 05:05:28'),
(2, 1, 7, 'Balo Laptop Thời Trang MARK RYDEN ROCKY', 'balo-laptop-thoi-trang-mark-ryden-rocky', 'Oxford - Leather', '[\"https://markryden.vn/wp-content/uploads/2024/07/1.png\", \"https://markryden.vn/wp-content/uploads/2024/07/MR-6602.1-768x1284.webp\", \"https://markryden.vn/wp-content/uploads/2024/07/5.png\"]', NULL, 'https://markryden.vn/wp-content/uploads/2024/07/1.png', 1, 0, 1, '2026-07-26 01:32:28', '2026-08-07 05:29:57'),
(3, 3, 3, 'KingBag Crossbody Mini', 'kingbag-crossbody-mini', 'Da PU cao cấp', '[\"https://product.hstatic.net/200000273565/product/1__1__298a6cabc69943318235e40bfbced192_master.jpg\", \"https://images.unsplash.com/photo-1547949003-9792a18a2601?w=500\"]', NULL, 'https://product.hstatic.net/200000273565/product/1__1__298a6cabc69943318235e40bfbced192_master.jpg', 0, 1, 1, '2026-07-26 01:32:28', '2026-08-18 09:23:39'),
(4, 5, 4, 'Balo Chống Sốc Laptop 13-15″ TOMTOC (USA)', 'balo-chong-soc-laptop-13-15-tomtoc-usa', 'Vải Cordura - 840D Nylon', '[\"https://bizweb.dktcdn.net/100/044/266/files/tomtoc_usa_t77s1d1_20__c73a830c-65b0-4fae-bec6-3f0731b9a58b.png?v=1780333232839\", \"https://bizweb.dktcdn.net/100/044/266/files/tomtoc_usa_t77s1d1_21__871aae2e-93f7-4a50-902b-55064abf7966.png?v=1780333329450\", \"https://bizweb.dktcdn.net/100/044/266/files/balo_chong_soc_laptop_13-15_tomtoc_usa_t77s1d1_-_black_size_nho_22l_6_1.png?v=1780333147915\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/files/tomtoc_usa_t77s1d1_20__c73a830c-65b0-4fae-bec6-3f0731b9a58b.png?v=1780333232839', 1, 0, 1, '2026-07-26 01:32:28', '2026-08-07 05:32:08'),
(5, 4, 5, 'Targus CitySmart 15.6 inch', 'targus-citysmart-156-inch', '2-Tone Polyester', '[\"https://www.maccenter.vn/Bags/Targus-CitySmart-Professional-A.jpg\", \"https://www.maccenter.vn/Bags/Targus-CitySmart-Professional-B.jpg\"]', NULL, 'https://www.maccenter.vn/Bags/Targus-CitySmart-Professional-A.jpg', 1, 0, 1, '2026-07-26 01:32:28', '2026-08-07 05:12:55'),
(6, 2, 6, 'Balo Laptop MIKKOR BRYANT PREMIER', 'balo-laptop-mikkor-bryant-premier', 'Vải 900D Kodura Supertex cùng vải lót P-210D', '[\"/media/image/6a756cede8000.jpg\", \"/media/image/6a756cede859d.jpg\", \"/media/image/6a756cede8a16.jpg\", \"/media/image/6a756cede0d19.jpg\"]', NULL, '/media/image/6a756cede8000.jpg', 0, 0, 1, '2026-07-26 01:32:28', '2026-08-07 05:28:27'),
(7, 4, 8, 'Balo Thời Trang Nữ Tính, Fix Laptop BOPAI LADY', 'balo-thoi-trang-nu-tinh-fix-laptop-bopai-lady', 'Nylon', '[\"https://bizweb.dktcdn.net/100/044/266/files/balo-thoi-trang-nu-tinh-fix-laptop-mong-nhe-14-1-inch-bopai-lady-pink-4.png?v=1746292069590\", \"https://bizweb.dktcdn.net/thumb/small/100/044/266/products/balo-thoi-trang-nu-tinh-fix-laptop-mong-nhe-14-1-inch-bopai-lady-pink-4-12df06ec-f1fa-44a6-9d7c-e75fb1d45e9e.png?v=1746351531307\", \"https://bizweb.dktcdn.net/thumb/small/100/044/266/products/balo-thoi-trang-nu-tinh-fix-laptop-mong-nhe-14-1-inch-bopai-lady-pink-5.png?v=1746351544487\", \"https://bizweb.dktcdn.net/thumb/small/100/044/266/products/balo-thoi-trang-nu-tinh-fix-laptop-mong-nhe-14-1-inch-bopai-lady-pink-1.jpg?v=1746351544487\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/files/balo-thoi-trang-nu-tinh-fix-laptop-mong-nhe-14-1-inch-bopai-lady-pink-4.png?v=1746292069590', 1, 0, 1, '2026-07-26 01:32:28', '2026-08-07 05:20:55'),
(8, 1, 3, 'KingBag Backpack Pro', 'kingbag-backpack-pro', 'Polyester 1200D', '[\"https://cdn2.fptshop.com.vn/unsafe/1920x0/filters:format(webp):quality(75)/balo_kingbag_zelos_kbg_138_156_inch_2_9a6a4d817c.jpg\", \"https://images.unsplash.com/photo-1601924582970-9238bcb495d4?w=500\"]', NULL, 'https://cdn2.fptshop.com.vn/unsafe/1920x0/filters:format(webp):quality(75)/balo_kingbag_zelos_kbg_138_156_inch_2_9a6a4d817c.jpg', 0, 1, 1, '2026-07-26 01:32:28', '2026-08-18 09:24:38'),
(9, 5, 4, 'Everki Flight 14L', 'everki-flight-14l', 'Ballistic Nylon 1680D', '[\"https://www.everki.com/media/catalog/product/cache/ce976a4921f47273e3ea74f8ffb4648f/e/k/ekp118_01.jpg\", \"https://www.everki.com/media/catalog/product/cache/ce976a4921f47273e3ea74f8ffb4648f/e/k/ekp118_03.jpg\", \"https://www.everki.com/media/catalog/product/cache/ce976a4921f47273e3ea74f8ffb4648f/e/k/ekp118_06.jpg\", \"https://youtu.be/QLQ7cjGxris?si=CeW_i_RCWQD5IcUL\"]', NULL, 'https://www.everki.com/media/catalog/product/cache/ce976a4921f47273e3ea74f8ffb4648f/e/k/ekp118_01.jpg', 0, 0, 1, '2026-07-26 01:32:28', '2026-08-07 05:24:56'),
(10, 1, 5, 'Balo Laptop Targus Groove X Max 15', 'balo-laptop-targus-groove-x-max-15', 'Polyester 300D', '[\"https://anphat.com.vn/media/product/34030_3.jpg\", \"https://anphat.com.vn/media/product/34030_4.jpg\", \"https://anphat.com.vn/media/product/34030_2.jpg\"]', NULL, 'https://anphat.com.vn/media/product/34030_3.jpg', 1, 0, 1, '2026-07-26 01:32:28', '2026-08-07 05:37:57'),
(11, 2, 6, 'Balo du lịch lớn Samsonite 50L', 'balo-du-lich-lon-samsonite-50l', 'Nylon 600D', '[\"https://cdn.hstatic.net/products/200000321545/xanhla_31x24x44cm_1_6e936579337d4369a507ace92ee90955_master.jpg\", \"https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500\"]', NULL, 'https://cdn.hstatic.net/products/200000321545/xanhla_31x24x44cm_1_6e936579337d4369a507ace92ee90955_master.jpg', 0, 1, 1, '2026-07-26 01:32:28', '2026-08-18 09:26:10'),
(12, 2, 2, 'Minimalist V2 Ultralight Backpack', 'minimalist-v2-ultralight-backpack', 'Robic Nylon', '[\"https://www.sixmoondesigns.com/cdn/shop/products/OV06170.jpg?v=1682026829&width=1600\", \"https://www.sixmoondesigns.com/cdn/shop/products/OV06129.jpg?v=1682026829&width=1000\", \"https://www.sixmoondesigns.com/cdn/shop/products/HydrationTubeoutbackofpack.png?v=1682026829&width=1000\"]', NULL, 'https://www.sixmoondesigns.com/cdn/shop/products/OV06170.jpg?v=1682026829&width=1600', 0, 0, 1, '2026-07-26 01:32:28', '2026-08-07 05:36:02'),
(13, 3, 1, 'Túi Đeo Chéo Ngang Fix iPad Pro 11\" MOYYI PEDRO', 'tui-deo-cheo-ngang-fix-ipad-pro-11-moyyi-pedro', 'Canvas tái chế', '[\"https://bizweb.dktcdn.net/100/044/266/files/tui-deo-cheo-tho-i-trang-moyyi-mo0438-12.jpg?v=1654846130970\", \"https://bizweb.dktcdn.net/100/044/266/files/tui-deo-cheo-tho-i-trang-moyyi-mo0438-2.jpg?v=1654846122026\", \"https://bizweb.dktcdn.net/100/044/266/files/tui-deo-cheo-tho-i-trang-moyyi-mo0438-11.jpg?v=1654846130292\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/files/tui-deo-cheo-tho-i-trang-moyyi-mo0438-12.jpg?v=1654846130970', 1, 0, 1, '2026-07-26 01:32:28', '2026-08-07 05:34:03'),
(14, 1, 9, 'Balo YKK ROKIN VELOCITY', 'balo-ykk-rokin-velocity', 'Vải 600 Polyester và 1680D Nylon trượt nước cao cấp', '[\"https://bizweb.dktcdn.net/100/044/266/files/balo-nam-cuc-chat-sieu-ben-chac-thiet-ke-tien-ich-fix-laptop-15-6-inch-rokin-velocity-1.jpg?v=1668572884404\", \"https://bizweb.dktcdn.net/100/044/266/files/balo-nam-cuc-chat-sieu-ben-chac-thiet-ke-tien-ich-fix-laptop-15-6-inch-rokin-velocity-3.jpg?v=1668572887413\", \"https://bizweb.dktcdn.net/100/044/266/files/balo-nam-cuc-chat-sieu-ben-chac-thiet-ke-tien-ich-fix-laptop-15-6-inch-rokin-velocity-5.jpg?v=1668572890676\", \"https://bizweb.dktcdn.net/100/044/266/files/balo-nam-cuc-chat-sieu-ben-chac-thiet-ke-tien-ich-fix-laptop-15-6-inch-rokin-velocity-10.jpg?v=1668572898901\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/files/balo-nam-cuc-chat-sieu-ben-chac-thiet-ke-tien-ich-fix-laptop-15-6-inch-rokin-velocity-1.jpg?v=1668572884404', 0, 0, 1, '2026-08-15 05:08:53', '2026-08-15 05:11:48'),
(15, 1, 4, 'Balo KMORE VIOLET', 'balo-kmore-violet', 'Vải Polyester 600 900 WR PU 3T Polyester 210D', '[\"https://bizweb.dktcdn.net/100/044/266/products/balo-phong-cach-tre-trung-kmore-violet-red-1.jpg?v=1785243637273\", \"https://bizweb.dktcdn.net/100/044/266/products/balo-phong-cach-tre-trung-kmore-violet-satallite-1.jpg?v=1785242348513\", \"https://bizweb.dktcdn.net/100/044/266/products/balo-phong-cach-tre-trung-kmore-violet-olive-3.jpg?v=1785244375423\", \"https://bizweb.dktcdn.net/100/044/266/products/balo-phong-cach-tre-trung-kmore-violet-satallite-olive-3.jpg?v=1785242935713\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/balo-phong-cach-tre-trung-kmore-violet-red-1.jpg?v=1785243637273', 0, 0, 1, '2026-08-15 05:16:40', '2026-08-15 05:18:31'),
(16, 2, 7, 'Balo Du Lịch MARK RYDEN AEGIS FORTRESS', 'balo-du-lich-mark-ryden-aegis-fortress', 'Oxford', '[\"https://bizweb.dktcdn.net/100/044/266/products/balo-du-lich-da-nang-cao-cap-mark-ryden-aegis-fortre-17.jpg?v=1780645506170\", \"https://bizweb.dktcdn.net/100/044/266/products/mark-ryden-t-mr8288.png?v=1780645506170\", \"https://bizweb.dktcdn.net/100/044/266/products/balo-du-lich-da-nang-cao-cap-mark-ryden-aegis-fortre-10.jpg?v=1780645506170\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/balo-du-lich-da-nang-cao-cap-mark-ryden-aegis-fortre-17.jpg?v=1780645506170', 0, 0, 1, '2026-08-15 05:21:23', '2026-08-15 05:21:23'),
(17, 1, 7, 'Balo Cao Cấp MARK RYDEN AERO SHIELD', 'balo-cao-cap-mark-ryden-aero-shield', 'Oxford và PU', '[\"https://bizweb.dktcdn.net/100/044/266/products/hd1b3a7d8c34a479a9102d3f6e1a9313.png?v=1740758925340\", \"https://bizweb.dktcdn.net/100/044/266/products/h59c9b38c5db240bcab73dd5c0313123.png?v=1740758925340\", \"https://bizweb.dktcdn.net/100/044/266/products/hecaab100439a4d7083672477939ec48.png?v=1740758925340\", \"https://bizweb.dktcdn.net/100/044/266/products/h685fb821063b4f0a997867263d5d23c.png?v=1740758925340\", \"https://youtu.be/huKQrclnE_I?si=igEUVYY8fLtAVV4u\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/hd1b3a7d8c34a479a9102d3f6e1a9313.png?v=1740758925340', 0, 0, 1, '2026-08-15 05:25:48', '2026-08-15 05:26:40'),
(18, 1, 7, 'Balo Laptop MARK RYDEN KEYBOSS', 'balo-laptop-mark-ryden-keyboss', 'ABS Oxford', '[\"https://bizweb.dktcdn.net/100/044/266/products/balo-khoa-so-dang-cap.png?v=1754539481533\", \"https://bizweb.dktcdn.net/100/044/266/products/balo-laptop-dang-cap-mark-ryden-keyboss-20-jpeg.jpg?v=1754539489600\", \"https://bizweb.dktcdn.net/100/044/266/products/balo-laptop-dang-cap-mark-ryden-keyboss-3.png?v=1754539493417\", \"https://bizweb.dktcdn.net/100/044/266/products/balo-laptop-dang-cap-mark-ryden-keyboss-5-optimized.png?v=1754539497027\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/balo-khoa-so-dang-cap.png?v=1754539481533', 0, 0, 1, '2026-08-15 05:29:49', '2026-08-15 05:29:49'),
(19, 6, 10, 'Túi Thể Thao Đựng Vợt PickleBall HIER', 'tui-the-thao-dung-vot-pickleball-hier', 'Vải Tpe trượt nước và phối quai Giả da vân Safiano từ Ý', '[\"https://bizweb.dktcdn.net/100/044/266/products/tui-dung-vot-pickleball-co-ngan-giay-rieng-hier-2-in-1-10.jpg?v=1745126823230\", \"https://bizweb.dktcdn.net/100/044/266/products/tui-dung-vot-pickleball-co-ngan-giay-rieng-hier-2-in-1-14.jpg?v=1745126845143\", \"https://bizweb.dktcdn.net/100/044/266/products/tui-dung-vot-pickleball-co-ngan-giay-rieng-hier-2-in-1-8.jpg?v=1745126923130\", \"https://bizweb.dktcdn.net/100/044/266/products/tui-dung-vot-pickleball-co-ngan-giay-rieng-hier-2-in-1-11.jpg?v=1745126923130\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/tui-dung-vot-pickleball-co-ngan-giay-rieng-hier-2-in-1-10.jpg?v=1745126823230', 0, 0, 1, '2026-08-15 05:35:05', '2026-08-15 05:38:58'),
(20, 2, 7, 'Túi Du Lịch Travel & Business MARK RYDEN CARRY', 'tui-du-lich-travel-business-mark-ryden-carry', 'Polyester trượt nước', '[\"https://bizweb.dktcdn.net/100/044/266/products/bag-for-travel-business-mark-ryden-carry-1.jpg?v=1640425196807\", \"https://bizweb.dktcdn.net/100/044/266/products/bag-for-travel-business-mark-ryden-carry-4.jpg?v=1640425196807\", \"https://bizweb.dktcdn.net/100/044/266/products/bag-for-travel-business-mark-ryden-carry-5.jpg?v=1640425196807\", \"https://bizweb.dktcdn.net/100/044/266/products/bag-for-travel-business-mark-ryden-carry-3.jpg?v=1640425196807\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/bag-for-travel-business-mark-ryden-carry-1.jpg?v=1640425196807', 0, 0, 1, '2026-08-15 05:43:45', '2026-08-15 05:43:45'),
(21, 5, 1, 'BALO TOMTOC (USA) T73M1D1 30L', 'balo-tomtoc-usa-t73m1d1-30l', 'Vải X PAC công nghệ mới cao cấp', '[\"https://bizweb.dktcdn.net/100/044/266/products/balo-chong-soc-ultrabook-13-14-15-16-tomtoc-usa-h73e1d1-4.jpg?v=1686644323537\", \"https://bizweb.dktcdn.net/100/044/266/products/balo-chong-soc-ultrabook-13-14-15-16-tomtoc-usa-h73e1d1-6.jpg?v=1742808429977\", \"https://bizweb.dktcdn.net/100/044/266/products/balo-chong-soc-ultrabook-13-14-15-16-tomtoc-usa-h73e1d1-8.jpg?v=1742808429977\", \"https://bizweb.dktcdn.net/100/044/266/products/balo-chong-soc-ultrabook-13-14-15-16-tomtoc-usa-h73e1d1-5.jpg?v=1742808429977\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/balo-chong-soc-ultrabook-13-14-15-16-tomtoc-usa-h73e1d1-4.jpg?v=1686644323537', 0, 0, 1, '2026-08-15 05:47:14', '2026-08-17 08:55:58'),
(23, 3, 7, 'CẶP XÁCH LAPTOP MARK RYDEN CLASSIC', 'cap-xach-laptop-mark-ryden-classic', 'Oxford', '[\"https://bizweb.dktcdn.net/100/044/266/products/cap-xach-laptop-13-3-14-inch-mark-ryden-classic-4.png?v=1740731222867\", \"https://bizweb.dktcdn.net/100/044/266/products/cap-xach-laptop-13-3-14-inch-mark-ryden-classic-2.png?v=1740731226840\", \"https://bizweb.dktcdn.net/100/044/266/products/cap-xach-laptop-13-3-14-inch-mark-ryden-classic-1.png?v=1740731226840\", \"https://bizweb.dktcdn.net/100/044/266/products/cap-xach-laptop-13-3-14-inch-mark-ryden-classic-16.png?v=1740731225090\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/cap-xach-laptop-13-3-14-inch-mark-ryden-classic-4.png?v=1740731222867', 0, 0, 1, '2026-08-18 04:40:15', '2026-08-18 04:40:15'),
(24, 7, 11, 'Cặp Xách Chống Sốc Macbook Air 15-16\" TOMTOC (USA) A33F3D1', 'cap-xach-chong-soc-macbook-air-15-16-tomtoc-usa-a33f3d1', 'Vải Polyester trượt nước - lớp lót bên trong mềm mại', '[\"https://bizweb.dktcdn.net/100/044/266/products/cap-xach-chong-soc-macbook-air-15-16-tomtoc-usa-a33f3d1-black-7.png?v=1784982351013\", \"https://bizweb.dktcdn.net/100/044/266/products/cap-xach-chong-soc-macbook-air-15-16-tomtoc-usa-a33f3d1-black-1.png?v=1784982351013\", \"https://bizweb.dktcdn.net/100/044/266/products/cap-xach-chong-soc-macbook-air-15-16-tomtoc-usa-a33f3d1-black-6.png?v=1784982351013\", \"https://bizweb.dktcdn.net/100/044/266/products/cap-xach-chong-soc-macbook-air-15-16-tomtoc-usa-a33f3d1-black-6-l.png?v=1784982351013\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/cap-xach-chong-soc-macbook-air-15-16-tomtoc-usa-a33f3d1-black-7.png?v=1784982351013', 0, 0, 1, '2026-08-18 04:44:02', '2026-08-18 05:02:28'),
(25, 3, 8, 'Cặp Xách/ Túi Đeo Chéo Cao Cấp BOPAI GENTLE', 'cap-xach-tui-deo-cheo-cao-cap-bopai-gentle', 'PVC - Microfiber', '[\"https://bizweb.dktcdn.net/100/044/266/products/tui-deo-cheo-tho-i-trang-bopai-12.jpg?v=1670071003033\", \"https://bizweb.dktcdn.net/100/044/266/products/tui-deo-cheo-tho-i-trang-bopai-18.jpg?v=1670071645597\", \"https://bizweb.dktcdn.net/100/044/266/products/tui-deo-cheo-tho-i-trang-bopai-14.jpg?v=1670071645597\", \"https://youtu.be/WOEwyTK-U2s?si=5p46Lioouqc0k0PO\"]', '<p><strong>- Số Ngăn:&nbsp;</strong>1 ngăn chính - nhiều ngăn phụ</p><p><strong>- Khối Lượng:&nbsp;</strong>0.76 Kg</p><p><strong>- Tải Trọng:&nbsp;</strong>10 Kg</p>', 'https://bizweb.dktcdn.net/100/044/266/products/tui-deo-cheo-tho-i-trang-bopai-12.jpg?v=1670071003033', 0, 0, 1, '2026-08-18 04:46:56', '2026-08-18 04:46:56'),
(26, 8, 11, 'Túi Chống Sốc Macbook Pro 14\" TOMTOC (USA) A13D2DV', 'tui-chong-soc-macbook-pro-14-tomtoc-usa-a13d2dv', 'Vải tái chế bền bỉ - Vật liệu làm từ chai nhựa PET', '[\"https://bizweb.dktcdn.net/100/044/266/products/tui-chong-soc-macbook-pro-14-tomtoc-usa-a13d2d1gp-5.jpg?v=1665043794160\", \"https://bizweb.dktcdn.net/100/044/266/products/tui-chong-soc-macbook-pro-14-tomtoc-usa-a13d2d1gp-6.jpg?v=1665043794160\", \"https://bizweb.dktcdn.net/100/044/266/products/tui-chong-soc-macbook-pro-14-tomtoc-usa-a13d2g1gp-8.jpg?v=1691660007530\", \"https://bizweb.dktcdn.net/100/044/266/products/tui-chong-soc-macbook-pro-14-tomtoc-usa-a13d2g1gp-12.jpg?v=1691659954717\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/tui-chong-soc-macbook-pro-14-tomtoc-usa-a13d2d1gp-5.jpg?v=1665043794160', 0, 0, 1, '2026-08-18 05:02:02', '2026-08-18 05:44:02'),
(27, 3, 10, 'Túi Đeo Chéo Thời Trang Unisex HIER EVERYDAY MESSENGER', 'tui-deo-cheo-thoi-trang-unisex-hier-everyday-messenger', 'Vải Oxford trượt nước cao cấp', '[\"https://bizweb.dktcdn.net/100/044/266/products/tui-nam-deo-cheo-hier-everyday-messenger-11.jpg?v=1704616366980\", \"https://bizweb.dktcdn.net/100/044/266/products/tui-nam-deo-cheo-hier-everyday-messenger-8.jpg?v=1704617554430\", \"https://bizweb.dktcdn.net/100/044/266/products/tui-nam-deo-cheo-hier-everyday-messenger-16.jpg?v=1704614398717\", \"https://bizweb.dktcdn.net/100/044/266/products/tui-nam-deo-cheo-hier-everyday-messenger-22.jpg?v=1715519632207\", \"https://youtu.be/rQ4x0HqrqaA?si=9o316eXnv9kajNR2\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/tui-nam-deo-cheo-hier-everyday-messenger-11.jpg?v=1704616366980', 0, 0, 1, '2026-08-18 05:06:27', '2026-08-18 05:06:27'),
(28, 7, 11, 'Cặp Chống Sốc Macbook Pro 16\" TOMTOC (USA) A42E2Y1', 'cap-chong-soc-macbook-pro-16-tomtoc-usa-a42e2y1', 'Vải tái chế từ chai nhựa PET', '[\"https://bizweb.dktcdn.net/100/044/266/products/cap-chong-soc-macbook-pro-16-tomtoc-usa-a42e2y1-7.jpg?v=1700989977703\", \"https://bizweb.dktcdn.net/100/044/266/products/cap-chong-soc-macbook-pro-16-tomtoc-usa-a42e2y1-11.jpg?v=1700989978907\", \"https://bizweb.dktcdn.net/100/044/266/products/cap-chong-soc-macbook-pro-16-tomtoc-usa-a42e2y1-2.jpg?v=1680867582867\", \"https://bizweb.dktcdn.net/100/044/266/files/cap-chong-soc-macbook-pro-16-tomtoc-usa-a42e2y1-25.jpg?v=1680867636892\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/cap-chong-soc-macbook-pro-16-tomtoc-usa-a42e2y1-7.jpg?v=1700989977703', 0, 0, 1, '2026-08-18 05:09:12', '2026-08-18 05:09:12'),
(29, 3, 11, 'Túi Đeo Chéo Chống Sốc Macbook Pro 14\" TOMTOC (USA) A04D3D1', 'tui-deo-cheo-chong-soc-macbook-pro-14-tomtoc-usa-a04d3d1', 'Vải 840D Polyester trượt nước cao cấp siêu bền - chống mài mòn lót đệm mịn chống trầy xước', '[\"https://bizweb.dktcdn.net/100/044/266/products/tui-deo-cheo-chong-soc-macbook-pro-14-tomtoc-usa-a04d3d1-black-21.png?v=1784281474273\", \"https://bizweb.dktcdn.net/100/044/266/products/tui-deo-cheo-chong-soc-macbook-pro-14-tomtoc-usa-a04d3d1-black-14.png?v=1784281474273\", \"https://bizweb.dktcdn.net/100/044/266/files/tui_eo_cheo_chong_soc_macbook_pro_14_tomtoc_usa_a04d3d1_-_black_23_.png?v=1784654505221\", \"https://bizweb.dktcdn.net/100/044/266/products/tui-deo-cheo-chong-soc-macbook-pro-14-tomtoc-usa-a04d3d1-black-30-1.jpg?v=1784281474273\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/tui-deo-cheo-chong-soc-macbook-pro-14-tomtoc-usa-a04d3d1-black-21.png?v=1784281474273', 0, 0, 1, '2026-08-18 05:12:59', '2026-08-18 05:12:59'),
(30, 7, 7, 'CẶP XÁCH LAPTOP 13,3 - 14 inch MARK RYDEN CLASSIC', 'cap-xach-laptop-133-14-inch-mark-ryden-classic', 'Oxford', '[\"https://bizweb.dktcdn.net/100/044/266/products/cap-xach-laptop-13-3-14-inch-mark-ryden-classic-4.png?v=1740731222867\", \"https://bizweb.dktcdn.net/100/044/266/files/cap-xach-laptop-13-3-14-inch-mark-ryden-classic-11-optimized.png?v=1740731644581\", \"https://bizweb.dktcdn.net/thumb/small/100/044/266/products/cap-xach-laptop-13-3-14-inch-mark-ryden-classic-1.png?v=1740731226840\", \"https://youtu.be/uolEl8e8t5U?si=QjSP6dHFvc314Fqk\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/cap-xach-laptop-13-3-14-inch-mark-ryden-classic-4.png?v=1740731222867', 0, 0, 1, '2026-08-18 05:15:52', '2026-08-18 05:15:52'),
(31, 4, 11, 'Balo Vintpack For Macbook/ Laptop 13-14 inch TOMTOC (USA) TA1S1Y1', 'balo-vintpack-for-macbook-laptop-13-14-inch-tomtoc-usa-ta1s1y1', 'Vải polyester trượt nước cao cấp', '[\"https://bizweb.dktcdn.net/100/044/266/products/balo-chong-soc-laptop-13-14-15-16-tomtoc-usa-ta1m1d1-1-6b360a01-b405-457a-a2c5-6b909f03fc8c.jpg?v=1709278340150\", \"https://bizweb.dktcdn.net/100/044/266/products/balo-chong-soc-laptop-13-14-15-16-tomtoc-usa-ta1m1d1-14-49558253-2725-46f0-8da9-82870c215c38.jpg?v=1709278340150\", \"https://bizweb.dktcdn.net/100/044/266/products/balo-chong-soc-laptop-13-14-15-16-tomtoc-usa-ta1m1d1-2-a9bb9d65-931a-41bf-bde8-c797995597e7.png?v=1709278805653\", \"https://bizweb.dktcdn.net/100/044/266/products/balo-chong-soc-laptop-13-14-15-16-tomtoc-usa-ta1m1d1-5-330a353a-1773-4620-b5bf-e48e760b183d.jpg?v=1709278805653\"]', NULL, 'https://bizweb.dktcdn.net/100/044/266/products/balo-chong-soc-laptop-13-14-15-16-tomtoc-usa-ta1m1d1-1-6b360a01-b405-457a-a2c5-6b909f03fc8c.jpg?v=1709278340150', 0, 0, 1, '2026-08-18 05:19:51', '2026-08-18 08:57:59'),
(34, 5, 8, 'test', 'test', 'da', '[]', NULL, NULL, 0, 0, 1, '2026-08-18 08:59:53', '2026-08-18 08:59:53');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `color_id` bigint UNSIGNED NOT NULL,
  `size_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT '0.0',
  `stock` int NOT NULL DEFAULT '0',
  `import_quantity` int UNSIGNED NOT NULL DEFAULT '0',
  `import_price` decimal(12,0) DEFAULT NULL,
  `last_import_date` timestamp NULL DEFAULT NULL,
  `price` decimal(12,0) NOT NULL,
  `sale_price` decimal(12,0) DEFAULT NULL,
  `is_on_sale` tinyint(1) NOT NULL DEFAULT '0',
  `sale_type` enum('campaign','preorder') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sale_campaign_id` bigint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_variants_product_id_foreign` (`product_id`),
  KEY `product_variants_color_id_foreign` (`color_id`),
  KEY `product_variants_sale_campaign_id_foreign` (`sale_campaign_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `color_id`, `size_name`, `rating`, `stock`, `import_quantity`, `import_price`, `last_import_date`, `price`, `sale_price`, `is_on_sale`, `sale_type`, `created_at`, `updated_at`, `sale_campaign_id`) VALUES
(1, 1, 1, '38 x 28 x 10 cm', 4.8, 0, 11, 1005000, '2026-08-16 10:17:20', 14500000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:39:13', NULL),
(2, 1, 2, '38 x 28 x 10 cm', 4.7, 2, 12, 1005000, '2026-08-16 10:18:34', 14500000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:39:13', NULL),
(3, 2, 1, '38 x 28 x 10 cm', 4.9, 2498, 2500, 1590000, '2026-08-18 05:20:25', 2100000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:39:13', NULL),
(4, 3, 4, '38.1 x 27.1 x 3.2 cm', 4.5, 0, 120, 250000, '2026-08-18 09:23:39', 450000, 360000, 1, 'preorder', '2026-07-26 01:32:28', '2026-08-18 09:26:39', 4),
(5, 4, 1, '38 x 28 x 10 cm', 4.9, 399, 500, 1500000, '2026-08-18 05:20:49', 2350000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:39:13', NULL),
(6, 4, 2, '38 x 28 x 10 cm', 4.8, 250, 500, 1500000, '2026-08-18 05:20:49', 2350000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:39:13', NULL),
(7, 5, 1, '38 x 28 x 10 cm', 4.7, 600, 600, 950000, '2026-08-18 05:21:18', 1250000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:06:29', NULL),
(8, 6, 3, '38 x 28 x 10 cm', 4.8, 350, 400, 490000, '2026-08-18 05:22:23', 649000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:06:45', NULL),
(9, 6, 2, '38 x 28 x 10 cm', 4.7, 200, 400, 490000, '2026-08-18 05:22:23', 649000, 584100, 1, 'campaign', '2026-07-26 01:32:28', '2026-08-18 09:06:45', NULL),
(10, 7, 9, '38 x 28 x 10 cm', 4.6, 650, 700, 1150000, '2026-08-18 05:23:40', 1500000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:06:55', NULL),
(11, 7, 1, '38 x 28 x 10 cm', 4.5, 400, 500, 1150000, '2026-08-18 05:23:40', 1500000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:06:55', NULL),
(12, 8, 1, '38.1 x 27.1 x 3.2 cm', 4.4, 0, 70, 690000, '2026-08-18 09:24:38', 990000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:32:10', NULL),
(13, 9, 1, '38 x 28 x 10 cm', 4.9, 1500, 1500, 1400000, '2026-08-18 05:24:02', 1850000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:07:13', NULL),
(14, 10, 1, '38 x 28 x 10 cm', 4.3, 1000, 1000, 100000, '2026-08-18 05:25:47', 350000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:08:09', NULL),
(15, 10, 2, '38 x 28 x 10 cm', 4.2, 110, 120, 100000, '2026-08-18 05:26:59', 350000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:08:09', NULL),
(16, 11, 3, '38.1 x 27.1 x 3.2 cm', 4.8, 0, 50, 1900000, '2026-08-18 09:26:10', 2850000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:26:10', NULL),
(17, 11, 4, '38.1 x 27.1 x 3.2 cm', 4.7, 0, 50, 1900000, '2026-08-18 09:26:10', 2850000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:26:10', NULL),
(18, 12, 2, '38 x 28 x 10 cm', 4.5, 500, 500, 890000, '2026-08-18 05:27:42', 1190000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:08:24', NULL),
(19, 13, 1, '38 x 28 x 10 cm', 4.4, 900, 900, 450000, '2026-08-18 05:28:08', 650000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:08:34', NULL),
(20, 13, 2, '38 x 28 x 10 cm', 4.3, 850, 850, 450000, '2026-08-18 05:28:08', 650000, NULL, 0, NULL, '2026-07-26 01:32:28', '2026-08-18 09:08:34', NULL),
(21, 1, 2, '38 x 28 x 10 cm', 0.0, 3, 6, 1005000, '2026-08-18 09:05:50', 14500000, NULL, 0, NULL, '2026-08-03 09:26:29', '2026-08-18 09:05:50', NULL),
(22, 6, 1, '38 x 28 x 10 cm', 0.0, 300, 400, 490000, '2026-08-18 05:22:23', 649000, NULL, 0, NULL, '2026-08-07 05:16:14', '2026-08-18 09:06:45', NULL),
(23, 12, 3, '38 x 28 x 10 cm', 0.0, 500, 500, 890000, '2026-08-18 05:27:42', 1190000, NULL, 0, NULL, '2026-08-07 05:36:02', '2026-08-18 09:08:24', NULL),
(24, 12, 4, '38 x 28 x 10 cm', 0.0, 500, 500, 890000, '2026-08-18 05:27:42', 1190000, NULL, 0, NULL, '2026-08-07 05:36:02', '2026-08-18 09:08:24', NULL),
(25, 14, 1, '51 x 32 x 25 cm', 0.0, 500, 500, 1590000, '2026-08-15 05:08:54', 1790000, NULL, 0, NULL, '2026-08-15 05:08:54', '2026-08-15 05:08:54', NULL),
(26, 15, 5, '38 x 28 x 10 cm', 0.0, 500, 500, 550000, '2026-08-15 05:16:40', 700000, NULL, 0, NULL, '2026-08-15 05:16:40', '2026-08-15 05:16:40', NULL),
(27, 15, 2, '38 x 28 x 10 cm', 0.0, 500, 500, 550000, '2026-08-15 05:16:40', 700000, NULL, 0, NULL, '2026-08-15 05:16:40', '2026-08-15 05:16:40', NULL),
(28, 15, 10, '38 x 28 x 10 cm', 0.0, 500, 500, 550000, '2026-08-15 05:18:31', 700000, NULL, 0, NULL, '2026-08-15 05:18:31', '2026-08-15 05:18:31', NULL),
(29, 16, 1, '50 x 32 x 22 cm', 0.0, 500, 500, 1490000, '2026-08-15 05:21:23', 1790000, NULL, 0, NULL, '2026-08-15 05:21:23', '2026-08-15 05:21:23', NULL),
(30, 17, 1, '47 x 31 x 15 cm', 0.0, 500, 500, 1390000, '2026-08-15 05:25:48', 1690000, NULL, 0, NULL, '2026-08-15 05:25:48', '2026-08-15 05:25:48', NULL),
(31, 18, 1, '46 x 31 x 9 cm', 0.0, 500, 500, 1500000, '2026-08-15 05:29:49', 1800000, NULL, 0, NULL, '2026-08-15 05:29:49', '2026-08-15 05:29:49', NULL),
(32, 19, 1, '48 x 30 x 22 cm', 0.0, 500, 500, 550000, '2026-08-15 05:35:05', 850000, NULL, 0, NULL, '2026-08-15 05:35:05', '2026-08-15 05:35:05', NULL),
(33, 20, 1, '58 x 28 x 25 cm', 0.0, 500, 500, 1290000, '2026-08-15 05:43:45', 1590000, NULL, 0, NULL, '2026-08-15 05:43:45', '2026-08-15 05:43:45', NULL),
(34, 21, 1, '46.9 x 30.9 x 16.5 cm', 0.0, 500, 500, 4590000, '2026-08-15 05:47:14', 5000000, NULL, 0, NULL, '2026-08-15 05:47:14', '2026-08-15 05:47:14', NULL),
(36, 23, 1, '40 x 30 x 7 (cm)', 0.0, 500, 500, 690000, '2026-08-18 04:40:15', 1000000, NULL, 0, NULL, '2026-08-18 04:40:15', '2026-08-18 04:40:15', NULL),
(37, 24, 1, '38.5 x 28.5 x 5.5 cm', 0.0, 500, 500, 1390000, '2026-08-18 04:44:02', 2000000, NULL, 0, NULL, '2026-08-18 04:44:02', '2026-08-18 04:44:02', NULL),
(38, 25, 1, '49 x 34 x 11 cm', 0.0, 500, 500, 1890000, '2026-08-18 04:46:56', 2457000, NULL, 0, NULL, '2026-08-18 04:46:56', '2026-08-18 04:46:56', NULL),
(39, 26, 1, '33.78 x 24.99 x 2.79 cm', 0.0, 500, 500, 979000, '2026-08-18 05:02:02', 1500000, NULL, 0, NULL, '2026-08-18 05:02:02', '2026-08-18 05:02:02', NULL),
(40, 26, 2, '33.78 x 24.99 x 2.79 cm', 0.0, 500, 500, 979000, '2026-08-18 05:02:02', 1500000, NULL, 0, NULL, '2026-08-18 05:02:02', '2026-08-18 05:02:02', NULL),
(41, 27, 1, '30 x 23 x 6 cm', 0.0, 500, 500, 550000, '2026-08-18 05:06:27', 1000000, NULL, 0, NULL, '2026-08-18 05:06:27', '2026-08-18 11:56:40', NULL),
(42, 27, 2, '30 x 23 x 6 cm', 0.0, 500, 500, 550000, '2026-08-18 05:06:27', 1000000, NULL, 0, NULL, '2026-08-18 05:06:27', '2026-08-18 11:56:40', NULL),
(43, 28, 4, '38.1 x 27.1 x 3.2 cm', 0.0, 500, 500, 1270000, '2026-08-18 05:09:12', 1700000, NULL, 0, NULL, '2026-08-18 05:09:12', '2026-08-18 05:09:12', NULL),
(44, 29, 1, '51 x 32 x 25 cm', 0.0, 500, 500, 1250000, '2026-08-18 05:12:59', 1700000, NULL, 0, NULL, '2026-08-18 05:12:59', '2026-08-18 05:12:59', NULL),
(45, 30, 1, '40 x 30 x 7 (cm)', 0.0, 500, 500, 690000, '2026-08-18 05:15:53', 1000000, NULL, 0, NULL, '2026-08-18 05:15:53', '2026-08-18 05:15:53', NULL),
(46, 31, 7, '45 x 26 x 10 cm', 0.0, 500, 500, 1990000, '2026-08-18 05:19:51', 2600000, NULL, 0, NULL, '2026-08-18 05:19:51', '2026-08-18 09:01:56', NULL),
(47, 31, 1, '45 x 26 x 10 cm', 0.0, 500, 500, 1990000, '2026-08-18 05:19:51', 2600000, NULL, 0, NULL, '2026-08-18 05:19:51', '2026-08-18 05:19:51', NULL),
(50, 34, 7, 's', 0.0, 20, 25, 1000, '2026-08-18 08:59:53', 10000, NULL, 0, NULL, '2026-08-18 08:59:53', '2026-08-18 08:59:53', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quote_requests`
--

DROP TABLE IF EXISTS `quote_requests`;
CREATE TABLE IF NOT EXISTS `quote_requests` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `company_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_quantity` int NOT NULL,
  `total` decimal(12,0) DEFAULT NULL,
  `requirement` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `logo_file` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quote_requests_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `quote_requests`
--

INSERT INTO `quote_requests` (`id`, `user_id`, `company_name`, `email`, `phone`, `total_quantity`, `total`, `requirement`, `logo_file`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'Công ty TNHH Giải Pháp Xanh', 'contact@green.com', '0912345678', 150, NULL, 'Cần in logo công ty lên mặt trước balo, size 8x8cm', NULL, 'pending', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(3, 5, 'abc', 'abc@gmail.com', '1234567890', 50, 72500000, '{\"address\":\"124\",\"ward\":\"Phường Bến Thành\"}', NULL, 'pending', '2026-08-04 09:42:18', '2026-08-04 09:42:18'),
(4, 5, 'abc', 'abc@gmail', '123', 50, 72500000, '{\"address\":\"123\",\"city\":\"TP. Hồ Chí Minh\",\"district\":\"Quận 1\",\"ward\":\"Phường Bến Nghé\",\"delivery_date\":\"2026-08-12\"}', NULL, 'pending', '2026-08-04 12:19:31', '2026-08-04 12:19:31'),
(5, 5, 'abc', 'abc@gmail', '123', 50, 72500000, '{\"address\":\"123\",\"city\":\"TP. Hồ Chí Minh\",\"district\":\"Quận 1\",\"ward\":\"Phường Bến Nghé\",\"delivery_date\":\"2026-08-12\"}', NULL, 'pending', '2026-08-04 12:46:52', '2026-08-04 12:46:52'),
(6, 5, 'a', 'a@gmail.com', '44444', 50, 72500000, '{\"address\":\"aaaa\",\"city\":\"Hà Nội\",\"district\":\"Phú Nhuận\",\"ward\":\"Phường Bến Nghé\",\"tax_code\":\"111111\",\"delivery_date\":\"2026-08-12\"}', NULL, 'pending', '2026-08-04 14:39:33', '2026-08-04 14:39:33'),
(7, 5, 'aaaaa', 'thanhphuongkhuu@gmail.com', '111111111', 50, 72500000, '{\"address\":\"aaaaa\",\"city\":\"Đồng Nai\",\"district\":\"Tân Bình\",\"ward\":\"Phường Bến Nghé\",\"delivery_date\":\"2026-09-03\"}', NULL, 'pending', '2026-08-04 14:44:13', '2026-08-04 14:44:13'),
(8, 5, 'abcd', 'abcd@gmail.com', '1234567890', 50, 72500000, '{\"address\":\"123\",\"city\":\"TP. Hồ Chí Minh\",\"district\":\"Quận 10\",\"ward\":\"Phường Cầu Kho\",\"delivery_date\":\"2026-08-28\"}', NULL, 'pending', '2026-08-05 07:45:14', '2026-08-05 07:45:14'),
(9, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', 'thanhphuongkhuu@gmail.com', '0123456789', 50, 36250000, '{\"address\":\"123\",\"city\":\"Bình Dương\",\"district\":\"Quận 1\",\"ward\":\"Phường Bến Nghé\",\"tax_code\":\"0319669204\",\"delivery_date\":\"2026-08-26\"}', NULL, 'pending', '2026-08-10 06:25:50', '2026-08-10 06:25:50'),
(10, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', 'ryukianami@gmail.com', '0123456789', 50, 36250000, '{\"address\":\"123\",\"city\":\"TP. Hồ Chí Minh\",\"district\":\"Quận 3\",\"ward\":\"Phường Bến Nghé\",\"tax_code\":\"0319669204\",\"delivery_date\":\"2026-09-02\"}', NULL, 'pending', '2026-08-10 06:30:09', '2026-08-10 06:30:09'),
(11, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', 'ryukianami@gmail.com', '0123456789', 50, 36250000, '{\"address\":\"123\",\"city\":\"Đà Nẵng\",\"district\":\"Quận 3\",\"ward\":\"Phường Bến Thành\",\"tax_code\":\"0319669204\",\"delivery_date\":\"2026-08-13\"}', NULL, 'pending', '2026-08-10 06:54:34', '2026-08-10 06:54:34'),
(12, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', 'ryukianami@gmail.com', '0123456789', 50, 36250000, '{\"address\":\"123\",\"city\":\"Bình Dương\",\"district\":\"Quận 2\",\"ward\":\"Phường Bến Nghé\",\"tax_code\":\"0319669204\",\"delivery_date\":\"2026-08-25\"}', NULL, 'pending', '2026-08-10 07:53:10', '2026-08-10 07:53:10'),
(13, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', 'thanhphuongkhuu@gmail.com', '0123456789', 50, 36250000, '{\"address\":\"123\",\"city\":\"Hà Nội\",\"district\":\"Quận 2\",\"ward\":\"Phường Bến Thành\",\"tax_code\":\"0319669204\",\"delivery_date\":\"2026-08-21\"}', NULL, 'pending', '2026-08-10 07:57:11', '2026-08-10 07:57:11'),
(14, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', 'ryukianami@gmail.com', '0123456789', 50, 36250000, '{\"address\":\"123\",\"city\":\"TP. Hồ Chí Minh\",\"district\":\"Quận 3\",\"ward\":\"Phường Bến Nghé\",\"tax_code\":\"0319669204\",\"delivery_date\":\"2026-08-15\"}', NULL, 'pending', '2026-08-10 07:58:32', '2026-08-10 07:58:32'),
(15, 5, 'Văn phòng luật sư Ngọc Tuấn Legal', 'ryukianami@gmail.com', '0123456789', 50, 52500000, '{\"address\":\"1234\",\"city\":\"TP. Hồ Chí Minh\",\"district\":\"Quận 1\",\"ward\":\"Phường Bến Nghé\",\"tax_code\":\"0319669204\",\"delivery_date\":\"2026-08-25\"}', NULL, 'pending', '2026-08-10 08:03:28', '2026-08-10 08:03:28'),
(16, 5, 'DOANH NGHIỆP TƯ NHÂN LOAN PHÁT', 'thanhphuongkhuu@gmail.com', '0123456789', 50, 36250000, '{\"address\":\"13\",\"city\":\"Hà Nội\",\"district\":\"Quận 1\",\"ward\":\"Phường Bến Nghé\",\"tax_code\":\"1700494236\",\"delivery_date\":\"2026-08-26\"}', NULL, 'pending', '2026-08-16 09:43:27', '2026-08-16 09:43:27'),
(17, 5, 'DOANH NGHIỆP TƯ NHÂN LOAN PHÁT', 'thanhphuongkhuu@gmail.com', '0123456789', 50, 36250000, '{\"address\":\"12\",\"city\":\"TP. Hồ Chí Minh\",\"district\":\"Quận 3\",\"ward\":\"Phường Bến Nghé\",\"tax_code\":\"1700494236\",\"delivery_date\":\"2026-08-24\"}', NULL, 'pending', '2026-08-16 10:05:50', '2026-08-16 10:05:50'),
(18, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', 'thanhphuongkhuu@gmail.com', '0123456789', 50, 52500000, '{\"address\":\"123\",\"city\":\"TP. Hồ Chí Minh\",\"district\":\"Quận 1\",\"ward\":\"Phường Bến Nghé\",\"tax_code\":\"0111606029\",\"delivery_date\":\"2026-08-20\"}', NULL, 'pending', '2026-08-17 03:07:12', '2026-08-17 03:07:12'),
(19, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', 'thanhphuongkhuu@gmail.com', '0123456789', 50, 58750000, '{\"address\":\"123\",\"city\":\"TP. Hồ Chí Minh\",\"district\":\"Quận 7\",\"ward\":\"Khác\",\"tax_code\":\"0111606029\",\"delivery_date\":\"2026-08-26\"}', NULL, 'pending', '2026-08-17 07:17:13', '2026-08-17 07:17:13'),
(20, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', 'thanhphuongkhuu@gmail.com', '0123456789', 50, 52500000, '{\"address\":\"123\",\"tax_code\":\"0111606029\",\"delivery_date\":\"2026-08-23\"}', NULL, 'pending', '2026-08-17 07:18:40', '2026-08-17 07:18:40'),
(21, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', 'thanhphuongkhuu@gmail.com', '0123456780', 50, 52500000, '{\"address\":\"123\",\"city\":\"Đà Nẵng\",\"district\":\"Quận 1\",\"ward\":\"Phường Bến Nghé\",\"tax_code\":\"0111606029\",\"delivery_date\":\"2026-08-26\"}', NULL, 'pending', '2026-08-17 07:23:43', '2026-08-17 07:23:43'),
(22, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', 'thanhphuongkhuu@gmail.com', '0123456789', 50, 52500000, '{\"address\":\"123\",\"city\":\"Hà Nội\",\"district\":\"Quận 2\",\"ward\":\"Phường Bến Nghé\",\"tax_code\":\"0111606029\",\"delivery_date\":\"2026-08-26\"}', NULL, 'pending', '2026-08-17 07:25:34', '2026-08-17 07:25:34'),
(23, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', 'thanhphuongkhuu@gmail.com', '0123456789', 50, 36250000, '{\"address\":\"123\",\"city\":\"Hà Nội\",\"district\":\"Quận 2\",\"ward\":\"Phường Bến Nghé\",\"tax_code\":\"0111606029\",\"delivery_date\":\"2026-08-20\"}', NULL, 'pending', '2026-08-17 07:28:46', '2026-08-17 07:28:46'),
(24, 5, 'Trường Tiểu học và Trung học cơ sở Vân Hà', 'thanhphuongkhuu@gmail.com', '0123456789', 50, 36250000, '{\"address\":\"123\",\"city\":\"TP. Hồ Chí Minh\",\"district\":\"Quận 2\",\"ward\":\"Phường Bến Thành\",\"tax_code\":\"0111606029\",\"delivery_date\":\"2026-08-24\"}', NULL, 'pending', '2026-08-17 07:39:12', '2026-08-17 07:39:12'),
(25, 5, 'CÔNG TY TNHH BLOSSOM CREATIVE SAIGON', 'thanhphuongkhuu@gmail.com', '0123456789', 50, 36250000, '{\"address\":\"123\",\"city\":\"TP. Hồ Chí Minh\",\"district\":\"Quận 2\",\"ward\":\"Phường Bến Thành\",\"tax_code\":\"0319677692\",\"delivery_date\":\"2026-08-29\"}', NULL, 'pending', '2026-08-17 08:44:52', '2026-08-17 08:44:52'),
(26, 5, 'CÔNG TY TNHH AGRI 1986', 'thanhphuongkhuu@gmail.com', '0123456789', 50, 52500000, '{\"address\":\"1\",\"city\":\"A\",\"district\":\"1\",\"ward\":\"1\",\"tax_code\":\"3801358804\",\"delivery_date\":\"2026-08-31\"}', NULL, 'pending', '2026-08-17 11:43:42', '2026-08-17 11:43:42'),
(27, 6, 'CÔNG TY CỔ PHẦN SỮA VIỆT NAM', 'a@gmail.com', '0906758927', 50, 30827500, '{\"address\":\"1\",\"city\":\"HCM\",\"district\":\"1\",\"ward\":\"1\",\"tax_code\":\"0300588569\",\"delivery_date\":\"2026-09-03\"}', NULL, 'pending', '2026-08-18 09:36:34', '2026-08-18 09:36:34'),
(28, 6, 'CÔNG TY CỔ PHẦN SỮA VIỆT NAM', 'trucmy10042004@gmail.com', '0907864587', 50, 123500000, '{\"address\":\"1\",\"city\":\"1\",\"district\":\"1\",\"ward\":\"1\",\"tax_code\":\"0300588569\",\"delivery_date\":\"2026-09-10\"}', NULL, 'pending', '2026-08-18 09:47:03', '2026-08-18 09:47:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quote_request_details`
--

DROP TABLE IF EXISTS `quote_request_details`;
CREATE TABLE IF NOT EXISTS `quote_request_details` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `quote_request_id` bigint UNSIGNED NOT NULL,
  `product_variant_id` bigint UNSIGNED NOT NULL,
  `quantity` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quote_request_details_quote_request_id_foreign` (`quote_request_id`),
  KEY `quote_request_details_product_variant_id_foreign` (`product_variant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `quote_request_details`
--

INSERT INTO `quote_request_details` (`id`, `quote_request_id`, `product_variant_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 46, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(2, 1, 2, 45, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(3, 1, 3, 46, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(4, 1, 4, 44, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(5, 1, 5, 26, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(6, 1, 6, 49, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(7, 1, 7, 19, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(8, 1, 8, 49, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(9, 1, 9, 12, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(10, 1, 10, 50, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(11, 1, 11, 40, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(12, 1, 12, 25, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(13, 1, 13, 41, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(14, 1, 14, 24, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(15, 1, 15, 45, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(16, 1, 16, 46, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(17, 1, 17, 44, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(18, 1, 18, 23, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(19, 1, 19, 30, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(20, 1, 20, 49, '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(22, 3, 1, 50, '2026-08-04 09:42:18', '2026-08-04 09:42:18'),
(23, 4, 1, 50, '2026-08-04 12:19:31', '2026-08-04 12:19:31'),
(24, 5, 1, 50, '2026-08-04 12:46:52', '2026-08-04 12:46:52'),
(25, 6, 1, 50, '2026-08-04 14:39:33', '2026-08-04 14:39:33'),
(26, 7, 1, 50, '2026-08-04 14:44:13', '2026-08-04 14:44:13'),
(27, 8, 1, 50, '2026-08-05 07:45:14', '2026-08-05 07:45:14'),
(28, 9, 1, 50, '2026-08-10 06:25:50', '2026-08-10 06:25:50'),
(29, 10, 1, 50, '2026-08-10 06:30:09', '2026-08-10 06:30:09'),
(30, 11, 1, 50, '2026-08-10 06:54:34', '2026-08-10 06:54:34'),
(31, 12, 1, 50, '2026-08-10 07:53:10', '2026-08-10 07:53:10'),
(32, 13, 1, 50, '2026-08-10 07:57:15', '2026-08-10 07:57:15'),
(33, 14, 1, 50, '2026-08-10 07:58:32', '2026-08-10 07:58:32'),
(34, 15, 3, 50, '2026-08-10 08:03:28', '2026-08-10 08:03:28'),
(35, 16, 1, 50, '2026-08-16 09:43:27', '2026-08-16 09:43:27'),
(36, 17, 1, 50, '2026-08-16 10:05:50', '2026-08-16 10:05:50'),
(37, 18, 3, 50, '2026-08-17 03:07:12', '2026-08-17 03:07:12'),
(38, 19, 5, 50, '2026-08-17 07:17:13', '2026-08-17 07:17:13'),
(39, 20, 3, 50, '2026-08-17 07:18:40', '2026-08-17 07:18:40'),
(40, 21, 3, 50, '2026-08-17 07:23:43', '2026-08-17 07:23:43'),
(41, 22, 3, 50, '2026-08-17 07:25:34', '2026-08-17 07:25:34'),
(42, 23, 1, 50, '2026-08-17 07:28:46', '2026-08-17 07:28:46'),
(43, 24, 1, 50, '2026-08-17 07:39:12', '2026-08-17 07:39:12'),
(44, 25, 1, 50, '2026-08-17 08:44:52', '2026-08-17 08:44:52'),
(45, 26, 3, 50, '2026-08-17 11:43:42', '2026-08-17 11:43:42'),
(46, 27, 8, 50, '2026-08-18 09:36:34', '2026-08-18 09:36:34'),
(47, 28, 46, 50, '2026-08-18 09:47:03', '2026-08-18 09:47:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `product_variant_id` bigint UNSIGNED NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  KEY `reviews_product_variant_id_foreign` (`product_variant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_variant_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 3.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(2, 2, 2, 4.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(3, 3, 3, 5.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(4, 2, 4, 5.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(5, 2, 5, 4.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(6, 3, 6, 3.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(7, 2, 7, 4.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(8, 3, 8, 3.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(9, 3, 9, 4.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(10, 2, 10, 4.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(11, 3, 11, 3.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(12, 2, 12, 4.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(13, 3, 13, 4.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(14, 2, 14, 4.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(15, 3, 15, 3.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(16, 3, 16, 3.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(17, 2, 17, 3.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(18, 2, 18, 3.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(19, 3, 19, 5.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41'),
(20, 2, 20, 3.0, 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.', '2026-07-26 01:32:41', '2026-07-26 01:32:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('Zx76CpmKZ7fxH01wzF2G4qQ6tgHQdoF5xiteeBXf', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ6TW9TUXJCa2pxM2w0MXJDUTYwdmtpakVBMG91N1JMbG9jZnQ0dVc4IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC90aGFuaC10b2FuP2NhcnQ9JTdCJTIyMzMlMjIlM0ElN0IlMjJxdWFudGl0eSUyMiUzQTElMkMlMjJwcmljZSUyMiUzQSUyMjE1OTAwMDAyMzg1MDAlMjIlMkMlMjJtZXRhJTIyJTNBJTdCJTIybG9nbyUyMiUzQSU3QiUyMnBvc2l0aW9uJTIyJTNBJTIyZnJvbnQlMjIlMkMlMjJzaXplJTIyJTNBJTIybWVkaXVtJTIyJTJDJTIybm90ZSUyMiUzQSUyMiUyMiUyQyUyMmZpbGUlMjIlM0ElMjJsb2dvcyUyRmUxMjQzNTBkLTU0NDQtNDJiNi05YTQxLTI3MDJhODc4ODUzOS5wbmclMjIlMkMlMjJmdWxsTmFtZSUyMiUzQSUyMlB5JTIyJTJDJTIyZW1haWwlMjIlM0ElMjJ0cnVjbXkxMDA0MjAwNCU0MGdtYWlsLmNvbSUyMiUyQyUyMnBob25lJTIyJTNBJTIyMDkwNzg2ODk1NiUyMiU3RCU3RCU3RCU3RCZlbWFpbD10cnVjbXkxMDA0MjAwNCU0MGdtYWlsLmNvbSZuYW1lPVB5Jm9yZGVyX3R5cGU9Y3VzdG9taXplJnBob25lPTA5MDc4Njg5NTYiLCJyb3V0ZSI6ImNoZWNrb3V0In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjZ9', 1787055089);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `group` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `group`, `created_at`, `updated_at`) VALUES
(1, 'store_name', 'BigBag.vn', 'general', NULL, '2026-08-17 09:04:36'),
(2, 'store_email', 'contact@bigbag.vn', 'general', NULL, '2026-08-17 09:04:37'),
(3, 'store_phone', '1900 1234', 'general', NULL, '2026-08-17 09:04:37'),
(4, 'store_address', '123 Đường ABC, Quận 1, TP.HCM', 'general', NULL, '2026-08-17 09:04:37'),
(5, 'tax_code', '', 'general', NULL, '2026-08-17 09:04:37'),
(6, 'b2b_email', 'b2b@bigbag.vn', 'general', NULL, '2026-08-17 09:04:37'),
(7, 'preorder_deposit', '100', 'general', NULL, '2026-08-17 09:04:37'),
(8, 'preorder_lead_time', '15', 'general', NULL, '2026-08-17 09:04:37'),
(9, 'payment_cod', 'true', 'general', NULL, '2026-08-17 09:04:37'),
(10, 'payment_bank', 'true', 'general', NULL, '2026-08-17 09:04:37'),
(11, 'payment_momo', 'false', 'general', NULL, '2026-08-17 09:04:37'),
(12, 'payment_vnpay', 'false', 'general', NULL, '2026-08-17 09:04:37'),
(13, 'seo_title', 'BigBag.vn - Balo và Túi xách cao cấp', 'general', NULL, '2026-08-17 09:04:37'),
(14, 'seo_description', 'BigBag.vn chuyên cung cấp balo, túi xách cao cấp', 'general', NULL, '2026-08-17 09:04:37'),
(15, 'seo_keywords', 'balo, túi xách, phụ kiện', 'general', NULL, '2026-08-17 09:04:37');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `status` tinyint NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `phone`, `avatar`, `role`, `status`, `deleted_at`) VALUES
(1, 'Admin User', 'admin@bigbag.vn', NULL, '$2y$12$s/8XvUfXeI4LuO.a2MzF7OZMNE5dskiRB.rUmXQbBdL0o4jC0Mmji', NULL, '2026-07-26 01:32:29', '2026-08-18 09:28:47', '0987654321', NULL, 'admin', 1, '2026-08-18 09:28:47'),
(2, 'Customer One', 'customer1@example.com', NULL, '$2y$12$8QvVjv1Vk.W3uxPKL0kqW.6wMQXxQSHo16lGKZ.iwmarN29CbsDR2', NULL, '2026-07-26 01:32:29', '2026-07-26 01:32:29', '0912345678', NULL, 'user', 1, NULL),
(3, 'Customer Two', 'customer2@example.com', NULL, '$2y$12$kIrW90Mp.PZ9PUI8rwuAReAz6e0ehGOPCGAlwLF8hnc1kGK66coYO', NULL, '2026-07-26 01:32:29', '2026-07-26 01:32:29', '0923456789', NULL, 'user', 1, NULL),
(4, 'My', 'admin@gmail.com', NULL, '$2y$12$nQTKXYOBjh0lDo2buTfwwO.UmFfiddtatQKS4aHIeHsLW5zefrhPC', NULL, '2026-07-26 01:32:29', '2026-07-26 01:32:29', '0923456785', NULL, 'admin', 1, NULL),
(5, 'Thanh Phuong Khuu', 'thanhphuongkhuu@gmail.com', NULL, '$2y$12$oJIVfWmwWBA2JuoIyPizYeZfpLQkb3t9gDkPA/x4GQFsCBTyBo2D.', NULL, '2026-07-26 01:32:29', '2026-07-26 01:32:29', '0123456789', NULL, 'admin', 1, NULL),
(6, 'Py', 'trucmy10042004@gmail.com', NULL, '$2y$12$I8zx3bBZp97Yn3hHHEOQyeXUjGXr2XdQCQM0zrSkC32zHCtPMbl2K', NULL, '2026-08-18 09:28:11', '2026-08-18 09:28:59', '0907868956', NULL, 'admin', 1, NULL);

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `banners`
--
ALTER TABLE `banners`
  ADD CONSTRAINT `banners_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_banner_id_foreign` FOREIGN KEY (`banner_id`) REFERENCES `banners` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `campaigns_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `campaign_configs`
--
ALTER TABLE `campaign_configs`
  ADD CONSTRAINT `campaign_configs_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `campaign_products`
--
ALTER TABLE `campaign_products`
  ADD CONSTRAINT `campaign_products_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `campaign_products_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chatbot_messages`
--
ALTER TABLE `chatbot_messages`
  ADD CONSTRAINT `chatbot_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `logo_print_requests`
--
ALTER TABLE `logo_print_requests`
  ADD CONSTRAINT `logo_print_requests_order_detail_id_foreign` FOREIGN KEY (`order_detail_id`) REFERENCES `order_details` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_banner_id_foreign` FOREIGN KEY (`banner_id`) REFERENCES `banners` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `news_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE RESTRICT;

--
-- Các ràng buộc cho bảng `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT;

--
-- Các ràng buộc cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_color_id_foreign` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variants_sale_campaign_id_foreign` FOREIGN KEY (`sale_campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `quote_requests`
--
ALTER TABLE `quote_requests`
  ADD CONSTRAINT `quote_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `quote_request_details`
--
ALTER TABLE `quote_request_details`
  ADD CONSTRAINT `quote_request_details_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `quote_request_details_quote_request_id_foreign` FOREIGN KEY (`quote_request_id`) REFERENCES `quote_requests` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
