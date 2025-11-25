<?php
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');
require_role(['rider']);

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    echo '<p>Invalid order ID.</p>';
    exit;
}

// --- Fetch order + user info + branch ---
$stmt = $pdo->prepare("
    SELECT
        o.id,
        o.user_id,
        o.branch_id,
        o.total,
        o.status,
        o.note,
        o.created_at,
        o.delivered_at,
        o.phone,
        o.address,

        u.first_name,
        u.last_name,
        u.phone AS user_phone,
        u.house_number,
        u.street,
        u.barangay,
        u.municipality,
        u.province,
        u.nearest_landmark,

        b.name AS branch_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN branches b ON o.branch_id = b.id
    WHERE o.id = ?
");


$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo '<p>Order not found.</p>';
    exit;
}

// --- Fetch items ---
$items_stmt = $pdo->prepare(
    "SELECT m.name, oi.qty, oi.price
     FROM order_items oi
     JOIN menu_items m ON oi.menu_item_id = m.id
     WHERE oi.order_id = ?"
);
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Build full address (for display) ---
$full_address = trim($order['address'] ?? '');
if ($full_address === '') {
    $address_parts = [];
    if (!empty($order['house_number'])) $address_parts[] = 'House ' . $order['house_number'];
    if (!empty($order['street'])) $address_parts[] = $order['street'];
    if (!empty($order['nearest_landmark'])) $address_parts[] = 'Near ' . $order['nearest_landmark'];
    if (!empty($order['barangay'])) $address_parts[] = 'Brgy. ' . $order['barangay'];
    if (!empty($order['municipality'])) $address_parts[] = $order['municipality'];
    if (!empty($order['province'])) $address_parts[] = $order['province'];
    $full_address = implode(', ', $address_parts);
}

// --- Build a simpler address string for geocoding (barangay + municipality + province + country) ---
$geo_address_parts = [];
if (!empty($order['barangay'])) {
    // Nominatim usually understands plain barangay name
    $geo_address_parts[] = $order['barangay'];
}
if (!empty($order['municipality'])) {
    $geo_address_parts[] = $order['municipality'];
}
if (!empty($order['province'])) {
    $geo_address_parts[] = $order['province'];
}
$geo_address = implode(', ', $geo_address_parts);
if ($geo_address !== '') {
    $geo_address .= ', Philippines';
}

// Fallback: if somehow barangay/municipality/province are empty, clean up the full address
if ($geo_address === '' && $full_address !== '') {
    $geo_address = $full_address;
    $geo_address = str_replace(['(municipality/city)', '(province)'], '', $geo_address);
    $geo_address = preg_replace('/\bNear\b\s*/i', '', $geo_address);
    $geo_address = preg_replace('/\bBrgy\.?\b\s*/i', 'Barangay ', $geo_address);
    $geo_address = preg_replace('/\s+,/', ',', $geo_address);
    $geo_address = trim($geo_address, " ,");
    if (stripos($geo_address, 'Philippines') === false) {
        $geo_address .= ', Philippines';
    }
}

// --- Server-side geocoding ---
$geo_lat = null;
$geo_lon = null;
if ($geo_address !== '') {
    // Use HTTP to avoid SSL/certificate issues in some PHP/XAMPP setups
    $url = 'http://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($geo_address);
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: KamulanKitchenetteRiderApp/1.0\r\n",
            'timeout' => 5
        ]
    ];
    $context = stream_context_create($opts);
    $json = @file_get_contents($url, false, $context);
    if ($json !== false) {
        $data = json_decode($json, true);
        if (!empty($data[0]['lat']) && !empty($data[0]['lon'])) {
            $geo_lat = (float)$data[0]['lat'];
            $geo_lon = (float)$data[0]['lon'];
        }
    }
}

