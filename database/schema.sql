CREATE DATABASE IF NOT EXISTS ecotech_ecommerce
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ecotech_ecommerce;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  username VARCHAR(30) NOT NULL UNIQUE,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'vendor', 'user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  seller_id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  brand VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  image_url VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_categories
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_products_sellers
    FOREIGN KEY (seller_id) REFERENCES users(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  shipping_name VARCHAR(120) NOT NULL,
  shipping_address TEXT NOT NULL,
  notes TEXT NULL,
  status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_users
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_order_items_orders
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_order_items_products
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE contact_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(180) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  title VARCHAR(140) NOT NULL,
  body TEXT NOT NULL,
  is_approved TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5),
  CONSTRAINT fk_reviews_products
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_reviews_users
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (name, username, email, password_hash, role) VALUES
('Admin User', 'admin', 'admin@example.com', '$2y$10$RZkf4KU6L34vS1XqO8Xos.KzXJeiMPj2QlPL80esU/0BBvqlCNrl6', 'admin'),
('EcoTech Vendor', 'vendor', 'vendor@example.com', '$2y$10$/KIhviKYEuQ.VBqrCvPRiuyUWk9wFby4jBR8K5wdAxpUY6pUzzf/W', 'vendor'),
('Demo Customer', 'customer', 'user@example.com', '$2y$10$cjg8gCjuN5nc4w.F41iYFe0pPjEU3HkOtz5.BG.q8I2JAeT.UD0ay', 'user');

INSERT INTO categories (name, description) VALUES
('Laptops', 'Energy-efficient laptops and accessories for study, work, and creative projects.'),
('Smartphones', 'Modern smartphones selected for long battery life and repair-friendly ownership.'),
('Audio', 'Headphones and speakers with recycled materials and strong performance.'),
('Smart Home', 'Connected devices that help reduce energy usage and improve daily convenience.');

INSERT INTO products (category_id, seller_id, name, brand, description, price, stock, image_url, is_active) VALUES
(1, 2, 'EcoBook Air 14', 'EcoBook', 'A lightweight 14-inch laptop with efficient components, recycled aluminium casing, and all-day battery life for students and professionals.', 899.00, 12, 'assets/img/laptop.svg', 1),
(1, 2, 'RepairPro Laptop Kit', 'FixWise', 'A compact toolkit for safe laptop maintenance, including precision bits and anti-static tools for responsible device repair.', 49.00, 35, 'assets/img/laptop.svg', 1),
(2, 2, 'GreenPhone X', 'GreenPhone', 'A modular smartphone with a bright OLED screen, strong camera, replaceable battery, and responsibly sourced materials.', 699.00, 18, 'assets/img/phone.svg', 1),
(2, 2, 'SolarCharge Power Bank', 'SunVolt', 'A high-capacity portable charger with USB-C fast charging and a solar trickle-charge panel for emergency backup power.', 79.00, 24, 'assets/img/phone.svg', 1),
(3, 2, 'QuietLeaf Headphones', 'LeafSound', 'Wireless noise-cancelling headphones with recycled plastic housing, comfortable ear cups, and up to 40 hours of playback.', 159.00, 20, 'assets/img/headphones.svg', 1),
(3, 2, 'Bamboo Mini Speaker', 'LeafSound', 'A compact Bluetooth speaker with bamboo finish, clear sound, and a rechargeable battery for portable listening.', 59.00, 30, 'assets/img/headphones.svg', 1),
(4, 2, 'EcoHub Smart Speaker', 'HomeLoop', 'A voice-ready smart speaker for routines, reminders, and smart home automation with a low-power standby mode.', 129.00, 15, 'assets/img/smart-home.svg', 1),
(4, 2, 'Smart Energy Plug Pack', 'HomeLoop', 'A pack of four smart plugs that track power usage, schedule devices, and help reduce unnecessary energy consumption.', 89.00, 22, 'assets/img/smart-home.svg', 1);

INSERT INTO orders (user_id, total_amount, shipping_name, shipping_address, notes, status) VALUES
(3, 858.00, 'Demo Customer', '12 Green Street, Melbourne VIC 3000', 'Leave at reception for demo delivery.', 'processing'),
(3, 1028.00, 'Demo Customer', '12 Green Street, Melbourne VIC 3000', 'Demo order used for the sales graph.', 'completed');

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 3, 1, 699.00),
(1, 5, 1, 159.00),
(2, 1, 1, 899.00),
(2, 7, 1, 129.00);

INSERT INTO contact_messages (name, email, subject, message) VALUES
('Jordan Lee', 'jordan@example.com', 'Accessibility feedback', 'The keyboard navigation works well. Please add more product comparison information in the future.');

INSERT INTO reviews (product_id, user_id, rating, title, body, is_approved) VALUES
(1, 3, 5, 'Excellent student laptop', 'Battery life is strong and the lightweight design is perfect for campus work.', 1),
(3, 3, 4, 'Great modular phone', 'The replaceable battery and clear product information make this feel like an ethical purchase.', 1),
(5, 3, 5, 'Very comfortable headphones', 'Noise cancellation is impressive and the recycled housing is a great sustainability feature.', 1);
