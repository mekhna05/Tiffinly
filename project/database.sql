-- Create the database
CREATE DATABASE IF NOT EXISTS tiffinly;
USE tiffinly;

-- USERS
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    role ENUM('user','admin','delivery') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- MEAL PLANS
CREATE TABLE meal_plans (
    plan_id INT PRIMARY KEY AUTO_INCREMENT,
    plan_name VARCHAR(50) NOT NULL,
    description TEXT,
    price DECIMAL(8,2) NOT NULL
);

-- MEALS
CREATE TABLE meals (
    meal_id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT,
    day ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
    meal_type ENUM('Breakfast','Lunch','Dinner') NOT NULL,
    option_type ENUM('veg','non_veg') NOT NULL,
    meal_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (plan_id) REFERENCES meal_plans(plan_id)
);

-- SUBSCRIPTIONS
CREATE TABLE subscriptions (
    subscription_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    plan_id INT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    schedule ENUM('Weekdays','Extended','Full Week') NOT NULL,
    delivery_time TIME NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    payment_status ENUM('paid','unpaid','failed') DEFAULT 'unpaid',
    status ENUM('active','cancelled','pending') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (plan_id) REFERENCES meal_plans(plan_id)
);

-- CUSTOM MEAL SELECTION
CREATE TABLE custom_meal_selection (
    selection_id INT PRIMARY KEY AUTO_INCREMENT,
    subscription_id INT,
    meal_id INT,
    day ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
    meal_type ENUM('Breakfast','Lunch','Dinner') NOT NULL,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(subscription_id),
    FOREIGN KEY (meal_id) REFERENCES meals(meal_id)
);

-- PAYMENTS
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    subscription_id INT,
    amount DECIMAL(10,2) NOT NULL,
    method VARCHAR(50),
    status ENUM('pending','paid','failed') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(subscription_id)
);

-- DELIVERIES
CREATE TABLE deliveries (
    delivery_id INT PRIMARY KEY AUTO_INCREMENT,
    subscription_id INT,
    partner_id INT,
    delivery_date DATE NOT NULL,
    delivery_time TIME NOT NULL,
    status ENUM('available','accepted','out_for_delivery','delivered','failed') DEFAULT 'available',
    notes TEXT,
    accepted_at TIMESTAMP NULL,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(subscription_id),
    FOREIGN KEY (partner_id) REFERENCES users(user_id)
);

-- INQUIRIES
CREATE TABLE inquiries (
    inquiry_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    message TEXT NOT NULL,
    response TEXT,
    status ENUM('pending','responded','closed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- FEEDBACK
CREATE TABLE feedback (
    feedback_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    subscription_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comments TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(subscription_id)
);

-- ADDRESSES TABLE

CREATE TABLE addresses (
    address_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    line1 VARCHAR(255) NOT NULL,
    line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    landmark VARCHAR(255),
    is_default BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE delivery_partner_details (
    partner_id INT PRIMARY KEY,
    vehicle_type VARCHAR(50) NOT NULL,
    vehicle_number VARCHAR(20) NOT NULL,
    license_number VARCHAR(30) NOT NULL,
    license_file VARCHAR(255),
    aadhar_number VARCHAR(20),
    availability ENUM('Morning', 'Evening', 'Full Day') NOT NULL,
    FOREIGN KEY (partner_id) REFERENCES users(user_id) ON DELETE CASCADE
);

--ALTER TABLE users
--ADD security_question VARCHAR(255) NOT NULL,
--ADD security_answer VARCHAR(255) NOT NULL;