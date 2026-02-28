-- ============================================================
-- SmartVehi — Smart Vehicle Service & Digital Parking System
-- BCA 6th Semester | Priya.T (23IABCA120) | 2026
-- HOW TO USE:
--   1. Open phpMyAdmin → http://localhost/phpmyadmin
--   2. Click "New" → Create database: smartvehi
--   3. Select smartvehi → Import → choose this file → Go
-- ============================================================

CREATE DATABASE IF NOT EXISTS smartvehi
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE smartvehi;

-- -------------------------------------------------------
-- USERS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(120) NOT NULL,
    email      VARCHAR(180) NOT NULL UNIQUE,
    phone      VARCHAR(20)  NOT NULL DEFAULT '',
    password   VARCHAR(255) NOT NULL,
    role       ENUM('provider','receiver') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- LISTINGS  (parking / washing / rental)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS listings (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    type             ENUM('parking','washing','rental') NOT NULL,
    name             VARCHAR(200) NOT NULL,
    location         VARCHAR(300) NOT NULL,
    description      TEXT,
    image            VARCHAR(300) DEFAULT NULL,

    -- parking
    price_hour       DECIMAL(10,2) DEFAULT NULL,
    price_day        DECIMAL(10,2) DEFAULT NULL,
    total_slots      INT           DEFAULT NULL,
    vehicle_type     VARCHAR(50)   DEFAULT NULL,

    -- washing
    price_basic      DECIMAL(10,2) DEFAULT NULL,
    price_full       DECIMAL(10,2) DEFAULT NULL,
    services_offered TEXT          DEFAULT NULL,

    -- rental
    rental_type      VARCHAR(100)  DEFAULT NULL,
    vehicle_model    VARCHAR(100)  DEFAULT NULL,
    rent_hour        DECIMAL(10,2) DEFAULT NULL,
    rent_day         DECIMAL(10,2) DEFAULT NULL,
    fuel_type        VARCHAR(50)   DEFAULT NULL,

    is_active        TINYINT(1) DEFAULT 1,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- BOOKINGS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS bookings (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    listing_id    INT NOT NULL,
    user_id       INT NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    booking_date  DATE NOT NULL,
    duration      VARCHAR(50)  NOT NULL,
    notes         TEXT,
    cost          DECIMAL(10,2) DEFAULT 0.00,
    status        ENUM('confirmed','completed','cancelled') DEFAULT 'confirmed',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- REVIEWS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    listing_id    INT NOT NULL,
    user_id       INT NOT NULL,
    reviewer_name VARCHAR(120) NOT NULL,
    rating        TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment       TEXT NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- DEMO DATA  (password = "password" for both accounts)
-- -------------------------------------------------------
INSERT INTO users (full_name, email, phone, password, role) VALUES
('Demo Provider', 'provider@demo.com', '9876543210',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'provider'),
('Demo Receiver', 'receiver@demo.com', '9123456789',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receiver');

INSERT INTO listings
  (user_id,type,name,location,description,price_hour,price_day,total_slots,vehicle_type)
VALUES
  (1,'parking','City Center Parking Zone A','Anna Nagar, Chennai',
   'Covered parking with 24/7 CCTV and security.',30.00,200.00,20,'Both');

INSERT INTO listings
  (user_id,type,name,location,description,price_basic,price_full,services_offered)
VALUES
  (1,'washing','SparkleWash Center','T. Nagar, Chennai',
   'Professional car washing with eco-friendly products.',150.00,450.00,
   'Exterior wash, Interior vacuum, Waxing');

INSERT INTO listings
  (user_id,type,name,location,description,rental_type,vehicle_model,rent_hour,rent_day,fuel_type)
VALUES
  (1,'rental','City Ride Rentals','Adyar, Chennai',
   'Well-maintained vehicles with GPS. Available 24/7.',
   '2-Wheeler (Bike/Scooter)','Honda Activa',80.00,500.00,'Petrol');

INSERT INTO reviews (listing_id,user_id,reviewer_name,rating,comment) VALUES
  (1,2,'Arjun Kumar',  5,'Very clean and safe parking zone!'),
  (1,2,'Priya Sharma', 4,'Good location, affordable pricing.'),
  (2,2,'Karthik R',    5,'My car looks brand new after the wash!'),
  (3,2,'Sneha M',      4,'Smooth ride, well-maintained bike.');
