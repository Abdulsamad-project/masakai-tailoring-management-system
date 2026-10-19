<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
msk_require_login();
msk_require_permission('portal');

$clientId = msk_current_client_id();
$client   = null;
if ($clientId) {
    $r = $conn->query("SELECT * FROM clients WHERE id = $clientId LIMIT 1");
    $client = ($r && $r->num_rows) ? $r->fetch_assoc() : null;
}
$orders   = $clientId ? msk_get_client_orders($clientId) : [];
$payments = $clientId ? msk_payments('', $clientId) : [];
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Orders — Masakai Tailoring Service</title>
<link rel="stylesheet" href="assets/css/style.css"></head>
<body class="dash-body" style="padding:24px">
<div style="max-width:960px;margin:0 auto">
  <div class="page-header">
    <h2 style="font-family:Georgia,serif">Welcome<?= $client ? ', ' . msk_e($client['name']) : '' ?></h2>
    <a href="logout.php" class="btn-ghost">Logout</a>
  </div>
  <?php if (!$client): ?>
    <div class="alert alert-error">Your account is not linked to a customer record yet. Please contact reception.</div>
  <?php endif; ?>

  <div class="tbox">
    <div class="tbox-head"><h3>My Orders (<?= count($orders) ?>)</h3></div>
    <table class="data-table">
      <thead><tr><th>Order</th><th>Service</th><th>Status</th><th>Due</th><th>Price</th><th>Slip</th></tr></thead>
      <tbody>
      <?php if (!$orders): ?><tr><td colspan="6" style="text-align:center;padding:40px;color:#999">No orders yet.</td></tr><?php endif; ?>
      <?php foreach ($orders as $o): ?>
        <tr><td><strong><?= msk_e($o['order_id']) ?></strong></td>
            <td><?= msk_service_label($o['service_type']) ?></td>
            <td><?= msk_badge($o['status']) ?></td>
            <td><?= $o['due_date'] ? date('d M Y', strtotime($o['due_date'])) : '—' ?></td>
            <td><?= msk_money((float)$o['price']) ?></td>
            <td><a class="btn-sm btn-dark" target="_blank" href="print_receipt.php?order_id=<?= urlencode($o['order_id']) ?>">Receipt</a></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="tbox">
    <div class="tbox-head"><h3>My Payments</h3></div>
    <table class="data-table">
      <thead><tr><th>Date</th><th>Order</th><th>Amount</th><th>Method</th></tr></thead>
      <tbody>
      <?php if (!$payments): ?><tr><td colspan="4" style="text-align:center;padding:30px;color:#999">No payments recorded.</td></tr><?php endif; ?>
      <?php foreach ($payments as $p): ?>
        <tr><td><?= date('d M Y', strtotime($p['paid_at'])) ?></td><td><?= msk_e($p['order_id']) ?></td>
            <td><?= msk_money((float)$p['amount']) ?></td><td><?= ucfirst(msk_e($p['method'])) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body></html>
