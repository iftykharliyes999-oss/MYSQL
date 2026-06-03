-- =====================================================================
-- Aurora Grand · Hotel Management System
-- MySQL 8 / MariaDB 10+ schema with relational constraints + seed data
-- 15 tables, all FK protected. Run on a fresh schema.
-- =====================================================================

DROP DATABASE IF EXISTS aurora_hms;
CREATE DATABASE aurora_hms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aurora_hms;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Roles
CREATE TABLE roles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(32) UNIQUE NOT NULL
) ENGINE=InnoDB;

-- 2. Users (auth principals)
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  phone VARCHAR(20),
  password_hash VARCHAR(255) NOT NULL,
  role_id INT NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  deleted_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3. Customer profiles
CREATE TABLE customers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNIQUE NOT NULL,
  address TEXT,
  nid VARCHAR(40),
  loyalty_points INT DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Staff profiles
CREATE TABLE staff (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNIQUE NOT NULL,
  department ENUM('Housekeeping','Front Desk','Maintenance','Concierge') NOT NULL,
  shift ENUM('Morning','Evening','Night') NOT NULL,
  hired_on DATE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Room types
CREATE TABLE room_types (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(60) UNIQUE NOT NULL,
  base_price DECIMAL(10,2) NOT NULL,
  capacity INT NOT NULL,
  description TEXT
) ENGINE=InnoDB;

-- 6. Rooms
CREATE TABLE rooms (
  id INT PRIMARY KEY AUTO_INCREMENT,
  number VARCHAR(10) UNIQUE NOT NULL,
  type_id INT NOT NULL,
  status ENUM('available','occupied','dirty','maintenance') DEFAULT 'available',
  price DECIMAL(10,2) NOT NULL,
  floor INT,
  FOREIGN KEY (type_id) REFERENCES room_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 7. Amenities
CREATE TABLE amenities (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(60) UNIQUE NOT NULL
) ENGINE=InnoDB;

-- 8. Room ↔ amenities
CREATE TABLE room_amenities (
  room_id INT NOT NULL,
  amenity_id INT NOT NULL,
  PRIMARY KEY (room_id, amenity_id),
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Bookings
CREATE TABLE bookings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(20) UNIQUE NOT NULL,
  customer_id INT NOT NULL,
  room_id INT NOT NULL,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  nights INT NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  status ENUM('pending','confirmed','checked-in','checked-out','cancelled') DEFAULT 'pending',
  deleted_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 10. Payments
CREATE TABLE payments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  booking_id INT NOT NULL,
  method ENUM('card','bkash','cash') NOT NULL,
  reference VARCHAR(80) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 11. Invoices
CREATE TABLE invoices (
  id INT PRIMARY KEY AUTO_INCREMENT,
  booking_id INT UNIQUE NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  tax DECIMAL(12,2) NOT NULL,
  service DECIMAL(12,2) NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 12. Housekeeping logs
CREATE TABLE housekeeping_logs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  room_id INT NOT NULL,
  staff_id INT NOT NULL,
  status_set ENUM('clean','dirty','maintenance') NOT NULL,
  note TEXT,
  logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 13. Staff assignments
CREATE TABLE staff_assignments (
  staff_id INT NOT NULL,
  room_id INT NOT NULL,
  assigned_on DATE DEFAULT (CURRENT_DATE),
  PRIMARY KEY (staff_id, room_id),
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 14. Service orders
CREATE TABLE service_orders (
  id INT PRIMARY KEY AUTO_INCREMENT,
  booking_id INT NOT NULL,
  type ENUM('food','laundry','spa','transport','other') NOT NULL,
  description TEXT NOT NULL,
  status ENUM('pending','in-progress','delivered','cancelled') DEFAULT 'pending',
  amount DECIMAL(10,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 15. Audit log
CREATE TABLE audit_logs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  action VARCHAR(120) NOT NULL,
  entity VARCHAR(60),
  entity_id INT,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============== SEED DATA ==============
INSERT INTO roles (id, name) VALUES (1,'admin'),(2,'staff'),(3,'customer');

-- Passwords: admin123 / staff123 / guest123 (use password_hash() in production)
INSERT INTO users (name,email,phone,password_hash,role_id) VALUES
 ('Hotel Admin','admin@aurora.com','+8801700000001','$2y$10$abcdefghijklmnopqrstuO',1),
 ('Sarah Khatun','staff@aurora.com','+8801700000002','$2y$10$abcdefghijklmnopqrstuO',2),
 ('Imran Hossain','imran@aurora.com','+8801700000004','$2y$10$abcdefghijklmnopqrstuO',2),
 ('Nadia Akter','nadia@aurora.com','+8801700000005','$2y$10$abcdefghijklmnopqrstuO',2),
 ('Rifat Ahmed','guest@aurora.com','+8801700000003','$2y$10$abcdefghijklmnopqrstuO',3);

INSERT INTO customers (user_id,address,nid,loyalty_points) VALUES (5,'House 12, Banani, Dhaka','1990123456789',420);
INSERT INTO staff (user_id,department,shift,hired_on) VALUES
 (2,'Housekeeping','Morning','2023-06-01'),
 (3,'Front Desk','Evening','2022-11-15'),
 (4,'Maintenance','Morning','2024-01-20');

INSERT INTO room_types (name,base_price,capacity,description) VALUES
 ('Standard',4500,2,'Comfortable city-view room'),
 ('Deluxe',7800,3,'Spacious deluxe with mini bar'),
 ('Suite',14500,4,'Luxury suite with living room'),
 ('Executive',22000,2,'Skyline executive with butler');

INSERT INTO amenities (name) VALUES ('WiFi'),('AC'),('TV'),('Mini Bar'),('City View'),('Pool View'),('Jacuzzi'),('Butler'),('Living Room'),('Skyline View');

INSERT INTO rooms (number,type_id,status,price,floor) VALUES
 ('101',1,'available',4500,1),('102',2,'available',7800,1),('103',3,'occupied',14500,1),
 ('104',4,'available',22000,1),('105',1,'dirty',4500,1),
 ('201',2,'available',7800,2),('202',3,'available',14500,2),('203',4,'occupied',22000,2),
 ('204',1,'available',4500,2),('205',2,'available',7800,2),
 ('301',3,'available',14500,3),('302',4,'maintenance',22000,3),('303',1,'available',4500,3),
 ('304',2,'available',7800,3),('305',3,'available',14500,3);

INSERT INTO bookings (code,customer_id,room_id,check_in,check_out,nights,total,status) VALUES
 ('BK-1001',1,3,'2026-06-02','2026-06-05',3,43500.00,'checked-in'),
 ('BK-1002',1,8,'2026-06-08','2026-06-11',3,23400.00,'confirmed');

INSERT INTO payments (booking_id,method,reference,amount) VALUES
 (1,'bkash','BKS-88123',43500.00),
 (2,'card','CRD-77821',23400.00);

INSERT INTO invoices (booking_id,subtotal,tax,service,total) VALUES
 (1,43500,6525,2175,52200),
 (2,23400,3510,1170,28080);

INSERT INTO staff_assignments (staff_id,room_id) VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(3,12);

-- =========================================================
-- END OF SCHEMA
-- =========================================================