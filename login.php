<?php
// ============================================================
// login.php — User Login
// ============================================================
require_once 'includes/config.php';

// Already logged in? Redirect
if (isLoggedIn()) redirect('index.php');

$pageTitle = 'Login';
$error = '';
$success = '';

// Where to go after login
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $db = getDB();
        // Use prepared statement to prevent SQL injection
        $stmt = $db->prepare("SELECT id, full_name, password, role, address FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['address']   = $user['address'] ?? '';

            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);

            // Redirect
            if ($user['role'] === 'admin') {
                redirect('admin/index.php');
            } else {
                redirect($redirect);
            }
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">🔐</div>
            <h2>Welcome Back!</h2>
            <p>Login to your FoodieExpress account</p>
        </div>

        <div class="auth-body">
            <?php if ($error): ?>
                <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert-success"><i class="fas fa-check-circle"></i> <?= e($success) ?></div>
            <?php endif; ?>

            <form method="POST" id="loginForm" novalidate>
                <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email"
                               value="<?= e($_POST['email'] ?? '') ?>"
                               placeholder="you@example.com"
                               autocomplete="email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password"
                               autocomplete="current-password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block mt-2">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <!-- Demo credentials hint -->
            <div style="background:var(--bg-input);border-radius:8px;padding:12px;margin-top:16px;font-size:0.82rem;color:var(--text-muted);">
                <b>Demo Admin:</b> admin@foodie.com / admin123<br>
                (Register for a user account below)
            </div>
        </div>

        <div class="auth-footer">
            Don't have an account?
            <a href="register.php">Create one free</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
