-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 19, 2026 at 11:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `veritick`
--

-- --------------------------------------------------------

--
-- Table structure for table `checkins`
--

CREATE TABLE `checkins` (
  `checkin_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `scanner_id` int(11) NOT NULL,
  `checkin_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `result` varchar(50) NOT NULL,
  `raw_payload` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checkins`
--

INSERT INTO `checkins` (`checkin_id`, `ticket_id`, `scanner_id`, `checkin_time`, `result`, `raw_payload`) VALUES
(5, 11, 11, '2026-03-19 04:45:39', 'OK', 'VT-2EA79D');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date` datetime NOT NULL,
  `location` varchar(255) NOT NULL,
  `status` enum('draft','published','cancelled') DEFAULT 'published',
  `total_seats` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `organizer_id`, `title`, `description`, `date`, `location`, `status`, `total_seats`, `created_at`, `image_url`) VALUES
(5, 10, 'Nikhil B\'day', 'Kjoiagejnle', '2026-08-17 10:01:00', 'KP-25', 'published', 20, '2026-03-19 04:32:15', 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=800&h=400'),
(8, 11, 'Vumika Birthday', 'i want to host a party', '2026-03-21 10:09:00', 'CAMPUS 25', 'published', 25, '2026-03-19 04:40:02', 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=800&h=400'),
(9, 14, 'SDIS', 'CODING', '2026-03-22 10:41:00', 'KIIT', 'published', 100, '2026-03-19 05:11:29', 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=800&h=400');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `status` varchar(50) NOT NULL,
  `provider_txn_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `qr_code` varchar(255) NOT NULL,
  `qr_signature` varchar(255) NOT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used` tinyint(1) DEFAULT 0,
  `used_at` datetime DEFAULT NULL,
  `used_by_scanner_id` int(11) DEFAULT NULL,
  `attendee_name` varchar(255) DEFAULT NULL,
  `attendee_contact` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`ticket_id`, `user_id`, `event_id`, `qr_code`, `qr_signature`, `issued_at`, `used`, `used_at`, `used_by_scanner_id`, `attendee_name`, `attendee_contact`) VALUES
(11, 13, 8, 'VT-2EA79D', '74bc1b7e6930b17e83481627fec4ccd5f1eb9a8990a1314661ba7201fc5b8056', '2026-03-19 04:42:10', 1, '2026-03-19 10:15:39', 11, 'Tanwi Tejeswani', '35476798'),
(12, 15, 9, 'VT-27A2BE', '484bc5867403af22f8dd973d14cc7fa7fac3bc4e11931a70aa703ec85d1800e2', '2026-03-19 05:12:10', 0, NULL, NULL, 'PRIYA', '4564565678');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `organizer_code` varchar(50) DEFAULT NULL,
  `linked_organizer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password_hash`, `role`, `organizer_code`, `linked_organizer_id`) VALUES
(8, 'Mr. India', 'n@gmail.com', '$2y$10$MBuCbDbVjGbpknpGgP72JOx43D/3mAMSuNtlWWnkZrrgAVHXrbYQ.', 'user', NULL, 1),
(9, 'madhura', 'madhura@yay.com', '$2y$10$cfzjeudEC7sePwUB6ZE56e3ZuXkXiKpt/xKt9r.Gm1jbmYt7V5daO', 'admin', 'ORG-8B53E9', NULL),
(10, 'Mr. X', 'nikhil@gmail.com', '$2y$10$9lTTaagZN698gs5LpiBjp.D1j4o6yNKcVqB0q3SV7fv6PUW9F6fEm', 'admin', 'ORG-02CF81', NULL),
(11, 'Vumika Vijayani', 'vumikavijayani@gmail.com', '$2y$10$U4muwsmf7COzv52m6l0/ROpCIN0PnErygKVyXdzIELvwTD2s3WXPG', 'admin', 'ORG-5CC708', NULL),
(12, 'PRIYA', 'archi@faah.com', '$2y$10$YOhWIgXuQjlzXIgXRXSq.eUbUSgiQcMw1S.d6bSbZ0QE9un60BI/y', 'user', NULL, 9),
(13, 'Tanwi Tejeswani', 'tanwi@gmail.com', '$2y$10$y9NsM/WY31dQf1YsAP6v.eCZhuwX5q0X1zGtOgnAwFJv.foBVjkSK', 'user', NULL, 11),
(14, 'subhadarshini', 'subh@gmail.com', '$2y$10$CtM/YZguUS0xeEHKnwnEvO0iEmsRAiMtLRywx27pWdML.In2X3tGO', 'admin', 'ORG-1CBED4', NULL),
(15, 'PRIYA', '4444@YE.COM', '$2y$10$XpDsk/o24Ci.gHBF6hKTXOWLvqOqaEUybOaaVvxuz4c21LzK2vXMO', 'user', NULL, 14);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `checkins`
--
ALTER TABLE `checkins`
  ADD PRIMARY KEY (`checkin_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `scanner_id` (`scanner_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `organizer_id` (`organizer_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `provider_txn_id` (`provider_txn_id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `used_by_scanner_id` (`used_by_scanner_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `organizer_code` (`organizer_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `checkins`
--
ALTER TABLE `checkins`
  MODIFY `checkin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `checkins`
--
ALTER TABLE `checkins`
  ADD CONSTRAINT `checkins_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `checkins_ibfk_2` FOREIGN KEY (`scanner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`used_by_scanner_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
