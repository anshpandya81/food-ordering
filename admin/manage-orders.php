<?php
// ============================================================
// admin/manage-orders.php — View & Update All Orders
// ============================================================
$pageTitle = 'Manage Orders';
require_once 'includes/admin-header.php';

$db = getDB();
$success = '';

// Update order status via AJAX POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $allowedStatuses = ['Pending','Preparing','Out for Delivery','Delivered','Cancelled'];
    $newStatus = $_POST['status'];
    $orderId   = (int)$_POST['order_id'];

    if (in_array($newStatus, $allowedStatuses)) {
        $stmt = $db->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->execute([$newStatus, $orderId]);

        // If AJAX request, return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit();
        }
        $success = "Order #$orderId status updated to '$newStatus'.";
    }
}

// Filter
$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['q'] ?? '');

$sql = "SELECT o.*, u.full_name AS customer, u.email AS customer_email,
               COUNT(oi.id) AS item_count
        FROM orders o
        JOIN users u ON u.id = o.user_id
        LEFT JOIN order_items oi ON oi.order_id = o.id
        WHERE 1=1";
$params = [];

if ($statusFilter) { $sql .= " AND o.status = ?";      $params[] = $statusFilter; }
if ($search)       { $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR o.id LIKE ?)";
                     $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }

$sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$statusOptions = ['Pending','Preparing','Out for Delivery','Delivered','Cancelled'];
$badgeMap = [
    'Pending'          => 'badge-pending',
    'Preparing'        => 'badge-preparing',
    'Out for Delivery' => 'badge-delivery',
    'Delivered'        => 'badge-delivered',
    'Cancelled'        => 'badge-cancelled'
];

// Counts for filter tabs
$counts = [];
foreach ($statusOptions as $s) {
    $c = $db->prepare("SELECT COUNT(*) FROM orders WHERE status=?");
    $c->execute([$s]);
    $counts[$s] = $c->fetchColumn();
}
$counts['All'] = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
?>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Status Filter Tabs -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
    <a href="manage-orders.php" class="btn <?= !$statusFilter ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
        All <span style="background:rgba(255,255,255,0.3);padding:1px 7px;border-radius:50px;margin-left:4px;"><?= $counts['All'] ?></span>
    </a>
    <?php foreach ($statusOptions as $s): ?>
    <a href="?status=<?= urlencode($s) ?>" class="btn <?= $statusFilter === $s ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
        <?= $s ?>
        <span style="background:rgba(255,255,255,0.3);padding:1px 7px;border-radius:50px;margin-left:4px;"><?= $counts[$s] ?></span>
    </a>
    <?php endforeach; ?>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-receipt" style="color:var(--primary);"></i>
            <?= $statusFilter ? $statusFilter : 'All' ?> Orders (<?= count($orders) ?>)
        </h3>
        <!-- Search -->
        <form method="GET" style="display:flex;gap:8px;">
            <?php if ($statusFilter): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <?php endif; ?>
            <div style="position:relative;">
                <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.8rem;"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Search customer..."
                       style="padding:8px 12px 8px 32px;border:1.5px solid var(--border);border-radius:8px;font-size:0.85rem;outline:none;font-family:inherit;width:200px;">
            </div>
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="7">
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <p>No orders found.</p>
                    </div>
                </td></tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr id="order-row-<?= $order['id'] ?>">
                    <td><b>#<?= $order['id'] ?></b></td>
                    <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($order['customer']) ?></div>
                        <div style="font-size:0.78rem;color:var(--text-muted);"><?= htmlspecialchars($order['customer_email']) ?></div>
                    </td>
                    <td>
                        <button onclick="toggleOrderDetails(<?= $order['id'] ?>)"
                                class="btn btn-secondary btn-sm">
                            <?= $order['item_count'] ?> item(s) <i class="fas fa-chevron-down"></i>
                        </button>
                    </td>
                    <td><b>$<?= number_format($order['total_amount'], 2) ?></b></td>
                    <td style="font-size:0.82rem;color:var(--text-muted);">
                        <?= date('M j Y', strtotime($order['created_at'])) ?><br>
                        <?= date('g:i A', strtotime($order['created_at'])) ?>
                    </td>
                    <td>
                        <span class="badge <?= $badgeMap[$order['status']] ?? '' ?>"
                              id="badge-<?= $order['id'] ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </td>
                    <td>
                        <select class="status-select" id="status-select-<?= $order['id'] ?>"
                                onchange="updateOrderStatus(<?= $order['id'] ?>, this.value)">
                            <?php foreach ($statusOptions as $s): ?>
                            <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <!-- Order Details (hidden, toggleable) -->
                <tr id="details-<?= $order['id'] ?>" style="display:none;">
                    <td colspan="7" style="background:#f9fafb;padding:0;">
                        <div style="padding:16px 24px;" id="details-body-<?= $order['id'] ?>">
                            <i class="fas fa-spinner fa-spin"></i> Loading...
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Update order status via AJAX (no page reload)
function updateOrderStatus(orderId, newStatus) {
    fetch('manage-orders.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `order_id=${orderId}&status=${encodeURIComponent(newStatus)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update badge without page reload
            const badge = document.getElementById('badge-' + orderId);
            if (badge) {
                const map = {
                    'Pending':          'badge-pending',
                    'Preparing':        'badge-preparing',
                    'Out for Delivery': 'badge-delivery',
                    'Delivered':        'badge-delivered',
                    'Cancelled':        'badge-cancelled'
                };
                badge.className = 'badge ' + (map[newStatus] || 'badge-pending');
                badge.textContent = newStatus;
            }
            // Flash row green briefly
            const row = document.getElementById('order-row-' + orderId);
            if (row) {
                row.style.background = '#d1fae5';
                setTimeout(() => row.style.background = '', 1500);
            }
        }
    })
    .catch(() => alert('Failed to update status. Please try again.'));
}

