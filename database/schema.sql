-- OUTSINC Database Schema
-- Outreach Someone In Need of Change

CREATE DATABASE IF NOT EXISTS outsinc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE outsinc_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('client', 'worker', 'provider', 'admin', 'public') NOT NULL DEFAULT 'client',
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(20),
    date_of_birth DATE,
    security_question VARCHAR(255),
    security_answer_hash VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login DATETIME,
    failed_login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_user_id (user_id),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User preferences table
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    theme_mode ENUM('light', 'dark', 'auto') DEFAULT 'light',
    font_size ENUM('small', 'medium', 'large') DEFAULT 'medium',
    dyslexia_friendly BOOLEAN DEFAULT FALSE,
    high_contrast BOOLEAN DEFAULT FALSE,
    notifications_enabled BOOLEAN DEFAULT TRUE,
    notification_sound BOOLEAN DEFAULT TRUE,
    language VARCHAR(10) DEFAULT 'en',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clients table (extended profile for clients)
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    preferred_name VARCHAR(100),
    alias VARCHAR(100),
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relationship VARCHAR(50),
    current_location TEXT,
    housing_status ENUM('homeless', 'sheltered', 'housed', 'unstable', 'unknown') DEFAULT 'unknown',
    primary_language VARCHAR(50),
    requires_interpreter BOOLEAN DEFAULT FALSE,
    has_id BOOLEAN DEFAULT FALSE,
    id_type VARCHAR(50),
    consent_to_contact BOOLEAN DEFAULT TRUE,
    best_contact_method ENUM('phone', 'email', 'in_person', 'text') DEFAULT 'phone',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_housing_status (housing_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Consents table
CREATE TABLE IF NOT EXISTS consents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    agency_name VARCHAR(255) NOT NULL,
    consent_type ENUM('housing', 'health', 'mental_health', 'legal', 'general') NOT NULL,
    status ENUM('granted', 'denied', 'revoked') NOT NULL,
    granted_date DATE NOT NULL,
    expiry_date DATE,
    consent_method ENUM('verbal', 'digital', 'written') DEFAULT 'verbal',
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_client_id (client_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Needs assessments table
CREATE TABLE IF NOT EXISTS needs_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    assessment_date DATE NOT NULL,
    completed_by INT NOT NULL,
    completion_status ENUM('in_progress', 'completed', 'partial') DEFAULT 'in_progress',
    housing_score INT,
    substance_use_score INT,
    mental_health_score INT,
    physical_health_score INT,
    safety_score INT,
    income_score INT,
    legal_score INT,
    overall_priority ENUM('low', 'moderate', 'high', 'crisis') DEFAULT 'moderate',
    assessment_data JSON,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (completed_by) REFERENCES users(id),
    INDEX idx_client_id (client_id),
    INDEX idx_priority (overall_priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Risk assessments table
CREATE TABLE IF NOT EXISTS risk_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    assessment_date DATE NOT NULL,
    assessed_by INT NOT NULL,
    self_harm_risk ENUM('none', 'low', 'moderate', 'high') DEFAULT 'none',
    harm_to_others_risk ENUM('none', 'low', 'moderate', 'high') DEFAULT 'none',
    exploitation_risk ENUM('none', 'low', 'moderate', 'high') DEFAULT 'none',
    overdose_risk ENUM('none', 'low', 'moderate', 'high') DEFAULT 'none',
    violence_risk ENUM('none', 'low', 'moderate', 'high') DEFAULT 'none',
    neglect_risk ENUM('none', 'low', 'moderate', 'high') DEFAULT 'none',
    overall_risk ENUM('low', 'moderate', 'high', 'crisis') DEFAULT 'moderate',
    safety_plan TEXT,
    crisis_contacts JSON,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (assessed_by) REFERENCES users(id),
    INDEX idx_client_id (client_id),
    INDEX idx_overall_risk (overall_risk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quality of life tracking table
CREATE TABLE IF NOT EXISTS qol_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    tracking_date DATE NOT NULL,
    stress_level INT CHECK (stress_level BETWEEN 1 AND 10),
    mood_level INT CHECK (mood_level BETWEEN 1 AND 10),
    safety_level INT CHECK (safety_level BETWEEN 1 AND 10),
    hope_level INT CHECK (hope_level BETWEEN 1 AND 10),
    connection_level INT CHECK (connection_level BETWEEN 1 AND 10),
    er_visits_30d INT DEFAULT 0,
    arrests_30d INT DEFAULT 0,
    nights_outside_30d INT DEFAULT 0,
    nights_sheltered_30d INT DEFAULT 0,
    nights_housed_30d INT DEFAULT 0,
    notes TEXT,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id),
    INDEX idx_client_id (client_id),
    INDEX idx_tracking_date (tracking_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Case notes table
CREATE TABLE IF NOT EXISTS case_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    note_date DATETIME NOT NULL,
    note_type ENUM('contact', 'crisis', 'housing', 'income', 'mental_health', 'harm_reduction', 'legal', 'general') DEFAULT 'general',
    contact_location VARCHAR(255),
    note_text TEXT NOT NULL,
    tags JSON,
    is_client_visible BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_client_id (client_id),
    INDEX idx_note_type (note_type),
    INDEX idx_note_date (note_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Goals table
CREATE TABLE IF NOT EXISTS goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    goal_text TEXT NOT NULL,
    goal_category ENUM('housing', 'health', 'income', 'relationships', 'safety', 'legal', 'personal', 'other') DEFAULT 'personal',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('active', 'in_progress', 'completed', 'on_hold', 'abandoned') DEFAULT 'active',
    target_date DATE,
    completion_date DATE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_client_id (client_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasks table
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    goal_id INT,
    task_text TEXT NOT NULL,
    assigned_to INT,
    due_date DATE,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    completed_date DATE,
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (goal_id) REFERENCES goals(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_client_id (client_id),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table (for harm reduction supplies)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(50) UNIQUE NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    category ENUM('needles', 'stems', 'naloxone', 'condoms', 'pipes', 'cookers', 'other') NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    unit_type VARCHAR(50),
    status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory table
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    location VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    min_threshold INT DEFAULT 10,
    last_restock_date DATE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_location (location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    client_id INT NOT NULL,
    order_date DATETIME NOT NULL,
    status ENUM('pending', 'awaiting_pickup', 'fulfilled', 'delivered', 'on_hold', 'cancelled') DEFAULT 'pending',
    pickup_dropoff ENUM('pickup', 'dropoff') DEFAULT 'pickup',
    scheduled_time DATETIME,
    location VARCHAR(255),
    instructions TEXT,
    fulfilled_by INT,
    fulfilled_date DATETIME,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (fulfilled_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_client_id (client_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resources directory table
CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_name VARCHAR(255) NOT NULL,
    category ENUM('shelter', 'health', 'mental_health', 'legal', 'food', 'id_clinic', 'outreach', 'drop_in', 'other') NOT NULL,
    description TEXT,
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(20),
    phone VARCHAR(20),
    email VARCHAR(255),
    website VARCHAR(255),
    hours_of_operation TEXT,
    eligibility_criteria TEXT,
    population_served JSON,
    services_offered JSON,
    accepts_walkins BOOLEAN DEFAULT TRUE,
    wheelchair_accessible BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'temporarily_closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Referrals table
CREATE TABLE IF NOT EXISTS referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    resource_id INT NOT NULL,
    referral_date DATE NOT NULL,
    referral_type ENUM('warm', 'cold', 'self') DEFAULT 'warm',
    status ENUM('pending', 'connected', 'waitlist', 'declined', 'not_qualified', 'completed') DEFAULT 'pending',
    outcome_notes TEXT,
    follow_up_date DATE,
    referred_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id),
    FOREIGN KEY (referred_by) REFERENCES users(id),
    INDEX idx_client_id (client_id),
    INDEX idx_status (status),
    INDEX idx_referral_date (referral_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Public reports table
CREATE TABLE IF NOT EXISTS public_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_number VARCHAR(50) UNIQUE NOT NULL,
    report_type ENUM('needles', 'encampment', 'welfare_check', 'abandoned_belongings', 'other') NOT NULL,
    location TEXT NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    description TEXT,
    safety_concern BOOLEAN DEFAULT FALSE,
    reporter_name VARCHAR(100),
    reporter_contact VARCHAR(255),
    status ENUM('new', 'assigned', 'in_progress', 'completed', 'closed') DEFAULT 'new',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    assigned_to INT,
    response_notes TEXT,
    responded_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    INDEX idx_report_type (report_type),
    INDEX idx_status (status),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_title VARCHAR(255) NOT NULL,
    event_type ENUM('outreach', 'community_meal', 'meeting', 'popup', 'training', 'other') NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    location TEXT,
    target_audience ENUM('clients', 'staff', 'public', 'all') DEFAULT 'all',
    capacity INT,
    registration_required BOOLEAN DEFAULT FALSE,
    status ENUM('planned', 'active', 'completed', 'cancelled') DEFAULT 'planned',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_event_date (event_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- News/posts table
CREATE TABLE IF NOT EXISTS news_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_title VARCHAR(255) NOT NULL,
    post_content TEXT NOT NULL,
    post_type ENUM('news', 'update', 'impact', 'announcement') DEFAULT 'news',
    is_public BOOLEAN DEFAULT TRUE,
    published_date DATE,
    author_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id),
    INDEX idx_published_date (published_date),
    INDEX idx_is_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Learning content table
CREATE TABLE IF NOT EXISTS learning_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_title VARCHAR(255) NOT NULL,
    content_type ENUM('guide', 'video', 'audio', 'toolkit', 'course') NOT NULL,
    description TEXT,
    content_url VARCHAR(255),
    content_body TEXT,
    target_audience JSON,
    tags JSON,
    estimated_duration INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_content_type (content_type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    subject VARCHAR(255),
    message_text TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME,
    parent_message_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_message_id) REFERENCES messages(id) ON DELETE CASCADE,
    INDEX idx_sender_id (sender_id),
    INDEX idx_recipient_id (recipient_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    notification_title VARCHAR(255) NOT NULL,
    notification_text TEXT NOT NULL,
    link_url VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit log table
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
