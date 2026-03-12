-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 10, 2026 at 01:59 PM
-- Server version: 8.0.44
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cardgame`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `actor_user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(64) NOT NULL,
  `target_type` varchar(32) NOT NULL,
  `target_id` bigint UNSIGNED DEFAULT NULL,
  `metadata_json` json DEFAULT NULL,
  `ip_address` varbinary(16) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `actor_user_id`, `action`, `target_type`, `target_id`, `metadata_json`, `ip_address`, `created_at`) VALUES
(1, NULL, 'GUEST_CREATE', 'user', 2, '{\"username\": \"guest_C909F35C\"}', 0x00000000000000000000000000000001, '2026-03-01 17:18:32'),
(2, NULL, 'USER_REGISTER', 'user', 3, '{\"email\": \"crispino.zyrus@gmail.com\", \"username\": \"ZacZac460\"}', 0x00000000000000000000000000000001, '2026-03-01 17:28:29'),
(3, 1, 'USER_APPROVE', 'user', 3, '{\"note\": \"approved\"}', NULL, '2026-03-01 17:28:37'),
(4, NULL, 'GUEST_CREATE', 'user', 4, '{\"username\": \"guest_A7DB7E4A\"}', 0x00000000000000000000000000000001, '2026-03-10 14:32:27'),
(5, NULL, 'GUEST_CREATE', 'user', 5, '{\"username\": \"guest_62B9B424\"}', 0x00000000000000000000000000000001, '2026-03-10 14:37:44'),
(6, NULL, 'GUEST_CREATE', 'user', 6, '{\"username\": \"guest_E38E26A4\"}', 0x00000000000000000000000000000001, '2026-03-10 14:38:20'),
(7, NULL, 'GUEST_CREATE', 'user', 7, '{\"username\": \"guest_BE755BAE\"}', 0x00000000000000000000000000000001, '2026-03-10 14:39:41'),
(8, NULL, 'GUEST_CREATE', 'user', 8, '{\"username\": \"guest_730939E2\"}', 0x00000000000000000000000000000001, '2026-03-10 14:40:25'),
(9, NULL, 'GUEST_CREATE', 'user', 9, '{\"username\": \"guest_1A8F9B60\"}', 0x00000000000000000000000000000001, '2026-03-10 15:00:29');

-- --------------------------------------------------------

--
-- Table structure for table `auth_sessions`
--

