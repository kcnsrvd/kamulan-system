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
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@gmail.com')) {
        $err = 'Please enter a valid Gmail address.';
    } elseif (!preg_match('/^\d{11}$/', $phone)) {
        $err = 'Phone number must be exactly 11 digits.';
    } elseif (strlen($password) < 6) {
        $err = 'Password must be at least 6 characters.';
    } else {
        $role = 'buyer';
        $branch_id = null;
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $otp = rand(100000, 999999);

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

        $_SESSION['otp_created_at'] = time();

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'kamulan886@gmail.com';
            $mail->Password = 'dgpk zwzj milc hdld';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('kamulan886@gmail.com', 'Kamulan');
            $mail->addAddress($email, "$first_name $last_name");

            $mail->isHTML(true);
$mail->Subject = 'Verify Your Email';

$expiryMinutes = 10; // OTP validity in minutes
$expiryTime = date('h:i A', strtotime("+$expiryMinutes minutes"));

$mail->Body = "
  <div style='font-family: Poppins, sans-serif; background:#f4f4f4; padding:20px;'>
    <div style='max-width:500px; margin:auto; background:white; padding:25px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1);'>
      <h2 style='color:#4b5d2a; text-align:center;'>Email Verification</h2>
      <p>Hi <strong>$first_name</strong>,</p>
      <p>Thank you for registering with <b>Kamulan</b>. Please use the verification code below to complete your account setup.</p>

      <div style='text-align:center; margin:25px 0;'>
        <div style='display:inline-block; background:#e8f0dc; padding:15px 25px; border-radius:10px; font-size:22px; letter-spacing:4px; color:#4b5d2a; font-weight:bold;'>
          $otp
        </div>
      </div>

      <p>This code will expire in <b>$expiryMinutes minutes</b>.</p>
      <p style='font-size:14px; color:#555;'>If you did not request this verification, please disregard this message.</p>
      <br>
      <p style='font-size:14px; color:#666;'>Warm regards,<br><b>Kamulan Team</b></p>
    </div>
  </div>
";

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
  align-items: center;
  background: url('/kamulan-system/assets/images/kamulan-backg.jpg') no-repeat center center/cover;
}
body::before {
  content: "";
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(6px);
  z-index: -1;
}
main.form-card {
  background: rgba(255,255,255,0.97);
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.15);
  max-width: 500px;
  width: 90%;
  position: relative;
}
h2 { text-align:center; color:#4b5d2a; margin-bottom:15px; }
.form-card label { display:block; margin-bottom:6px; font-weight:500; color:#333; }
.form-card input { width:100%; padding:8px; border-radius:8px; border:1px solid #ccc; margin-bottom:6px; }
small.error-msg { color:red; font-size:13px; display:none; }
.name-row { display:flex; gap:10px; }
.toggle-btn, .button { width:100%; border:none; padding:10px; border-radius:8px; cursor:pointer; font-weight:500; }
.toggle-btn { background:#6b4f2c; color:white; }
.toggle-btn:hover { background:#83623a; }
.button { background:#556B2F; color:white; margin-top:10px; }
.button:hover { background:#6b7b44; }
#addressSection { display:none; }
.back-btn {
  background:none;
  border:none;
  font-size:18px;
  color:#4b5d2a;
  position:absolute;
  top:10px;
  left:10px;
  cursor:pointer;
}
.password-wrapper {
  position: relative;
}
.password-wrapper input {
  width: 100%;
  padding-right: 35px;
}
.password-toggle {
  position: absolute;
  right: 10px;
  top: 8px;
  cursor: pointer;
}
.already {
  text-align:center;
  margin-top:10px;
}
.error { text-align:center; color:red; margin-bottom:10px; font-weight:500; }
</style>
</head>
<body>

<main class="form-card">
  <h2>Register</h2>
  <?php if(!empty($err)) echo '<p class="error">'.htmlspecialchars($err).'</p>'; ?>

  <form method="post" id="regForm" onsubmit="return validateAll()">
    <!-- Personal Info Section -->
    <div id="personalSection">
      <div class="name-row">
        <label>First Name<input name="first_name" required></label>
        <label>Last Name<input name="last_name" required></label>
      </div>

      <label>Email<input type="email" name="email" required><small class="error-msg">Please enter a valid Gmail address.</small></label>
      <label>Phone<input name="phone" pattern="\d{11}" maxlength="11" required><small class="error-msg">Must be exactly 11 digits.</small></label>
      <label>Password</label>
      <div class="password-wrapper">
        <input type="password" name="password" minlength="6" required>
        <span class="password-toggle" onclick="togglePassword(this)">👁</span>
      </div>
      <small class="error-msg">Password must be at least 6 characters.</small>

      <button type="button" class="toggle-btn" onclick="nextPage()">Next ➜</button>
    </div>

    <!-- Address Section -->
    <div id="addressSection">
      <button type="button" class="back-btn" onclick="backPage()">←</button>

      <div style="display:flex; gap:10px;">
        <label style="flex:1;">House No.<input name="house_number" required></label>
        <label style="flex:1;">Street<input name="street" required></label>
      </div>
      <label>Barangay<input name="barangay" required></label>
      <label>Municipality/City<input name="municipality" required></label>
      <label>Province<input name="province" required></label>
      <label>Nearest Landmark<input name="nearest_landmark" required></label>

      <button type="submit" class="button">Sign up</button>
    </div>

    <p class="already">Already have an account? <a href="/kamulan-system/auth/login.php">Login</a></p>
  </form>
</main>

<script>
const personal = document.getElementById('personalSection');
const address = document.getElementById('addressSection');

function nextPage() {
  if (validatePersonal()) {
    personal.style.display = 'none';
    address.style.display = 'block';
  }
}

function backPage() {
  address.style.display = 'none';
  personal.style.display = 'block';
}

function togglePassword(icon) {
  const input = icon.previousElementSibling;
  input.type = input.type === 'password' ? 'text' : 'password';
}

function validatePersonal() {
  let valid = true;
  document.querySelectorAll('#personalSection input').forEach(input => {
    const errorMsg = input.parentElement.querySelector('.error-msg');
    if (!input.value.trim()) {
      if (errorMsg) errorMsg.style.display = 'block';
      valid = false;
    } else {
      if (errorMsg) errorMsg.style.display = 'none';
    }
  });

  const email = document.querySelector('input[name="email"]').value.trim();
  const phone = document.querySelector('input[name="phone"]').value.trim();
  const pass = document.querySelector('input[name="password"]').value.trim();

  if (!email.endsWith('@gmail.com')) {
    document.querySelector('input[name="email"]').nextElementSibling.style.display = 'block';
    valid = false;
  }
  if (!/^\d{11}$/.test(phone)) {
    document.querySelector('input[name="phone"]').nextElementSibling.style.display = 'block';
    valid = false;
  }
  if (pass.length < 6) {
    document.querySelector('input[name="password"]').parentElement.nextElementSibling.style.display = 'block';
    valid = false;
  }
  return valid;
}

function validateAll() {
  return validatePersonal(); // also checks required on address fields via HTML5
}
</script>

</body>
</html>