// --- Display phone ---
$display_phone = $order['phone'] ?? $order['user_phone'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Order #<?= htmlspecialchars($order['id']) ?> - Rider View</title>
<link rel="stylesheet" href="/kamulan-system/assets/css/style.css">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet Routing Machine CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<style>
body { font-family: Poppins,sans-serif; background:#f5f5f0; margin:0; }
.container { width:90%; max-width:900px; margin:20px auto; background:#fff; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
h2,h3,h4 { color:#3e4425; }
.order-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; }
.order-meta p { margin:4px 0; }
.items-table { width:100%; border-collapse:collapse; margin-top:10px; }
.items-table th, .items-table td { padding:6px; border:1px solid #ddd; }
.items-table th { background:#4b5320; color:#fff; }
.btn { display:inline-block; margin-top:10px; padding:6px 10px; border-radius:6px; background:#3e4425; color:#fff; text-decoration:none; border:none; cursor:pointer; }
.btn:hover { background:#4b5320; }
#customerMap { width:100%; height:300px; border-radius:10px; margin-top:10px; }
#mapStatus { color:#666; font-size:14px; margin-top:5px; }
#mapLoading { text-align:center; color:#666; font-size:14px; margin-top:10px; }
.spinner { border:4px solid #f3f3f3; border-top:4px solid #4b5320; border-radius:50%; width:20px; height:20px; animation:spin 1s linear infinite; display:inline-block; margin-right:10px; }
@keyframes spin {0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}
</style>
</head>
<body>
<?php include(__DIR__ . '/../_partials/header_staff.php'); ?>
<main class="container">
  <div class="order-header">
    <h2>Order #<?= htmlspecialchars($order['id']) ?></h2>
    <a href="dashboard.php" class="btn">← Back to Dashboard</a>
  </div>

  <section class="order-meta">
    <p><strong>Customer:</strong> <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($display_phone !== '' ? $display_phone : '—') ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($full_address !== '' ? $full_address : '—') ?></p>
    <p><strong>Branch:</strong> <?= htmlspecialchars($order['branch_name'] ?? '—') ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
    <p><strong>Total:</strong> ₱<?= number_format($order['total'], 2) ?></p>
    <p><strong>Note:</strong> <?= htmlspecialchars($order['note'] ?? 'None') ?></p>
    <p><strong>Created At:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
    <?php if (!empty($order['delivered_at'])): ?>
      <p><strong>Delivered At:</strong> <?= htmlspecialchars($order['delivered_at']) ?></p>
    <?php endif; ?>
  </section>

  <section class="order-items">
    <h3>Items</h3>
    <?php if (empty($items)): ?>
      <p>No items found.</p>
    <?php else: ?>
      <table class="items-table">
        <tr>
          <th>Item</th>
          <th>Qty</th>
          <th>Price</th>
        </tr>
        <?php foreach ($items as $i): ?>
          <tr>
            <td><?= htmlspecialchars($i['name']) ?></td>
            <td style="text-align:center;"><?= (int)$i['qty'] ?></td>
            <td>₱<?= number_format($i['price'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </section>

  <section class="order-map">
    <h3>Delivery Location</h3>
    <!-- Map Container -->
    <div id="orderMap" style="height: 400px; width: 100%; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
  </section>
</main>
<?php include(__DIR__ . '/../_partials/footer.php'); ?>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Leaflet Routing Machine JS -->
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
(function () {
  'use strict';

  // Use coordinates computed on the server (PHP) to avoid CORS issues in the browser
  const buyerLat = <?= $geo_lat !== null ? $geo_lat : 'null' ?>;
  const buyerLon = <?= $geo_lon !== null ? $geo_lon : 'null' ?>;
  const buyerAddress = <?= json_encode($full_address !== '' ? $full_address : $geo_address) ?>;
  const buyerLandmark = <?= json_encode($order['nearest_landmark'] ?? '') ?>;

  // Fallback rider location (if geolocation fails) – Manila city center
  const fallbackRiderLatLng = [14.5995, 120.9842];

  function getRiderLocation() {
    return new Promise((resolve) => {
      if (!navigator.geolocation) {
        console.warn('Geolocation not supported, using fallback rider location.');
        resolve(fallbackRiderLatLng);
        return;
      }

      navigator.geolocation.getCurrentPosition(
        (pos) => {
          const coords = [pos.coords.latitude, pos.coords.longitude];
          resolve(coords);
        },
        (err) => {
          console.warn('Geolocation failed, using fallback rider location.', err);
          resolve(fallbackRiderLatLng);
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 30000,
        }
      );
    });
  }

  function initMap(riderLatLng, buyerLatLng, buyerAddressText) {
    const map = L.map('orderMap').setView(buyerLatLng || fallbackRiderLatLng, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    // Rider marker
    L.marker(riderLatLng, { title: 'Rider' })
      .addTo(map)
      .bindPopup('<b>Rider Location</b>');

    if (buyerLatLng) {
      // Buyer marker with optional landmark in popup
      let popupHtml = `<b>Buyer Address:</b><br>${buyerAddressText}`;
      if (buyerLandmark && buyerLandmark.trim() !== '') {
        popupHtml += `<br><b>Landmark:</b> ${buyerLandmark}`;
      }

      L.marker(buyerLatLng, { title: 'Buyer' })
        .addTo(map)
        .bindPopup(popupHtml);

      // Fit bounds so both markers are visible
      const bounds = L.latLngBounds([riderLatLng, buyerLatLng]);
      map.fitBounds(bounds, { padding: [50, 50] });

      // Draw route using Leaflet Routing Machine (if available)
      if (typeof L.Routing !== 'undefined') {
        L.Routing.control({
          waypoints: [
            L.latLng(riderLatLng[0], riderLatLng[1]),
            L.latLng(buyerLatLng[0], buyerLatLng[1]),
          ],
          routeWhileDragging: false,
          addWaypoints: false,
          draggableWaypoints: false,
          showAlternatives: false,
          lineOptions: {
            styles: [{ color: '#0066ff', weight: 5, opacity: 0.8 }],
          },
          show: false,
        }).addTo(map);
      } else {
        console.warn('Leaflet Routing Machine not available – route will not be drawn.');
      }
    } else {
      console.warn('Buyer coordinates not available; showing rider location only.');
    }
  }

  async function initDeliveryMap() {
    // Determine buyer coordinates from server-side geocoding
    const buyerLatLng =
      buyerLat !== null && buyerLon !== null ? [buyerLat, buyerLon] : null;

    // Get rider coordinates (geolocation or fallback)
    const riderLatLng = await getRiderLocation();

    initMap(riderLatLng, buyerLatLng, buyerAddress);
  }

  document.addEventListener('DOMContentLoaded', initDeliveryMap);
})();
</script>
</body>
</html>
