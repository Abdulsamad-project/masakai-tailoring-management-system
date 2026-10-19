<?php
/**
 * Masakai Tailoring System — Helper Functions
 */

// ---------------------------------------------------------------
// LABELS
// ---------------------------------------------------------------
function msk_status_label(string $s): string {
    return ['received'=>'Received','cutting'=>'Cutting','sewing'=>'Sewing',
            'ready_for_pickup'=>'Ready for Pickup','delivered'=>'Delivered'][$s] ?? ucfirst($s);
}

function msk_service_label(string $s): string {
    return ['babban_riga'=>'Babban Riga','agbada'=>'Agbada','kaftan'=>'Kaftan',
            'slim_fit'=>'Slim Fit Suit','custom_suit'=>'Custom Suit','bridal'=>'Bridal Gown',
            'alterations'=>'Alterations','casual_wear'=>'Casual Wear','other'=>'Other'][$s] ?? ucfirst($s);
}

function msk_badge(string $status): string {
    $map = ['received'=>'badge-received','cutting'=>'badge-cutting','sewing'=>'badge-sewing',
            'ready_for_pickup'=>'badge-ready','delivered'=>'badge-delivered'];
    $cls = $map[$status] ?? 'badge-received';
    return '<span class="badge ' . $cls . '">' . msk_status_label($status) . '</span>';
}

// ---------------------------------------------------------------
// SECURITY
// ---------------------------------------------------------------
function msk_is_logged_in(): bool {
    return !empty($_SESSION['msk_logged_in']) && $_SESSION['msk_logged_in'] === true;
}

