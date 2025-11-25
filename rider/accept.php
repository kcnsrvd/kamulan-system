<?php
require_once(__DIR__.'/../config/session.php');
require_once(__DIR__.'/../config/db.php');
require_once(__DIR__.'/../config/helpers.php');
require_role(['rider']);

if($_SERVER['REQUEST_METHOD']==='POST'){
    $order_id = intval($_POST['order_id']);
    $rider_id = $_SESSION['user']['id'];

    // Do not allow accepting a new order if rider already has a pending delivery
    $check = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE rider_id = ? AND status = 'Out for Delivery'");
    $check->execute([$rider_id]);
    $active_count = (int)$check->fetchColumn();
    if ($active_count > 0) {
        $_SESSION['error'] = "You already have an active delivery. Please complete it before accepting another order.";
        header('Location: dashboard.php');
        exit;
    }

    // Accept the order if it's unassigned and status=Placed
    $stmt = $pdo->prepare(" 
        UPDATE orders 
        SET rider_id=?, status='Out for Delivery'
        WHERE id=? AND status='Placed' AND (rider_id IS NULL OR rider_id=0)
    ");
    $stmt->execute([$rider_id, $order_id]);

    if($stmt->rowCount()>0){
        // Mark rider unavailable because they now have a pending delivery
        $pdo->prepare("UPDATE users SET is_available=0 WHERE id=?")->execute([$rider_id]);

        // Fetch buyer user_id to notify
        $uStmt = $pdo->prepare('SELECT user_id FROM orders WHERE id = ?');
        $uStmt->execute([$order_id]);
        $user_id = $uStmt->fetchColumn() ?: null;

        // Send push: status now Out for Delivery
        send_order_status_push($order_id, 'Out for Delivery', $user_id);

        $_SESSION['success'] = "You accepted order #$order_id.";
    } else {
        $_SESSION['error'] = "Unable to accept order #$order_id. Maybe it's already assigned.";
    }
}

header('Location: dashboard.php'); exit;
