<?php
// ============================================================
// register.php — User Registration
// ============================================================
require_once 'includes/config.php';

if (isLoggedIn()) redirect('index.php');

$pageTitle = 'Register';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name']        ?? '');
    $email    = trim($_POST['email']            ?? '');
    $phone    = trim($_POST['phone']            ?? '');
    $address  = trim($_POST['address']          ?? '');
    $password = trim($_POST['password']         ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');

    // Validate
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Name, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db = getDB();
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'This email is already registered. Please login.';
        } else {
            // Hash password with bcrypt
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $db->prepare("
                INSERT INTO users (full_name, email, password, phone, address)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $hashed, $phone, $address]);

            // Auto-login after registration
            $userId = $db->lastInsertId();
            $_SESSION['user_id']   = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['role']      = 'user';
            $_SESSION['address']   = $address;
            session_regenerate_id(true);

            redirect('index.php');
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card" style="max-width:500px;">
        <div class="auth-header">
            <div class="auth-icon">🍕</div>
            <h2>Create Account</h2>
            <p>Join FoodieExpress and order in minutes</p>
        </div>

        <div class="auth-body">
            <?php if ($error): ?>
                <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="registerForm" novalidate>

                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" id="full_name" name="full_name"
                               value="<?= e($_POST['full_name'] ?? '') ?>"
                               placeholder="John Doe" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email"
                               value="<?= e($_POST['email'] ?? '') ?>"
                               placeholder="you@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="phone" name="phone"
                               value="<?= e($_POST['phone'] ?? '') ?>"
                               placeholder="+1 (555) 000-0000">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Default Delivery Address</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" id="address" name="address"
                               value="<?= e($_POST['address'] ?? '') ?>"
                               placeholder="123 Main St, City, State">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password * <small>(min 6 characters)</small></label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password"
                               placeholder="Create a strong password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Repeat your password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block mt-2">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
        </div>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
