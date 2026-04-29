<?php
// ============================================================
// index.php — FoodieExpress Homepage
// ============================================================
require_once 'includes/config.php';
$pageTitle = 'Home — Order Delicious Food Online';

// Fetch featured products (latest 8 available items)
$db = getDB();
$stmt = $db->prepare("
    SELECT p.*, c.name AS category_name, c.icon AS category_icon
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_available = 1
    ORDER BY p.created_at DESC
    LIMIT 8
");
$stmt->execute();
$featured = $stmt->fetchAll();

// Fetch all categories
$cats = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Count stats
$totalProducts  = $db->query("SELECT COUNT(*) FROM products WHERE is_available=1")->fetchColumn();
$totalOrders    = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();

include 'includes/header.php';
?>

<!-- ── HERO ── -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">🔥 Free delivery on orders over $25</div>
        <h1>Delicious Food,<br><span>Delivered Fast</span></h1>
        <p>Order from the best local restaurants — fresh, hot, and right to your door in under 30 minutes.</p>
        <div class="hero-buttons">
            <a href="menu.php" class="btn btn-primary">
                <i class="fas fa-utensils"></i> Browse Menu
            </a>
            <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-outline">
                <i class="fas fa-user-plus"></i> Join Free
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ── STATS BAR ── -->
<div class="stats-bar">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item"><h3><?= $totalProducts ?>+</h3><p>Menu Items</p></div>
            <div class="stat-item"><h3><?= $totalOrders ?>+</h3><p>Orders Delivered</p></div>
            <div class="stat-item"><h3>30 min</h3><p>Avg Delivery</p></div>
            <div class="stat-item"><h3>4.9 ⭐</h3><p>Customer Rating</p></div>
        </div>
    </div>
</div>

<!-- ── CATEGORIES ── -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Browse by <span class="highlight">Category</span></h2>
            <p>Pick your favorite cuisine and explore our wide variety of options</p>
        </div>
        <div class="category-tabs" style="justify-content:center;flex-wrap:wrap;">
            <?php foreach ($cats as $cat): ?>
            <a href="menu.php?cat=<?= $cat['id'] ?>" class="cat-tab">
                <span class="cat-icon"><?= $cat['icon'] ?></span>
                <?= e($cat['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── FEATURED ITEMS ── -->
<section class="section" style="padding-top:0;">
    <div class="container">
        <div class="section-header">
            <h2>Today's <span class="highlight">Featured</span></h2>
            <p>Hand-picked favorites our customers love the most</p>
        </div>

        <div class="food-grid" id="foodGrid">
            <?php foreach ($featured as $item): ?>
            <div class="food-card"
                 data-id="<?= $item['id'] ?>"
                 data-name="<?= e($item['name']) ?>"
                 data-price="<?= $item['price'] ?>"
                 data-desc="<?= e($item['description']) ?>"
                 data-cat-id="<?= $item['category_id'] ?>"
                 data-emoji="<?= $item['category_icon'] ?>">

                <!-- Food Image / Emoji Fallback -->
                <?php if ($item['image'] && file_exists("images/food/{$item['image']}")): ?>
                    <img class="food-card-img" src="images/food/<?= e($item['image']) ?>"
                         alt="<?= e($item['name']) ?>" loading="lazy">
                <?php else: ?>
                    <div class="food-card-img"><?= $item['category_icon'] ?></div>
                <?php endif; ?>

                <div class="food-card-body">
                    <div class="food-card-category"><?= e($item['category_name']) ?></div>
                    <h3 class="food-card-name"><?= e($item['name']) ?></h3>
                    <p class="food-card-desc"><?= e($item['description']) ?></p>
                    <div class="food-card-footer">
                        <div class="food-price">$<?= number_format($item['price'], 2) ?></div>
                        <button class="add-to-cart-btn" onclick="addToCart(this)" title="Add to Cart">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;margin-top:40px;">
            <a href="menu.php" class="btn btn-primary">
                <i class="fas fa-th-large"></i> View Full Menu
            </a>
        </div>
    </div>
</section>

<!-- ── HOW IT WORKS ── -->
<section class="section" style="background:var(--bg-input);">
    <div class="container">
        <div class="section-header">
            <h2>How It <span class="highlight">Works</span></h2>
            <p>Ordering is simple, fast, and always satisfying</p>
        </div>
        <div class="how-grid">
            <div class="how-step">
                <div class="step-number">1</div>
                <div class="how-icon">🍽️</div>
                <h3>Choose Your Food</h3>
                <p>Browse our extensive menu and pick your favorites with ease</p>
            </div>
            <div class="how-step">
                <div class="step-number">2</div>
                <div class="how-icon">🛒</div>
                <h3>Add to Cart</h3>
                <p>Add items to your cart — no page reload, instant updates</p>
            </div>
            <div class="how-step">
                <div class="step-number">3</div>
                <div class="how-icon">💳</div>
                <h3>Place Order</h3>
                <p>Enter your delivery address and confirm your order</p>
            </div>
            <div class="how-step">
                <div class="step-number">4</div>
                <div class="how-icon">🚀</div>
                <h3>Fast Delivery</h3>
                <p>Track your order in real-time and enjoy your meal!</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
