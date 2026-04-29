# 🍕 FoodieExpress — Full Stack Food Ordering Website

A complete, real-world food ordering web application built with:
- **Frontend:** HTML5, CSS3 (Flexbox/Grid, Dark Mode, Responsive), JavaScript (ES6+, LocalStorage, Fetch API)
- **Backend:** Core PHP 8 (PDO, Sessions, Password Hashing, Prepared Statements)
- **Database:** MySQL

---

## 🚀 Quick Setup (5 Minutes)

### Step 1: Install XAMPP
Download from: https://www.apachefriends.org
- Start **Apache** and **MySQL** from XAMPP Control Panel

### Step 2: Copy Project
```
Copy the `food-ordering` folder to:
Windows: C:\xampp\htdocs\food-ordering\
Mac:     /Applications/XAMPP/htdocs/food-ordering/
```

### Step 3: Run Installer
Open your browser and go to:
```
http://localhost/food-ordering/install.php
```
Fill in DB details (default: host=localhost, user=root, password=empty) and click **Install**.

### Step 4: Done! 🎉
- **Website:** http://localhost/food-ordering/
- **Admin Panel:** http://localhost/food-ordering/admin/
- **Default Admin:** admin@foodie.com / admin123

> ⚠️ **Delete install.php** after setup for security!

---

## 📁 Project Structure

```
food-ordering/
├── index.php              ← Homepage
├── menu.php               ← Full menu with search & filter
├── cart.php               ← Shopping cart
├── login.php              ← User login
├── register.php           ← User registration
├── order-history.php      ← Order history with tracker
├── logout.php             ← Session destroy
├── install.php            ← ONE-TIME installer (delete after use)
├── database.sql           ← Raw SQL for manual setup
│
├── css/
│   └── style.css          ← Complete responsive stylesheet
│
├── js/
│   └── script.js          ← Cart, AJAX, dark mode, search
│
├── images/food/           ← Uploaded food images go here
│
├── includes/
│   ├── config.php         ← DB connection, helper functions
│   ├── header.php         ← Common navbar
│   └── footer.php         ← Common footer
│
├── api/
│   ├── place-order.php    ← AJAX order placement endpoint
│   └── order-details.php  ← AJAX order details for admin
│
└── admin/
    ├── login.php           ← Admin login
    ├── index.php           ← Dashboard with stats
    ├── add-food.php        ← Add / edit food items
    ├── manage-food.php     ← List, delete, toggle food items
    ├── manage-categories.php ← Category CRUD
    ├── manage-orders.php   ← Orders with AJAX status update
    ├── manage-users.php    ← View all users
    ├── css/admin-style.css ← Admin panel styles
    └── includes/
        ├── admin-header.php ← Sidebar + header
        └── admin-footer.php ← Scripts + closing tags
```

---

## ✨ Features

### User Side
- ✅ Register & Login with bcrypt password hashing
- ✅ Browse full menu with category tabs
- ✅ Real-time search & sort (no page reload)
- ✅ Add to cart using localStorage (instant, no reload)
- ✅ Flying cart animation on add
- ✅ Update quantity / remove items from cart
- ✅ Place order via AJAX Fetch API
- ✅ Order history with visual status tracker
- ✅ Dark mode toggle (persisted in localStorage)
- ✅ Fully responsive (mobile, tablet, desktop)

### Admin Panel
- ✅ Separate admin login
- ✅ Dashboard with stats (orders, revenue, users, items)
- ✅ Add / Edit / Delete food items with image upload
- ✅ Toggle item availability
- ✅ Full Category CRUD
- ✅ View all orders, filter by status
- ✅ Update order status via AJAX (no page reload)
- ✅ Expandable order details in table
- ✅ User management

---

## 🔐 Security Features
- Password hashing with `password_hash()` (bcrypt)
- PDO prepared statements (SQL injection prevention)
- `htmlspecialchars()` on all output (XSS prevention)
- Session regeneration on login
- Admin-only routes protected by `isAdmin()` check
- File upload validation (type, size)

---

## 🎓 Viva Questions & Answers

**Q: What is PDO and why use it?**
A: PHP Data Objects — a database abstraction layer that supports prepared statements, preventing SQL injection and supporting multiple DB drivers.

**Q: How is password stored securely?**
A: Using `password_hash($pass, PASSWORD_BCRYPT)` which creates a unique bcrypt hash. Verified with `password_verify()`.

**Q: How does the cart work without a database?**
A: Cart data is stored in browser `localStorage` as JSON. JavaScript reads/writes it instantly. On order placement, data is sent to PHP via Fetch API.

**Q: What is a prepared statement?**
A: A query template with placeholders (`?`) that is compiled once. User data is bound separately, preventing SQL injection.

**Q: How does AJAX work in this project?**
A: JavaScript `fetch()` sends POST requests to PHP API files (`api/place-order.php`). PHP returns JSON. JavaScript updates the DOM without reloading the page.

**Q: What is session hijacking and how is it prevented?**
A: Attacker steals session ID to impersonate a user. Prevented by calling `session_regenerate_id(true)` after login.

**Q: How are admin pages protected?**
A: Every admin file includes `admin-header.php` which calls `isAdmin()`. If not admin, user is redirected to login.

---

## 📝 Sample Test Data
After installation, the following data is pre-loaded:
- 6 categories (Pizza, Burgers, Drinks, Desserts, Sides, Sushi)
- 18 food items with descriptions and prices
- 1 admin account (admin@foodie.com / admin123)

---

Built with ❤️ for learning full-stack PHP development.
