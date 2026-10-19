<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
msk_require_login();
msk_require_permission('orders.status');

$isTailor = msk_role() === 'tailor';
$orders   = $isTailor ? msk_orders_for_tailor(msk_current_user_id()) : msk_get_all_orders();

msk_page_start('My Tasks', '&#129525; My Tailoring Tasks');
msk_flash();
?>
<div class="page-header">
  <h2><?= $isTailor ? 'Orders assigned to me' : 'All workshop orders' ?> (<?= count($orders) ?>)</h2>
</div>

<div class="tbox">
  <table class="data-table">
    <thead><tr><th>Order ID</th><th>Client</th><th>Service</th><th>Due</th><th>Status</th><th>Update</th><th>Measurements</th><th>Print</th></tr></thead>
    <tbody>
    <?php if (!$orders): ?>
      <tr><td colspan="8" style="text-align:center;color:#999;padding:40px;font-family:sans-serif">No orders assigned yet.</td></tr>
    <?php else: foreach ($orders as $o):
      $overdue = $o['due_date'] && strtotime($o['due_date']) < time() && $o['status'] !== 'delivered'; ?>
      <tr class="<?= $overdue ? 'row-overdue' : ($o['status']==='ready_for_pickup' ? 'row-ready' : '') ?>">
        <td><strong><?= msk_e($o['order_id']) ?></strong></td>
        <td><?= msk_e($o['client_name']) ?><br><span style="color:#999;font-size:12px"><?= msk_e($o['client_phone']) ?></span></td>
        <td><?= msk_service_label($o['service_type']) ?></td>
        <td><?= $o['due_date'] ? date('d M Y', strtotime($o['due_date'])) : '—' ?></td>
        <td><?= msk_badge($o['status']) ?></td>
        <td>
          <form method="POST" action="update_status.php" class="status-form">
            <input type="hidden" name="order_id" value="<?= msk_e($o['order_id']) ?>">
            <input type="hidden" name="redirect" value="my_tasks.php">
            <select name="status">
              <?php foreach (['received','cutting','sewing','ready_for_pickup','delivered'] as $s): ?>
                <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= msk_status_label($s) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-sm btn-dark">Save</button>
          </form>
        </td>
        <td><?php if (msk_can('measurements.view')): ?><a class="btn-sm btn-dark" href="view_client.php?id=<?= (int)$o['client_id'] ?>" title="View customer measurements" aria-label="View customer measurements">&#128065; View</a><?php else: ?>&mdash;<?php endif; ?></td>
        <td><a class="btn-sm btn-dark" target="_blank" href="print_order.php?order_id=<?= urlencode($o['order_id']) ?>">&#128424; Slip</a></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?php msk_page_end(); ?>
