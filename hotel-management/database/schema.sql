-- Hotel Management System Database Schema
-- MySQL 5.7+ / MariaDB 10.2+

CREATE DATABASE IF NOT EXISTS hotel_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_management;

-- =========================
-- USERS TABLE (Admin/Staff/Customer)
-- =========================
DROP TABLE IF EXISTS housekeeping;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','staff','customer') NOT NULL DEFAULT 'customer',
    phone VARCHAR(30),
    address TEXT,
    nid VARCHAR(50),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================
-- ROOMS TABLE
-- =========================
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) NOT NULL UNIQUE,
    room_type ENUM('single','double','suite','deluxe') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    capacity INT DEFAULT 2,
    description TEXT,
    image VARCHAR(255),
    status ENUM('available','booked','maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================
-- BOOKINGS TABLE
-- =========================
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    total_nights INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    special_request TEXT,
    status ENUM('pending','confirmed','checked_in','checked_out','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- PAYMENTS TABLE
-- =========================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash','card') NOT NULL,
    payment_status ENUM('paid','due','partial') DEFAULT 'due',
    transaction_ref VARCHAR(100),
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- HOUSEKEEPING TABLE
-- =========================
CREATE TABLE housekeeping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    staff_id INT NOT NULL,
    task_type ENUM('cleaning','maintenance','inspection') DEFAULT 'cleaning',
    status ENUM('pending','in_progress','done') DEFAULT 'pending',
    notes TEXT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- DEFAULT DATA
-- =========================
-- Default password for ALL accounts: password123
-- Generated with: password_hash('password123', PASSWORD_DEFAULT)
INSERT INTO users (name,email,password,role,phone) VALUES
('Super Admin','admin@hotel.com','$2y$12$bTfqAV7ZoWdUMmQce8RPp.OWAh3annX1KsS5s30AxyTlPWwFKfknu','admin','01700000001'),
('John Staff','staff@hotel.com','$2y$12$bTfqAV7ZoWdUMmQce8RPp.OWAh3annX1KsS5s30AxyTlPWwFKfknu','staff','01700000002'),
('Jane Customer','customer@hotel.com','$2y$12$bTfqAV7ZoWdUMmQce8RPp.OWAh3annX1KsS5s30AxyTlPWwFKfknu','customer','01700000003');

INSERT INTO rooms (room_number,room_type,price,capacity,description,status) VALUES
('101','single',2500.00,1,'Cozy single room with city view','available'),
('102','single',2500.00,1,'Single room with garden view','available'),
('201','double',4500.00,2,'Spacious double room with balcony','available'),
('202','double',4500.00,2,'Double room with sea view','available'),
('301','suite',8500.00,4,'Luxurious suite with living area','available'),
('302','deluxe',12000.00,4,'Deluxe suite with private pool','available');
