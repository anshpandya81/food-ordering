// ============================================================
// script.js — FoodieExpress Main JavaScript
// Cart (localStorage), AJAX, Dark Mode, Search, Animations
// ============================================================

// ── CONSTANTS ──
const SITE_URL = window.location.origin + '/food-ordering';
const CART_KEY = 'foodie_cart';

// ============================================================
// CART MANAGEMENT
// ============================================================
const Cart = {
    // Get cart from localStorage (returns array)
    get() {
        try {
            return JSON.parse(localStorage.getItem(CART_KEY)) || [];
        } catch {
            return [];
        }
    },

    // Save cart to localStorage
    save(cart) {
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
        Cart.updateBadge();
        Cart.updateCartPage(); // if on cart page
    },

    // Add item to cart
    add(product) {
        const cart = Cart.get();
        const existing = cart.find(item => item.id === product.id);

        if (existing) {
            existing.qty += 1;
            showToast(`${product.name} quantity updated! 🛒`, 'success');
        } else {
            cart.push({ ...product, qty: 1 });
            showToast(`${product.name} added to cart! 🍕`, 'success');
        }

        Cart.save(cart);
        Cart.animateBadge();
    },

    // Remove item from cart
    remove(productId) {
        const cart = Cart.get().filter(item => item.id !== productId);
        Cart.save(cart);
        showToast('Item removed from cart', 'warning');
    },

    // Update item quantity
    updateQty(productId, qty) {
        const cart = Cart.get();
        const item = cart.find(i => i.id === productId);
        if (item) {
            item.qty = Math.max(1, qty);
            Cart.save(cart);
        }
    },

    // Clear entire cart
    clear() {
        localStorage.removeItem(CART_KEY);
        Cart.updateBadge();
        Cart.updateCartPage();
    },

    // Get total item count
    count() {
        return Cart.get().reduce((sum, item) => sum + item.qty, 0);
    },

    // Get total price
    total() {
        return Cart.get().reduce((sum, item) => sum + (item.price * item.qty), 0);
    },

    // Update the cart badge number in navbar
    updateBadge() {
        const badge = document.getElementById('cartBadge');
        if (badge) {
            const count = Cart.count();
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
    },

    // Animate the badge (bounce)
    animateBadge() {
        const badge = document.getElementById('cartBadge');
        if (badge) {
            badge.classList.add('bump');
            setTimeout(() => badge.classList.remove('bump'), 300);
        }
    },

    // Render the cart page items
    updateCartPage() {
        const container = document.getElementById('cartItemsContainer');
        if (!container) return; // not on cart page

        const cart = Cart.get();
        const emptyMsg = document.getElementById('cartEmpty');
        const checkoutSection = document.getElementById('checkoutSection');

        if (cart.length === 0) {
            container.innerHTML = '';
            if (emptyMsg) emptyMsg.style.display = 'block';
            if (checkoutSection) checkoutSection.style.display = 'none';
            Cart.updateSummary();
            return;
        }

        if (emptyMsg) emptyMsg.style.display = 'none';
        if (checkoutSection) checkoutSection.style.display = 'block';

        // Build cart items HTML
        container.innerHTML = cart.map(item => `
            <div class="cart-item" id="cart-item-${item.id}" data-id="${item.id}">
                <div class="cart-item-emoji">${item.emoji || '🍽️'}</div>
                <div class="cart-item-details">
                    <div class="cart-item-name">${escapeHTML(item.name)}</div>
                    <div class="cart-item-price">$${(item.price * item.qty).toFixed(2)} 
                        <small style="color:var(--text-muted);">($${parseFloat(item.price).toFixed(2)} each)</small>
                    </div>
                </div>
                <div class="qty-control">
                    <button class="qty-btn" onclick="Cart.updateQty(${item.id}, ${item.qty - 1})" 
                            ${item.qty <= 1 ? 'onclick="Cart.remove('+item.id+')"' : ''}>
                        ${item.qty <= 1 ? '<i class="fas fa-trash" style="font-size:0.7rem"></i>' : '−'}
                    </button>
                    <span class="qty-value">${item.qty}</span>
                    <button class="qty-btn" onclick="Cart.updateQty(${item.id}, ${item.qty + 1})">+</button>
                </div>
                <button class="remove-item-btn" onclick="Cart.remove(${item.id})" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');

        Cart.updateSummary();
    },

    // Update order summary totals
    updateSummary() {
        const subtotal = Cart.total();
        const delivery = subtotal > 0 ? 2.99 : 0;
        const tax = subtotal * 0.08;
        const total = subtotal + delivery + tax;

        const el = id => document.getElementById(id);
        if (el('summarySubtotal'))  el('summarySubtotal').textContent  = '$' + subtotal.toFixed(2);
        if (el('summaryDelivery'))  el('summaryDelivery').textContent  = '$' + delivery.toFixed(2);
        if (el('summaryTax'))       el('summaryTax').textContent       = '$' + tax.toFixed(2);
        if (el('summaryTotal'))     el('summaryTotal').textContent     = '$' + total.toFixed(2);
        if (el('grandTotal'))       el('grandTotal').value             = total.toFixed(2);
    }
};

// ============================================================
// ADD TO CART BUTTON HANDLER
// ============================================================
function addToCart(btn) {
    const card = btn.closest('.food-card');
    if (!card) return;

    // Extract product data from data attributes
    const product = {
        id:    parseInt(card.dataset.id),
        name:  card.dataset.name,
        price: parseFloat(card.dataset.price),
        emoji: card.dataset.emoji || '🍽️'
    };

    // Visual feedback on button
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.style.background = 'var(--success)';
    setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-plus"></i>';
        btn.style.background = '';
    }, 1000);

    Cart.add(product);

    // Flying animation
    flyToCart(btn);
}

// ── Flying cart animation ──
function flyToCart(btn) {
    const cartIcon = document.querySelector('.cart-icon-link');
    if (!cartIcon) return;

    const btnRect  = btn.getBoundingClientRect();
    const cartRect = cartIcon.getBoundingClientRect();

    const dot = document.createElement('div');
    dot.style.cssText = `
        position:fixed;
        width:16px; height:16px;
        background:var(--primary);
        border-radius:50%;
        pointer-events:none;
        z-index:9999;
        top:${btnRect.top + btnRect.height/2}px;
        left:${btnRect.left + btnRect.width/2}px;
        transition:all 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);
    `;
    document.body.appendChild(dot);

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            dot.style.top  = (cartRect.top  + cartRect.height/2) + 'px';
            dot.style.left = (cartRect.left + cartRect.width/2)  + 'px';
            dot.style.width = '6px';
            dot.style.height = '6px';
            dot.style.opacity = '0';
        });
    });
    setTimeout(() => dot.remove(), 800);
}

// ============================================================
// ORDER PLACEMENT (AJAX to PHP backend)
// ============================================================
async function placeOrder(event) {
    event.preventDefault();

    const cart = Cart.get();
    if (cart.length === 0) {
        showToast('Your cart is empty!', 'error');
        return;
    }

    const address = document.getElementById('deliveryAddress')?.value?.trim();
    const notes   = document.getElementById('orderNotes')?.value?.trim() || '';

    if (!address) {
        showToast('Please enter a delivery address', 'error');
        document.getElementById('deliveryAddress')?.focus();
        return;
    }

    const btn = document.getElementById('placeOrderBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing Order...';

    try {
        const response = await fetch(`${SITE_URL}/api/place-order.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                cart:    cart,
                address: address,
                notes:   notes,
                total:   Cart.total()
            })
        });

        const data = await response.json();

        if (data.success) {
            Cart.clear();
            showToast('Order placed successfully! 🎉', 'success');
            setTimeout(() => {
                window.location.href = `${SITE_URL}/order-history.php?new_order=${data.order_id}`;
            }, 1500);
        } else {
            showToast(data.error || 'Failed to place order', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Place Order';
        }
    } catch (err) {
        console.error('Order error:', err);
        showToast('Network error. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle"></i> Place Order';
    }
}

