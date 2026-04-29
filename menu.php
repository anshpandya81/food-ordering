<?php
// ============================================================
// menu.php — Full Menu with Search, Filter, Categories
// ============================================================
require_once 'includes/config.php';
$pageTitle = 'Menu';

$db = getDB();

// Get categories
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Active category filter
$catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Build query
$sql = "SELECT p.*, c.name AS cat_name, c.icon AS cat_icon
        FROM products p JOIN categories c ON p.category_id = c.id
        WHERE p.is_available = 1";
$params = [];

if ($catId > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $catId;
}
if ($search !== '') {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY c.name, p.name";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1>🍽️ Our Menu</h1>
    <p>Explore <?= count($products) ?> delicious items across <?= count($categories) ?> categories</p>
</div>

<section class="section">
    <div class="container">

        <!-- Search & Sort Bar -->
        <div class="filter-bar">
            <div class="filter-search">
                <i class="fas fa-search"></i>
                <input type="text" id="menuSearch" placeholder="Search food..."
                       value="<?= e($search) ?>">
            </div>
            <select class="filter-select" id="sortSelect">
                <option value="default">Sort: Default</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
                <option value="name-asc">Name: A–Z</option>
            </select>
        </div>

        <!-- Category Tabs -->
        <div class="category-tabs">
            <button class="cat-tab <?= $catId === 0 && $search === '' ? 'active' : '' ?>"
                    data-cat-id="all"
                    onclick="filterByCategory(this,'all')">
                <span class="cat-icon">🍽️</span> All
            </button>
            <?php foreach ($categories as $cat): ?>
            <button class="cat-tab <?= $catId === (int)$cat['id'] ? 'active' : '' ?>"
                    data-cat-id="<?= $cat['id'] ?>"
                    onclick="filterByCategory(this,'<?= $cat['id'] ?>')">
                <span class="cat-icon"><?= $cat['icon'] ?></span>
                <?= e($cat['name']) ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Food Grid -->
        <div class="food-grid" id="foodGrid">
            <?php if (empty($products)): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <p>No items found. Try a different search.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $item): ?>
                <div class="food-card"
                     data-id="<?= $item['id'] ?>"
                     data-name="<?= e($item['name']) ?>"
                     data-price="<?= $item['price'] ?>"
                     data-desc="<?= e($item['description']) ?>"
                     data-cat-id="<?= $item['category_id'] ?>"
                     data-emoji="<?= $item['cat_icon'] ?>">

                    <?php if ($item['image'] && file_exists("images/food/{$item['image']}")): ?>
                        <img class="food-card-img" src="images/food/<?= e($item['image']) ?>"
                             alt="<?= e($item['name']) ?>" loading="lazy">
                    <?php else: ?>
                        <div class="food-card-img"><?= $item['cat_icon'] ?></div>
                    <?php endif; ?>

                    <div class="food-card-body">
                        <div class="food-card-category"><?= e($item['cat_name']) ?></div>
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
            <?php endif; ?>
        </div>

        <!-- No results (for live filter) -->
        <div id="noResults" class="no-results" style="display:none;">
            <i class="fas fa-search"></i>
            <p>No items match your search.</p>
            <button class="btn btn-secondary btn-sm mt-2"
                    onclick="document.getElementById('menuSearch').value=''; liveFilter();">
                Clear Search
            </button>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>
