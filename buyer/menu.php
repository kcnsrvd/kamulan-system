<?php 
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');

// Fetch categories
$categories = $pdo->query("SELECT DISTINCT category FROM menu_items ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
$category = $_GET['category'] ?? 'All';

if ($category !== 'All') {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE category = ?");
    $stmt->execute([$category]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $items = $pdo->query("SELECT * FROM menu_items")->fetchAll(PDO::FETCH_ASSOC);
}

// Cart preview
$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $c) {
    $total += $c['price'] * $c['qty'];
}

// Group menu items by base name (before " - ")
$grouped_items = [];
foreach ($items as $it) {
    $base_name = explode(' - ', $it['name'])[0];
    $grouped_items[$base_name][] = $it;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Kamulan - Menu</title>
  <link rel="stylesheet" href="/kamulan-system/assets/css/styles.css">
  <style>
  :root {
    --army-green: #4B5320;
    --olive: #6B8E23;
    --sage: #8F9779;
    --light-bg: #f8f8f5;
    --text-dark: #333;
    --text-muted: #666;
  }

  body {
    background: var(--light-bg);
    font-family: "Poppins", sans-serif;
    margin: 0;
    color: var(--text-dark);
  }

  main.container {
    max-width: 1100px;
    margin: 40px auto;
    background: #fff;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }

  h2 {
    color: var(--army-green);
    margin-bottom: 20px;
    font-weight: 600;
  }

  .category-buttons {
    margin-bottom: 25px;
    text-align: center;
    flex-wrap: wrap;
  }

  .category-buttons a {
    text-decoration: none;
    padding: 8px 18px;
    margin: 5px;
    border-radius: 20px;
    background: var(--olive);
    color: white;
    font-size: 14px;
    transition: all 0.2s ease;
    display: inline-block;
  }

  .category-buttons a:hover,
  .category-buttons a.active {
    background: var(--army-green);
  }

  .search-box {
    text-align: center;
    margin-bottom: 25px;
  }

  .search-box input {
    padding: 10px 14px;
    width: 60%;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
  }

  .card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 24px;
  }

  .card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    padding: 15px;
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 14px rgba(0,0,0,0.15);
  }

  .card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 10px;
  }

  .card h4 {
    margin: 10px 0 5px;
    color: var(--army-green);
    font-size: 1rem;
  }

  .card p {
    color: var(--text-muted);
    font-size: 14px;
    line-height: 1.4;
    margin-bottom: 10px;
    height: 40px;
    overflow: hidden;
  }

  .card-footer {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 10px;
  }

  .price-qty-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 8px;
  }

  .price {
    background: var(--army-green);
    color: #fff;
    padding: 8px 14px;
    border-radius: 6px;
    font-weight: bold;
    font-size: 14px;
  }

  .qty-input {
    width: 60px;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 8px;
    text-align: center;
    font-size: 14px;
  }

  .button {
    background: var(--olive);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    transition: 0.2s;
    width: 80%;
    max-width: 140px;
  }

  .button:hover {
    background: var(--army-green);
  }

  .not-available {
    background: #ccc;
    color: #555;
    padding: 8px 14px;
    border-radius: 6px;
    display: inline-block;
    font-weight: 600;
    margin-bottom: 6px;
  }

  .floating-cart {
    position: fixed;
    right: 25px;
    bottom: 25px;
    background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    border-radius: 14px;
    padding: 16px;
    width: 260px;
    z-index: 1000;
    transition: all 0.3s ease;
    border: 2px solid var(--olive);
  }

  .floating-cart h4 {
    margin-top: 0;
    color: var(--army-green);
    text-align: center;
    font-weight: 600;
  }

  .floating-cart table {
    width: 100%;
    font-size: 13px;
  }

  .floating-cart td {
    padding: 4px;
  }

  .floating-cart .total {
    font-weight: bold;
    color: var(--text-dark);
    border-top: 1px solid #ddd;
    padding-top: 8px;
    text-align: right;
  }

  .floating-cart .button {
    width: 100%;
    margin-top: 10px;
  }

  @media (max-width: 768px) {
    .floating-cart {
      right: 15px;
      bottom: 15px;
      width: 90%;
    }
  }
  </style>
