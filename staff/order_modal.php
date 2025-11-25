<?php 
require_once(__DIR__ . '/../config/db.php');

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) { 
    echo '<p>Invalid order ID.</p>'; 
    exit; 
}

// Fetch order info + user info + rider info
$stmt = $pdo->prepare("
    SELECT 
        o.id, o.user_id, o.branch_id, o.total, o.payment_method, o.status, 
        o.rider_id, o.created_at, o.delivered_at, o.address, o.phone, o.note,
        u.first_name, u.last_name, u.phone AS user_phone, 
        u.house_number, u.street, u.barangay, u.municipality, u.province, u.nearest_landmark,
        r.first_name AS rider_first, r.last_name AS rider_last
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN users r ON o.rider_id = r.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order){ 
    echo '<p>Order not found.</p>'; 
    exit; 
}

// Fetch order items (add-ons are stored as separate order_items rows)
$items_stmt = $pdo->prepare("
    SELECT m.name, oi.qty, oi.price
    FROM order_items oi
    JOIN menu_items m ON oi.menu_item_id = m.id
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Auto-assign rider if status is Out for Delivery and no rider assigned
if($order['status'] === 'Out for Delivery' && empty($order['rider_id'])){
    // Pick available rider with the fewest deliveries today for this branch (fair assignment)
    $rider_stmt = $pdo->prepare("SELECT u.id, u.first_name, u.last_name,
        COALESCE((
            SELECT COUNT(*) FROM orders o
            WHERE o.rider_id = u.id
              AND o.status = 'Delivered'
              AND DATE(o.delivered_at) = CURDATE()
        ), 0) AS deliveries_today
      FROM users u
      WHERE u.role='rider' AND u.branch_id=? AND u.is_available=1
      ORDER BY deliveries_today ASC, u.id ASC
      LIMIT 1");
    $rider_stmt->execute([$order['branch_id']]);
    $rider = $rider_stmt->fetch(PDO::FETCH_ASSOC);
    if($rider){
        // Only assign if still unassigned to avoid race with other processes
        $update_stmt = $pdo->prepare("UPDATE orders SET rider_id=? WHERE id=? AND (rider_id IS NULL OR rider_id=0)");
        $update_stmt->execute([$rider['id'], $order_id]);
        if ($update_stmt->rowCount() > 0) {
            // Mark this rider as unavailable now that they have a delivery
            $pdo->prepare("UPDATE users SET is_available = 0 WHERE id = ?")->execute([$rider['id']]);

            $order['rider_id'] = $rider['id'];
            $order['rider_first'] = $rider['first_name'];
            $order['rider_last'] = $rider['last_name'];
        }
    }
}

// Build latest full address from users table (fallback)
$address_parts = [];
if (!empty($order['house_number'])) $address_parts[] = 'House ' . $order['house_number'];
if (!empty($order['street'])) $address_parts[] = $order['street'];
if (!empty($order['nearest_landmark'])) $address_parts[] = 'Near ' . $order['nearest_landmark'];
if (!empty($order['barangay'])) $address_parts[] = 'Brgy. ' . $order['barangay'];
if (!empty($order['municipality'])) $address_parts[] = $order['municipality'];
if (!empty($order['province'])) $address_parts[] = $order['province'];
$latest_address = implode(', ', $address_parts);

// Address & phone
$final_address = !empty($order['address']) ? trim($order['address']) : ($latest_address ?: '—');
$display_phone = $order['phone'] ?? ($order['user_phone'] ?? '—');
?>

<h3 style="color:#4b5320;">Order #<?= htmlspecialchars($order['id']) ?></h3>

<p>
<strong>Name:</strong> <?= htmlspecialchars(($order['first_name'] ?? '').' '.($order['last_name'] ?? '')) ?><br>
<strong>Phone:</strong> <?= htmlspecialchars($display_phone) ?><br>
<strong>Address:</strong> <?= htmlspecialchars($final_address) ?><br>
<strong>Status:</strong> <?= htmlspecialchars($order['status']) ?><br>
<strong>Rider:</strong> <?= !empty($order['rider_first']) ? htmlspecialchars($order['rider_first'].' '.$order['rider_last']) : '—' ?><br>
<strong>Total:</strong> ₱<?= number_format($order['total'],2) ?><br>
<strong>Date:</strong> <?= htmlspecialchars($order['created_at']) ?><br>
<strong>Note:</strong> <?= htmlspecialchars($order['note'] ?? 'None') ?><br>
</p>

<h4 style="color:#4b5320;">Items</h4>
<table style="width:100%;border-collapse:collapse;margin-top:8px;">
<tr style="background:#4b5320;color:white;">
<th style="padding:6px;">Item</th>
<th style="padding:6px;">Qty</th>
<th style="padding:6px;">Price</th>
</tr>

<?php foreach($items as $i): ?>
<tr>
<td style="padding:6px;"><?= htmlspecialchars($i['name']) ?></td>
<td style="padding:6px;text-align:center;"><?= intval($i['qty']) ?></td>
<td style="padding:6px;">₱<?= number_format($i['price'],2) ?></td>
</tr>
<?php endforeach; ?>
</table>
