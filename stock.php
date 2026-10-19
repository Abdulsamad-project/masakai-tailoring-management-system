<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
msk_require_login();
msk_require_permission('stock.view');

$error = '';
$action = $_POST['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    msk_require_permission('stock.manage');
    $id       = (int)($_POST['id'] ?? 0);
    $name     = msk_s($_POST['name'] ?? '');
    $category = msk_s($_POST['category'] ?? '');
    $qty      = (float)($_POST['quantity'] ?? 0);
    $unit     = msk_s($_POST['unit'] ?? 'yards');
    $cost     = (float)($_POST['unit_cost'] ?? 0);
    $reorder  = (float)($_POST['reorder_level'] ?? 0);
    $supplier = msk_s($_POST['supplier'] ?? '');
    $notes    = msk_s($_POST['notes'] ?? '');

    if ($action === 'add' && $name) {
        $conn->query("INSERT INTO stock_items (name,category,quantity,unit,unit_cost,reorder_level,supplier,notes)
                      VALUES ('$name','$category',$qty,'$unit',$cost,$reorder,'$supplier','$notes')");
        header('Location: stock.php?msg=' . urlencode("Stock item '$name' added.")); exit;
    }
    if ($action === 'edit' && $id) {
        $conn->query("UPDATE stock_items SET name='$name',category='$category',quantity=$qty,unit='$unit',
                      unit_cost=$cost,reorder_level=$reorder,supplier='$supplier',notes='$notes' WHERE id=$id");
        header('Location: stock.php?msg=' . urlencode('Stock item updated.')); exit;
    }
    if ($action === 'delete' && $id) {
        $conn->query("DELETE FROM stock_items WHERE id=$id");
        header('Location: stock.php?msg=' . urlencode('Stock item deleted.')); exit;
    }
}

$search = trim($_GET['q'] ?? '');
$items  = msk_stock_items($search);
$low    = msk_low_stock();
$editId = (int)($_GET['edit'] ?? 0);
$editing = null;
foreach ($items as $it) if ((int)$it['id'] === $editId) $editing = $it;
$canManage = msk_can('stock.manage');

msk_page_start('Stock', '&#128230; Stock Management');
msk_flash();
?>
<?php if ($low): ?>
<div class="alert alert-error">
  &#9888; <strong>Low stock alert:</strong>
  <?php $names = array_map(fn($l) => $l['name'] . ' (' . rtrim(rtrim(number_format((float)$l['quantity'],2),'0'),'.') . ' ' . $l['unit'] . ')', $low);
        echo msk_e(implode(', ', $names)); ?>
</div>
<?php endif; ?>

<div class="page-header">
  <h2>Stock Items (<?= count($items) ?>)</h2>
  <form method="GET" style="display:flex;gap:8px">
    <input type="text" name="q" placeholder="Search stock..." value="<?= msk_e($search) ?>"
           style="padding:8px 14px;border:1px solid #ddd;border-radius:6px;font-size:13px">
    <button class="btn-sm btn-dark" type="submit">Search</button>
  </form>
</div>

<?php if ($canManage): ?>
<div class="form-card">
  <form method="POST">
    <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
    <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">
    <fieldset>
      <legend><?= $editing ? 'Edit Stock Item' : 'Add Stock Item' ?></legend>
      <div class="form-row3">
        <div class="form-group"><label>Item Name *</label><input type="text" name="name" required value="<?= msk_e($editing['name'] ?? '') ?>" placeholder="White Guinea Brocade"></div>
        <div class="form-group"><label>Category</label><input type="text" name="category" value="<?= msk_e($editing['category'] ?? '') ?>" placeholder="Fabric"></div>
        <div class="form-group"><label>Supplier</label><input type="text" name="supplier" value="<?= msk_e($editing['supplier'] ?? '') ?>" placeholder="Kano Textile Market"></div>
      </div>
      <div class="form-row4">
        <div class="form-group"><label>Quantity</label><input type="number" step="0.01" name="quantity" value="<?= msk_e($editing['quantity'] ?? '0') ?>"></div>
        <div class="form-group"><label>Unit</label><input type="text" name="unit" value="<?= msk_e($editing['unit'] ?? 'yards') ?>"></div>
        <div class="form-group"><label>Unit Cost (&#8358;)</label><input type="number" step="0.01" name="unit_cost" value="<?= msk_e($editing['unit_cost'] ?? '0') ?>"></div>
        <div class="form-group"><label>Low Stock Level</label><input type="number" step="0.01" name="reorder_level" value="<?= msk_e($editing['reorder_level'] ?? '5') ?>"></div>
      </div>
      <div class="form-group"><label>Notes</label><input type="text" name="notes" value="<?= msk_e($editing['notes'] ?? '') ?>"></div>
    </fieldset>
    <div class="form-actions">
      <button class="btn-gold" type="submit"><?= $editing ? 'Save Changes' : 'Add Stock' ?></button>
      <?php if ($editing): ?><a class="btn-ghost" href="stock.php">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="tbox">
  <table class="data-table">
    <thead><tr><th>Item</th><th>Category</th><th>Qty</th><th>Unit Cost</th><th>Stock Value</th><th>Status</th><th>Supplier</th><?php if ($canManage): ?><th>Actions</th><?php endif; ?></tr></thead>
    <tbody>
    <?php if (!$items): ?><tr><td colspan="8" style="text-align:center;padding:40px;color:#999">No stock items.</td></tr><?php endif; ?>
    <?php foreach ($items as $it):
      $isLow = (float)$it['quantity'] <= (float)$it['reorder_level']; ?>
      <tr class="<?= $isLow ? 'row-overdue' : '' ?>">
        <td><strong><?= msk_e($it['name']) ?></strong></td>
        <td><?= msk_e($it['category'] ?: '—') ?></td>
        <td><?= rtrim(rtrim(number_format((float)$it['quantity'],2),'0'),'.') ?> <?= msk_e($it['unit']) ?></td>
        <td><?= msk_money((float)$it['unit_cost']) ?></td>
        <td><?= msk_money((float)$it['unit_cost'] * (float)$it['quantity']) ?></td>
        <td><?= $isLow ? '<span class="badge badge-pending">Low Stock</span>' : '<span class="badge badge-completed">OK</span>' ?></td>
        <td><?= msk_e($it['supplier'] ?: '—') ?></td>
        <?php if ($canManage): ?>
        <td style="white-space:nowrap">
          <a class="btn-sm btn-dark" href="stock.php?edit=<?= (int)$it['id'] ?>">Edit</a>
          <form method="POST" style="display:inline" onsubmit="return confirm('Delete this stock item?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
            <button class="btn-sm" style="background:#ef4444;color:#fff" type="submit">Delete</button>
          </form>
        </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php msk_page_end(); ?>
