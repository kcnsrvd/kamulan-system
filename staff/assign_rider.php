<?php
require_once(__DIR__.'/../config/session.php');
require_once(__DIR__.'/../config/db.php');
require_role(['staff']);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $order_id = intval($_POST['order_id']);
    
    // Fetch order branch
    $stmt = $pdo->prepare("SELECT branch_id, status, rider_id FROM orders WHERE id=?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$order || $order['status'] !== 'Out for Delivery' || !empty($order['rider_id'])) exit;

    $branch_id = $order['branch_id'];

    // Get available rider with the fewest deliveries today (fair assignment)
    $rider_stmt = $pdo->prepare("SELECT u.id,
        COALESCE((
            SELECT COUNT(*) FROM orders o
            WHERE o.rider_id = u.id
              AND o.status = 'Delivered'
              AND DATE(o.delivered_at) = CURDATE()
        ), 0) AS deliveries_today
      FROM users u
      WHERE u.role = 'rider' AND u.branch_id = ? AND u.is_available = 1
      ORDER BY deliveries_today ASC, u.id ASC
      LIMIT 1");
    $rider_stmt->execute([$branch_id]);
    $rider = $rider_stmt->fetchColumn();

    if($rider){
        // Assign rider
        $pdo->prepare("UPDATE orders SET rider_id=? WHERE id=?")->execute([$rider, $order_id]);
        $pdo->prepare("UPDATE users SET is_available=0 WHERE id=?")->execute([$rider]);
        $_SESSION['success'] = "Rider #$rider assigned to order #$order_id.";
    } else {
        $_SESSION['error'] = "No available riders for this branch.";
    }
}

header('Location: dashboard.php');
exit;
