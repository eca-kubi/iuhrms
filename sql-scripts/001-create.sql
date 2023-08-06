CREATE DATABASE IF NOT EXISTS iuhrms;
USE iuhrms;

CREATE TABLE `users` (
                         `id` INT(11) NOT NULL AUTO_INCREMENT,
                         `first_name` VARCHAR(255) NOT NULL,
                         `last_name` VARCHAR(255) NOT NULL,
                         `email` VARCHAR(255) NOT NULL,
                         `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
                         `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         PRIMARY KEY (`id`),
                         UNIQUE (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `room_types` (
                              `id` INT(11) NOT NULL AUTO_INCREMENT,
                              `type` VARCHAR(255) NOT NULL,
                              `price` DECIMAL(10,2) NOT NULL,
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `hostels` (
                           `id` INT(11) NOT NULL AUTO_INCREMENT,
                           `name` VARCHAR(255) NOT NULL,
                           `description` TEXT NOT NULL,
                           `total_rooms` INT(11) NOT NULL,
                           `occupied_rooms` INT(11) NOT NULL,
                           `location` VARCHAR(255) NOT NULL,
                           `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                           `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                           PRIMARY KEY (`id`),
                           CONSTRAINT chk_rooms CHECK (occupied_rooms <= total_rooms)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reservations` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT,
                                `user_id` INT(11) NOT NULL,
                                `hostel_id` INT(11) NOT NULL,
                                `room_type_id` INT(11) NOT NULL,
                                `reservation_date` DATE NOT NULL,
                                `semester` VARCHAR(255) NOT NULL,
                                `status` VARCHAR(255) NOT NULL,
                                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                PRIMARY KEY (`id`),
                                FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                                FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE,
                                FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `emails` (
                          `id` INT(11) NOT NULL AUTO_INCREMENT,
                          `user_id` INT(11) NOT NULL,
                          `subject` VARCHAR(255) NOT NULL,
                          `body` TEXT NOT NULL,
                          `sent` TINYINT(1) NOT NULL DEFAULT 0,
                          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          PRIMARY KEY (`id`),
                          FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
