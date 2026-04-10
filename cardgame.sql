-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2026 at 06:33 AM
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
(15, NULL, 'PASSWORD_RESET', 'user', 3, '{\"method\": \"email_otp\"}', NULL, '2026-03-16 15:15:40'),
(16, 3, 'zeny_topup', 'credit_topup', 3, '{\"pack\": \"Starter Cache\", \"payment_id\": \"pay_Vtpnkvnk5fUfGf47wAp1fNYi\", \"credits_added\": 250, \"credits_after\": 250, \"credits_before\": 0}', 0x3a3a31, '2026-03-16 19:39:47'),
(17, NULL, 'GUEST_CREATE', 'user', 14, '{\"username\": \"guest_7FB8C1DF\"}', 0x00000000000000000000000000000001, '2026-03-16 19:46:15'),
(18, NULL, 'GUEST_CREATE', 'user', 15, '{\"username\": \"guest_B799E5FB\"}', 0x00000000000000000000000000000001, '2026-04-07 19:27:55'),
(19, NULL, 'GUEST_CREATE', 'user', 16, '{\"username\": \"guest_E80E8149\"}', 0x00000000000000000000000000000001, '2026-04-10 12:24:39');

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
(55, 12, '048fad7c9e3b421dfe26dd2a765c87a817dd6595b6f6207f3f8a327fc94e0ccc', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-16 15:34:22', '2026-03-16 15:34:22', '2026-03-30 15:34:22', '2026-03-16 19:26:04'),
(56, 3, '4b3da6f58d8bb2c9a5ff86f9b29d3eb22d09d9a048079aa46072ee913045ffc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-16 19:39:30', '2026-03-16 19:39:30', '2026-03-30 19:39:30', '2026-03-16 19:46:17'),
(57, 3, '7ea4cb6c0adfe2db21cbeaa20f8bc4bd8b6da43f0e2c21785f1ecfa7b289e6e7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 0x00000000000000000000000000000001, '2026-03-16 19:46:20', '2026-03-16 19:46:20', '2026-03-30 19:46:20', NULL),
(58, 12, '8804ed2ca305ecdcf23150ec24e9c9f5ec5ccd86c453459e7919aece6edc86b4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', 0x00000000000000000000000000000001, '2026-04-07 19:28:20', '2026-04-07 19:28:20', '2026-04-21 19:28:20', NULL),
(59, 12, '3f5de06439b09c4f8d818f2cc3d6f7897dd456c6ce00d9e04950619e418fce90', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', 0x00000000000000000000000000000001, '2026-04-09 00:13:11', '2026-04-09 00:13:11', '2026-04-23 00:13:11', '2026-04-09 01:30:47'),
(60, 12, 'b1dc5d7d123c75b1fc3412d097661cb4d0e759c885d0485f2a669f67a76593f3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', 0x00000000000000000000000000000001, '2026-04-09 01:37:39', '2026-04-09 01:37:39', '2026-04-23 01:37:39', '2026-04-09 01:37:42'),
(61, 12, '9672c42d10ce9f98b2e3242092e42025ae864668da494d31d1e3d1ec45ae24fd', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', 0x00000000000000000000000000000001, '2026-04-09 01:37:45', '2026-04-09 01:37:45', '2026-04-23 01:37:45', NULL),
(62, 12, '41f86fc9b0e2760bcf052b585811aba4d9bcd538e263e5472892d1938dc3d0bb', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', 0x00000000000000000000000000000001, '2026-04-10 12:30:23', '2026-04-10 12:30:23', '2026-04-24 12:30:23', NULL);

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
(1, 1, 12, 10),
(2, 1, 3, 10);

-- --------------------------------------------------------

--
-- Table structure for table `credit_topups`
--

CREATE TABLE `credit_topups` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `pack_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pack_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_php` decimal(10,2) NOT NULL,
  `credits_amount` int NOT NULL,
  `bonus_credits` int NOT NULL DEFAULT '0',
  `total_credits` int NOT NULL,
  `paymongo_checkout_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paymongo_payment_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paymongo_payment_intent_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','paid','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `credited_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `credit_topups`
--

INSERT INTO `credit_topups` (`id`, `user_id`, `pack_code`, `pack_name`, `amount_php`, `credits_amount`, `bonus_credits`, `total_credits`, `paymongo_checkout_id`, `paymongo_payment_id`, `paymongo_payment_intent_id`, `reference_number`, `status`, `credited_at`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 12, 'starter_50', 'Starter Cache', 50.00, 250, 0, 250, 'cs_b248ac89434a2874d4370358', NULL, NULL, 'LOGIA-TOPUP-12-1773659588', 'pending', NULL, NULL, '2026-03-16 19:13:08', '2026-03-16 19:13:09'),
(3, 3, 'starter_50', 'Starter Cache', 50.00, 250, 0, 250, 'cs_fe5360522e6702b739f05e14', 'pay_Vtpnkvnk5fUfGf47wAp1fNYi', 'pi_48dNEoDgPZ9CUnfo9DQpGix4', 'LOGIA-TOPUP-3-1773661175', 'paid', '2026-03-16 19:39:47', '2026-03-16 19:39:47', '2026-03-16 19:39:35', '2026-03-16 19:39:47');

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
(14, 3, 'message', 'New Message', 'You received a new message.', '/messages.php?conversation_id=1', 1, '2026-03-13 19:51:39', NULL),
(15, 3, 'message', 'New Message', 'You received a new message.', '/messages.php?conversation_id=1', 1, '2026-03-13 19:51:40', NULL),
(16, 12, 'credit_update', 'Zeny Added', 'Your wallet was credited with 250 Zeny from Starter Cache.', '/cardgame/shop.php?tab=credits', 1, '2026-03-16 19:20:14', NULL),
(17, 3, 'credit_update', 'Zeny Added', 'Your wallet was credited with 250 Zeny from Starter Cache.', '/cardgame/shop.php?tab=credits', 1, '2026-03-16 19:39:47', NULL),
(18, 12, 'message', 'New Message', 'You received a new message.', '/messages.php?conversation_id=1', 1, '2026-03-16 19:40:05', NULL),
(19, 12, 'message', 'New Message', 'You received a new message.', '/messages.php?conversation_id=1', 1, '2026-03-16 19:40:10', NULL),
(20, 12, 'message', 'New Message', 'You received a new message.', '/messages.php?conversation_id=1', 1, '2026-03-16 19:40:15', NULL);

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
-- Table structure for table `game_logs`
--

CREATE TABLE `game_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `room_id` bigint UNSIGNED NOT NULL,
  `log_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `game_logs`
--

INSERT INTO `game_logs` (`id`, `room_id`, `log_text`, `created_at`) VALUES
(1, 1, 'ZaccyBoi created the room.', '2026-04-09 00:13:21'),
(3, 2, 'Room reset by host.', '2026-04-09 00:14:15'),
(4, 3, 'ZaccyBoi created the room.', '2026-04-09 00:15:59'),
(5, 4, 'ZaccyBoi created the room.', '2026-04-09 00:27:20'),
(6, 5, 'ZaccyBoi created the room.', '2026-04-09 00:31:04'),
(8, 6, 'Game started with 4 total seat(s).', '2026-04-09 00:35:49'),
(9, 6, 'ZaccyBoi takes the first turn.', '2026-04-09 00:35:49'),
(10, 6, 'ZaccyBoi played Water 9.', '2026-04-09 00:35:52'),
(11, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:35:54'),
(12, 6, 'AI 3 played Lightning 7.', '2026-04-09 00:35:55'),
(13, 6, 'AI 4 passed and drew 1 card.', '2026-04-09 00:35:56'),
(14, 6, 'ZaccyBoi played Earth 6.', '2026-04-09 00:35:59'),
(15, 6, 'AI 2 played Wind 7.', '2026-04-09 00:36:00'),
(16, 6, 'AI 3 played Wood 8.', '2026-04-09 00:36:01'),
(17, 6, 'AI 4 played Fire 7.', '2026-04-09 00:36:02'),
(18, 6, 'ZaccyBoi played +4 Wild → Water.', '2026-04-09 00:36:07'),
(19, 6, 'AI 2 passed and drew 4 card(s).', '2026-04-09 00:36:08'),
(20, 6, 'AI 3 played +4 Wild → Water.', '2026-04-09 00:36:09'),
(21, 6, 'AI 4 passed and drew 4 card(s).', '2026-04-09 00:36:10'),
(22, 6, 'ZaccyBoi played Lightning 8.', '2026-04-09 00:36:32'),
(23, 6, 'AI 2 played Earth 3.', '2026-04-09 00:36:34'),
(24, 6, 'AI 3 played Wind 6.', '2026-04-09 00:36:35'),
(25, 6, 'AI 4 played Wood 6.', '2026-04-09 00:36:36'),
(26, 6, 'ZaccyBoi played Fire 8.', '2026-04-09 00:36:40'),
(27, 6, 'AI 2 played Water 7.', '2026-04-09 00:36:41'),
(28, 6, 'AI 3 played +4 Wild → Water.', '2026-04-09 00:36:42'),
(29, 6, 'AI 4 passed and drew 4 card(s).', '2026-04-09 00:36:43'),
(30, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:36:46'),
(31, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:36:47'),
(32, 6, 'AI 3 regains initiative.', '2026-04-09 00:36:47'),
(33, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:36:48'),
(34, 6, 'AI 4 played Lightning 2.', '2026-04-09 00:36:49'),
(35, 6, 'ZaccyBoi played Earth 10.', '2026-04-09 00:36:53'),
(36, 6, 'AI 2 played Wind 5.', '2026-04-09 00:36:55'),
(37, 6, 'AI 3 played Wood 4.', '2026-04-09 00:36:56'),
(38, 6, 'AI 4 played Fire 4.', '2026-04-09 00:36:57'),
(39, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:37:02'),
(40, 6, 'AI 2 played Water 8.', '2026-04-09 00:37:03'),
(41, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:37:04'),
(42, 6, 'AI 4 played Lightning 3.', '2026-04-09 00:37:05'),
(43, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:37:08'),
(44, 6, 'AI 2 played Earth 8.', '2026-04-09 00:37:10'),
(45, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:37:11'),
(46, 6, 'AI 4 played Wind 2.', '2026-04-09 00:37:12'),
(47, 6, 'ZaccyBoi played Wood 7.', '2026-04-09 00:37:13'),
(48, 6, 'AI 2 played Fire 1.', '2026-04-09 00:37:15'),
(49, 6, 'AI 3 played Water 3.', '2026-04-09 00:37:16'),
(50, 6, 'AI 4 played Lightning 4.', '2026-04-09 00:37:17'),
(51, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:37:29'),
(52, 6, 'AI 2 played Earth 9.', '2026-04-09 00:37:30'),
(53, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:37:31'),
(54, 6, 'AI 4 played Wind 4.', '2026-04-09 00:37:32'),
(55, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:37:35'),
(56, 6, 'AI 2 played Wood 5.', '2026-04-09 00:37:37'),
(57, 6, 'AI 3 played Fire 2.', '2026-04-09 00:37:38'),
(58, 6, 'AI 4 played Water 5.', '2026-04-09 00:37:39'),
(59, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:37:40'),
(60, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:37:42'),
(61, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:37:43'),
(62, 6, 'AI 4 regains initiative.', '2026-04-09 00:37:43'),
(63, 6, 'AI 4 passed and drew 1 card.', '2026-04-09 00:37:44'),
(64, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:37:45'),
(65, 6, 'AI 2 played Lightning 9.', '2026-04-09 00:37:47'),
(66, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:37:48'),
(67, 6, 'AI 4 played Earth 1.', '2026-04-09 00:37:49'),
(68, 6, 'ZaccyBoi played Wind 8.', '2026-04-09 00:37:58'),
(69, 6, 'AI 2 played Wood 8.', '2026-04-09 00:38:00'),
(70, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:38:01'),
(71, 6, 'AI 4 played Fire 5.', '2026-04-09 00:38:02'),
(72, 6, 'ZaccyBoi played Water 5.', '2026-04-09 00:38:04'),
(73, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:38:06'),
(74, 6, 'AI 3 played Lightning 6.', '2026-04-09 00:38:07'),
(75, 6, 'AI 4 played Earth 8.', '2026-04-09 00:38:08'),
(76, 6, 'ZaccyBoi played Wind 1.', '2026-04-09 00:38:10'),
(77, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:38:11'),
(78, 6, 'AI 3 played Wood 5.', '2026-04-09 00:38:12'),
(79, 6, 'AI 4 played Fire 9.', '2026-04-09 00:38:13'),
(80, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:38:15'),
(81, 6, 'AI 2 played Water 4.', '2026-04-09 00:38:17'),
(82, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:38:18'),
(83, 6, 'AI 4 passed and drew 1 card.', '2026-04-09 00:38:19'),
(84, 6, 'ZaccyBoi played Lightning 1.', '2026-04-09 00:38:23'),
(85, 6, 'AI 2 played Earth 4.', '2026-04-09 00:38:25'),
(86, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:38:27'),
(87, 6, 'AI 4 played Wind 9.', '2026-04-09 00:38:28'),
(88, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:38:29'),
(89, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:38:31'),
(90, 6, 'AI 3 played +2 Wood.', '2026-04-09 00:38:32'),
(91, 6, 'AI 4 passed and drew 2 card(s).', '2026-04-09 00:38:33'),
(92, 6, 'ZaccyBoi played Fire 3.', '2026-04-09 00:38:35'),
(93, 6, 'AI 2 played Water 10.', '2026-04-09 00:38:36'),
(94, 6, 'AI 3 played Lightning 8.', '2026-04-09 00:38:37'),
(95, 6, 'AI 4 played Earth 7.', '2026-04-09 00:38:38'),
(96, 6, 'ZaccyBoi played Wind 5.', '2026-04-09 00:38:40'),
(97, 6, 'AI 2 played Wood 1.', '2026-04-09 00:38:41'),
(98, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:38:42'),
(99, 6, 'AI 4 played Fire 5.', '2026-04-09 00:38:43'),
(100, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:38:45'),
(101, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:38:46'),
(102, 6, 'AI 3 played Water 1.', '2026-04-09 00:38:47'),
(103, 6, 'AI 4 passed and drew 1 card.', '2026-04-09 00:38:48'),
(104, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:38:50'),
(105, 6, 'AI 2 played Lightning 5.', '2026-04-09 00:38:52'),
(106, 6, 'AI 3 played +4 Wild → Water.', '2026-04-09 00:38:53'),
(107, 6, 'AI 4 passed and drew 4 card(s).', '2026-04-09 00:38:54'),
(108, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:38:55'),
(109, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:38:57'),
(110, 6, 'AI 3 regains initiative.', '2026-04-09 00:38:57'),
(111, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:38:58'),
(112, 6, 'AI 4 played Lightning 5.', '2026-04-09 00:38:59'),
(113, 6, 'ZaccyBoi played Earth 2.', '2026-04-09 00:39:02'),
(114, 6, 'AI 2 played Wind 8.', '2026-04-09 00:39:03'),
(115, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:39:04'),
(116, 6, 'AI 4 played Wood 9.', '2026-04-09 00:39:05'),
(117, 6, 'ZaccyBoi played +2 Fire.', '2026-04-09 00:39:07'),
(118, 6, 'AI 2 played +2 Earth.', '2026-04-09 00:39:08'),
(119, 6, 'AI 3 played +2 Water.', '2026-04-09 00:39:09'),
(120, 6, 'AI 4 passed and drew 6 card(s).', '2026-04-09 00:39:10'),
(121, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:39:13'),
(122, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:39:15'),
(123, 6, 'AI 3 regains initiative.', '2026-04-09 00:39:15'),
(124, 6, 'AI 3 played +2 Lightning.', '2026-04-09 00:39:16'),
(125, 6, 'AI 4 passed and drew 2 card(s).', '2026-04-09 00:39:17'),
(126, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:39:20'),
(127, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:39:22'),
(128, 6, 'AI 3 regains initiative.', '2026-04-09 00:39:22'),
(129, 6, 'AI 3 played Earth 5.', '2026-04-09 00:39:23'),
(130, 6, 'AI 4 played Wind 2.', '2026-04-09 00:39:24'),
(131, 6, 'ZaccyBoi played Wood 2.', '2026-04-09 00:39:27'),
(132, 6, 'AI 2 played Fire 4.', '2026-04-09 00:39:29'),
(133, 6, 'AI 3 played Water 8.', '2026-04-09 00:39:30'),
(134, 6, 'AI 4 played Lightning 3.', '2026-04-09 00:39:31'),
(135, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:39:34'),
(136, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:39:36'),
(137, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:39:37'),
(138, 6, 'AI 4 regains initiative.', '2026-04-09 00:39:37'),
(139, 6, 'AI 4 played Earth 4.', '2026-04-09 00:39:38'),
(140, 6, 'ZaccyBoi played +4 Wild → Lightning.', '2026-04-09 00:39:41'),
(141, 6, 'AI 2 passed and drew 4 card(s).', '2026-04-09 00:39:42'),
(142, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:39:43'),
(143, 6, 'AI 4 played Earth 5.', '2026-04-09 00:39:44'),
(144, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:39:46'),
(145, 6, 'AI 2 played Wind 5.', '2026-04-09 00:39:47'),
(146, 6, 'AI 3 played Wood 8.', '2026-04-09 00:39:48'),
(147, 6, 'AI 4 played Fire 3.', '2026-04-09 00:39:49'),
(148, 6, 'ZaccyBoi played Water 2.', '2026-04-09 00:39:51'),
(149, 6, 'AI 2 played Lightning 5.', '2026-04-09 00:39:53'),
(150, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:39:54'),
(151, 6, 'AI 4 passed and drew 1 card.', '2026-04-09 00:39:55'),
(152, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:39:57'),
(153, 6, 'AI 2 regains initiative.', '2026-04-09 00:39:57'),
(154, 6, 'AI 2 played Earth 9.', '2026-04-09 00:39:58'),
(155, 6, 'AI 3 played +2 Wind.', '2026-04-09 00:39:59'),
(156, 6, 'AI 4 passed and drew 2 card(s).', '2026-04-09 00:40:00'),
(157, 6, 'ZaccyBoi played Wood 3.', '2026-04-09 00:40:02'),
(158, 6, 'AI 2 played Fire 10.', '2026-04-09 00:40:03'),
(159, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:40:04'),
(160, 6, 'AI 4 played Water 1.', '2026-04-09 00:40:05'),
(161, 6, 'ZaccyBoi played Lightning 1.', '2026-04-09 00:40:08'),
(162, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:40:09'),
(163, 6, 'AI 3 played Earth 10.', '2026-04-09 00:40:10'),
(164, 6, 'AI 4 played Wind 3.', '2026-04-09 00:40:11'),
(165, 6, 'ZaccyBoi played Wood 4.', '2026-04-09 00:40:13'),
(166, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:40:14'),
(167, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:40:15'),
(168, 6, 'AI 4 played Fire 6.', '2026-04-09 00:40:16'),
(169, 6, 'ZaccyBoi played Water 8.', '2026-04-09 00:43:30'),
(170, 6, 'AI 2 played Lightning 8.', '2026-04-09 00:43:32'),
(171, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:43:33'),
(172, 6, 'AI 4 passed and drew 1 card.', '2026-04-09 00:43:34'),
(173, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:43:38'),
(174, 6, 'AI 2 regains initiative.', '2026-04-09 00:43:38'),
(175, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:43:39'),
(176, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:43:40'),
(177, 6, 'AI 4 passed and drew 1 card.', '2026-04-09 00:43:41'),
(178, 6, 'AI 2 regains initiative.', '2026-04-09 00:43:41'),
(179, 6, 'AI 2 played Earth 6.', '2026-04-09 00:43:42'),
(180, 6, 'AI 3 played Wind 5.', '2026-04-09 00:43:43'),
(181, 6, 'AI 4 played Wood 5.', '2026-04-09 00:43:44'),
(182, 6, 'ZaccyBoi played Fire 1.', '2026-04-09 00:43:48'),
(183, 6, 'AI 2 played Water 7.', '2026-04-09 00:43:50'),
(184, 6, 'AI 3 played Lightning 6.', '2026-04-09 00:43:51'),
(185, 6, 'AI 4 passed and drew 1 card.', '2026-04-09 00:43:52'),
(186, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:43:55'),
(187, 6, 'AI 2 passed and drew 1 card.', '2026-04-09 00:43:57'),
(188, 6, 'AI 3 regains initiative.', '2026-04-09 00:43:57'),
(189, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:43:58'),
(190, 6, 'AI 4 passed and drew 1 card.', '2026-04-09 00:43:59'),
(191, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:44:11'),
(192, 6, 'AI 3 regains initiative.', '2026-04-09 00:44:11'),
(193, 6, 'AI 3 passed and drew 1 card.', '2026-04-09 00:44:12'),
(194, 6, 'AI 4 passed and drew 1 card.', '2026-04-09 00:44:13'),
(195, 6, 'ZaccyBoi passed and drew 1 card.', '2026-04-09 00:44:43'),
(196, 6, 'AI 3 regains initiative.', '2026-04-09 00:44:43'),
(197, 6, 'AI 3 played Earth 8.', '2026-04-09 00:44:45'),
(198, 6, 'AI 4 played Wind 4.', '2026-04-09 00:44:46');

-- --------------------------------------------------------

--
-- Table structure for table `game_player_hands`
--

CREATE TABLE `game_player_hands` (
  `id` bigint UNSIGNED NOT NULL,
  `room_id` bigint UNSIGNED NOT NULL,
  `seat_no` tinyint UNSIGNED NOT NULL,
  `hand_json` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `game_player_hands`
--

INSERT INTO `game_player_hands` (`id`, `room_id`, `seat_no`, `hand_json`, `created_at`, `updated_at`) VALUES
(21, 6, 1, '[{\"id\":\"9204672b9188\",\"kind\":\"normal\",\"element\":\"Fire\",\"value\":8,\"name\":\"Fire 8\"},{\"id\":\"38e0e9a2ecec\",\"kind\":\"normal\",\"element\":\"Lightning\",\"value\":5,\"name\":\"Lightning 5\"},{\"id\":\"cacc002bf49e\",\"kind\":\"normal\",\"element\":\"Water\",\"value\":5,\"name\":\"Water 5\"},{\"id\":\"cb1a38fea237\",\"kind\":\"normal\",\"element\":\"Wood\",\"value\":6,\"name\":\"Wood 6\"},{\"id\":\"0dbc7626156d\",\"kind\":\"normal\",\"element\":\"Fire\",\"value\":5,\"name\":\"Fire 5\"}]', '2026-04-09 00:35:49', '2026-04-09 00:44:43'),
(22, 6, 2, '[{\"id\":\"53aec7c3145a\",\"kind\":\"plus2\",\"element\":\"Wood\",\"value\":null,\"name\":\"+2 Wood\"},{\"id\":\"95b9bd19e10d\",\"kind\":\"normal\",\"element\":\"Wood\",\"value\":8,\"name\":\"Wood 8\"},{\"id\":\"e13c35da99ed\",\"kind\":\"normal\",\"element\":\"Fire\",\"value\":2,\"name\":\"Fire 2\"},{\"id\":\"34b6f2cc4988\",\"kind\":\"plus2\",\"element\":\"Fire\",\"value\":null,\"name\":\"+2 Fire\"}]', '2026-04-09 00:35:49', '2026-04-09 00:43:57'),
(23, 6, 3, '[{\"id\":\"e9143422f20d\",\"kind\":\"normal\",\"element\":\"Wood\",\"value\":1,\"name\":\"Wood 1\"},{\"id\":\"ab22ca2e274a\",\"kind\":\"normal\",\"element\":\"Fire\",\"value\":7,\"name\":\"Fire 7\"},{\"id\":\"c319f32acdd4\",\"kind\":\"normal\",\"element\":\"Wind\",\"value\":8,\"name\":\"Wind 8\"},{\"id\":\"7a4ae95f1b44\",\"kind\":\"normal\",\"element\":\"Water\",\"value\":4,\"name\":\"Water 4\"}]', '2026-04-09 00:35:49', '2026-04-09 00:44:45'),
(24, 6, 4, '[{\"id\":\"7e77b748dfbc\",\"kind\":\"normal\",\"element\":\"Water\",\"value\":6,\"name\":\"Water 6\"},{\"id\":\"13ad0050bc31\",\"kind\":\"normal\",\"element\":\"Wood\",\"value\":10,\"name\":\"Wood 10\"},{\"id\":\"7991d41690b8\",\"kind\":\"normal\",\"element\":\"Wind\",\"value\":10,\"name\":\"Wind 10\"},{\"id\":\"9079c4079600\",\"kind\":\"normal\",\"element\":\"Lightning\",\"value\":10,\"name\":\"Lightning 10\"},{\"id\":\"a842c50f2e83\",\"kind\":\"normal\",\"element\":\"Wind\",\"value\":9,\"name\":\"Wind 9\"},{\"id\":\"dc4d5e733e5f\",\"kind\":\"normal\",\"element\":\"Lightning\",\"value\":4,\"name\":\"Lightning 4\"},{\"id\":\"0f29aca6782d\",\"kind\":\"normal\",\"element\":\"Lightning\",\"value\":8,\"name\":\"Lightning 8\"},{\"id\":\"bf95083dd579\",\"kind\":\"normal\",\"element\":\"Fire\",\"value\":8,\"name\":\"Fire 8\"},{\"id\":\"ca168bdbc4ea\",\"kind\":\"normal\",\"element\":\"Wood\",\"value\":9,\"name\":\"Wood 9\"},{\"id\":\"f5a045f90b22\",\"kind\":\"normal\",\"element\":\"Wind\",\"value\":8,\"name\":\"Wind 8\"},{\"id\":\"b929b1582d15\",\"kind\":\"normal\",\"element\":\"Water\",\"value\":9,\"name\":\"Water 9\"},{\"id\":\"98fde9cecf0b\",\"kind\":\"normal\",\"element\":\"Fire\",\"value\":9,\"name\":\"Fire 9\"},{\"id\":\"f0228e17453d\",\"kind\":\"plus4\",\"element\":\"Wild\",\"value\":null,\"name\":\"+4 Wild\",\"chosenElement\":\"Water\"}]', '2026-04-09 00:35:49', '2026-04-09 00:44:46');

-- --------------------------------------------------------

--
-- Table structure for table `game_rooms`
--

CREATE TABLE `game_rooms` (
  `id` bigint UNSIGNED NOT NULL,
  `room_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_name` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_type` enum('custom','solo','casual','ranked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'custom',
  `visibility` enum('private','public') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'private',
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('waiting','playing','finished','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `max_players` tinyint UNSIGNED NOT NULL DEFAULT '4',
  `created_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `host_user_id` bigint UNSIGNED DEFAULT NULL,
  `current_turn_seat` tinyint UNSIGNED DEFAULT NULL,
  `lead_seat` tinyint UNSIGNED DEFAULT NULL,
  `last_played_seat` tinyint UNSIGNED DEFAULT NULL,
  `winner_seat` tinyint UNSIGNED DEFAULT NULL,
  `active_card_json` longtext COLLATE utf8mb4_unicode_ci,
  `active_element` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pending_draw` int NOT NULL DEFAULT '0',
  `pass_count` int NOT NULL DEFAULT '0',
  `draw_pile_json` longtext COLLATE utf8mb4_unicode_ci,
  `discard_pile_json` longtext COLLATE utf8mb4_unicode_ci,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `game_rooms`
--

INSERT INTO `game_rooms` (`id`, `room_code`, `room_name`, `room_type`, `visibility`, `password_hash`, `status`, `max_players`, `created_by_user_id`, `host_user_id`, `current_turn_seat`, `lead_seat`, `last_played_seat`, `winner_seat`, `active_card_json`, `active_element`, `pending_draw`, `pass_count`, `draw_pile_json`, `discard_pile_json`, `started_at`, `finished_at`, `created_at`, `updated_at`) VALUES
(1, '5QUG4QH2', 'Test', 'custom', 'private', NULL, 'waiting', 4, 12, 12, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, '2026-04-09 00:13:21', '2026-04-09 00:13:21'),
(2, 'XPKUMQ8P', 'Test', 'custom', 'private', NULL, 'waiting', 4, 12, 12, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, '2026-04-09 00:13:48', '2026-04-09 00:13:48'),
(3, 'BZWJN97A', 'Test', 'custom', 'private', NULL, 'waiting', 4, 12, 12, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, '2026-04-09 00:15:59', '2026-04-09 00:16:18'),
(4, 'YMKC3TFU', 'Test', 'custom', 'private', NULL, 'waiting', 4, 12, 12, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, '2026-04-09 00:27:20', '2026-04-09 00:27:20'),
(5, 'USUWFE98', 'Test', 'custom', 'private', NULL, 'waiting', 4, 12, 12, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, '2026-04-09 00:31:04', '2026-04-09 00:31:04'),
(6, 'WNGUHXTZ', 'Test', 'custom', 'private', NULL, 'playing', 4, 12, 12, 1, 4, 4, NULL, '{\"id\":\"d9d2909332c1\",\"kind\":\"normal\",\"element\":\"Wind\",\"value\":4,\"name\":\"Wind 4\"}', 'Wind', 0, 0, '[{\"id\":\"0ce67cd18b18\",\"kind\":\"normal\",\"element\":\"Wind\",\"value\":1,\"name\":\"Wind 1\"},{\"id\":\"3f173fe8768b\",\"kind\":\"normal\",\"element\":\"Earth\",\"value\":7,\"name\":\"Earth 7\"},{\"id\":\"53e721e1b54e\",\"kind\":\"normal\",\"element\":\"Lightning\",\"value\":2,\"name\":\"Lightning 2\"},{\"id\":\"d47bd09467f6\",\"kind\":\"normal\",\"element\":\"Earth\",\"value\":8,\"name\":\"Earth 8\"},{\"id\":\"be0ad8b61313\",\"kind\":\"normal\",\"element\":\"Lightning\",\"value\":9,\"name\":\"Lightning 9\"},{\"id\":\"1b8f586a7b59\",\"kind\":\"plus4\",\"element\":\"Wild\",\"value\":null,\"name\":\"+4 Wild\",\"chosenElement\":\"Water\"},{\"id\":\"0cc15491b8fb\",\"kind\":\"normal\",\"element\":\"Earth\",\"value\":3,\"name\":\"Earth 3\"},{\"id\":\"c037465e77be\",\"kind\":\"normal\",\"element\":\"Earth\",\"value\":2,\"name\":\"Earth 2\"},{\"id\":\"bddf10597c53\",\"kind\":\"normal\",\"element\":\"Lightning\",\"value\":7,\"name\":\"Lightning 7\"},{\"id\":\"81d4d5db83d9\",\"kind\":\"normal\",\"element\":\"Wind\",\"value\":7,\"name\":\"Wind 7\"},{\"id\":\"d1d313e33bd8\",\"kind\":\"normal\",\"element\":\"Water\",\"value\":5,\"name\":\"Water 5\"},{\"id\":\"1e9390deda73\",\"kind\":\"normal\",\"element\":\"Water\",\"value\":10,\"name\":\"Water 10\"},{\"id\":\"cb5b1c06bda1\",\"kind\":\"plus4\",\"element\":\"Wild\",\"value\":null,\"name\":\"+4 Wild\",\"chosenElement\":\"Water\"},{\"id\":\"2fc4df35ac11\",\"kind\":\"normal\",\"element\":\"Wood\",\"value\":5,\"name\":\"Wood 5\"},{\"id\":\"89b381e55e68\",\"kind\":\"plus2\",\"element\":\"Earth\",\"value\":null,\"name\":\"+2 Earth\"},{\"id\":\"381b685447c5\",\"kind\":\"normal\",\"element\":\"Wood\",\"value\":7,\"name\":\"Wood 7\"},{\"id\":\"1e8257c7def6\",\"kind\":\"normal\",\"element\":\"Water\",\"value\":3,\"name\":\"Water 3\"},{\"id\":\"4070813a3717\",\"kind\":\"normal\",\"element\":\"Fire\",\"value\":5,\"name\":\"Fire 5\"},{\"id\":\"55c49dbb63a9\",\"kind\":\"normal\",\"element\":\"Wind\",\"value\":6,\"name\":\"Wind 6\"},{\"id\":\"74ca136c076e\",\"kind\":\"normal\",\"element\":\"Earth\",\"value\":1,\"name\":\"Earth 1\"}]', '[{\"id\":\"f04dbefbc283\",\"kind\":\"plus2\",\"element\":\"Water\",\"value\":null,\"name\":\"+2 Water\"},{\"id\":\"e741c44ae8d9\",\"kind\":\"plus2\",\"element\":\"Lightning\",\"value\":null,\"name\":\"+2 Lightning\"},{\"id\":\"2d9e77f0060e\",\"kind\":\"normal\",\"element\":\"Earth\",\"value\":5,\"name\":\"Earth 5\"},{\"id\":\"082abb4cf2e2\",\"kind\":\"normal\",\"element\":\"Wind\",\"value\":2,\"name\":\"Wind 2\"},{\"id\":\"e5985cd899d0\",\"kind\":\"normal\",\"element\":\"Wood\",\"value\":2,\"name\":\"Wood 2\"},{\"id\":\"b854c283a53d\",\"kind\":\"normal\",\"element\":\"Fire\",\"value\":4,\"name\":\"Fire 4\"},{\"id\":\"04f363f0f240\",\"kind\":\"normal\",\"element\":\"Water\",\"value\":8,\"name\":\"Water 8\"},{\"id\":\"5882e478927e\",\"kind\":\"normal\",\"element\":\"Lightning\",\"value\":3,\"name\":\"Lightning 3\"},{\"id\":\"52de404b1e1d\",\"kind\":\"normal\",\"element\":\"Earth\",\"value\":4,\"name\":\"Earth 4\"},{\"id\":\"95faf3bf926f\",\"kind\":\"plus4\",\"element\":\"Wild\",\"value\":null,\"name\":\"+4 Wild\",\"chosenElement\":\"Lightning\"},{\"id\":\"0826d0785ebe\",\"kind\":\"normal\",\"element\":\"Earth\",\"value\":5,\"name\":\"Earth 5\"},{\"id\":\"9f73486bcac8\",\"kind\":\"normal\",\"element\":\"Wind\",\"value\":5,\"name\":\"Wind 5\"},{\"id\":\"861a6481defd\",\"kind\":\"normal\",\"element\":\"Wood\",\"value\":8,\"name\":\"Wood 8\"},{\"id\":\"5aa8829f7574\",\"kind\":\"normal\",\"element\":\"Fire\",\"value\":3,\"name\":\"Fire 3\"},{\"id\":\"798ce1361f4b\",\"kind\":\"normal\",\"element\":\"Water\",\"value\":2,\"name\":\"Water 2\"},{\"id\":\"6fdea11a4d8d\",\"kind\":\"normal\",\"element\":\"Lightning\",\"value\":5,\"name\":\"Lightning 5\"},{\"id\":\"6b4907d6c8ba\",\"kind\":\"normal\",\"element\":\"Earth\",\"value\":9,\"name\":\"Earth 9\"},{\"id\":\"2003ac1c7f82\",\"kind\":\"plus2\",\"element\":\"Wind\",\"value\":null,\"name\":\"+2 Wind\"},{\"id\":\"589f5d5c7327\",\"kind\":\"normal\",\"element\":\"Wood\",\"value\":3,\"name\":\"Wood 3\"},{\"id\":\"47b3c6e9e31c\",\"kind\":\"normal\",\"element\":\"Fire\",\"value\":10,\"name\":\"Fire 10\"},{\"id\":\"bc8aec473983\",\"kind\":\"normal\",\"element\":\"Water\",\"value\":1,\"name\":\"Water 1\"},{\"id\":\"584368f44225\",\"kind\":\"normal\",\"element\":\"Lightning\",\"value\":1,\"name\":\"Lightning 1\"},{\"id\":\"d6d3ded3069d\",\"kind\":\"normal\",\"element\":\"Earth\",\"value\":10,\"name\":\"Earth 10\"},{\"id\":\"79dd6908eff5\",\"kind\":\"normal\",\"element\":\"Wind\",\"value\":3,\"name\":\"Wind 3\"},{\"id\":\"ad7eaed7b740\",\"kind\":\"normal\",\"element\":\"Wood\",\"value\":4,\"name\":\"Wood 4\"},{\"id\":\"aa796bf118ca\",\"kind\":\"normal\",\"element\":\"Fire\",\"value\":6,\"name\":\"Fire 6\"},{\"id\":\"4fc23a1137e3\",\"kind\":\"normal\",\"element\":\"Water\",\"value\":8,\"name\":\"Water 8\"},{\"id\":\"a8436f59f181\",\"kind\":\"normal\",\"element\":\"Lightning\",\"value\":8,\"name\":\"Lightning 8\"},{\"id\":\"f57e3b463f24\",\"kind\":\"normal\",\"element\":\"Earth\",\"value\":6,\"name\":\"Earth 6\"},{\"id\":\"34170a1e3b17\",\"kind\":\"normal\",\"element\":\"Wind\",\"value\":5,\"name\":\"Wind 5\"},{\"id\":\"1f1981e2e029\",\"kind\":\"normal\",\"element\":\"Wood\",\"value\":5,\"name\":\"Wood 5\"},{\"id\":\"6adfc0577f8d\",\"kind\":\"normal\",\"element\":\"Fire\",\"value\":1,\"name\":\"Fire 1\"},{\"id\":\"456cd7808455\",\"kind\":\"normal\",\"element\":\"Water\",\"value\":7,\"name\":\"Water 7\"},{\"id\":\"31dc7e11d056\",\"kind\":\"normal\",\"element\":\"Lightning\",\"value\":6,\"name\":\"Lightning 6\"},{\"id\":\"4b54942d197d\",\"kind\":\"normal\",\"element\":\"Earth\",\"value\":8,\"name\":\"Earth 8\"},{\"id\":\"d9d2909332c1\",\"kind\":\"normal\",\"element\":\"Wind\",\"value\":4,\"name\":\"Wind 4\"}]', '2026-04-08 18:35:49', NULL, '2026-04-09 00:35:46', '2026-04-09 00:44:46');

-- --------------------------------------------------------

--
-- Table structure for table `game_room_players`
--

CREATE TABLE `game_room_players` (
  `id` bigint UNSIGNED NOT NULL,
  `room_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `seat_no` tinyint UNSIGNED NOT NULL,
  `player_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `player_type` enum('human','ai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'human',
  `is_host` tinyint(1) NOT NULL DEFAULT '0',
  `connected_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `game_room_players`
--

INSERT INTO `game_room_players` (`id`, `room_id`, `user_id`, `seat_no`, `player_name`, `player_type`, `is_host`, `connected_at`, `last_seen_at`) VALUES
(1, 1, 12, 1, 'ZaccyBoi', 'human', 1, '2026-04-09 00:13:21', '2026-04-09 00:13:21'),
(2, 2, 12, 1, 'ZaccyBoi', 'human', 1, '2026-04-09 00:13:48', '2026-04-09 00:14:34'),
(3, 3, 12, 1, 'ZaccyBoi', 'human', 1, '2026-04-09 00:15:59', '2026-04-09 00:25:28'),
(13, 4, 12, 1, 'ZaccyBoi', 'human', 1, '2026-04-09 00:27:20', '2026-04-09 00:30:59'),
(17, 5, 12, 1, 'ZaccyBoi', 'human', 1, '2026-04-09 00:31:04', '2026-04-09 00:35:41'),
(21, 6, 12, 1, 'ZaccyBoi', 'human', 1, '2026-04-09 00:35:46', '2026-04-09 00:47:39'),
(22, 6, NULL, 2, 'AI 2', 'ai', 0, '2026-04-09 00:35:49', '2026-04-09 00:35:49'),
(23, 6, NULL, 3, 'AI 3', 'ai', 0, '2026-04-09 00:35:49', '2026-04-09 00:35:49'),
(24, 6, NULL, 4, 'AI 4', 'ai', 0, '2026-04-09 00:35:49', '2026-04-09 00:35:49');

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
(63, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-16 15:34:22'),
(64, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-16 19:39:30'),
(65, 3, 'crispino.zyrus@gmail.com', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', NULL, '2026-03-16 19:46:20'),
(66, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', NULL, '2026-04-07 19:28:20'),
(67, NULL, 'user@liftright.local', 0, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', 'wrong_credentials', '2026-04-09 00:13:06'),
(68, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', NULL, '2026-04-09 00:13:11'),
(69, 1, 'admin', 0, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', 'wrong_credentials', '2026-04-09 01:30:52'),
(70, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', NULL, '2026-04-09 01:37:39'),
(71, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', NULL, '2026-04-09 01:37:45'),
(72, NULL, 'user@liftright.local', 0, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', 'wrong_credentials', '2026-04-10 12:30:18'),
(73, 12, 'ZaccyBoi', 1, 0x00000000000000000000000000000001, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', NULL, '2026-04-10 12:30:23');

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
(7, 1, 12, 'Test', '2026-03-13 19:51:40'),
(8, 1, 3, 'Oy kelan ka pa nagkapera what the fuck', '2026-03-16 19:40:05'),
(9, 1, 3, 'Pahingi naman o', '2026-03-16 19:40:10'),
(10, 1, 3, 'ayaw mong maglaro kasi e', '2026-03-16 19:40:15');

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
(1, 'admin', 'admin@game.local', '$2y$10$axxzhPXLJmTV4gHq9p2Pq.3nUk.0SJpmAwMoyF4w4PZM/0/LTkVoy', 'Admin', '2026-03-01 16:59:26', 1, 0, NULL, 'approved', NULL, NULL, NULL, 1, 0, NULL, 'none', NULL, 'default', '2026-03-05 18:52:17', '2026-03-01 16:59:26', '2026-04-09 01:30:52', 1, 0, 100, 0, 0, 0, NULL, NULL, NULL, NULL),
(3, 'ZacZac460', 'crispino.zyrus@gmail.com', '$2y$10$u6W3WqPs.JGt1LjIQBD4b.HVRMQ1nrnlMgTb5TN8cXU/6AzkclgR2', 'ZacZac460', NULL, 0, 0, NULL, 'approved', 1, '2026-03-01 17:28:37', NULL, 1, 0, NULL, 'none', NULL, 'default', '2026-03-16 19:46:20', '2026-03-01 17:28:29', '2026-03-16 19:46:20', 1, 0, 100, 250, 0, 0, NULL, 'uploads/avatars/avatar_3_1773661284.png', NULL, NULL),
(10, 'guest_AAF4CE60', 'guest_AAF4CE60@guest.local', '', 'Guest', NULL, 0, 0, NULL, 'approved', NULL, NULL, NULL, 1, 1, NULL, 'none', NULL, 'default', NULL, '2026-03-11 20:46:41', '2026-03-11 20:46:41', 1, 0, 100, 0, 0, 0, NULL, NULL, NULL, NULL),
(11, 'guest_1BAD5D10', 'guest_1BAD5D10@guest.local', '', 'Guest', NULL, 0, 0, NULL, 'approved', NULL, NULL, NULL, 1, 1, NULL, 'none', NULL, 'default', NULL, '2026-03-12 21:53:53', '2026-03-12 21:53:53', 1, 0, 100, 0, 0, 0, NULL, NULL, NULL, NULL),
(12, 'ZaccyBoi', 'zacgames.tv@gmail.com', '$2y$10$q5zfGSrYB0rG8Ixt68UKvOxkamcHf4g3MU9rBjW2UDQA8e/xowYxa', 'ZaccyBoi', '2026-03-16 17:32:00', 0, 0, NULL, 'approved', 1, '2026-03-13 17:01:47', NULL, 1, 0, NULL, 'none', NULL, 'light', '2026-04-10 12:30:23', '2026-03-13 17:00:45', '2026-04-10 12:30:36', 1, 0, 100, 250, 0, 0, 'Testing', 'uploads/avatars/avatar_12_1773654943.png', '', ''),
(13, 'guest_876B308C', 'guest_876B308C@guest.local', '', 'Guest', NULL, 0, 0, NULL, 'approved', NULL, NULL, NULL, 1, 1, NULL, 'none', NULL, 'default', NULL, '2026-03-16 14:25:24', '2026-03-16 14:25:24', 1, 0, 100, 0, 0, 0, NULL, NULL, NULL, NULL),
(14, 'guest_7FB8C1DF', 'guest_7FB8C1DF@guest.local', '', 'Guest', NULL, 0, 0, NULL, 'approved', NULL, NULL, NULL, 1, 1, NULL, 'none', NULL, 'default', NULL, '2026-03-16 19:46:15', '2026-03-16 19:46:15', 1, 0, 100, 0, 0, 0, NULL, NULL, NULL, NULL),
(15, 'guest_B799E5FB', 'guest_B799E5FB@guest.local', '', 'Guest', NULL, 0, 0, NULL, 'approved', NULL, NULL, NULL, 1, 1, NULL, 'none', NULL, 'default', NULL, '2026-04-07 19:27:55', '2026-04-07 19:27:55', 1, 0, 100, 0, 0, 0, NULL, NULL, NULL, NULL),
(16, 'guest_E80E8149', 'guest_E80E8149@guest.local', '', 'Guest', NULL, 0, 0, NULL, 'approved', NULL, NULL, NULL, 1, 1, NULL, 'none', NULL, 'light', NULL, '2026-04-10 12:24:39', '2026-04-10 12:25:13', 1, 0, 100, 0, 0, 0, NULL, NULL, NULL, NULL);

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
(13, 3, NULL, '2026-03-16 14:25:24'),
(14, 3, NULL, '2026-03-16 19:46:15'),
(15, 3, NULL, '2026-04-07 19:27:55'),
(16, 3, NULL, '2026-04-10 12:24:39');

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
-- Indexes for table `credit_topups`
--
ALTER TABLE `credit_topups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_credit_topups_checkout_id` (`paymongo_checkout_id`),
  ADD UNIQUE KEY `uq_credit_topups_reference` (`reference_number`),
  ADD KEY `idx_credit_topups_user_id` (`user_id`),
  ADD KEY `idx_credit_topups_status` (`status`);

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
-- Indexes for table `game_logs`
--
ALTER TABLE `game_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_game_logs_room_id` (`room_id`,`id`),
  ADD KEY `idx_game_logs_created_at` (`created_at`);

--
-- Indexes for table `game_player_hands`
--
ALTER TABLE `game_player_hands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_game_player_hands_room_seat` (`room_id`,`seat_no`),
  ADD KEY `idx_game_player_hands_room_id` (`room_id`);

--
-- Indexes for table `game_rooms`
--
ALTER TABLE `game_rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_game_rooms_room_code` (`room_code`),
  ADD KEY `idx_game_rooms_status` (`status`),
  ADD KEY `idx_game_rooms_room_type` (`room_type`),
  ADD KEY `idx_game_rooms_visibility` (`visibility`),
  ADD KEY `idx_game_rooms_host_user_id` (`host_user_id`),
  ADD KEY `idx_game_rooms_created_by_user_id` (`created_by_user_id`),
  ADD KEY `idx_game_rooms_created_at` (`created_at`);

--
-- Indexes for table `game_room_players`
--
ALTER TABLE `game_room_players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_game_room_players_room_seat` (`room_id`,`seat_no`),
  ADD UNIQUE KEY `uq_game_room_players_room_user` (`room_id`,`user_id`),
  ADD KEY `idx_game_room_players_room_id` (`room_id`),
  ADD KEY `idx_game_room_players_user_id` (`user_id`),
  ADD KEY `idx_game_room_players_player_type` (`player_type`);

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `auth_sessions`
--
ALTER TABLE `auth_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

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
-- AUTO_INCREMENT for table `credit_topups`
--
ALTER TABLE `credit_topups`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dashboard_notifications`
--
ALTER TABLE `dashboard_notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
-- AUTO_INCREMENT for table `game_logs`
--
ALTER TABLE `game_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT for table `game_player_hands`
--
ALTER TABLE `game_player_hands`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- AUTO_INCREMENT for table `game_rooms`
--
ALTER TABLE `game_rooms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `game_room_players`
--
ALTER TABLE `game_room_players`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
-- Constraints for table `credit_topups`
--
ALTER TABLE `credit_topups`
  ADD CONSTRAINT `fk_credit_topups_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `game_logs`
--
ALTER TABLE `game_logs`
  ADD CONSTRAINT `fk_game_logs_room` FOREIGN KEY (`room_id`) REFERENCES `game_rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `game_player_hands`
--
ALTER TABLE `game_player_hands`
  ADD CONSTRAINT `fk_game_player_hands_room` FOREIGN KEY (`room_id`) REFERENCES `game_rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `game_rooms`
--
ALTER TABLE `game_rooms`
  ADD CONSTRAINT `fk_game_rooms_created_by_user` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_game_rooms_host_user` FOREIGN KEY (`host_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `game_room_players`
--
ALTER TABLE `game_room_players`
  ADD CONSTRAINT `fk_game_room_players_room` FOREIGN KEY (`room_id`) REFERENCES `game_rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_game_room_players_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
