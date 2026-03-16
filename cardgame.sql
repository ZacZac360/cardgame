-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2026 at 11:12 AM
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
  `action` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
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
(9, NULL, 'GUEST_CREATE', 'user', 9, '{\"username\": \"guest_1A8F9B60\"}', 0x00000000000000000000000000000001, '2026-03-10 15:00:29'),
(10, NULL, 'GUEST_CREATE', 'user', 10, '{\"username\": \"guest_AAF4CE60\"}', 0x00000000000000000000000000000001, '2026-03-11 20:46:41'),
(11, NULL, 'GUEST_CREATE', 'user', 11, '{\"username\": \"guest_1BAD5D10\"}', 0x00000000000000000000000000000001, '2026-03-12 21:53:53'),
(12, NULL, 'USER_REGISTER', 'user', 12, '{\"email\": \"zacgames.tv@gmail.com\", \"username\": \"ZaccyBoi\"}', 0x00000000000000000000000000000001, '2026-03-13 17:00:45'),
(13, 1, 'USER_APPROVE', 'user', 12, '{\"note\": \"approved from admin queue\"}', NULL, '2026-03-13 17:01:47'),
(14, NULL, 'GUEST_CREATE', 'user', 13, '{\"username\": \"guest_876B308C\"}', 0x00000000000000000000000000000001, '2026-03-16 14:25:24'),
(15, NULL, 'PASSWORD_RESET', 'user', 3, '{\"method\": \"email_otp\"}', NULL, '2026-03-16 15:15:40');

-- --------------------------------------------------------

--
-- Table structure for table `auth_sessions`
--

