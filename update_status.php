<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
msk_require_login();
msk_require_permission('orders.status');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = msk_s($_POST['order_id'] ?? '');
    $status  = msk_s($_POST['status']   ?? '');
    $allowed = ['received','cutting','sewing','ready_for_pickup','delivered'];

    if ($orderId && in_array($status, $allowed)) {
        $cur     = msk_get_order($orderId);
        $old     = $cur['status'] ?? '';
        // A tailor may only touch orders assigned to them
        if (msk_role() === 'tailor' && (int)($cur['tailor_id'] ?? 0) !== msk_current_user_id()) {
            header('Location: my_tasks.php?msg=' . urlencode('You can only update orders assigned to you.'));
            exit;
        }
        $conn->query("UPDATE orders SET status='$status' WHERE order_id='$orderId'");
        if ($old !== $status) {
            msk_log_status($orderId, $old, $status, msk_current_username());
        }
    }
}

$redirect = $_POST['redirect'] ?? 'orders.php';
// Whitelist redirect to prevent open redirect
$safe = ['orders.php','dashboard.php','clients.php','my_tasks.php','history.php','print_order.php'];
if (!in_array(basename($redirect), $safe)) $redirect = 'orders.php';

header("Location: $redirect?msg=" . urlencode("Order status updated successfully."));
exit;
