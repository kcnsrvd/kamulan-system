<?php
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');
require_role(['rider']);

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    echo '<p>Invalid order ID.</p>';
    exit;
}

// Fetch order + user info + branch
$stmt = $pdo->prepare("
SELECT o.*, u.first_name, u.last_name, u.phone,
       u.house_number, u.street, u.barangay, u.municipality, u.province, u.nearest_landmark,
       b.name AS branch_name
FROM orders o
LEFT JOIN users u ON o.user_id=u.id
LEFT JOIN branches b ON o.branch_id=b.id
WHERE o.id=?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    echo '<p>Order not found.</p>';
    exit;
}

// Fetch items
$items_stmt = $pdo->prepare("
SELECT m.name, oi.qty, oi.price
FROM order_items oi
JOIN menu_items m ON oi.menu_item_id=m.id
WHERE oi.order_id=?
");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Build full address
$full_address = trim(
    ($order['house_number'] ?? '') . ' ' . ($order['street'] ?? '') . ', ' .
    ($order['barangay'] ?? '') . ', ' .
    ($order['municipality'] ?? '') . ', ' .
    ($order['province'] ?? '') .
    (!empty($order['nearest_landmark']) ? ' (near ' . $order['nearest_landmark'] . ')' : '')
);
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<style>
.modal-content {
    background: #fff;
    padding: 20px 25px;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    font-family: Poppins, sans-serif;
}
.modal-content h3, .modal-content h4 { color: #4b5320; margin-top:0; }
.modal-content p { margin: 5px 0; }
.modal-content table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.modal-content th, .modal-content td { padding: 6px; border: 1px solid #ddd; }
.modal-content th { background: #4b5320; color: #fff; }
.modal-close {
    float: right;
    font-weight: bold;
    font-size: 18px;
    cursor: pointer;
    color: #333;
}
.btn { display:inline-block; margin-top:10px; padding:6px 10px; border-radius:6px; background:#3e4425; color:#fff; text-decoration:none; }
.btn:hover { background:#4b5320; }

/* Map container */
#customerMap { width: 100%; height: 300px; border-radius: 10px; margin-top: 10px; }
#mapStatus { color:#666; font-size:14px; margin-top:5px; }
</style>

<div class="modal-content">
<span class="modal-close" onclick="document.getElementById('orderModal').style.display='none'">&times;</span>

<h3>Order #<?= htmlspecialchars($order['id']) ?></h3>

<p>
<strong>Customer:</strong> <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?><br>
<strong>Phone:</strong> <?= htmlspecialchars($order['phone'] ?? '—') ?><br>
<strong>Address:</strong> <?= htmlspecialchars($full_address ?: '—') ?><br>
<strong>Branch:</strong> <?= htmlspecialchars($order['branch_name'] ?? '—') ?><br>
<strong>Status:</strong> <?= htmlspecialchars($order['status']) ?><br>
<strong>Total:</strong> ₱<?= number_format($order['total'], 2) ?><br>
<strong>Created At:</strong> <?= htmlspecialchars($order['created_at']) ?><br>
<?php if (!empty($order['delivered_at'])): ?>
<strong>Delivered At:</strong> <?= htmlspecialchars($order['delivered_at']) ?><br>
<?php endif; ?>
</p>

<h4>Items</h4>
<?php if (empty($items)): ?>
<p>No items found.</p>
<?php else: ?>
<table>
<tr>
<th>Item</th>
<th>Qty</th>
<th>Price</th>
</tr>
<?php foreach ($items as $i): ?>
<tr>
<td><?= htmlspecialchars($i['name']) ?></td>
<td style="text-align:center;"><?= intval($i['qty']) ?></td>
<td>₱<?= number_format($i['price'], 2) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<h4>Delivery Location</h4>
<div id="customerMap"></div>
<p id="mapStatus">Loading map...</p>

</div>
<a href="javascript:void(0)" class="btn" onclick="closeModal()">Close</a>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
function closeModal() {
    const modal = document.getElementById('orderModal');
    if(modal) modal.style.display='none';
}

// 🌍 Initialize Leaflet map dynamically after modal loads
(function initMap() {
    const mapDiv = document.getElementById("customerMap");
    const statusDiv = document.getElementById("mapStatus");
    if (!mapDiv) return;

    const address = <?= json_encode($full_address) ?>;

    if (!address || address.trim() === "") {
        statusDiv.innerText = "No address available.";
        return;
    }

    fetch("https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(address), {
        headers: {
            "Accept": "application/json",
            "User-Agent": "KamulanKitchenetteRiderApp/1.0"
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data || data.length === 0) {
            statusDiv.innerText = "Unable to locate address.";
            return;
        }

        const lat = parseFloat(data[0].lat);
        const lon = parseFloat(data[0].lon);

        statusDiv.innerText = "";

        const map = L.map("customerMap").setView([lat, lon], 16);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19
        }).addTo(map);

        L.marker([lat, lon]).addTo(map)
            .bindPopup("Customer Location")
            .openPopup();
    })
    .catch(() => {
        statusDiv.innerText = "Error loading map.";
    });
})();
</script>