CREATE TABLE `auth_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `refresh_token_hash` char(64) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip_address` varbinary(16) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auth_sessions`
--

INSERT INTO `auth_sessions` (`id`, `user_id`, `refresh_token_hash`, `user_agent`, `ip_address`, `created_at`, `last_seen_at`, `expires_at`, `revoked_at`) VALUES
(1, 1, '8e4c716040a8639b98c9f7b546fcf5aed60a6f80000a1966e95ec8643f489b5d', 'mysql-cli-test', 0x7f000001, '2026-03-01 17:00:36', '2026-03-01 17:00:36', '2026-03-15 17:00:36', NULL),
(2, 3, '26198f6d3923022c9cf8cb9cb94a559ac0064b5369482775ce9e751bea917fee', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-01 17:28:52', '2026-03-01 17:28:52', '2026-03-15 17:28:52', NULL),
(3, 3, '8563896790eb164dd2a3c58957bf1457773fa6ee0c9050a651b097317cc6dad7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-01 17:29:47', '2026-03-01 17:29:47', '2026-03-15 17:29:47', NULL),
(4, 3, 'f0ecea7fdb6923623057f9303acd2c818d2d11a49824a3b0724a9a1d211acb7f', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-01 17:44:10', '2026-03-01 17:44:10', '2026-03-15 17:44:10', '2026-03-01 17:54:53'),
(5, 1, 'a8e581855528af7a33239a1b14b063193fc3324572f3fcf86b566f97ea6bb326', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-03 19:18:49', '2026-03-03 19:18:49', '2026-03-17 19:18:49', '2026-03-03 19:19:50'),
(6, 1, '97731fea97bc8bebf18b2665a716e210b6d798008d6d7adb6371fd07ae8f3ada', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-03 19:19:52', '2026-03-03 19:19:52', '2026-03-17 19:19:52', '2026-03-03 19:20:00'),
(7, 3, '153bbd80065e0f1f150d04a36cbb570d785bee6928d96f8fbcb02c3ade352bbe', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-03 19:20:19', '2026-03-03 19:20:19', '2026-03-17 19:20:19', '2026-03-03 19:21:09'),
(8, 1, '62cefe75e76af43352b0e38d071e76f56c860fe3353cf19cef740aac10ea8346', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-05 18:52:17', '2026-03-05 18:52:17', '2026-03-19 18:52:17', NULL),
(9, 3, 'de386cddfab72b62675f38a529c7cf4e4ff51d664c66536fb81ec708a50bc579', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-05 18:54:23', '2026-03-05 18:54:23', '2026-03-19 18:54:23', '2026-03-05 19:02:36'),
(10, 3, '97daa7fea064e6424f75715e33a1dede3cd7e781d6a095f3a4f131313da8a7a3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-05 19:11:42', '2026-03-05 19:11:42', '2026-03-19 19:11:42', NULL),
(11, 3, '871ca5058bc689ca1bff0eae0d4c6b39482ad93f74fdb35de02b6e6b2263c888', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-05 21:06:59', '2026-03-05 21:06:59', '2026-03-19 21:06:59', '2026-03-05 21:08:21'),
(12, 3, 'dd6b583d8bd36cbe55438f882b60f4327c825d74d22d72d55588191378c19526', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-05 21:08:23', '2026-03-05 21:08:23', '2026-03-19 21:08:23', NULL),
(13, 3, '8726c11e649249dd3ed175cbe8538b0b86c0a92325f334768e46f91b9dd73b2d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-06 13:52:51', '2026-03-06 13:52:51', '2026-03-20 13:52:51', '2026-03-06 14:22:41'),
(14, 3, 'd02c5274e884de276d8fd4bebd4b29af5b54818b61af9e52d142204d7dc74267', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-06 15:35:30', '2026-03-06 15:35:30', '2026-03-20 15:35:30', '2026-03-06 15:45:13'),
(15, 3, '47f30a58cfab85c4dcacb938b036d6708ae86cc523541833cfbbc5df0f38c5d3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-06 15:45:25', '2026-03-06 15:45:25', '2026-03-20 15:45:25', '2026-03-06 15:45:47'),
(16, 3, 'fafbafbc38e2420bd15f1e44fbe0efbe3eaee5f1c078343234de4789e5a43ab8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 12:19:50', '2026-03-10 12:19:50', '2026-03-24 12:19:50', NULL),
(17, 3, '515a2717601b9318abe68d0acd8ab47872994abb11f8b5c727e8a4b7668239f1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 13:48:46', '2026-03-10 13:48:46', '2026-03-24 13:48:46', '2026-03-10 14:10:44'),
(18, 3, '9f73bbda58fd86eca8f3e3e9f9cdd3b7edf62f4c8ce88946fb9497299fdc07a1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 14:15:20', '2026-03-10 14:15:20', '2026-03-24 14:15:20', '2026-03-10 14:29:21'),
(19, 3, 'de0f691f89e86a9f04d10c4a586f041229d553ef668be778a20f66093f42e1e2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 19:03:57', '2026-03-10 19:03:57', '2026-03-24 19:03:57', '2026-03-10 19:55:25'),
(20, 3, '5bd7c8d68d112f0718d991e5a8bd93f991f9de916612d5fa9bcfb7f130c7dcee', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 19:55:28', '2026-03-10 19:55:28', '2026-03-24 19:55:28', '2026-03-10 20:45:22'),
(21, 3, '23986ad43af5d28a1cd04435763366fdaf9b33b8834cf4c29c891959c90e714b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 20:45:24', '2026-03-10 20:45:24', '2026-03-24 20:45:24', '2026-03-10 20:45:35'),
(22, 3, '0d95ba557b21c043bbfe36ba08abcc70968e712a0594e638f27d7f5b0d102810', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 20:45:38', '2026-03-10 20:45:38', '2026-03-24 20:45:38', '2026-03-10 20:53:21'),
(23, 3, 'c61834a23af8cb3631da240dfcc81875acd0831a35c860d058724ed883141847', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 20:53:24', '2026-03-10 20:53:24', '2026-03-24 20:53:24', '2026-03-10 20:53:39'),
(24, 3, '8dd863537cff6a83233613abff7d0eabbfdc46ebc95e6fbb0097502428d62ddc', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 20:53:42', '2026-03-10 20:53:42', '2026-03-24 20:53:42', '2026-03-10 20:53:51'),
(25, 3, 'ba05daee63f582dc2133f0af1e19065a8f004cb4f98bae6d94178da9a85ec356', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 20:53:54', '2026-03-10 20:53:54', '2026-03-24 20:53:54', '2026-03-10 20:54:01'),
(26, 3, 'd4548acae75542ec1d8d868f6512613aa8794c3aca582dbdd68ce21b22cbf289', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 20:54:55', '2026-03-10 20:54:55', '2026-03-24 20:54:55', '2026-03-10 20:56:27'),
(27, 3, 'cd98018920d6d298ec1282c5ab6a77d477837f9c2b50944696b70c49cb3f8c65', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 20:56:30', '2026-03-10 20:56:30', '2026-03-24 20:56:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_notifications`
--

