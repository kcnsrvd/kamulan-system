<?php
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');
require_role(['staff']);

$user = $_SESSION['user'];
$branch_id = $user['branch_id'] ?? null;
$staff_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

// Fetch branch name
$branch_stmt = $pdo->prepare("SELECT name FROM branches WHERE id = ?");
$branch_stmt->execute([$branch_id]);
$branch_name = $branch_stmt->fetchColumn() ?: 'Unknown Branch';

// Fetch orders for this branch with rider info
$sql = "SELECT o.*, 
               CONCAT(u.first_name, ' ', u.last_name) AS customer,
               u.phone, u.house_number, u.street, u.barangay, u.municipality, u.province, u.nearest_landmark,
               CONCAT(r.first_name, ' ', r.last_name) AS rider_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN users r ON o.rider_id = r.id AND r.role='rider'
        WHERE o.branch_id=?
        ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$branch_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Analytics
$active_orders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE branch_id=? AND status NOT IN ('Delivered','Cancelled')");
$active_orders->execute([$branch_id]);
$active_orders = $active_orders->fetchColumn();

$delivered_today = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE branch_id=? AND DATE(created_at)=CURDATE() AND status='Delivered'");
$delivered_today->execute([$branch_id]);
$delivered_today = $delivered_today->fetchColumn();