// ============================================================
// SEARCH & FILTER (Real-time, no page reload)
// ============================================================
let searchTimeout = null;

function initSearch() {
    // Global navbar search
    const globalSearch = document.getElementById('globalSearch');
    if (globalSearch) {
        globalSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.trim().length > 0) {
                    window.location.href = `${SITE_URL}/menu.php?q=${encodeURIComponent(this.value.trim())}`;
                }
            }, 600);
        });
    }

    // Menu page search (live filter)
    const menuSearch = document.getElementById('menuSearch');
    if (menuSearch) {
        menuSearch.addEventListener('input', liveFilter);
    }

    // Sort select
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', liveFilter);
    }
}

// Live filter food cards without page reload
function liveFilter() {
    const query  = (document.getElementById('menuSearch')?.value  || '').toLowerCase();
    const sort   = (document.getElementById('sortSelect')?.value  || 'default');
    const catId  = document.querySelector('.cat-tab.active')?.dataset?.catId || 'all';

    const cards  = Array.from(document.querySelectorAll('.food-card'));
    let visible  = 0;

    cards.forEach(card => {
        const name  = card.dataset.name?.toLowerCase()  || '';
        const desc  = card.dataset.desc?.toLowerCase()  || '';
        const cId   = card.dataset.catId || '';

        const matchQuery = !query || name.includes(query) || desc.includes(query);
        const matchCat   = catId === 'all' || cId === catId;

        if (matchQuery && matchCat) {
            card.style.display = '';
            visible++;
        } else {
            card.style.display = 'none';
        }
    });

    // Sort visible cards
    if (sort !== 'default') {
        const grid = document.getElementById('foodGrid');
        if (grid) {
            const visibleCards = cards.filter(c => c.style.display !== 'none');
            visibleCards.sort((a, b) => {
                const pa = parseFloat(a.dataset.price);
                const pb = parseFloat(b.dataset.price);
                const na = a.dataset.name;
                const nb = b.dataset.name;
                if (sort === 'price-asc')  return pa - pb;
                if (sort === 'price-desc') return pb - pa;
                if (sort === 'name-asc')   return na.localeCompare(nb);
                return 0;
            });
            visibleCards.forEach(card => grid.appendChild(card));
        }
    }

    // Show no results message
    const noResults = document.getElementById('noResults');
    if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
}

