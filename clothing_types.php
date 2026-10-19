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
    $cat  = msk_s($_POST['category'] ?? '');
    $price = (float)($_POST['unit_price'] ?? 0);
    $desc = msk_s($_POST['description'] ?? '');
    $act  = isset($_POST['is_active']) ? 1 : 0;
    $a = $_POST['action'] ?? '';
    if ($a === 'add' && $name)      $conn->query("INSERT INTO clothing_types (name,category,unit_price,notes,is_active) VALUES ('$name','$cat',$price,'$desc',1)");
    if ($a === 'edit' && $id)       $conn->query("UPDATE clothing_types SET name='$name',category='$cat',unit_price=$price,notes='$desc',is_active=$act WHERE id=$id");
    if ($a === 'delete' && $id)     $conn->query("DELETE FROM clothing_types WHERE id=$id");
    header('Location: clothing_types.php?msg=' . urlencode('Garment types updated.') . ($conn->error ? '&err=' . urlencode($conn->error) : '')); exit;
}
$types = msk_clothing_types(false);
$editId = (int)($_GET['edit'] ?? 0); $editing = null;
foreach ($types as $t) if ((int)$t['id'] === $editId) $editing = $t;
$canManage = msk_can('types.manage');
msk_page_start('Clothing Types', '&#129525; Clothing Type Management');
msk_flash();
?>
<div class="page-header"><h2>Clothing Types (<?= count($types) ?>)</h2></div>
<?php if ($canManage): ?>
<div class="form-card">
  <form method="POST">
    <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
    <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">
    <fieldset><legend><?= $editing ? 'Edit Clothing Type' : 'Add Clothing Type' ?></legend>
      <div class="form-row3">
        <div class="form-group"><label>Name *</label><input type="text" name="name" required value="<?= msk_e($editing['name'] ?? '') ?>" placeholder="Guinea Brocade"></div>
        <div class="form-group"><label>Category</label><input type="text" name="category" value="<?= msk_e($editing['category'] ?? '') ?>" placeholder="Brocade"></div>
        <div class="form-group"><label>Unit Price (&#8358;)</label><input type="number" step="0.01" name="unit_price" value="<?= msk_e($editing['unit_price'] ?? '0') ?>"></div>
      </div>
      <div class="form-group"><label>Notes</label><input type="text" name="description" value="<?= msk_e($editing['notes'] ?? '') ?>"></div>
      <?php if ($editing): ?><label style="font-size:13px"><input type="checkbox" name="is_active" <?= (int)$editing['is_active'] ? 'checked' : '' ?>> Active</label><?php endif; ?>
    </fieldset>
    <div class="form-actions">
      <button class="btn-gold" type="submit"><?= $editing ? 'Save Changes' : 'Add Clothing Type' ?></button>
      <?php if ($editing): ?><a class="btn-ghost" href="clothing_types.php">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>
<?php endif; ?>
<div class="tbox"><table class="data-table">
  <thead><tr><th>Name</th><th>Category</th><th>Unit Price</th><th>Notes</th><th>Status</th><?php if ($canManage): ?><th>Actions</th><?php endif; ?></tr></thead>
  <tbody>
  <?php foreach ($types as $t): ?>
    <tr>
      <td><strong><?= msk_e($t['name']) ?></strong></td>
      <td><?= msk_e($t['category'] ?: '—') ?></td>
      <td><?= msk_money((float)$t['unit_price']) ?></td>
      <td><?= msk_e($t['notes'] ?: '—') ?></td>
      <td><?= (int)$t['is_active'] ? '<span class="badge badge-completed">Active</span>' : '<span class="badge badge-pending">Inactive</span>' ?></td>
      <?php if ($canManage): ?>
      <td style="white-space:nowrap">
        <a class="btn-sm btn-dark" href="clothing_types.php?edit=<?= (int)$t['id'] ?>">Edit</a>
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
