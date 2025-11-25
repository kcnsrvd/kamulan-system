<?php
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');

// Ensure cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Read parameters (support both old single-item and new base+add-ons flow)
$id  = $_POST['id'] ?? ($_POST['flavor'] ?? null);
$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 0;

$baseId    = isset($_POST['base_id']) ? (int)$_POST['base_id'] : null;
$baseQty   = isset($_POST['base_qty']) ? (int)$_POST['base_qty'] : 0;
$addonIds  = (isset($_POST['addon_ids']) && is_array($_POST['addon_ids'])) ? $_POST['addon_ids'] : [];
$addonQtys = (isset($_POST['addon_qtys']) && is_array($_POST['addon_qtys'])) ? $_POST['addon_qtys'] : [];

// Extra actions used by cart.php (remove / update / place order)
$removeId   = $_POST['remove_id']   ?? null;
$updateId   = $_POST['update_id']   ?? null;
$placeOrder = isset($_POST['place_order']);

// Helper: add or increment a simple item in the cart
function add_item_to_cart($pdo, $id, $qty) {
    if (!$id || $qty <= 0) return;

    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) return;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$c) {
        if ($c['id'] == $id) {
            $c['qty'] += $qty;
            $found = true;
            break;
        }
    }
    unset($c);

    if (!$found) {
        $_SESSION['cart'][] = [
            'id'    => $item['id'],
            'name'  => $item['name'],
            'price' => $item['price'],
            'qty'   => $qty
        ];
    }
}

// Helper: render mini cart HTML based on current session cart
function render_mini_cart() {
    $cart = $_SESSION['cart'] ?? [];

    if (empty($cart)) {
        echo '<p style="text-align:center;color:#666;">Cart is empty</p>';
        return;
    }

    $total = 0;
    echo '<table>';
    foreach ($cart as $c) {
        $lineTotal = $c['price'] * $c['qty'];
        $total += $lineTotal;

        echo '<tr>';
        echo '<td>' . htmlspecialchars($c['name']) . '</td>';
        echo '<td>x' . (int)$c['qty'] . '</td>';
        echo '<td style="text-align:right;">₱' . number_format($lineTotal, 2) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<div class="total">Total: ₱' . number_format($total, 2) . '</div>';
    echo '<a href="/kamulan-system/buyer/cart.php"><button class="button">Go to Checkout</button></a>';
}

// --- Handle remove item (from cart.php) ---
if ($removeId !== null) {
    $removeId = (string)$removeId;
    foreach ($_SESSION['cart'] as $k => $c) {
        if ((string)$c['id'] === $removeId) {
            unset($_SESSION['cart'][$k]);
            break;
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    render_mini_cart();
    exit;
}

// --- Handle update quantity (from cart.php) ---
if ($updateId !== null && isset($_POST['qty'])) {
    $updateId = (string)$updateId;
    $newQty   = max(1, (int)$_POST['qty']);

    foreach ($_SESSION['cart'] as &$c) {
        if ((string)$c['id'] === $updateId) {
            $c['qty'] = $newQty;
            break;
        }
    }
    unset($c);

    render_mini_cart();
    exit;
}

// --- Handle place order (from cart.php) ---
if ($placeOrder) {
    // Basic guard: if cart is empty, just ignore
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        $_SESSION['cart_msg'] = '⚠️ Your cart is empty.';
        exit;
    }

    require_once(__DIR__ . '/../config/session.php'); // ensure user helpers

    if (!function_exists('require_login')) {
        // If helper is not available, just prevent silent failure.
        $_SESSION['cart_msg'] = '⚠️ Unable to place order (login helper missing).';
        exit;
    }

    require_login();
    $user_id = $_SESSION['user']['id'] ?? null;
    $branch_id = (int)($_POST['branch_id'] ?? 0);
    $payment_method = trim($_POST['payment_method'] ?? '');
    $note = trim($_POST['note'] ?? '');

    // Fetch latest address + phone from users table
    $addrStmt = $pdo->prepare("SELECT house_number, street, barangay, municipality, province, nearest_landmark, phone FROM users WHERE id = ?");
    $addrStmt->execute([$user_id]);
    $addrRow = $addrStmt->fetch(PDO::FETCH_ASSOC);

    $address_parts = [];
    if (!empty($addrRow['house_number'])) $address_parts[] = 'House ' . $addrRow['house_number'];
    if (!empty($addrRow['street'])) $address_parts[] = $addrRow['street'];
    if (!empty($addrRow['nearest_landmark'])) $address_parts[] = 'Near ' . $addrRow['nearest_landmark'];
    if (!empty($addrRow['barangay'])) $address_parts[] = 'Brgy. ' . $addrRow['barangay'];
    if (!empty($addrRow['municipality'])) $address_parts[] = $addrRow['municipality'];
    if (!empty($addrRow['province'])) $address_parts[] = $addrRow['province'];
    $address = implode(', ', $address_parts);
    $phone = trim($addrRow['phone'] ?? '');

    if ($branch_id <= 0 || $payment_method === '' || $address === '' || $phone === '') {
        $_SESSION['cart_msg'] = '⚠️ Please complete all required fields before placing your order.';
        exit;
    }

    // Compute subtotal
    $subtotal = 0;
    foreach ($cart as $c) {
        $subtotal += $c['price'] * $c['qty'];
    }

    // Fetch delivery fee for this branch from branches table
    $feeStmt = $pdo->prepare('SELECT delivery_fee FROM branches WHERE id = ?');
    $feeStmt->execute([$branch_id]);
    $delivery_fee = (float)($feeStmt->fetchColumn() ?? 0);

    $total = $subtotal + $delivery_fee;

    // Insert order including delivery_fee so it can be used for reports and rider earnings
    $sql = 'INSERT INTO orders (user_id, branch_id, address, phone, total, delivery_fee, payment_method, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, "Placed", NOW())';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $branch_id, $address, $phone, $total, $delivery_fee, $payment_method]);
    $order_id = $pdo->lastInsertId();

    // Insert order items
    $istmt = $pdo->prepare('INSERT INTO order_items (order_id, menu_item_id, qty, price) VALUES (?, ?, ?, ?)');
    foreach ($cart as $c) {
        $istmt->execute([$order_id, $c['id'], $c['qty'], $c['price']]);
    }

    // Clear cart and set message
    unset($_SESSION['cart']);
    $_SESSION['cart_msg'] = '✅ Order placed successfully! Order ID: ' . $order_id;
    exit;
}

// --- Default: handle add-to-cart from menu.php ---
if ($baseId && $baseQty > 0) {
    // New flow: base item plus optional add-ons
    add_item_to_cart($pdo, $baseId, $baseQty);

    foreach ($addonIds as $idx => $aid) {
        $aid = (int)$aid;
        $aqty = isset($addonQtys[$idx]) ? (int)$addonQtys[$idx] : 0;
        if ($aid && $aqty > 0) {
            add_item_to_cart($pdo, $aid, $aqty);
        }
    }
} elseif ($id && $qty > 0) {
    // Backwards-compatible simple add-to-cart (no add-ons)
    add_item_to_cart($pdo, (int)$id, $qty);
}

// Render mini cart snippet based on current session cart
render_mini_cart();
