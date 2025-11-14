<?php 
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');

// Fetch logged-in user name
$user_name = $_SESSION['user']['first_name'] ?? 'Guest';

// Fetch categories
$categories = $pdo->query("SELECT DISTINCT category FROM menu_items ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

// Fetch branches
$branches = $pdo->query("SELECT * FROM branches")->fetchAll(PDO::FETCH_ASSOC);

// Handle search
$searchQuery = trim($_GET['q'] ?? '');
$searchResults = [];
if ($searchQuery !== '') {
    $sstmt = $pdo->prepare("SELECT * FROM menu_items WHERE (name LIKE ? OR description LIKE ?) AND available > 0 ORDER BY created_at DESC");
    $sstmt->execute(["%$searchQuery%", "%$searchQuery%"]);
    $searchResults = $sstmt->fetchAll(PDO::FETCH_ASSOC);

    // Group flavors
    $groupedSearchResults = [];
    foreach ($searchResults as $item) {
        $base = explode(' - ', $item['name'])[0];
        $groupedSearchResults[$base][] = $item;
    }
}

// Fetch all menu items for each category
$all_by_category = [];
foreach ($categories as $cat) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE category = ? AND available > 0 ORDER BY created_at DESC");
    $stmt->execute([$cat]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group flavors by base name
    $grouped = [];
    foreach ($items as $it) {
        $base = explode(' - ', $it['name'])[0];
        $grouped[$base][] = $it;
    }
    $all_by_category[$cat] = $grouped;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Kamulan - Home</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
:root {
  --primary: #6b4f2c;
  --accent: #8b5e34;
  --dark-brown: #4b3621;
  --text-light: #fff;
  --shadow: 0 4px 10px rgba(0,0,0,0.1);
}
body { font-family: Arial, sans-serif; background:#f5f5f5; margin:0; padding:0; }
main.container { width:90%; margin:30px auto; }

.welcome { text-align:left; margin-bottom:18px; color:var(--dark-brown); font-weight:600; font-size:1.1rem; }

.search-bar { text-align:center; margin-bottom:24px; }
.search-bar input { padding:10px; width:320px; border-radius:8px; border:1px solid #ccc; }
.search-bar button { padding:10px 14px; margin-left:8px; border-radius:8px; border:none; background:var(--accent); color:#fff; cursor:pointer; }

.category-section { margin-bottom:50px; }
.category-section h2 { margin-bottom:12px; color:var(--primary); }

/* Card grid horizontal */
.card-grid {
  display: flex;
  gap: 16px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  padding-bottom: 10px;
}

.card-grid::-webkit-scrollbar {
  height: 8px;
}

.card-grid::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 4px;
}

.card {
  flex: 0 0 220px;
  background: #fff;
  border-radius: 12px;
  box-shadow: var(--shadow);
  padding: 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  scroll-snap-align: start;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover { transform: translateY(-5px); box-shadow: 0 6px 14px rgba(0,0,0,0.2); }

.card img {
  width: 100%;
  height: 140px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 6px;
}

.card h4 { margin:6px 0 4px; font-size:1rem; text-align:center; color:#4b5320; }
.card p { font-size:0.85rem; color:#555; text-align:center; height:40px; overflow:hidden; margin-bottom:6px; }
.card .price { background:#4b5320; color:#fff; padding:6px 12px; border-radius:8px; font-weight:600; text-align:center; }

/* Branches */
.branches { margin-top:30px; background-color:var(--primary); border-radius:18px; padding:30px 20px; text-align:center; color:#fff; }
.branches h2 { font-size:2rem; margin-bottom:30px; letter-spacing:1px; }
.branch-grid { display:flex; justify-content:center; gap:20px; overflow-x:auto; padding-bottom:10px; }
.branch-card { flex:0 0 250px; background:#fff; color:#333; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.2); padding:15px; text-align:center; }
.branch-card img { width:100%; height:140px; object-fit:cover; border-radius:8px; margin-bottom:10px; }
.branch-card h3 { color:var(--primary); margin:6px 0; font-size:1.05rem; }
.branch-card p { font-size:0.85rem; }

@media (max-width:720px) {
  .card { flex:0 0 180px; }
  .card img { height:120px; }
}
</style>
</head>
<body>
<?php include __DIR__ . '/../_partials/header.php'; ?>

<main class="container">
<div class="welcome">Welcome, <?= htmlspecialchars($user_name) ?> ✨</div>

<!-- Search -->
<div class="search-bar">
<form method="GET" action="">
<input type="text" name="q" placeholder="Search food (name / description)..." value="<?= htmlspecialchars($searchQuery) ?>">
<button type="submit">Search</button>
</form>
</div>

<!-- Search Results -->
<?php if ($searchQuery !== ''): ?>
<div class="category-section">
<h2>Search Results for "<?= htmlspecialchars($searchQuery) ?>"</h2>
<?php if (empty($groupedSearchResults)): ?>
<p style="text-align:center;color:#666;">No results found.</p>
<?php else: ?>
<div class="card-grid">
<?php foreach ($groupedSearchResults as $base => $variants): 
    $first = $variants[0];
?>
<div class="card" onclick="location.href='menu.php?id=<?= $first['id'] ?>'">
<img src="/kamulan-system/assets/images/foods/<?= htmlspecialchars($first['image'] ?: 'default.jpg') ?>" alt="">
<h4><?= htmlspecialchars($base) ?></h4>
<p><?= htmlspecialchars($first['description']) ?></p>
<div class="price">₱<?= number_format($first['price'],2) ?></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
<?php endif; ?>

<!-- Categories -->
<?php foreach ($categories as $cat):
$grouped = $all_by_category[$cat] ?? [];
if (empty($grouped)) continue;
?>
<div class="category-section">
<h2><?= htmlspecialchars($cat) ?></h2>
<div class="card-grid">
<?php foreach ($grouped as $base => $variants):
$first = $variants[0]; ?>
<div class="card" onclick="location.href='menu.php?id=<?= $first['id'] ?>'">
<img src="/kamulan-system/assets/images/foods/<?= htmlspecialchars($first['image'] ?: 'default.jpg') ?>" alt="">
<h4><?= htmlspecialchars($base) ?></h4>
<p><?= htmlspecialchars($first['description']) ?></p>
<div class="price">₱<?= number_format($first['price'],2) ?></div>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>

<!-- Branches -->
<div class="branches">
<h2>Our Branches</h2>
<div class="branch-grid">
<?php foreach ($branches as $b): ?>
<div class="branch-card">
<img src="/kamulan-system/assets/images/branches/<?= htmlspecialchars($b['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($b['name']) ?>">
<h3><?= htmlspecialchars($b['name']) ?></h3>
<p>📍 <?= htmlspecialchars($b['address'] ?? 'Address not set') ?><br>☎ <?= htmlspecialchars($b['contact'] ?? 'N/A') ?></p>
</div>
<?php endforeach; ?>
</div>
</div>

</main>
<?php include(__DIR__ . '/../_partials/footer.php'); ?>
</body>
</html>