</head>

<body>
<?php include(__DIR__ . '/../_partials/header.php'); ?>

<main class="container">
  <h2>Our Menu</h2>

  <div class="category-buttons">
    <a href="?category=All" class="<?= $category==='All'?'active':'' ?>">All</a>
    <?php foreach ($categories as $cat): ?>
      <a href="?category=<?= urlencode($cat) ?>" class="<?= $category===$cat?'active':'' ?>">
        <?= htmlspecialchars($cat) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="search-box">
    <input type="text" id="searchInput" placeholder="Search for dishes...">
  </div>

  <div class="card-grid" id="menuGrid">
    <?php foreach($grouped_items as $base_name => $variants): 
      $first = $variants[0];
    ?>
      <div class="card" data-name="<?= strtolower($base_name) ?>">
        <img src="/kamulan-system/assets/images/foods/<?= htmlspecialchars($first['image']) ?>" alt="">
        <h4><?= htmlspecialchars($base_name) ?></h4>
        <p><?= htmlspecialchars($first['description']) ?></p>

        <?php if (count($variants) > 1): ?>
          <label style="font-size:13px;">Flavor:</label>
          <select name="flavor" class="flavor-select" style="margin-bottom:8px;padding:5px;border-radius:6px;">
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
          <?php
            // Determine selected variant
            $selected_variant = $variants[0];
            $available = $selected_variant['available'];
          ?>

          <div class="not-available" style="display: <?= $available ? 'none' : 'block' ?>;">
            Not Available
          </div>

          <form class="addToCartForm" style="display: <?= $available ? 'flex' : 'none' ?>; flex-direction: column; align-items: center;">
            <input type="hidden" name="id" class="menu-id" value="<?= $selected_variant['id'] ?>">

            <div class="price-qty-row">
              <div class="price">₱<?= number_format($selected_variant['price'], 2) ?></div>
              <input type="number" name="qty" min="1" placeholder="0" class="qty-input">
            </div>

            <button class="button" type="submit">Add</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<div class="floating-cart" id="miniCart">
  <h4>Your Cart</h4>
  <div id="miniCartContent">
    <?php if (empty($cart)): ?>
      <p style="text-align:center;color:#666;">Cart is empty</p>
    <?php else: ?>
      <table>
        <?php foreach($cart as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td>x<?= $c['qty'] ?></td>
            <td style="text-align:right;">₱<?= number_format($c['price'] * $c['qty'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
      <div class="total">Total: ₱<?= number_format($total,2) ?></div>
      <a href="/kamulan-system/buyer/cart.php">
        <button class="button">Go to Checkout</button>
      </a>
    <?php endif; ?>
  </div>
</div>

<?php include(__DIR__ . '/../_partials/footer.php'); ?>

<script>
  // Live search
  document.getElementById('searchInput').addEventListener('keyup', function() {
    let query = this.value.toLowerCase();
    document.querySelectorAll('#menuGrid .card').forEach(card => {
      let name = card.getAttribute('data-name');
      card.style.display = name.includes(query) ? 'block' : 'none';
    });
  });

  // Update hidden id, price, availability when flavor changes
  document.querySelectorAll('.flavor-select').forEach(select => {
    select.addEventListener('change', function() {
      const card = this.closest('.card');
      const idInput = card.querySelector('.menu-id');
      const priceDiv = card.querySelector('.price');
      const notAvailableDiv = card.querySelector('.not-available');
      const addForm = card.querySelector('.addToCartForm');

      const selected = this.options[this.selectedIndex];
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

  // AJAX Add to Cart
  document.querySelectorAll('.addToCartForm').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch('/kamulan-system/buyer/cart_add.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.text())
      .then(html => {
        document.getElementById('miniCartContent').innerHTML = html;
      });
    });
  });
</script>
</body>
</html>
