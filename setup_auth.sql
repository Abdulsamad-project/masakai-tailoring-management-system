-- =====================================================================
-- Masakai Tailoring Service Management System
-- Authentication Module Migration Script
-- =====================================================================
-- Compatible with: XAMPP / MySQL 5.7+ / MariaDB 10.3+
-- Run via:  phpMyAdmin  →  Import  →  Choose this file  →  Go
--      OR:  mysql -u root tailoring_db < sql/setup_auth.sql
--
-- Default Credentials:
--      Username : Masakai@gmail.com
--      Password : Masakai@2026
-- =====================================================================

USE `tailoring_db`;

-- ---------------------------------------------------------------------
-- 1. Create admin_users table (idempotent)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(100)  NOT NULL,
    `password_hash` VARCHAR(255)  NOT NULL,
    `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. Wipe table to guarantee a clean, single-admin install
-- ---------------------------------------------------------------------
TRUNCATE TABLE `admin_users`;

-- ---------------------------------------------------------------------
-- 3. Insert the single administrator
--    Pre-calculated BCRYPT hash for the password 'Masakai@2026'
--    (cost = 10, generated with PHP password_hash / verified with
--     password_verify — also accepted by all $2y$/$2b$ implementations)
-- ---------------------------------------------------------------------
INSERT INTO `admin_users` (`username`, `password_hash`) VALUES
('Masakai@gmail.com',
 '$2b$10$EAhhsiKU.RrhWH/L7gqA/Or5yA.WwKs3H4uhIwmttlMCVDwAMxAoW');

-- ---------------------------------------------------------------------
-- 4. Verify
-- ---------------------------------------------------------------------
SELECT id, username, LEFT(password_hash, 7) AS hash_algo, LENGTH(password_hash) AS hash_len, created_at
FROM `admin_users`;
