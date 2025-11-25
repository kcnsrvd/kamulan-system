<?php
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');

// Fetch categories (exclude Add-ons for buyers)
$categories = $pdo->query("SELECT DISTINCT category FROM menu_items ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
$categories = array_values(array_filter($categories, function($c) {
    return $c !== 'Add-ons';
}));

$category = $_GET['category'] ?? 'All';

// Fetch menu items (never include add-ons here)
if ($category !== 'All') {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE category = ? AND category != 'Add-ons'");
    $stmt->execute([$category]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $items = $pdo->query("SELECT * FROM menu_items WHERE category != 'Add-ons'")->fetchAll(PDO::FETCH_ASSOC);
}

// Cart preview (simple items; add-ons are stored as separate line items in the cart)
$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $c) {
    $total += $c['price'] * $c['qty'];
}

// Group menu items by category then base name
$grouped_by_category = [];
foreach ($items as $it) {
    $cat_name = $it['category'];
    $base_name = explode(' - ', $it['name'])[0];
    $grouped_by_category[$cat_name][$base_name][] = $it;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Kamulan - Menu</title>
<link rel="stylesheet" href="/kamulan-system/assets/css/styles.css">
<style>
:root {
  --primary: #4B5320;
  --secondary: #6B8E23;
  --bg-light: #f8f8f5;
  --text-dark: #333;
  --text-muted: #666;
  --card-shadow: rgba(0,0,0,0.1);
}
body {
  background: var(--bg-light);
  font-family: "Poppins", sans-serif;
  margin:0;
  color: var(--text-dark);
}
.container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
h2 { color: var(--primary); text-align:center; font-weight:700; margin-bottom:20px; }

.category-buttons { display:flex; justify-content:center; flex-wrap:wrap; gap:10px; margin-bottom:25px; }
.category-buttons a { text-decoration:none; padding:8px 18px; border-radius:20px; background:var(--secondary); color:white; font-size:14px; transition:0.2s; }
.category-buttons a.active, .category-buttons a:hover { background: var(--primary); }

.search-box { text-align:center; margin-bottom:25px; }
.search-box input { padding:10px 14px; width:60%; border:1px solid #ccc; border-radius:8px; font-size:14px; transition:0.2s; }
.search-box input:focus { outline:none; border-color: var(--primary); }

.card-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(250px,1fr)); gap:24px; }
.card { background:#fff; border-radius:14px; box-shadow:0 4px 12px var(--card-shadow); overflow:hidden; display:flex; flex-direction:column; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.card:hover { transform: translateY(-5px); box-shadow:0 6px 20px var(--card-shadow); }
.card img { width:100%; height:160px; object-fit:cover; }
.card-body { padding:15px; flex:1; display:flex; flex-direction:column; justify-content:space-between; }
.card h4 { margin:0 0 8px; color: var(--primary); font-size:1.1rem; }
.card p { margin:0 0 10px; color: var(--text-muted); font-size:14px; height:40px; overflow:hidden; }
.card-footer { display:flex; flex-direction:column; align-items:center; gap:8px; }

.price-qty-row { display:flex; align-items:center; justify-content:center; gap:10px; }
.price { background: var(--primary); color:#fff; padding:6px 12px; border-radius:6px; font-weight:bold; font-size:14px; }
.qty-input { width:60px; padding:6px; border:1px solid #ccc; border-radius:6px; text-align:center; font-size:14px; }

.button { background:var(--secondary); color:white; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; font-size:14px; transition:0.2s; }
.button:hover { background: var(--primary); }

.not-available { background:#ccc; color:#555; padding:8px 14px; border-radius:6px; font-weight:600; }

.floating-cart {
  position: fixed; right:25px; bottom:25px; background:#fff;
  box-shadow:0 4px 16px rgba(0,0,0,0.2);
  border-radius:14px; padding:16px; width:280px; z-index:1000; border:2px solid var(--secondary);
}
.floating-cart h4 { text-align:center; color:var(--primary); margin:0 0 10px; }
.floating-cart table { width:100%; font-size:13px; }
.floating-cart td { padding:4px; }
.floating-cart .total { font-weight:bold; text-align:right; border-top:1px solid #ddd; padding-top:8px; }

.addons-section {
  position: fixed; left:25px; bottom:25px;
  background:#f3f3f0; padding:12px; border-radius:12px; width:280px; z-index:999;
  box-shadow:0 4px 16px rgba(0,0,0,0.15);
}
.addons-section h3 { margin:0; cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
.addons-section #addonsContent { display:none; margin-top:10px; max-height:250px; overflow-y:auto; }

@media (max-width: 1024px){
  .addons-section { left:10px; bottom:100px; width:90%; }
  .floating-cart { right:10px; width:90%; bottom:10px; }
  .search-box input { width:90%; }
}
</style>
</head>
<body>
<?php include(__DIR__ . '/../_partials/header.php'); ?>

<div class="container">
<h2>Our Menu</h2>

<div class="category-buttons">
<a href="?category=All" class="<?= $category==='All'?'active':'' ?>">All</a>
<?php foreach ($categories as $cat): ?>
  <a href="?category=<?= urlencode($cat) ?>" class="<?= $category===$cat?'active':'' ?>"><?= htmlspecialchars($cat) ?></a>
<?php endforeach; ?>
</div>

<div class="search-box">
<input type="text" id="searchInput" placeholder="Search for dishes...">
</div>

<?php foreach($grouped_by_category as $cat_name => $items_by_base): ?>
<h3 style="margin-bottom:15px; color:var(--primary)"><?= htmlspecialchars($cat_name) ?></h3>
<div class="card-grid">
<?php foreach($items_by_base as $base_name => $variants): 
  $first = $variants[0];
?>
<div class="card" data-name="<?= strtolower($base_name) ?>">
  <img src="/kamulan-system/assets/images/foods/<?= htmlspecialchars($first['image']) ?>" alt="">
  <div class="card-body">
    <h4><?= htmlspecialchars($base_name) ?></h4>
    <p><?= htmlspecialchars($first['description']) ?></p>

    <?php if (count($variants) > 1): ?>
    <select name="flavor" class="flavor-select" style="padding:6px; border-radius:6px; margin-bottom:10px;">
      <?php foreach ($variants as $v):
        $flavor_name = $v['flavor'] ?: (explode(' - ', $v['name'])[1] ?? 'Default');
      ?>
        <option value="<?= $v['id'] ?>" data-price="<?= $v['price'] ?>" data-available="<?= $v['available'] ?>">
          <?= htmlspecialchars($flavor_name) ?> — ₱<?= number_format($v['price'], 2) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <?php else: ?>
    <input type="hidden" class="flavor-select" value="<?= $first['id'] ?>" data-price="<?= $first['price'] ?>" data-available="<?= $first['available'] ?>">
    <?php endif; ?>

    <div class="card-footer">
      <div class="not-available" style="display: <?= $first['available'] ? 'none' : 'block' ?>;">Not Available</div>
      <form class="addToCartForm" style="display: <?= $first['available'] ? 'flex' : 'none' ?>; flex-direction: column; align-items: center;">
        <input type="hidden" name="id" class="menu-id" value="<?= $first['id'] ?>">
        <input type="hidden" name="addons_selected" class="addons-selected">
        <div class="price-qty-row">
          <div class="price">₱<?= number_format($first['price'], 2) ?></div>
          <input type="number" name="qty" min="1" value="1" class="qty-input">
        </div>
        <button class="button" type="submit">Add</button>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>


<!-- Floating cart -->
<div class="floating-cart" id="miniCart">
<h4>Your Cart</h4>
<div id="miniCartContent">
  <?php include(__DIR__ . '/cart_add.php'); ?>
</div>
</div>

<?php include(__DIR__ . '/../_partials/footer.php'); ?>

<!-- Add-ons panel (shown after selecting a menu item, bottom-left) -->
<div id="addonsModal" style="display:none; position:fixed; left:25px; bottom:25px; z-index:2000;">
  <div style="background:#f3f3f0; padding:12px; border-radius:12px; width:280px; max-width:90%; box-shadow:0 4px 16px rgba(0,0,0,0.15);">
    <h4 style="margin-top:0; color:#4b5320;">Add-ons</h4>
    <p id="addonsBaseName" style="font-size:14px; color:#555;"></p>
    <div id="addonsContainer" style="max-height:220px; overflow:auto; margin-bottom:10px;"></div>
    <div style="display:flex; justify-content:flex-end; gap:8px;">
      <button type="button" id="addonsSkipBtn" class="button" style="background:#ccc; color:#333; width:auto;">Skip</button>
      <button type="button" id="addonsConfirmBtn" class="button" style="width:auto;">Add to Cart</button>
    </div>
  </div>
</div>

<script>
// Live search
const searchInput = document.getElementById('searchInput');
if (searchInput) {
  searchInput.addEventListener('keyup', function () {
    let q = this.value.toLowerCase();
    document.querySelectorAll('.card').forEach(card => {
      let name = card.getAttribute('data-name') || '';
      card.style.display = name.includes(q) ? 'block' : 'none';
    });
  });
}

// Flavor select: update hidden id, price, availability
document.querySelectorAll('.flavor-select').forEach(sel => {
  sel.addEventListener('change', function () {
    const card = this.closest('.card');
    const idInput = card.querySelector('.menu-id');
    const priceDiv = card.querySelector('.price');
    const notAvailableDiv = card.querySelector('.not-available');
    const addForm = card.querySelector('.addToCartForm');

    const selected = this.options ? this.options[this.selectedIndex] : { dataset: { price: this.dataset.price, available: this.dataset.available }, value: this.value };
    const available = selected.dataset.available === "1";

    if (idInput) idInput.value = selected.value;
    if (priceDiv) priceDiv.textContent = '₱' + parseFloat(selected.dataset.price).toFixed(2);

    if (available) {
      if (notAvailableDiv) notAvailableDiv.style.display = 'none';
      if (addForm) addForm.style.display = 'flex';
    } else {
      if (notAvailableDiv) notAvailableDiv.style.display = 'block';
      if (addForm) addForm.style.display = 'none';
    }
  });
});

// Add-ons flow
let addonsCurrentBase = { id: null, qty: 0, name: '' };

document.querySelectorAll('.addToCartForm').forEach(form => {
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const qtyInput = this.querySelector('.qty-input');
    const qty = parseInt(qtyInput.value || '0', 10);
    if (!qty || qty <= 0) return;

    const idInput = this.querySelector('.menu-id');
    const baseId = parseInt(idInput.value, 10);
    const baseName = this.closest('.card').querySelector('h4').textContent.trim();

    addonsCurrentBase = { id: baseId, qty: qty, name: baseName };

    // Fetch add-ons dynamically only when a menu item is added
    fetch('/kamulan-system/buyer/fetch_addons.php?menu_id=' + encodeURIComponent(baseId))
      .then(res => res.text())
      .then(html => {
        // If no add-ons, behave like original simple add-to-cart
        if (!html.trim()) {
          const fd = new FormData();
          fd.append('id', baseId);
          fd.append('qty', qty);
          return fetch('/kamulan-system/buyer/cart_add.php', {
            method: 'POST',
            body: fd
          }).then(res => res.text())
            .then(html => {
              document.getElementById('miniCartContent').innerHTML = html;
            });
        }

        // Populate and show modal
        document.getElementById('addonsBaseName').textContent =
          'Selected item: ' + baseName + ' (Qty: ' + qty + ')';
        document.getElementById('addonsContainer').innerHTML = html;
        document.getElementById('addonsModal').style.display = 'flex';
      });
  });
});

const addonsModal = document.getElementById('addonsModal');
const addonsSkipBtn = document.getElementById('addonsSkipBtn');
const addonsConfirmBtn = document.getElementById('addonsConfirmBtn');

// Skip: add base item only
if (addonsSkipBtn) {
  addonsSkipBtn.addEventListener('click', () => {
    addonsModal.style.display = 'none';

    const fd = new FormData();
    fd.append('id', addonsCurrentBase.id);
    fd.append('qty', addonsCurrentBase.qty);

    fetch('/kamulan-system/buyer/cart_add.php', {
      method: 'POST',
      body: fd
    }).then(res => res.text())
      .then(html => {
        document.getElementById('miniCartContent').innerHTML = html;
      });
  });
}

// Confirm: add base + selected add-ons
if (addonsConfirmBtn) {
  addonsConfirmBtn.addEventListener('click', () => {
    const fd = new FormData();
    fd.append('base_id', addonsCurrentBase.id);
    fd.append('base_qty', addonsCurrentBase.qty);

    const addonInputs = addonsModal.querySelectorAll('.addon-qty');
    addonInputs.forEach(inp => {
      const q = parseInt(inp.value || '0', 10);
      if (q > 0) {
        fd.append('addon_ids[]', inp.dataset.id);
        fd.append('addon_qtys[]', q);
      }
    });

    fetch('/kamulan-system/buyer/cart_add.php', {
      method: 'POST',
      body: fd
    }).then(res => res.text())
      .then(html => {
        document.getElementById('miniCartContent').innerHTML = html;
        addonsModal.style.display = 'none';
      });
  });
}
</script>
</body>
</html>
