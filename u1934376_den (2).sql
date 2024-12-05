-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost:3306
-- Üretim Zamanı: 05 Ara 2024, 22:39:32
-- Sunucu sürümü: 10.3.39-MariaDB-cll-lve
-- PHP Sürümü: 8.1.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `u1934376_den`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `cash_registers`
--

CREATE TABLE `cash_registers` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `register_type` enum('main','sub') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Tablo döküm verisi `cash_registers`
--

INSERT INTO `cash_registers` (`id`, `title`, `balance`, `register_type`, `description`, `created_at`, `status`) VALUES
(1, 'Ana Kasa', 0.00, 'main', NULL, '2024-11-29 01:02:35', 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `type` enum('income','expense') NOT NULL DEFAULT 'expense'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `categories`
--

INSERT INTO `categories` (`id`, `title`, `type`) VALUES
(45, '38AHM097 YAKIT GİDERİ', 'expense'),
(44, 'SAHİBİNDEN.COM / EMLAK', 'expense'),
(43, 'SAHİBİNDEN.COM / GALERİ', 'expense'),
(42, 'GEÇEN AYDAN DEVİR', 'income'),
(46, '21AGD438 YAKIT GİDERİ', 'expense'),
(47, 'VERGİ/KURUM ÖDEMELERİ', 'expense'),
(48, 'OFİS GİDERLERİ', 'expense'),
(49, 'EV GİDERLERİ', 'expense'),
(50, 'MARKET GİDERLERİ', 'expense'),
(51, 'ARAÇ MASRAFLARI', 'expense'),
(52, 'TELEFON/SU/ELEKTİRİK GİDERLERİ', 'expense'),
(53, 'KİRA GİDERLERİ', 'expense'),
(54, 'MEHMET ERSİN TÜRK', 'expense'),
(55, 'ENES TÜRK', 'expense'),
(56, 'KOMİSYON GELİRİ', 'income');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `credit_cards`
--

CREATE TABLE `credit_cards` (
  `id` int(11) NOT NULL,
  `bank_account_id` int(11) DEFAULT NULL,
  `card_name` varchar(100) NOT NULL,
  `card_number` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `credit_limit` decimal(15,2) DEFAULT NULL,
  `available_limit` decimal(15,2) DEFAULT NULL,
  `current_debt` decimal(15,2) DEFAULT 0.00,
  `payment_day` int(11) NOT NULL,
  `due_date` date DEFAULT NULL,
  `minimum_payment` decimal(15,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `phone` text DEFAULT NULL,
  `email` text DEFAULT NULL,
  `tax_no` text DEFAULT NULL,
  `tax_office` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `customers`
--

INSERT INTO `customers` (`id`, `title`, `phone`, `email`, `tax_no`, `tax_office`, `address`, `status`) VALUES
(22, 'MEHMET ERSİN TÜRK', '05323460821', NULL, NULL, NULL, NULL, 1),
(21, 'ENES TÜRK', '05326590422', NULL, NULL, NULL, NULL, 1),
(23, 'SAHİBİNDEN.COM', NULL, NULL, NULL, NULL, NULL, 1),
(24, 'ERSİN EMLAK', NULL, NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `finances`
--

CREATE TABLE `finances` (
  `id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `method_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `event_type` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `adddate` datetime NOT NULL DEFAULT current_timestamp(),
  `is_recurring` tinyint(1) DEFAULT 0,
  `recurring_start_date` date DEFAULT NULL,
  `recurring_months` int(11) DEFAULT NULL,
  `parent_recurring_id` int(11) DEFAULT NULL,
  `recurring_count` int(11) DEFAULT NULL,
  `safe_id` int(11) DEFAULT NULL,
  `payment_source_type` varchar(20) DEFAULT NULL,
  `payment_source_id` int(11) DEFAULT NULL,
  `payment_status` tinyint(1) DEFAULT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `ai_categorized` tinyint(1) DEFAULT 0,
  `ai_confidence` decimal(5,2) DEFAULT 0.00,
  `ai_matched_keywords` text DEFAULT NULL,
  `ai_suggested_categories` text DEFAULT NULL,
  `ai_analysis_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `processed` tinyint(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `finances`
--

INSERT INTO `finances` (`id`, `u_id`, `customer_id`, `category_id`, `method_id`, `price`, `event_type`, `description`, `adddate`, `is_recurring`, `recurring_start_date`, `recurring_months`, `parent_recurring_id`, `recurring_count`, `safe_id`, `payment_source_type`, `payment_source_id`, `payment_status`, `transaction_id`, `ai_categorized`, `ai_confidence`, `ai_matched_keywords`, `ai_suggested_categories`, `ai_analysis_data`, `processed`) VALUES
(123, 1, 21, 42, 0, 18476.00, 1, 'HESAP BAŞLANGICI', '2024-12-03 23:49:24', 0, NULL, NULL, NULL, NULL, 59, NULL, NULL, NULL, NULL, 0, 0.00, NULL, NULL, NULL, 0),
(122, 1, 21, 42, 0, 55435.00, 1, 'GEÇEN AYDAN DEVİR', '2024-12-03 23:48:04', 0, NULL, NULL, NULL, NULL, 60, NULL, NULL, NULL, NULL, 0, 0.00, NULL, NULL, NULL, 0),
(132, 1, 22, 46, 0, 1000.00, 0, '', '2024-12-05 15:33:12', 1, '2024-12-05', 1, NULL, 12, 59, NULL, NULL, NULL, NULL, 0, 0.00, NULL, NULL, NULL, 1),
(133, 1, 21, 49, 0, 1000.00, 0, '', '2024-12-05 15:33:51', 1, '2024-12-05', 1, NULL, 12, 60, NULL, NULL, NULL, NULL, 0, 0.00, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `methods`
--

CREATE TABLE `methods` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `safe_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `methods`
--

INSERT INTO `methods` (`id`, `title`, `safe_id`) VALUES
(1, 'Kredi Kartı', NULL),
(2, 'Nakit', NULL),
(3, 'Çek/Senet', NULL),
(4, 'Banka Havalesi', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `pending_payments`
--

CREATE TABLE `pending_payments` (
  `id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `safe_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` datetime NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','future_payment','completed') DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `reminders`
--

CREATE TABLE `reminders` (
  `id` int(11) NOT NULL,
  `finance_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `reminder_date` date NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `safes`
--

CREATE TABLE `safes` (
  `id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(20) NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `credit_limit` decimal(10,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `statement_day` int(11) DEFAULT NULL,
  `due_day` int(11) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `safes`
--

INSERT INTO `safes` (`id`, `u_id`, `title`, `type`, `balance`, `credit_limit`, `description`, `status`, `created_at`, `statement_day`, `due_day`, `bank_name`) VALUES
(58, 1, 'NAKİT', 'cash', 0.00, 0.00, NULL, 1, '2024-12-03 23:38:02', NULL, NULL, ''),
(59, 1, 'ZİRAAT VADESİZ', 'bank_account', 17476.00, 0.00, NULL, 1, '2024-12-03 23:38:39', NULL, NULL, ''),
(60, 1, 'BANKKART GOLD', 'credit_card', 73835.00, 79150.00, NULL, 1, '2024-12-03 23:39:53', 28, 7, 'Ziraat Bankası');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `scheduled_payments`
--

CREATE TABLE `scheduled_payments` (
  `id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `safe_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `payment_date` datetime NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `finance_id` int(11) DEFAULT NULL,
  `source_type` enum('cash','bank','credit') DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `transaction_type` enum('income','expense','transfer') DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `balance_before` decimal(15,2) DEFAULT NULL,
  `balance_after` decimal(15,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` text NOT NULL,
  `phone` text NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `uniqid` text NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `fullname`, `phone`, `email`, `password`, `uniqid`, `status`) VALUES
(1, 'Enes TÜRK', '0541 373 1437', '1@1', '40f5888b67c748df7efba008e7c2f9d2', '63754e7f172f9', 1),
(9, 'MEHMET ERSİN TÜRK', '05323460821', 'ersin@gmail.com', 'f4dac52dd1f2b6bd422826fbe7347111', '67471b7533216', 1);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `cash_registers`
--
ALTER TABLE `cash_registers`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `credit_cards`
--
ALTER TABLE `credit_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bank_account_id` (`bank_account_id`);

--
-- Tablo için indeksler `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `finances`
--
ALTER TABLE `finances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `safe_id` (`safe_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Tablo için indeksler `methods`
--
ALTER TABLE `methods`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `pending_payments`
--
ALTER TABLE `pending_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `u_id` (`u_id`),
  ADD KEY `safe_id` (`safe_id`),
  ADD KEY `status` (`status`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Tablo için indeksler `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `safes`
--
ALTER TABLE `safes`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `scheduled_payments`
--
ALTER TABLE `scheduled_payments`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `finance_id` (`finance_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `cash_registers`
--
ALTER TABLE `cash_registers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- Tablo için AUTO_INCREMENT değeri `credit_cards`
--
ALTER TABLE `credit_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Tablo için AUTO_INCREMENT değeri `finances`
--
ALTER TABLE `finances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- Tablo için AUTO_INCREMENT değeri `methods`
--
ALTER TABLE `methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `pending_payments`
--
ALTER TABLE `pending_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Tablo için AUTO_INCREMENT değeri `reminders`
--
ALTER TABLE `reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Tablo için AUTO_INCREMENT değeri `safes`
--
ALTER TABLE `safes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- Tablo için AUTO_INCREMENT değeri `scheduled_payments`
--
ALTER TABLE `scheduled_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
