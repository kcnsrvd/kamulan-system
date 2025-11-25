<?php
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');
require_role(['rider']);

$user = $_SESSION['user'];
$branch_id = $user['branch_id'];
$rider_id = $user['id'];

// Branch name (for display only)
$branch_name = $user['branch_name'] ?? '';

// Analytics: today's deliveries and earnings based on delivery_fee
$analytics_stmt = $pdo->prepare(" 
    SELECT COUNT(*) AS total_deliveries, COALESCE(SUM(delivery_fee),0) AS total_earnings
    FROM orders
    WHERE rider_id=? AND status='Delivered' AND DATE(delivered_at)=CURDATE()
");
$analytics_stmt->execute([$rider_id]);
$analytics = $analytics_stmt->fetch(PDO::FETCH_ASSOC);
$total_deliveries = (int)($analytics['total_deliveries'] ?? 0);
$total_earnings = (float)($analytics['total_earnings'] ?? 0);

// Available orders for this rider (status=Placed, unassigned)
$orders_stmt = $pdo->prepare(" 
    SELECT o.*, CONCAT(u.first_name,' ',u.last_name) AS customer
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.branch_id=? AND status='Placed' AND (rider_id IS NULL OR rider_id=0)
    ORDER BY o.created_at ASC
");
$orders_stmt->execute([$branch_id]);
$available_orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Orders assigned to this rider (status=Out for Delivery)
$assigned_stmt = $pdo->prepare(" 
    SELECT o.*, CONCAT(u.first_name,' ',u.last_name) AS customer
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.rider_id=? AND status='Out for Delivery'
    ORDER BY o.created_at ASC
");
$assigned_stmt->execute([$rider_id]);
$assigned_orders = $assigned_stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine availability based on pending deliveries
$has_pending_delivery = !empty($assigned_orders);

// Sync the is_available flag in users table so it always reflects pending deliveries
$avail_stmt = $pdo->prepare('SELECT is_available FROM users WHERE id = ?');
$avail_stmt->execute([$rider_id]);
$current_flag = (int)($avail_stmt->fetchColumn() ?? 1);

if ($has_pending_delivery && $current_flag === 1) {
    // Rider has an active delivery but is marked available -> mark unavailable
    $pdo->prepare('UPDATE users SET is_available = 0 WHERE id = ?')->execute([$rider_id]);
    $current_flag = 0;
} elseif (!$has_pending_delivery && $current_flag === 0) {
    // Rider has no active delivery but is marked unavailable -> mark available
    $pdo->prepare('UPDATE users SET is_available = 1 WHERE id = ?')->execute([$rider_id]);
    $current_flag = 1;
}

$is_available = $current_flag === 1;

// Completed deliveries
$history_stmt = $pdo->prepare(" 
    SELECT o.*, CONCAT(u.first_name,' ',u.last_name) AS customer, b.name AS branch_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN branches b ON o.branch_id = b.id
    WHERE o.rider_id=? AND status='Delivered'
    ORDER BY delivered_at DESC
");
$history_stmt->execute([$rider_id]);
$history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Rider Dashboard - Kamulan</title>
<link rel="stylesheet" href="/kamulan-system/assets/css/style.css">
<style>
body{font-family:Poppins,sans-serif;background:#f5f5f0;margin:0;}
.container{width:90%;margin:20px auto;background:#fff;padding:20px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.1);}
h2,h3{color:#3e4425;}
.stats{display:flex;gap:15px;flex-wrap:wrap;margin-bottom:25px;}
.stat-card{flex:1;min-width:200px;background:#4b5320;color:#fff;padding:15px;border-radius:10px;text-align:center;box-shadow:0 2px 6px rgba(0,0,0,0.15);}
table{width:100%;border-collapse:collapse;margin-top:15px;}
th,td{border:1px solid #ddd;padding:8px;text-align:left;}
th{background:#4b5320;color:#fff;}
button,a.btn{padding:5px 8px;border:none;border-radius:5px;background:#3e4425;color:#fff;cursor:pointer;text-decoration:none;}
button:hover,a.btn:hover{background:#4b5320;}
form{margin:0;display:inline;}
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:1000;}
.modal-content{background:white;padding:20px;border-radius:12px;width:90%;max-width:500px;max-height:80vh;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,0.3);}
.modal-content h3,h4{color:#4b5320;margin-top:0;}
.modal-content p{margin:5px 0;}
.modal-content table{width:100%;border-collapse:collapse;margin-top:10px;}
.modal-content th,.modal-content td{padding:6px;border:1px solid #ddd;}
.modal-content th{background:#4b5320;color:#fff;}
.modal-close{float:right;font-weight:bold;font-size:18px;cursor:pointer;color:#333;}
</style>
</head>
<body>
<?php include(__DIR__ . '/../_partials/header_staff.php'); ?>

<main class="container">
<h2>Rider Dashboard - <?= htmlspecialchars($branch_name ?: 'Branch') ?></h2>

<div class="stats">
  <div class="stat-card">
    <h3>Deliveries Today</h3>
    <p><?= $total_deliveries ?></p>
  </div>
  <div class="stat-card">
    <h3>Earnings Today (₱)</h3>
    <p><?= number_format($total_earnings,2) ?></p>
  </div>
  <div class="stat-card">
    <h3>Availability</h3>
    <p><?= $is_available ? 'Available' : 'On Delivery' ?></p>
  </div>
</div>

<h3>Available Orders</h3>
<?php if(empty($available_orders)): ?>
<p>No available deliveries.</p>
<?php else: ?>
<table>
<thead>
<tr>
<th>Order ID</th>
<th>Customer</th>
<th>Total</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach($available_orders as $o): ?>
<tr>
<td>#<?= $o['id'] ?></td>
<td><?= htmlspecialchars($o['customer']) ?></td>
<td>₱<?= number_format($o['total'],2) ?></td>
<td><?= htmlspecialchars($o['status']) ?></td>
<td>
<a class="btn" href="view_order.php?id=<?= $o['id'] ?>">View Details</a>
<form method="post" action="accept.php" style="display:inline;">
<input type="hidden" name="order_id" value="<?= $o['id'] ?>">
<button type="submit">Accept</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<h3>Orders Assigned to Me</h3>
<?php if(empty($assigned_orders)): ?>
<p>No orders assigned yet.</p>
<?php else: ?>
<table>
<thead>
<tr>
<th>Order ID</th>
<th>Customer</th>
<th>Total</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach($assigned_orders as $o): ?>
<tr>
<td>#<?= $o['id'] ?></td>
<td><?= htmlspecialchars($o['customer']) ?></td>
<td>₱<?= number_format($o['total'],2) ?></td>
<td><?= htmlspecialchars($o['status']) ?></td>
<td>
<a class="btn" href="view_order.php?id=<?= $o['id'] ?>">View Details</a>
<form method="post" action="mark_delivered.php" style="display:inline;">
<input type="hidden" name="order_id" value="<?= $o['id'] ?>">
<button type="submit">Mark Delivered</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<h3>Completed Deliveries (History)</h3>
<?php if(empty($history)): ?>
<p>No deliveries completed yet.</p>
<?php else: ?>
<table>
<thead>
<tr>
<th>Order ID</th>
<th>Customer</th>
<th>Total</th>
<th>Delivery Fee (₱)</th>
<th>Date Delivered</th>
<th>Earnings (₱)</th>
</tr>
</thead>
<tbody>
<?php foreach($history as $h): ?>
<tr>
<td>#<?= $h['id'] ?></td>
<td><?= htmlspecialchars($h['customer']) ?></td>
<td>₱<?= number_format($h['total'],2) ?></td>
<td>₱<?= number_format($h['delivery_fee'] ?? 0,2) ?></td>
<td><?= htmlspecialchars($h['delivered_at']) ?></td>
<td>₱<?= number_format($h['delivery_fee'] ?? 0,2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

</main>

</body>
</html>
