-- ============================================================
-- Aurora Grand · v2 Patch (Messaging, Notifications, Image Uploads)
-- Run this AFTER database.sql on an existing aurora_hms database:
--   mysql -u root -p aurora_hms < migrations.sql
-- Safe to re-run: uses IF NOT EXISTS / DROP IF EXISTS.
-- ============================================================
USE aurora_hms;

-- 1) Image column for individual rooms (in addition to type's default image)
ALTER TABLE rooms
  ADD COLUMN IF NOT EXISTS image VARCHAR(180) NULL AFTER floor,
  ADD COLUMN IF NOT EXISTS description TEXT NULL AFTER image;

-- 2) Make sure room_types.image can hold uploaded path
ALTER TABLE room_types MODIFY image VARCHAR(180) NULL;

-- 3) Notifications (for admin/staff/customer)
CREATE TABLE IF NOT EXISTS notifications (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NULL,                    -- NULL = broadcast to a role
  role ENUM('admin','staff','customer') NULL,
  type VARCHAR(40) NOT NULL,           -- booking, message, payment, system
  title VARCHAR(180) NOT NULL,
  body TEXT,
  link VARCHAR(255),
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id, is_read),
  INDEX idx_role (role, is_read),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4) Message threads (one per conversation / inquiry)
CREATE TABLE IF NOT EXISTS message_threads (
  id INT PRIMARY KEY AUTO_INCREMENT,
  customer_id INT NULL,                -- NULL if guest (use guest_name/email)
  guest_name VARCHAR(120) NULL,
  guest_email VARCHAR(150) NULL,
  subject VARCHAR(200) NOT NULL,
  status ENUM('open','answered','closed') DEFAULT 'open',
  last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5) Messages inside a thread
CREATE TABLE IF NOT EXISTS messages (
  id INT PRIMARY KEY AUTO_INCREMENT,
  thread_id INT NOT NULL,
  sender_id INT NULL,                  -- NULL = guest, otherwise users.id
  sender_role ENUM('admin','staff','customer','guest') NOT NULL,
  body TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_thread (thread_id, created_at),
  FOREIGN KEY (thread_id) REFERENCES message_threads(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 6) Demo data
INSERT INTO message_threads (customer_id, subject, status) VALUES
 (1, 'Late check-in request', 'open'),
 (2, 'Airport pickup availability', 'answered');

INSERT INTO messages (thread_id, sender_id, sender_role, body) VALUES
 (1, 5, 'customer', 'Hi, I will arrive around 1 AM. Is late check-in possible?'),
 (2, 6, 'customer', 'Do you provide airport pickup service?'),
 (2, 1, 'admin', 'Yes, we offer airport pickup at ৳1500. Please share your flight details.');

INSERT INTO notifications (role, type, title, body, link) VALUES
 ('admin', 'message', 'New inquiry: Late check-in request', 'A customer asked about late check-in.', 'admin/messages.php'),
 ('admin', 'booking', 'New booking BK-1004 (pending)', 'Maliha Rahman booked Suite Room.', 'admin/bookings.php');
