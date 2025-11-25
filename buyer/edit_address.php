<?php
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');
require_login();

$user_id = $_SESSION['user']['id'] ?? null;
if (!$user_id) {
    header("Location: /kamulan-system/buyer/cart.php");
    exit;
}

$msg = "";

// --- Fetch existing user address and phone ---
$stmt = $pdo->prepare("SELECT house_number, street, barangay, municipality, province, nearest_landmark, phone FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $house_number = trim($_POST['house_number'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $municipality = trim($_POST['municipality'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $nearest_landmark = trim($_POST['nearest_landmark'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($street === '' || $barangay === '' || $municipality === '' || $province === '' || $phone === '') {
        $msg = "⚠️ Please fill in all required fields (house number is optional).";
    } else {
        $update = $pdo->prepare("UPDATE users SET house_number=?, street=?, barangay=?, municipality=?, province=?, nearest_landmark=?, phone=? WHERE id=?");
        $update->execute([$house_number, $street, $barangay, $municipality, $province, $nearest_landmark, $phone, $user_id]);
        $msg = "✅ Address and contact info updated successfully!";
        // refresh user data
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Edit Address & Contact</title>
<style>
body { font-family:'Poppins',sans-serif; background:#f4f6f4; margin:0; }
main { max-width:600px; margin:50px auto; background:white; padding:25px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
h2 { color:#4b5320; margin-bottom:20px; }
label { display:block; margin:10px 0 4px; font-weight:bold; }
input { width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; font-size:14px; }
.button { margin-top:15px; background:linear-gradient(90deg,#4b5320,#3e4425); color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; transition:0.2s; }
.button:hover { background:linear-gradient(90deg,#3e4425,#4b5320); }
.success { background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:15px; }
.error { background:#f8d7da; color:#721c24; padding:10px; border-radius:6px; margin-bottom:15px; }
</style>
</head>
<body>
<?php include(__DIR__ . '/../_partials/header.php'); ?>
<main>
<h2>Edit Address & Contact</h2>
<?php if($msg) echo "<div class='".(strpos($msg,'⚠️')!==false?'error':'success')."'>$msg</div>"; ?>

<form method="post">
<label>House Number (optional)</label>
<input type="text" name="house_number" value="<?=htmlspecialchars($user['house_number'])?>">

<label>Street <span style="color:red">*</span></label>
<input type="text" name="street" value="<?=htmlspecialchars($user['street'])?>" required>

<label>Barangay <span style="color:red">*</span></label>
<input type="text" name="barangay" value="<?=htmlspecialchars($user['barangay'])?>" required>

<label>Municipality <span style="color:red">*</span></label>
<input type="text" name="municipality" value="<?=htmlspecialchars($user['municipality'])?>" required>

<label>Province <span style="color:red">*</span></label>
<input type="text" name="province" value="<?=htmlspecialchars($user['province'])?>" required>

<label>Nearest Landmark (optional)</label>
<input type="text" name="nearest_landmark" value="<?=htmlspecialchars($user['nearest_landmark'])?>">

<label>Phone <span style="color:red">*</span></label>
<input type="text" name="phone" value="<?=htmlspecialchars($user['phone'])?>" required>

<button type="submit" class="button">Save Changes</button>
</form>

<p style="margin-top:20px;"><a href="/kamulan-system/buyer/cart.php" class="button" style="background:#3e4425;">Back to Cart</a></p>
</main>
<?php include(__DIR__ . '/../_partials/footer.php'); ?>
</body>
</html>