<?php  
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
$cart = &$_SESSION['cart']; // reference
$msg = "";

// --- Fetch user address & phone ---
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

// --- Show message ---
if (!empty($_SESSION['cart_msg'])) { 
    $msg=$_SESSION['cart_msg']; 
    unset($_SESSION['cart_msg']); 
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Your Cart</title>
<link rel="stylesheet" href="/kamulan-system/assets/css/style.css">
<style>
body { background:#f4f6f4; font-family:'Poppins',sans-serif; margin:0; }
main.container { max-width:920px; margin:40px auto; background:white; padding:25px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
h2 { color:#3e4425; margin-bottom:20px; }
table { width:100%; border-collapse:collapse; margin-bottom:20px; }
th,td { padding:10px; text-align:center; border-bottom:1px solid #ddd; }
th { background:#4b5320; color:white; }
.qty-control { display:flex; justify-content:center; align-items:center; gap:6px; }
.qty-control button { background:#4b5320; color:white; border:none; width:26px; height:26px; border-radius:6px; cursor:pointer; }
.qty-control button:disabled { opacity:0.5; cursor:not-allowed; }
.qty-control input { width:45px; text-align:center; border:1px solid #ccc; border-radius:6px; padding:3px; }
.button { background:linear-gradient(90deg,#4b5320,#3e4425); color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; transition:0.2s; text-decoration:none; display:inline-block; }
.button:hover { background:linear-gradient(90deg,#3e4425,#4b5320); }
.remove-btn { background:#a23b3b; color:white; border:none; padding:6px 10px; border-radius:5px; cursor:pointer; }
.remove-btn:hover { background:#812f2f; }
.success { background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:15px; }
.error { background:#f8d7da; color:#721c24; padding:10px; border-radius:6px; margin-bottom:15px; }
.address-field, .contact-field, .note-field { margin-bottom:15px; }
.edit-btn { font-size:0.9em; margin-left:8px; text-decoration:none; color:#4b5320; }
.edit-btn:hover { text-decoration:underline; }
</style>
</head>
<body>
<?php include(__DIR__ . '/../_partials/header.php'); ?>

<main class="container">
<h2>Your Cart</h2>
<?php if($msg) echo "<div class='".(strpos($msg,'⚠️')!==false?'error':'success')."'>$msg</div>"; ?>

<?php if(empty($cart)): ?>
  <p>Your cart is empty.</p>
  <a href="/kamulan-system/buyer/menu.php" class="button">Continue Ordering</a>
<?php else: ?>
<table>
<tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th><th></th></tr>
<?php 
$subtotal = 0;
foreach ($cart as $c): 
    $addons_total = array_sum(array_column($c['addons'] ?? [], 'price'));
    $sub = ($c['price'] + $addons_total) * $c['qty'];
    $subtotal += $sub;
?>
<tr data-id="<?= htmlspecialchars($c['id']) ?>" data-price="<?= htmlspecialchars($c['price']) ?>">
  <td><?= htmlspecialchars($c['name']) ?>
      <?php if(!empty($c['addons'])): ?>
          <br><small style="color:#666;">
              <?php foreach($c['addons'] as $ad): ?>
                  + <?= htmlspecialchars($ad['name']) ?> (₱<?= number_format($ad['price'],2) ?>)<br>
              <?php endforeach; ?>
          </small>
      <?php endif; ?>
  </td>
  <td>
    <div class="qty-control">
      <button type="button" data-action="minus">−</button>
      <input type="text" value="<?= $c['qty'] ?>" readonly>
      <button type="button" data-action="plus">+</button>
    </div>
  </td>
  <td>₱<?= number_format($c['price'] + $addons_total, 2) ?></td>
  <td class="subtotal">₱<?= number_format($sub,2) ?></td>
  <td>
    <button class="remove-btn">Remove</button>
  </td>
</tr>
<?php endforeach; ?>
<tr>
  <td colspan="3" style="text-align:right;"><b>Subtotal:</b></td>
  <td colspan="2"><b>₱<span id="total-amount"><?= number_format($subtotal,2) ?></span></b></td>
</tr>
</table>

<div class="form-row">
  <div>
    <label><b>Branch:</b>
      <select name="branch_id" id="branch_id" required>
        <option value="">Select Branch</option>
        <option value="1">Rizal</option>
        <option value="2">Zulueta</option>
        <option value="3">Mabini</option>
      </select>
    </label>
  </div>

  <div>
    <label><b>Payment:</b>
      <select name="payment_method" id="payment_method" required>
        <option value="">Select Payment Method</option>
        <option value="COD">Cash on Delivery</option>
        <option value="GCash">GCash</option>
      </select>
    </label>
  </div>
</div>

<div class="address-field">
  <label><b>Address:</b></label>
  <input type="text" value="<?= htmlspecialchars($user_address) ?>" readonly>
  <a href="/kamulan-system/buyer/edit_address.php" class="edit-btn">Edit</a>
</div>

<div class="contact-field">
  <label><b>Contact:</b></label>
  <input type="text" value="<?= htmlspecialchars($user_phone) ?>" readonly>
</div>

<div class="note-field">
  <label><b>Note:</b></label>
  <textarea id="note" placeholder="Add a note..." rows="3" style="width:100%; padding:8px; border-radius:6px; border:1px solid #ccc;"></textarea>
</div>

<div style="display:flex; justify-content:space-between; margin-top:18px;">
  <a href="/kamulan-system/buyer/menu.php" class="button">Continue Ordering</a>
  <button class="button" id="placeOrderBtn">Place Order</button>
</div>
<?php endif; ?>
</main>

<script>
// --- Utility functions ---
function updateSubtotal() {
    let total = 0;
    document.querySelectorAll('tr[data-id]').forEach(row=>{
        const qty = parseInt(row.querySelector('input').value);
        const price = parseFloat(row.dataset.price);
        const addonTotal = Array.from(row.querySelectorAll('small')).reduce((sum, el)=>{
            const lines = el.textContent.split('\n');
            lines.forEach(l=>{
                const match = l.match(/₱([\d,.]+)/);
                if(match) sum += parseFloat(match[1].replace(',',''));
            });
            return sum;
        },0);
        const subtotal = (price + addonTotal)*qty;
        row.querySelector('.subtotal').textContent = '₱'+subtotal.toFixed(2);
        total += subtotal;
    });
    document.querySelector('#total-amount').textContent = total.toFixed(2);
}

function updateFloatingCart() {
    fetch('/kamulan-system/buyer/cart_add.php')
    .then(res=>res.text())
    .then(html=>{
        const floatingCart = document.querySelector('#floating-cart');
        if(floatingCart) floatingCart.innerHTML = html;
    });
}

// --- Event listeners ---
document.addEventListener('DOMContentLoaded', ()=>{
    document.querySelectorAll('.qty-control').forEach(qc=>{
        const input = qc.querySelector('input');
        const minus = qc.querySelector('button[data-action="minus"]');
        const plus = qc.querySelector('button[data-action="plus"]');
        function updateMinusBtn() {
            if(parseInt(input.value)<=1){ minus.disabled=true; } else { minus.disabled=false; }
        }
        updateMinusBtn();

        minus.addEventListener('click', ()=>{
            let val = parseInt(input.value);
            if(val>1) val--;
            input.value = val;
            updateMinusBtn();
            updateSubtotal();
            const id = qc.closest('tr').dataset.id;
            fetch('/kamulan-system/buyer/cart_add.php',{
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'update_id='+encodeURIComponent(id)+'&qty='+encodeURIComponent(val)
            }).then(()=>updateFloatingCart());
        });

        plus.addEventListener('click', ()=>{
            let val = parseInt(input.value);
            val++;
            input.value = val;
            updateMinusBtn();
            updateSubtotal();
            const id = qc.closest('tr').dataset.id;
            fetch('/kamulan-system/buyer/cart_add.php',{
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'update_id='+encodeURIComponent(id)+'&qty='+encodeURIComponent(val)
            }).then(()=>updateFloatingCart());
        });
    });

    document.querySelectorAll('.remove-btn').forEach(btn=>{
        btn.addEventListener('click', ()=>{
            const row = btn.closest('tr');
            const id = row.dataset.id;
            fetch('/kamulan-system/buyer/cart_add.php',{
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'remove_id='+encodeURIComponent(id)
            }).then(()=>{
                row.remove();
                updateSubtotal();
                updateFloatingCart();
            });
        });
    });

    document.querySelector('#placeOrderBtn')?.addEventListener('click', ()=>{
        const branch = document.querySelector('#branch_id').value;
        const payment = document.querySelector('#payment_method').value;
        const note = document.querySelector('#note').value;

        const formData = new URLSearchParams();
        formData.append('place_order','1');
        formData.append('branch_id', branch);
        formData.append('payment_method', payment);
        formData.append('note', note);

        fetch('/kamulan-system/buyer/cart_add.php',{method:'POST', body:formData})
        .then(()=>location.reload());
    });
});
</script>
<?php include(__DIR__ . '/../_partials/footer.php'); ?>
</body>
</html>
