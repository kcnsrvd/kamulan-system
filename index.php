<?php
require_once(__DIR__ . '/config/db.php');

// Fetch 3 best sellers (based on total orders)
$bestSellers = $pdo->query("
  SELECT mi.*
  FROM menu_items mi
  JOIN order_items oi ON mi.id = oi.menu_item_id
  GROUP BY mi.id
  ORDER BY SUM(oi.qty) DESC
  LIMIT 3
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all categories dynamically
$categories = $pdo->query("SELECT DISTINCT category FROM menu_items ORDER BY category ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all menu items (for search functionality)
$allItems = $pdo->query("SELECT * FROM menu_items ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Kamulan Kitchenette</title>
  <link rel="stylesheet" href="/kamulan-system/assets/css/style.css">
  <link rel="stylesheet" href="/kamulan-system/assets/css/home.css">
  <link rel="icon" type="image/png" href="/kamulan-system/assets/images/kamulan-logo.jpg">
  
  <style>
    .hidden { display: none; }

    /* SEARCH RESULTS */
/* SEARCH RESULTS HORIZONTAL SCROLL */
.search-results {
  display: flex;
  gap: 16px;
  overflow-x: auto;
  padding-bottom: 10px;
  margin-top: 16px;
}

.search-results::-webkit-scrollbar {
  height: 8px;
}

.search-results::-webkit-scrollbar-thumb {
  background: #6b8e23;
  border-radius: 4px;
}

.search-results .menu-item {
  min-width: 180px; /* Width of each card */
  background: #fff;
  padding: 10px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  flex: 0 0 auto; /* prevent shrinking */
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

.search-results .menu-item img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: 8px;
}

.search-results .menu-item h4 {
  margin: 8px 0 4px;
  font-size: 1rem;
  color: #4b5320;
}

.search-results .menu-item .price {
  color: #6b8e23;
  font-weight: 600;
  margin-bottom: 6px;
}

.branch-card a[href*="facebook"] {
  color: #1877F2;
  font-weight: 500;
  text-decoration: none;
}
.branch-card a[href*="facebook"]:hover {
  text-decoration: underline;
}


  </style>
</head>
<body>

<!-- HEADER -->
<header class="main-header" style="
  background: linear-gradient(90deg, #4b5d2a, #2f3e1b);
  color: #f5f5f5;
  padding: 15px 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 6px rgba(0,0,0,0.3);
  position: sticky;
  top: 0;
  z-index: 999;
">
  <div class="logo" style="display:flex; align-items:center; gap:12px;">
    <img src="/kamulan-system/assets/images/kamulan-logo.jpg" alt="Kamulan Kitchenette Logo" style="height:50px; width:50px; border-radius:50%; object-fit:cover;">
    <h1 style="font-size:1.4rem; color:#fefae0; letter-spacing:1px; font-weight:600;">Kamulan Kitchenette</h1>
  </div>

  <nav class="nav-links" style="display:flex; gap:12px;">
    <a href="/kamulan-system/auth/register.php" class="btn" style="
      background:#7c8c56;
      color:#fff;
      padding:8px 16px;
      border-radius:6px;
      text-decoration:none;
      transition:all 0.3s ease;
      font-weight:500;
    " onmouseover="this.style.background='#a2b67e';" onmouseout="this.style.background='#7c8c56';">Register</a>

    <a href="/kamulan-system/auth/login.php" class="btn" style="
      background:#6b7b44;
      color:#fff;
      padding:8px 16px;
      border-radius:6px;
      text-decoration:none;
      transition:all 0.3s ease;
      font-weight:500;
    " onmouseover="this.style.background='#8da164';" onmouseout="this.style.background='#6b7b44';">Login</a>

    <a href="/kamulan-system/auth/register.php" class="btn order-now" style="
      background:#a2b67e;
      color:#2f3e1b;
      padding:8px 18px;
      border-radius:6px;
      text-decoration:none;
      font-weight:600;
      transition:all 0.3s ease;
    " onmouseover="this.style.background='#cde0a8'; this.style.color='#1e2812';" onmouseout="this.style.background='#a2b67e'; this.style.color='#2f3e1b';">Order Now</a>
  </nav>
</header>

<!-- MAIN CONTENT -->
<main class="landing">

  <!-- TOP CONTROLS -->
  <section class="top-controls" style="display:flex; justify-content:space-between; align-items:center; gap:20px; flex-wrap:wrap; margin:20px;">
    <div class="control-buttons" style="display:flex; gap:10px;">
      <button id="menuBtn" class="tab-btn active">Menu</button>
      <button id="storesBtn" class="tab-btn">Branches</button>
    </div>

    <div class="search-bar" style="display:flex; align-items:center; gap:8px;">
      <div class="search-results hidden" style="margin-top:16px; display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px;"></div>

      <input type="text" placeholder="Search menu..." style="padding:8px; border-radius:6px; border:1px solid #ccc;">
      <button style="padding:8px 12px; background:#6b7b44; color:white; border:none; border-radius:6px; cursor:pointer;">🔍</button>
    </div>
  </section>

  <!-- MENU SECTION -->
  <section id="menuSection" class="content-section">
    <!-- PHOTO CAROUSEL -->
    <div class="photo-carousel">
      <h2>Discover Our Dishes</h2>
      <div class="carousel-container">
        <div class="carousel-track">
          <?php
$carousel = $pdo->query("SELECT * FROM home_carousel_images")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php foreach ($carousel as $c): ?>
    <img src="/kamulan-system/assets/images/carousel/<?= htmlspecialchars($c['image']) ?>">
<?php endforeach; ?>

        </div>
      </div>
    </div>

    <!-- FEATURED -->
    <div class="featured-scroll">
      <h2>Featured Menu</h2>
      <div class="scroll-row">
        <?php foreach ($categories as $cat): ?>
          <div class="category-card" data-category="<?= htmlspecialchars($cat['category']) ?>">
              <?php
              $featImg = $pdo->prepare("SELECT image FROM featured_category_images WHERE category=? LIMIT 1");
              $featImg->execute([$cat['category']]);
              $img = $featImg->fetchColumn();
              ?>
              <img 
                src="/kamulan-system/assets/images/menu/solo.jpg" 
                alt="Solo Menu"
                onerror="this.onerror=null; this.src='/kamulan-system/assets/images/kamulan-cover.jpg';"
              />

            <h3><?= htmlspecialchars(ucfirst($cat['category'])) ?></h3>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- CATEGORY MENU DISPLAY -->
    <div id="category-items" class="category-items hidden">
      <div class="menu-grid">
        <?php if (!empty($items)): foreach ($items as $item): ?>
          <div class="menu-item">
            <h4><?= htmlspecialchars($item['name']) ?></h4>
            <span class="price">₱<?= number_format($item['price'], 2) ?></span>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

<!-- BEST SELLERS -->  
<div class="bestsellers">
  <h2>Best Sellers</h2>
  <div class="meal-grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px;">
    <?php if ($bestSellers): ?>
      <?php foreach ($bestSellers as $meal): ?>
      <div class="meal-card" style="background:#fff; padding:10px; border-radius:12px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
        <img src="/kamulan-system/assets/images/foods/<?= htmlspecialchars($meal['image']) ?>" 
             alt="<?= htmlspecialchars($meal['name']) ?>" 
             style="width:100%; height:120px; object-fit:cover; border-radius:8px;">
        <h3 style="font-size:1rem; margin:8px 0 4px; color:#4b5320;"><?= htmlspecialchars($meal['name']) ?></h3>
        <p style="font-size:0.9rem; color:#6b8e23;">₱<?= number_format($meal['price'], 2) ?></p>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No best sellers yet.</p>
    <?php endif; ?>
  </div>
</div>



    <!-- ABOUT -->
    <section style="display:flex; justify-content:center; align-items:center; flex-direction:column; padding:40px 20px; text-align:center;">
      <div class="about" style="max-width:700px;">
        <h2 style="color:#4b5d2a; margin-bottom:20px;">About Kamulan</h2>
        <img src="/kamulan-system/assets/images/kamulan-logo.jpg" 
             alt="Kamulan Logo" 
             style="width:120px; height:auto; display:block; margin:0 auto 20px auto; border-radius:12px;">
        <p style="font-size:1.05rem; line-height:1.6; color:#333;">
          Kamulan Kitchenette brings the rich flavors of Filipino home-style cooking to every table. 
          With our three branches across Laguna, we proudly serve dishes made with love and authenticity. 
          Experience Kamulan — where every meal feels like home.
        </p>
      </div>
    </section>
  </section>

  <!-- BRANCHES SECTION -->
  <section id="storesSection" class="content-section hidden">
    <h2>Our Branches</h2>
    <div class="branch-grid">
  <div class="branch-card">
    <img src="/kamulan-system/assets/images/branches/rizal.jpg" alt="Rizal">
    <h3>Kamulan – Rizal, Laguna</h3>
    <p>
      📍 Rizal, Laguna<br>
      ☎ 0970-401-1952<br>
      <a href="https://www.facebook.com/kamulankitchenette" target="_blank" rel="noopener noreferrer">
        🔗 Visit on Facebook
      </a>
    </p>
    <a href="/kamulan-system/auth/register.php" class="btn">Order Now</a>
  </div>

  <div class="branch-card">
    <img src="/kamulan-system/assets/images/branches/zulueta.jpg" alt="Zulueta">
    <h3>Kamulan – San Pablo City (P. Zulueta)</h3>
    <p>
      📍 P. Zulueta St., San Pablo City<br>
      ☎ 0950-188-8568<br>
      <a href="https://www.facebook.com/profile.php?id=61576721933563" target="_blank" rel="noopener noreferrer">
        🔗 Visit on Facebook
      </a>
    </p>
    <a href="/kamulan-system/auth/register.php" class="btn">Order Now</a>
  </div>

  <div class="branch-card">
    <img src="/kamulan-system/assets/images/branches/mabini.jpg" alt="Mabini">
    <h3>Kamulan – San Pablo City (A. Mabini)</h3>
    <p>
      📍 A. Mabini St., San Pablo City<br>
      ☎ 0992-622-5396<br>
      <a href="https://www.facebook.com/profile.php?id=61578990466393" target="_blank" rel="noopener noreferrer">
        🔗 Visit on Facebook
      </a>
    </p>
    <a href="/kamulan-system/auth/register.php" class="btn">Order Now</a>
  </div>
</div>

  </section>

</main>

<?php include(__DIR__ . '/_partials/footer.php'); ?>

<!-- TOGGLE SCRIPT -->
<script>
const menuBtn = document.getElementById('menuBtn');
const storesBtn = document.getElementById('storesBtn');
const menuSec = document.getElementById('menuSection');
const storeSec = document.getElementById('storesSection');

menuBtn.addEventListener('click', () => {
  menuSec.classList.remove('hidden');
  storeSec.classList.add('hidden');
  menuBtn.classList.add('active');
  storesBtn.classList.remove('active');
});

storesBtn.addEventListener('click', () => {
  storeSec.classList.remove('hidden');
  menuSec.classList.add('hidden');
  storesBtn.classList.add('active');
  menuBtn.classList.remove('active');
});
</script>

<!-- CAROUSEL SCRIPT --> 
 <script> 
const container = document.querySelector('.carousel-container'); 
const btnLeft = document.querySelector('.carousel-btn.left'); 
const btnRight = document.querySelector('.carousel-btn.right'); 
const scrollStep = 320;

btnLeft.addEventListener('click', () => { container.scrollBy({ left: -scrollStep, behavior: 'smooth' }); 
});

btnRight.addEventListener('click', () => { container.scrollBy({ left: scrollStep, behavior: 'smooth' }); 
}); </script> 

<script>
// When category image is clicked 
document.querySelectorAll('.category-card').forEach(card => { 
  card.addEventListener('click', async () => { 
    const category = card.dataset.category;
    const itemsContainer = document.getElementById('category-items'); 
    
    // ✅ Fetch category items via AJAX (use backticks)
    const res = await fetch(`/kamulan-system/buyer/fetch_items.php?category=${encodeURIComponent(category)}`);
    const data = await res.text(); 
    
    // ✅ Insert HTML (use backticks for template string)
    itemsContainer.innerHTML = `
      <div class="cat-header">
        <h2>${category} Menu</h2>
        <button id="close-category">Close</button> 
      </div>
      ${data}
    `;
    
    itemsContainer.classList.remove('hidden'); 
    
    // ✅ Close button function
    document.getElementById('close-category').onclick = () => { 
      itemsContainer.classList.add('hidden'); 
      itemsContainer.innerHTML = ''; 
    }; 
  }); 
});


// Real-time search functionality
const searchInput = document.querySelector('.search-bar input');
const searchResults = document.querySelector('.search-results');

const allItems = <?php echo json_encode($allItems); ?>;

searchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();

    if (query === '') {
        searchResults.innerHTML = '';
        searchResults.classList.add('hidden');
        return;
    }

    const matches = allItems.filter(item => item.name.toLowerCase().includes(query));

    if (matches.length === 0) {
        searchResults.innerHTML = '<p style="grid-column:1/-1; text-align:center; color:#666;">No results found</p>';
    } else {
        searchResults.innerHTML = matches.map(item => `
            <div class="menu-item">
                <img src="/kamulan-system/assets/images/foods/${item.image}" alt="${item.name}">
                <h4>${item.name}</h4>
                <span class="price">₱${parseFloat(item.price).toFixed(2)}</span>
            </div>
        `).join('');
    }

    searchResults.classList.remove('hidden');
});




document.getElementById('menuBtn').addEventListener('click', () => { 
  const featured = document.querySelector('.featured-scroll'); 
  if (featured) featured.scrollIntoView({ behavior: 'smooth' }); 
  }); 
document.getElementById('storesBtn').addEventListener('click', () => { 
  const branches = document.querySelector('.branches'); 
  if (branches) branches.scrollIntoView({ behavior: 'smooth' });
  });
</script>

</body>
</html>