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

-- ============ v2 UPGRADE (roles, history, reports, stock, types, sleeve) ============
-- =============================================================
--  MASAKAI TAILORING SERVICE MANAGEMENT SYSTEM
--  UPGRADE SCRIPT  v1 -> v2  (roles, history, reports, stock,
--  garment/clothing types, payments, sleeve types)
--  Safe to run on an existing tailoring_db. Non-destructive.
--  Import via phpMyAdmin or:  mysql -u root tailoring_db < sql/upgrade_v2.sql
-- =============================================================
SET NAMES utf8mb4;

-- -------------------------------------------------------------
-- 1. USER ROLES on existing admin_users (no data loss)
-- -------------------------------------------------------------
ALTER TABLE `admin_users`
  ADD COLUMN IF NOT EXISTS `full_name` VARCHAR(150) NULL AFTER `username`,
  ADD COLUMN IF NOT EXISTS `phone`     VARCHAR(30)  NULL AFTER `full_name`,
  ADD COLUMN IF NOT EXISTS `role`      ENUM('admin','tailor','receptionist','customer')
                                       NOT NULL DEFAULT 'admin' AFTER `password_hash`,
  ADD COLUMN IF NOT EXISTS `client_id` INT UNSIGNED NULL AFTER `role`,
  ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `client_id`;

-- Existing accounts stay administrators
UPDATE `admin_users` SET `role` = 'admin' WHERE `role` IS NULL OR `role` = '';

-- -------------------------------------------------------------
-- 2. MEASUREMENTS: sleeve type (Round / Long)
-- -------------------------------------------------------------
ALTER TABLE `measurements`
  ADD COLUMN IF NOT EXISTS `sleeve_type`   ENUM('round','long') NOT NULL DEFAULT 'round' AFTER `round_sleeve`,
  ADD COLUMN IF NOT EXISTS `sleeve_length` DECIMAL(5,1) NULL AFTER `sleeve_type`,
  ADD COLUMN IF NOT EXISTS `wrist`         DECIMAL(5,1) NULL AFTER `sleeve_length`,
  ADD COLUMN IF NOT EXISTS `arm_hole`      DECIMAL(5,1) NULL AFTER `wrist`;

-- -------------------------------------------------------------
-- 3. GARMENT TYPE MANAGEMENT
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `garment_types` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(120) NOT NULL,
  `code`        VARCHAR(60)  NOT NULL,
  `base_price`  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `description` TEXT NULL,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_garment_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `garment_types` (`name`,`code`,`base_price`) VALUES
 ('Babban Riga','babban_riga',35000),('Agbada','agbada',55000),('Kaftan','kaftan',22000),
 ('Slim Fit Suit','slim_fit',75000),('Custom Suit','custom_suit',90000),('Bridal Gown','bridal',120000),
 ('Alterations','alterations',5000),('Casual Wear','casual_wear',12000),('Other','other',0);

-- -------------------------------------------------------------
-- 4. CLOTHING TYPE MANAGEMENT (fabric / material category)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clothing_types` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120) NOT NULL,
  `category`   VARCHAR(80) NULL,
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `notes`      TEXT NULL,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_clothing_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `clothing_types` (`name`,`category`,`unit_price`) VALUES
 ('Atamfa','Cotton',3500),('Damask','Brocade',6000),('Guinea Brocade','Brocade',8000),
 ('Lace','Ceremonial',12000),('Cashmere','Wool Blend',9500),('Ankara','Cotton Print',3000),
 ('Senator Material','Wool Blend',7000),('Silk','Luxury',15000);

-- -------------------------------------------------------------
-- 5. ORDERS: tailor assignment + type links
-- -------------------------------------------------------------
ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `tailor_id`        INT UNSIGNED NULL AFTER `client_id`,
  ADD COLUMN IF NOT EXISTS `garment_type_id`  INT UNSIGNED NULL AFTER `service_type`,
  ADD COLUMN IF NOT EXISTS `clothing_type_id` INT UNSIGNED NULL AFTER `garment_type_id`;

-- -------------------------------------------------------------
-- 6. STOCK MANAGEMENT
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stock_items` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(150) NOT NULL,
  `category`    VARCHAR(80)  NULL,
  `quantity`    DECIMAL(10,2) NOT NULL DEFAULT 0,
  `unit`        VARCHAR(30)  NOT NULL DEFAULT 'yards',
  `unit_cost`   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `reorder_level` DECIMAL(10,2) NOT NULL DEFAULT 5,
  `supplier`    VARCHAR(150) NULL,
  `notes`       TEXT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `stock_items` (`id`,`name`,`category`,`quantity`,`unit`,`unit_cost`,`reorder_level`,`supplier`) VALUES
 (1,'White Guinea Brocade','Fabric',24,'yards',8000,10,'Kano Textile Market'),
 (2,'Gold Embroidery Thread','Thread',4,'rolls',2500,6,'Sokoto Supplies'),
 (3,'Ivory Bridal Lace','Fabric',12,'yards',12000,5,'Lagos Lace House'),
 (4,'Charcoal Cashmere','Fabric',3,'yards',9500,8,'Kaduna Fabrics'),
 (5,'Shirt Buttons (Gold)','Accessory',180,'pieces',50,50,'Local Supplier');

-- -------------------------------------------------------------
-- 7. PAYMENT HISTORY
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`   VARCHAR(20) NOT NULL,
  `client_id`  INT UNSIGNED NOT NULL,
  `amount`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `method`     ENUM('cash','transfer','pos','other') NOT NULL DEFAULT 'cash',
  `reference`  VARCHAR(120) NULL,
  `recorded_by` VARCHAR(150) NULL,
  `paid_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pay_order` (`order_id`),
  KEY `idx_pay_client` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill deposits already recorded on orders
INSERT INTO `payments` (`order_id`,`client_id`,`amount`,`method`,`reference`,`recorded_by`,`paid_at`)
SELECT o.order_id, o.client_id, o.deposit_paid, 'cash', 'Initial deposit', 'system', o.created_at
FROM `orders` o
WHERE o.deposit_paid > 0
  AND NOT EXISTS (SELECT 1 FROM `payments` p WHERE p.order_id = o.order_id);

-- -------------------------------------------------------------
-- 8. ORDER STATUS HISTORY
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`    VARCHAR(20) NOT NULL,
  `old_status`  VARCHAR(40) NULL,
  `new_status`  VARCHAR(40) NOT NULL,
  `changed_by`  VARCHAR(150) NULL,
  `changed_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_osh_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 9. MEASUREMENT HISTORY (snapshot on every update)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `measurement_history` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id`  INT UNSIGNED NOT NULL,
  `snapshot`   TEXT NULL,
  `changed_by` VARCHAR(150) NULL,
  `changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mh_client` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 10. DEMO STAFF ACCOUNTS (password for all: Masakai@2026)
--     Existing admin account is left untouched.
-- -------------------------------------------------------------
INSERT IGNORE INTO `admin_users` (`username`,`full_name`,`phone`,`password_hash`,`role`) VALUES
 ('tailor@masakai.ng','Sani the Tailor','08099887766','$2y$12$fhU.2mPJuogAvgzRexKQX.CzUY4S/ZTpGqotJKvnjuYxvXqentg5G','tailor'),
 ('reception@masakai.ng','Aisha Reception','08055443322','$2y$12$fhU.2mPJuogAvgzRexKQX.CzUY4S/ZTpGqotJKvnjuYxvXqentg5G','receptionist');

SELECT id, username, role, is_active FROM `admin_users`;
