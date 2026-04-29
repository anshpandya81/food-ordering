<?php
// ============================================================
// admin/manage-food.php — List, Edit, Delete Food Items
// ============================================================
$pageTitle = 'Food Items';
require_once 'includes/admin-header.php';

$db = getDB();
$success = '';

// Handle delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    // Get image filename first
    $stmt = $db->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$delId]);
    $item = $stmt->fetch();

    // Delete image file if it exists
    if ($item && $item['image'] && $item['image'] !== 'default-food.jpg') {
        $imgPath = dirname(__DIR__) . '/images/food/' . $item['image'];
        if (file_exists($imgPath)) unlink($imgPath);
    }

    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$delId]);
    $success = 'Food item deleted successfully.';
}

// Handle availability toggle
if (isset($_GET['toggle'])) {
    $toggleId = (int)$_GET['toggle'];
    $db->prepare("UPDATE products SET is_available = NOT is_available WHERE id = ?")->execute([$toggleId]);
    $success = 'Availability updated.';
}

// Search
$search = trim($_GET['q'] ?? '');
$catFilter = (int)($_GET['cat'] ?? 0);

$sql = "SELECT p.*, c.name AS cat_name, c.icon AS cat_icon
        FROM products p JOIN categories c ON c.id = p.category_id WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($catFilter) { $sql .= " AND p.category_id = ?"; $params[] = $catFilter; }
$sql .= " ORDER BY c.name, p.name";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-utensils" style="color:var(--primary);"></i> All Food Items (<?= count($products) ?>)</h3>
        <a href="add-food.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add New Item</a>
    </div>

    <!-- Filters -->
    <div style="padding:16px 24px;border-bottom:1px solid var(--border);display:flex;gap:12px;flex-wrap:wrap;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;flex:1;">
            <div style="position:relative;flex:1;min-width:180px;">
                <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9ca3af;"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Search food..."
                       style="width:100%;padding:9px 14px 9px 36px;border:1.5px solid var(--border);border-radius:8px;font-size:0.9rem;outline:none;font-family:inherit;">
            </div>
            <select name="cat" style="padding:9px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:0.9rem;outline:none;font-family:inherit;">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $catFilter == $cat['id'] ? 'selected' : '' ?>>
                    <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <?php if ($search || $catFilter): ?>
                <a href="manage-food.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-utensils"></i>
                            <p>No food items found.</p>
                            <a href="add-food.php" class="btn btn-primary btn-sm" style="margin-top:12px;">Add Your First Item</a>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $item): ?>
                <tr>
                    <td>
                        <div class="food-thumb">
                            <?php if ($item['image'] && file_exists(dirname(__DIR__).'/images/food/'.$item['image'])): ?>
                                <img src="<?= SITE_URL ?>/images/food/<?= htmlspecialchars($item['image']) ?>"
                                     style="width:50px;height:50px;object-fit:cover;border-radius:10px;">
                            <?php else: ?>
                                <?= $item['cat_icon'] ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($item['name']) ?></div>
                        <div style="font-size:0.78rem;color:var(--text-muted);max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?= htmlspecialchars($item['description']) ?>
                        </div>
                    </td>
                    <td><?= $item['cat_icon'] ?> <?= htmlspecialchars($item['cat_name']) ?></td>
                    <td><b>$<?= number_format($item['price'], 2) ?></b></td>
                    <td>
                        <a href="?toggle=<?= $item['id'] ?><?= $search ? '&q='.urlencode($search) : '' ?>"
                           class="badge <?= $item['is_available'] ? 'badge-active' : 'badge-inactive' ?>"
                           title="Click to toggle">
                            <?= $item['is_available'] ? '✅ Available' : '❌ Hidden' ?>
                        </a>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="add-food.php?edit=<?= $item['id'] ?>" class="btn btn-warning btn-sm btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirmDelete('manage-food.php?delete=<?= $item['id'] ?>', '<?= htmlspecialchars(addslashes($item['name'])) ?>')"
                                    class="btn btn-danger btn-sm btn-icon" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
