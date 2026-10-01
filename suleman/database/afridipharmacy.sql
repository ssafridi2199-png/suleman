-- ========================================================
-- AFRIDI PHARMACY Complete Database Schema & Seed Data
-- Database: afridipharmacy
-- Compatible with PHP 8+ and MySQL / MariaDB (XAMPP)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `afridipharmacy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `afridipharmacy`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `inventory`;
DROP TABLE IF EXISTS `coupons`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `prescriptions`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `addresses`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `brands`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS TABLE
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(30) NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ADMINS TABLE
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `role_title` VARCHAR(100) DEFAULT 'Pharmacist Admin',
  `department` VARCHAR(100) DEFAULT 'Pharmacy Operations',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CATEGORIES TABLE
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `icon_class` VARCHAR(50) DEFAULT 'bi-capsule',
  `image` VARCHAR(255) NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BRANDS TABLE
CREATE TABLE `brands` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `logo` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. PRODUCTS TABLE
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `brand_id` INT NULL,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `sku` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NOT NULL,
  `usage_info` TEXT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `discount_price` DECIMAL(10,2) NULL,
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `requires_prescription` TINYINT(1) NOT NULL DEFAULT 0,
  `image` VARCHAR(255) NULL,
  `expiry_date` DATE NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE SET NULL,
  INDEX `idx_category` (`category_id`),
  INDEX `idx_brand` (`brand_id`),
  INDEX `idx_featured` (`is_featured`),
  INDEX `idx_sku` (`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ADDRESSES TABLE
CREATE TABLE `addresses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `full_name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `street_address` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `postal_code` VARCHAR(20) NOT NULL,
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. CART TABLE
CREATE TABLE `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `session_id` VARCHAR(100) NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_cart` (`user_id`),
  INDEX `idx_session_cart` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. ORDERS TABLE
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `customer_name` VARCHAR(120) NOT NULL,
  `customer_phone` VARCHAR(30) NOT NULL,
  `customer_email` VARCHAR(150) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `postal_code` VARCHAR(20) NULL,
  `order_notes` TEXT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `delivery_fee` DECIMAL(10,2) NOT NULL DEFAULT 150.00,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('cod', 'card', 'easypaisa', 'jazzcash') DEFAULT 'cod',
  `payment_status` ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
  `order_status` ENUM('Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
  `has_prescription` TINYINT(1) DEFAULT 0,
  `tracking_number` VARCHAR(100) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
  INDEX `idx_order_user` (`user_id`),
  INDEX `idx_order_status` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. ORDER ITEMS TABLE
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. PRESCRIPTIONS TABLE
CREATE TABLE `prescriptions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `order_id` INT NULL,
  `prescription_file` VARCHAR(255) NOT NULL,
  `notes` TEXT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  `pharmacist_notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. PAYMENTS TABLE
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `transaction_id` VARCHAR(100) NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending',
  `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. REVIEWS TABLE
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `rating` INT NOT NULL DEFAULT 5,
  `review_text` TEXT NOT NULL,
  `status` ENUM('approved', 'pending') DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. COUPONS TABLE
CREATE TABLE `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_type` ENUM('fixed', 'percent') NOT NULL DEFAULT 'percent',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_order_amount` DECIMAL(10,2) DEFAULT 0.00,
  `expiry_date` DATE NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. INVENTORY TABLE
CREATE TABLE `inventory` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `transaction_type` ENUM('in', 'out', 'adjustment') NOT NULL,
  `quantity` INT NOT NULL,
  `notes` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. NOTIFICATIONS TABLE
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `type` VARCHAR(50) DEFAULT 'info',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- SEED DATA
-- Default password for all seed accounts: password123
-- ========================================================

-- Insert Users (Admin & Customer)
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`, `status`) VALUES
(1, 'Pharmacist Admin', 'admin@afridipharmacy.com', '+923001234567', '$2y$10$yMDitV1G83Ct/AaLVIfl0upFj3aCatyMDTe4c14DRmjOCWbWEeKsq', 'admin', 'active'),
(2, 'John Doe', 'john@example.com', '+923129876543', '$2y$10$yMDitV1G83Ct/AaLVIfl0upFj3aCatyMDTe4c14DRmjOCWbWEeKsq', 'customer', 'active');

INSERT INTO `admins` (`id`, `user_id`, `role_title`, `department`) VALUES
(1, 1, 'Chief Pharmacist', 'Pharmacy Operations & Quality Assurance');

-- Insert Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon_class`, `image`) VALUES
(1, 'Medicines', 'medicines', 'Prescription and over-the-counter pharmaceuticals.', 'bi-capsule', 'cat-medicines.jpg'),
(2, 'Vitamins & Supplements', 'vitamins-supplements', 'Essential vitamins, minerals, and daily health supplements.', 'bi-heart-pulse', 'cat-vitamins.jpg'),
(3, 'Baby Care', 'baby-care', 'Infant formula, diapers, wipes, and skincare for toddlers.', 'bi-emoji-smile', 'cat-baby.jpg'),
(4, 'Personal Care', 'personal-care', 'Hygiene, soaps, shampoos, and body care items.', 'bi-droplet-half', 'cat-personal.jpg'),
(5, 'Skin Care', 'skin-care', 'Dermatological creams, cleansers, and sun protection.', 'bi-flower1', 'cat-skin.jpg'),
(6, 'First Aid', 'first-aid', 'Bandages, antiseptics, wound care, and emergency kits.', 'bi-bandaid', 'cat-firstaid.jpg'),
(7, 'Medical Equipment', 'medical-equipment', 'Blood pressure monitors, thermometers, and glucometers.', 'bi-activity', 'cat-equipment.jpg'),
(8, 'Diabetes Care', 'diabetes-care', 'Insulin supplies, testing strips, and diabetic care.', 'bi-shield-plus', 'cat-diabetes.jpg'),
(9, 'Dental Care', 'dental-care', 'Toothpaste, toothbrushes, mouthwash, and dental hygiene.', 'bi-shield-check', 'cat-dental.jpg');

