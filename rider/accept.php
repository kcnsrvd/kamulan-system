<?php
require_once(__DIR__.'/../config/session.php');
require_once(__DIR__.'/../config/db.php');
require_role(['rider']);

if($_SERVER['REQUEST_METHOD']==='POST'){
    $order_id = intval($_POST['order_id']);
    $rider_id = $_SESSION['user']['id'];

    // Accept the order if it's unassigned and status=Placed
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET rider_id=?, status='Out for Delivery'
        WHERE id=? AND status='Placed' AND (rider_id IS NULL OR rider_id=0)
    ");
    $stmt->execute([$rider_id, $order_id]);

    if($stmt->rowCount()>0){
        // Mark rider unavailable
        $pdo->prepare("UPDATE users SET is_available=0 WHERE id=?")->execute([$rider_id]);
        $_SESSION['success'] = "You accepted order #$order_id.";
    } else {
        $_SESSION['error'] = "Unable to accept order #$order_id. Maybe it's already assigned.";
    }
}

header('Location: dashboard.php'); exit;