CREATE TABLE `auth_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `refresh_token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
(2, 3, '26198f6d3923022c9cf8cb9cb94a559ac0064b5369482775ce9e751bea917fee', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-01 17:28:52', '2026-03-01 17:28:52', '2026-03-15 17:28:52', '2026-03-16 15:15:40'),
(3, 3, '8563896790eb164dd2a3c58957bf1457773fa6ee0c9050a651b097317cc6dad7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-01 17:29:47', '2026-03-01 17:29:47', '2026-03-15 17:29:47', '2026-03-16 15:15:40'),
(4, 3, 'f0ecea7fdb6923623057f9303acd2c818d2d11a49824a3b0724a9a1d211acb7f', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-01 17:44:10', '2026-03-01 17:44:10', '2026-03-15 17:44:10', '2026-03-01 17:54:53'),
(5, 1, 'a8e581855528af7a33239a1b14b063193fc3324572f3fcf86b566f97ea6bb326', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-03 19:18:49', '2026-03-03 19:18:49', '2026-03-17 19:18:49', '2026-03-03 19:19:50'),
(6, 1, '97731fea97bc8bebf18b2665a716e210b6d798008d6d7adb6371fd07ae8f3ada', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-03 19:19:52', '2026-03-03 19:19:52', '2026-03-17 19:19:52', '2026-03-03 19:20:00'),
(7, 3, '153bbd80065e0f1f150d04a36cbb570d785bee6928d96f8fbcb02c3ade352bbe', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-03 19:20:19', '2026-03-03 19:20:19', '2026-03-17 19:20:19', '2026-03-03 19:21:09'),
(8, 1, '62cefe75e76af43352b0e38d071e76f56c860fe3353cf19cef740aac10ea8346', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-05 18:52:17', '2026-03-05 18:52:17', '2026-03-19 18:52:17', NULL),
(9, 3, 'de386cddfab72b62675f38a529c7cf4e4ff51d664c66536fb81ec708a50bc579', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-05 18:54:23', '2026-03-05 18:54:23', '2026-03-19 18:54:23', '2026-03-05 19:02:36'),
(10, 3, '97daa7fea064e6424f75715e33a1dede3cd7e781d6a095f3a4f131313da8a7a3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-05 19:11:42', '2026-03-05 19:11:42', '2026-03-19 19:11:42', '2026-03-16 15:15:40'),
(11, 3, '871ca5058bc689ca1bff0eae0d4c6b39482ad93f74fdb35de02b6e6b2263c888', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-05 21:06:59', '2026-03-05 21:06:59', '2026-03-19 21:06:59', '2026-03-05 21:08:21'),
(12, 3, 'dd6b583d8bd36cbe55438f882b60f4327c825d74d22d72d55588191378c19526', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-05 21:08:23', '2026-03-05 21:08:23', '2026-03-19 21:08:23', '2026-03-16 15:15:40'),
(13, 3, '8726c11e649249dd3ed175cbe8538b0b86c0a92325f334768e46f91b9dd73b2d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-06 13:52:51', '2026-03-06 13:52:51', '2026-03-20 13:52:51', '2026-03-06 14:22:41'),
(14, 3, 'd02c5274e884de276d8fd4bebd4b29af5b54818b61af9e52d142204d7dc74267', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-06 15:35:30', '2026-03-06 15:35:30', '2026-03-20 15:35:30', '2026-03-06 15:45:13'),
(15, 3, '47f30a58cfab85c4dcacb938b036d6708ae86cc523541833cfbbc5df0f38c5d3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-06 15:45:25', '2026-03-06 15:45:25', '2026-03-20 15:45:25', '2026-03-06 15:45:47'),
(16, 3, 'fafbafbc38e2420bd15f1e44fbe0efbe3eaee5f1c078343234de4789e5a43ab8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 12:19:50', '2026-03-10 12:19:50', '2026-03-24 12:19:50', '2026-03-16 15:15:40'),
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
(27, 3, 'cd98018920d6d298ec1282c5ab6a77d477837f9c2b50944696b70c49cb3f8c65', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-10 20:56:30', '2026-03-10 20:56:30', '2026-03-24 20:56:30', '2026-03-16 15:15:40'),
(28, 3, '04ee80e7dcb87e262c1b4ca00a291ddfddf3f3aba742b011e4bdd4272e95ba81', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-11 20:43:48', '2026-03-11 20:43:48', '2026-03-25 20:43:48', '2026-03-16 15:15:40'),
(29, 3, '8c129c711a289d085c56b6748158d89bcb0e9fe94f329ed65eee60ccd5ef8dc6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-11 20:44:32', '2026-03-11 20:44:32', '2026-03-25 20:44:32', '2026-03-11 20:46:07'),
(30, 3, 'b3d116c24b1d4f4bb46705c43a2c30760433eab634fe1245ff374392d4f621f5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-12 18:27:40', '2026-03-12 18:27:40', '2026-03-26 18:27:40', '2026-03-12 21:53:04'),
(31, 3, 'b1c12156fac574f9d965236626f11e4bdb562ce39210ce10015383aaddbb12ff', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-12 21:53:09', '2026-03-12 21:53:09', '2026-03-26 21:53:09', '2026-03-12 21:53:35'),
(32, 3, '19ca02b23c478d0083542f1586aef692507a893fdfea8484149c42cd4ab3c27d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-12 22:00:43', '2026-03-12 22:00:43', '2026-03-26 22:00:43', '2026-03-12 23:43:42'),
(33, 3, '9e3ba4ef251cbf1fe99d50b5d85b14faff805846fdf3b45666c0ea3431d1a8e1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 13:52:54', '2026-03-13 13:52:54', '2026-03-27 13:52:54', '2026-03-13 14:02:28'),
(34, 3, '1da82de13542853cfcc3ce943a8cc4d8ae8d4887bdd788540bd65de6d79912e8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 15:52:39', '2026-03-13 15:52:39', '2026-03-27 15:52:39', '2026-03-13 17:00:22'),
(35, 3, 'e0685979f982b70e5669a0a3b6f0da3b159c33b0a6bc6f311710f03a8f8802e3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 17:02:01', '2026-03-13 17:02:01', '2026-03-27 17:02:01', '2026-03-13 17:02:12'),
(36, 12, '1ac2fbf9f03a2ae7ef1ac74c62e8efd1fc8fff45829f627afd3918b2b8fc4d37', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 17:02:20', '2026-03-13 17:02:20', '2026-03-27 17:02:20', '2026-03-13 17:54:16'),
(37, 12, 'b982552d92a2c0851dd4104686749bcfd7c6ae4545bad1e4b7ef00cef8f8cde9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 17:54:18', '2026-03-13 17:54:18', '2026-03-27 17:54:18', '2026-03-13 17:55:23'),
(38, 3, '871d62f2756637b064cab9a81e8d68b44defbaacc79787971488a42c2b66b9b3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 17:55:27', '2026-03-13 17:55:27', '2026-03-27 17:55:27', '2026-03-13 17:59:01'),
(39, 3, '0d99e4dc5ac8d6334f40376905ecf0b1c399fc17402da5a9b636bf8281efe02d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 17:59:03', '2026-03-13 17:59:03', '2026-03-27 17:59:03', '2026-03-13 17:59:40'),
(40, 3, 'fe4ca9ef68f8ff9386917ffc15192af26232fefa5de3314ed802f2801cba9f52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 17:59:42', '2026-03-13 17:59:42', '2026-03-27 17:59:42', '2026-03-13 18:09:21'),
(41, 3, '0933865957825a504c4d100f7846993743c9649bc9b4c4dcad3c5a120c456e01', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 18:09:23', '2026-03-13 18:09:23', '2026-03-27 18:09:23', '2026-03-13 18:15:37'),
(42, 12, '612d1eed1d64cd3c36487c0c76bae2d6ada422b9409b3ad17b8d310accab4d47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 18:15:43', '2026-03-13 18:15:43', '2026-03-27 18:15:43', '2026-03-13 18:27:00'),
(43, 12, '16673d637d6b90081d8b63e2b89ba941d47179c6defb27f43fefe7428bff986f', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 18:27:03', '2026-03-13 18:27:03', '2026-03-27 18:27:03', '2026-03-13 18:27:08'),
(44, 3, '8cdf2514e3ab7c0af3fa428fded1401a413d68f663db86c22a0719d9849723bb', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 18:27:12', '2026-03-13 18:27:12', '2026-03-27 18:27:12', '2026-03-13 18:27:23'),
(45, 12, '51d64486c21b28a9a72c386cb7cb048a8bcdd95e1a69bc96d62c7a8c82f14dc4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 18:27:26', '2026-03-13 18:27:26', '2026-03-27 18:27:26', NULL),
(46, 12, '6feccb174b13ddbd32fadde94e9408a0ead26caa9d353c60f3311dc47de5646c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 18:46:32', '2026-03-13 18:46:32', '2026-03-27 18:46:32', '2026-03-13 19:16:36'),
(47, 3, 'ab6b31b632a638f8af18346f89d61ca2d7ebc4c632a202542e672fe586d4edbb', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 19:16:40', '2026-03-13 19:16:40', '2026-03-27 19:16:40', '2026-03-13 19:18:49'),
(48, 3, 'f5a490b7de785e38c0a8958c56cc6c21b6a0d19c879cef91cf1e463abe7876c8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 19:20:58', '2026-03-13 19:20:58', '2026-03-27 19:20:58', '2026-03-13 19:51:23'),
(49, 12, 'f518c4d65baf368cda10d65c658b3e7cd9e2489c783768e2d41f8b0fc25e5a94', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-13 19:51:31', '2026-03-13 19:51:31', '2026-03-27 19:51:31', NULL),
(50, 12, 'ca1b53d393ea7d9634ab6b0dca1229d820ece4e8174cb435d67af9cc3533c282', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-16 11:40:29', '2026-03-16 11:40:29', '2026-03-30 11:40:29', '2026-03-16 12:19:58'),
(51, 12, '1bba307757457a14250b4c0c953a91350ee9ccfc7b76d937c8af0b148a6db5e6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-16 14:39:57', '2026-03-16 14:39:57', '2026-03-30 14:39:57', '2026-03-16 14:48:30'),
(52, 3, '7780d4478054e86db89bb12d32b7b7414b80425676085dc024cd2086cbaa4a5a', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-16 14:48:38', '2026-03-16 14:48:38', '2026-03-30 14:48:38', '2026-03-16 14:48:41'),
(53, 3, '56f4b016dcc4deaa696a27b22d2eacfec8e6caa45358a01b96e8c8ddd4b05ff2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-16 15:13:45', '2026-03-16 15:13:45', '2026-03-30 15:13:45', '2026-03-16 15:13:45'),
(54, 3, 'a6ce54bfa2a89dcb98d09d9df6be7bf2fc0680f59635915ff5a27625832d0a43', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-16 15:18:26', '2026-03-16 15:18:26', '2026-03-30 15:18:26', '2026-03-16 15:19:39'),
(55, 12, '048fad7c9e3b421dfe26dd2a765c87a817dd6595b6f6207f3f8a327fc94e0ccc', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-16 15:34:22', '2026-03-16 15:34:22', '2026-03-30 15:34:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token_type` enum('login_verify','password_reset','email_verify') COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backup_codes`
--

CREATE TABLE `backup_codes` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `code_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `backup_codes`
--

INSERT INTO `backup_codes` (`id`, `user_id`, `code_hash`, `is_used`, `used_at`, `created_at`) VALUES
(1, 12, '$2y$10$6DB3GHZG.UPT6l4qH60pruynHTD2oBzHMSaijD5t/rx/tZk2mrwHe', 0, NULL, '2026-03-16 17:41:31'),
(2, 12, '$2y$10$fylQn0Jt0odpZxhVoHj7N.nKGDDlv4OcPTsgauoy9Yedf3Mq56.sS', 0, NULL, '2026-03-16 17:41:31'),
(3, 12, '$2y$10$.OT/oOHvXvYc7EX1lm0Xjuc5DVvUfguI8lOhPB2ejbBGumzXJv7ri', 0, NULL, '2026-03-16 17:41:31'),
(4, 12, '$2y$10$rROqtUBUGRMM2OIiv6uo2uEoBSSTzmBgx3SIUlEsT1oZGK8qA036i', 0, NULL, '2026-03-16 17:41:31'),
(5, 12, '$2y$10$k7SgxaNAKCTTKIUDaWy68uNvAlDXTZJoBr1bXa2Gz2qZq56JHOUd.', 0, NULL, '2026-03-16 17:41:31'),
(6, 12, '$2y$10$nGu1WWUJeTn0tO6ZpA7oW.Ox9xmCtlwGmnQZTNDqzPQD8xS/354Se', 0, NULL, '2026-03-16 17:41:31'),
(7, 12, '$2y$10$L7a81fh4nqK7hfjheTOAt.XdoVpq1LhIahqcU8OBEJ8qzxTI/RYYC', 0, NULL, '2026-03-16 17:41:31'),
(8, 12, '$2y$10$inCqWkaMHlbuxYz5Opv9PeDqKzCyVOv54SZOKaKlByxF4Bfwyenk.', 0, NULL, '2026-03-16 17:41:31'),
(9, 12, '$2y$10$Sm.3J/eAOP46jwYbcNbzJOaeqAzLoBcya6NQnhNdPpMHTZFNjZV.a', 0, NULL, '2026-03-16 17:41:32'),
(10, 12, '$2y$10$Isi3nsyAyBjWmii3zQCRteqkhDi/RW5WknbYxajC0EerEESmg3OZG', 0, NULL, '2026-03-16 17:41:32');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `created_at`) VALUES
(1, '2026-03-13 17:32:15');

-- --------------------------------------------------------

--
-- Table structure for table `conversation_members`
--

CREATE TABLE `conversation_members` (
  `id` int NOT NULL,
  `conversation_id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `last_read_message_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversation_members`
--

INSERT INTO `conversation_members` (`id`, `conversation_id`, `user_id`, `last_read_message_id`) VALUES
(1, 1, 12, 7),
(2, 1, 3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_notifications`
--

CREATE TABLE `dashboard_notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dashboard_notifications`
--

INSERT INTO `dashboard_notifications` (`id`, `user_id`, `type`, `title`, `body`, `link_url`, `is_read`, `created_at`, `read_at`) VALUES
(1, 1, 'admin_approval', 'New account pending approval', 'User: ZacZac460 (crispino.zyrus@gmail.com)', '/admin/pending-users.php?user_id=3', 0, '2026-03-01 17:28:29', NULL),
(2, 3, 'system', 'Account approved', 'You can now log in and play.', '/index.php', 1, '2026-03-01 17:28:37', NULL),
(3, 1, 'admin_approval', 'New account pending approval', 'User: ZaccyBoi (zacgames.tv@gmail.com)', '/admin/pending-users.php?user_id=12', 0, '2026-03-13 17:00:45', NULL),
(4, 12, 'system', 'Account approved', 'You can now log in.', '/index.php', 1, '2026-03-13 17:01:47', NULL),
(5, 12, 'friend_request', 'Friend Request', 'You received a new friend request.', '/friends.php', 1, '2026-03-13 17:02:10', NULL),
(6, 3, 'friend_accept', 'Friend Request Accepted', 'Your friend request was accepted.', '/friends.php', 1, '2026-03-13 17:02:26', NULL),
(7, 3, 'message', 'New Message', 'You received a new message.', '/messages.php?conversation_id=1', 1, '2026-03-13 17:54:34', NULL),
(8, 12, 'message', 'New Message', 'You received a new message.', '/messages.php?conversation_id=1', 1, '2026-03-13 17:58:49', NULL),
(9, 12, 'message', 'New Message', 'You received a new message.', '/messages.php?conversation_id=1', 1, '2026-03-13 18:15:36', NULL),
(10, 3, 'message', 'New Message', 'You received a new message.', '/messages.php?conversation_id=1', 1, '2026-03-13 18:15:56', NULL),
(11, 12, 'friend_request', 'Friend Request', 'You received a new friend request.', '/friends.php', 1, '2026-03-13 18:27:22', NULL),
(12, 3, 'friend_accept', 'Friend Request Accepted', 'Your friend request was accepted.', '/friends.php', 1, '2026-03-13 18:27:28', NULL),
(13, 12, 'message', 'New Message', 'You received a new message.', '/messages.php?conversation_id=1', 1, '2026-03-13 19:21:18', NULL),
(14, 3, 'message', 'New Message', 'You received a new message.', '/messages.php?conversation_id=1', 0, '2026-03-13 19:51:39', NULL),
(15, 3, 'message', 'New Message', 'You received a new message.', '/messages.php?conversation_id=1', 0, '2026-03-13 19:51:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `purpose` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'login'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `user_id`, `email`, `otp_code`, `expires_at`, `verified_at`, `created_at`, `purpose`) VALUES
(6, 12, 'zacgames.tv@gmail.com', '404501', '2026-03-16 10:36:45', '2026-03-16 17:32:00', '2026-03-16 17:31:45', 'login');

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

CREATE TABLE `friends` (
  `id` int NOT NULL,
  `user_one` bigint UNSIGNED NOT NULL,
  `user_two` bigint UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `friends`
--

INSERT INTO `friends` (`id`, `user_one`, `user_two`, `created_at`) VALUES
(2, 3, 12, '2026-03-13 18:27:28');

-- --------------------------------------------------------

--
-- Table structure for table `friend_requests`
--

CREATE TABLE `friend_requests` (
  `id` int NOT NULL,
  `sender_id` bigint UNSIGNED NOT NULL,
  `receiver_id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','accepted','declined','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `responded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `friend_requests`
--

INSERT INTO `friend_requests` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`, `responded_at`) VALUES
(1, 3, 12, 'accepted', '2026-03-13 17:02:10', '2026-03-13 17:02:26'),
(2, 3, 12, 'accepted', '2026-03-13 18:27:22', '2026-03-13 18:27:28');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varbinary(16) DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `failure_reason` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
(31, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-10 20:56:30'),
(32, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-11 20:43:47'),
(33, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-11 20:44:32'),
(34, NULL, 'user1@propertyhub.local', 0, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 'wrong_credentials', '2026-03-12 18:27:35'),
(35, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-12 18:27:40'),
(36, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-12 21:53:09'),
(37, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-12 22:00:43'),
(38, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 13:52:54'),
(39, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 15:52:39'),
(40, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 17:02:01'),
(41, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 17:02:20'),
(42, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 17:54:18'),
(43, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 17:55:27'),
(44, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 17:59:03'),
(45, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 17:59:42'),
(46, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 18:09:23'),
(47, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 18:15:43'),
(48, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 18:27:03'),
(49, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 18:27:12'),
(50, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 18:27:26'),
(51, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 18:46:32'),
(52, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 19:16:40'),
(53, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 19:20:58'),
(54, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-13 19:51:31'),
(55, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-16 11:40:29'),
(56, 12, 'ZaccyBoi', 0, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 'wrong_credentials', '2026-03-16 14:36:20'),
(57, 12, 'ZaccyBoi', 0, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 'wrong_credentials', '2026-03-16 14:36:25'),
(58, 12, 'ZaccyBoi', 0, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 'wrong_credentials', '2026-03-16 14:36:28'),
(59, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-16 14:39:57'),
(60, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-16 14:48:38'),
(61, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-16 15:13:44'),
(62, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-16 15:18:26'),
(63, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-16 15:34:22');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `conversation_id` int NOT NULL,
  `sender_id` bigint UNSIGNED NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `body`, `created_at`) VALUES
(1, 1, 12, 'Test', '2026-03-13 17:54:34'),
(2, 1, 3, 'Sup bro', '2026-03-13 17:58:49'),
(3, 1, 3, 'Dude ilan talo mo kagabi', '2026-03-13 18:15:36'),
(4, 1, 12, 'Ay nako wag mo na tanungin. Bwiset na baraha to', '2026-03-13 18:15:56'),
(5, 1, 3, 'Aight gg pars', '2026-03-13 19:21:18'),
(6, 1, 12, 'Test', '2026-03-13 19:51:39'),
(7, 1, 12, 'Test', '2026-03-13 19:51:40');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` smallint UNSIGNED NOT NULL,
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
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
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `secret_key` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enabled_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `two_factor_secrets`
--

INSERT INTO `two_factor_secrets` (`id`, `user_id`, `secret_key`, `is_enabled`, `created_at`, `enabled_at`, `updated_at`) VALUES
(1, 12, 'MPQBGQWNGIKUH64ZOF3WVLYG3YDMOXST', 1, '2026-03-16 17:11:01', '2026-03-16 17:26:32', '2026-03-16 17:26:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `failed_login_attempts` int NOT NULL DEFAULT '0',
  `security_challenge_required` tinyint(1) NOT NULL DEFAULT '0',
  `last_failed_login_at` datetime DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejected_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_guest` tinyint(1) NOT NULL DEFAULT '0',
  `banned_until` datetime DEFAULT NULL,
  `bank_link_status` enum('none','pending','linked','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `bank_linked_at` datetime DEFAULT NULL,
  `appearance_mode` enum('default','dark','light') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `level` int DEFAULT '1',
  `exp` int DEFAULT '0',
  `exp_to_next` int DEFAULT '100',
  `credits` int DEFAULT '0',
  `matches_played` int DEFAULT '0',
  `matches_won` int DEFAULT '0',
  `bio` text COLLATE utf8mb4_unicode_ci,
  `avatar_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favorite_deck` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tagline` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `display_name`, `email_verified_at`, `failed_login_attempts`, `security_challenge_required`, `last_failed_login_at`, `approval_status`, `approved_by`, `approved_at`, `rejected_reason`, `is_active`, `is_guest`, `banned_until`, `bank_link_status`, `bank_linked_at`, `appearance_mode`, `last_login_at`, `created_at`, `updated_at`, `level`, `exp`, `exp_to_next`, `credits`, `matches_played`, `matches_won`, `bio`, `avatar_path`, `favorite_deck`, `tagline`) VALUES
(1, 'admin', 'admin@game.local', '$2y$10$axxzhPXLJmTV4gHq9p2Pq.3nUk.0SJpmAwMoyF4w4PZM/0/LTkVoy', 'Admin', '2026-03-01 16:59:26', 0, 0, NULL, 'approved', NULL, NULL, NULL, 1, 0, NULL, 'none', NULL, 'default', '2026-03-05 18:52:17', '2026-03-01 16:59:26', '2026-03-05 18:52:17', 1, 0, 100, 0, 0, 0, NULL, NULL, NULL, NULL),
(3, 'ZacZac460', 'crispino.zyrus@gmail.com', '$2y$10$u6W3WqPs.JGt1LjIQBD4b.HVRMQ1nrnlMgTb5TN8cXU/6AzkclgR2', 'ZacZac460', NULL, 0, 0, NULL, 'approved', 1, '2026-03-01 17:28:37', NULL, 1, 0, NULL, 'none', NULL, 'default', '2026-03-16 15:18:26', '2026-03-01 17:28:29', '2026-03-16 15:18:26', 1, 0, 100, 0, 0, 0, NULL, 'uploads/avatars/avatar_3_1773395746.png', NULL, NULL),
(10, 'guest_AAF4CE60', 'guest_AAF4CE60@guest.local', '', 'Guest', NULL, 0, 0, NULL, 'approved', NULL, NULL, NULL, 1, 1, NULL, 'none', NULL, 'default', NULL, '2026-03-11 20:46:41', '2026-03-11 20:46:41', 1, 0, 100, 0, 0, 0, NULL, NULL, NULL, NULL),
(11, 'guest_1BAD5D10', 'guest_1BAD5D10@guest.local', '', 'Guest', NULL, 0, 0, NULL, 'approved', NULL, NULL, NULL, 1, 1, NULL, 'none', NULL, 'default', NULL, '2026-03-12 21:53:53', '2026-03-12 21:53:53', 1, 0, 100, 0, 0, 0, NULL, NULL, NULL, NULL),
(12, 'ZaccyBoi', 'zacgames.tv@gmail.com', '$2y$10$q5zfGSrYB0rG8Ixt68UKvOxkamcHf4g3MU9rBjW2UDQA8e/xowYxa', 'ZaccyBoi', '2026-03-16 17:32:00', 0, 0, NULL, 'approved', 1, '2026-03-13 17:01:47', NULL, 1, 0, NULL, 'none', NULL, 'default', '2026-03-16 15:34:22', '2026-03-13 17:00:45', '2026-03-16 17:56:12', 1, 0, 100, 0, 0, 0, 'Testing', 'uploads/avatars/avatar_12_1773654943.png', '', ''),
(13, 'guest_876B308C', 'guest_876B308C@guest.local', '', 'Guest', NULL, 0, 0, NULL, 'approved', NULL, NULL, NULL, 1, 1, NULL, 'none', NULL, 'default', NULL, '2026-03-16 14:25:24', '2026-03-16 14:25:24', 1, 0, 100, 0, 0, 0, NULL, NULL, NULL, NULL);

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
(3, 3, NULL, '2026-03-01 17:28:29'),
(10, 3, NULL, '2026-03-11 20:46:41'),
(11, 3, NULL, '2026-03-12 21:53:53'),
(12, 3, NULL, '2026-03-13 17:00:45'),
(13, 3, NULL, '2026-03-16 14:25:24');

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
-- Indexes for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_auth_tokens_user` (`user_id`,`token_type`,`expires_at`),
  ADD KEY `idx_auth_tokens_expires` (`expires_at`);

--
-- Indexes for table `backup_codes`
--
ALTER TABLE `backup_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_backup_codes_user` (`user_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversations_created_at` (`created_at`);

--
-- Indexes for table `conversation_members`
--
ALTER TABLE `conversation_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_conversation_member` (`conversation_id`,`user_id`),
  ADD KEY `idx_conversation_members_user_id` (`user_id`),
  ADD KEY `idx_conversation_members_last_read` (`last_read_message_id`);

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
-- Indexes for table `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_friends_pair` (`user_one`,`user_two`),
  ADD KEY `idx_friends_user_one` (`user_one`),
  ADD KEY `idx_friends_user_two` (`user_two`);

--
-- Indexes for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_friend_requests_sender` (`sender_id`),
  ADD KEY `idx_friend_requests_receiver` (`receiver_id`),
  ADD KEY `idx_friend_requests_status` (`status`),
  ADD KEY `idx_friend_requests_created_at` (`created_at`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_user` (`user_id`,`created_at`),
  ADD KEY `idx_login_attempts_identifier` (`identifier`,`created_at`),
  ADD KEY `idx_login_attempts_ip` (`ip_address`,`created_at`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_conversation_id` (`conversation_id`),
  ADD KEY `idx_messages_sender_id` (`sender_id`),
  ADD KEY `idx_messages_created_at` (`created_at`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_two_factor_user` (`user_id`);

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `auth_sessions`
--
ALTER TABLE `auth_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backup_codes`
--
ALTER TABLE `backup_codes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `conversation_members`
--
ALTER TABLE `conversation_members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `dashboard_notifications`
--
ALTER TABLE `dashboard_notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `friends`
--
ALTER TABLE `friends`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `friend_requests`
--
ALTER TABLE `friend_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` smallint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `two_factor_secrets`
--
ALTER TABLE `two_factor_secrets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
-- Constraints for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD CONSTRAINT `fk_auth_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `backup_codes`
--
ALTER TABLE `backup_codes`
  ADD CONSTRAINT `fk_backup_codes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_members`
--
ALTER TABLE `conversation_members`
  ADD CONSTRAINT `fk_conversation_members_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conversation_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `friends`
--
ALTER TABLE `friends`
  ADD CONSTRAINT `fk_friends_user_one` FOREIGN KEY (`user_one`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_friends_user_two` FOREIGN KEY (`user_two`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD CONSTRAINT `fk_friend_requests_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_friend_requests_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD CONSTRAINT `fk_login_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `two_factor_secrets`
--
ALTER TABLE `two_factor_secrets`
  ADD CONSTRAINT `fk_two_factor_secrets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
