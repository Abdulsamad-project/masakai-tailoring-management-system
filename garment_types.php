<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
msk_require_login();
msk_require_permission('types.view');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    msk_require_permission('types.manage');
    $id = (int)($_POST['id'] ?? 0);
    $name = msk_s($_POST['name'] ?? '');
    $code = msk_s(strtolower(str_replace(' ', '_', $_POST['code'] ?? $_POST['name'] ?? '')));
    $price = (float)($_POST['base_price'] ?? 0);
    $desc = msk_s($_POST['description'] ?? '');
    $act  = isset($_POST['is_active']) ? 1 : 0;
    $a = $_POST['action'] ?? '';
    if ($a === 'add' && $name)      $conn->query("INSERT INTO garment_types (name,code,base_price,description,is_active) VALUES ('$name','$code',$price,'$desc',1)");
    if ($a === 'edit' && $id)       $conn->query("UPDATE garment_types SET name='$name',code='$code',base_price=$price,description='$desc',is_active=$act WHERE id=$id");
    if ($a === 'delete' && $id)     $conn->query("DELETE FROM garment_types WHERE id=$id");
    header('Location: garment_types.php?msg=' . urlencode('Garment types updated.') . ($conn->error ? '&err=' . urlencode($conn->error) : '')); exit;
}
$types = msk_garment_types(false);
$editId = (int)($_GET['edit'] ?? 0); $editing = null;
foreach ($types as $t) if ((int)$t['id'] === $editId) $editing = $t;
$canManage = msk_can('types.manage');
msk_page_start('Garment Types', '&#128087; Garment Type Management');
msk_flash();
?>
<div class="page-header"><h2>Garment Types (<?= count($types) ?>)</h2></div>
<?php if ($canManage): ?>
<div class="form-card">
  <form method="POST">
    <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
    <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">
    <fieldset><legend><?= $editing ? 'Edit Garment Type' : 'Add Garment Type' ?></legend>
      <div class="form-row3">
        <div class="form-group"><label>Name *</label><input type="text" name="name" required value="<?= msk_e($editing['name'] ?? '') ?>" placeholder="Agbada"></div>
        <div class="form-group"><label>Code</label><input type="text" name="code" value="<?= msk_e($editing['code'] ?? '') ?>" placeholder="agbada"></div>
        <div class="form-group"><label>Base Price (&#8358;)</label><input type="number" step="0.01" name="base_price" value="<?= msk_e($editing['base_price'] ?? '0') ?>"></div>
      </div>
      <div class="form-group"><label>Description</label><input type="text" name="description" value="<?= msk_e($editing['description'] ?? '') ?>"></div>
      <?php if ($editing): ?><label style="font-size:13px"><input type="checkbox" name="is_active" <?= (int)$editing['is_active'] ? 'checked' : '' ?>> Active</label><?php endif; ?>
    </fieldset>
    <div class="form-actions">
      <button class="btn-gold" type="submit"><?= $editing ? 'Save Changes' : 'Add Garment Type' ?></button>
      <?php if ($editing): ?><a class="btn-ghost" href="garment_types.php">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>
<?php endif; ?>
<div class="tbox"><table class="data-table">
  <thead><tr><th>Name</th><th>Code</th><th>Base Price</th><th>Description</th><th>Status</th><?php if ($canManage): ?><th>Actions</th><?php endif; ?></tr></thead>
  <tbody>
  <?php foreach ($types as $t): ?>
    <tr>
      <td><strong><?= msk_e($t['name']) ?></strong></td>
      <td><?= msk_e($t['code']) ?></td>
      <td><?= msk_money((float)$t['base_price']) ?></td>
      <td><?= msk_e($t['description'] ?: '—') ?></td>
      <td><?= (int)$t['is_active'] ? '<span class="badge badge-completed">Active</span>' : '<span class="badge badge-pending">Inactive</span>' ?></td>
      <?php if ($canManage): ?>
      <td style="white-space:nowrap">
        <a class="btn-sm btn-dark" href="garment_types.php?edit=<?= (int)$t['id'] ?>">Edit</a>
        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this garment type?')">
          <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
          <button class="btn-sm" style="background:#ef4444;color:#fff">Delete</button>
        </form>
      </td>
      <?php endif; ?>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php msk_page_end(); ?>
