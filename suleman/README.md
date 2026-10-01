# 💊 AFRIDI PHARMACY — Complete E-Commerce + Pharmacy Management System

A production-grade, full-stack pharmacy e-commerce and store management platform built using **PHP 8+, MySQL (MariaDB), HTML5, CSS3, JavaScript, and Bootstrap 5**.

Designed for **XAMPP local server environments**, providing a seamless experience for both online customers and PMDC-certified pharmacists managing inventory, orders, and prescription reviews.

---

## 📁 Complete Folder & File Structure

```text
c:\xampp\htdocs\suleman\
│
├── index.php                 # Home page (Hero, Categories, Featured, Discount offers, Reviews)
├── products.php              # Product catalog with search, category/brand filters & sorting
├── product-details.php       # Single product details, usage info, Rx badge & customer reviews
├── categories.php            # Categories overview grid
├── cart.php                  # Interactive cart, delivery fee calculation & Rx detection
├── cart-action.php           # AJAX backend controller for Add/Update/Remove cart
├── checkout.php              # Checkout form, prescription upload & order placement
├── login.php                 # Customer & Admin login page
├── register.php              # Customer registration page with password_hash()
├── logout.php                # Session logout handler
├── orders.php                # Customer order dashboard & delivery status
├── order-details.php         # Printable invoice & visual delivery progress timeline
├── prescriptions.php         # Standalone prescription upload & pharmacist tracker
├── profile.php               # Customer profile update & default delivery address manager
├── contact.php               # Customer inquiry form & store helpline details
├── about.php                 # Pharmacy credentials & mission statement
│
├── admin/                    # Pharmacist & Store Admin Management Portal
│   ├── index.php             # Overview dashboard (Sales, Orders, Stock Alerts, Pending Rx)
│   ├── products.php          # Product catalog CRUD table
│   ├── add-product.php       # Add product form with image upload & Rx rules
│   ├── edit-product.php      # Edit product details & stock adjustment
│   ├── categories.php        # Manage healthcare categories
│   ├── brands.php            # Manage pharmaceutical brands
│   ├── orders.php            # Order list & status update dropdowns
│   ├── order-details.php     # Admin order details & prescription inspector
│   ├── inventory.php         # Stock tracking, low stock/expiry alerts & adjustment log
│   ├── prescriptions.php     # Pharmacist prescription review & approval module
│   ├── customers.php         # Registered patient directory & order stats
│   ├── coupons.php           # Promo code manager (percent/fixed discount)
│   ├── reports.php           # Revenue analytics, top selling medicines & stock valuation
│   ├── settings.php          # Store helpline, address & delivery threshold configuration
│   └── includes/
│       ├── admin-header.php  # Admin header with auth middleware guard
│       ├── admin-sidebar.php # Admin navigation sidebar with live badge counters
│       └── admin-footer.php  # Admin footer wrapper
│
├── config/
│   └── database.php          # PDO MySQL database connection & helper functions
│
├── includes/
│   ├── auth.php              # Authentication guards & guest-to-user cart sync
│   ├── header.php            # Public HTML header, SEO meta & CSS bundles
│   ├── navbar.php            # Sticky header navbar with search & cart counter
│   └── footer.php            # Public footer, store contact & JS bundles
│
├── assets/
│   ├── css/
│   │   └── style.css         # Custom healthcare design system CSS
│   ├── js/
│   │   └── main.js           # AJAX cart, toast notifications & upload preview JS
│   └── images/
│       └── hero-banner.jpg   # High resolution hero banner
│
├── database/
│   ├── afridipharmacy.sql    # Complete SQL schema & pre-seeded demo data
│   └── create_placeholders.php # SVG vector placeholder generator for products & Rx
│
└── uploads/
    ├── products/             # Product image uploads
    └── prescriptions/        # Uploaded customer doctor prescriptions
```

---

## 🗄️ MySQL Database Schema & Setup

### Database Name
`afridipharmacy`

### Tables Included
1. `users` — Customer and Admin accounts
2. `admins` — Pharmacist administrator credentials
3. `categories` — Healthcare product categories
4. `brands` — Pharmaceutical brands and manufacturers
5. `products` — Products with price, discount, SKU, stock quantity, Rx flag & expiry date
6. `addresses` — Customer saved shipping addresses
7. `cart` — Session-based & user-linked shopping cart
8. `orders` — Customer orders with tracking numbers and statuses
9. `order_items` — Line items per order
10. `prescriptions` — Doctor prescription files uploaded for pharmacist review
11. `payments` — Transaction records
12. `reviews` — Customer product ratings & reviews
13. `coupons` — Promotional discount coupons
14. `inventory` — Stock movement logs
15. `notifications` — Admin alert notifications

---

## 🔑 Demo Account Credentials

Default password for all demo accounts: **`password123`**

### 1. Pharmacist Admin Account
- **Email:** `admin@afridipharmacy.com`
- **Password:** `password123`
- **Portal URL:** `http://localhost/suleman/admin/`

### 2. Demo Customer Account
- **Email:** `john@example.com`
- **Password:** `password123`
- **Website URL:** `http://localhost/suleman/`

---

## 🚀 How to Run on XAMPP

1. **Start XAMPP Control Panel**:
   - Start **Apache**
   - Start **MySQL**

2. **Import Database**:
   - Open phpMyAdmin in browser: `http://localhost/phpmyadmin/`
   - Create a new database named `afridipharmacy`
   - Import file: `c:\xampp\htdocs\suleman\database\afridipharmacy.sql`
   - *Alternatively via Command Prompt*:
     ```cmd
     C:\xampp\mysql\bin\mysql.exe -u root < c:\xampp\htdocs\suleman\database\afridipharmacy.sql
     ```

3. **Access the Website**:
   - Customer Frontend: `http://localhost/suleman/`
   - Admin Panel: `http://localhost/suleman/admin/`

---

## 🏷️ Test Coupons Included
- **HEALTH10**: 10% OFF on orders over PKR 1,000
- **AFRIDI500**: PKR 500 OFF on orders over PKR 3,000

---

## 🛡️ Security Implementation Checklist
- [x] Passwords hashed using `password_hash()` with BCRYPT algorithm.
- [x] Verified via `password_verify()` during login.
- [x] All database queries execute via PDO Prepared Statements (`SELECT ... WHERE id = ?`).
- [x] Output escaping with `htmlspecialchars()` to prevent XSS attacks.
- [x] Admin routes protected with `requireAdmin()` session guard.
- [x] Secure file upload validation for prescriptions and product images (extension white-listing: JPG, PNG, PDF).
- [x] Guest-to-user shopping cart migration upon customer login.

---

## 🧪 Comprehensive Testing Checklist

- [x] **Database Connectivity:** Tested PDO connection to MySQL `afridipharmacy`.
- [x] **Home & Catalog Browsing:** Category filtering, brand filtering, live search, and sorting.
- [x] **Shopping Cart:** Add to cart via AJAX, quantity modification (+/-), item deletion, subtotal & free delivery threshold logic.
- [x] **Prescription Workflow:** Prescription detection in cart, file upload step during checkout, and standalone prescription submission.
- [x] **Checkout & Order Placement:** Delivery address pre-fill, coupon code redemption, COD / payment method selection, and order creation.
- [x] **Customer Order Tracking:** Visual progress timeline (Pending -> Confirmed -> Processing -> Shipped -> Delivered) & printable invoice.
- [x] **Admin Dashboard:** Overview stat cards, low stock alerts, expiring medicines warnings, order status update dropdowns, and pharmacist prescription review approval/rejection module.
