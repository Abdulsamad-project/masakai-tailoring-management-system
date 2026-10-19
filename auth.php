<?php
/**
 * Masakai Tailoring System — Roles & Permissions
 * Roles: admin | tailor | receptionist | customer
 * This layer sits ON TOP of the existing session auth — it never replaces it.
 */

// ---------------------------------------------------------------
// PERMISSION MATRIX
// ---------------------------------------------------------------
function msk_permissions(): array {
    return [
        'admin' => [
            'dashboard','clients.view','clients.manage','measurements.view','measurements.manage',
            'orders.view','orders.manage','orders.status','payments.view','payments.manage',
            'history.view','reports.view','stock.view','stock.manage',
            'types.view','types.manage','users.manage','profile.view','print',
        ],
        'receptionist' => [
            'dashboard','clients.view','clients.manage','measurements.view','measurements.manage',
            'orders.view','orders.manage','orders.status','payments.view','payments.manage',
            'history.view','reports.view','stock.view','types.view','profile.view','print',
        ],
        'tailor' => [
            'dashboard','tasks.view','orders.view','orders.status',
            'measurements.view','measurements.manage','history.view','profile.view','print',
        ],
        'customer' => [
            'portal','print',
        ],
    ];
}

function msk_role(): string {
    return $_SESSION['msk_role'] ?? 'admin';
}

function msk_role_label(?string $r = null): string {
    $r = $r ?? msk_role();
    return ['admin'=>'Administrator','tailor'=>'Tailor',
            'receptionist'=>'Receptionist','customer'=>'Customer'][$r] ?? ucfirst($r);
}

function msk_can(string $permission): bool {
    $perms = msk_permissions();
    $role  = msk_role();
    return in_array($permission, $perms[$role] ?? [], true);
}

/** Block the page when the current role lacks the permission. */
function msk_require_permission(string $permission): void {
    msk_require_login();
    if (!msk_can($permission)) {
        http_response_code(403);
        $isAdminDir = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/');
        $home = $isAdminDir ? 'dashboard.php' : 'index.php';
        die('<!DOCTYPE html><html><head><meta charset="utf-8">
             <title>Access Denied — Masakai Tailoring Service</title>
             <link rel="stylesheet" href="' . ($isAdminDir ? '../' : '') . 'assets/css/style.css"></head>
             <body class="auth-body"><div class="auth-card" style="text-align:center">
               <div class="auth-brand">Masakai</div>
               <h2 class="auth-heading">Access Denied</h2>
               <p style="color:#888;font-size:14px;font-family:sans-serif;margin-bottom:20px">
                 Your role (<strong>' . msk_e(msk_role_label()) . '</strong>) is not permitted to open this page.
               </p>
               <a class="btn-gold btn-full" href="' . $home . '">Back to my dashboard</a>
             </div></body></html>');
    }
}

/** The landing page for a role right after login. */
function msk_role_home(string $role): string {
    return match ($role) {
        'customer' => '../customer_portal.php',
        'tailor'   => 'my_tasks.php',
        default    => 'dashboard.php',
    };
}

function msk_current_user_id(): int {
    return (int)($_SESSION['msk_id'] ?? 0);
}

function msk_current_username(): string {
    return (string)($_SESSION['msk_username'] ?? 'User');
}

function msk_current_client_id(): int {
    return (int)($_SESSION['msk_client_id'] ?? 0);
}

/**
 * Tailors may view a customer's profile/measurements only when that customer
 * belongs to one of the tailor's assigned orders.
 */
function msk_tailor_may_view_client(int $clientId): bool {
    global $conn;
    if (msk_role() !== 'tailor') return true;
    $tailorId = msk_current_user_id();
    if ($clientId <= 0 || $tailorId <= 0) return false;
    $stmt = $conn->prepare("SELECT 1 FROM orders WHERE client_id = ? AND tailor_id = ? LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param('ii', $clientId, $tailorId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result && $result->num_rows > 0;
}

/** All staff members with a given role (used for tailor assignment). */
function msk_users_by_role(string $role): array {
    global $conn;
    $r = $conn->query("SELECT id, username, full_name FROM admin_users
                       WHERE role='" . $conn->real_escape_string($role) . "' AND is_active=1
                       ORDER BY COALESCE(full_name, username)");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}
