<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$tracking   = null;
$trackError = null;
$searched   = false;

if (isset($_GET['phone']) && $_GET['phone'] !== '') {
    $searched = true;
    $client   = msk_get_client_by_phone($_GET['phone']);
    if ($client) {
        $orders   = msk_get_client_orders((int)$client['id']);
        $hasReady = !empty(array_filter($orders, fn($o) => $o['status'] === 'ready_for_pickup'));
        $tracking = compact('client', 'orders', 'hasReady');
    } else {
        $trackError = 'No account found for that phone number. Please check and try again, or contact us at 0800-MASAKAI.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Track My Order — Masakai Tailoring Service</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="container nav-inner">
    <a href="index.php" class="nav-brand">&#9997; Masakai <small>| Tailoring Service</small></a>
    <div class="nav-links">
      <a href="index.php#services">Services</a>
      <a href="index.php#how-it-works">How It Works</a>
      <a href="track_status.php" style="color:var(--gold)">Track Order</a>
      <a href="login.php" class="nav-cta">Admin Portal</a>
    </div>
  </div>
</nav>

<!-- PAGE -->
<section class="section">
  <div class="container" style="max-width:760px">
    <h2 class="sec-title" style="text-align:left;margin-bottom:6px">&#128269; Track My Order</h2>
    <p class="sec-sub" style="text-align:left;margin-bottom:30px">Enter the phone number you used when placing your order at Masakai Tailoring Service.</p>

    <!-- SEARCH FORM -->
    <form method="GET" style="display:flex;gap:12px;margin-bottom:30px;flex-wrap:wrap">
      <input type="tel" name="phone" placeholder="e.g. 08012345678" required
             value="<?= msk_e($_GET['phone'] ?? '') ?>"
             style="flex:1;min-width:220px;padding:13px 18px;border:1px solid #ddd;border-radius:8px;
                    font-size:16px;font-family:sans-serif;outline:none;transition:border-color .2s"
             onfocus="this.style.borderColor='#D4AF37'" onblur="this.style.borderColor='#ddd'">
      <button type="submit" class="btn-gold">Search</button>
      <?php if ($searched): ?>
        <a href="track_status.php" class="btn-ghost">Clear</a>
      <?php endif; ?>
    </form>

    <?php if ($trackError): ?>
      <div class="alert alert-error">&#10007; <?= msk_e($trackError) ?></div>
    <?php endif; ?>

    <?php if ($tracking): ?>
      <!-- READY BANNER -->
      <?php if ($tracking['hasReady']): ?>
      <div class="pickup-banner">
        <span class="pulse"></span>
        <span><strong>Great news!</strong> One or more of your orders at Masakai Tailoring Service are <strong>Ready for Pickup!</strong></span>
      </div>
      <?php endif; ?>

      <!-- CLIENT INFO -->
      <div style="background:#fff;border:1px solid #e5e0d5;border-radius:8px;padding:16px 20px;margin-bottom:18px;font-family:sans-serif">
        <div style="font-size:16px;font-weight:700;margin-bottom:4px">&#128100; <?= msk_e($tracking['client']['name']) ?></div>
        <div style="font-size:13px;color:#777">
          <?= msk_e($tracking['client']['phone']) ?>
          <?php if ($tracking['client']['email']): ?>
            &nbsp;&bull;&nbsp; <?= msk_e($tracking['client']['email']) ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- ORDERS -->
      <?php if (empty($tracking['orders'])): ?>
        <p style="text-align:center;color:#777;font-family:sans-serif;padding:40px 0">No orders found on your account yet.</p>
      <?php else: ?>
        <?php foreach ($tracking['orders'] as $o): ?>
        <div class="order-card <?= $o['status'] === 'ready_for_pickup' ? 'is-ready' : '' ?>">
          <div class="order-card-head">
            <strong><?= msk_e($o['order_id']) ?> &mdash; <?= msk_service_label($o['service_type']) ?></strong>
            <?= msk_badge($o['status']) ?>
          </div>

          <?php if (!in_array($o['status'], ['delivered'])): ?>
            <?= msk_progress_steps($o['status']) ?>
          <?php endif; ?>

          <?php if ($o['status'] === 'delivered'): ?>
            <div style="display:flex;align-items:center;gap:8px;margin:8px 0;font-family:sans-serif;font-size:13px;color:#166534">
              <span style="font-size:18px">&#10004;</span> Your garment has been collected. Thank you for choosing Masakai Tailoring Service!
            </div>
          <?php endif; ?>

          <div class="order-card-body" style="margin-top:8px">
            <?php if ($o['description']): ?><div><?= msk_e($o['description']) ?></div><?php endif; ?>
            <?php if ($o['due_date']): ?>
              <div style="margin-top:4px">
                Due: <strong><?= date('d F Y', strtotime($o['due_date'])) ?></strong>
                <?php if (strtotime($o['due_date']) < time() && !in_array($o['status'],['delivered'])): ?>
                  <span class="badge" style="background:#fee2e2;color:#7f1d1d;margin-left:6px">Overdue</span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>

    <?php elseif (!$searched): ?>
      <!-- Empty state prompt -->
      <div style="text-align:center;padding:60px 20px;color:#999;font-family:sans-serif">
        <div style="font-size:56px;margin-bottom:16px">&#128269;</div>
        <h3 style="font-family:Georgia,serif;color:#1A1A1A;margin-bottom:8px">Enter your phone number above</h3>
        <p>We'll show you the live status of all your orders at Masakai Tailoring Service.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <div class="container">
    <div class="footer-inner">
      <div>
        <div class="footer-brand">Masakai Tailoring Service</div>
        <div class="footer-tag">Precision Crafted. Perfectly Fitted.</div>
      </div>
      <div class="footer-col">
        <h4>Quick Links</h4>
        <a href="index.php">Homepage</a>
        <a href="index.php#services">Services</a>
        <a href="login.php">Admin Portal</a>
      </div>
      <div class="footer-col">
        <h4>Contact</h4>
        <a href="#">14 Tailor Lane, Sokoto</a>
        <a href="tel:08000000000">0800-MASAKAI</a>
      </div>
    </div>
    <div class="footer-bottom">&copy; <?= date('Y') ?> Masakai Tailoring Service. All rights reserved.</div>
  </div>
</footer>

<script src="assets/js/script.js"></script>
</body>
</html>
