<?php
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');

if (!isset($_SESSION['user'])) {
    header('Location: /kamulan-system/auth/login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

// --- filter by status if selected ---
$status_filter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : '';

$query = "
    SELECT o.*, b.name AS branch_name
    FROM orders o
    LEFT JOIN branches b ON o.branch_id = b.id
    WHERE o.user_id = ?
";
$params = [$user_id];

if ($status_filter) {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Orders - Kamulan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .container { width: 90%; margin: 30px auto; }
        h2 { color: var(--dark-brown); margin-bottom: 20px; }

        .filter-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f9f9f9;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            width: 25%;
            min-width: 220px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .filter-box label {
            font-weight: 600;
            color: #3b3b1f;
            font-size: 14px;
        }

        .filter-box select {
            flex: 1;
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 13px;
        }

        table { width:100%; border-collapse:collapse; margin-top: 15px; font-size: 14px; }
        th, td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #3b3b1f; color: #fff; }
        tr:hover { background: #f5f5f5; }
        .status { padding: 3px 7px; border-radius: 6px; font-weight: 600; font-size: 13px; }
        .pending { background:#ffeb99; color:#7a6600; }
        .preparing { background:#b3d9ff; color:#004080; }
        .on-delivery { background:#ffd480; color:#663300; }
        .completed { background:#c1f0c1; color:#006600; }
        .cancelled { background:#f0b3b3; color:#660000; }
        .empty { text-align:center; padding:40px; color:#666; font-size:14px; }
        .details { margin-top:8px; background:#fafafa; padding:8px; border-radius:8px; font-size:13px; }

        .enable-push-btn {
            margin-bottom: 6px;
            padding: 6px 14px;
            border-radius: 6px;
            border: none;
            background: #3b3b1f;
            color: #fff;
            cursor: pointer;
            font-size: 13px;
        }

        .enable-push-btn:disabled {
            opacity: 0.7;
            cursor: default;
        }

        .enable-push-hint {
            display: block;
            margin-bottom: 15px;
            color: #555;
            font-size: 12px;
        }

        button:hover { background: #5a5a30; }

        @media (max-width: 600px) {
            .container { font-size: 12px; }
            table { font-size: 12px; display: block; overflow-x: auto; }
            th, td { white-space: nowrap; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../_partials/header.php'; ?>

<div class="container">
    <h2>My Orders</h2>

    <button id="enablePushBtn" class="enable-push-btn" type="button">Enable Order Notifications</button>
    <small class="enable-push-hint">Turn this on to receive status updates for your orders.</small>

    <form method="get" class="filter-box">
        <label for="status">Filter by status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="Preparing" <?= $status_filter === 'Preparing' ? 'selected' : '' ?>>Preparing</option>
            <option value="Out for Delivery" <?= $status_filter === 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
            <option value="Delivered" <?= $status_filter === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
            <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <noscript><button type="submit">Apply</button></noscript>
    </form>

    <?php if (empty($orders)): ?>
        <div class="empty">No orders found<?= $status_filter ? " for status '$status_filter'" : '' ?>.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Branch</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['branch_name'] ?? 'N/A') ?></td>
                        <td>₱<?= number_format($order['total'], 2) ?></td>
                        <td><?= htmlspecialchars(ucfirst($order['payment_method'])) ?></td>
                        <td>
                            <span class="status <?= strtolower(str_replace(' ', '-', $order['status'])) ?>">
                                <?= htmlspecialchars(ucfirst($order['status'])) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php
                        $itemStmt = $pdo->prepare("
                            SELECT oi.*, m.name
                            FROM order_items oi
                            JOIN menu_items m ON oi.menu_item_id = m.id
                            WHERE oi.order_id = ?
                        ");
                        $itemStmt->execute([$order['id']]);
                        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <tr>
                        <td colspan="5">
                            <div class="details">
                                <?php foreach ($items as $it): ?>
                                    <div>
                                        <?= htmlspecialchars($it['name']) ?> × <?= (int)$it['qty'] ?> — ₱<?= number_format($it['price'],2) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../_partials/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('enablePushBtn');
    if (!btn) return;

    if (!window.kamulanOneSignalReady) {
        btn.disabled = true;
        btn.textContent = 'Push not available';
        return;
    }

    btn.addEventListener('click', async function () {
        btn.disabled = true;
        btn.textContent = 'Checking...';

        try {
            var OneSignal = await window.kamulanOneSignalReady;
            if (!OneSignal || !OneSignal.Notifications) {
                throw new Error('OneSignal is not ready');
            }

            var permissionState = await (OneSignal.Notifications.getPermissionState
                ? OneSignal.Notifications.getPermissionState()
                : OneSignal.Notifications.permission);

            if (normalizePermission(permissionState) === 'granted') {
                alert('Notifications are already enabled!');
                setEnabledState(btn);
                return;
            }

            var result = await OneSignal.Notifications.requestPermission();
            if (normalizePermission(result) === 'granted') {
                alert('Notifications enabled! You will receive order updates.');
                setEnabledState(btn);
            } else {
                alert('Notifications were not enabled. Please allow notifications in your browser settings.');
                resetButton(btn);
            }
        } catch (err) {
            console.error('Push enable failed:', err);
            alert('Unable to enable notifications right now.');
            resetButton(btn);
        }
    });

    function normalizePermission(value) {
        if (!value) return 'default';
        if (typeof value === 'string') {
            return value.toLowerCase();
        }
        if (typeof value === 'object') {
            if (value.state) return String(value.state).toLowerCase();
            if (value.permission) return String(value.permission).toLowerCase();
        }
        return 'default';
    }

    function setEnabledState(button) {
        button.textContent = 'Notifications Enabled';
        button.disabled = true;
    }

    function resetButton(button) {
        button.disabled = false;
        button.textContent = 'Enable Order Notifications';
    }
});

</script>

</body>
</html>
