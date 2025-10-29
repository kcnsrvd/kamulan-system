<?php
session_start();
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_otp = trim($_POST['otp'] ?? '');
    if ($input_otp == ($_SESSION['reg_otp'] ?? '')) {
        // OTP correct, insert user into DB
        require_once('../config/db.php');
        $data = $_SESSION['reg_data'];
        $stmt = $pdo->prepare('INSERT INTO users 
            (first_name,last_name,email,phone,password,role,branch_id,street,barangay,municipality,province,house_number,nearest_landmark)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
            $data['first_name'],$data['last_name'],$data['email'],$data['phone'],$data['password'],
            $data['role'],$data['branch_id'],$data['street'],$data['barangay'],$data['municipality'],$data['province'],
            $data['house_number'],$data['nearest_landmark']
        ]);

        unset($_SESSION['reg_otp'], $_SESSION['reg_data']);
        header('Location: login.php?verified=1');
        exit;
    } else {
        $err = 'Invalid OTP, please check your email.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify Email</title>
<link rel="stylesheet" href="/kamulan-system/assets/css/style.css">
<style>
/* Basic styling */
form { max-width: 400px; margin:50px auto; background:#fff; padding:20px; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.1);}
input { width:100%; padding:8px; margin-bottom:10px; border-radius:6px; border:1px solid #ccc;}
button { width:100%; padding:10px; border:none; border-radius:6px; background:#556B2F; color:#fff;}
button:hover { background:#6b7b44;}
.error { color:red; text-align:center; margin-bottom:10px;}
</style>
</head>
<body>
<form method="post">
    <h2>Verify Your Email</h2>
    <label>Enter OTP sent to your email:</label>
    <input type="text" name="otp" required>
    <button type="submit">Verify</button>
    <?php if(!empty($err)) echo '<p class="error">'.htmlspecialchars($err).'</p>'; ?>
</form>
</body>
</html>
