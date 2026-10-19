-- =====================================================================
-- Masakai Tailoring System — Upgrade v3
-- Order status workflow: Received -> Cutting -> Sewing -> Ready for Pickup -> Delivered
-- Non-destructive: converts existing data, no orders are lost.
-- Import AFTER upgrade_v2.sql. Safe to run once.
-- =====================================================================
SET NAMES utf8mb4;
START TRANSACTION;

-- 1) Widen the ENUM so both old and new values are temporarily valid
ALTER TABLE `orders`
  MODIFY `status` ENUM('pending','received','cutting','sewing','ready_for_pickup','completed','delivered')
  NOT NULL DEFAULT 'received';

-- 2) Convert existing order data
UPDATE `orders` SET `status` = 'received'  WHERE `status` = 'pending';
UPDATE `orders` SET `status` = 'delivered' WHERE `status` = 'completed';

-- 3) Convert historical status log entries (created by upgrade_v2)
UPDATE `order_status_history` SET `old_status` = 'received'  WHERE `old_status` = 'pending';
UPDATE `order_status_history` SET `old_status` = 'delivered' WHERE `old_status` = 'completed';
UPDATE `order_status_history` SET `new_status` = 'received'  WHERE `new_status` = 'pending';
UPDATE `order_status_history` SET `new_status` = 'delivered' WHERE `new_status` = 'completed';

-- 4) Lock the ENUM down to the new workflow only
ALTER TABLE `orders`
  MODIFY `status` ENUM('received','cutting','sewing','ready_for_pickup','delivered')
  NOT NULL DEFAULT 'received';

COMMIT;