-- Insert Brands
INSERT INTO `brands` (`id`, `name`, `slug`, `logo`) VALUES
(1, 'Panadol', 'panadol', 'brand-panadol.png'),
(2, 'GSK', 'gsk', 'brand-gsk.png'),
(3, 'Pfizer', 'pfizer', 'brand-pfizer.png'),
(4, 'Abbott', 'abbott', 'brand-abbott.png'),
(5, 'Johnson & Johnson', 'johnson-johnson', 'brand-jnj.png'),
(6, 'Dettol', 'dettol', 'brand-dettol.png'),
(7, 'OneTouch', 'onetouch', 'brand-onetouch.png'),
(8, 'Omron', 'omron', 'brand-omron.png'),
(9, 'Sensodyne', 'sensodyne', 'brand-sensodyne.png');

-- Insert Products
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `description`, `usage_info`, `price`, `discount_price`, `stock_quantity`, `requires_prescription`, `image`, `expiry_date`, `is_featured`, `status`) VALUES
(1, 1, 1, 'Panadol Extra 500mg Tablets (20s)', 'panadol-extra-500mg', 'MED-PAN-001', 'Panadol Extra provides fast, effective relief from tough pain including headache, backache, and toothache. Formulated with paracetamol and caffeine for enhanced absorption.', 'Take 1 to 2 tablets every 4 to 6 hours as required. Do not exceed 8 tablets in 24 hours.', 180.00, 160.00, 150, 0, 'panadol-extra.jpg', '2027-12-31', 1, 'active'),
(2, 1, 3, 'Amoxicillin 500mg Capsules (10s)', 'amoxicillin-500mg', 'MED-AMX-002', 'Broad-spectrum antibiotic used to treat various bacterial infections of the respiratory tract, ear, nose, throat, and urinary tract. Requires a valid doctor prescription.', 'As directed by a certified physician. Complete the full course of treatment.', 350.00, NULL, 45, 1, 'amoxicillin.jpg', '2026-11-30', 1, 'active'),
(3, 1, 3, 'Lipitor 20mg Tablets (30s)', 'lipitor-20mg', 'MED-LIP-003', 'Lipitor (Atorvastatin) lowers cholesterol and triglyceride levels in the blood, reducing the risk of heart attack and stroke. Prescription required.', 'Take once daily at the same time, with or without food, as prescribed by your cardiologist.', 1250.00, 1150.00, 28, 1, 'lipitor.jpg', '2027-08-15', 1, 'active'),
(4, 2, 2, 'Centrum Adults Multivitamin (60 Tablets)', 'centrum-adults-multivitamin', 'VIT-CEN-001', 'Complete daily multivitamin and mineral supplement with key antioxidants, B-vitamins, Vitamin D3, and Zinc to support energy, immunity, and overall health.', 'Adults take one tablet daily with food.', 2400.00, 2199.00, 60, 0, 'centrum-adults.jpg', '2028-01-30', 1, 'active'),
(5, 2, 2, 'Vitamin C 1000mg Effervescent (20s)', 'vitamin-c-1000mg-effervescent', 'VIT-VTC-002', 'High-strength effervescent Vitamin C with Zinc for powerful daily immune defense and fatigue reduction. Refreshing orange flavor.', 'Dissolve 1 tablet in 200ml of cold water daily.', 650.00, 580.00, 85, 0, 'vitaminc-effervescent.jpg', '2027-05-20', 1, 'active'),
(6, 3, 5, 'Pampers Sensitive Baby Wipes (80 Wipes)', 'pampers-sensitive-baby-wipes', 'BAB-PAM-001', 'Hypoallergenic, fragrance-free baby wipes crafted with 99% pure water. Ultra-soft for newborn sensitive skin.', 'Pull wipe and clean baby skin gently. Reseal package after use to maintain moisture.', 520.00, 475.00, 110, 0, 'pampers-wipes.jpg', '2028-06-30', 0, 'active'),
(7, 3, 5, 'Johnson Infant Gentle Bath 500ml', 'johnson-infant-gentle-bath', 'BAB-JNJ-002', 'No More Tears bath wash specially formulated for delicate newborn skin and hair. Paraben and dye-free.', 'Apply to wet skin with hands or washcloth, lather and rinse thoroughly.', 890.00, NULL, 5, 0, 'johnson-bath.jpg', '2027-09-10', 0, 'active'),
(8, 6, 6, 'Dettol Antiseptic Disinfectant Liquid 500ml', 'dettol-antiseptic-liquid-500ml', 'FIRST-DET-001', 'Trusted antiseptic liquid for first aid, wound cleansing, personal hygiene, and household surface disinfection.', 'For first aid: dilute 1 tablespoon in 250ml water before applying to cuts or scrapes.', 620.00, 570.00, 95, 0, 'dettol-antiseptic.jpg', '2028-10-15', 1, 'active'),
(9, 7, 7, 'OneTouch Select Plus Simple Glucometer Kit', 'onetouch-select-plus-glucometer', 'EQP-ONE-001', 'Accurate, hassle-free blood glucose monitoring system with color-coded target range indicator. Includes lancing device and 10 test strips.', 'Insert test strip, apply blood droplet, and view fast test result in 5 seconds.', 3800.00, 3499.00, 18, 0, 'onetouch-glucometer.jpg', NULL, 1, 'active'),
(10, 7, 8, 'Omron M2 Basic Automatic Blood Pressure Monitor', 'omron-m2-automatic-bp-monitor', 'EQP-OMR-001', 'Clinically validated upper arm digital BP monitor with Intellisense technology for comfortable, accurate readings.', 'Wrap cuff around upper arm at heart level and press START button.', 7800.00, 7200.00, 12, 0, 'omron-bp-monitor.jpg', NULL, 1, 'active'),
(11, 9, 9, 'Sensodyne Rapid Relief Toothpaste 100g', 'sensodyne-rapid-relief-toothpaste', 'DEN-SEN-001', 'Clinically proven fast relief from tooth sensitivity pain in just 60 seconds. Creates a protective barrier over sensitive areas.', 'Brush twice daily for long-lasting sensitivity protection.', 450.00, 399.00, 75, 0, 'sensodyne-toothpaste.jpg', '2027-03-31', 0, 'active'),
(12, 8, 4, 'Insulin Syringes 100 U-100 (Box of 100)', 'insulin-syringes-u100', 'DIA-ABB-001', 'Ultra-fine needle insulin syringes designed for precise dosing and minimal injection discomfort. Sterile and single use.', 'Single-use only under certified medical guidance.', 1100.00, 990.00, 4, 1, 'insulin-syringes.jpg', '2026-10-31', 0, 'active'),
(13, 6, 6, 'Waterproof Adhesive Bandages (Box of 50)', 'waterproof-adhesive-bandages-50s', 'FIRST-BAN-002', 'Sterile, breathable waterproof bandages for minor cuts, burns, and abrasions.', 'Clean wound area thoroughly before applying sterile bandage.', 250.00, 210.00, 120, 0, 'bandages.jpg', '2029-01-01', 0, 'active');

