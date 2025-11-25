<?php
require_once(__DIR__.'/../config/session.php');
require_once(__DIR__.'/../config/db.php');
require_once(__DIR__.'/../config/helpers.php');
require_role(['staff']);

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD']==='POST'){
    $order_id = intval($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    $allowed = ['Preparing','Out for Delivery','Delivered','Cancelled'];
    if($order_id <= 0 || !in_array($status,$allowed,true)){
        echo json_encode(['success'=>false,'message'=>'Invalid input']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE orders SET status=? WHERE id=?');
    $ok = $stmt->execute([$status,$order_id]);
    if($ok){
        // Keep rider availability in sync with order status
        $rStmt = $pdo->prepare('SELECT rider_id FROM orders WHERE id = ?');
        $rStmt->execute([$order_id]);
        $rider_id = $rStmt->fetchColumn();
        if ($rider_id) {
            if ($status === 'Out for Delivery') {
                // Rider now has an active delivery
                $pdo->prepare('UPDATE users SET is_available = 0 WHERE id = ?')->execute([$rider_id]);
            } elseif (in_array($status, ['Delivered','Cancelled','Preparing'], true)) {
                // If this order is no longer Out for Delivery, rider might become available again
                $cStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE rider_id = ? AND status = 'Out for Delivery'");
                $cStmt->execute([$rider_id]);
                $pending = (int)$cStmt->fetchColumn();
                if ($pending === 0) {
                    $pdo->prepare('UPDATE users SET is_available = 1 WHERE id = ?')->execute([$rider_id]);
                }
            }
        }

        // Optionally fetch user_id to send with notification
        $uStmt = $pdo->prepare('SELECT user_id FROM orders WHERE id = ?');
        $uStmt->execute([$order_id]);
        $user_id = $uStmt->fetchColumn() ?: null;
        send_order_status_push($order_id, $status, $user_id);
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false,'message'=>'DB error']);
    }
    exit;
}
echo json_encode(['success'=>false,'message'=>'Invalid request']);