// Category tab filter
function filterByCategory(tabEl, catId) {
    // Update active tab
    document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
    tabEl.classList.add('active');

    // Update hidden catId reference
    tabEl.dataset.catId = catId;

    liveFilter();
}

// ============================================================
// DARK MODE TOGGLE
// ============================================================
function initTheme() {
    const saved = localStorage.getItem('foodie_theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    updateThemeIcon(saved);

    document.getElementById('themeToggle')?.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        const next    = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('foodie_theme', next);
        updateThemeIcon(next);
    });
}

function updateThemeIcon(theme) {
    const icon = document.querySelector('#themeToggle i');
    if (icon) {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

// ============================================================
// MOBILE HAMBURGER MENU
// ============================================================
function initMobileMenu() {
    const hamburger = document.getElementById('hamburger');
    const navLinks  = document.getElementById('navLinks');

    hamburger?.addEventListener('click', () => {
        navLinks?.classList.toggle('open');
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
        if (!hamburger?.contains(e.target) && !navLinks?.contains(e.target)) {
            navLinks?.classList.remove('open');
        }
    });
}

// ============================================================
// NAVBAR SCROLL EFFECT
// ============================================================
function initNavbarScroll() {
    window.addEventListener('scroll', () => {
        const navbar = document.getElementById('navbar');
        if (navbar) {
            navbar.style.boxShadow = window.scrollY > 20
                ? '0 4px 24px rgba(0,0,0,0.12)'
                : '0 2px 20px rgba(0,0,0,0.08)';
        }
    });
}

// ============================================================
// TOAST NOTIFICATION
// ============================================================
let toastTimer = null;

function showToast(message, type = 'default') {
    const toast = document.getElementById('toast');
    if (!toast) return;

    clearTimeout(toastTimer);

    const icons = {
        success: '✅',
        error:   '❌',
        warning: '⚠️',
        default: 'ℹ️'
    };

    toast.innerHTML = `<span>${icons[type] || icons.default}</span> ${message}`;
    toast.className = `toast show toast-${type}`;

    toastTimer = setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// ============================================================
// FORM VALIDATION
// ============================================================
function validateLoginForm(e) {
    const email    = document.getElementById('email')?.value.trim();
    const password = document.getElementById('password')?.value;
    let valid = true;

    clearErrors();

    if (!email || !isValidEmail(email)) {
        showFieldError('email', 'Please enter a valid email address');
        valid = false;
    }
    if (!password || password.length < 6) {
        showFieldError('password', 'Password must be at least 6 characters');
        valid = false;
    }

    if (!valid) e.preventDefault();
}

function validateRegisterForm(e) {
    const name     = document.getElementById('full_name')?.value.trim();
    const email    = document.getElementById('email')?.value.trim();
    const password = document.getElementById('password')?.value;
    const confirm  = document.getElementById('confirm_password')?.value;
    let valid = true;

    clearErrors();

    if (!name || name.length < 2) {
        showFieldError('full_name', 'Please enter your full name');
        valid = false;
    }
    if (!email || !isValidEmail(email)) {
        showFieldError('email', 'Please enter a valid email address');
        valid = false;
    }
    if (!password || password.length < 6) {
        showFieldError('password', 'Password must be at least 6 characters');
        valid = false;
    }
    if (password !== confirm) {
        showFieldError('confirm_password', 'Passwords do not match');
        valid = false;
    }

    if (!valid) e.preventDefault();
}

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = 'var(--danger)';
        const err = document.createElement('p');
        err.className = 'error-msg';
        err.textContent = message;
        field.parentNode.appendChild(err);
    }
}

function clearErrors() {
    document.querySelectorAll('.error-msg').forEach(e => e.remove());
    document.querySelectorAll('input').forEach(i => i.style.borderColor = '');
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ============================================================
// ANIMATE ELEMENTS ON SCROLL
// ============================================================
function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.food-card, .how-step, .order-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(el);
    });
}

