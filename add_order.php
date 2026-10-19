<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
msk_require_login();
msk_require_permission('orders.manage');

$clients       = msk_get_all_clients();
$tailors       = msk_users_by_role('tailor');
$garmentTypes  = msk_garment_types();
$clothingTypes = msk_clothing_types();
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientId  = (int)($_POST['client_id']   ?? 0);
    $svcType   = msk_s($_POST['service_type'] ?? 'other');
    $desc      = msk_s($_POST['description']  ?? '');
    $status    = msk_s($_POST['status']       ?? 'received');
    $dueDate   = msk_s($_POST['due_date']     ?? '');
    $price     = (float)($_POST['price']      ?? 0);
    $deposit   = (float)($_POST['deposit']    ?? 0);
    $notes     = msk_s($_POST['notes']        ?? '');
    $tailorId  = (int)($_POST['tailor_id']        ?? 0);
    $garmentId = (int)($_POST['garment_type_id']  ?? 0);
    $clothId   = (int)($_POST['clothing_type_id'] ?? 0);
    $orderId   = msk_generate_order_id();

    if (!$clientId) {
        $error = 'Please select a client.';
    } else {
        $due = $dueDate ? "'$dueDate'" : 'NULL';
        $tailorSql  = $tailorId  ? $tailorId  : 'NULL';
        $garmentSql = $garmentId ? $garmentId : 'NULL';
        $clothSql   = $clothId   ? $clothId   : 'NULL';
        $conn->query("INSERT INTO orders
          (order_id, client_id, tailor_id, service_type, garment_type_id, clothing_type_id,
           description, status, due_date, price, deposit_paid, notes)
          VALUES ('$orderId', $clientId, $tailorSql, '$svcType', $garmentSql, $clothSql,
                  '$desc', '$status', $due, $price, 0, '$notes')");
        if ($conn->affected_rows) {
            // Order history + deposit as a payment record
            msk_log_status($orderId, '', $status, msk_current_username());
            if ($deposit > 0) {
                msk_record_payment($orderId, $clientId, $deposit, 'cash', 'Initial deposit', msk_current_username());
            }
            header("Location: orders.php?msg=" . urlencode("Order $orderId created successfully."));
            exit;
        }
        $error = 'Error: ' . htmlspecialchars($conn->error);
    }
}
$prefillClient = (int)($_GET['client_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Order — Masakai Tailoring Service</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dash-body">
<div class="dash-wrap">
<?php include __DIR__ . '/../includes/sidebar.php'; ?>
<div class="dash-main">
  <div class="topbar">
    <div class="topbar-title">&#128196; Create New Order</div>
    <div class="topbar-user"><?= msk_e($_SESSION['msk_username'] ?? '') ?></div>
  </div>
  <div class="dash-content">
    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

    <div class="page-header">
      <h2>Create New Order</h2>
      <a href="orders.php" class="btn-ghost">&#8592; Back to Orders</a>
    </div>

    <div class="form-card">
    <form method="POST">
      <div class="form-row">
        <div class="form-group">
          <label>Client *</label>
          <select name="client_id" required>
            <option value="">— Select Client —</option>
            <?php foreach ($clients as $c): ?>
              <option value="<?= $c['id'] ?>"
                <?= ($prefillClient === (int)$c['id'] || (isset($_POST['client_id']) && (int)$_POST['client_id']===(int)$c['id'])) ? 'selected' : '' ?>>
                <?= msk_e($c['name']) ?> (<?= msk_e($c['phone']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Service Type *</label>
          <select name="service_type" required>
            <?php foreach (['babban_riga'=>'Babban Riga','agbada'=>'Agbada','kaftan'=>'Kaftan',
                            'slim_fit'=>'Slim Fit Suit','custom_suit'=>'Custom Suit','bridal'=>'Bridal Gown',
                            'alterations'=>'Alterations','casual_wear'=>'Casual Wear','other'=>'Other'] as $v=>$l): ?>
              <option value="<?= $v ?>"><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row3">
        <div class="form-group">
          <label>Garment Type</label>
          <select name="garment_type_id">
            <option value="">— Not set —</option>
            <?php foreach ($garmentTypes as $g): ?>
              <option value="<?= (int)$g['id'] ?>"><?= msk_e($g['name']) ?> (&#8358;<?= number_format((float)$g['base_price'], 0) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Clothing / Fabric Type</label>
          <select name="clothing_type_id">
            <option value="">— Not set —</option>
            <?php foreach ($clothingTypes as $ct): ?>
              <option value="<?= (int)$ct['id'] ?>"><?= msk_e($ct['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Assign Tailor</label>
          <select name="tailor_id">
            <option value="">— Unassigned —</option>
            <?php foreach ($tailors as $t): ?>
              <option value="<?= (int)$t['id'] ?>"><?= msk_e($t['full_name'] ?: $t['username']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Description / Garment Details</label>
        <textarea name="description" rows="3" placeholder="e.g. Babban Riga — blue damask with gold embroidery on collar and cuffs..."><?= msk_e($_POST['description'] ?? '') ?></textarea>
      </div>

      <div class="form-row3">
        <div class="form-group">
          <label>Initial Status</label>
          <select name="status">
            <?php foreach (['received'=>'Received','cutting'=>'Cutting','sewing'=>'Sewing','ready_for_pickup'=>'Ready for Pickup','delivered'=>'Delivered'] as $v=>$l): ?>
              <option value="<?= $v ?>"><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Due Date</label>
          <input type="date" name="due_date" min="<?= date('Y-m-d') ?>" value="<?= msk_e($_POST['due_date'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Total Price (&#8358;)</label>
          <input type="number" step="0.01" name="price" placeholder="35000" value="<?= msk_e($_POST['price'] ?? '') ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Deposit Paid (&#8358;)</label>
          <input type="number" step="0.01" name="deposit" placeholder="15000" value="<?= msk_e($_POST['deposit'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Internal Notes</label>
          <input type="text" name="notes" placeholder="Fabric colour, embroidery details, special requests..." value="<?= msk_e($_POST['notes'] ?? '') ?>">
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-gold">Create Order</button>
        <a href="orders.php" class="btn-ghost">Cancel</a>
      </div>
    </form>
    </div>
  </div>
</div>
</div>
<script src="../assets/js/script.js"></script>
</body>
</html>
