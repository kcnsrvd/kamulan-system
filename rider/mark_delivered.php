<?php
require_once(__DIR__.'/../config/session.php');
require_once(__DIR__.'/../config/db.php');
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
        // Make rider available again
        $pdo->prepare("UPDATE users SET is_available=1 WHERE id=?")->execute([$rider_id]);
        $_SESSION['success'] = "Order #$order_id marked as delivered!";
    } else {
        $_SESSION['error'] = "Unable to mark order #$order_id as delivered.";
    }
}

header('Location: dashboard.php'); exit;
