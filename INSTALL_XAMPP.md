# Masakai Tailoring Service Management System — v2

PHP 8+ / MySQL (MariaDB) / HTML5 / CSS3 / JavaScript — XAMPP ready. No frameworks.

## 1. Install on XAMPP
1. Copy the `masakai` folder into `C:\xampp\htdocs\` so you have `C:\xampp\htdocs\masakai`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open <http://localhost/phpmyadmin>.

## 2. Database
**Fresh install:** Import `sql/masakai_v2_full.sql` (creates `tailoring_db` with everything, utf8mb4).

**Existing database:** Select `tailoring_db` → Import → `sql/upgrade_v2.sql`. It is non-destructive: no table is dropped and no record is deleted.

**Status workflow upgrade (v3):** After `upgrade_v2.sql`, import `sql/upgrade_v3_status_workflow.sql`. It converts `pending → received` and `completed → delivered` in `orders` and `order_status_history`, then locks the ENUM to the new workflow: Received → Cutting → Sewing → Ready for Pickup → Delivered. Non-destructive; no orders are lost. (Fresh installs of `sql/masakai_v2_full.sql` already include the new statuses.)

Connection settings live in `includes/db.php` (host `localhost`, user `root`, empty password, db `tailoring_db`).

## 3. Log in
Open <http://localhost/masakai/admin/login.php>

| Role | Username | Password |
|---|---|---|
| Admin | Masakai@gmail.com | Masakai@2026 |
| Tailor | tailor@masakai.ng | Masakai@2026 |
| Receptionist | reception@masakai.ng | Masakai@2026 |

Customer accounts are created under **Users & Roles** and linked to a client record; they land on `customer_portal.php`.

## 4. What is new in v2
- **Roles & permissions** — admin, receptionist, tailor, customer (`includes/auth.php`, role-aware sidebar).
- **History** — order status, measurement, customer and payment history (`admin/history.php`).
- **Reports** — sales, orders by status, tailor performance, with date range + print (`admin/reports.php`).
- **Stock management** — add/edit/delete plus low-stock alerts (`admin/stock.php`).
- **Garment types** and **Clothing types** management (`admin/garment_types.php`, `admin/clothing_types.php`).
- **Print order** — professional invoice/slip with measurements and payments (`admin/print_order.php`).
- **Sleeve type** — Round Sleeve shows the round sleeve field; Long Sleeve shows sleeve length, wrist and arm hole.
- **Payments** module with running balances.

Existing features (clients, measurements, orders, status updates, WhatsApp link, public tracking, receipt) are unchanged.

## 5. Notes
- All tables use `utf8mb4_unicode_ci`; the connection calls `set_charset('utf8mb4')`.
- Public tracking stays at `track_status.php` and needs no login.

## v3.1 — Tailor & Receptionist customer/measurement access
- No database changes required (no SQL upgrade to run for this release).
- Tailors can now register clients and view/add/update measurements; Receptionists unchanged (already permitted) but now have a client profile page.
- New page: `admin/view_client.php` — client profile with the complete measurement record.
- Measurements on `print_receipt.php` are now staff-only or the customer's own record.