-- Insert Customer Saved Address
INSERT INTO `addresses` (`id`, `user_id`, `full_name`, `phone`, `street_address`, `city`, `postal_code`, `is_default`) VALUES
(1, 2, 'John Doe', '+923129876543', 'House # 45, Street 12, Sector F-8/3', 'Islamabad', '44000', 1);

-- Insert Sample Orders
INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_name`, `customer_phone`, `customer_email`, `shipping_address`, `city`, `postal_code`, `order_notes`, `subtotal`, `delivery_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `has_prescription`, `tracking_number`) VALUES
(1, 'ORD-20261001-1001', 2, 'John Doe', '+923129876543', 'john@example.com', 'House # 45, Street 12, Sector F-8/3', 'Islamabad', '44000', 'Please deliver before 5 PM.', 2350.00, 150.00, 2500.00, 'cod', 'pending', 'Confirmed', 1, 'TRK-98745612');

-- Insert Sample Order Items
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `total_price`) VALUES
(1, 1, 1, 'Panadol Extra 500mg Tablets (20s)', 160.00, 2, 320.00),
(2, 1, 4, 'Centrum Adults Multivitamin (60 Tablets)', 2199.00, 1, 2199.00);

-- Insert Sample Prescription
INSERT INTO `prescriptions` (`id`, `user_id`, `order_id`, `prescription_file`, `notes`, `status`, `pharmacist_notes`) VALUES
(1, 2, 1, 'sample-rx-john.jpg', 'Prescription for regular daily vitamins and mild pain relief from Dr. Ahmed Khan.', 'Approved', 'Verified valid prescription seal by Dr. Ahmed Khan, PMDC # 78452.');

