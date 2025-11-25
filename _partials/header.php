<?php
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/push.php');

$user = $_SESSION['user'] ?? null;
$userName = $user['first_name'] ?? '';
$userId = $user['id'] ?? null;
$oneSignalAppId = defined('ONESIGNAL_APP_ID') ? ONESIGNAL_APP_ID : null;
?>

<style>
  .site-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(90deg, #4b5320, #3e4425);
    padding: 12px 20px;
    color: #fff;
    font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
    box-shadow: 0 2px 8px rgba(0,0,0,0.25);
    position: sticky;
    top: 0;
    z-index: 999;
  }

  .site-header .brand {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .site-header .brand .logo {
    height: 45px;
    width: 45px;
    border-radius: 8px;
    object-fit: cover;
  }

  .site-header .brand h1 {
    margin: 0;
    font-size: 20px;
    letter-spacing: 0.5px;
  }

  .site-header .nav {
    display: flex;
    align-items: center;
    gap: 18px;
  }

  .site-header .nav a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    transition: 0.3s;
  }

  .site-header .nav a:hover {
    color: #f4e04d;
    transform: scale(1.05);
    text-shadow: 0 0 6px rgba(255,255,255,0.3);
  }

  .site-header .welcome-pill {
    margin-left: 10px;
    background: #fff;
    color: #4b5320;
    padding: 6px 12px;
    border-radius: 25px;
    font-weight: bold;
    font-size: 14px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  }

  .menu-toggle {
    display: none;
    background: transparent;
    color: #fff;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
  }

  @media (max-width: 768px) {
    .site-header {
      flex-direction: row;
      align-items: center;
    }

    .menu-toggle {
      display: block;
      margin-left: auto;
    }

    .site-header .nav {
      display: none;
      flex-direction: column;
      width: 100%;
      margin-top: 8px;
      gap: 10px;
    }

    .site-header.nav-open .nav {
      display: flex;
    }

    .site-header .welcome-pill {
      margin-left: 0;
    }
  }
</style>

<header class="site-header">
  <!-- BRAND -->
  <div class="brand">
    <img src="/kamulan-system/assets/images/kamulan-logo.jpg" class="logo" alt="Kamulan Logo">
    <h1>Kamulan Kitchenette</h1>
  </div>

  <!-- HAMBURGER TOGGLE (MOBILE) -->
  <button class="menu-toggle" type="button" aria-label="Toggle navigation">
    ☰
  </button>

  <!-- NAVIGATION -->
  <nav class="nav">
    <a href="/kamulan-system/buyer/index.php">Home</a>
    <a href="/kamulan-system/buyer/menu.php">Menu</a>

    <?php if ($user): ?>
      <a href="/kamulan-system/buyer/cart.php">Cart</a>
      <a href="/kamulan-system/buyer/orders.php">My Orders</a>
      <a href="/kamulan-system/buyer/profile.php">Profile</a>
      <a href="/kamulan-system/auth/logout.php" class="nav-link logout" onclick="confirmLogout(event)">Logout</a>
      <span class="welcome-pill">
        👋 Welcome, <?= htmlspecialchars($userName) ?>
      </span>
    <?php else: ?>
      <a href="/kamulan-system/auth/login.php">Login</a>
    <?php endif; ?>
  </nav>
</header>

<?php if (!empty($oneSignalAppId) && $oneSignalAppId !== 'YOUR_ONESIGNAL_APP_ID'): ?>
<script>
  (function bootstrapOneSignalPromise() {
    if (window.kamulanOneSignalReady) {
      return;
    }
    window.kamulanOneSignalReady = new Promise(function(resolve, reject) {
      window.__oneSignalReadyHandlers = { resolve, reject };
    });
  })();
</script>
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<?php endif; ?>

<script>
function confirmLogout(event) {
  event.preventDefault();
  if (confirm("Are you sure you want to logout?")) {
    window.location.href = "/kamulan-system/auth/logout.php";
  }
}

// Mobile hamburger toggle
document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.querySelector('.menu-toggle');
  var header = document.querySelector('.site-header');
  if (!toggle || !header) return;

  toggle.addEventListener('click', function () {
    header.classList.toggle('nav-open');
  });
});

<?php if (!empty($oneSignalAppId) && $oneSignalAppId !== 'YOUR_ONESIGNAL_APP_ID'): ?>
window.OneSignalDeferred = window.OneSignalDeferred || [];
OneSignalDeferred.push(async function(OneSignal) {
  try {
    await OneSignal.init({
      appId: "<?= htmlspecialchars($oneSignalAppId, ENT_QUOTES) ?>",
      serviceWorkerPath: "/kamulan-system/OneSignalSDKWorker.js",
      serviceWorkerParam: { scope: "/kamulan-system/" },
      serviceWorkerUpdaterPath: "/kamulan-system/OneSignalSDKWorker.js"
    });
    <?php if (!empty($userId)): ?>
    await OneSignal.login("<?= (int)$userId ?>");
    <?php endif; ?>
    if (window.__oneSignalReadyHandlers?.resolve) {
      window.__oneSignalReadyHandlers.resolve(OneSignal);
    }
  } catch (err) {
    console.error('Failed to initialize OneSignal', err);
    if (window.__oneSignalReadyHandlers?.reject) {
      window.__oneSignalReadyHandlers.reject(err);
    }
  }
});
<?php endif; ?>
</script>
