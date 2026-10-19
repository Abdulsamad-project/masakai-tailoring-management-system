<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
msk_require_login();
msk_require_permission('dashboard');

$role = msk_role();

if ($role === 'tailor') {
    $myOrders = msk_orders_for_tailor(msk_current_user_id());
    $active = $ready = $delivered = $overdue = 0;
    foreach ($myOrders as $o) {
        if ($o['status'] === 'delivered') $delivered++;
        else $active++;
        if ($o['status'] === 'ready_for_pickup') $ready++;
        if (!empty($o['due_date']) && strtotime($o['due_date']) < strtotime('today') && $o['status'] !== 'delivered') $overdue++;
    }
} else {
    $summary      = msk_dashboard_summary();
    $readyOrders  = msk_get_all_orders('ready_for_pickup');
    $recentOrders = array_slice(msk_get_all_orders(), 0, 12);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $role === 'tailor' ? 'Tailor Dashboard' : 'Dashboard' ?> — Masakai Tailoring Service</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dash-body">
<div class="dash-wrap">
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="dash-main">
  <div class="topbar">
    <div class="topbar-title">
      <?= $role === 'tailor' ? '🧵 Tailor Dashboard' : '&#9781; Overview Dashboard' ?>
    </div>
    <div class="topbar-user">
      Welcome, <strong><?= msk_e($_SESSION['msk_username'] ?? 'User') ?></strong>
      <span class="role-badge">Role: <?= msk_e(msk_role_label()) ?></span>
    </div>
  </div>

  <div class="dash-content">
  <?php if ($role === 'tailor'): ?>

    <div class="tailor-welcome">
      <div>
        <span class="hero-tag">TAILOR WORKSPACE</span>
        <h1>Welcome to your workshop.</h1>
        <p>View your assigned work, update order progress, and access the customer measurements needed for each garment.</p>
      </div>
      <a href="my_tasks.php" class="btn-gold">🧵 View My Tasks</a>
    </div>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-num"><?= count($myOrders) ?></div><div class="stat-lbl">My Assigned Orders</div></div>
      <div class="stat-card"><div class="stat-num"><?= $active ?></div><div class="stat-lbl">Orders in Progress</div></div>
      <div class="stat-card gold"><div class="stat-num"><?= $ready ?></div><div class="stat-lbl">Ready for Pickup</div></div>
      <div class="stat-card"><div class="stat-num"><?= $delivered ?></div><div class="stat-lbl">Delivered Work</div></div>
      <div class="stat-card red"><div class="stat-num"><?= $overdue ?></div><div class="stat-lbl">Overdue</div></div>
    </div>

    <div class="tbox">
      <div class="tbox-head">
        <h3>My Assigned Work</h3>
        <a href="my_tasks.php" class="btn-sm btn-dark">View All Tasks</a>
      </div>
      <table class="data-table">
        <thead><tr><th>Order ID</th><th>Client</th><th>Service</th><th>Due</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($myOrders, 0, 8) as $o): ?>
          <tr>
            <td><strong><?= msk_e($o['order_id']) ?></strong></td>
            <td><?= msk_e($o['client_name']) ?></td>
            <td><?= msk_service_label($o['service_type']) ?></td>
            <td><?= $o['due_date'] ? date('d M Y', strtotime($o['due_date'])) : '—' ?></td>
            <td><?= msk_badge($o['status']) ?></td>
            <td><a class="btn-sm btn-dark" href="view_client.php?id=<?= (int)$o['client_id'] ?>" title="View measurements">👁️ View</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$myOrders): ?><tr><td colspan="6" style="text-align:center;color:#999;padding:35px">No orders are currently assigned to you.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>

  <?php else: ?>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-num"><?= $summary['total_clients'] ?></div><div class="stat-lbl">Total Clients</div></div>
      <div class="stat-card"><div class="stat-num"><?= $summary['total_orders'] ?></div><div class="stat-lbl">Total Orders</div></div>
      <div class="stat-card"><div class="stat-num"><?= $summary['active'] ?></div><div class="stat-lbl">Active Orders</div></div>
      <div class="stat-card"><div class="stat-num"><?= $summary['received'] ?></div><div class="stat-lbl">Received Orders</div></div>
      <div class="stat-card gold"><div class="stat-num"><?= $summary['ready'] ?></div><div class="stat-lbl">Ready for Pickup</div></div>
      <div class="stat-card"><div class="stat-num"><?= $summary['delivered'] ?></div><div class="stat-lbl">Delivered Orders</div></div>
      <div class="stat-card red"><div class="stat-num"><?= $summary['overdue'] ?></div><div class="stat-lbl">Overdue</div></div>
      <div class="stat-card gold" style="grid-column:span 2">
        <div class="stat-num" style="font-size:24px">&#8358; <?= number_format((float)$summary['revenue'], 0) ?></div>
        <div class="stat-lbl">Total Revenue (Delivered Orders)</div>
      </div>
    </div>

    <?php if ($readyOrders): ?>
    <div class="tbox" style="border-color:#22c55e">
      <div class="tbox-head" style="background:#f0fdf4"><h3 style="color:#166534">&#10004; Ready for Pickup (<?= count($readyOrders) ?>)</h3><a href="orders.php?status=ready_for_pickup" class="btn-sm btn-dark">View All</a></div>
      <table class="data-table"><thead><tr><th>Order ID</th><th>Client</th><th>Phone</th><th>Service</th><th>Due</th><th>WhatsApp</th><th>Receipt</th></tr></thead><tbody>
      <?php foreach ($readyOrders as $o): ?><tr class="row-ready">
        <td><strong><?= msk_e($o['order_id']) ?></strong></td><td><?= msk_e($o['client_name']) ?></td><td><?= msk_e($o['client_phone']) ?></td><td><?= msk_service_label($o['service_type']) ?></td>
        <td><?= $o['due_date'] ? date('d M Y', strtotime($o['due_date'])) : '—' ?></td>
        <td><a href="<?= msk_whatsapp_link($o['client_phone'], $o['client_name'], $o['order_id']) ?>" target="_blank" class="btn-wa">&#128222; WhatsApp</a></td>
        <td><a href="../print_receipt.php?order_id=<?= urlencode($o['order_id']) ?>" target="_blank" class="btn-sm btn-dark">&#128196; Receipt</a></td>
      </tr><?php endforeach; ?></tbody></table>
    </div>
    <?php endif; ?>

    <div class="tbox">
      <div class="tbox-head"><h3>Recent Orders</h3><a href="orders.php" class="btn-sm btn-dark">View All</a></div>
      <table class="data-table"><thead><tr><th>Order ID</th><th>Client</th><th>Service</th><th>Status</th><th>Due</th><th>Update Status</th></tr></thead><tbody>
      <?php foreach ($recentOrders as $o): $isOverdue = $o['due_date'] && strtotime($o['due_date']) < time() && $o['status'] !== 'delivered'; ?>
      <tr class="<?= $o['status']==='ready_for_pickup' ? 'row-ready' : ($isOverdue ? 'row-overdue' : '') ?>">
        <td><strong><?= msk_e($o['order_id']) ?></strong></td><td><?= msk_e($o['client_name']) ?></td><td><?= msk_service_label($o['service_type']) ?></td><td><?= msk_badge($o['status']) ?></td>
        <td><?= $o['due_date'] ? date('d M Y', strtotime($o['due_date'])) : '—' ?></td>
        <td><form method="POST" action="update_status.php" class="status-form">
          <input type="hidden" name="order_id" value="<?= msk_e($o['order_id']) ?>"><input type="hidden" name="redirect" value="dashboard.php">
          <select name="status"><?php foreach (['received','cutting','sewing','ready_for_pickup','delivered'] as $ss): ?><option value="<?= $ss ?>" <?= $o['status']===$ss?'selected':'' ?>><?= msk_status_label($ss) ?></option><?php endforeach; ?></select>
          <button type="submit" class="btn-sm btn-dark">Save</button>
        </form></td>
      </tr>
      <?php endforeach; ?></tbody></table>
    </div>
  <?php endif; ?>
  </div>
</div>
</div>
<script src="../assets/js/script.js"></script>
</body>
</html>
