<?php
// ============================================================
// api/place-order.php — AJAX Order Placement Endpoint
// Called by JavaScript fetch() from cart.php
// ============================================================
require_once '../includes/config.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Must be logged in
if (!isLoggedIn()) {
    jsonResponse(['error' => 'Please login to place an order'], 401);
}

// Get JSON body
$input = json_decode(file_get_contents('php://input'), true);

$cart    = $input['cart']    ?? [];
$address = trim($input['address'] ?? '');
$notes   = trim($input['notes']   ?? '');

// Validate
if (empty($cart)) {
    jsonResponse(['error' => 'Cart is empty'], 400);
}
if (empty($address)) {
    jsonResponse(['error' => 'Delivery address is required'], 400);
}

// Validate and re-calculate total from DB (never trust client price)
$db = getDB();
$calculatedTotal = 0;
$validItems = [];

foreach ($cart as $item) {
    $productId = (int)($item['id'] ?? 0);
    $qty       = (int)($item['qty'] ?? 0);

    if ($productId <= 0 || $qty <= 0) continue;

    // Get real price from DB
    $stmt = $db->prepare("SELECT id, name, price FROM products WHERE id = ? AND is_available = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if ($product) {
        $calculatedTotal += $product['price'] * $qty;
        $validItems[] = [
            'id'    => $product['id'],
            'price' => $product['price'],
            'qty'   => $qty
        ];
    }
}

if (empty($validItems)) {
    jsonResponse(['error' => 'No valid items found'], 400);
}

// Add fees
$deliveryFee = 2.99;
$tax         = $calculatedTotal * 0.08;
$totalAmount = round($calculatedTotal + $deliveryFee + $tax, 2);

// Insert order + items in a transaction
try {
    $db->beginTransaction();

    // Insert order
    $stmt = $db->prepare("
        INSERT INTO orders (user_id, total_amount, status, address, notes)
        VALUES (?, ?, 'Pending', ?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $totalAmount, $address, $notes]);
    $orderId = $db->lastInsertId();

    // Insert order items
    $itemStmt = $db->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($validItems as $item) {
        $itemStmt->execute([$orderId, $item['id'], $item['qty'], $item['price']]);
    }

    $db->commit();

    jsonResponse([
        'success'  => true,
        'order_id' => $orderId,
        'total'    => $totalAmount,
        'message'  => 'Order placed successfully!'
    ]);

} catch (Exception $e) {
    $db->rollBack();
    jsonResponse(['error' => 'Failed to place order. Please try again.'], 500);
}
