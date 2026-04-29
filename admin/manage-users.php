<?php
// ============================================================
// admin/manage-users.php — View All Users
// ============================================================
$pageTitle = 'Users';
require_once 'includes/admin-header.php';

$db = getDB();
$success = '';

// Delete user (prevent deleting self)
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId === (int)$_SESSION['user_id']) {
        $error = 'You cannot delete your own admin account!';
    } else {
        $db->prepare("DELETE FROM users WHERE id=? AND role='user'")->execute([$delId]);
        $success = 'User deleted.';
    }
}

$search = trim($_GET['q'] ?? '');
$sql = "SELECT u.*, COUNT(o.id) AS order_count FROM users u
        LEFT JOIN orders o ON o.user_id = u.id
        WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}
$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-users" style="color:var(--primary);"></i> All Users (<?= count($users) ?>)</h3>
        <form method="GET" style="display:flex;gap:8px;">
            <div style="position:relative;">
                <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.8rem;"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Search by name or email..."
                       style="padding:8px 12px 8px 32px;border:1.5px solid var(--border);border-radius:8px;font-size:0.85rem;outline:none;font-family:inherit;width:220px;">
            </div>
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
            <?php if ($search): ?>
                <a href="manage-users.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </form>
    </div>
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Orders</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:34px;height:34px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.85rem;flex-shrink:0;">
                            <?= strtoupper(substr($user['full_name'],0,1)) ?>
                        </div>
                        <b><?= htmlspecialchars($user['full_name']) ?></b>
                    </div>
                </td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['phone'] ?? '—') ?></td>
                <td>
                    <span class="badge <?= $user['role']==='admin' ? 'badge-delivery' : 'badge-active' ?>">
                        <?= ucfirst($user['role']) ?>
                    </span>
                </td>
                <td style="text-align:center;font-weight:700;"><?= $user['order_count'] ?></td>
                <td style="font-size:0.82rem;color:var(--text-muted);"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                <td>
                    <?php if ($user['role'] !== 'admin'): ?>
                    <button onclick="confirmDelete('manage-users.php?delete=<?= $user['id'] ?>', '<?= htmlspecialchars(addslashes($user['full_name'])) ?>')"
                            class="btn btn-danger btn-sm btn-icon">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php else: ?>
                    <span style="color:var(--text-muted);font-size:0.8rem;">Admin</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
