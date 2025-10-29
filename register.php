<?php
session_start();
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../config/session.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // PHPMailer autoload

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $password   = $_POST['password'] ?? '';
    $street     = trim($_POST['street'] ?? '');
    $barangay   = trim($_POST['barangay'] ?? '');
    $municipality = trim($_POST['municipality'] ?? '');
    $province     = trim($_POST['province'] ?? '');
    $house_number = trim($_POST['house_number'] ?? '');
    $nearest_landmark = trim($_POST['nearest_landmark'] ?? '');

    // Validation
    if (!$first_name || !$last_name || !$email || !$phone || !$password || !$street || !$barangay || !$municipality || !$province || !$nearest_landmark) {
        $err = 'All required fields must be filled.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Invalid email format.';
    } elseif (!preg_match('/^\d{11}$/', $phone)) {
        $err = 'Phone number must be exactly 11 digits.';
    } elseif (strlen($password) < 6) {
        $err = 'Password must be at least 6 characters.';
    } else {
        // Determine role
        $role = 'buyer';
        $branch_id = null;
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Generate OTP
        $otp = rand(100000, 999999);

        // Save registration data in session until verified
        $_SESSION['reg_otp'] = $otp;
        $_SESSION['reg_data'] = [
            'first_name'=>$first_name,
            'last_name'=>$last_name,
            'email'=>$email,
            'phone'=>$phone,
            'password'=>$hashed_password,
            'role'=>$role,
            'branch_id'=>$branch_id,
            'street'=>$street,
            'barangay'=>$barangay,
            'municipality'=>$municipality,
            'province'=>$province,
            'house_number'=>$house_number,
            'nearest_landmark'=>$nearest_landmark
        ];

        // Send OTP via PHPMailer
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'kamulan886@gmail.com'; // YOUR Gmail
            $mail->Password = 'dgpk zwzj milc hdld';    // YOUR Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('kamulan886@gmail.com', 'Kamulan');
            $mail->addAddress($email, "$first_name $last_name");

            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email for Kamulan';
            $mail->Body = "Hi $first_name,<br><br>Your verification code (OTP) is: <b>$otp</b><br>Enter it in the next page to verify your account.";

            $mail->send();
            header('Location: verify_email.php');
            exit;

        } catch (Exception $e) {
            $err = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Register</title>
<link rel="stylesheet" href="/kamulan-system/assets/css/style.css">
<style>
body {
  font-family: "Poppins", sans-serif;
  margin: 0;
  padding: 0;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  background: url('/kamulan-system/assets/images/kamulan-backg.jpg') no-repeat center center/cover;
  overflow-y: auto;
}
body::before {
  content: "";
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(6px);
  z-index: -1;
}
main.form-card {
  background: rgba(255,255,255,0.95);
  padding: 30px;
  margin: 50px 0;
  border-radius: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.15);
  max-width: 500px;
  width: 90%;
}
main.form-card h2 {
  color: #4b5d2a;
  margin-bottom: 10px;
  text-align: center;
}
.form-card label { display:block; margin-bottom:6px; font-weight:500; color:#333; }
.form-card input { width:100%; padding:8px; border-radius:8px; border:1px solid #ccc; margin-bottom:12px; }
.name-row { display:flex; gap:10px; }
.name-row label { flex:1; }
.toggle-address { background:#6b4f2c; color:white; border:none; padding:10px; width:100%; border-radius:8px; cursor:pointer; font-weight:500; }
.toggle-address:hover { background:#83623a; }
#addressSection { display:none; margin-top:10px; padding-top:10px; border-top:1px solid #ddd; }
.button { background:#556B2F; color:white; border:none; padding:10px; width:100%; border-radius:8px; cursor:pointer; margin-top:10px; }
.button:hover { background:#6b7b44; }
.error { color:red; font-weight:500; margin-bottom:10px; text-align:center; }
</style>
</head>
<body>
<main class="form-card">
  <h2>Register</h2>
  <?php if(!empty($err)) echo '<p class="error">'.htmlspecialchars($err).'</p>'; ?>
  <form method="post">

    <div class="name-row">
      <label>First Name<input name="first_name" required></label>
      <label>Last Name<input name="last_name" required></label>
    </div>

    <label>Email<input type="email" name="email" required></label>
    <label>Phone<input name="phone" pattern="\d{11}" title="Must be exactly 11 digits" required></label>
    <label>Password<input type="password" name="password" minlength="6" required></label>

    <button type="button" class="toggle-address" onclick="toggleAddress()">📍 Add Address ▼</button>
    <div id="addressSection">
      <label>House Number<input name="house_number"></label>
      <label>Province<input name="province" required></label>
      <label>Municipality<input name="municipality" required></label>
      <label>Barangay<input name="barangay" required></label>
      <label>Street<input name="street" required></label>
      <label>Nearest Landmark<input name="nearest_landmark" required></label>
    </div>

    <button type="submit" class="button">Sign up</button>
  </form>
</main>

<script>
function toggleAddress() {
  const section = document.getElementById('addressSection');
  const btn = document.querySelector('.toggle-address');
  const visible = section.style.display === 'block';
  section.style.display = visible ? 'none' : 'block';
  btn.innerHTML = visible ? 'Add Address ▼' : 'Hide Address ▲';
}
</script>
</body>
</html>