// ============================================================
// UTILITY
// ============================================================
function escapeHTML(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// ============================================================
// ORDER TRACKER ANIMATION
// ============================================================
function initOrderTracker() {
    const trackers = document.querySelectorAll('.order-tracker');
    trackers.forEach(tracker => {
        const status = tracker.dataset.status;
        const statusMap = {
            'Pending':          0,
            'Preparing':        1,
            'Out for Delivery': 2,
            'Delivered':        3
        };
        const currentStep = statusMap[status] ?? 0;
        const dots  = tracker.querySelectorAll('.tracker-dot');
        const lines = tracker.querySelectorAll('.tracker-line');

        dots.forEach((dot, i) => {
            if (i < currentStep) dot.classList.add('done');
            else if (i === currentStep) dot.classList.add('active');
        });
        lines.forEach((line, i) => {
            if (i < currentStep) line.classList.add('done');
        });
    });
}

// ============================================================
// INITIALISE EVERYTHING ON DOM READY
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    // Core
    initTheme();
    initMobileMenu();
    initNavbarScroll();
    initSearch();
    initScrollAnimations();
    initOrderTracker();

    // Cart
    Cart.updateBadge();
    Cart.updateCartPage();

    // Form validators
    document.getElementById('loginForm')?.addEventListener('submit', validateLoginForm);
    document.getElementById('registerForm')?.addEventListener('submit', validateRegisterForm);

    // Checkout form
    document.getElementById('checkoutForm')?.addEventListener('submit', placeOrder);

    // Category tabs (on menu page)
    document.querySelectorAll('.cat-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            filterByCategory(this, this.dataset.catId);
        });
    });

    console.log('🍕 FoodieExpress loaded!');
});
