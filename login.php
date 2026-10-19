```php
<?php
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

/*
 * If already logged in, send the user to the correct workspace.
 */
if (!empty($_SESSION['msk_logged_in']) && $_SESSION['msk_logged_in'] === true) {
    $role = $_SESSION['msk_role'] ?? 'admin';

    switch ($role) {
        case 'customer':
            header('Location: customer_portal.php');
            break;

        case 'tailor':
            header('Location: admin/my_tasks.php');
            break;

        case 'receptionist':
        case 'admin':
        default:
            header('Location: admin/dashboard.php');
            break;
    }

    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {

        /*
         * Find the account without asking the user to select a role.
         */
        $stmt = $conn->prepare("
            SELECT
                id,
                username,
                password_hash,
                role,
                client_id,
                is_active
            FROM admin_users
            WHERE username = ?
            LIMIT 1
        ");

        $account = null;

        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();

            $result = $stmt->get_result();
            $account = $result ? $result->fetch_assoc() : null;

            $stmt->close();
        }

        if (!$account || !password_verify($password, $account['password_hash'])) {

            $error = 'Invalid username or password.';

        } elseif ((int)$account['is_active'] !== 1) {

            $error = 'This account has been deactivated. Contact the administrator.';

        } else {

            /*
             * Login successful.
             *
             * The role is detected automatically from the database.
             */
            session_regenerate_id(true);

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = (int)$account['id'];
            $_SESSION['admin_username'] = $account['username'];

            $_SESSION['msk_logged_in'] = true;
            $_SESSION['msk_username'] = $account['username'];
            $_SESSION['msk_id'] = (int)$account['id'];
            $_SESSION['msk_role'] = $account['role'];
            $_SESSION['msk_client_id'] = (int)($account['client_id'] ?? 0);

            /*
             * Automatically redirect according to the detected role.
             */
            switch ($account['role']) {

                case 'customer':
                    header('Location: customer_portal.php');
                    break;

                case 'tailor':
                    header('Location: admin/my_tasks.php');
                    break;

                case 'receptionist':
                case 'admin':
                default:
                    header('Location: admin/dashboard.php');
                    break;
            }

            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login — Masakai</title>

<link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="auth-body">

<div class="portal-split">

    <div
        class="portal-visual portal-visual-admin"
        style="background-image: url('assets/tailor-working.jpg');"
    >

        <div class="portal-visual-overlay"></div>

        <div class="portal-visual-content">

            <span class="portal-kicker">
                MASAKAI TAILORING SERVICE
            </span>

            <h1>Welcome to <em>Masakai</em></h1>

            <p>
                Tailoring Service Management System
            </p>

            <div class="portal-feature">
                🧵 One system. Every workspace.
            </div>

        </div>
    </div>


    <div class="portal-login">

        <div class="auth-card portal-auth-card">

            <div class="portal-icon">🔐</div>

            <div class="auth-brand">
                Masakai
            </div>

            <div class="auth-tagline">
                Tailoring Service Management System
            </div>

            <h2 class="auth-heading">
                Sign In
            </h2>

            <p class="portal-subtitle">
                Enter your account details to continue.
            </p>


            <?php if ($error): ?>

                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <form method="POST" autocomplete="off">

                <div class="form-group">

                    <label for="username">
                        Email / Username
                    </label>

                    <input
                        id="username"
                        type="text"
                        name="username"
                        required
                        autocomplete="username"
                        placeholder="Enter your username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >

                </div>


                <button
                    class="btn-gold btn-full"
                    type="submit"
                >
                    Sign In →
                </button>

            </form>


            <div class="portal-switch">
                <span>
                    Your role will be detected automatically.
                </span>
            </div>

        </div>

    </div>

</div>

</body>
</html>
```
