<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
msk_require_login();
msk_require_permission('payments.view');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    msk_require_permission('payments.manage');
    $orderId = msk_s($_POST['order_id'] ?? '');
    $amount  = (float)($_POST['amount'] ?? 0);
    $method  = in_array($_POST['method'] ?? '', ['cash','transfer','pos','other'], true) ? $_POST['method'] : 'cash';
    $ref     = msk_s($_POST['reference'] ?? '');
    $order   = $orderId ? msk_get_order($orderId) : null;
    if ($order && $amount > 0) {
        msk_record_payment($orderId, (int)$order['client_id'], $amount, $method, $ref, msk_current_username());
        header('Location: payments.php?msg=' . urlencode('Payment recorded.')); exit;
    }
    header('Location: payments.php?err=' . urlencode('Invalid order or amount.')); exit;
}
$payments = msk_payments();
$orders   = msk_get_all_orders();
$total    = array_sum(array_map(fn($p) => (float)$p['amount'], $payments));
msk_page_start('Payments', '&#128181; Payments');
msk_flash();
?>
<div class="page-header"><h2>Payments (<?= count($payments) ?>) — <?= msk_money($total) ?> received</h2></div>
<?php if (msk_can('payments.manage')): ?>
<div class="form-card"><form method="POST">
  <fieldset><legend>Record Payment</legend>
    <div class="form-row4">
      <div class="form-group"><label>Order *</label><select name="order_id" required>
        <?php foreach ($orders as $o): ?><option value="<?= msk_e($o['order_id']) ?>"><?= msk_e($o['order_id']) ?> — <?= msk_e($o['client_name']) ?></option><?php endforeach; ?>
      </select></div>
      <div class="form-group"><label>Amount (&#8358;) *</label><input type="number" step="0.01" name="amount" required></div>
      <div class="form-group"><label>Method</label><select name="method"><option value="cash">Cash</option><option value="transfer">Bank Transfer</option><option value="pos">POS</option><option value="other">Other</option></select></div>
      <div class="form-group"><label>Reference</label><input type="text" name="reference"></div>
    </div>
  </fieldset>
  <div class="form-actions"><button class="btn-gold">Record Payment</button></div>
</form></div>
<?php endif; ?>
<div class="tbox"><table class="data-table">
  <thead><tr><th>When</th><th>Order</th><th>Client</th><th>Amount</th><th>Method</th><th>Reference</th><th>By</th><th>Slip</th></tr></thead>
  <tbody>
  <?php if (!$payments): ?><tr><td colspan="8" style="text-align:center;padding:40px;color:#999">No payments recorded.</td></tr><?php endif; ?>
  <?php foreach ($payments as $p): ?>
    <tr>
      <td><?= date('d M Y, H:i', strtotime($p['paid_at'])) ?></td>
      <td><strong><?= msk_e($p['order_id']) ?></strong></td>
      <td><?= msk_e($p['client_name'] ?? '—') ?></td>
      <td><?= msk_money((float)$p['amount']) ?></td>
      <td><?= ucfirst(msk_e($p['method'])) ?></td>
      <td><?= msk_e($p['reference'] ?: '—') ?></td>
      <td><?= msk_e($p['recorded_by'] ?: '—') ?></td>
      <td><a class="btn-sm btn-dark" target="_blank" href="print_order.php?order_id=<?= urlencode($p['order_id']) ?>">&#128424;</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php msk_page_end(); ?>