$total_sales_today = $pdo->prepare("SELECT SUM(total) FROM orders WHERE branch_id=? AND DATE(created_at)=CURDATE() AND status='Delivered'");
$total_sales_today->execute([$branch_id]);
$total_sales_today = $total_sales_today->fetchColumn() ?: 0;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Staff Dashboard</title>
<style>
body{font-family:'Poppins',sans-serif;background:#f5f5f0;margin:0;}
.container{width:90%;margin:20px auto;background:#fff;padding:20px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.1);}
h2{color:#3e4425;}
.stats{display:flex;gap:15px;flex-wrap:wrap;margin-bottom:25px;}
.stat-card{flex:1;min-width:200px;background:#4b5320;color:#fff;padding:15px;border-radius:10px;text-align:center;}
.orders-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:15px;}
.card{border:1px solid #ddd;border-radius:8px;padding:15px;background:#f9faf5;box-shadow:0 2px 5px rgba(0,0,0,0.1);}
.card strong{color:#3e4425;}
.card-status{display:inline-block;padding:4px 8px;border-radius:6px;font-size:13px;font-weight:bold;margin-top:6px;}
.Preparing{background:#fff3cd;color:#856404;}
.OutForDelivery{background:#cce5ff;color:#004085;}
.Delivered{background:#d4edda;color:#155724;}
.Cancelled{background:#f8d7da;color:#721c24;}
select,button{padding:4px 6px;border-radius:6px;margin-top:6px;border:1px solid #ccc;}
button{background:#3e4425;color:#fff;border:none;cursor:pointer;}
button:hover{background:#4b5320;}
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:1000;}
.modal-content{background:white;padding:20px;border-radius:10px;width:90%;max-width:600px;max-height:80vh;overflow-y:auto;}
.modal-close{float:right;cursor:pointer;color:#333;font-weight:bold;}
.receipt{font-family:monospace;font-size:13px;width:280px;margin:0 auto;background:#fff;padding:10px;border:1px dashed #333;}
.receipt h3{text-align:center;margin:5px 0;}
.receipt table{width:100%;border-collapse:collapse;margin-top:5px;}
.receipt td{padding:2px 0;}
.receipt .total{text-align:right;font-weight:bold;border-top:1px dashed #000;padding-top:4px;}
@media print{
  body *{visibility:hidden;}
  #printArea, #printArea *{visibility:visible;}
  #printArea{position:absolute;left:0;top:0;width:80mm;}
}
</style>
</head>
<body>
<?php include __DIR__.'/../_partials/header_staff.php'; ?>

<main class="container">
<h2>Welcome, <?= htmlspecialchars($staff_name ?: 'Staff') ?> 👋</h2>

<div class="stats">
  <div class="stat-card"><h3>Active Orders</h3><p><?= $active_orders ?></p></div>
  <div class="stat-card"><h3>Delivered Today</h3><p><?= $delivered_today ?></p></div>
  <div class="stat-card"><h3>Total Sales Today (₱)</h3><p><?= number_format($total_sales_today, 2) ?></p></div>
</div>

<h2>Orders for Your Branch</h2>
<?php if(!empty($orders)): ?>
<div class="orders-grid">
<?php foreach($orders as $o): ?>
<div class="card" data-order-id="<?= $o['id'] ?>">
  <strong>Order #<?= $o['id'] ?></strong><br>
  <?= htmlspecialchars($o['customer'] ?? 'Guest') ?><br>
  <span class="card-status <?= str_replace(' ','',$o['status']) ?>">
      <?= htmlspecialchars($o['status']) ?>
  </span>

  <form class="status-form" data-order-id="<?= $o['id'] ?>" onsubmit="return updateStatus(event, <?= $o['id'] ?>)">
      <select name="status">
          <option value="Preparing" <?= $o['status']=='Preparing'?'selected':'' ?>>Preparing</option>
          <option value="Out for Delivery" <?= $o['status']=='Out for Delivery'?'selected':'' ?>>Out for Delivery</option>
          <option value="Cancelled" <?= $o['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
      </select>
      <button type="submit">Save</button>
      <span class="status-msg"></span>
  </form>

  <div style="margin-top:6px;">
      <button type="button" onclick="openModal(<?= $o['id'] ?>)">View Details</button>
      <button type="button" onclick="printReceipt(<?= $o['id'] ?>)">Print Receipt</button>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p>No orders found for this branch.</p>
<?php endif; ?>
</main>

<!-- Modal -->
<div class="modal" id="orderModal">
  <div class="modal-content">
    <span class="modal-close" onclick="closeModal()">&times;</span>
    <div id="modalDetails"><p>Loading...</p></div>
  </div>
</div>

<!-- Hidden Print Area -->
<div id="printArea" style="display:none;"></div>

<script>
// View Details modal
function openModal(orderId){
  const modal=document.getElementById('orderModal');
  const content=document.getElementById('modalDetails');
  modal.style.display='flex';
  content.innerHTML='<p>Loading...</p>';
  fetch('order_modal.php?id='+orderId)
  .then(r=>r.text()).then(html=>content.innerHTML=html)
  .catch(()=>content.innerHTML='<p>Error loading details.</p>');
}
function closeModal(){document.getElementById('orderModal').style.display='none';}
window.onclick=e=>{if(e.target==document.getElementById('orderModal'))closeModal();}

// Update order status
function updateStatus(e,id){
  e.preventDefault();
  const form=document.querySelector(`.status-form[data-order-id='${id}']`);
  const select=form.querySelector('select');
  const msg=form.querySelector('.status-msg');
  msg.textContent='Saving...';
  fetch('update_status.php',{
    method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`order_id=${id}&status=${encodeURIComponent(select.value)}`
  }).then(r=>r.json()).then(res=>{
    if(res.success){
      msg.textContent='';
      const lbl=form.closest('.card').querySelector('.card-status');
      lbl.textContent=select.value;
      lbl.className='card-status '+select.value.replace(/\s+/g,'');
    }else{msg.textContent='Failed';msg.style.color='crimson';}
  }).catch(()=>{msg.textContent='Error';msg.style.color='crimson';});
  return false;
}

// Print Receipt
function printReceipt(orderId) {
  const url = 'order_receipt.php?id=' + orderId;

  // Open small popup for 80mm thermal print
  const printWindow = window.open(url, 'printWindow', 'width=380,height=600,scrollbars=no');
  
  // Ensure it auto prints and closes when done
  printWindow.onload = function() {
    printWindow.focus();
    printWindow.print();
    setTimeout(() => printWindow.close(), 1000);
  };
}

</script>

<?php include(__DIR__.'/../_partials/footer.php'); ?>
</body>
</html>
