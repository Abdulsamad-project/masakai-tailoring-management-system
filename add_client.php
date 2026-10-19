<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
msk_require_login();
msk_require_permission('clients.manage');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = msk_s($_POST['name']  ?? '');
    $phone = msk_s($_POST['phone'] ?? '');
    $email = msk_s($_POST['email'] ?? '');
    $addr  = msk_s($_POST['address'] ?? '');

    if (!$name || !$phone) {
        $error = 'Full name and phone number are required.';
    } else {
        $conn->query("INSERT INTO clients (name, phone, email, address) VALUES ('$name','$phone','$email','$addr')");
        if ($conn->insert_id) {
            $clientId = (int)$conn->insert_id;
            // Measurements
            
            foreach ($nums as $n) {
                $v = (float)($_POST[$n] ?? 0);
                if ($v > 0) { $cols[] = "`$n`"; $vals[] = $v; }
            }
            $notes = msk_s($_POST['m_notes'] ?? '');
            $colStr = implode(',', $cols);
            $valStr = implode(',', $vals);
            if ($colStr) {
                $conn->query("INSERT INTO measurements (client_id, garment_type, sleeve_type, $colStr, notes)
                              VALUES ($clientId, '$gt', '$sleeveType', $valStr, '$notes')");
            } else {
                $conn->query("INSERT INTO measurements (client_id, garment_type, sleeve_type, notes)
                              VALUES ($clientId, '$gt', '$sleeveType', '$notes')");
            }
            // Measurement history snapshot
            $snap = ['garment_type' => $gt, 'sleeve_type' => $sleeveType, 'notes' => $notes];
            foreach ($nums as $n) { $v = (float)($_POST[$n] ?? 0); if ($v > 0) $snap[$n] = $v; }
            msk_log_measurement($clientId, $snap, msk_current_username());
            header("Location: clients.php?msg=" . urlencode("Client '$name' registered successfully."));
            exit;
        } else {
            $error = 'Error: ' . htmlspecialchars($conn->error) . ' — phone may already exist.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register Client — Masakai Tailoring Service</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dash-body">
<div class="dash-wrap">
<?php include __DIR__ . '/../includes/sidebar.php'; ?>
<div class="dash-main">
  <div class="topbar">
    <div class="topbar-title">&#43; Register New Client</div>
    <div class="topbar-user"><?= msk_e($_SESSION['msk_username'] ?? '') ?></div>
  </div>
  <div class="dash-content">
    <?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <div class="page-header">
      <h2>Register New Client</h2>
      <a href="clients.php" class="btn-ghost">&#8592; Back to Clients</a>
    </div>

    <div class="form-card">
    <form method="POST">

      <fieldset>
        <legend>Client Information</legend>
        <div class="form-row">
          <div class="form-group"><label>Full Name *</label><input type="text" name="name" required placeholder="Musa Bello" value="<?= msk_e($_POST['name'] ?? '') ?>"></div>
          <div class="form-group"><label>Phone Number *</label><input type="tel" name="phone" required placeholder="08012345678" value="<?= msk_e($_POST['phone'] ?? '') ?>"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Email Address</label><input type="email" name="email" placeholder="musa@email.com" value="<?= msk_e($_POST['email'] ?? '') ?>"></div>
          <div class="form-group"><label>Home / Office Address</label><input type="text" name="address" placeholder="14 Sultan Road, Sokoto" value="<?= msk_e($_POST['address'] ?? '') ?>"></div>
        </div>
      </fieldset>

      <fieldset>
        <legend>Measurement Profile</legend>
        <div class="form-group">
          <label>Primary Garment Type</label>
          <select name="garment_type" id="garment_type">
            <?php foreach (['babban_riga'=>'Babban Riga','agbada'=>'Agbada','kaftan'=>'Kaftan',
                            'slim_fit'=>'Slim Fit Suit','custom_suit'=>'Custom Suit','bridal'=>'Bridal Gown',
                            'alterations'=>'Alterations','casual_wear'=>'Casual Wear','other'=>'Other'] as $v=>$l): ?>
              <option value="<?= $v ?>"><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- ── BABBAN RIGA / AGBADA: Traditional robe ─── -->
        <div data-mg="grp-traditional">
          <p class="sec-lbl">Traditional Robe Measurements (inches)</p>
          <div class="form-row4">
            <div class="form-group"><label>Chest</label><input type="number" step=".5" name="chest" placeholder="44"></div>
            <div class="form-group"><label>Shoulder</label><input type="number" step=".5" name="shoulder" placeholder="18"></div>
            <div class="form-group"><label>Sleeve Length</label><input type="number" step=".5" name="sleeve" placeholder="34"></div>
            <div class="form-group"><label>Round Sleeve</label><input type="number" step=".5" name="round_sleeve" placeholder="16"></div>
          </div>
          <div class="form-row4">
            <div class="form-group"><label>Gown Length</label><input type="number" step=".5" name="length" placeholder="60"></div>
            <div class="form-group"><label>Back Length</label><input type="number" step=".5" name="back_length" placeholder="28"></div>
            <div class="form-group"><label>Front Length</label><input type="number" step=".5" name="front_length" placeholder="26"></div>
            <div class="form-group"><label>Neck</label><input type="number" step=".5" name="neck" placeholder="15"></div>
          </div>
        </div>

        <!-- ── BABBAN RIGA: Inner trouser ─── -->
        <div data-mg="grp-babban">
          <p class="sec-lbl">Babban Riga — Inner Trouser</p>
          <div class="form-row4">
            <div class="form-group"><label>Trouser Waist</label><input type="number" step=".5" name="trouser_waist" placeholder="34"></div>
            <div class="form-group"><label>Trouser Length</label><input type="number" step=".5" name="trouser_length" placeholder="40"></div>
            <div class="form-group"><label>Thigh</label><input type="number" step=".5" name="thigh" placeholder="24"></div>
            <div class="form-group"><label>Knee</label><input type="number" step=".5" name="knee" placeholder="16"></div>
          </div>
        </div>

        <!-- ── AGBADA: Trouser + Sokoto ─── -->
        <div data-mg="grp-trouser">
          <p class="sec-lbl">Agbada — Sokoto (Inner Trousers)</p>
          <div class="form-row4">
            <div class="form-group"><label>Trouser Waist</label><input type="number" step=".5" name="trouser_waist" placeholder="34"></div>
            <div class="form-group"><label>Trouser Length</label><input type="number" step=".5" name="trouser_length" placeholder="40"></div>
            <div class="form-group"><label>Thigh</label><input type="number" step=".5" name="thigh" placeholder="24"></div>
            <div class="form-group"><label>Ankle</label><input type="number" step=".5" name="ankle" placeholder="14"></div>
          </div>
        </div>

        <!-- ── KAFTAN ─── -->
        <div data-mg="grp-kaftan">
          <p class="sec-lbl">Kaftan / Senator Measurements (inches)</p>
          <div class="form-row4">
            <div class="form-group"><label>Chest</label><input type="number" step=".5" name="chest" placeholder="40"></div>
            <div class="form-group"><label>Length</label><input type="number" step=".5" name="length" placeholder="50"></div>
            <div class="form-group"><label>Sleeve</label><input type="number" step=".5" name="sleeve" placeholder="28"></div>
            <div class="form-group"><label>Round Sleeve</label><input type="number" step=".5" name="round_sleeve" placeholder="14"></div>
          </div>
          <div class="form-row4">
            <div class="form-group"><label>Shoulder</label><input type="number" step=".5" name="shoulder" placeholder="17"></div>
            <div class="form-group"><label>Back Length</label><input type="number" step=".5" name="back_length" placeholder="26"></div>
            <div class="form-group"><label>Front Length</label><input type="number" step=".5" name="front_length" placeholder="24"></div>
            <div class="form-group"><label>Neck</label><input type="number" step=".5" name="neck" placeholder="15"></div>
          </div>
          <p class="sec-lbl" style="margin-top:10px">Kaftan Trouser</p>
          <div class="form-row4">
            <div class="form-group"><label>Trouser Waist</label><input type="number" step=".5" name="trouser_waist" placeholder="32"></div>
            <div class="form-group"><label>Trouser Length</label><input type="number" step=".5" name="trouser_length" placeholder="38"></div>
            <div class="form-group"><label>Thigh</label><input type="number" step=".5" name="thigh" placeholder="22"></div>
            <div class="form-group"><label>Ankle</label><input type="number" step=".5" name="ankle" placeholder="14"></div>
          </div>
        </div>

        <!-- ── FORMAL SUIT ─── -->
        <div data-mg="grp-suit">
          <p class="sec-lbl">Formal Suit — Jacket (inches)</p>
          <div class="form-row4">
            <div class="form-group"><label>Neck</label><input type="number" step=".5" name="neck" placeholder="15.5"></div>
            <div class="form-group"><label>Shoulder</label><input type="number" step=".5" name="shoulder" placeholder="18.5"></div>
            <div class="form-group"><label>Chest</label><input type="number" step=".5" name="chest" placeholder="42"></div>
            <div class="form-group"><label>Sleeve</label><input type="number" step=".5" name="sleeve" placeholder="34"></div>
          </div>
          <div class="form-row4">
            <div class="form-group"><label>Waist</label><input type="number" step=".5" name="waist" placeholder="34"></div>
            <div class="form-group"><label>Hip</label><input type="number" step=".5" name="hip" placeholder="40"></div>
            <div class="form-group"><label>Jacket Length</label><input type="number" step=".5" name="length" placeholder="30"></div>
            <div class="form-group"><label>Inseam</label><input type="number" step=".5" name="inseam" placeholder="32"></div>
          </div>
        </div>

        <!-- ── SUIT TROUSER ─── -->
        <div data-mg="grp-suit-trouser">
          <p class="sec-lbl">Suit Trouser</p>
          <div class="form-row4">
            <div class="form-group"><label>Trouser Waist</label><input type="number" step=".5" name="trouser_waist" placeholder="34"></div>
            <div class="form-group"><label>Trouser Length</label><input type="number" step=".5" name="trouser_length" placeholder="42"></div>
            <div class="form-group"><label>Thigh</label><input type="number" step=".5" name="thigh" placeholder="22"></div>
            <div class="form-group"><label>Ankle</label><input type="number" step=".5" name="ankle" placeholder="14"></div>
          </div>
        </div>

        <!-- ── BRIDAL ─── -->
        <div data-mg="grp-bridal">
          <p class="sec-lbl">Bridal Measurements (inches)</p>
          <div class="form-row4">
            <div class="form-group"><label>Bust</label><input type="number" step=".5" name="chest" placeholder="36"></div>
            <div class="form-group"><label>Waist</label><input type="number" step=".5" name="waist" placeholder="28"></div>
            <div class="form-group"><label>Hip</label><input type="number" step=".5" name="hip" placeholder="38"></div>
            <div class="form-group"><label>Dress Length</label><input type="number" step=".5" name="length" placeholder="60"></div>
          </div>
          <div class="form-row4">
            <div class="form-group"><label>Shoulder</label><input type="number" step=".5" name="shoulder" placeholder="14"></div>
            <div class="form-group"><label>Sleeve</label><input type="number" step=".5" name="sleeve" placeholder="24"></div>
            <div class="form-group"><label>Back Length</label><input type="number" step=".5" name="back_length" placeholder="16"></div>
            <div class="form-group"><label>Front Length</label><input type="number" step=".5" name="front_length" placeholder="15"></div>
          </div>
        </div>

        <!-- ── GENERAL ─── -->
        <div data-mg="grp-general">
          <p class="sec-lbl">General Measurements (inches)</p>
          <div class="form-row4">
            <div class="form-group"><label>Neck</label><input type="number" step=".5" name="neck" placeholder="14.5"></div>
            <div class="form-group"><label>Shoulder</label><input type="number" step=".5" name="shoulder" placeholder="17"></div>
            <div class="form-group"><label>Chest</label><input type="number" step=".5" name="chest" placeholder="38"></div>
            <div class="form-group"><label>Sleeve</label><input type="number" step=".5" name="sleeve" placeholder="32.5"></div>
          </div>
          <div class="form-row4">
            <div class="form-group"><label>Waist</label><input type="number" step=".5" name="waist" placeholder="30"></div>
            <div class="form-group"><label>Hip</label><input type="number" step=".5" name="hip" placeholder="36"></div>
            <div class="form-group"><label>Length</label><input type="number" step=".5" name="length" placeholder="28.5"></div>
            <div class="form-group"><label>Inseam</label><input type="number" step=".5" name="inseam" placeholder="30"></div>
          </div>
        </div>

        <!-- ── SLEEVE CONFIGURATION (v2) ─── -->
        <p class="sec-lbl" style="margin-top:14px">Sleeve Type</p>
        <div class="form-row">
          <div class="form-group">
            <label>Select Sleeve Type *</label>
            <select name="sleeve_type" id="sleeveType" onchange="mskToggleSleeve()">
              <option value="round">Round Sleeve</option>
              <option value="long">Long Sleeve</option>
            </select>
          </div>
        </div>
        <div id="sleeveRound">
          <div class="form-row4">
            <div class="form-group"><label>Round Sleeve</label><input type="number" step=".5" name="round_sleeve" placeholder="16"></div>
          </div>
        </div>
        <div id="sleeveLong" style="display:none">
          <div class="form-row4">
            <div class="form-group"><label>Sleeve Length</label><input type="number" step=".5" name="sleeve_length" placeholder="24"></div>
            <div class="form-group"><label>Wrist</label><input type="number" step=".5" name="wrist" placeholder="8"></div>
            <div class="form-group"><label>Arm Hole</label><input type="number" step=".5" name="arm_hole" placeholder="18"></div>
          </div>
        </div>

        <div class="form-group" style="margin-top:12px">
          <label>Measurement Notes</label>
          <textarea name="m_notes" rows="2" placeholder="Special fitting notes, posture, preferences..."></textarea>
        </div>
      </fieldset>

      <div class="form-actions">
        <button type="submit" class="btn-gold">Register Client</button>
        <a href="clients.php" class="btn-ghost">Cancel</a>
      </div>
    </form>
    </div>
  </div>
</div>
</div>
<script src="../assets/js/script.js"></script>
</body>
</html>
