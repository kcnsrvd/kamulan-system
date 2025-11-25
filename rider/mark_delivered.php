<?php
require_once(__DIR__.'/../config/session.php');
require_once(__DIR__.'/../config/db.php');
require_once(__DIR__.'/../config/helpers.php');
require_role(['rider']);

if($_SERVER['REQUEST_METHOD']==='POST'){
    $order_id = intval($_POST['order_id']);
    $rider_id = $_SESSION['user']['id'];

    $stmt = $pdo->prepare("
        UPDATE orders
        SET status='Delivered', delivered_at=NOW()
        WHERE id=? AND rider_id=? AND status='Out for Delivery'
    ");
    $stmt->execute([$order_id, $rider_id]);

    if($stmt->rowCount()>0){
        // Rider finished one delivery. Check if they still have other pending deliveries.
        $check = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE rider_id = ? AND status = 'Out for Delivery'");
        $check->execute([$rider_id]);
        $remaining = (int)$check->fetchColumn();
        if ($remaining === 0) {
            // Only mark available if there are no more active deliveries.
            $pdo->prepare("UPDATE users SET is_available=1 WHERE id=?")->execute([$rider_id]);
        }

        // Fetch buyer user_id to notify
        $uStmt = $pdo->prepare('SELECT user_id FROM orders WHERE id = ?');
        $uStmt->execute([$order_id]);
        $user_id = $uStmt->fetchColumn() ?: null;

        // Send push: status now Delivered
        send_order_status_push($order_id, 'Delivered', $user_id);

        $_SESSION['success'] = "Order #$order_id marked as delivered!";
    } else {
        $_SESSION['error'] = "Unable to mark order #$order_id as delivered.";
    }
}

header('Location: dashboard.php'); exit;
