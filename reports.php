<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
msk_require_login();
msk_require_permission('reports.view');

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = date('Y-m-d');

$sales   = msk_sales_report($from, $to);
$orders  = msk_order_report($from, $to);
$tailors = msk_tailor_report($from, $to);
$salesTotal = array_sum(array_map(fn($r) => (float)$r['total'], $sales));
$orderTotal = array_sum(array_map(fn($r) => (int)$r['orders'], $orders));
$orderValue = array_sum(array_map(fn($r) => (float)$r['value'], $orders));

msk_page_start('Reports', '&#128202; Reports');
?>
<div class="page-header">
  <h2>Business Reports</h2>
  <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <input type="date" name="from" value="<?= msk_e($from) ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px">
    <span style="color:#888">to</span>
    <input type="date" name="to" value="<?= msk_e($to) ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px">
    <button class="btn-sm btn-dark" type="submit">Apply</button>
    <button class="btn-sm btn-dark" type="button" onclick="window.print()">&#128424; Print</button>
  </form>
</div>

<div class="stats-grid">
  <div class="stat-card gold"><div class="stat-num" style="font-size:22px"><?= msk_money($salesTotal) ?></div><div class="stat-lbl">Payments Received</div></div>
  <div class="stat-card"><div class="stat-num"><?= $orderTotal ?></div><div class="stat-lbl">Orders Created</div></div>
  <div class="stat-card"><div class="stat-num" style="font-size:22px"><?= msk_money($orderValue) ?></div><div class="stat-lbl">Order Value</div></div>
  <div class="stat-card"><div class="stat-num"><?= count($tailors) ?></div><div class="stat-lbl">Active Tailors</div></div>
</div>

<div class="tbox">
  <div class="tbox-head"><h3>Sales Report (payments per day)</h3></div>
  <table class="data-table">
    <thead><tr><th>Date</th><th>Payments</th><th>Total Received</th></tr></thead>
    <tbody>
    <?php if (!$sales): ?><tr><td colspan="3" style="text-align:center;padding:30px;color:#999">No payments in this period.</td></tr><?php endif; ?>
    <?php foreach ($sales as $s): ?>
      <tr><td><?= date('d M Y', strtotime($s['day'])) ?></td><td><?= (int)$s['payments'] ?></td><td><?= msk_money((float)$s['total']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="tbox">
  <div class="tbox-head"><h3>Order Report (by status)</h3></div>
  <table class="data-table">
    <thead><tr><th>Status</th><th>Orders</th><th>Value</th></tr></thead>
    <tbody>
    <?php if (!$orders): ?><tr><td colspan="3" style="text-align:center;padding:30px;color:#999">No orders in this period.</td></tr><?php endif; ?>
    <?php foreach ($orders as $o): ?>
      <tr><td><?= msk_badge($o['status']) ?></td><td><?= (int)$o['orders'] ?></td><td><?= msk_money((float)$o['value']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="tbox">
  <div class="tbox-head"><h3>Tailor Performance Report</h3></div>
  <table class="data-table">
    <thead><tr><th>Tailor</th><th>Assigned</th><th>Delivered</th><th>Ready for Pickup</th><th>Overdue</th><th>Delivery Rate</th><th>Value Handled</th></tr></thead>
    <tbody>
    <?php if (!$tailors): ?><tr><td colspan="7" style="text-align:center;padding:30px;color:#999">No tailor accounts yet — add one under Users &amp; Roles.</td></tr><?php endif; ?>
    <?php foreach ($tailors as $t):
      $rate = (int)$t['assigned'] ? round(100 * (int)$t['delivered'] / (int)$t['assigned']) : 0; ?>
      <tr>
        <td><strong><?= msk_e($t['tailor']) ?></strong></td>
        <td><?= (int)$t['assigned'] ?></td>
        <td><?= (int)$t['delivered'] ?></td>
        <td><?= (int)$t['ready'] ?></td>
        <td style="color:<?= (int)$t['overdue'] ? '#ef4444' : '#333' ?>"><?= (int)$t['overdue'] ?></td>
        <td><?= $rate ?>%</td>
        <td><?= msk_money((float)$t['value']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php msk_page_end(); ?>
