<?php
// ============================================================
// cart.php — Shopping Cart Page
// Items managed by JavaScript localStorage
// ============================================================
require_once 'includes/config.php';
$pageTitle = 'Your Cart';
include 'includes/header.php';
?>

<div class="page-header">
    <h1>🛒 Your Cart</h1>
    <p>Review your items and place your order</p>
</div>

<section class="section">
    <div class="container">
        <div class="cart-layout">

            <!-- LEFT: Cart Items (rendered by JavaScript) -->
            <div class="cart-items-section">
                <h2><i class="fas fa-shopping-basket"></i> Cart Items</h2>

                <!-- Empty state -->
                <div id="cartEmpty" class="cart-empty" style="display:none;">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p>Looks like you haven't added anything yet.</p>
                    <a href="menu.php" class="btn btn-primary mt-2">
                        <i class="fas fa-utensils"></i> Browse Menu
                    </a>
                </div>

                <!-- Cart items injected by script.js -->
                <div id="cartItemsContainer"></div>

                <!-- Clear cart button -->
                <button onclick="Cart.clear(); showToast('Cart cleared','warning');"
                        class="btn btn-secondary btn-sm mt-2"
                        id="clearCartBtn">
                    <i class="fas fa-trash"></i> Clear Cart
                </button>
            </div>

            <!-- RIGHT: Order Summary & Checkout -->
            <div id="checkoutSection">
                <div class="cart-summary-section">
                    <h2><i class="fas fa-receipt"></i> Order Summary</h2>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="summarySubtotal">$0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee</span>
                        <span id="summaryDelivery">$2.99</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (8%)</span>
                        <span id="summaryTax">$0.00</span>
                    </div>
                    <div class="summary-row total-row">
                        <span class="summary-total">Total</span>
                        <span class="summary-total" id="summaryTotal">$0.00</span>
                    </div>

                    <?php if (isLoggedIn()): ?>
                    <!-- Checkout Form (submitted via JavaScript AJAX) -->
                    <form class="checkout-form" id="checkoutForm">
                        <input type="hidden" id="grandTotal" name="total" value="0">

                        <div class="form-group mt-2">
                            <label><i class="fas fa-map-marker-alt"></i> Delivery Address *</label>
                            <textarea id="deliveryAddress" name="address" rows="3"
                                      placeholder="Enter your full delivery address..."
                                      style="resize:vertical;"
                                      required><?= e($_SESSION['address'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-sticky-note"></i> Order Notes (optional)</label>
                            <input type="text" id="orderNotes" name="notes"
                                   placeholder="e.g., No onions, extra sauce...">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block mt-2" id="placeOrderBtn">
                            <i class="fas fa-check-circle"></i> Place Order
                        </button>
                    </form>

                    <?php else: ?>
                    <!-- Not logged in -->
                    <div style="text-align:center;padding:20px 0;">
                        <p class="text-muted mb-2">Please login to place your order</p>
                        <a href="login.php?redirect=cart.php" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt"></i> Login to Checkout
                        </a>
                        <p style="margin-top:12px;font-size:0.85rem;color:var(--text-muted);">
                            Don't have an account?
                            <a href="register.php" style="color:var(--primary);font-weight:600;">Register free</a>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
