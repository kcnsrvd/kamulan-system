<?php
require_once("../config/session.php");
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;

    if ($id && $quantity && isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as &$c) {
            if ($c['id'] == $id) {
                $c['qty'] = intval($quantity);
                break;
            }
        }
        unset($c);
        echo "updated";
    } else {
        echo "error";
    }
}
?>
