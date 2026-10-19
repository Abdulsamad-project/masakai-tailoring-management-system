-- =============================================================
--  MASAKAI TAILORING SERVICE MANAGEMENT SYSTEM
--  UPGRADE SCRIPT  v1 -> v2  (roles, history, reports, stock,
--  garment/clothing types, payments, sleeve types)
--  Safe to run on an existing tailoring_db. Non-destructive.
--  Import via phpMyAdmin or:  mysql -u root tailoring_db < sql/upgrade_v2.sql
-- =============================================================
USE `tailoring_db`;
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
