CREATE DATABASE wedding_management;
USE wedding_management;

-- 1. Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('Admin', 'Organiser', 'Attendee') NOT NULL
);

-- 2. Events Table (The core of the wedding)
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    couple_id INT NOT NULL,
    location VARCHAR(255),
    food_choice VARCHAR(100),
    decoration_style VARCHAR(100),
    event_status ENUM('Pending', 'Approved') DEFAULT 'Pending',
    FOREIGN KEY (couple_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Guest List Table
CREATE TABLE guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone_number VARCHAR(20),
    rsvp_status ENUM('Confirmed', 'Declined', 'Pending') DEFAULT 'Pending',
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- 4. Couple Details Table
CREATE TABLE couple_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- The ID from the 'users' table
    bride_name VARCHAR(100),
    groom_name VARCHAR(100),
    phone_number VARCHAR(20),
    wedding_date DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Staff Details Table
CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_name VARCHAR(100),
    specialty VARCHAR(50), -- e.g., 'Catering', 'Decoration'
    phone_number VARCHAR(20)
);

-- Staff assigment Table
CREATE TABLE staff_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    staff_id INT NOT NULL,
    task_category ENUM('Photography', 'Catering', 'Decor') NOT NULL,
    job_title VARCHAR(100), -- Example: "Lead" or "Assistant"
    
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
);

-- 6. Gifts Table
CREATE TABLE gifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    is_taken BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE SET NULL
);

