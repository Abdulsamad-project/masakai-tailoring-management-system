<?php
/**
 * Masakai Tailoring System — Shared admin page shell (v2)
 */
function msk_page_start(string $title, string $topbarTitle): void {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
       . '<title>' . msk_e($title) . ' — Masakai Tailoring Service</title>'
       . '<link rel="stylesheet" href="../assets/css/style.css"></head>'
       . '<body class="dash-body"><div class="dash-wrap">';
    include __DIR__ . '/sidebar.php';
    echo '<div class="dash-main"><div class="topbar">'
       . '<div class="topbar-title">' . $topbarTitle . '</div>'
       . '<div class="topbar-user">' . msk_e(msk_current_username())
       . ' &nbsp;<span style="color:#D4AF37">' . msk_e(msk_role_label()) . '</span></div>'
       . '</div><div class="dash-content">';
}

function msk_page_end(): void {
    echo '</div></div></div><script src="../assets/js/script.js"></script></body></html>';
}

function msk_flash(): void {
    if (!empty($_GET['msg']))  echo '<div class="alert alert-success">' . msk_e($_GET['msg']) . '</div>';
    if (!empty($_GET['err']))  echo '<div class="alert alert-error">'   . msk_e($_GET['err']) . '</div>';
}