-- Insert Sample Reviews
INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `review_text`, `status`) VALUES
(1, 1, 2, 5, 'Fast delivery and genuine medicine from Afridi Pharmacy. Panadol Extra works great!', 'approved'),
(2, 4, 2, 5, 'Original imported Centrum vitamin. Very good price with quick packaging.', 'approved');

-- Insert Coupons
INSERT INTO `coupons` (`id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `expiry_date`, `status`) VALUES
(1, 'HEALTH10', 'percent', 10.00, 1000.00, '2027-12-31', 'active'),
(2, 'AFRIDI500', 'fixed', 500.00, 3000.00, '2027-12-31', 'active');

-- Insert Initial Inventory Log
INSERT INTO `inventory` (`product_id`, `transaction_type`, `quantity`, `notes`) VALUES
(1, 'in', 150, 'Initial stock import'),
(2, 'in', 45, 'Initial stock import'),
(3, 'in', 28, 'Initial stock import'),
(4, 'in', 60, 'Initial stock import'),
(7, 'in', 5, 'Low stock warning test item'),
(12, 'in', 4, 'Low stock warning test item');

-- Insert Initial Admin Notification
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`) VALUES
(1, 'Welcome to AFRIDI PHARMACY Management', 'Pharmacy portal initialised successfully. 2 items are currently at low stock level.', 'warning');

-- ========================================================
-- END OF SCHEMA & SEED DATA
-- ========================================================
