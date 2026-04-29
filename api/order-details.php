<?php
// ============================================================
// api/order-details.php — Get order items for admin panel
// ============================================================
require_once '../includes/config.php';

if (!isAdmin()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    jsonResponse(['error' => 'Invalid order ID'], 400);
}

$db = getDB();

// Get order details
$stmt = $db->prepare("SELECT address, notes FROM orders WHERE id=?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    jsonResponse(['error' => 'Order not found'], 404);
}

// Get order items
$stmt = $db->prepare("
    SELECT oi.quantity, oi.price, p.name, c.icon
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN categories c ON c.id = p.category_id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

jsonResponse([
    'items'   => $items,
    'address' => $order['address'],
    'notes'   => $order['notes']
]);
