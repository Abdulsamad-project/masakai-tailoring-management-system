<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
msk_require_login();
msk_require_permission('users.manage');

$roles = ['admin','receptionist','tailor','customer'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $username = msk_s($_POST['username'] ?? '');
    $full = msk_s($_POST['full_name'] ?? '');
    $phone = msk_s($_POST['phone'] ?? '');
    $role = in_array($_POST['role'] ?? '', $roles, true) ? $_POST['role'] : 'receptionist';
    $clientId = (int)($_POST['client_id'] ?? 0);
    $cSql = $clientId ? $clientId : 'NULL';
    $act = isset($_POST['is_active']) ? 1 : 0;
    $pw = trim($_POST['password'] ?? '');
    if ($a === 'add' && $username && $pw) {
        $hash = $conn->real_escape_string(password_hash($pw, PASSWORD_BCRYPT));
        $conn->query("INSERT INTO admin_users (username,full_name,phone,password_hash,role,client_id,is_active)
                      VALUES ('$username','$full','$phone','$hash','$role',$cSql,1)");
    } elseif ($a === 'edit' && $id) {
        $conn->query("UPDATE admin_users SET username='$username',full_name='$full',phone='$phone',
                      role='$role',client_id=$cSql,is_active=$act WHERE id=$id");
        if ($pw !== '') {
            $hash = $conn->real_escape_string(password_hash($pw, PASSWORD_BCRYPT));
            $conn->query("UPDATE admin_users SET password_hash='$hash' WHERE id=$id");
        }
    } elseif ($a === 'delete' && $id && $id !== msk_current_user_id()) {
        $conn->query("DELETE FROM admin_users WHERE id=$id");
    }
    header('Location: users.php?msg=' . urlencode('User accounts updated.')); exit;
}
$users = $conn->query("SELECT * FROM admin_users ORDER BY role, username")->fetch_all(MYSQLI_ASSOC);
$clients = msk_get_all_clients();
$editId = (int)($_GET['edit'] ?? 0); $editing = null;
foreach ($users as $u) if ((int)$u['id'] === $editId) $editing = $u;
msk_page_start('Users & Roles', '&#128273; Users &amp; Roles');
msk_flash();
?>
<div class="page-header"><h2>Staff & Customer Accounts (<?= count($users) ?>)</h2></div>
<div class="form-card">
  <form method="POST">
    <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
    <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">
    <fieldset><legend><?= $editing ? 'Edit Account' : 'Create Account' ?></legend>
      <div class="form-row3">
        <div class="form-group"><label>Email / Username *</label><input type="text" name="username" required value="<?= msk_e($editing['username'] ?? '') ?>"></div>
        <div class="form-group"><label>Full Name</label><input type="text" name="full_name" value="<?= msk_e($editing['full_name'] ?? '') ?>"></div>
        <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= msk_e($editing['phone'] ?? '') ?>"></div>
      </div>
      <div class="form-row3">
        <div class="form-group"><label>Role *</label>
          <select name="role">
            <?php foreach ($roles as $r): ?><option value="<?= $r ?>" <?= ($editing['role'] ?? '')===$r?'selected':'' ?>><?= msk_role_label($r) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label>Linked Customer (for Customer role)</label>
          <select name="client_id">
            <option value="0">— None —</option>
            <?php foreach ($clients as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)($editing['client_id'] ?? 0)===(int)$c['id']?'selected':'' ?>><?= msk_e($c['name']) ?> (<?= msk_e($c['phone']) ?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label>Password <?= $editing ? '(leave blank to keep)' : '*' ?></label><input type="text" name="password" <?= $editing ? '' : 'required' ?>></div>
      </div>
      <?php if ($editing): ?><label style="font-size:13px"><input type="checkbox" name="is_active" <?= (int)$editing['is_active'] ? 'checked' : '' ?>> Active</label><?php endif; ?>
    </fieldset>
    <div class="form-actions">
      <button class="btn-gold"><?= $editing ? 'Save Changes' : 'Create Account' ?></button>
      <?php if ($editing): ?><a class="btn-ghost" href="users.php">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>
<div class="tbox"><table class="data-table">
  <thead><tr><th>Username</th><th>Name</th><th>Phone</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <tr>
      <td><strong><?= msk_e($u['username']) ?></strong></td>
      <td><?= msk_e($u['full_name'] ?? '—') ?></td>
      <td><?= msk_e($u['phone'] ?? '—') ?></td>
      <td><?= msk_e(msk_role_label($u['role'] ?? 'admin')) ?></td>
      <td><?= (int)($u['is_active'] ?? 1) ? '<span class="badge badge-completed">Active</span>' : '<span class="badge badge-pending">Disabled</span>' ?></td>
      <td style="white-space:nowrap">
        <a class="btn-sm btn-dark" href="users.php?edit=<?= (int)$u['id'] ?>">Edit</a>
        <?php if ((int)$u['id'] !== msk_current_user_id()): ?>
        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this account?')">
          <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
          <button class="btn-sm" style="background:#ef4444;color:#fff">Delete</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php msk_page_end(); ?>
