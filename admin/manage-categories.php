<?php
// ============================================================
// admin/manage-categories.php — CRUD for Categories
// ============================================================
$pageTitle = 'Categories';
require_once 'includes/admin-header.php';

$db = getDB();
$success = '';
$error   = '';
$editCat = null;

// DELETE
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    // Check if products use this category
    $count = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id=?");
    $count->execute([$delId]);
    if ($count->fetchColumn() > 0) {
        $error = 'Cannot delete: This category has food items. Remove food items first.';
    } else {
        $db->prepare("DELETE FROM categories WHERE id=?")->execute([$delId]);
        $success = 'Category deleted.';
    }
}

// EDIT — load data
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editCat = $stmt->fetch();
}

// SAVE (Add or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $icon   = trim($_POST['icon'] ?? '🍽️');
    $postId = (int)($_POST['edit_id'] ?? 0);

    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        if ($postId > 0) {
            $db->prepare("UPDATE categories SET name=?, icon=? WHERE id=?")->execute([$name, $icon, $postId]);
            $success = 'Category updated!';
            $editCat = null;
        } else {
            // Check duplicate
            $chk = $db->prepare("SELECT id FROM categories WHERE name=?");
            $chk->execute([$name]);
            if ($chk->fetch()) {
                $error = "Category '$name' already exists.";
            } else {
                $db->prepare("INSERT INTO categories (name, icon) VALUES (?,?)")->execute([$name, $icon]);
                $success = 'Category added!';
            }
        }
    }
}

// Fetch all with product counts
$categories = $db->query("
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY c.name
")->fetchAll();
?>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">

    <!-- Category Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-tags" style="color:var(--primary);"></i> All Categories (<?= count($categories) ?>)</h3>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr><th>Icon</th><th>Name</th><th>Items</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td style="font-size:1.6rem;"><?= $cat['icon'] ?></td>
                    <td><b><?= htmlspecialchars($cat['name']) ?></b></td>
                    <td>
                        <span style="background:#f3f4f6;padding:3px 10px;border-radius:50px;font-size:0.82rem;font-weight:700;">
                            <?= $cat['product_count'] ?> items
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="?edit=<?= $cat['id'] ?>" class="btn btn-warning btn-sm btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirmDelete('manage-categories.php?delete=<?= $cat['id'] ?>', '<?= htmlspecialchars(addslashes($cat['name'])) ?>')"
                                    class="btn btn-danger btn-sm btn-icon" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add / Edit Form -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-<?= $editCat ? 'edit' : 'plus' ?>" style="color:var(--primary);"></i>
                <?= $editCat ? 'Edit Category' : 'Add Category' ?>
            </h3>
            <?php if ($editCat): ?>
                <a href="manage-categories.php" class="btn btn-secondary btn-sm">Cancel Edit</a>
            <?php endif; ?>
        </div>
        <div class="admin-card-body">
            <form method="POST">
                <?php if ($editCat): ?>
                    <input type="hidden" name="edit_id" value="<?= $editCat['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($editCat['name'] ?? '') ?>"
                           placeholder="e.g. Pizza, Burgers..." required>
                </div>
                <div class="form-group">
                    <label>Emoji Icon</label>
                    <input type="text" name="icon" class="form-control"
                           value="<?= htmlspecialchars($editCat['icon'] ?? '🍽️') ?>"
                           placeholder="🍕" maxlength="5"
                           style="font-size:1.4rem;width:80px;">
                    <p class="form-hint">Paste an emoji, e.g. 🍕 🍔 🥤 🍰 🍟 🍣</p>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= $editCat ? 'Update' : 'Add Category' ?>
                </button>
            </form>
        </div>
    </div>

</div>

<?php require_once 'includes/admin-footer.php'; ?>
