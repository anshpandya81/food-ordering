<?php
// ============================================================
// install.php — One-click database installer
// Visit: http://localhost/food-ordering/install.php ONCE
// DELETE this file after setup for security!
// ============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FoodieExpress — Install</title>
<style>
  body { font-family: Arial; background: #1a1a2e; color: #eee; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; }
  .box { background:#16213e; padding:40px; border-radius:16px; max-width:600px; width:90%; }
  h1 { color:#ff6b35; margin-top:0; }
  .step { background:#0f3460; padding:12px 16px; border-radius:8px; margin:8px 0; }
  .ok   { color:#4ade80; } .err { color:#f87171; }
  button { background:#ff6b35; color:#fff; border:none; padding:12px 28px; border-radius:8px; font-size:16px; cursor:pointer; margin-top:16px; width:100%; }
  button:hover { background:#e55a2b; }
  input { width:100%; padding:10px; border-radius:8px; border:1px solid #444; background:#0f3460; color:#fff; box-sizing:border-box; margin-top:6px; }
  label { font-size:14px; color:#aaa; }
</style>
</head>
<body>
<div class="box">
  <h1>🍕 FoodieExpress Installer</h1>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host     = $_POST['host']     ?? 'localhost';
    $dbuser   = $_POST['dbuser']   ?? 'root';
    $dbpass   = $_POST['dbpass']   ?? '';
    $dbname   = $_POST['dbname']   ?? 'food_ordering';
    $adminEmail = trim($_POST['admin_email'] ?? 'admin@foodie.com');
    $adminPass  = trim($_POST['admin_pass']  ?? 'admin123');
    $adminName  = trim($_POST['admin_name']  ?? 'Admin');

    $errors = [];

    try {
        // Connect without selecting DB first
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $dbuser, $dbpass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        echo '<div class="step"><span class="ok">✓</span> Connected to MySQL</div>';

        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");
        echo '<div class="step"><span class="ok">✓</span> Database created/selected: <b>'.$dbname.'</b></div>';

        // Create tables
        $tables = [
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                phone VARCHAR(15) DEFAULT NULL,
                address TEXT DEFAULT NULL,
                role ENUM('user','admin') DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB",

            "CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                icon VARCHAR(10) DEFAULT '🍽️',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB",

            "CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                category_id INT NOT NULL,
                name VARCHAR(150) NOT NULL,
                description TEXT DEFAULT NULL,
                price DECIMAL(10,2) NOT NULL,
                image VARCHAR(255) DEFAULT 'default-food.jpg',
                is_available TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB",

            "CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                total_amount DECIMAL(10,2) NOT NULL,
                status ENUM('Pending','Preparing','Out for Delivery','Delivered','Cancelled') DEFAULT 'Pending',
                address TEXT NOT NULL,
                notes TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB",

            "CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                price DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB"
        ];

        foreach ($tables as $sql) {
            $pdo->exec($sql);
        }
        echo '<div class="step"><span class="ok">✓</span> All 5 tables created</div>';

        // Insert categories
        $cats = [['Pizza','🍕'],['Burgers','🍔'],['Drinks','🥤'],['Desserts','🍰'],['Sides','🍟'],['Sushi','🍣']];
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, icon) VALUES (?,?)");
        foreach ($cats as $c) $stmt->execute($c);
        echo '<div class="step"><span class="ok">✓</span> Sample categories inserted</div>';

        // Insert sample products
        $products = [
            [1,'Margherita Pizza','Classic tomato base, fresh mozzarella, basil',8.99,'margherita.jpg'],
            [1,'Pepperoni Pizza','Loaded with spicy pepperoni on rich tomato sauce',10.99,'pepperoni.jpg'],
            [1,'BBQ Chicken Pizza','Smoky BBQ sauce, grilled chicken, red onions',11.99,'bbq-chicken.jpg'],
            [1,'Veggie Supreme','Bell peppers, olives, mushrooms, onions',9.99,'veggie.jpg'],
            [2,'Classic Burger','Juicy beef patty with lettuce, tomato, pickles',6.99,'classic-burger.jpg'],
            [2,'Double Cheese Burger','Double patty, double cheese, special sauce',8.99,'cheese-burger.jpg'],
            [2,'Chicken Burger','Crispy fried chicken with coleslaw',7.49,'chicken-burger.jpg'],
            [2,'Mushroom Swiss','Sautéed mushrooms with melted Swiss cheese',7.99,'mushroom-burger.jpg'],
            [3,'Cola','Ice cold Coca-Cola 330ml',1.99,'cola.jpg'],
            [3,'Mango Shake','Fresh mango blended with chilled milk',3.99,'mango-shake.jpg'],
            [3,'Lemonade','Freshly squeezed lemon with mint',2.99,'lemonade.jpg'],
            [3,'Iced Coffee','Cold brew coffee with milk and ice',3.49,'iced-coffee.jpg'],
            [4,'Chocolate Brownie','Warm fudgy brownie with vanilla ice cream',4.99,'brownie.jpg'],
            [4,'Cheesecake Slice','New York style creamy cheesecake',4.49,'cheesecake.jpg'],
            [5,'French Fries','Golden crispy fries with seasoning',2.99,'fries.jpg'],
            [5,'Onion Rings','Beer-battered crispy onion rings',3.49,'onion-rings.jpg'],
            [6,'Salmon Roll','8-piece fresh salmon and avocado roll',12.99,'salmon-roll.jpg'],
            [6,'Tuna Nigiri','4-piece fresh tuna nigiri',9.99,'tuna-nigiri.jpg'],
        ];
        $stmt = $pdo->prepare("INSERT IGNORE INTO products (category_id,name,description,price,image) VALUES (?,?,?,?,?)");
        foreach ($products as $p) $stmt->execute($p);
        echo '<div class="step"><span class="ok">✓</span> '.count($products).' sample food items inserted</div>';

        // Create admin user
        $hash = password_hash($adminPass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?,?,?,'admin') ON DUPLICATE KEY UPDATE password=?");
        $stmt->execute([$adminName, $adminEmail, $hash, $hash]);
        echo '<div class="step"><span class="ok">✓</span> Admin account created — Email: <b>'.$adminEmail.'</b> | Pass: <b>'.$adminPass.'</b></div>';

        // Update config.php
        $configContent = '<?php
define(\'DB_HOST\', \''.$host.'\');
define(\'DB_USER\', \''.$dbuser.'\');
define(\'DB_PASS\', \''.$dbpass.'\');
define(\'DB_NAME\', \''.$dbname.'\');
define(\'SITE_URL\', \'http://localhost/food-ordering\');
define(\'SITE_NAME\', \'FoodieExpress\');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode([\'error\' => \'Database connection failed\']));
        }
    }
    return $pdo;
}

if (session_status() === PHP_SESSION_NONE) { session_start(); }

function isLoggedIn()  { return isset($_SESSION[\'user_id\']); }
function isAdmin()     { return isset($_SESSION[\'user_id\']) && isset($_SESSION[\'role\']) && $_SESSION[\'role\'] === \'admin\'; }
function redirect($url){ header("Location: $url"); exit(); }
function e($str)       { return htmlspecialchars($str, ENT_QUOTES, \'UTF-8\'); }
function jsonResponse($data, $code=200){ http_response_code($code); header(\'Content-Type: application/json\'); echo json_encode($data); exit(); }
?>';
        file_put_contents(__DIR__.'/includes/config.php', $configContent);
        echo '<div class="step"><span class="ok">✓</span> config.php updated with your DB settings</div>';

        echo '<div style="background:#166534;padding:16px;border-radius:8px;margin-top:16px;">
            <b>🎉 Installation Complete!</b><br><br>
            👉 <a href="index.php" style="color:#4ade80;">Go to Homepage</a><br>
            👉 <a href="admin/index.php" style="color:#4ade80;">Go to Admin Panel</a><br><br>
            ⚠️ <b>DELETE install.php now for security!</b>
        </div>';

    } catch (PDOException $e) {
        echo '<div class="step"><span class="err">✗ Error: '.htmlspecialchars($e->getMessage()).'</span></div>';
    }

} else {
?>
  <p style="color:#aaa;">This will create the database, tables, sample data, and admin account automatically.</p>
  <div>
    <label>MySQL Host</label>
    <form method="POST">
    <input type="text" name="host" value="localhost">

    <label style="margin-top:12px;display:block;">MySQL Username</label>
    <input type="text" name="dbuser" value="root">

    <label style="margin-top:12px;display:block;">MySQL Password</label>
    <input type="password" name="dbpass" placeholder="Leave empty for XAMPP default">

    <label style="margin-top:12px;display:block;">Database Name</label>
    <input type="text" name="dbname" value="food_ordering">

    <hr style="border-color:#333;margin:20px 0;">
    <b>Admin Account</b>

    <label style="margin-top:12px;display:block;">Admin Name</label>
    <input type="text" name="admin_name" value="Admin">

    <label style="margin-top:12px;display:block;">Admin Email</label>
    <input type="email" name="admin_email" value="admin@foodie.com">

    <label style="margin-top:12px;display:block;">Admin Password</label>
    <input type="text" name="admin_pass" value="admin123">

    <button type="submit">🚀 Install FoodieExpress</button>
    </form>
  </div>
<?php } ?>
</div>
</body>
</html>
