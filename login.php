<?php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

/*
|--------------------------------------------------------------------------
| Masakai Unified Login Compatibility
|--------------------------------------------------------------------------
| The main login page is now:
|
|   /masakai/login.php
|
| All roles use the same login:
|   - Admin
|   - Receptionist
|   - Tailor
|   - Customer
|
| This file remains only as a compatibility route for old links/bookmarks.
|--------------------------------------------------------------------------
*/

// If the user is already logged in, send them to their correct workspace.
if (!empty($_SESSION['msk_logged_in']) && $_SESSION['msk_logged_in'] === true) {

    $currentRole = $_SESSION['msk_role'] ?? 'admin';

    switch ($currentRole) {

        case 'customer':
            header('Location: ../customer_portal.php');
            break;

        case 'tailor':
            header('Location: my_tasks.php');
            break;

        case 'receptionist':
        case 'admin':
        default:
            header('Location: dashboard.php');
            break;
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| Redirect to the Unified Login
|--------------------------------------------------------------------------
| The old admin login previously contained separate portal login forms.
| Those are no longer used.
|
| Anyone who visits:
|   /admin/login.php
|   /admin/login.php?portal=admin
|   /admin/login.php?portal=tailor
|   /admin/login.php?portal=receptionist
|   /admin/login.php?portal=customer
|
| will now be sent to the single login page.
|--------------------------------------------------------------------------
*/

header('Location: ../login.php');
exit;