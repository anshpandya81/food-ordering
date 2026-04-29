<?php
// ============================================================
// order-history.php — User's Order History with Tracker
// ============================================================
require_once 'includes/config.php';

if (!isLoggedIn()) redirect('login.php?redirect=order-history.php');

$pageTitle = 'My Orders';
$db = getDB();

// Fetch user's orders with item count
$stmt = $db->prepare("
    SELECT o.*,
           COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Check for new order notification
$newOrderId = isset($_GET['new_order']) ? (int)$_GET['new_order'] : 0;

include 'includes/header.php';
?>

<div class="page-header">
    <h1>📋 My Orders</h1>
    <p>Track and review all your past orders</p>
</div>

<section class="section">
    <div class="container" style="max-width:860px;">

        <?php if ($newOrderId): ?>
        <div class="alert-success mb-3">
            🎉 <b>Order #<?= $newOrderId ?> placed successfully!</b>
            Your food is being prepared. You can track it below.
        </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
        <div class="cart-empty">
            <i class="fas fa-receipt"></i>
            <h3>No orders yet</h3>
            <p>Your order history will appear here once you place your first order.</p>
            <a href="menu.php" class="btn btn-primary mt-2">
                <i class="fas fa-utensils"></i> Start Ordering
            </a>
        </div>
        <?php else: ?>

        <?php foreach ($orders as $order): ?>
        <?php
            // Get order items for this order
            $itemStmt = $db->prepare("
                SELECT oi.*, p.name AS product_name, c.icon AS cat_icon
                FROM order_items oi
                JOIN products p ON p.id = oi.product_id
                JOIN categories c ON c.id = p.category_id
                WHERE oi.order_id = ?
            ");
            $itemStmt->execute([$order['id']]);
            $orderItems = $itemStmt->fetchAll();

            // Badge class map
            $badgeMap = [
                'Pending'          => 'badge-pending',
                'Preparing'        => 'badge-preparing',
                'Out for Delivery' => 'badge-delivery',
                'Delivered'        => 'badge-delivered',
                'Cancelled'        => 'badge-cancelled'
            ];
            $badgeClass = $badgeMap[$order['status']] ?? 'badge-pending';
        ?>

        <div class="order-card <?= $order['id'] == $newOrderId ? 'new-order' : '' ?>">

            <!-- Order Header -->
            <div class="order-card-header">
                <div>
                    <div class="order-id">Order <span>#<?= $order['id'] ?></span></div>
                    <div class="order-date">
                        <i class="fas fa-clock" style="color:var(--text-muted);font-size:0.8rem;"></i>
                        <?= date('D, M j Y — g:i A', strtotime($order['created_at'])) ?>
                    </div>
                </div>
                <span class="badge <?= $badgeClass ?>"><?= e($order['status']) ?></span>
            </div>

            <!-- Order Tracker -->
            <?php if ($order['status'] !== 'Cancelled'): ?>
            <div style="padding: 20px 24px 0;">
                <div class="order-tracker" data-status="<?= e($order['status']) ?>">
                    <div class="tracker-step">
                        <div class="tracker-dot"><i class="fas fa-receipt" style="font-size:0.8rem;"></i></div>
                        <div class="tracker-label">Order<br>Placed</div>
                    </div>
                    <div class="tracker-line"></div>
                    <div class="tracker-step">
                        <div class="tracker-dot">🍳</div>
                        <div class="tracker-label">Preparing</div>
                    </div>
                    <div class="tracker-line"></div>
                    <div class="tracker-step">
                        <div class="tracker-dot">🛵</div>
                        <div class="tracker-label">On the<br>Way</div>
                    </div>
                    <div class="tracker-line"></div>
                    <div class="tracker-step">
                        <div class="tracker-dot">✅</div>
                        <div class="tracker-label">Delivered</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Order Body -->
            <div class="order-card-body">

                <!-- Items List -->
                <div class="order-items-list">
                    <?php foreach ($orderItems as $item): ?>
                    <div class="order-item-row">
                        <span>
                            <?= $item['cat_icon'] ?> <?= e($item['product_name']) ?>
                            <span style="color:var(--text-muted);"> × <?= $item['quantity'] ?></span>
                        </span>
                        <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Delivery Address -->
                <div style="font-size:0.85rem;color:var(--text-muted);margin-top:10px;">
                    <i class="fas fa-map-marker-alt" style="color:var(--primary);"></i>
                    <?= e($order['address']) ?>
                </div>

                <!-- Total -->
                <div class="order-total">
                    Total: $<?= number_format($order['total_amount'], 2) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<style>
.new-order { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(255,107,53,0.15); }
</style>

<?php include 'includes/footer.php'; ?>