CREATE TABLE `dashboard_notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(32) NOT NULL,
  `title` varchar(120) NOT NULL,
  `body` varchar(500) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dashboard_notifications`
--

INSERT INTO `dashboard_notifications` (`id`, `user_id`, `type`, `title`, `body`, `link_url`, `is_read`, `created_at`, `read_at`) VALUES
(1, 1, 'admin_approval', 'New account pending approval', 'User: ZacZac460 (crispino.zyrus@gmail.com)', '/admin/pending-users.php?user_id=3', 0, '2026-03-01 17:28:29', NULL),
(2, 3, 'system', 'Account approved', 'You can now log in and play.', '/index.php', 0, '2026-03-01 17:28:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(20) NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `identifier` varchar(255) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varbinary(16) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `failure_reason` varchar(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `user_id`, `identifier`, `success`, `ip_address`, `user_agent`, `failure_reason`, `created_at`) VALUES
(1, 1, 'admin', 1, 0x7f000001, 'mysql-cli-test', NULL, '2026-03-01 17:00:43'),
(2, NULL, 'admin', 0, 0x7f000001, 'mysql-cli-test', 'wrong_password', '2026-03-01 17:00:50'),
(3, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-01 17:28:52'),
(4, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-01 17:29:47'),
(5, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-01 17:44:10'),
(6, 1, 'admin@game.local', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-03 19:18:49'),
(7, 1, 'admin@game.local', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-03 19:19:52'),
(8, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-03 19:20:19'),
(9, NULL, 'user@cardgame.local', 0, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 'wrong_credentials', '2026-03-05 18:51:44'),
(10, 1, 'admin@game.local', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-05 18:52:17'),
(11, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-05 18:54:23'),
(12, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-05 19:11:42'),
(13, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-05 21:06:59'),
(14, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-05 21:08:23'),
(15, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-06 13:52:51'),
(16, NULL, 'user1@propertyhub.local', 0, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 'wrong_credentials', '2026-03-06 15:35:23'),
(17, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-06 15:35:30'),
(18, NULL, 'user1@propertyhub.local', 0, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 'wrong_credentials', '2026-03-06 15:45:19'),
(19, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-06 15:45:25'),
(20, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-10 12:19:50'),
(21, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-10 13:48:46'),
(22, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-10 14:15:20'),
(23, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-10 19:03:57'),
(24, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-10 19:55:28'),
(25, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-10 20:45:24'),
(26, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-10 20:45:38'),
(27, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-10 20:53:24'),
(28, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-10 20:53:42'),
(29, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-10 20:53:54'),
(30, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-10 20:54:55'),
(31, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-10 20:56:30');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` smallint UNSIGNED NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Full access'),
(2, 'moderator', 'Manage reports/penalties'),
(3, 'player', 'Normal player');

-- --------------------------------------------------------

--
-- Table structure for table `two_factor_secrets`
--

CREATE TABLE `two_factor_secrets` (
  `user_id` bigint UNSIGNED NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `method` enum('totp') NOT NULL DEFAULT 'totp',
  `secret_enc` varbinary(255) DEFAULT NULL,
  `enabled_at` datetime DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `display_name` varchar(64) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejected_reason` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_guest` tinyint(1) NOT NULL DEFAULT '0',
  `banned_until` datetime DEFAULT NULL,
  `bank_link_status` enum('none','pending','linked','failed') NOT NULL DEFAULT 'none',
  `bank_linked_at` datetime DEFAULT NULL,
  `appearance_mode` enum('default','dark','light') NOT NULL DEFAULT 'default',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `display_name`, `email_verified_at`, `approval_status`, `approved_by`, `approved_at`, `rejected_reason`, `is_active`, `is_guest`, `banned_until`, `bank_link_status`, `bank_linked_at`, `appearance_mode`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@game.local', '$2y$10$axxzhPXLJmTV4gHq9p2Pq.3nUk.0SJpmAwMoyF4w4PZM/0/LTkVoy', 'Admin', '2026-03-01 16:59:26', 'approved', NULL, NULL, NULL, 1, 0, NULL, 'none', NULL, 'default', '2026-03-05 18:52:17', '2026-03-01 16:59:26', '2026-03-05 18:52:17'),
(3, 'ZacZac460', 'crispino.zyrus@gmail.com', '$2y$10$JA5TOx2v.CYAP/OFYqPPjOGIxev05wF43XlLqHFZ16IYc6WVx/El6', 'ZacZac460', NULL, 'approved', 1, '2026-03-01 17:28:37', NULL, 1, 0, NULL, 'none', NULL, 'default', '2026-03-10 20:56:30', '2026-03-01 17:28:29', '2026-03-10 20:56:30');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` bigint UNSIGNED NOT NULL,
  `role_id` smallint UNSIGNED NOT NULL,
  `assigned_by` bigint UNSIGNED DEFAULT NULL,
  `assigned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_by`, `assigned_at`) VALUES
(1, 1, 1, '2026-03-01 16:59:29'),
(3, 3, NULL, '2026-03-01 17:28:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_actor` (`actor_user_id`,`created_at`),
  ADD KEY `idx_audit_target` (`target_type`,`target_id`,`created_at`),
  ADD KEY `idx_audit_action` (`action`,`created_at`);

--
-- Indexes for table `auth_sessions`
--
ALTER TABLE `auth_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_auth_sessions_rth` (`refresh_token_hash`),
  ADD KEY `idx_auth_sessions_user` (`user_id`),
  ADD KEY `idx_auth_sessions_expires` (`expires_at`),
  ADD KEY `idx_auth_sessions_revoked` (`revoked_at`);

--
-- Indexes for table `dashboard_notifications`
--
ALTER TABLE `dashboard_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dash_user` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ev_user` (`user_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_user` (`user_id`,`created_at`),
  ADD KEY `idx_login_attempts_identifier` (`identifier`,`created_at`),
  ADD KEY `idx_login_attempts_ip` (`ip_address`,`created_at`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_roles_name` (`name`);

--
-- Indexes for table `two_factor_secrets`
--
ALTER TABLE `two_factor_secrets`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_active` (`is_active`),
  ADD KEY `idx_users_banned_until` (`banned_until`),
  ADD KEY `fk_users_approved_by` (`approved_by`),
  ADD KEY `idx_users_approval_status` (`approval_status`,`created_at`),
  ADD KEY `idx_users_is_guest` (`is_guest`,`created_at`),
  ADD KEY `idx_users_bank_link_status` (`bank_link_status`,`created_at`),
  ADD KEY `idx_users_appearance_mode` (`appearance_mode`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `idx_user_roles_role` (`role_id`),
  ADD KEY `fk_user_roles_assigned_by` (`assigned_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `auth_sessions`
--
ALTER TABLE `auth_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `dashboard_notifications`
--
ALTER TABLE `dashboard_notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` smallint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `auth_sessions`
--
ALTER TABLE `auth_sessions`
  ADD CONSTRAINT `fk_auth_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dashboard_notifications`
--
ALTER TABLE `dashboard_notifications`
  ADD CONSTRAINT `fk_dash_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `fk_ev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD CONSTRAINT `fk_login_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `two_factor_secrets`
--
ALTER TABLE `two_factor_secrets`
  ADD CONSTRAINT `fk_2fa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_user_roles_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
