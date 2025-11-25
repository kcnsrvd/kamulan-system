<?php
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../config/session.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) $err = 'Email and password required';
    else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) $err = 'User not found';
        else {
            $valid = false;

            if (password_verify($password, $user['password'])) $valid = true;
            elseif ($password === $user['password']) $valid = true;

            if ($valid) {
                unset($user['password']);
                $_SESSION['user'] = $user;
                switch ($user['role']) {
                    case 'rider': header('Location: /kamulan-system/rider/dashboard.php'); exit;
                    case 'staff': header('Location: /kamulan-system/staff/dashboard.php'); exit;
                    case 'manager': header('Location: /kamulan-system/admin/dashboard.php'); exit;
                    default: header('Location: /kamulan-system/buyer/index.php'); exit;
                }
            } else $err = 'Invalid password';
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login | Kamulan Kitchenette</title>
    <link rel="stylesheet" href="/kamulan-system/assets/css/style.css">
    <link rel="stylesheet" href="/kamulan-system/assets/css/login.css">
    <style>
        /* Body & Background */
        body {
            font-family: "Poppins", sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(rgba(255,255,255,0.1), rgba(255,255,255,0.1)),
                        url('/kamulan-system/assets/images/kamulan-backg.jpg') no-repeat center center/cover;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            backdrop-filter: blur(6px);
            z-index: -1;
        }

        /* Login Card */
        .form-card {
            max-width: 400px;
            width: 90%;
            background: rgba(255, 255, 255, 0.95);
            padding: 35px 30px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            text-align: center;
            position: relative;
            z-index: 1;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .form-card h2 {
            color: #4b5d2a;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .form-card label {
            display: block;
            margin-bottom: 8px;
            text-align: left;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-card input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            margin-top: 4px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .form-card input:focus {
            border-color: #556B2F;
            outline: none;
        }

        .form-card .button {
            background-color: #556B2F;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            margin-top: 15px;
            font-size: 1rem;
            transition: 0.3s;
        }

        .form-card .button:hover {
            background-color: #6b7b44;
        }

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            width: 100%;
            padding-right: 45px;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            color: #556B2F;
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0;
        }

        .toggle-password img {
            width: 22px;
            height: 22px;
            pointer-events: none;
            filter: grayscale(1);
        }

        .form-card p {
            margin-top: 12px;
            font-size: 0.9rem;
        }

        .form-card a {
            color: #556B2F;
            text-decoration: none;
            font-weight: 500;
        }

        .form-card a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        /* Back Arrow */
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 1.5rem;
            color: #556B2F;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .form-card {
                padding: 25px;
                width: 95%;
            }

            .form-card h2 {
                font-size: 1.5rem;
            }

            .back-link {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    
    <main class="form-card">
        <a class="back-link" href="/kamulan-system/index.php" aria-label="Back to Home">&#8592;</a>
        <h2>Login</h2>
        <?php if(!empty($err)) echo '<p class="error">'.htmlspecialchars($err).'</p>'; ?>
        <form method="post">
            <label>Email
                <input type="email" name="email" required>
            </label>
            <label>Password
                <span class="password-wrapper">
                    <input id="password" type="password" name="password" required>
                    <button type="button" class="toggle-password" aria-label="Show password" data-target="password">
                        <img src="https://cdn-icons-png.flaticon.com/512/2767/2767146.png" alt="Toggle password visibility">
                    </button>
                </span>
            </label>
            <button class="button" type="submit">Log in</button>
        </form>
        <p><a href="/kamulan-system/auth/register.php">Don't have an account? Register</a></p>
    </main>
<script>
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.dataset.target);
      if (!input) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });
</script>
</body>
</html>
