<?php
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');
require_role(['rider']);

$userSession = $_SESSION['user'] ?? null;
$userId = $userSession['id'] ?? null;

if (!$userId) {
    header('Location: /kamulan-system/auth/login.php');
    exit;
}

$success = '';
$error = '';

// Fetch latest user + branch info
$stmt = $pdo->prepare('SELECT u.*, b.name AS branch_name FROM users u LEFT JOIN branches b ON u.branch_id = b.id WHERE u.id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $error = 'User not found.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';

    if ($action === 'update_profile') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name  = trim($_POST['last_name'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');

        if ($first_name === '' || $last_name === '' || $email === '' || $phone === '') {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $update = $pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?');
            $update->execute([$first_name, $last_name, $email, $phone, $userId]);

            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $userSession = $user;
                unset($userSession['password']);
                $_SESSION['user'] = $userSession;
            }

            $success = 'Profile updated successfully.';
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new === '' || $confirm === '' || $current === '') {
            $error = 'Please fill in all password fields.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            $pwStmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
            $pwStmt->execute([$userId]);
            $row = $pwStmt->fetch(PDO::FETCH_ASSOC);
            $stored = $row['password'] ?? '';

            $valid = false;
            if ($stored && password_verify($current, $stored)) {
                $valid = true;
            } elseif ($current === $stored) { // legacy plain-text
                $valid = true;
            }

            if (!$valid) {
                $error = 'Current password is incorrect.';
            } else {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $updPw = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                $updPw->execute([$newHash, $userId]);
                $success = 'Password updated successfully.';
            }
        }
    } elseif ($action === 'delete_account') {
        $confirmText = trim($_POST['confirm_delete'] ?? '');
        if ($confirmText !== 'DELETE') {
            $error = 'Please type DELETE in all caps to confirm account deletion.';
        } else {
            $del = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $del->execute([$userId]);
            session_destroy();
            header('Location: /kamulan-system/index.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Rider Profile</title>
  <link rel="stylesheet" href="/kamulan-system/assets/css/style.css">
  <style>
    body { background: var(--light-bg, #f5f2ed); }
    .profile-page {
      max-width: 900px;
      margin: 2rem auto;
      padding: 2rem;
      background: #fff;
      border-radius: var(--radius, 12px);
      box-shadow: var(--shadow, 0 4px 12px rgba(0,0,0,0.1));
    }
    .profile-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    .profile-title { font-size: 1.6rem; color: var(--primary, #556b2f); }
    .profile-sub { font-size: 0.9rem; color: #555; }
    .profile-grid {
      display: grid;
      grid-template-columns: minmax(0, 2fr) minmax(0, 1.5fr);
      gap: 1.5rem;
    }
    .profile-card {
      background: #f8faf4;
      border-radius: 12px;
      padding: 1.2rem 1.4rem;
      border: 1px solid #dde3cf;
    }
    .profile-card h3 {
      margin-top: 0;
      margin-bottom: 0.8rem;
      color: var(--primary, #556b2f);
      font-size: 1.1rem;
    }
    .field-group { margin-bottom: 0.8rem; }
    .field-group label {
      display: block;
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 0.2rem;
      color: #334021;
    }
    .field-inline {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.8rem;
    }
    .profile-actions {
      display: flex;
      justify-content: flex-end;
      gap: 0.75rem;
      margin-top: 1rem;
    }
    .btn-secondary {
      background: transparent;
      color: var(--primary, #556b2f);
      border: 1px solid var(--primary, #556b2f);
      border-radius: var(--radius, 12px);
      padding: 8px 14px;
      cursor: pointer;
      font-weight: 500;
      transition: var(--transition, all 0.25s ease);
    }
    .btn-secondary:hover { background: rgba(85,107,47,0.06); }
    .btn-danger {
      background: #b03232;
      border-radius: var(--radius, 12px);
      padding: 8px 14px;
      color: #fff;
      border: none;
      cursor: pointer;
      font-weight: 600;
      transition: var(--transition, all 0.25s ease);
    }
    .btn-danger:hover { background: #8e2525; }
    .meta-row { font-size: 0.9rem; color: #555; margin-bottom: 0.25rem; }
    .meta-label { font-weight: 600; color: #3b4225; }
    .alert {
      padding: 0.75rem 1rem;
      border-radius: 10px;
      margin-bottom: 1rem;
      font-size: 0.9rem;
    }
    .alert-success {
      background: #e8f5e9;
      color: #256029;
      border: 1px solid #c8e6c9;
    }
    .alert-error {
      background: #fdecea;
      color: #a83b3b;
      border: 1px solid #f5c6cb;
    }
    @media (max-width: 768px) {
      .profile-grid { grid-template-columns: minmax(0, 1fr); }
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../_partials/header_staff.php'; ?>
<main class="profile-page">
  <div class="profile-header">
    <div>
      <h2 class="profile-title">Rider Profile</h2>
      <p class="profile-sub">View and update your account information.</p>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="profile-grid">
    <section class="profile-card">
      <h3>Account Information</h3>
      <p class="meta-row"><span class="meta-label">Role:</span> <?= htmlspecialchars(ucfirst($user['role'] ?? 'rider')) ?></p>
      <p class="meta-row"><span class="meta-label">Branch:</span> <?= htmlspecialchars($user['branch_name'] ?? 'N/A') ?></p>
      <p class="meta-row"><span class="meta-label">Availability:</span> <?= !empty($user['is_available']) ? 'Available' : 'On Delivery' ?></p>

      <form method="post">
        <input type="hidden" name="action" value="update_profile">

        <div class="field-inline">
          <div class="field-group">
            <label for="first_name">First name</label>
            <input id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
          </div>
          <div class="field-group">
            <label for="last_name">Last name</label>
            <input id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
          </div>
        </div>

        <div class="field-group">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
        </div>

        <div class="field-group">
          <label for="phone">Phone</label>
          <input id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
        </div>

        <div class="profile-actions">
          <button type="submit" class="button">Save changes</button>
        </div>
      </form>
    </section>

    <section class="profile-card">
      <h3>Security</h3>
      <form method="post" style="margin-bottom:1.2rem;">
        <input type="hidden" name="action" value="change_password">

        <div class="field-group">
          <label for="current_password">Current password</label>
          <input id="current_password" type="password" name="current_password" required>
        </div>
        <div class="field-group">
          <label for="new_password">New password</label>
          <input id="new_password" type="password" name="new_password" minlength="6" required>
        </div>
        <div class="field-group">
          <label for="confirm_password">Confirm new password</label>
          <input id="confirm_password" type="password" name="confirm_password" minlength="6" required>
        </div>

        <div class="profile-actions">
          <button type="submit" class="btn-secondary">Update password</button>
        </div>
      </form>

      <h3 style="margin-top:1rem; color:#a83b3b;">Danger zone</h3>
      <form method="post" onsubmit="return confirm('This will permanently delete your rider account. Continue?');">
        <input type="hidden" name="action" value="delete_account">
        <div class="field-group">
          <label for="confirm_delete">Type <strong>DELETE</strong> to confirm</label>
          <input id="confirm_delete" name="confirm_delete" placeholder="DELETE">
        </div>
        <div class="profile-actions">
          <button type="submit" class="btn-danger">Delete my account</button>
        </div>
      </form>
    </section>
  </div>
</main>
<?php include __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
