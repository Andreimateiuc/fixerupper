-- FixerUpper database schema for CMP214 Secure Website Development
-- Run this file in MySQL/phpMyAdmin before opening the PHP website.

CREATE DATABASE IF NOT EXISTS fixerupper
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE fixerupper;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS products;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  CHECK (price >= 0),
  CHECK (stock >= 0)
) ENGINE=InnoDB;

CREATE TABLE customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status VARCHAR(30) NOT NULL DEFAULT 'confirmed',
  CONSTRAINT fk_orders_customer
    FOREIGN KEY (customer_id) REFERENCES customers(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE RESTRICT,
  CHECK (quantity > 0),
  CHECK (price >= 0)
) ENGINE=InnoDB;

INSERT INTO products (name, description, price, image_url, stock) VALUES
('Cordless Power Drill', '18V cordless drill with battery pack and fast charger.', 79.99, 'assets/img/products/cordless-drill.jpg', 20),
('Compact Air Compressor', 'Portable compressor suitable for tyres, nailers, and workshop jobs.', 129.50, 'assets/img/products/air-compressor.jpg', 12),
('Electric Pressure Washer', 'High-pressure washer for patios, driveways, and outdoor tools.', 149.99, 'assets/img/products/pressure-washer.jpg', 8),
('Workbench Tool Cabinet', 'Steel rolling cabinet with lockable drawers for secure tool storage.', 219.00, 'assets/img/products/tool-cabinet.jpg', 6),
('Mitre Saw 210mm', 'Precision mitre saw for timber cutting and home renovation projects.', 179.95, 'assets/img/products/mitre-saw.jpg', 10);
