-- Database: shop_online
-- Create database if not exists
CREATE DATABASE IF NOT EXISTS shop_online;
USE shop_online;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Table: orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table: order_items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@shop.com', '$2y$10$xLRsYPd7xN5K3q8vQjZn6O7YgT0wMfvCbE8dA1uR4sH3mK9pL2iXe', 'admin');

-- Insert sample categories
INSERT INTO categories (name, description) VALUES 
('Elektronik', 'Produk elektronik dan gadget'),
('Fashion', 'Pakaian dan aksesoris'),
('Makanan', 'Makanan dan minuman'),
('Olahraga', 'Peralatan olahraga');

-- Insert sample products
INSERT INTO products (name, description, price, stock, image, category_id) VALUES 
('Smartphone XYZ', 'Smartphone terbaru dengan kamera 48MP dan RAM 8GB', 3500000, 15, 'smartphone.jpg', 1),
('Laptop ABC', 'Laptop gaming dengan processor Intel i7 dan RTX 3060', 12000000, 8, 'laptop.jpg', 1),
('Kaos Polos Premium', 'Kaos polos berbahan katun combed 30s', 85000, 50, 'kaos.jpg', 2),
('Sepatu Running', 'Sepatu running dengan teknologi cushion terbaru', 650000, 20, 'sepatu.jpg', 4),
('Kopi Arabica 100g', 'Kopi arabica premium dari pegunungan', 45000, 100, 'kopi.jpg', 3),
('Headphone Wireless', 'Headphone bluetooth dengan noise cancelling', 450000, 25, 'headphone.jpg', 1);
