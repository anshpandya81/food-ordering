<?php
// ============================================================
// admin/index.php — Admin Dashboard
// ============================================================
$pageTitle = 'Dashboard';
require_once 'includes/admin-header.php';

$db = getDB();

// ── Stats ──
$totalProducts  = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders    = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalUsers     = $db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalRevenue   = $db->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status='Delivered'")->fetchColumn();
$pendingOrders  = $db->query("SELECT COUNT(*) FROM orders WHERE status='Pending'")->fetchColumn();
$todayOrders    = $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn();

// ── Recent Orders ──
$recentOrders = $db->query("
    SELECT o.*, u.full_name AS customer
    FROM orders o
    JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
    LIMIT 8
")->fetchAll();

// ── Popular Items ──
$popularItems = $db->query("
    SELECT p.name, p.price, c.icon, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN categories c ON c.id = p.category_id
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 5
")->fetchAll();
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#fff3ee;color:#ff6b35;">🍕</div>
        <div class="stat-info">
            <h3><?= $totalProducts ?></h3>
            <p>Food Items</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#ecfdf5;color:#10b981;">📋</div>
        <div class="stat-info">
            <h3><?= $totalOrders ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;color:#3b82f6;">👥</div>
        <div class="stat-info">
            <h3><?= $totalUsers ?></h3>
            <p>Customers</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fffbeb;color:#f59e0b;">💰</div>
        <div class="stat-info">
            <h3>$<?= number_format($totalRevenue, 0) ?></h3>
            <p>Revenue</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7;color:#d97706;">⏳</div>
        <div class="stat-info">
            <h3><?= $pendingOrders ?></h3>
            <p>Pending Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f3e8ff;color:#7c3aed;">📅</div>
        <div class="stat-info">
            <h3><?= $todayOrders ?></h3>
            <p>Today's Orders</p>
        </div>
    </div>
</div>

<!-- Two Column Layout -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">

    <!-- Recent Orders Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-receipt" style="color:var(--primary);"></i> Recent Orders</h3>
            <a href="manage-orders.php" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px;">No orders yet</td></tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $order):
                        $badgeMap = [
                            'Pending'          => 'badge-pending',
                            'Preparing'        => 'badge-preparing',
                            'Out for Delivery' => 'badge-delivery',
                            'Delivered'        => 'badge-delivered',
                            'Cancelled'        => 'badge-cancelled'
                        ];
                        $cls = $badgeMap[$order['status']] ?? 'badge-pending';
                    ?>
                    <tr>
                        <td><b>#<?= $order['id'] ?></b></td>
                        <td><?= htmlspecialchars($order['customer']) ?></td>
                        <td><b>$<?= number_format($order['total_amount'], 2) ?></b></td>
                        <td><span class="badge <?= $cls ?>"><?= $order['status'] ?></span></td>
                        <td style="color:var(--text-muted);font-size:0.82rem;"><?= date('M j, g:i A', strtotime($order['created_at'])) ?></td>
                        <td><a href="manage-orders.php?id=<?= $order['id'] ?>" class="btn btn-info btn-sm btn-icon"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Popular Items -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-fire" style="color:#ff6b35;"></i> Top Items</h3>
        </div>
        <div class="admin-card-body">
            <?php if (empty($popularItems)): ?>
                <p style="color:var(--text-muted);text-align:center;padding:20px;">No order data yet</p>
            <?php else: ?>
                <?php foreach ($popularItems as $i => $item): ?>
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
                    <div style="font-size:1.6rem;width:36px;text-align:center;"><?= $item['icon'] ?></div>
                    <div style="flex:1;">
                        <div style="font-weight:600;font-size:0.9rem;"><?= htmlspecialchars($item['name']) ?></div>
                        <div style="color:var(--text-muted);font-size:0.8rem;">$<?= number_format($item['price'],2) ?></div>
                    </div>
                    <div style="background:#f3f4f6;padding:4px 10px;border-radius:50px;font-size:0.8rem;font-weight:700;">
                        <?= $item['total_sold'] ?> sold
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once 'includes/admin-footer.php'; ?>
