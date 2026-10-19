<?php
/**
 * Masakai Tailoring System — Client profile & measurement record
 * Accessible to any role with measurements.view (admin, receptionist, tailor).
 * Editing requires measurements.manage.
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
msk_require_login();
msk_require_permission('measurements.view');

$clientId = (int)($_GET['id'] ?? $_POST['client_id'] ?? 0);
if (msk_role() === 'tailor' && !msk_tailor_may_view_client($clientId)) {
    http_response_code(403);
    die('You are not authorized to view this customer.');
}
$client   = $clientId ? msk_get_client($clientId) : null;

if (!$client) {
    msk_page_start('Client', '&#128100; Client Profile');
    echo '<div class="alert alert-error">Client not found.</div>'
       . '<a class="btn-ghost" href="clients.php">&#8592; Back to Clients</a>';
    msk_page_end();
    exit;
}

$canManage = msk_can('measurements.manage');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Server-side authorization — never rely on the hidden form alone.
    msk_require_permission('measurements.manage');
    if (msk_save_client_measurements($clientId, $_POST, msk_current_username())) {
        header('Location: view_client.php?id=' . $clientId . '&msg=' . urlencode('Measurements saved.'));
        exit;
    }
    $error = 'Could not save measurements: ' . htmlspecialchars($conn->error);
}

$m       = msk_get_client_measurements($clientId);
$fields  = msk_measurement_fields();
$orders  = msk_get_client_orders($clientId);
$sleeveT = $m['sleeve_type'] ?? 'round';
$garment = $m['garment_type'] ?? 'other';
$val     = fn(string $k) => ($m && $m[$k] !== null && $m[$k] !== '') ? rtrim(rtrim((string)(float)$m[$k], '0'), '.') : '';

msk_page_start('Client Profile', '&#128100; ' . msk_e($client['name']));
msk_flash();
if ($error) echo '<div class="alert alert-error">' . $error . '</div>';
?>
<div class="page-header">
  <h2><?= msk_e($client['name']) ?> <span style="color:#999;font-size:14px"><?= msk_e($client['phone']) ?></span></h2>
  <div style="display:flex;gap:8px">
    <a href="clients.php" class="btn-ghost">&#8592; Clients</a>
    <?php if (msk_can('orders.manage')): ?>
      <a href="add_order.php?client_id=<?= $clientId ?>" class="btn-gold">+ New Order</a>
    <?php endif; ?>
  </div>
</div>

<div class="tbox">
  <div class="tbox-head"><h3>Client Information</h3></div>
  <table class="data-table">
    <tbody>
      <tr><th style="width:200px">Full Name</th><td><?= msk_e($client['name']) ?></td></tr>
      <tr><th>Phone</th><td><?= msk_e($client['phone']) ?></td></tr>
      <tr><th>Email</th><td><?= msk_e($client['email'] ?? '') ?: '—' ?></td></tr>
      <tr><th>Address</th><td><?= msk_e($client['address'] ?? '') ?: '—' ?></td></tr>
      <tr><th>Registered</th><td><?= !empty($client['created_at']) ? date('d M Y', strtotime($client['created_at'])) : '—' ?></td></tr>
    </tbody>
  </table>
</div>

<div class="tbox">
  <div class="tbox-head"><h3>Measurement Record<?= $m ? '' : ' — none recorded yet' ?></h3></div>
  <table class="data-table">
    <thead><tr><th>Measurement</th><th>Value (inches)</th><th>Measurement</th><th>Value (inches)</th></tr></thead>
    <tbody>
      <tr>
        <th>Garment Type</th><td><?= msk_e(msk_service_label($garment)) ?></td>
        <th>Sleeve Type</th><td><?= msk_e(msk_sleeve_label($sleeveT)) ?></td>
      </tr>
      <?php
        $rows = array_chunk(array_keys($fields), 2);
        foreach ($rows as $pair):
      ?>
      <tr>
        <?php foreach ($pair as $k): ?>
          <th><?= msk_e($fields[$k]) ?></th>
          <td><?= $val($k) !== '' ? msk_e($val($k)) . '"' : '<span style="color:#bbb">No Data</span>' ?></td>
        <?php endforeach; ?>
        <?php if (count($pair) === 1): ?><th></th><td></td><?php endif; ?>
      </tr>
      <?php endforeach; ?>
      <tr><th>Notes</th><td colspan="3"><?= $m && !empty($m['notes']) ? nl2br(msk_e($m['notes'])) : '<span style="color:#bbb">No Data</span>' ?></td></tr>
    </tbody>
  </table>
</div>

<?php if ($canManage): ?>
<div class="form-card">
  <form method="POST">
    <input type="hidden" name="client_id" value="<?= $clientId ?>">
    <fieldset>
      <legend><?= $m ? 'Update Measurements' : 'Add Measurements' ?></legend>
      <div class="form-row">
        <div class="form-group">
          <label>Garment Type</label>
          <select name="garment_type">
            <?php foreach (['babban_riga'=>'Babban Riga','agbada'=>'Agbada','kaftan'=>'Kaftan',
                            'slim_fit'=>'Slim Fit Suit','custom_suit'=>'Custom Suit','bridal'=>'Bridal Gown',
                            'alterations'=>'Alterations','casual_wear'=>'Casual Wear','other'=>'Other'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= $garment === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Sleeve Type</label>
          <select name="sleeve_type" id="sleeve_type">
            <option value="round" <?= $sleeveT === 'round' ? 'selected' : '' ?>>Round Sleeve</option>
            <option value="long"  <?= $sleeveT === 'long'  ? 'selected' : '' ?>>Long Sleeve</option>
          </select>
        </div>
      </div>

      <div class="form-row4">
        <?php foreach ($fields as $k => $label): ?>
          <div class="form-group" data-sleeve-field="<?= in_array($k, ['round_sleeve'], true) ? 'round' : (in_array($k, ['sleeve_length','wrist','arm_hole'], true) ? 'long' : '') ?>">
            <label><?= msk_e($label) ?></label>
            <input type="number" step=".5" name="<?= $k ?>" value="<?= msk_e($val($k)) ?>">
          </div>
        <?php endforeach; ?>
      </div>

      <div class="form-group">
        <label>Notes</label>
        <textarea name="m_notes" rows="2"><?= msk_e($m['notes'] ?? '') ?></textarea>
      </div>
      <button type="submit" class="btn-gold">Save Measurements</button>
    </fieldset>
  </form>
</div>
<script>
(function () {
  var sel = document.getElementById('sleeve_type');
  if (!sel) return;
  function sync() {
    document.querySelectorAll('[data-sleeve-field]').forEach(function (el) {
      var t = el.getAttribute('data-sleeve-field');
      if (!t) return;
      el.style.display = (t === sel.value) ? '' : 'none';
    });
  }
  sel.addEventListener('change', sync); sync();
})();
</script>
<?php endif; ?>

<div class="tbox">
  <div class="tbox-head"><h3>Orders (<?= count($orders) ?>)</h3></div>
  <table class="data-table">
    <thead><tr><th>Order</th><th>Service</th><th>Status</th><th>Due</th><th>Slip</th></tr></thead>
    <tbody>
      <?php if (!$orders): ?><tr><td colspan="5" style="text-align:center;color:#999;padding:30px">No orders yet.</td></tr><?php endif; ?>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><strong><?= msk_e($o['order_id']) ?></strong></td>
          <td><?= msk_service_label($o['service_type']) ?></td>
          <td><?= msk_badge($o['status']) ?></td>
          <td><?= $o['due_date'] ? date('d M Y', strtotime($o['due_date'])) : '—' ?></td>
          <td><a class="btn-sm btn-dark" target="_blank" href="print_order.php?order_id=<?= urlencode($o['order_id']) ?>">&#128424;</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php msk_page_end(); ?>
