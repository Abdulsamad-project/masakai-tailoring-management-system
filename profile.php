<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

msk_require_login();
msk_require_permission('profile.view');

$id = msk_current_user_id();
$user = null;
$stmt = $conn->prepare("SELECT id, username, full_name, phone, role, is_active FROM admin_users WHERE id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}
if (!$user) die('Account not found.');

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full = msk_s($_POST['full_name'] ?? '');
    $phone = msk_s($_POST['phone'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($newPassword !== '' && $newPassword !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $stmt = $conn->prepare("UPDATE admin_users SET full_name = ?, phone = ? WHERE id = ?");
        $stmt->bind_param('ssi', $full, $phone, $id);
        $stmt->execute();

        if ($newPassword !== '') {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param('si', $hash, $id);
            $stmt->execute();
        }

        $msg = 'Profile updated successfully.';
        $user['full_name'] = $full;
        $user['phone'] = $phone;
    }
}

msk_page_start('Profile', '👤 My Profile');
msk_flash();
?>
<div class="page-header">
  <h2>My Profile</h2>
  <span class="role-badge">Role: <?= msk_e(msk_role_label()) ?></span>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= msk_e($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= msk_e($error) ?></div><?php endif; ?>

<div class="form-card" style="max-width:760px">
  <form method="POST">
    <fieldset>
      <legend>Account Information</legend>
      <div class="form-row">
        <div class="form-group">
          <label>Email / Username</label>
          <input type="text" value="<?= msk_e($user['username']) ?>" disabled>
        </div>
        <div class="form-group">
          <label>Role</label>
          <input type="text" value="<?= msk_e(msk_role_label($user['role'])) ?>" disabled>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" value="<?= msk_e($user['full_name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="text" name="phone" value="<?= msk_e($user['phone'] ?? '') ?>">
        </div>
      </div>
    </fieldset>

    <fieldset>
      <legend>Change Password</legend>
      <div class="form-row">
        <div class="form-group">
          <label>New Password</label>
          <input type="password" name="new_password" autocomplete="new-password">
        </div>
        <div class="form-group">
          <label>Confirm New Password</label>
          <input type="password" name="confirm_password" autocomplete="new-password">
        </div>
      </div>
    </fieldset>

    <div class="form-actions">
      <button class="btn-gold" type="submit">Save Changes</button>
    </div>
  </form>
</div>
<?php msk_page_end(); ?>
