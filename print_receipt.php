<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Allow access from admin (logged in) OR from direct link with order_id
$orderId = trim($_GET['order_id'] ?? '');
if (!$orderId) { header('Location: index.php'); exit; }

$order = msk_get_order($orderId);
if (!$order) { die('<p style="font-family:sans-serif;padding:40px;color:red">Order not found: ' . msk_e($orderId) . '</p>'); }

// Privacy: a logged-in customer may only open their OWN receipt.
if (msk_is_logged_in() && msk_role() === 'customer'
    && (int)($order['client_id'] ?? 0) !== msk_current_client_id()) {
    http_response_code(403);
    die('<p style="font-family:sans-serif;padding:40px;color:#b91c1c">You are not permitted to view this order.</p>');
}

// Measurements are staff-only, or the customer's own record.
$maySeeMeasurements = (msk_is_logged_in()
    && (msk_can('measurements.view')
        || (msk_role() === 'customer' && (int)($order['client_id'] ?? 0) === msk_current_client_id())));
$measurements = $maySeeMeasurements ? msk_get_client_measurements((int)($order['client_id'] ?? 0)) : null;

$price    = (float)$order['price'];
$deposit  = (float)$order['deposit_paid'];
$balance  = max(0, $price - $deposit);
$isPaid   = $balance <= 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt #<?= msk_e($order['order_id']) ?> — Masakai Tailoring Service</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="receipt-page">

<!-- PRINT BUTTON (hidden on print) -->
<div class="print-btn no-print">
  <button onclick="window.print()" class="btn-gold">&#128196; Print / Save PDF</button>
  
  <!-- Direct Link is more stable than history.back() -->
  <a href="admin/dashboard.php" class="btn-ghost" style="text-decoration:none; display:inline-block;">&#8592; Back</a>
</div>

