-- Create database
CREATE DATABASE IF NOT EXISTS pets_shop;
USE pets_shop;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Hotels/Services table
CREATE TABLE hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    address TEXT,
    city VARCHAR(100),
    price_per_day DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    gallery TEXT, -- JSON array of image paths
    amenities TEXT, -- JSON array of amenities
    capacity INT DEFAULT 1,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Services table
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2),
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pets table
CREATE TABLE pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('dog', 'cat', 'bird', 'other') NOT NULL,
    breed VARCHAR(100),
    age INT,
    weight DECIMAL(5,2),
    special_needs TEXT,
    vaccination_status ENUM('up_to_date', 'partial', 'none') DEFAULT 'none',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hotel_id INT NOT NULL,
    pet_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    total_days INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    special_requests TEXT,
    status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hotel_id INT NOT NULL,
    booking_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Messages/Chat table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    admin_id INT,
    message TEXT NOT NULL,
    sender_type ENUM('user', 'admin') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@petheaven.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample hotels
INSERT INTO hotels (name, description, address, city, price_per_day, image, amenities, capacity) VALUES 
('Pets Garden for Animals', 'Luxury pet boarding facility with spacious rooms and professional care', '109, Elshorouk, Elwaha Street', 'Cairo', 1200.00, 'imgs/e97c1c92271677155efd6666443a0fb0.jpg', '["24/7 Care", "Play Area", "Grooming", "Veterinary Care"]', 20),
('Golden Retriever Paradise', 'Specialized care for large breed dogs with outdoor play areas', '45 Maadi Street', 'Cairo', 1500.00, 'imgs/8-month-old-golden-retriever-600nw-2312248817.webp', '["Large Play Area", "Swimming Pool", "Training", "Grooming"]', 15),
('Cat Kingdom', 'Exclusive cat boarding with individual suites and climbing areas', '78 Zamalek Avenue', 'Cairo', 1000.00, 'imgs/cat13.jpg', '["Individual Suites", "Climbing Trees", "Quiet Environment", "Special Diet Care"]', 25);

-- Insert sample services
INSERT INTO services (name, description, price, image) VALUES 
('Pet Hosting', 'Professional pet boarding services', 1200.00, 'imgs/5916010158671120475.jpg'),
('Pet Care', 'Daily care and attention for your pets', 500.00, 'imgs/5916010158671120476.jpg'),
('Pet Training', 'Professional training services', 800.00, 'imgs/5916010158671120477.jpg'),
('Additional Services', 'Grooming, veterinary care, and more', 300.00, 'imgs/5916010158671120480.jpg');
