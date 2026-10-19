<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
msk_require_login();
msk_require_permission('orders.view');

$filterStatus = $_GET['status']    ?? '';
$filterClient = (int)($_GET['client_id'] ?? 0);
$orders       = msk_role() === 'tailor'
               ? msk_orders_for_tailor(msk_current_user_id())
               : msk_get_all_orders($filterStatus, $filterClient);
$msg          = msk_e($_GET['msg'] ?? '');
$statuses     = ['','received','cutting','sewing','ready_for_pickup','delivered'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders — Masakai Tailoring Service</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dash-body">
<div class="dash-wrap">
<?php include __DIR__ . '/../includes/sidebar.php'; ?>
<div class="dash-main">
  <div class="topbar">
    <div class="topbar-title">&#128203; All Orders</div>
    <div class="topbar-user"><?= msk_e($_SESSION['msk_username'] ?? '') ?></div>
  </div>
  <div class="dash-content">
    <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

    <div class="page-header">
      <h2>Orders (<?= count($orders) ?>)</h2>
      <?php if (msk_can('orders.manage')): ?><a href="add_order.php" class="btn-gold">+ New Order</a><?php endif; ?>
    </div>

    <div class="tbox">
      <div class="filter-bar">
        Filter:
        <?php foreach ($statuses as $s): ?>
          <a href="orders.php<?= $s ? '?status='.$s : '' ?>"
             class="filter-lnk <?= $filterStatus===$s ? 'on' : '' ?>">
            <?= $s ? msk_status_label($s) : 'All' ?>
          </a>
        <?php endforeach; ?>
      </div>
      <table class="data-table">
        <thead>
          <tr><th>Order ID</th><th>Client</th><th>Service</th><th>Status</th><th>Due</th><th>Price</th><?php if (msk_role() !== 'tailor'): ?><th>Update</th><?php endif; ?><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (empty($orders)): ?>
          <tr><td colspan="8" style="text-align:center;color:#999;padding:40px;font-family:sans-serif">No orders found.</td></tr>
        <?php else: ?>
          <?php foreach ($orders as $o):
            $isOverdue = $o['due_date'] && strtotime($o['due_date']) < time() && $o['status'] !== 'delivered';
          ?>
          <tr class="<?= $o['status']==='ready_for_pickup' ? 'row-ready' : ($isOverdue ? 'row-overdue' : '') ?>">
            <td><strong><?= msk_e($o['order_id']) ?></strong></td>
            <td><?= msk_e($o['client_name']) ?></td>
            <td><?= msk_service_label($o['service_type']) ?></td>
            <td><?= msk_badge($o['status']) ?></td>
            <td>
              <?= $o['due_date'] ? date('d M Y', strtotime($o['due_date'])) : '—' ?>
              <?php if ($isOverdue): ?>
                <span class="badge" style="background:#fee2e2;color:#7f1d1d;margin-left:4px">Overdue</span>
              <?php endif; ?>
            </td>
            <td><?= $o['price'] ? '&#8358;' . number_format((float)$o['price'], 0) : '—' ?></td>
            <?php if (msk_role() !== 'tailor'): ?>
            <td>
              <?php if (msk_can('orders.manage') || msk_can('orders.status')): ?>
              <form method="POST" action="update_status.php" class="status-form">
                <input type="hidden" name="order_id" value="<?= msk_e($o['order_id']) ?>">
                <input type="hidden" name="redirect" value="orders.php">
                <select name="status">
                  <?php foreach (['received','cutting','sewing','ready_for_pickup','delivered'] as $ss): ?>
                    <option value="<?= $ss ?>" <?= $o['status']===$ss?'selected':'' ?>><?= msk_status_label($ss) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-sm btn-dark">Save</button>
              </form>
              <?php else: ?>
                <span style="color:#888">View only</span>
              <?php endif; ?>
            </td>
            <?php endif; ?>
            <td style="white-space:nowrap">
              <?php if ($o['status'] === 'ready_for_pickup'): ?>
                <a href="<?= msk_whatsapp_link($o['client_phone'], $o['client_name'], $o['order_id']) ?>"
                   target="_blank" class="btn-wa" title="Notify on WhatsApp">&#128222; WA</a>
              <?php endif; ?>
              <?php if (msk_can('measurements.view')): ?>
                <a href="view_client.php?id=<?= (int)$o['client_id'] ?>" class="btn-sm btn-dark" title="View customer measurements" aria-label="View customer measurements">&#128065;</a>
              <?php endif; ?>
              <a href="../print_receipt.php?order_id=<?= urlencode($o['order_id']) ?>"
                 target="_blank" class="btn-sm btn-dark" title="Print Receipt">&#128196;</a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>
<script src="../assets/js/script.js"></script>
</body>
</html>
