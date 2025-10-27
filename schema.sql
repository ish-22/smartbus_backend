-- SMARTBUS DB schema (MySQL)
-- Run: import into a MySQL database named `smartbus`

CREATE DATABASE IF NOT EXISTS `smartbus` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smartbus`;

-- Users (passengers, drivers, admins)
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(191) NOT NULL,
  `email` VARCHAR(191) UNIQUE,
  `phone` VARCHAR(50) UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('passenger','driver','admin') DEFAULT 'passenger',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buses
CREATE TABLE `buses` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `number` VARCHAR(50) NOT NULL,
  `type` ENUM('expressway','normal') NOT NULL DEFAULT 'normal',
  `route_id` BIGINT UNSIGNED NULL,
  `capacity` INT DEFAULT 50,
  `driver_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Routes
CREATE TABLE `routes` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(191) NOT NULL,
  `start_point` VARCHAR(255),
  `end_point` VARCHAR(255),
  `metadata` JSON NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Stops
CREATE TABLE `stops` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `route_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `lat` DECIMAL(10,7) NULL,
  `lng` DECIMAL(10,7) NULL,
  `sequence` INT DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  INDEX (`route_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bookings
CREATE TABLE `bookings` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `bus_id` BIGINT UNSIGNED NOT NULL,
  `route_id` BIGINT UNSIGNED NULL,
  `seat_number` VARCHAR(50) NULL,
  `ticket_category` VARCHAR(191) NULL,
  `status` ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
  `total_amount` DECIMAL(10,2) DEFAULT 0,
  `payment_method` VARCHAR(50) DEFAULT 'pay_on_bus',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  INDEX (`user_id`),
  INDEX (`bus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Feedback
CREATE TABLE `feedback` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NULL,
  `bus_id` BIGINT UNSIGNED NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `comment` TEXT,
  `created_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payments (basic)
CREATE TABLE `payments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_id` BIGINT UNSIGNED NULL,
  `amount` DECIMAL(10,2) DEFAULT 0,
  `status` ENUM('pending','completed','failed') DEFAULT 'pending',
  `gateway` VARCHAR(100) NULL,
  `meta` JSON NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Simple seed: sample expressway bus
INSERT INTO `buses` (`number`,`type`,`capacity`,`created_at`) VALUES ('EXP-1001','expressway',40,NOW());
INSERT INTO `routes` (`name`,`start_point`,`end_point`,`created_at`) VALUES ('Colombo - Kandy','Colombo','Kandy',NOW());

-- Note: add foreign keys and further constraints in Laravel migrations if desired.
