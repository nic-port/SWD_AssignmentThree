CREATE DATABASE wedding_management;
USE wedding_management;

-- 1. USERS TABLE
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('Admin', 'Organiser', 'Attendee') NOT NULL
);

-- 2. EVENTS TABLE (core system)
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    couple_id INT NOT NULL,
    location VARCHAR(255),
    food_choice VARCHAR(100),
    decoration_style VARCHAR(100),
    event_status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',

    FOREIGN KEY (couple_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. GUESTS TABLE
CREATE TABLE guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone_number VARCHAR(20),
    rsvp_status ENUM('Confirmed', 'Declined', 'Pending') DEFAULT 'Pending',

    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- 4. COUPLE PROFILE TABLE
CREATE TABLE couple_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bride_name VARCHAR(100),
    groom_name VARCHAR(100),
    phone_number VARCHAR(20),
    wedding_date DATE,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. STAFF TABLE
CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_name VARCHAR(100),
    specialty VARCHAR(50),
    phone_number VARCHAR(20)
);

-- 6. STAFF ASSIGNMENTS TABLE
CREATE TABLE staff_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    staff_id INT NOT NULL,
    task_category ENUM('Photography', 'Catering', 'Decor') NOT NULL,
    job_title VARCHAR(100),

    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
);

-- 7. GIFTS TABLE (FIXED VERSION)
CREATE TABLE gifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    is_taken BOOLEAN DEFAULT FALSE,
    guest_id INT NULL,

    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE SET NULL
);