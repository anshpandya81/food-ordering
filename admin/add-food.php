<?php
// ============================================================
// admin/add-food.php — Add New Food Item
// ============================================================
$pageTitle = 'Add Food Item';
require_once 'includes/admin-header.php';

$db = getDB();
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$success = '';
$error   = '';

// Handle editing existing item
$editItem = null;
$editId   = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
if ($editId > 0) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$editId]);
    $editItem  = $stmt->fetch();
    $pageTitle = 'Edit Food Item';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price']    ?? 0);
    $catId       = (int)($_POST['category_id'] ?? 0);
    $available   = isset($_POST['is_available']) ? 1 : 0;
    $postEditId  = (int)($_POST['edit_id'] ?? 0);

    if (empty($name) || $price <= 0 || $catId <= 0) {
        $error = 'Name, price, and category are required.';
    } else {
        // Handle image upload
        $imageName = $postEditId > 0 ? ($_POST['existing_image'] ?? 'default-food.jpg') : 'default-food.jpg';

        if (!empty($_FILES['image']['name'])) {
            $allowed  = ['jpg','jpeg','png','gif','webp'];
            $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $maxSize  = 5 * 1024 * 1024; // 5MB

            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, GIF, WEBP images allowed.';
            } elseif ($_FILES['image']['size'] > $maxSize) {
                $error = 'Image must be under 5MB.';
            } else {
                $uploadDir = dirname(__DIR__) . '/images/food/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $imageName = uniqid('food_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
            }
        }

        if (empty($error)) {
            if ($postEditId > 0) {
                // UPDATE
                $stmt = $db->prepare("
                    UPDATE products SET name=?, description=?, price=?, category_id=?, is_available=?, image=?
                    WHERE id=?
                ");
                $stmt->execute([$name, $description, $price, $catId, $available, $imageName, $postEditId]);
                $success = '✅ Food item updated successfully!';
                // Reload edit data
                $stmt = $db->prepare("SELECT * FROM products WHERE id=?");
                $stmt->execute([$postEditId]);
                $editItem = $stmt->fetch();
            } else {
                // INSERT
                $stmt = $db->prepare("
                    INSERT INTO products (name, description, price, category_id, is_available, image)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $description, $price, $catId, $available, $imageName]);
                $success = '✅ Food item added successfully!';
                // Clear form
                $editItem = null;
            }
        }
    }
}
?>

<div style="max-width:700px;">

    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-<?= $editItem ? 'edit' : 'plus-circle' ?>" style="color:var(--primary);"></i>
                <?= $editItem ? 'Edit Food Item' : 'Add New Food Item' ?>
            </h3>
            <a href="manage-food.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
        <div class="admin-card-body">

            <form method="POST" enctype="multipart/form-data">
                <?php if ($editItem): ?>
                    <input type="hidden" name="edit_id" value="<?= $editItem['id'] ?>">
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editItem['image']) ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <!-- Name -->
                    <div class="form-group">
                        <label>Food Name *</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= htmlspecialchars($editItem['name'] ?? '') ?>"
                               placeholder="e.g. Margherita Pizza" required>
                    </div>

                    <!-- Category -->
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select category...</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= (isset($editItem) && $editItem['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price -->
                    <div class="form-group">
                        <label>Price ($) *</label>
                        <input type="number" name="price" step="0.01" min="0.01" class="form-control"
                               value="<?= $editItem['price'] ?? '' ?>"
                               placeholder="0.00" required>
                    </div>

                    <!-- Availability -->
                    <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:28px;">
                        <input type="checkbox" name="is_available" id="is_available"
                               style="width:18px;height:18px;accent-color:var(--primary);"
                               <?= (!isset($editItem) || $editItem['is_available']) ? 'checked' : '' ?>>
                        <label for="is_available" style="margin:0;color:var(--text);">Available for ordering</label>
                    </div>

                    <!-- Description -->
                    <div class="form-group full">
                        <label>Description</label>
                        <textarea name="description" class="form-control"
                                  placeholder="Describe the dish — ingredients, taste, portion size..."><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Image Upload -->
                    <div class="form-group full">
                        <label>Food Image <small>(JPG/PNG/WEBP, max 5MB)</small></label>
                        <input type="file" name="image" accept="image/*" class="form-control"
                               onchange="previewImage(this,'imgPreview')">
                        <div class="image-preview" id="imgPreview">
                            <?php if (!empty($editItem['image']) && file_exists(dirname(__DIR__).'/images/food/'.$editItem['image'])): ?>
                                <img src="<?= SITE_URL ?>/images/food/<?= htmlspecialchars($editItem['image']) ?>">
                            <?php else: ?>
                                📷
                            <?php endif; ?>
                        </div>
                        <p class="form-hint">Leave empty to keep existing image. If no image, an emoji will be shown.</p>
                    </div>
                </div>

                <div style="display:flex;gap:12px;margin-top:8px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <?= $editItem ? 'Update Item' : 'Add Item' ?>
                    </button>
                    <a href="manage-food.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>

        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
