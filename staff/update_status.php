<?php
require_once(__DIR__.'/../config/session.php');
require_once(__DIR__.'/../config/db.php');
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
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false,'message'=>'DB error']);
    }
    exit;
}
echo json_encode(['success'=>false,'message'=>'Invalid request']);
