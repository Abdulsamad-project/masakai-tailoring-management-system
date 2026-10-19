-- =============================================================
--  MASAKAI TAILORING SERVICE MANAGEMENT SYSTEM
--  Database Setup SQL — tailoring_db
--  Run via: includes/setup.php  OR  paste into phpMyAdmin
-- =============================================================

CREATE DATABASE IF NOT EXISTS `tailoring_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `tailoring_db`;

-- -------------------------------------------------------------
-- TABLE: admin_users
-- NOTE: password_hash MUST be VARCHAR(255) so bcrypt is not cut
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(150)    NOT NULL UNIQUE,
  `password_hash` VARCHAR(255)    NOT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin inserted by includes/setup.php with PHP password_hash()
-- Username: Masakai@gmail.com | Password: Masakai@2026

-- -------------------------------------------------------------
-- TABLE: clients
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clients` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255) NOT NULL,
  `phone`      VARCHAR(30)  NOT NULL UNIQUE,
  `email`      VARCHAR(255)          DEFAULT NULL,
  `address`    TEXT                  DEFAULT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: measurements
-- Includes Babban Riga, Agbada, Kaftan + standard fields
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `measurements` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id`    INT UNSIGNED NOT NULL,
  `garment_type` ENUM('babban_riga','agbada','kaftan','slim_fit','custom_suit','bridal','alterations','casual_wear','other')
                              NOT NULL DEFAULT 'other',
  -- Standard
  `neck`         DECIMAL(5,1) DEFAULT NULL,
  `shoulder`     DECIMAL(5,1) DEFAULT NULL,
  `chest`        DECIMAL(5,1) DEFAULT NULL,
  `sleeve`       DECIMAL(5,1) DEFAULT NULL,
  `waist`        DECIMAL(5,1) DEFAULT NULL,
  `hip`          DECIMAL(5,1) DEFAULT NULL,
  `length`       DECIMAL(5,1) DEFAULT NULL,
  `inseam`       DECIMAL(5,1) DEFAULT NULL,
  -- Babban Riga / Agbada / Kaftan extras
  `back_length`  DECIMAL(5,1) DEFAULT NULL,
  `front_length` DECIMAL(5,1) DEFAULT NULL,
  `round_sleeve` DECIMAL(5,1) DEFAULT NULL,
  `trouser_waist`  DECIMAL(5,1) DEFAULT NULL,
  `trouser_length` DECIMAL(5,1) DEFAULT NULL,
  `thigh`        DECIMAL(5,1) DEFAULT NULL,
  `knee`         DECIMAL(5,1) DEFAULT NULL,
  `ankle`        DECIMAL(5,1) DEFAULT NULL,
  -- Meta
  `notes`        TEXT                  DEFAULT NULL,
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_meas_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: orders
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`      VARCHAR(20)  NOT NULL UNIQUE,
  `client_id`     INT UNSIGNED NOT NULL,
  `service_type`  ENUM('babban_riga','agbada','kaftan','slim_fit','custom_suit','bridal','alterations','casual_wear','other')
                               NOT NULL DEFAULT 'other',
  `description`   TEXT                  DEFAULT NULL,
  `status`        ENUM('received','cutting','sewing','ready_for_pickup','delivered')
                               NOT NULL DEFAULT 'received',
  `due_date`      DATE                  DEFAULT NULL,
  `price`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `deposit_paid`  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `notes`         TEXT                  DEFAULT NULL,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_order_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- SAMPLE DATA (3 clients, 5 orders across all statuses)
-- =============================================================
INSERT IGNORE INTO `clients` (`name`, `phone`, `email`, `address`) VALUES
  ('Alhaji Musa Bello',   '08012345678', 'musa@email.com',   '14 Sultan Road, Sokoto'),
  ('Hauwa Garba',         '08023456789', 'hauwa@email.com',  '7 Emir Close, Kano'),
  ('Ibrahim Sule Waziri', '08034567890', 'ibrahim@email.com','3 Tudun Wada, Kaduna');

INSERT IGNORE INTO `orders` (`order_id`,`client_id`,`service_type`,`description`,`status`,`due_date`,`price`,`deposit_paid`,`notes`) VALUES
  ('MSK-AAA001', 1, 'babban_riga', 'Babban Riga — Blue damask with gold embroidery', 'sewing',           DATE_ADD(CURDATE(), INTERVAL 4 DAY),  35000, 15000, 'Embroidery on collar and cuffs'),
  ('MSK-BBB002', 2, 'bridal',      'Bridal gown with lace trim, ivory',              'ready_for_pickup', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 120000, 60000, 'Fitted bodice, cathedral train'),
  ('MSK-CCC003', 3, 'agbada',      '3-piece Agbada set — deep maroon',               'cutting',          DATE_ADD(CURDATE(), INTERVAL 6 DAY),  55000, 25000, 'Hand-embroidered chest panel'),
  ('MSK-DDD004', 1, 'kaftan',      'Senator kaftan — plain white with gold trim',    'received',         DATE_ADD(CURDATE(), INTERVAL 8 DAY),  22000, 10000, 'Gold embroidery on collar'),
  ('MSK-EEE005', 2, 'slim_fit',    'Slim fit suit — charcoal grey, peak lapels',     'delivered',        DATE_SUB(CURDATE(), INTERVAL 3 DAY),  75000, 75000, 'Monogram on breast pocket');
