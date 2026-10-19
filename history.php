<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

msk_require_login();
msk_require_permission('history.view');

if (msk_role() === 'tailor') {
    $rows = msk_status_history_for_tailor(msk_current_user_id());
    msk_page_start('History', '🕘 My Work History');
    msk_flash();
?>
<div class="page-header">
  <h2>My Work History</h2>
  <span class="role-badge">Role: Tailor</span>
</div>
<div class="tbox">
  <div class="tbox-head"><h3>Order Status History</h3></div>
  <table class="data-table">
    <thead><tr><th>When</th><th>Order</th><th>Client</th><th>From</th><th>To</th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="5" style="text-align:center;padding:40px;color:#999">No work history yet.</td></tr><?php endif; ?>
    <?php foreach ($rows as $h): ?>
      <tr>
        <td><?= date('d M Y, H:i', strtotime($h['changed_at'])) ?></td>
        <td><strong><?= msk_e($h['order_id']) ?></strong></td>
        <td><?= msk_e($h['client_name'] ?? '—') ?></td>
        <td><?= $h['old_status'] ? msk_status_label($h['old_status']) : '— new —' ?></td>
        <td><?= msk_badge($h['new_status']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
    msk_page_end();
    exit;
}

$tab = $_GET['tab'] ?? 'orders';
$valid = ['orders','measurements','customers','payments'];
if (!in_array($tab, $valid, true)) $tab = 'orders';

msk_page_start('History', '&#128340; History');
msk_flash();
?>
<div class="page-header">
  <h2>System History</h2>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <?php foreach (['orders'=>'Order History','measurements'=>'Measurement History',
                    'customers'=>'Customer History','payments'=>'Payment History'] as $k=>$l): ?>
      <a href="history.php?tab=<?= $k ?>" class="btn-sm <?= $tab===$k ? 'btn-dark' : '' ?>"
         style="<?= $tab===$k ? '' : 'background:#e5e5e5;color:#333' ?>"><?= $l ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="tbox">
<?php if ($tab === 'orders'):
  $rows = msk_status_history(); ?>
  <table class="data-table"><thead><tr><th>When</th><th>Order</th><th>Client</th><th>From</th><th>To</th><th>By</th></tr></thead><tbody>
  <?php if (!$rows): ?><tr><td colspan="6" style="text-align:center;padding:40px;color:#999">No order history yet.</td></tr><?php endif; ?>
  <?php foreach ($rows as $h): ?><tr><td><?= date('d M Y, H:i', strtotime($h['changed_at'])) ?></td><td><strong><?= msk_e($h['order_id']) ?></strong></td><td><?= msk_e($h['client_name'] ?? '—') ?></td><td><?= $h['old_status'] ? msk_status_label($h['old_status']) : '— new —' ?></td><td><?= msk_badge($h['new_status']) ?></td><td><?= msk_e($h['changed_by'] ?? '—') ?></td></tr><?php endforeach; ?>
  </tbody></table>
<?php elseif ($tab === 'measurements'):
  $rows = msk_measurement_history((int)($_GET['client_id'] ?? 0)); ?>
  <table class="data-table"><thead><tr><th>When</th><th>Client</th><th>Phone</th><th>Snapshot</th><th>By</th></tr></thead><tbody>
  <?php if (!$rows): ?><tr><td colspan="5" style="text-align:center;padding:40px;color:#999">No measurement history yet.</td></tr><?php endif; ?>
  <?php foreach ($rows as $m): $snap=json_decode($m['snapshot'] ?? '{}',true)?:[]; ?><tr><td><?= date('d M Y, H:i', strtotime($m['changed_at'])) ?></td><td><strong><?= msk_e($m['client_name'] ?? '—') ?></strong></td><td><?= msk_e($m['client_phone'] ?? '—') ?></td><td style="font-size:12px;color:#555;font-family:sans-serif"><?php $parts=[]; foreach($snap as $k=>$v){if($v!==''&&$v!==null)$parts[]=ucwords(str_replace('_',' ',$k)).': '.$v;} echo msk_e(implode(' · ',$parts)?:'—'); ?></td><td><?= msk_e($m['changed_by'] ?? '—') ?></td></tr><?php endforeach; ?>
  </tbody></table>
<?php elseif ($tab === 'customers'):
  $clients = msk_get_all_clients(); ?>
  <table class="data-table"><thead><tr><th>Client</th><th>Phone</th><th>Registered</th><th>Orders</th><th>Total Value</th><th>Last Order</th><th></th></tr></thead><tbody>
  <?php foreach ($clients as $c): $o=msk_get_client_orders((int)$c['id']); $val=array_sum(array_map(fn($x)=>(float)$x['price'],$o)); ?><tr><td><strong><?= msk_e($c['name']) ?></strong></td><td><?= msk_e($c['phone']) ?></td><td><?= date('d M Y',strtotime($c['created_at'])) ?></td><td><?= count($o) ?></td><td><?= msk_money($val) ?></td><td><?= $o?date('d M Y',strtotime($o[0]['created_at'])):'—' ?></td><td><a class="btn-sm btn-dark" href="orders.php?client_id=<?= (int)$c['id'] ?>">Orders</a></td></tr><?php endforeach; ?>
  <?php if (!$clients): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:#999">No customers yet.</td></tr><?php endif; ?>
  </tbody></table>
<?php else:
  $rows=msk_payments(); ?>
  <table class="data-table"><thead><tr><th>When</th><th>Order</th><th>Client</th><th>Amount</th><th>Method</th><th>Reference</th><th>By</th></tr></thead><tbody>
  <?php if (!$rows): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:#999">No payments recorded yet.</td></tr><?php endif; ?>
  <?php foreach($rows as $p): ?><tr><td><?= date('d M Y, H:i',strtotime($p['paid_at'])) ?></td><td><strong><?= msk_e($p['order_id']) ?></strong></td><td><?= msk_e($p['client_name']??'—') ?></td><td><?= msk_money((float)$p['amount']) ?></td><td><?= ucfirst(msk_e($p['method'])) ?></td><td><?= msk_e($p['reference']??'—') ?></td><td><?= msk_e($p['recorded_by']??'—') ?></td></tr><?php endforeach; ?>
  </tbody></table>
<?php endif; ?>
</div>
<?php msk_page_end(); ?>
