<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
msk_require_login();
msk_require_permission('clients.view');
if (msk_role() === 'tailor') {
    http_response_code(403);
    die('Tailors do not have general client-management access. Open measurements from My Tasks or an assigned order.');
}

$search  = trim($_GET['q'] ?? '');
$clients = msk_get_all_clients($search);
$msg     = msk_e($_GET['msg'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clients — Masakai Tailoring Service</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dash-body">
<div class="dash-wrap">
<?php include __DIR__ . '/../includes/sidebar.php'; ?>
<div class="dash-main">
  <div class="topbar">
    <div class="topbar-title">&#128100; All Clients</div>
    <div class="topbar-user"><?= msk_e($_SESSION['msk_username'] ?? '') ?></div>
  </div>
  <div class="dash-content">
    <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

    <div class="page-header">
      <h2>Clients (<?= count($clients) ?>)</h2>
      <div style="display:flex;gap:10px;align-items:center">
        <form method="GET" style="display:flex;gap:8px">
          <input type="text" name="q" placeholder="Search name or phone..." value="<?= msk_e($search) ?>"
                 style="padding:8px 14px;border:1px solid #ddd;border-radius:6px;font-size:13px;font-family:sans-serif">
          <button type="submit" class="btn-sm btn-dark">Search</button>
          <?php if ($search): ?><a href="clients.php" class="btn-sm" style="background:#ddd;color:#333">Clear</a><?php endif; ?>
        </form>
        <?php if (msk_can('clients.manage')): ?><a href="add_client.php" class="btn-gold">+ Register Client</a><?php endif; ?>
      </div>
    </div>

    <div class="tbox">
      <table class="data-table">
        <thead>
          <tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Address</th><th>Registered</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (empty($clients)): ?>
          <tr><td colspan="7" style="text-align:center;color:#999;padding:40px;font-family:sans-serif">No clients found.</td></tr>
        <?php else: ?>
          <?php foreach ($clients as $i => $c): ?>
          <tr>
            <td style="color:#999"><?= $i + 1 ?></td>
            <td><strong><?= msk_e($c['name']) ?></strong></td>
            <td><?= msk_e($c['phone']) ?></td>
            <td><?= msk_e($c['email'] ?? '—') ?></td>
            <td><?= msk_e($c['address'] ?? '—') ?></td>
            <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
            <td style="white-space:nowrap">
              <?php if (msk_can('measurements.view')): ?>
                <a href="view_client.php?id=<?= $c['id'] ?>" class="btn-sm btn-dark">Profile &amp; Measurements</a>
              <?php endif; ?>
              <a href="orders.php?client_id=<?= $c['id'] ?>" class="btn-sm btn-dark">Orders</a>
              <?php if (msk_can('orders.manage')): ?><a href="add_order.php?client_id=<?= $c['id'] ?>" class="btn-sm" style="background:var(--gold);color:var(--charcoal)">+ Order</a><?php endif; ?>
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