// Toggle order item details
function toggleOrderDetails(orderId) {
    const row  = document.getElementById('details-' + orderId);
    const body = document.getElementById('details-body-' + orderId);

    if (row.style.display === 'none') {
        row.style.display = '';
        // Fetch order items via AJAX
        fetch(`<?= SITE_URL ?>/api/order-details.php?id=${orderId}`)
        .then(r => r.json())
        .then(data => {
            if (data.items && data.items.length > 0) {
                let html = '<table style="width:100%;border-collapse:collapse;">';
                html += '<tr style="font-size:0.8rem;color:var(--text-muted);"><th style="text-align:left;padding:4px 8px;">Item</th><th style="padding:4px 8px;">Qty</th><th style="padding:4px 8px;">Price</th><th style="padding:4px 8px;">Subtotal</th></tr>';
                data.items.forEach(item => {
                    html += `<tr style="border-top:1px solid #e5e7eb;">
                        <td style="padding:6px 8px;">${item.icon} ${item.name}</td>
                        <td style="padding:6px 8px;text-align:center;">${item.quantity}</td>
                        <td style="padding:6px 8px;text-align:center;">$${parseFloat(item.price).toFixed(2)}</td>
                        <td style="padding:6px 8px;text-align:center;font-weight:700;">$${(item.price * item.quantity).toFixed(2)}</td>
                    </tr>`;
                });
                html += '</table>';
                html += `<div style="margin-top:10px;font-size:0.85rem;color:#6b7280;"><i class="fas fa-map-marker-alt" style="color:#ff6b35;"></i> ${data.address}</div>`;
                if (data.notes) html += `<div style="margin-top:6px;font-size:0.82rem;color:#6b7280;"><i class="fas fa-sticky-note"></i> ${data.notes}</div>`;
                body.innerHTML = html;
            } else {
                body.innerHTML = '<p style="color:#9ca3af;">No items found.</p>';
            }
        });
    } else {
        row.style.display = 'none';
    }
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>
