<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
msk_require_login();
msk_require_permission('print');

$orderId = trim($_GET['order_id'] ?? '');
$order   = $orderId ? msk_order_full($orderId) : null;
if ($order && msk_role() === 'tailor' && (int)($order['tailor_id'] ?? 0) !== msk_current_user_id()) {
    http_response_code(403);
    die('<p style="font-family:sans-serif;padding:40px;color:#b91c1c">You are not permitted to print this order.</p>');
}
if (!$order) { die('<p style="font-family:sans-serif;padding:40px;color:#b91c1c">Order not found.</p>'); }
$m        = msk_get_client_measurements((int)$order['client_id']);
$payments = msk_payments($order['order_id']);
$paid     = array_sum(array_map(fn($p) => (float)$p['amount'], $payments));
if (!$paid) $paid = (float)$order['deposit_paid'];
$price    = (float)$order['price'];
$balance  = max(0, $price - $paid);
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Slip <?= msk_e($order['order_id']) ?> — Masakai Tailoring Service</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
 body{background:#f4f4f4;font-family:'Segoe UI',sans-serif;color:#222;margin:0;padding:24px}
 .slip{max-width:800px;margin:0 auto;background:#fff;padding:38px 42px;border-radius:10px;box-shadow:0 4px 24px rgba(0,0,0,.08)}
 .hd{text-align:center;border-bottom:3px solid #D4AF37;padding-bottom:18px;margin-bottom:22px}
 .hd h1{font-family:Georgia,serif;font-size:30px;letter-spacing:3px;margin:0}
 .hd h1 span{color:#D4AF37}
 .hd p{margin:6px 0 0;font-size:12px;color:#777;letter-spacing:1px}
 .grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:22px}
 .card{border:1px solid #e6e6e6;border-radius:8px;padding:14px 16px}
 .lbl{font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:#999}
 .val{font-size:15px;font-weight:600;margin-top:2px}
 table{width:100%;border-collapse:collapse;margin-bottom:18px;font-size:13px}
 th,td{border:1px solid #e6e6e6;padding:9px 11px;text-align:left}
 th{background:#fafafa;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#777}
 .tot{display:flex;justify-content:flex-end}
 .tot table{width:320px}
 .tot .big{font-size:16px;font-weight:700;background:#fdf8e7}
 .ft{margin-top:26px;border-top:1px solid #eee;padding-top:14px;text-align:center;font-size:11px;color:#999}
 .sig{display:flex;justify-content:space-between;margin-top:40px;font-size:12px;color:#666}
 .sig div{border-top:1px solid #bbb;width:210px;padding-top:6px;text-align:center}
 @media print{body{background:#fff;padding:0}.slip{box-shadow:none;border-radius:0}.no-print{display:none}}
</style></head><body>
<div class="no-print" style="max-width:800px;margin:0 auto 14px;display:flex;gap:10px">
  <button class="btn-gold" onclick="window.print()">&#128424; Print / Save PDF</button>
  <a class="btn-ghost" href="orders.php">&#8592; Back to Orders</a>
</div>
<div class="slip">
  <div class="hd">
    <h1>MASA<span>KAI</span></h1>
    <p>TAILORING SERVICE &bull; PRECISION CRAFTED &bull; PERFECTLY FITTED</p>
    <p>14 Tailor Lane, Sokoto &nbsp;&bull;&nbsp; 0800-MASAKAI &nbsp;&bull;&nbsp; hello@masakai.ng</p>
  </div>

  <div class="grid">
    <div class="card">
      <div class="lbl">Order Invoice</div>
      <div class="val" style="font-size:19px"><?= msk_e($order['order_id']) ?></div>
      <div style="font-size:12px;color:#777;margin-top:6px">
        Received: <?= date('d M Y', strtotime($order['created_at'])) ?><br>
        Due: <?= $order['due_date'] ? date('d M Y', strtotime($order['due_date'])) : '—' ?><br>
        Status: <?= msk_status_label($order['status']) ?><br>
        Tailor: <?= msk_e($order['tailor_name'] ?: 'Unassigned') ?>
      </div>
    </div>
    <div class="card">
      <div class="lbl">Customer</div>
      <div class="val"><?= msk_e($order['client_name']) ?></div>
      <div style="font-size:12px;color:#777;margin-top:6px">
        <?= msk_e($order['client_phone']) ?><br>
        <?= msk_e($order['client_email'] ?: '') ?><br>
        <?= msk_e($order['client_address'] ?: '') ?>
      </div>
    </div>
  </div>

  <table>
    <thead><tr><th>Garment</th><th>Fabric / Clothing</th><th>Description</th><th style="text-align:right">Amount</th></tr></thead>
    <tbody>
      <tr>
        <td><?= msk_e($order['garment_name'] ?: msk_service_label($order['service_type'])) ?></td>
        <td><?= msk_e($order['clothing_name'] ?: '—') ?></td>
        <td><?= msk_e($order['description'] ?: '—') ?></td>
        <td style="text-align:right"><?= msk_money($price) ?></td>
      </tr>
    </tbody>
  </table>

  <?php if ($m): ?>
  <div class="lbl" style="margin-bottom:6px">Measurements (inches) — Sleeve: <?= msk_e(msk_sleeve_label($m['sleeve_type'] ?? 'round')) ?></div>
  <table><tbody><tr>
  <?php
    $fields = ['neck','shoulder','chest','sleeve','waist','hip','length','inseam','back_length','front_length',
               'trouser_waist','trouser_length','thigh','knee','ankle'];
    $fields = array_merge($fields, (($m['sleeve_type'] ?? 'round') === 'long')
              ? ['sleeve_length','wrist','arm_hole'] : ['round_sleeve']);
    $i = 0;
    foreach ($fields as $f) {
      if (!isset($m[$f]) || $m[$f] === null || (float)$m[$f] <= 0) continue;
      if ($i > 0 && $i % 5 === 0) echo '</tr><tr>';
      echo '<td><span class="lbl">' . ucwords(str_replace('_',' ',$f)) . '</span><br><strong>' . msk_e((string)$m[$f]) . '"</strong></td>';
      $i++;
    }
    if ($i === 0) echo '<td colspan="5" style="color:#999">No measurements recorded.</td>';
    while ($i % 5 !== 0 && $i > 0) { echo '<td></td>'; $i++; }
  ?>
  </tr></tbody></table>
  <?php endif; ?>

  <?php if ($payments): ?>
  <div class="lbl" style="margin-bottom:6px">Payment History</div>
  <table><thead><tr><th>Date</th><th>Method</th><th>Reference</th><th style="text-align:right">Amount</th></tr></thead><tbody>
  <?php foreach ($payments as $p): ?>
    <tr><td><?= date('d M Y', strtotime($p['paid_at'])) ?></td><td><?= ucfirst(msk_e($p['method'])) ?></td>
        <td><?= msk_e($p['reference'] ?: '—') ?></td><td style="text-align:right"><?= msk_money((float)$p['amount']) ?></td></tr>
  <?php endforeach; ?>
  </tbody></table>
  <?php endif; ?>

  <div class="tot"><table>
    <tr><td>Total</td><td style="text-align:right"><?= msk_money($price) ?></td></tr>
    <tr><td>Paid</td><td style="text-align:right"><?= msk_money($paid) ?></td></tr>
    <tr class="big"><td>Balance</td><td style="text-align:right"><?= msk_money($balance) ?></td></tr>
  </table></div>

  <div class="sig"><div>Customer Signature</div><div>Authorised Signature</div></div>
  <div class="ft">Thank you for choosing Masakai Tailoring Service. Please present this slip on pickup.</div>
</div>
</body></html>
