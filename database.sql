-- ============================================================
-- FOOD ORDERING WEBSITE — Complete Database Schema
-- Run this in phpMyAdmin → SQL tab
-- ============================================================

CREATE DATABASE IF NOT EXISTS food_ordering CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE food_ordering;

-- ─────────────────────────────────────────
-- TABLE 1: users
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100)         NOT NULL,
    email       VARCHAR(100) UNIQUE  NOT NULL,
    password    VARCHAR(255)         NOT NULL,
    phone       VARCHAR(15)          DEFAULT NULL,
    address     TEXT                 DEFAULT NULL,
    role        ENUM('user','admin') DEFAULT 'user',
    created_at  TIMESTAMP            DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- TABLE 2: categories
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    icon        VARCHAR(10)  DEFAULT '🍽️',
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- TABLE 3: products
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS products (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    category_id   INT            NOT NULL,
    name          VARCHAR(150)   NOT NULL,
    description   TEXT           DEFAULT NULL,
    price         DECIMAL(10,2)  NOT NULL,
    image         VARCHAR(255)   DEFAULT 'default-food.jpg',
    is_available  TINYINT(1)     DEFAULT 1,
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- TABLE 4: orders
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS orders (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT            NOT NULL,
    total_amount  DECIMAL(10,2)  NOT NULL,
    status        ENUM('Pending','Preparing','Out for Delivery','Delivered','Cancelled') DEFAULT 'Pending',
    address       TEXT           NOT NULL,
    notes         TEXT           DEFAULT NULL,
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- TABLE 5: order_items
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT            NOT NULL,
    product_id  INT            NOT NULL,
    quantity    INT            NOT NULL DEFAULT 1,
    price       DECIMAL(10,2)  NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- SAMPLE CATEGORIES
-- ─────────────────────────────────────────
INSERT INTO categories (name, icon) VALUES
('Pizza',    '🍕'),
('Burgers',  '🍔'),
('Drinks',   '🥤'),
('Desserts', '🍰'),
('Sides',    '🍟'),
('Sushi',    '🍣');

-- ─────────────────────────────────────────
-- SAMPLE PRODUCTS
-- ─────────────────────────────────────────
INSERT INTO products (category_id, name, description, price, image) VALUES
(1, 'Margherita Pizza',    'Classic tomato base, fresh mozzarella, basil leaves',      8.99,  'margherita.jpg'),
(1, 'Pepperoni Pizza',     'Loaded with spicy pepperoni on rich tomato sauce',         10.99, 'pepperoni.jpg'),
(1, 'BBQ Chicken Pizza',   'Smoky BBQ sauce, grilled chicken, red onions',             11.99, 'bbq-chicken.jpg'),
(1, 'Veggie Supreme',      'Bell peppers, olives, mushrooms, onions',                  9.99,  'veggie.jpg'),
(2, 'Classic Burger',      'Juicy beef patty with lettuce, tomato, pickles',           6.99,  'classic-burger.jpg'),
(2, 'Double Cheese Burger','Double patty, double cheese, special sauce',               8.99,  'cheese-burger.jpg'),
(2, 'Chicken Burger',      'Crispy fried chicken with coleslaw',                       7.49,  'chicken-burger.jpg'),
(2, 'Mushroom Swiss',      'Sautéed mushrooms with melted Swiss cheese',               7.99,  'mushroom-burger.jpg'),
(3, 'Cola',                'Ice cold Coca-Cola 330ml can',                             1.99,  'cola.jpg'),
(3, 'Mango Shake',         'Fresh mango blended with chilled milk',                    3.99,  'mango-shake.jpg'),
(3, 'Lemonade',            'Freshly squeezed lemon with mint',                         2.99,  'lemonade.jpg'),
(3, 'Iced Coffee',         'Cold brew coffee with milk and ice',                       3.49,  'iced-coffee.jpg'),
(4, 'Chocolate Brownie',   'Warm fudgy brownie with vanilla ice cream',                4.99,  'brownie.jpg'),
(4, 'Cheesecake Slice',    'New York style creamy cheesecake',                         4.49,  'cheesecake.jpg'),
(5, 'French Fries',        'Golden crispy fries with seasoning',                       2.99,  'fries.jpg'),
(5, 'Onion Rings',         'Beer-battered crispy onion rings',                         3.49,  'onion-rings.jpg'),
(6, 'Salmon Roll',         '8-piece fresh salmon and avocado roll',                    12.99, 'salmon-roll.jpg'),
(6, 'Tuna Nigiri',         '4-piece fresh tuna nigiri',                                9.99,  'tuna-nigiri.jpg');

-- ─────────────────────────────────────────
-- ADMIN USER (password: admin123)
-- ─────────────────────────────────────────
INSERT INTO users (full_name, email, password, phone, role) VALUES
('Admin User', 'admin@foodie.com', '$2y$10$YourHashedPasswordHere.replaceThisWithRealHash', '0000000000', 'admin');

-- NOTE: The admin password hash above is a placeholder.
-- The install.php script will create the real admin with bcrypt hash.
