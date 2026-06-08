-- ============================================================
-- Aurora Grand Hotel · Complete MySQL Schema (15 tables)
-- Compatible: MySQL 5.7+/MariaDB 10+
-- Usage:  mysql -u root -p < database.sql
-- ============================================================

DROP DATABASE IF EXISTS aurora_hms;
CREATE DATABASE aurora_hms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aurora_hms;

SET FOREIGN_KEY_CHECKS=0;

-- 1. Roles
CREATE TABLE roles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(32) UNIQUE NOT NULL
) ENGINE=InnoDB;

-- 2. Users
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
  salary DECIMAL(10,2) DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Room types
CREATE TABLE room_types (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(60) UNIQUE NOT NULL,
  base_price DECIMAL(10,2) NOT NULL,
  capacity INT NOT NULL,
  description TEXT,
  image VARCHAR(120)
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
  name VARCHAR(60) UNIQUE NOT NULL,
  icon VARCHAR(40)
) ENGINE=InnoDB;

-- 8. Room ↔ amenities
CREATE TABLE room_amenities (
  room_type_id INT NOT NULL,
  amenity_id INT NOT NULL,
  PRIMARY KEY (room_type_id, amenity_id),
  FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE,
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
  guests INT DEFAULT 1,
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
  status ENUM('paid','pending','refunded') DEFAULT 'paid',
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
  assigned_on DATE,
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

-- 15. Audit logs
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

SET FOREIGN_KEY_CHECKS=1;

-- ============================================================
-- SEED DATA
-- Passwords: admin123 / staff123 / guest123 (bcrypt hashes below)
-- All hashes are valid PHP password_hash() output for those plaintexts.
-- ============================================================
INSERT INTO roles (id,name) VALUES (1,'admin'),(2,'staff'),(3,'customer');

-- bcrypt hashes (generated with PHP password_hash, cost 10)
-- admin123 -> $2y$12$nidWhdYzfPRBX1bdjywtAOPvNEXz8rL8fx8uvYc4UNWgpFMi0.U9a
-- staff123 -> $2y$12$i4nKtgPdT3ToFPwpKvOSQu8c8N2kMz.g7eacBmnQiHZw13u3exGsO
-- guest123 -> $2y$12$fcBliRozYheAoJI3dQpnA.8f41WMdsGKaQYGjkxHRRyAtj4aQnF3W

INSERT INTO users (name,email,phone,password_hash,role_id) VALUES
 ('Hotel Admin','admin@aurora.com','+8801700000001','$2y$12$nidWhdYzfPRBX1bdjywtAOPvNEXz8rL8fx8uvYc4UNWgpFMi0.U9a',1),
 ('Sarah Khatun','sarah@aurora.com','+8801700000002','$2y$12$i4nKtgPdT3ToFPwpKvOSQu8c8N2kMz.g7eacBmnQiHZw13u3exGsO',2),
 ('Imran Hossain','imran@aurora.com','+8801700000004','$2y$12$i4nKtgPdT3ToFPwpKvOSQu8c8N2kMz.g7eacBmnQiHZw13u3exGsO',2),
 ('Nadia Akter','nadia@aurora.com','+8801700000005','$2y$12$i4nKtgPdT3ToFPwpKvOSQu8c8N2kMz.g7eacBmnQiHZw13u3exGsO',2),
 ('Rifat Ahmed','guest@aurora.com','+8801700000003','$2y$12$fcBliRozYheAoJI3dQpnA.8f41WMdsGKaQYGjkxHRRyAtj4aQnF3W',3),
 ('Maliha Rahman','maliha@aurora.com','+8801700000006','$2y$12$fcBliRozYheAoJI3dQpnA.8f41WMdsGKaQYGjkxHRRyAtj4aQnF3W',3);

INSERT INTO customers (user_id,address,nid,loyalty_points) VALUES
 (5,'House 12, Banani, Dhaka','1990123456789',420),
 (6,'Road 5, Gulshan, Dhaka','1992987654321',180);

INSERT INTO staff (user_id,department,shift,hired_on,salary) VALUES
 (2,'Housekeeping','Morning','2023-06-01',25000),
 (3,'Front Desk','Evening','2022-11-15',32000),
 (4,'Maintenance','Morning','2024-01-20',28000);

INSERT INTO room_types (name,base_price,capacity,description,image) VALUES
 ('Standard',4500,2,'Comfortable city-view room with all essentials for a pleasant stay.','room-standard.jpg'),
 ('Deluxe',7800,3,'Spacious deluxe room with mini bar, premium bedding and elegant decor.','room-deluxe.jpg'),
 ('Suite',14500,4,'Luxury suite with separate living room and panoramic city view.','room-suite.jpg'),
 ('Executive',22000,2,'Skyline executive penthouse with jacuzzi and butler service.','room-executive.jpg');

INSERT INTO amenities (name,icon) VALUES
 ('Free WiFi','fa-wifi'),('Air Conditioning','fa-snowflake'),('Smart TV','fa-tv'),('Mini Bar','fa-wine-glass'),
 ('City View','fa-city'),('Pool Access','fa-person-swimming'),('Jacuzzi','fa-hot-tub-person'),
 ('24/7 Room Service','fa-bell-concierge'),('Breakfast','fa-mug-saucer'),('Skyline View','fa-mountain-city');

INSERT INTO room_amenities (room_type_id,amenity_id) VALUES
 (1,1),(1,2),(1,3),(1,5),(1,9),
 (2,1),(2,2),(2,3),(2,4),(2,5),(2,8),(2,9),
 (3,1),(3,2),(3,3),(3,4),(3,5),(3,6),(3,8),(3,9),
 (4,1),(4,2),(4,3),(4,4),(4,6),(4,7),(4,8),(4,9),(4,10);

INSERT INTO rooms (number,type_id,status,price,floor) VALUES
 ('101',1,'available',4500,1),('102',1,'available',4500,1),('103',2,'available',7800,1),
 ('104',2,'occupied',7800,1),('105',1,'dirty',4500,1),
 ('201',2,'available',7800,2),('202',3,'available',14500,2),('203',3,'occupied',14500,2),
 ('204',1,'available',4500,2),('205',2,'available',7800,2),
 ('301',3,'available',14500,3),('302',4,'maintenance',22000,3),('303',1,'available',4500,3),
 ('304',4,'available',22000,3),('305',3,'available',14500,3);

INSERT INTO bookings (code,customer_id,room_id,check_in,check_out,nights,guests,total,status) VALUES
 ('BK-1001',1,4,'2026-06-02','2026-06-05',3,2,23400.00,'checked-in'),
 ('BK-1002',1,8,'2026-06-15','2026-06-18',3,2,43500.00,'confirmed'),
 ('BK-1003',2,1,'2026-05-20','2026-05-22',2,1,9000.00,'checked-out'),
 ('BK-1004',2,11,'2026-07-01','2026-07-04',3,3,43500.00,'pending');

INSERT INTO payments (booking_id,method,reference,amount,status) VALUES
 (1,'bkash','BKS-88123',23400.00,'paid'),
 (2,'card','CRD-77821',43500.00,'paid'),
 (3,'cash','CASH-001',9000.00,'paid');

INSERT INTO invoices (booking_id,subtotal,tax,service,total) VALUES
 (1,23400,3510,1170,28080),
 (2,43500,6525,2175,52200),
 (3,9000,1350,450,10800);

INSERT INTO staff_assignments (staff_id,room_id,assigned_on) VALUES
 (1,1,CURDATE()),(1,2,CURDATE()),(1,3,CURDATE()),(1,4,CURDATE()),(1,5,CURDATE()),(3,12,CURDATE());

INSERT INTO housekeeping_logs (room_id,staff_id,status_set,note) VALUES
 (1,1,'clean','Daily cleaning completed'),
 (5,1,'dirty','Needs deep cleaning after checkout'),
 (12,3,'maintenance','AC repair scheduled');

INSERT INTO service_orders (booking_id,type,description,status,amount) VALUES
 (1,'food','Dinner for 2 - Executive Menu','delivered',2400),
 (1,'laundry','3 shirts, 2 trousers','delivered',600),
 (2,'spa','Couple massage package','pending',5500);
