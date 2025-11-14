<?php
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
$cart = &$_SESSION['cart']; // reference for convenience
$msg = "";

// --- Fetch user full address & phone ---
$user_id = $_SESSION['user']['id'] ?? null;
$user_address = '';
$user_phone = '';

if ($user_id) {
    $stmt = $pdo->prepare("SELECT house_number, street, barangay, municipality, province, nearest_landmark, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $parts = [];
        if (!empty($row['house_number'])) $parts[] = 'House ' . $row['house_number'];
        if (!empty($row['street'])) $parts[] = $row['street'];
        if (!empty($row['nearest_landmark'])) $parts[] = 'Near ' . $row['nearest_landmark'];
        if (!empty($row['barangay'])) $parts[] = 'Brgy. ' . $row['barangay'];
        if (!empty($row['municipality'])) $parts[] = $row['municipality'];
        if (!empty($row['province'])) $parts[] = $row['province'];
        $user_address = implode(', ', $parts);
        $user_phone = $row['phone'] ?? '';
    }
}

// --- Handle updates via POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Remove item (safer removal: match by item id inside cart entries)
    if (isset($_POST['remove_id'])) {
        $remove_id = intval($_POST['remove_id']);
        foreach ($_SESSION['cart'] as $k => $item) {
            if (isset($item['id']) && intval($item['id']) === $remove_id) {
                unset($_SESSION['cart'][$k]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex
                break;
            }
        }
    }

    // Update quantity (fallback if you decide to submit qty via form)
    if (isset($_POST['update_id']) && isset($_POST['qty'])) {
        $id = intval($_POST['update_id']);
        $qty = max(1, intval($_POST['qty']));
        foreach ($_SESSION['cart'] as $k => $item) {
            if (isset($item['id']) && intval($item['id']) === $id) {
                $_SESSION['cart'][$k]['qty'] = $qty;
                break;
            }
        }
    }

    // Place order
    if (isset($_POST['place_order'])) {
        // sanitize incoming
        $branch_id = intval($_POST['branch_id'] ?? 0);
        $payment_method = trim($_POST['payment_method'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($branch_id <= 0 || $payment_method === '' || $address === '' || $phone === '') {
            $msg = "⚠️ Please complete all required fields before placing your order.";
        } else {
            require_login();

            $subtotal = 0;
            foreach ($cart as $c) $subtotal += ($c['price'] * $c['qty']);

            // delivery fee mapping (Rizal=25, Zulueta=60, Mabini=60)
            $delivery_fee = 0;
            $branch_name = '';
            switch ($branch_id) {
                case 1: $delivery_fee = 25; $branch_name = 'Rizal'; break;
                case 2: $delivery_fee = 60; $branch_name = 'Zulueta'; break;
                case 3: $delivery_fee = 60; $branch_name = 'Mabini'; break;
                default: $delivery_fee = 0; $branch_name = 'Unknown'; break;
            }

            $total = $subtotal + $delivery_fee;

            // insert order (store phone + address in orders so staff sees final order snapshot)
            $stmt = $pdo->prepare('INSERT INTO orders (user_id, branch_id, address, phone, total, payment_method, status, created_at)
                                   VALUES (?, ?, ?, ?, ?, ?, "Pending", NOW())');
            $stmt->execute([$user_id, $branch_id, $address, $phone, $total, $payment_method]);
            $order_id = $pdo->lastInsertId();

            // insert order items (menu_item_id = item id expected)
            $istmt = $pdo->prepare('INSERT INTO order_items (order_id, menu_item_id, qty, price)
                                    VALUES (?, ?, ?, ?)');
            foreach ($cart as $c) {
                $istmt->execute([$order_id, $c['id'], $c['qty'], $c['price']]);
            }

            // clear cart
            unset($_SESSION['cart']);
            $cart = [];
            $msg = "✅ Order placed successfully!<br>Order ID: <b>{$order_id}</b><br>Branch: <b>{$branch_name}</b><br>Total: ₱" . number_format($total, 2);
        }
    }

    // redirect to avoid resubmission and to show message cleanly
    $_SESSION['cart_msg'] = $msg;
    header("Location: cart.php");
    exit;
}

// show message (after redirect)
if (!empty($_SESSION['cart_msg'])) {
    $msg = $_SESSION['cart_msg'];
    unset($_SESSION['cart_msg']);
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Your Cart</title>
<style>
body { background:#f4f6f4; font-family:'Poppins',sans-serif; margin:0; }
main.container { max-width:920px; margin:40px auto; background:white; padding:25px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
h2 { color:#3e4425; margin-bottom:20px; }
table { width:100%; border-collapse:collapse; margin-bottom:20px; }
th,td { padding:10px; text-align:center; border-bottom:1px solid #ddd; }
th { background:#4b5320; color:white; }
.qty-control { display:flex; justify-content:center; align-items:center; gap:6px; }
.qty-control button { background:#4b5320; color:white; border:none; width:26px; height:26px; border-radius:6px; cursor:pointer; }
.qty-control input { width:45px; text-align:center; border:1px solid #ccc; border-radius:6px; padding:3px; }
.button { background:linear-gradient(90deg,#4b5320,#3e4425); color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; transition:0.2s; text-decoration:none; display:inline-block; }
.button:hover { background:linear-gradient(90deg,#3e4425,#4b5320); }
.remove-btn { background:#a23b3b; color:white; border:none; padding:6px 10px; border-radius:5px; cursor:pointer; }
.remove-btn:hover { background:#812f2f; }

/* Make select + payment side-by-side, responsive */
.form-row { display:flex; gap:12px; margin-bottom:12px; }
.form-row > div { flex:1; min-width:200px; }
/* ensure selects take full width */
.form-row select, .form-row label > select { width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; font-size:14px; }

/* address + contact row */
.address-field, .contact-field { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.address-field input, .contact-field input { flex:1; padding:10px; border-radius:6px; border:1px solid #ccc; font-size:14px; }

/* small edit button beside field */
.edit-btn { background:#4b5320; color:white; border:none; padding:8px 10px; border-radius:6px; cursor:pointer; }
.edit-btn:hover { background:#3e4425; }

/* messages */
.success { background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:15px; }
.error { background:#f8d7da; color:#721c24; padding:10px; border-radius:6px; margin-bottom:15px; }

/* table responsiveness for mobile */
@media (max-width:720px){
  th, td { font-size:14px; padding:8px; }
  .form-row { flex-direction:column; }
  .address-field, .contact-field { flex-direction:column; align-items:stretch; }
}
</style>
</head>

<body>
<?php include(__DIR__ . '/../_partials/header.php'); ?>

<main class="container">
<h2>Your Cart</h2>
<?php if($msg) echo "<div class='".(strpos($msg,'⚠️')!==false?'error':'success')."'>$msg</div>"; ?>

<?php if (empty($cart)): ?>
  <p>Your cart is empty.</p>
  <a href="/kamulan-system/buyer/menu.php" class="button">Continue Ordering</a>
<?php else: ?>
<form method="post" id="cartForm">
<table>
<tr>
  <th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th><th></th>
</tr>
<?php 
$subtotal = 0;
foreach ($cart as $c): 
  $sub = $c['price'] * $c['qty']; 
  $subtotal += $sub; 
?>
<tr data-price="<?= htmlspecialchars($c['price']) ?>">
  <td><?= htmlspecialchars($c['name']) ?></td>
  <td>
    <div class="qty-control">
      <button type="button" data-action="minus" data-id="<?= htmlspecialchars($c['id']) ?>" data-price="<?= htmlspecialchars($c['price']) ?>">−</button>
      <input type="text" value="<?= htmlspecialchars($c['qty']) ?>" readonly>
      <button type="button" data-action="plus" data-id="<?= htmlspecialchars($c['id']) ?>" data-price="<?= htmlspecialchars($c['price']) ?>">+</button>
    </div>
  </td>
  <td>₱<?= number_format($c['price'], 2) ?></td>
  <td class="subtotal">₱<?= number_format($sub, 2) ?></td>
  <td>
    <!-- remove button posts remove_id = item id -->
    <button class="remove-btn" type="submit" name="remove_id" value="<?= htmlspecialchars($c['id']) ?>">Remove</button>
  </td>
</tr>
<?php endforeach; ?>
<tr>
  <td colspan="3" style="text-align:right;"><b>Subtotal:</b></td>
  <td colspan="2"><b>₱<span id="total-amount"><?= number_format($subtotal, 2) ?></span></b></td>
</tr>
</table>

<div class="form-row">
  <div>
    <label><b>Branch:</b>
      <select name="branch_id" required>
        <option value="">Select Branch</option>
        <option value="1">Rizal</option>
        <option value="2">Zulueta</option>
        <option value="3">Mabini</option>
      </select>
    </label>
  </div>

  <div>
    <label><b>Payment:</b>
      <select name="payment_method" required>
        <option value="">Select Payment Method</option>
        <option value="COD">Cash on Delivery</option>
        <option value="GCash">GCash</option>
      </select>
    </label>
  </div>
</div>

<div class="address-field">
  <label style="min-width:100px;"><b>Address:</b></label>
  <input type="text" name="address" id="addressInput" value="<?= htmlspecialchars($user_address) ?>" required <?= $user_address ? 'readonly' : '' ?>>
  <button type="button" class="edit-btn" onclick="toggleEdit('addressInput', this)">Edit</button>
</div>

<div class="contact-field">
  <label style="min-width:100px;"><b>Contact No.:</b></label>
  <input type="text" name="phone" id="phoneInput" value="<?= htmlspecialchars($user_phone) ?>" required readonly>
  <button type="button" class="edit-btn" onclick="toggleEdit('phoneInput', this)">Edit</button>
</div>

<p><i>Delivery Fee depends on selected branch (Rizal ₱25 — Zulueta & Mabini ₱60).</i></p>

<div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:18px;">
  <a href="/kamulan-system/buyer/menu.php" class="button">Continue Ordering</a>
  <button class="button" name="place_order" type="submit">Place Order</button>
</div>
</form>
<?php endif; ?>
</main>

<script>
// Toggle inline edit for address/phone
function toggleEdit(id, btn) {
  const input = document.getElementById(id);
  input.readOnly = !input.readOnly;
  if (!input.readOnly) {
    input.focus();
    btn.textContent = 'Lock';
  } else {
    btn.textContent = 'Edit';
  }
}

// quantity control — updates UI and sends to update_cart.php (keeps original behavior)
document.addEventListener('DOMContentLoaded', () => {
  const qtyButtons = document.querySelectorAll('.qty-control button');
  qtyButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.parentElement.querySelector('input');
      const price = parseFloat(btn.dataset.price);
      const itemId = btn.dataset.id;
      const action = btn.dataset.action;
      let qty = parseInt(input.value);
      if (action === 'minus' && qty > 1) qty--;
      if (action === 'plus') qty++;
      input.value = qty;
      updateSubtotal();

      // post update to update_cart.php (non-blocking)
      fetch('update_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${encodeURIComponent(itemId)}&quantity=${encodeURIComponent(qty)}`
      }).catch(err => console.error('update_cart error', err));
    });
  });

  function updateSubtotal() {
    let total = 0;
    document.querySelectorAll('tr[data-price]').forEach(row => {
      const price = parseFloat(row.dataset.price);
      const qty = parseInt(row.querySelector('input').value);
      const subtotal = price * qty;
      row.querySelector('.subtotal').textContent = '₱' + subtotal.toFixed(2);
      total += subtotal;
    });
    document.querySelector('#total-amount').textContent = total.toFixed(2);
  }
});
</script>

<?php include(__DIR__ . '/../_partials/footer.php'); ?>
</body>
</html>