function msk_require_login(): void {
    if (!msk_is_logged_in()) {
        header('Location: ' . (str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/') ? '../' : '') . 'login.php');
        exit;
    }
}

function msk_e(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function msk_s(string $v): string {
    global $conn;
    return $conn->real_escape_string(trim($v));
}

// ---------------------------------------------------------------
// ORDERS
// ---------------------------------------------------------------
function msk_generate_order_id(): string {
    return 'MSK-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
}

function msk_get_order(string $orderId): ?array {
    global $conn;
    $id = msk_s($orderId);
    $r = $conn->query(
        "SELECT o.*, c.name AS client_name, c.phone AS client_phone, c.email AS client_email
         FROM orders o JOIN clients c ON c.id = o.client_id
         WHERE o.order_id = '$id' LIMIT 1"
    );
    return ($r && $r->num_rows) ? $r->fetch_assoc() : null;
}

function msk_get_all_orders(string $status = '', int $clientId = 0): array {
    global $conn;
    $w = [];
    if ($status)   $w[] = "o.status = '" . msk_s($status) . "'";
    if ($clientId) $w[] = "o.client_id = $clientId";
    $where = $w ? 'WHERE ' . implode(' AND ', $w) : '';
    $r = $conn->query(
        "SELECT o.*, c.name AS client_name, c.phone AS client_phone
         FROM orders o JOIN clients c ON c.id = o.client_id $where
         ORDER BY o.created_at DESC"
    );
    return ($r) ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

// ---------------------------------------------------------------
// CLIENTS
// ---------------------------------------------------------------
function msk_get_client_by_phone(string $phone): ?array {
    global $conn;
    $p = msk_s($phone);
    $r = $conn->query("SELECT * FROM clients WHERE phone='$p' LIMIT 1");
    return ($r && $r->num_rows) ? $r->fetch_assoc() : null;
}

function msk_get_client_orders(int $clientId): array {
    global $conn;
    $r = $conn->query("SELECT * FROM orders WHERE client_id=$clientId ORDER BY created_at DESC");
    return ($r) ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function msk_get_all_clients(string $search = ''): array {
    global $conn;
    $w = $search ? "WHERE name LIKE '%" . msk_s($search) . "%' OR phone LIKE '%" . msk_s($search) . "%'" : '';
    $r = $conn->query("SELECT * FROM clients $w ORDER BY created_at DESC");
    return ($r) ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function msk_get_client_measurements(int $clientId): ?array {
    global $conn;
    $r = $conn->query("SELECT * FROM measurements WHERE client_id=$clientId LIMIT 1");
    return ($r && $r->num_rows) ? $r->fetch_assoc() : null;
}

// ---------------------------------------------------------------
// DASHBOARD
// ---------------------------------------------------------------
function msk_dashboard_summary(): array {
    global $conn;
    $q = fn($sql) => $conn->query($sql)->fetch_row()[0];
    return [
        'total_clients'  => $q("SELECT COUNT(*) FROM clients"),
        'total_orders'   => $q("SELECT COUNT(*) FROM orders"),
        'active'         => $q("SELECT COUNT(*) FROM orders WHERE status NOT IN ('delivered')"),
        'ready'          => $q("SELECT COUNT(*) FROM orders WHERE status='ready_for_pickup'"),
        'received'       => $q("SELECT COUNT(*) FROM orders WHERE status='received'"),
        'delivered'      => $q("SELECT COUNT(*) FROM orders WHERE status='delivered'"),
        'overdue'        => $q("SELECT COUNT(*) FROM orders WHERE due_date < CURDATE() AND status != 'delivered'"),
        'revenue'        => $q("SELECT COALESCE(SUM(price),0) FROM orders WHERE status='delivered'"),
    ];
}

// ---------------------------------------------------------------
// WHATSAPP
// ---------------------------------------------------------------
function msk_whatsapp_link(string $phone, string $clientName, string $orderId): string {
    $p = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($p, '0')) $p = '234' . substr($p, 1);
    $msg = urlencode("Hello $clientName, your order #$orderId is ready for pickup at Masakai Tailoring! We look forward to seeing you. 🙏");
    return "https://wa.me/$p?text=$msg";
}

// ---------------------------------------------------------------
// PROGRESS STEPS
// ---------------------------------------------------------------
function msk_progress_steps(string $status): string {
    $steps = [
        ['key'=>'received',         'icon'=>'📥', 'label'=>'Received'],
        ['key'=>'cutting',          'icon'=>'✂',  'label'=>'Cutting'],
        ['key'=>'sewing',           'icon'=>'🧵', 'label'=>'Sewing'],
        ['key'=>'ready_for_pickup', 'icon'=>'✅', 'label'=>'Ready for Pickup'],
        ['key'=>'delivered',        'icon'=>'🎁', 'label'=>'Delivered'],
    ];
    $order = ['received'=>0,'cutting'=>1,'sewing'=>2,'ready_for_pickup'=>3,'delivered'=>4];
    $cur   = $order[$status] ?? -1;
    $html  = '<div class="progress-tracker">';
    foreach ($steps as $i => $step) {
        $stepOrder = $order[$step['key']];
        if ($cur > $stepOrder)       $cls = 'done';
        elseif ($cur === $stepOrder) $cls = 'active';
        else                         $cls = '';
        $icon = ($cur > $stepOrder) ? '✓' : $step['icon'];
        $html .= '<div class="prog-step ' . $cls . '">'
               . '<div class="prog-dot">' . $icon . '</div>'
               . '<div class="prog-label">' . $step['label'] . '</div>'
               . '</div>';
    }
    return $html . '</div>';
}

// ===============================================================
//  v2 ADDITIONS — types, stock, payments, history, reports
// ===============================================================

function msk_money(float $v): string {
    return '&#8358; ' . number_format($v, 2);
}

function msk_sleeve_label(?string $t): string {
    return ['round' => 'Round Sleeve', 'long' => 'Long Sleeve'][$t ?? 'round'] ?? 'Round Sleeve';
}

// ---------------------------------------------------------------
// GARMENT / CLOTHING TYPES
// ---------------------------------------------------------------
function msk_garment_types(bool $activeOnly = true): array {
    global $conn;
    $w = $activeOnly ? 'WHERE is_active = 1' : '';
    $r = $conn->query("SELECT * FROM garment_types $w ORDER BY name");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function msk_clothing_types(bool $activeOnly = true): array {
    global $conn;
    $w = $activeOnly ? 'WHERE is_active = 1' : '';
    $r = $conn->query("SELECT * FROM clothing_types $w ORDER BY name");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

// ---------------------------------------------------------------
// STOCK
// ---------------------------------------------------------------
function msk_stock_items(string $search = ''): array {
    global $conn;
    $w = $search ? "WHERE name LIKE '%" . msk_s($search) . "%' OR category LIKE '%" . msk_s($search) . "%'" : '';
    $r = $conn->query("SELECT * FROM stock_items $w ORDER BY name");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function msk_low_stock(): array {
    global $conn;
    $r = $conn->query("SELECT * FROM stock_items WHERE quantity <= reorder_level ORDER BY quantity ASC");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

// ---------------------------------------------------------------
// PAYMENTS
// ---------------------------------------------------------------
function msk_record_payment(string $orderId, int $clientId, float $amount,
                            string $method = 'cash', string $ref = '', string $by = 'system'): void {
    global $conn;
    if ($amount <= 0) return;
    $o = msk_s($orderId); $m = msk_s($method); $r = msk_s($ref); $b = msk_s($by);
    $conn->query("INSERT INTO payments (order_id, client_id, amount, method, reference, recorded_by)
                  VALUES ('$o', $clientId, $amount, '$m', '$r', '$b')");
    $conn->query("UPDATE orders SET deposit_paid = deposit_paid + $amount WHERE order_id = '$o'");
}

function msk_payments(string $orderId = '', int $clientId = 0): array {
    global $conn;
    $w = [];
    if ($orderId)  $w[] = "p.order_id = '" . msk_s($orderId) . "'";
    if ($clientId) $w[] = "p.client_id = $clientId";
    $where = $w ? 'WHERE ' . implode(' AND ', $w) : '';
    $r = $conn->query("SELECT p.*, c.name AS client_name, c.phone AS client_phone
                       FROM payments p LEFT JOIN clients c ON c.id = p.client_id
                       $where ORDER BY p.paid_at DESC");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

// ---------------------------------------------------------------
// HISTORY
// ---------------------------------------------------------------
function msk_log_status(string $orderId, string $oldStatus, string $newStatus, string $by): void {
    global $conn;
    $o = msk_s($orderId); $a = msk_s($oldStatus); $n = msk_s($newStatus); $b = msk_s($by);
    $conn->query("INSERT INTO order_status_history (order_id, old_status, new_status, changed_by)
                  VALUES ('$o', '$a', '$n', '$b')");
}

function msk_status_history(string $orderId = ''): array {
    global $conn;
    $w = $orderId ? "WHERE h.order_id = '" . msk_s($orderId) . "'" : '';
    $r = $conn->query("SELECT h.*, c.name AS client_name
                       FROM order_status_history h
                       LEFT JOIN orders o ON o.order_id = h.order_id
                       LEFT JOIN clients c ON c.id = o.client_id
                       $w ORDER BY h.changed_at DESC LIMIT 300");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function msk_status_history_for_tailor(int $tailorId): array {
    global $conn;
    $stmt = $conn->prepare("SELECT h.*, c.name AS client_name
                            FROM order_status_history h
                            JOIN orders o ON o.order_id = h.order_id
                            LEFT JOIN clients c ON c.id = o.client_id
                            WHERE o.tailor_id = ?
                            ORDER BY h.changed_at DESC LIMIT 300");
    if (!$stmt) return [];
    $stmt->bind_param('i', $tailorId);
    $stmt->execute();
    $r = $stmt->get_result();
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function msk_log_measurement(int $clientId, array $data, string $by): void {
    global $conn;
    $json = msk_s(json_encode($data, JSON_UNESCAPED_UNICODE));
    $b    = msk_s($by);
    $conn->query("INSERT INTO measurement_history (client_id, snapshot, changed_by)
                  VALUES ($clientId, '$json', '$b')");
}

function msk_measurement_history(int $clientId = 0): array {
    global $conn;
    $w = $clientId ? "WHERE m.client_id = $clientId" : '';
    $r = $conn->query("SELECT m.*, c.name AS client_name, c.phone AS client_phone
                       FROM measurement_history m LEFT JOIN clients c ON c.id = m.client_id
                       $w ORDER BY m.changed_at DESC LIMIT 300");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

// ---------------------------------------------------------------
// REPORTS
// ---------------------------------------------------------------
function msk_sales_report(string $from, string $to): array {
    global $conn;
    $f = msk_s($from); $t = msk_s($to);
    $r = $conn->query("SELECT DATE(p.paid_at) AS day, COUNT(*) AS payments, SUM(p.amount) AS total
                       FROM payments p
                       WHERE DATE(p.paid_at) BETWEEN '$f' AND '$t'
                       GROUP BY DATE(p.paid_at) ORDER BY day DESC");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function msk_order_report(string $from, string $to): array {
    global $conn;
    $f = msk_s($from); $t = msk_s($to);
    $r = $conn->query("SELECT o.status, COUNT(*) AS orders, COALESCE(SUM(o.price),0) AS value
                       FROM orders o WHERE DATE(o.created_at) BETWEEN '$f' AND '$t'
                       GROUP BY o.status");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function msk_tailor_report(string $from, string $to): array {
    global $conn;
    $f = msk_s($from); $t = msk_s($to);
    $r = $conn->query("SELECT u.id, COALESCE(u.full_name, u.username) AS tailor,
                              COUNT(o.id) AS assigned,
                              SUM(o.status = 'delivered') AS delivered,
                              SUM(o.status = 'ready_for_pickup') AS ready,
                              SUM(o.due_date < CURDATE() AND o.status <> 'delivered') AS overdue,
                              COALESCE(SUM(o.price),0) AS value
                       FROM admin_users u
                       LEFT JOIN orders o ON o.tailor_id = u.id
                            AND DATE(o.created_at) BETWEEN '$f' AND '$t'
                       WHERE u.role = 'tailor'
                       GROUP BY u.id, tailor ORDER BY delivered DESC");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

// ---------------------------------------------------------------
// ORDERS (v2 helpers)
// ---------------------------------------------------------------
function msk_orders_for_tailor(int $tailorId): array {
    global $conn;
    $r = $conn->query("SELECT o.*, c.name AS client_name, c.phone AS client_phone
                       FROM orders o JOIN clients c ON c.id = o.client_id
                       WHERE o.tailor_id = $tailorId ORDER BY o.due_date IS NULL, o.due_date ASC");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function msk_order_full(string $orderId): ?array {
    global $conn;
    $id = msk_s($orderId);
    $r = $conn->query("SELECT o.*, c.name AS client_name, c.phone AS client_phone,
                              c.email AS client_email, c.address AS client_address,
                              g.name AS garment_name, ct.name AS clothing_name,
                              COALESCE(u.full_name, u.username) AS tailor_name
                       FROM orders o
                       JOIN clients c ON c.id = o.client_id
                       LEFT JOIN garment_types  g  ON g.id  = o.garment_type_id
                       LEFT JOIN clothing_types ct ON ct.id = o.clothing_type_id
                       LEFT JOIN admin_users    u  ON u.id  = o.tailor_id
                       WHERE o.order_id = '$id' LIMIT 1");
    return ($r && $r->num_rows) ? $r->fetch_assoc() : null;
}

// ---------------------------------------------------------------
// MEASUREMENTS (shared by admin / receptionist / tailor)
// ---------------------------------------------------------------
/** Every numeric measurement field supported by the system, with labels. */
function msk_measurement_fields(): array {
    return [
        'neck'           => 'Neck',
        'shoulder'       => 'Shoulder',
        'chest'          => 'Chest',
        'sleeve'         => 'Sleeve',
        'waist'          => 'Waist',
        'hip'            => 'Hip',
        'length'         => 'Length',
        'inseam'         => 'Inseam',
        'back_length'    => 'Back Length',
        'front_length'   => 'Front Length',
        'round_sleeve'   => 'Round Sleeve',
        'sleeve_length'  => 'Sleeve Length',
        'wrist'          => 'Wrist',
        'arm_hole'       => 'Arm Hole',
        'trouser_waist'  => 'Trouser Waist',
        'trouser_length' => 'Trouser Length',
        'thigh'          => 'Thigh',
        'knee'           => 'Knee',
        'ankle'          => 'Ankle',
    ];
}

function msk_get_client(int $clientId): ?array {
    global $conn;
    $r = $conn->query("SELECT * FROM clients WHERE id = $clientId LIMIT 1");
    return ($r && $r->num_rows) ? $r->fetch_assoc() : null;
}

/** Insert or update a client's measurement record and log a history snapshot. */
function msk_save_client_measurements(int $clientId, array $post, string $by): bool {
    global $conn;
    if ($clientId <= 0) return false;

    $fields     = array_keys(msk_measurement_fields());
    $garment    = msk_s($post['garment_type'] ?? 'other');
    $sleeveType = (($post['sleeve_type'] ?? 'round') === 'long') ? 'long' : 'round';
    $notes      = msk_s($post['m_notes'] ?? ($post['notes'] ?? ''));

    $sets = ["garment_type='$garment'", "sleeve_type='$sleeveType'", "notes='$notes'"];
    $snap = ['garment_type' => $garment, 'sleeve_type' => $sleeveType, 'notes' => $notes];
    foreach ($fields as $f) {
        $raw = trim((string)($post[$f] ?? ''));
        if ($raw === '') { $sets[] = "`$f`=NULL"; continue; }
        $v = (float)$raw;
        $sets[] = "`$f`=" . ($v > 0 ? $v : 'NULL');
        if ($v > 0) $snap[$f] = $v;
    }

    $existing = msk_get_client_measurements($clientId);
    if ($existing) {
        $ok = $conn->query("UPDATE measurements SET " . implode(',', $sets) . " WHERE client_id = $clientId");
    } else {
        $ok = $conn->query("INSERT INTO measurements (client_id, " . implode(',', array_map(fn($f) => "`$f`", $fields)) . ", garment_type, sleeve_type, notes) VALUES ($clientId, "
            . implode(',', array_map(function ($f) use ($post) {
                $raw = trim((string)($post[$f] ?? ''));
                $v = (float)$raw;
                return ($raw !== '' && $v > 0) ? $v : 'NULL';
            }, $fields))
            . ", '$garment', '$sleeveType', '$notes')");
    }
    if ($ok) msk_log_measurement($clientId, $snap, $by);
    return (bool)$ok;
}
