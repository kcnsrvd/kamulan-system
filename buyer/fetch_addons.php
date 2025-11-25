<?php
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');

header('Content-Type: text/html; charset=utf-8');

// menu_id is available if later you want to filter add-ons per item
$menu_id = isset($_GET['menu_id']) ? (int)$_GET['menu_id'] : 0;

// For now: all active add-ons from menu_items
$stmt = $pdo->prepare("SELECT id, name, price FROM menu_items WHERE category = 'Add-ons' AND available = 1 ORDER BY name ASC");
$stmt->execute();
$addons = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$addons) {
    // No add-ons available; frontend will treat as none
    echo '';
    exit;
}
?>
<div class="addons-list">
  <?php foreach ($addons as $a): ?>
    <label style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
      <span><?= htmlspecialchars($a['name']) ?> (₱<?= number_format($a['price'], 2) ?>)</span>
      <input type="number"
             min="0"
             value="0"
             class="addon-qty"
             data-id="<?= (int)$a['id'] ?>"
             data-price="<?= htmlspecialchars($a['price']) ?>">
    </label>
  <?php endforeach; ?>
</div>
