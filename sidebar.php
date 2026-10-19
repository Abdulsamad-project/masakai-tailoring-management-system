<?php
/**
 * Masakai Tailoring System — role-aware sidebar.
 * Tailors intentionally receive a small work-focused navigation.
 */
if (!function_exists('msk_can')) require_once __DIR__ . '/auth.php';

$mskCurrent = basename($_SERVER['SCRIPT_NAME'] ?? '');
$mskLink = function (string $file, string $icon, string $label, string $perm = '') use ($mskCurrent) {
    if ($perm !== '' && !msk_can($perm)) return;
    $active = $mskCurrent === $file ? ' class="active"' : '';
    echo '<a href="' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '"' . $active . '>' .
         $icon . ' &nbsp;' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
};
$role = msk_role();
?>
<aside class="sidebar <?= $role === 'tailor' ? 'sidebar-tailor' : '' ?>">
  <div class="sb-brand">
    <span class="sb-name">Masakai</span>
    <span class="sb-sub">Tailoring Service</span>
    <?php if ($role === 'tailor'): ?><span class="role-chip tailor-chip">🧵 TAILOR WORKSPACE</span><?php endif; ?>
  </div>
  <nav class="sb-nav">
    <?php if ($role === 'tailor'): ?>
      <div class="sb-section">Tailor Workspace</div>
      <?php $mskLink('dashboard.php', '&#9781;', 'Dashboard', 'dashboard'); ?>
      <?php $mskLink('my_tasks.php', '&#129525;', 'My Tasks', 'tasks.view'); ?>
      <?php $mskLink('orders.php', '&#128203;', 'All Orders', 'orders.view'); ?>
      <?php $mskLink('history.php', '&#128340;', 'History', 'history.view'); ?>
      <?php $mskLink('profile.php', '&#128100;', 'Profile', 'profile.view'); ?>
    <?php else: ?>
      <div class="sb-section">Main</div>
      <?php $mskLink('dashboard.php', '&#9781;', 'Overview', 'dashboard'); ?>
      <?php $mskLink('my_tasks.php', '&#129525;', 'My Tasks', 'orders.status'); ?>

      <?php if (msk_can('clients.view')): ?><div class="sb-section">Clients</div><?php endif; ?>
      <?php $mskLink('clients.php', '&#128100;', 'All Clients', 'clients.view'); ?>
      <?php $mskLink('add_client.php', '&#43;', 'Register Client', 'clients.manage'); ?>

      <?php if (msk_can('orders.view')): ?><div class="sb-section">Orders</div><?php endif; ?>
      <?php $mskLink('orders.php', '&#128203;', 'All Orders', 'orders.view'); ?>
      <?php $mskLink('add_order.php', '&#128196;', 'New Order', 'orders.manage'); ?>
      <?php $mskLink('payments.php', '&#128181;', 'Payments', 'payments.view'); ?>

      <?php if (msk_can('history.view') || msk_can('reports.view')): ?><div class="sb-section">Insights</div><?php endif; ?>
      <?php $mskLink('history.php', '&#128340;', 'History', 'history.view'); ?>
      <?php $mskLink('reports.php', '&#128202;', 'Reports', 'reports.view'); ?>

      <?php if (msk_can('stock.view') || msk_can('types.view')): ?><div class="sb-section">Setup</div><?php endif; ?>
      <?php $mskLink('stock.php', '&#128230;', 'Stock', 'stock.view'); ?>
      <?php $mskLink('garment_types.php', '&#128087;', 'Garment Types', 'types.view'); ?>
      <?php $mskLink('clothing_types.php', '&#129525;', 'Clothing Types', 'types.view'); ?>
      <?php $mskLink('users.php', '&#128273;', 'Users & Roles', 'users.manage'); ?>
    <?php endif; ?>
  </nav>
  <div class="sb-foot">
    <div style="font-size:11px;color:#888;padding:0 0 8px;font-family:sans-serif">
      <?= msk_e(msk_current_username()) ?><br>
      <span style="color:#D4AF37"><?= msk_e(msk_role_label()) ?></span>
    </div>
    <a href="../index.php" target="_blank">&#127760; View Site</a>
    <a href="../logout.php" style="color:#ef4444">&#10007; Logout</a>
  </div>
</aside>
