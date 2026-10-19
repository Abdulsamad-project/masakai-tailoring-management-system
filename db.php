<?php
/**
 * Masakai Tailoring System — Database Connection
 * Driver: MySQLi (no PDO)
 */
define('MSK_DB_HOST', 'localhost');
define('MSK_DB_USER', 'root');
define('MSK_DB_PASS', '');
define('MSK_DB_NAME', 'tailoring_db');

$conn = new mysqli(MSK_DB_HOST, MSK_DB_USER, MSK_DB_PASS, MSK_DB_NAME);

if ($conn->connect_error) {
    $setupLink = str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/')
        ? '../includes/setup.php'
        : 'includes/setup.php';
    die('
      <div style="font-family:sans-serif;background:#1A1A1A;color:#D4AF37;min-height:100vh;
                  display:flex;align-items:center;justify-content:center;padding:40px">
        <div style="max-width:480px;text-align:center">
          <div style="font-size:48px">&#9997;</div>
          <h2 style="color:#fff;margin:12px 0 8px">Masakai Tailoring</h2>
          <p style="color:#888;margin-bottom:24px">Database connection failed.<br>'
        . htmlspecialchars($conn->connect_error) .
         '</p>
          <a href="' . $setupLink . '"
             style="background:#D4AF37;color:#1A1A1A;padding:12px 28px;border-radius:8px;
                    font-weight:700;text-decoration:none">Run Setup</a>
        </div>
      </div>
    ');
}

$conn->set_charset('utf8mb4');
