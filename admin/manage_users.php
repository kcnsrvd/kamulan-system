<?php
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');
require_role(['manager']);

// --- FILTER USERS ---
$filter = isset($_GET['role']) && $_GET['role'] !== 'all' ? $_GET['role'] : null;
if ($filter) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ? ORDER BY id DESC");
    $stmt->execute([$filter]);
    $users = $stmt->fetchAll();
} else {
    $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
}

// --- DELETE USER ---
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: manage_users.php');
    exit();
}

// --- UPDATE USER ---
if (isset($_POST['update'])) {
    $branch_id = null;
    if (in_array($_POST['role'], ['rider', 'staff']) && !empty($_POST['branch_id'])) {
        $branch_id = $_POST['branch_id'];
    }

    $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, role=?, branch_id=? WHERE id=?");
    $stmt->execute([
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['role'],
        $branch_id,
        $_POST['id']
    ]);
    header('Location: manage_users.php');
    exit();
}


// --- ADD USER ---
if (isset($_POST['add'])) {
    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Determine branch_id if role is rider or staff
    $branch_id = null;
    if (in_array($_POST['role'], ['rider', 'staff']) && !empty($_POST['branch_id'])) {
        $branch_id = $_POST['branch_id'];
    }

    $stmt = $pdo->prepare("INSERT INTO users 
        (first_name, last_name, email, phone, password, role, branch_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['phone'],
        $hashed,
        $_POST['role'],
        $branch_id
    ]);

    header('Location: manage_users.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users - Kamulan Kitchenette</title>
<link rel="icon" type="image/png" href="/kamulan-system/assets/images/kamulan-logo.jpg">
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: #f6f4f0;
    margin: 0;
  }
  .container {
    width: 90%;
    margin: 30px auto;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  h2 {
    color: #4a3b20;
    text-align: center;
    margin-bottom: 15px;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
  }
  th, td {
    border-bottom: 1px solid #ddd;
    padding: 10px;
    text-align: center;
  }
  th {
    background: #4b5320;
    color: white;
  }
  tr:hover {
    background: #f9f5ef;
  }
  .btn {
    padding: 6px 12px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
    margin: 0 3px;
    display: inline-block;
  }
  .btn-edit { background: #3e4425; }
  .btn-delete { background: #a23b3b; }
  .btn-add {
    background: #4b5320;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    color: #fff;
    cursor: pointer;
  }
  .btn-add:hover { background: #3e4425; }
  .top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 10px;
  }
  .filter {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  select {
    padding: 6px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
  }
  .modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.4);
    justify-content: center;
    align-items: center;
  }
  .modal-content {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    width: 400px;
  }
  .modal-content input, .modal-content select {
    width: 100%;
    margin: 6px 0;
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
  }
  .back-btn {
    text-decoration: none;
    color: #4b5320;
    font-size: 16px;
    display: inline-flex;
    align-items: center;
    margin-bottom: 15px;
  }
  .back-btn svg {
    width: 22px;
    height: 22px;
    margin-right: 6px;
    stroke-width: 2;
  }
</style>
</head>
<body>
<?php include __DIR__ . '/../_partials/header_staff.php'; ?>

<div class="container">
  <!-- Back button -->
  <a href="../admin/dashboard.php" class="back-btn">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
    Back
  </a>

  <div class="top-bar">
    <h2>Manage Users</h2>

    <div class="filter">
      <form method="GET" id="filterForm">
        <select name="role" onchange="document.getElementById('filterForm').submit()">
          <option value="all" <?= !$filter ? 'selected' : '' ?>>All</option>
          <option value="buyer" <?= $filter === 'buyer' ? 'selected' : '' ?>>Buyers</option>
          <option value="rider" <?= $filter === 'rider' ? 'selected' : '' ?>>Riders</option>
          <option value="staff" <?= $filter === 'staff' ? 'selected' : '' ?>>Staff</option>
          <option value="manager" <?= $filter === 'manager' ? 'selected' : '' ?>>Managers</option>
        </select>
      </form>
      <button class="btn-add" onclick="openAdd()">+ Add User</button>
    </div>
  </div>

  <table>
    <tr>
      <th>ID</th>
      <th>Full Name</th>
      <th>Email</th>
      <th>Role</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($users as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= ucfirst($u['role']) ?></td>
      <td>
        <a href="#" class="btn btn-edit" onclick='openEdit(<?= json_encode($u) ?>)'>Edit</a>
        <a href="?delete=<?= $u['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this user?')">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
  <div class="modal-content">
    <h3>Add User</h3>
    <form method="POST">
      <input type="text" name="first_name" placeholder="First Name" required>
      <input type="text" name="last_name" placeholder="Last Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="text" name="phone" placeholder="Phone Number" required>
      <input type="password" name="password" placeholder="Password" required>
      <select name="role" id="addRole" required onchange="toggleBranchSelect('add')">
        <option value="buyer">Buyer</option>
        <option value="rider">Rider</option>
        <option value="staff">Staff</option>
        <option value="manager">Manager</option>
      </select>

      <!-- Branch selection for staff/rider -->
      <select name="branch_id" id="addBranch" style="display:none">
        <option value="">Select Branch</option>
        <?php
        $branches = $pdo->query("SELECT id, name FROM branches")->fetchAll();
        foreach ($branches as $b) {
            echo "<option value='{$b['id']}'>{$b['name']}</option>";
        }
        ?>
      </select>

      <br>
      <button type="submit" name="add" class="btn-add">Add</button>
      <button type="button" class="btn-delete" onclick="closeModal('addModal')">Cancel</button>
    </form>
  </div>
</div>


<!-- EDIT MODAL -->
<div class="modal" id="editModal">
  <div class="modal-content">
    <h3>Edit User</h3>
    <form method="POST">
      <input type="hidden" name="id" id="editId">
      <input type="text" name="first_name" id="editFirstName" placeholder="First Name" required>
      <input type="text" name="last_name" id="editLastName" placeholder="Last Name" required>
      <input type="email" name="email" id="editEmail" placeholder="Email" required>
      <input type="text" name="phone" id="editPhone" placeholder="Phone Number" required>
      <select name="role" id="editRole" required onchange="toggleBranchSelect('edit')">
        <option value="buyer">Buyer</option>
        <option value="rider">Rider</option>
        <option value="staff">Staff</option>
        <option value="manager">Manager</option>
      </select>

      <!-- Branch selection for staff/rider -->
      <select name="branch_id" id="editBranch" style="display:none">
        <option value="">Select Branch</option>
        <?php
        foreach ($branches as $b) {
            echo "<option value='{$b['id']}'>{$b['name']}</option>";
        }
        ?>
      </select>

      <br>
      <button type="submit" name="update" class="btn-edit">Save</button>
      <button type="button" class="btn-delete" onclick="closeModal('editModal')">Cancel</button>
    </form>
  </div>
</div>

<script>
function openAdd() {
  document.getElementById('addModal').style.display = 'flex';
}
function openEdit(user) {
  document.getElementById('editModal').style.display = 'flex';
  document.getElementById('editId').value = user.id;
  document.getElementById('editFirstName').value = user.first_name;
  document.getElementById('editLastName').value = user.last_name;
  document.getElementById('editEmail').value = user.email;
  document.getElementById('editRole').value = user.role;
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}


function toggleBranchSelect(prefix) {
    const roleSelect = document.getElementById(prefix + 'Role');
    const branchSelect = document.getElementById(prefix + 'Branch');
    if (roleSelect.value === 'staff' || roleSelect.value === 'rider') {
        branchSelect.style.display = 'block';
    } else {
        branchSelect.style.display = 'none';
        branchSelect.value = '';
    }
}

// Pre-fill branch when editing
function openEdit(user) {
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('editId').value = user.id;
    document.getElementById('editFirstName').value = user.first_name;
    document.getElementById('editLastName').value = user.last_name;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editPhone').value = user.phone;
    document.getElementById('editRole').value = user.role;
    toggleBranchSelect('edit');
    if (user.branch_id) {
        document.getElementById('editBranch').value = user.branch_id;
    }
}


</script>
</body>
</html>
