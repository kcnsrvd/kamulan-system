<?php
require_once(__DIR__ . '/../config/db.php');
session_start();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    echo '<p>Invalid order ID.</p>';
    exit;
}

// Fetch order + user + rider + branch
$stmt = $pdo->prepare("
    SELECT 
        o.id, o.user_id, o.branch_id, o.total, o.payment_method, o.status,
        o.rider_id, o.created_at, o.delivered_at, o.address,
        u.first_name, u.last_name, u.phone,
        u.house_number, u.street, u.barangay, u.municipality, u.province, u.nearest_landmark,
        r.first_name AS rider_first, r.last_name AS rider_last,
        b.name AS branch_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN users r ON o.rider_id = r.id
    LEFT JOIN branches b ON o.branch_id = b.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order){ 
    echo '<p>Order not found.</p>'; 
    exit; 
}

// Fetch ordered items
$items_stmt = $pdo->prepare("
    SELECT m.name, oi.qty, oi.price
    FROM order_items oi
    JOIN menu_items m ON oi.menu_item_id = m.id
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Build latest address
$latest_address = trim(
    ($order['house_number'] ?? '') . ' ' .
    ($order['street'] ?? '') . ', ' .
    ($order['barangay'] ?? '') . ', ' .
    ($order['municipality'] ?? '') . ', ' .
    ($order['province'] ?? '') .
    (!empty($order['nearest_landmark']) ? ' (near '.$order['nearest_landmark'].')' : '')
);
$final_address = !empty($latest_address) ? $latest_address : ($order['address'] ?? '—');

// Compute subtotal and delivery fee (based on branch)
$subtotal = 0;
foreach($items as $i){
    $subtotal += $i['qty'] * $i['price'];
}

// No need to compute delivery fee here.
// The total from the orders table already includes it.
$subtotal = 0;
foreach ($items as $i) {
    $subtotal += ($i['price'] * $i['qty']);
}

$total = $order['total']; // this is the final amount customer paid (includes DF)
$delivery_fee = $total - $subtotal;




$staff_name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt #<?= htmlspecialchars($order['id']) ?></title>
<style>
body {
    width: 80mm;
    font-family: "Courier New", monospace;
    font-size: 12px;
    color: #000;
    margin: 0 auto;
}
h2, h3 {
    text-align: center;
    margin: 5px 0;
}
hr {
    border: none;
    border-top: 1px dashed #000;
    margin: 6px 0;
}
table {
    width: 100%;
    border-collapse: collapse;
}
td, th {
    padding: 2px 0;
}
.text-right { text-align: right; }
.text-center { text-align: center; }
.total { font-weight: bold; }
@media print {
    @page { size: 80mm auto; margin: 0; }
    body { margin: 0; }
    button { display: none; }
}
</style>
</head>
<body>
    <h2>Kamulan Kitchenette</h2>
    <p class="text-center">
        Branch: <?= htmlspecialchars($order['branch_name'] ?? 'Main') ?><br>
        Date: <?= htmlspecialchars(date('Y-m-d h:i A', strtotime($order['created_at']))) ?><br>
        Receipt #: <?= htmlspecialchars($order['id']) ?>
    </p>

    <hr>

 <p>
        <strong>Staff:</strong> <?= htmlspecialchars($staff_name) ?><br>
        <strong>Rider:</strong> <?= !empty($order['rider_first']) ? htmlspecialchars($order['rider_first'].' '.$order['rider_last']) : '—' ?>
    </p>


    <hr>

       <p>
        <strong>Customer:</strong> <?= htmlspecialchars(($order['first_name'] ?? '').' '.($order['last_name'] ?? '')) ?><br>
        <strong>Phone:</strong> <?= htmlspecialchars($order['phone'] ?? '—') ?><br>
        <strong>Address:</strong> <?= htmlspecialchars($final_address) ?><br>
        <strong>Payment:</strong> <?= htmlspecialchars(ucwords($order['payment_method'])) ?><br>
      
    </p>

    <hr>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($items as $i): ?>
            <tr>
                <td><?= htmlspecialchars($i['name']) ?></td>
                <td class="text-center"><?= intval($i['qty']) ?></td>
                <td class="text-right">₱<?= number_format($i['qty'] * $i['price'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr>

    <table>
        <tr>
    <td colspan="2" class="total">Subtotal</td>
    <td class="total">₱<?= number_format($subtotal, 2) ?></td>
</tr>
<tr>
    <td colspan="2" class="total">Delivery Fee</td>
    <td class="total">₱<?= number_format($delivery_fee, 2) ?></td>
</tr>
<tr>
    <td colspan="2" class="total">Total</td>
    <td class="total">₱<?= number_format($total, 2) ?></td>
</tr>
                
    </table>

    <hr>

    <p class="text-center">Thank you for ordering with Kamulan Kitchenette!</p>

    <button onclick="window.print()">Print Receipt</button>
</body>
</html>