<div class="receipt-box">

  <!-- HEADER -->
  <div class="rcpt-hdr">
    <div class="rcpt-brand">MASA<span>KAI</span></div>
    <div class="rcpt-tag">TAILORING SERVICE &bull; PRECISION CRAFTED &bull; PERFECTLY FITTED</div>
    <div style="font-size:12px;color:#777;font-family:sans-serif;margin-top:8px">
      14 Tailor Lane, Sokoto &nbsp;&bull;&nbsp; 0800-MASAKAI &nbsp;&bull;&nbsp; hello@masakai.ng
    </div>
  </div>

  <!-- RECEIPT META -->
  <div class="rcpt-meta">
    <div>
      <div class="lbl">Invoice / Receipt</div>
      <div class="val" style="font-size:19px"><?= msk_e($order['order_id']) ?></div>
    </div>
    <div>
      <div class="lbl">Client</div>
      <div class="val"><?= msk_e($order['client_name']) ?></div>
      <div style="font-size:12px;color:#777;font-family:sans-serif;margin-top:2px"><?= msk_e($order['client_phone']) ?></div>
      <?php if ($order['client_email']): ?><div style="font-size:12px;color:#777;font-family:sans-serif"><?= msk_e($order['client_email']) ?></div><?php endif; ?>
    </div>
    <div>
      <div class="lbl">Date Issued</div>
      <div class="val"><?= date('d F Y') ?></div>
    </div>
    <div>
      <div class="lbl">Due Date</div>
      <div class="val"><?= $order['due_date'] ? date('d F Y', strtotime($order['due_date'])) : 'N/A' ?></div>
    </div>
    <div>
      <div class="lbl">Order Status</div>
      <div class="val"><?= msk_badge($order['status']) ?></div>
    </div>
  </div>

  <!-- ITEMS TABLE -->
  <table class="rcpt-table">
    <thead>
      <tr>
        <th style="width:55%">Description</th>
        <th>Service Type</th>
        <th style="text-align:right">Amount (&#8358;)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?= msk_e($order['description'] ?: 'Tailoring / Garment Service') ?></td>
        <td><?= msk_service_label($order['service_type']) ?></td>
        <td style="text-align:right;font-weight:600"><?= number_format($price, 2) ?></td>
      </tr>
      <?php if ($order['notes']): ?>
      <tr>
        <td colspan="3" style="color:#777;font-size:12px;font-style:italic;border-bottom:none">
          &#128221; Notes: <?= msk_e($order['notes']) ?>
        </td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- TOTALS -->
  <div class="rcpt-totals">
    <div class="rrow"><span>Subtotal</span><span>&#8358; <?= number_format($price, 2) ?></span></div>
    <div class="rrow total"><span><strong>Total</strong></span><span><strong>&#8358; <?= number_format($price, 2) ?></strong></span></div>
    <div class="rrow" style="color:#16a34a"><span>Deposit Paid</span><span>(&#8358; <?= number_format($deposit, 2) ?>)</span></div>
    <div class="rrow" style="font-weight:700;color:<?= $isPaid ? '#16a34a' : '#dc2626' ?>;border-bottom:2px solid currentColor">
      <span>Balance Due</span>
      <span>&#8358; <?= number_format($balance, 2) ?></span>
    </div>
  </div>

  <?php if ($measurements): ?>
  <!-- MEASUREMENTS RECORD -->
  <div style="margin-top:28px;padding-top:20px;border-top:1px solid #e5e0d5">
    <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;
                color:#999;font-family:sans-serif;margin-bottom:12px">
      Measurement Record — <?= msk_service_label($measurements['garment_type'] ?? 'other') ?>
    </div>
    <?php
    $fields = ['neck'=>'Neck','shoulder'=>'Shoulder','chest'=>'Chest / Bust','sleeve'=>'Sleeve',
               'waist'=>'Waist','hip'=>'Hip','length'=>'Length','inseam'=>'Inseam',
               'back_length'=>'Back Length','front_length'=>'Front Length','round_sleeve'=>'Round Sleeve',
               'trouser_waist'=>'Trouser Waist','trouser_length'=>'Trouser Length',
               'thigh'=>'Thigh','knee'=>'Knee','ankle'=>'Ankle'];
    $shown = array_filter($fields, fn($k) => !empty($measurements[$k]) && (float)$measurements[$k] > 0, ARRAY_FILTER_USE_KEY);
    if ($shown):
    ?>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;font-family:sans-serif;font-size:12px">
      <?php foreach ($shown as $key => $label): ?>
      <div style="background:#f7f5f0;border-radius:4px;padding:7px 10px">
        <div style="font-size:10px;color:#999;text-transform:uppercase;letter-spacing:.5px"><?= $label ?></div>
        <div style="font-weight:700;color:#1A1A1A;margin-top:1px"><?= (float)$measurements[$key] ?>"</div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($measurements['notes'])): ?>
      <p style="font-size:12px;color:#777;font-family:sans-serif;margin-top:10px;font-style:italic">
        Notes: <?= msk_e($measurements['notes']) ?>
      </p>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- RECEIPT FOOTER -->
  <div class="rcpt-ftr">
    <?php if ($isPaid): ?>
      <div class="rcpt-stamp">PAID IN FULL</div><br>
    <?php elseif ($order['status'] === 'ready_for_pickup'): ?>
      <div class="rcpt-stamp" style="border-color:var(--gold);color:var(--gold)">READY FOR PICKUP</div><br>
    <?php endif; ?>
    <p>Thank you for choosing <strong>Masakai Tailoring Service</strong>. We look forward to serving you again.</p>
    <p style="margin-top:6px">Questions? Call <strong>0800-MASAKAI</strong> &nbsp;&bull;&nbsp; 14 Tailor Lane, Sokoto</p>
    <p style="margin-top:12px;color:#bbb;font-size:11px">
      Generated by Masakai Tailoring Service Management System &bull; <?= date('d M Y, H:i') ?>
    </p>
  </div>

</div>

<script src="assets/js/script.js"></script>
</body>
</html>
