<?php
// ============================================================
// admin/login.php — Admin Login Page
// ============================================================
require_once '../includes/config.php';

if (isAdmin()) redirect(SITE_URL . '/admin/index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, full_name, password, role FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role']      = 'admin';
            session_regenerate_id(true);
            redirect(SITE_URL . '/admin/index.php');
        } else {
            $error = 'Invalid admin credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — FoodieExpress</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,0.4);
        }
        .login-header {
            background: linear-gradient(135deg, #ff6b35, #e55a2b);
            padding: 40px;
            text-align: center;
            color: #fff;
        }
        .login-header .icon { font-size: 3rem; margin-bottom: 12px; }
        .login-header h2 { font-family: 'Playfair Display', serif; font-size: 1.8rem; }
        .login-header p  { opacity: 0.85; margin-top: 6px; font-size: 0.9rem; }
        .login-body { padding: 36px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #6b7280; margin-bottom: 7px; }
        .input-wrap { position: relative; }
        .input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
        .input-wrap input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
        }
        .input-wrap input:focus { border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
        .btn {
            width: 100%;
            padding: 13px;
            background: #ff6b35;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s;
            margin-top: 8px;
        }
        .btn:hover { background: #e55a2b; }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 3px solid #ef4444;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 0.9rem;
        }
        .login-footer { padding: 18px 36px; border-top: 1px solid #f3f4f6; text-align: center; font-size: 0.85rem; color: #9ca3af; }
        .login-footer a { color: #ff6b35; font-weight: 600; }
        .hint { background: #f0f9ff; border-radius: 8px; padding: 10px 14px; margin-top: 14px; font-size: 0.8rem; color: #6b7280; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-header">
        <div class="icon">🛡️</div>
        <h2>Admin Login</h2>
        <p>FoodieExpress Control Panel</p>
    </div>
    <div class="login-body">
        <?php if ($error): ?>
            <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Admin Email</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="admin@foodie.com" required autocomplete="email">
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Enter admin password" required>
                </div>
            </div>
            <button type="submit"><i class="fas fa-shield-alt"></i> Login to Admin Panel</button>
        </form>

        <div class="hint">
            <b>Default:</b> admin@foodie.com / admin123<br>
            (Set up via install.php)
        </div>
    </div>
    <div class="login-footer">
        <a href="<?= SITE_URL ?>/index.php"><i class="fas fa-arrow-left"></i> Back to Website</a>
    </div>
</div>
</body>
</html>
