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

CREATE TABLE `semesters` (
                             `id` INT(11) NOT NULL AUTO_INCREMENT,
                             `name` VARCHAR(255) NOT NULL,
                             `semester_start` DATE NOT NULL,
                             `semester_end` DATE NOT NULL,
                             `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                             `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                             PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reservation_statuses` (
                                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                                        `name` VARCHAR(255) NOT NULL,
                                        PRIMARY KEY (`id`),
                                        UNIQUE (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reservations` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT,
                                `user_id` INT(11) NOT NULL,
                                `hostel_id` INT(11) NOT NULL,
                                `room_type_id` INT(11) NOT NULL,
                                `semester_id` INT(11) NOT NULL, -- Updated to reference semesters
                                `status_id` INT(11) NOT NULL DEFAULT 1, -- Updated to reference reservation_statuses
                                `reservation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                PRIMARY KEY (`id`),
                                FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                                FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE,
                                FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE,
                                FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE, -- Foreign key for semesters
                                FOREIGN KEY (`status_id`) REFERENCES `reservation_statuses` (`id`) ON DELETE RESTRICT -- Foreign key for reservation_statuses


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


-- Table to link hostels and room types
CREATE TABLE `hostel_room_types` (
                                     `hostel_id` INT(11) NOT NULL,
                                     `room_type_id` INT(11) NOT NULL,
                                     PRIMARY KEY (`hostel_id`, `room_type_id`),
                                     FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE,
                                     FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
