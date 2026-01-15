-- Pharmacy E-Commerce Database Schema
-- Database: pharmacy_db

-- Create database
CREATE DATABASE IF NOT EXISTS pharmacy_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pharmacy_db;

-- Drop tables if they exist (in correct order due to foreign keys)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255)
) ENGINE=InnoDB;

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    requires_prescription BOOLEAN DEFAULT FALSE,
    dosage_info TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    prescription_file VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Order Items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Cart table (supports both logged-in users and guests via session)
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    session_id VARCHAR(255),
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_slug ON products(slug);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_cart_session ON cart(session_id);

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- Insert Users (passwords are hashed versions of: 'password123')
-- Using PHP's password_hash() with PASSWORD_DEFAULT
INSERT INTO users (email, password, first_name, last_name, phone, address, role) VALUES
('admin@pharmacy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '+1234567890', '123 Admin Street, City, Country', 'admin'),
('manager@pharmacy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager', 'Staff', '+1234567891', '456 Manager Ave, City, Country', 'admin'),
('john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '+1234567892', '789 Customer Lane, City, Country', 'customer'),
('jane.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', '+1234567893', '321 Buyer Road, City, Country', 'customer'),
('mike.wilson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike', 'Wilson', '+1234567894', '654 Client Blvd, City, Country', 'customer');

-- Insert Categories
INSERT INTO categories (name, slug, description, image) VALUES
('Pain Relief', 'pain-relief', 'Over-the-counter pain relievers and analgesics for headaches, muscle pain, and inflammation.', 'pain-relief.jpg'),
('Cold & Flu', 'cold-flu', 'Medications to relieve cold and flu symptoms including cough, congestion, and fever.', 'cold-flu.jpg'),
('Vitamins & Supplements', 'vitamins-supplements', 'Essential vitamins, minerals, and dietary supplements for daily health support.', 'vitamins.jpg'),
('First Aid', 'first-aid', 'First aid supplies including bandages, antiseptics, and wound care products.', 'first-aid.jpg'),
('Personal Care', 'personal-care', 'Personal hygiene and care products for everyday use.', 'personal-care.jpg'),
('Prescription', 'prescription', 'Prescription medications - requires valid prescription from a licensed healthcare provider.', 'prescription.jpg');

-- Insert Products
-- Pain Relief Products
INSERT INTO products (category_id, name, slug, description, price, stock_quantity, image, requires_prescription, dosage_info) VALUES
(1, 'Ibuprofen 200mg Tablets', 'ibuprofen-200mg', 'Fast-acting pain relief for headaches, dental pain, menstrual cramps, muscle aches, and arthritis. Contains 100 coated tablets.', 8.99, 150, 'ibuprofen.jpg', FALSE, 'Adults and children 12 years and over: take 1 tablet every 4 to 6 hours while symptoms persist. Do not exceed 6 tablets in 24 hours unless directed by a doctor.'),
(1, 'Acetaminophen 500mg', 'acetaminophen-500mg', 'Effective pain reliever and fever reducer. Gentle on stomach. Contains 100 tablets.', 7.49, 200, 'acetaminophen.jpg', FALSE, 'Adults: 2 tablets every 6 hours as needed. Do not take more than 8 tablets in 24 hours.'),
(1, 'Aspirin 325mg', 'aspirin-325mg', 'Classic pain relief for minor aches and pains. Also helps reduce fever. 200 tablets.', 6.99, 180, 'aspirin.jpg', FALSE, 'Adults: 1-2 tablets every 4 hours as needed. Do not exceed 12 tablets in 24 hours.'),
(1, 'Naproxen Sodium 220mg', 'naproxen-sodium-220mg', 'Long-lasting pain relief for up to 12 hours. Effective for back pain, muscle pain, and arthritis. 50 caplets.', 11.99, 100, 'naproxen.jpg', FALSE, 'Adults: take 1 caplet every 8 to 12 hours. Do not exceed 2 caplets in 24 hours.'),

-- Cold & Flu Products
(2, 'DayQuil Cold & Flu', 'dayquil-cold-flu', 'Non-drowsy, multi-symptom cold and flu relief. Relieves headache, fever, sore throat, nasal congestion, and cough. 24 LiquiCaps.', 12.99, 120, 'dayquil.jpg', FALSE, 'Adults and children 12 years and over: take 2 LiquiCaps every 4 hours. Do not exceed 8 LiquiCaps in 24 hours.'),
(2, 'NyQuil Nighttime Cold & Flu', 'nyquil-nighttime', 'Nighttime relief for cold and flu symptoms. Helps you rest. 24 LiquiCaps.', 13.99, 100, 'nyquil.jpg', FALSE, 'Adults: take 2 LiquiCaps at bedtime. Do not take more than 2 LiquiCaps in 24 hours.'),
(2, 'Cough Suppressant Syrup', 'cough-suppressant-syrup', 'Fast-acting cough relief syrup. Soothes throat and suppresses cough for up to 8 hours. 8 oz bottle.', 9.49, 80, 'cough-syrup.jpg', FALSE, 'Adults: 2 teaspoons every 6-8 hours. Do not exceed 4 doses in 24 hours.'),
(2, 'Nasal Decongestant Spray', 'nasal-decongestant', 'Fast relief from nasal congestion due to cold, hay fever, or upper respiratory allergies. 1 oz spray.', 7.99, 150, 'nasal-spray.jpg', FALSE, 'Adults: 2-3 sprays in each nostril not more than every 10-12 hours. Do not use for more than 3 days.'),

-- Vitamins & Supplements
(3, 'Multivitamin Daily', 'multivitamin-daily', 'Complete daily multivitamin with essential vitamins and minerals. Supports overall health and wellness. 100 tablets.', 14.99, 200, 'multivitamin.jpg', FALSE, 'Adults: take 1 tablet daily with food.'),
(3, 'Vitamin C 1000mg', 'vitamin-c-1000mg', 'High-potency vitamin C for immune support. With rose hips for enhanced absorption. 100 tablets.', 12.49, 180, 'vitamin-c.jpg', FALSE, 'Adults: take 1 tablet daily with a meal.'),
(3, 'Vitamin D3 2000 IU', 'vitamin-d3-2000iu', 'Supports bone health, immune function, and mood. Essential for those with limited sun exposure. 120 softgels.', 10.99, 160, 'vitamin-d.jpg', FALSE, 'Adults: take 1 softgel daily with food.'),
(3, 'Omega-3 Fish Oil', 'omega-3-fish-oil', 'Purified fish oil with EPA and DHA for heart, brain, and joint health. 90 softgels.', 18.99, 120, 'fish-oil.jpg', FALSE, 'Adults: take 2 softgels daily with food.'),
(3, 'Calcium + Vitamin D', 'calcium-vitamin-d', 'Supports strong bones and teeth. Essential for women and adults over 50. 120 tablets.', 11.99, 140, 'calcium.jpg', FALSE, 'Adults: take 2 tablets daily with meals.'),

-- First Aid Products
(4, 'Adhesive Bandages Assorted', 'adhesive-bandages', 'Sterile adhesive bandages in assorted sizes. Flexible fabric for comfort. 100 count box.', 6.99, 250, 'bandages.jpg', FALSE, NULL),
(4, 'Antiseptic Wound Spray', 'antiseptic-spray', 'No-sting antiseptic spray for cleaning wounds. Kills germs to help prevent infection. 8 oz bottle.', 8.49, 100, 'antiseptic.jpg', FALSE, 'Clean affected area, apply spray 1-3 times daily. Cover with sterile bandage if needed.'),
(4, 'First Aid Kit Complete', 'first-aid-kit', 'Comprehensive first aid kit with 150 pieces. Includes bandages, gauze, tape, scissors, and antiseptic wipes.', 24.99, 50, 'first-aid-kit.jpg', FALSE, NULL),
(4, 'Hydrocortisone Cream 1%', 'hydrocortisone-cream', 'Anti-itch cream for temporary relief of minor skin irritations, rashes, and insect bites. 1 oz tube.', 7.99, 120, 'hydrocortisone.jpg', FALSE, 'Apply to affected area 3-4 times daily. Do not use for more than 7 days.'),
(4, 'Sterile Gauze Pads', 'sterile-gauze-pads', 'Sterile gauze pads for wound dressing. Highly absorbent. 4x4 inch, 25 count.', 5.99, 180, 'gauze.jpg', FALSE, NULL),

-- Personal Care Products
(5, 'Hand Sanitizer Gel', 'hand-sanitizer', 'Kills 99.99% of germs. Moisturizing formula with aloe vera. 8 oz pump bottle.', 4.99, 300, 'sanitizer.jpg', FALSE, 'Apply to hands, rub until dry. No water needed.'),
(5, 'Antibacterial Soap', 'antibacterial-soap', 'Gentle antibacterial liquid soap for everyday use. Fresh scent. 12 oz bottle.', 5.49, 200, 'soap.jpg', FALSE, NULL),
(5, 'Digital Thermometer', 'digital-thermometer', 'Fast and accurate digital thermometer. Memory recall function. Includes battery and storage case.', 12.99, 80, 'thermometer.jpg', FALSE, 'Place under tongue and wait for beep. Clean tip with alcohol before and after use.'),
(5, 'Blood Pressure Monitor', 'blood-pressure-monitor', 'Automatic digital blood pressure monitor for home use. Large display, memory for 60 readings.', 39.99, 40, 'bp-monitor.jpg', FALSE, 'Sit quietly for 5 minutes before use. Wrap cuff around upper arm and press start.'),

-- Prescription Products
(6, 'Amoxicillin 500mg', 'amoxicillin-500mg', 'Broad-spectrum antibiotic for bacterial infections. Prescription required. 30 capsules.', 15.99, 100, 'amoxicillin.jpg', TRUE, 'Take exactly as prescribed by your doctor. Usually 1 capsule 3 times daily for 7-10 days.'),
(6, 'Lisinopril 10mg', 'lisinopril-10mg', 'ACE inhibitor for high blood pressure and heart failure. Prescription required. 30 tablets.', 12.99, 80, 'lisinopril.jpg', TRUE, 'Take exactly as prescribed. Usually 1 tablet daily. Do not stop without consulting doctor.'),
(6, 'Metformin 500mg', 'metformin-500mg', 'Oral diabetes medication for type 2 diabetes. Prescription required. 60 tablets.', 18.99, 90, 'metformin.jpg', TRUE, 'Take with meals as directed by your doctor. Usually 1 tablet 2 times daily.'),
(6, 'Omeprazole 20mg', 'omeprazole-20mg', 'Proton pump inhibitor for acid reflux and ulcers. Prescription strength. 30 capsules.', 22.99, 70, 'omeprazole.jpg', TRUE, 'Take 1 capsule daily before a meal. Do not crush or chew.');

-- Insert Sample Orders
INSERT INTO orders (user_id, total_amount, status, shipping_address, notes) VALUES
(3, 45.96, 'delivered', '789 Customer Lane, City, Country', 'Please leave at front door'),
(3, 27.48, 'shipped', '789 Customer Lane, City, Country', NULL),
(4, 63.95, 'processing', '321 Buyer Road, City, Country', 'Call before delivery'),
(4, 18.98, 'pending', '321 Buyer Road, City, Country', NULL),
(5, 89.94, 'delivered', '654 Client Blvd, City, Country', 'Gift wrapping requested');

-- Insert Order Items
-- Order 1 (John Doe - delivered)
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 2, 8.99),   -- 2x Ibuprofen
(1, 9, 1, 14.99),  -- 1x Multivitamin
(1, 20, 1, 4.99);  -- 1x Hand Sanitizer

-- Order 2 (John Doe - shipped)
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(2, 5, 1, 12.99),  -- 1x DayQuil
(2, 10, 1, 12.49); -- 1x Vitamin C

-- Order 3 (Jane Smith - processing)
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(3, 17, 1, 24.99), -- 1x First Aid Kit
(3, 23, 1, 39.99); -- 1x BP Monitor (note: this order would normally need a correction as BP monitor is not prescription)

-- Order 4 (Jane Smith - pending)
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(4, 2, 1, 7.49),   -- 1x Acetaminophen
(4, 11, 1, 10.99); -- 1x Vitamin D3

-- Order 5 (Mike Wilson - delivered)
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(5, 12, 2, 18.99), -- 2x Omega-3
(5, 9, 2, 14.99),  -- 2x Multivitamin
(5, 14, 1, 6.99);  -- 1x Bandages

-- Add some items to cart for testing
INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES
(3, NULL, 1, 2),
(3, NULL, 10, 1),
(NULL, 'guest_session_123', 5, 1),
(NULL, 'guest_session_123', 15, 2);
