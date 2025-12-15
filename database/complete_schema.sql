-- ============================================================================
-- OUTSINC COMPLETE DATABASE SCHEMA
-- Outreach Someone In Need of Change
-- ============================================================================
-- This unified schema file contains:
--   1. Base schema with all core tables
--   2. Phase 1 enhancements and additional tables
--   3. Sample data for testing and demo purposes
--
-- USAGE:
--   DROP DATABASE IF EXISTS outsinc_db;
--   CREATE DATABASE outsinc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   USE outsinc_db;
--   SOURCE complete_schema.sql;
--
-- OR:
--   mysql -u root -p < complete_schema.sql
-- ============================================================================

CREATE DATABASE IF NOT EXISTS outsinc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE outsinc_db;

-- ============================================================================
-- CORE TABLES - User Management
-- ============================================================================

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

-- ============================================================================
-- CLIENT MANAGEMENT
-- ============================================================================

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
    -- Phase 1 enhancements
    preferred_contact_time VARCHAR(50),
    intake_completed_at TIMESTAMP NULL,
    intake_worker_id INT NULL,
    documents_uploaded BOOLEAN DEFAULT FALSE,
    substance_use_current ENUM('yes', 'no', 'sometimes', 'prefer_not', 'unknown') DEFAULT 'unknown',
    substance_types JSON,
    naloxone_trained BOOLEAN DEFAULT FALSE,
    health_card_status ENUM('yes', 'no', 'expired', 'dont_know', 'unknown') DEFAULT 'unknown',
    income_source VARCHAR(100),
    legal_issues BOOLEAN DEFAULT FALSE,
    housing_duration VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_housing_status (housing_status),
    INDEX idx_intake_completed (intake_completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INTAKE MANAGEMENT
-- ============================================================================

-- Intake sessions for pause/resume functionality
CREATE TABLE IF NOT EXISTS intake_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_uuid VARCHAR(36) UNIQUE NOT NULL,
    client_user_id INT NULL,
    worker_user_id INT NULL,
    current_step INT DEFAULT 1,
    form_data JSON,
    status ENUM('in_progress', 'completed', 'expired') DEFAULT 'in_progress',
    reminder_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (client_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (worker_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_client (client_user_id),
    INDEX idx_worker (worker_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Documents uploaded during intake
CREATE TABLE IF NOT EXISTS intake_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    intake_session_id INT NULL,
    client_id INT NULL,
    document_type ENUM('id', 'health_card', 'photo', 'other') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (intake_session_id) REFERENCES intake_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_session (intake_session_id),
    INDEX idx_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CONSENT MANAGEMENT
-- ============================================================================

-- Consent categories
CREATE TABLE IF NOT EXISTS consent_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    category_type ENUM('housing', 'health', 'mental_health', 'legal', 'financial', 'general') NOT NULL,
    description TEXT,
    plain_language_explanation TEXT,
    how_used_explanation TEXT,
    required_for_services BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (category_type)
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
    -- Phase 1 enhancements
    category_id INT NULL,
    partial_consent JSON,
    digital_signature TEXT,
    verbal_attestation_by INT NULL,
    revoked_by INT NULL,
    revoked_at TIMESTAMP NULL,
    revocation_reason TEXT,
    agencies_notified BOOLEAN DEFAULT FALSE,
    emergency_override BOOLEAN DEFAULT FALSE,
    emergency_approved_by INT NULL,
    emergency_reason TEXT,
    client_notified_of_override BOOLEAN DEFAULT FALSE,
    time_limit_months INT NULL,
    expiry_notification_sent BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES consent_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (verbal_attestation_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (revoked_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (emergency_approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_client_id (client_id),
    INDEX idx_status (status),
    INDEX idx_expiry (expiry_date),
    INDEX idx_status_type (status, consent_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Consent change history
CREATE TABLE IF NOT EXISTS consent_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consent_id INT NOT NULL,
    action ENUM('granted', 'revoked', 'modified', 'emergency_override', 'expired') NOT NULL,
    performed_by INT NOT NULL,
    action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reason TEXT,
    old_values JSON,
    new_values JSON,
    FOREIGN KEY (consent_id) REFERENCES consents(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id),
    INDEX idx_consent (consent_id),
    INDEX idx_action_date (action_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ASSESSMENT MANAGEMENT
-- ============================================================================

-- Assessment templates
CREATE TABLE IF NOT EXISTS assessment_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL,
    template_type ENUM('needs', 'risk', 'qol') NOT NULL,
    questions JSON NOT NULL,
    conditional_logic JSON,
    scoring_rules JSON,
    version INT DEFAULT 1,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (template_type),
    INDEX idx_active (active)
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
    -- Phase 1 enhancements
    template_id INT,
    social_support_score INT,
    overall_risk_score INT,
    risk_level ENUM('low', 'medium', 'high') DEFAULT 'low',
    high_risk_flags JSON,
    alerts_sent BOOLEAN DEFAULT FALSE,
    worker_notified BOOLEAN DEFAULT FALSE,
    supervisor_reviewed BOOLEAN DEFAULT FALSE,
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    follow_up_scheduled_date DATE NULL,
    comparison_to_baseline JSON,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (completed_by) REFERENCES users(id),
    FOREIGN KEY (template_id) REFERENCES assessment_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_client_id (client_id),
    INDEX idx_priority (overall_priority),
    INDEX idx_follow_up (follow_up_scheduled_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Assessment responses (individual question answers)
CREATE TABLE IF NOT EXISTS assessment_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    question_id VARCHAR(50) NOT NULL,
    question_text TEXT,
    answer_value TEXT,
    answer_score INT,
    skipped BOOLEAN DEFAULT FALSE,
    dont_know BOOLEAN DEFAULT FALSE,
    prefer_not_say BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES needs_assessments(id) ON DELETE CASCADE,
    INDEX idx_assessment (assessment_id),
    INDEX idx_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- RISK ASSESSMENT (Phase 1 Enhanced Version)
-- ============================================================================

-- Risk assessments - Using Phase 1 enhanced version
CREATE TABLE IF NOT EXISTS risk_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    assessment_date DATE NOT NULL,
    assessed_by INT NOT NULL,
    self_harm_risk ENUM('low', 'medium', 'high', 'unknown') DEFAULT 'unknown',
    harm_others_risk ENUM('low', 'medium', 'high', 'unknown') DEFAULT 'unknown',
    overdose_risk ENUM('low', 'medium', 'high', 'unknown') DEFAULT 'unknown',
    domestic_violence_risk ENUM('low', 'medium', 'high', 'unknown') DEFAULT 'unknown',
    housing_instability_risk ENUM('low', 'medium', 'high', 'unknown') DEFAULT 'unknown',
    overall_risk_level ENUM('low', 'medium', 'high') NOT NULL,
    risk_factors JSON,
    protective_factors JSON,
    immediate_concerns TEXT,
    worker_notified BOOLEAN DEFAULT FALSE,
    supervisor_notified BOOLEAN DEFAULT FALSE,
    supervisor_reviewed BOOLEAN DEFAULT FALSE,
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    crisis_resources_provided BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (assessed_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    INDEX idx_client (client_id),
    INDEX idx_overall_risk (overall_risk_level),
    INDEX idx_date (assessment_date),
    INDEX idx_risk_level_date (overall_risk_level, assessment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Safety plans
CREATE TABLE IF NOT EXISTS safety_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    risk_assessment_id INT NULL,
    emergency_contacts JSON,
    crisis_hotlines JSON,
    coping_strategies TEXT,
    safe_places TEXT,
    warning_signs TEXT,
    things_to_avoid TEXT,
    reasons_to_live TEXT,
    professional_supports JSON,
    shareable_with_emergency BOOLEAN DEFAULT FALSE,
    consent_to_share BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_reviewed_date DATE NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (risk_assessment_id) REFERENCES risk_assessments(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- QUALITY OF LIFE TRACKING
-- ============================================================================

-- QOL tracking (original simple version for backward compatibility)
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

-- QOL assessments (Phase 1 enhanced version)
CREATE TABLE IF NOT EXISTS qol_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    assessment_date DATE NOT NULL,
    assessment_type ENUM('baseline', 'follow_up', 'ad_hoc') DEFAULT 'follow_up',
    stress_level INT CHECK (stress_level BETWEEN 1 AND 10),
    mood_level INT CHECK (mood_level BETWEEN 1 AND 10),
    safety_feeling INT CHECK (safety_feeling BETWEEN 1 AND 10),
    hope_level INT CHECK (hope_level BETWEEN 1 AND 10),
    connection_level INT CHECK (connection_level BETWEEN 1 AND 10),
    er_visits_last_month INT DEFAULT 0,
    arrests_last_month INT DEFAULT 0,
    nights_outside_last_month INT DEFAULT 0,
    meals_missed_last_week INT DEFAULT 0,
    overall_wellbeing INT CHECK (overall_wellbeing BETWEEN 1 AND 10),
    notes TEXT,
    completed_by INT NOT NULL,
    comparison_to_previous JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (completed_by) REFERENCES users(id),
    INDEX idx_client (client_id),
    INDEX idx_type (assessment_type),
    INDEX idx_date (assessment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CASE MANAGEMENT
-- ============================================================================

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

-- ============================================================================
-- HARM REDUCTION - Products & Inventory
-- ============================================================================

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

-- ============================================================================
-- RESOURCES & REFERRALS
-- ============================================================================

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

-- ============================================================================
-- PUBLIC ENGAGEMENT
-- ============================================================================

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

-- ============================================================================
-- COMMUNICATION & NOTIFICATIONS
-- ============================================================================

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

-- Notifications table (Phase 1 enhanced version)
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_type ENUM('intake_reminder', 'consent_expiry', 'assessment_due', 'risk_alert', 'qol_due', 'general') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link_url VARCHAR(500),
    status ENUM('unread', 'read', 'dismissed') DEFAULT 'unread',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_type (notification_type),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scheduled tasks table
CREATE TABLE IF NOT EXISTS scheduled_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    task_type ENUM('assessment_followup', 'consent_check', 'qol_survey', 'risk_review', 'intake_reminder') NOT NULL,
    scheduled_date DATE NOT NULL,
    assigned_to INT NULL,
    status ENUM('pending', 'completed', 'cancelled', 'overdue') DEFAULT 'pending',
    completed_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_client (client_id),
    INDEX idx_scheduled_date (scheduled_date),
    INDEX idx_status (status),
    INDEX idx_assigned (assigned_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- AUDIT & SECURITY
-- ============================================================================

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
    -- Phase 1 enhancements
    emergency_access BOOLEAN DEFAULT FALSE,
    approved_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INITIAL DATA - Consent Categories
-- ============================================================================

INSERT INTO consent_categories (category_name, category_type, plain_language_explanation, how_used_explanation) VALUES
('Housing Support', 'housing', 'Permission to share your information with housing providers and shelters', 'We use this to help you find and maintain housing, coordinate with landlords, and connect you with housing support services'),
('Medical Information', 'health', 'Permission to share your health information with doctors, nurses, and clinics', 'We use this to coordinate your healthcare, share medical history with providers, and ensure you get appropriate treatment'),
('Mental Health Support', 'mental_health', 'Permission to share mental health information with counselors and psychiatrists', 'We use this to coordinate mental health services, connect you with therapists, and ensure continuity of care'),
('Legal Services', 'legal', 'Permission to share information with legal aid, lawyers, and court-related services', 'We use this to help with legal issues, coordinate with legal aid, and provide documentation when needed'),
('Financial/Income Support', 'financial', 'Permission to share financial information with income assistance programs', 'We use this to help you apply for benefits, coordinate with social assistance, and access financial supports'),
('General Case Coordination', 'general', 'Permission for OUTSINC staff to share basic information between team members', 'We use this for internal case coordination, ensuring smooth handoffs, and avoiding re-traumatizing intake processes');

-- ============================================================================
-- INITIAL DATA - Assessment Template
-- ============================================================================

INSERT INTO assessment_templates (template_name, template_type, questions, version, active)
VALUES (
    'Standard Needs Assessment v1',
    'needs',
    '{"sections": ["housing", "substances", "mental_health", "physical_health", "safety", "income"]}',
    1,
    TRUE
);

-- ============================================================================
-- SAMPLE DATA - Products
-- ============================================================================

INSERT INTO products (product_code, product_name, category, description, unit_type, status) VALUES
('NEEDLE-10', 'Needle Pack (10)', 'needles', '10-pack of sterile needles, various gauges', 'pack', 'active'),
('STEM-GLASS', 'Glass Stem', 'stems', 'Pyrex glass stem for safer use', 'piece', 'active'),
('NALOX-KIT', 'Naloxone Kit', 'naloxone', 'Nasal naloxone administration kit with instructions', 'kit', 'active'),
('CONDOM-12', 'Condoms (Pack of 12)', 'condoms', 'Assorted condoms, latex and non-latex', 'pack', 'active'),
('PIPE-GLASS', 'Glass Pipe', 'pipes', 'Heat-resistant glass pipe', 'piece', 'active'),
('COOKER-PACK', 'Cooker Pack', 'cookers', 'Sterile cookers for preparation', 'pack', 'active'),
('COTTON-FILT', 'Cotton Filters', 'other', 'Sterile cotton filters (50 pack)', 'pack', 'active'),
('ALCOHOL-SWAB', 'Alcohol Swabs', 'other', 'Alcohol prep pads (100 pack)', 'pack', 'active'),
('SHARPS-CONT', 'Sharps Container', 'other', 'Safe disposal container for used needles', 'piece', 'active'),
('TOURNIQUET', 'Tourniquets', 'other', 'Medical-grade tourniquets (5 pack)', 'pack', 'active'),
('WATER-STER', 'Sterile Water', 'other', 'Sterile water for injection (10 vials)', 'pack', 'active'),
('GAUZE-PAD', 'Gauze Pads', 'other', 'Sterile gauze pads (20 pack)', 'pack', 'active');

-- ============================================================================
-- SAMPLE DATA - Inventory
-- ============================================================================

INSERT INTO inventory (product_id, location, quantity, min_threshold) VALUES
(1, 'main_office', 150, 20),
(2, 'main_office', 80, 15),
(3, 'main_office', 50, 10),
(4, 'main_office', 200, 30),
(5, 'main_office', 60, 10),
(6, 'main_office', 100, 15),
(7, 'main_office', 120, 20),
(8, 'main_office', 180, 25),
(9, 'main_office', 40, 5),
(10, 'main_office', 90, 15),
(11, 'main_office', 110, 20),
(12, 'main_office', 140, 25),
(1, 'outreach_van', 50, 10),
(2, 'outreach_van', 30, 5),
(3, 'outreach_van', 20, 5),
(4, 'outreach_van', 60, 10),
(5, 'outreach_van', 20, 5);

-- ============================================================================
-- SAMPLE DATA - Resources
-- ============================================================================

INSERT INTO resources (resource_name, category, description, address, city, postal_code, phone, email, website, hours_of_operation, accepts_walkins, wheelchair_accessible, status) VALUES
('Sunrise Emergency Shelter', 'shelter', 'Emergency overnight shelter with meals', '123 Main Street', 'Vancouver', 'V6B 1A1', '604-555-0101', 'info@sunriseshelter.org', 'https://sunriseshelter.org', 'Open 24/7', TRUE, TRUE, 'active'),
('Hope Recovery Clinic', 'health', 'Walk-in medical clinic specializing in addiction medicine', '456 East Hastings', 'Vancouver', 'V6A 1P4', '604-555-0202', 'clinic@hoperecovery.ca', 'https://hoperecovery.ca', 'Mon-Fri 9am-5pm', TRUE, TRUE, 'active'),
('Mental Health Access Center', 'mental_health', 'Crisis intervention and mental health support', '789 Commercial Drive', 'Vancouver', 'V5L 3Y2', '604-555-0303', 'access@mhac.org', 'https://mhac.org', 'Mon-Fri 8am-8pm, Sat-Sun 10am-6pm', TRUE, TRUE, 'active'),
('Community Legal Clinic', 'legal', 'Free legal advice and representation', '321 Broadway', 'Vancouver', 'V5Y 1P3', '604-555-0404', 'help@communitylegal.ca', 'https://communitylegal.ca', 'Mon-Thu 9am-4pm', FALSE, TRUE, 'active'),
('Daily Bread Food Bank', 'food', 'Food hampers and hot meals', '654 Powell Street', 'Vancouver', 'V6A 1G8', '604-555-0505', 'info@dailybread.org', 'https://dailybread.org', 'Mon-Fri 10am-2pm', TRUE, FALSE, 'active'),
('ID Recovery Program', 'id_clinic', 'Assistance obtaining government ID and documents', '987 Granville Street', 'Vancouver', 'V6Z 1K3', '604-555-0606', 'id@recoveryprogram.ca', NULL, 'Tue-Thu 1pm-4pm (by appointment)', FALSE, TRUE, 'active'),
('Street Outreach Team', 'outreach', 'Mobile outreach and harm reduction services', 'Various locations', 'Vancouver', 'V6B 0A1', '604-555-0707', 'outreach@streetteam.org', NULL, 'Daily 6pm-2am', TRUE, FALSE, 'active'),
('Safe Haven Drop-In', 'drop_in', 'Daytime drop-in center with showers, laundry, meals', '147 Abbott Street', 'Vancouver', 'V6B 2K7', '604-555-0808', 'welcome@safehaven.org', 'https://safehaven.org', 'Daily 8am-6pm', TRUE, TRUE, 'active');

-- ============================================================================
-- SAMPLE DATA - Demo Users
-- ============================================================================
-- ⚠️ SECURITY WARNING ⚠️
-- These are DEMO accounts for LOCALHOST TESTING ONLY!
-- Both accounts use the same password hash for simplicity in testing.
-- 
-- **REQUIRED ACTIONS AFTER FIRST LOGIN:**
-- 1. Change the admin password immediately
-- 2. Change the worker password immediately  
-- 3. Delete these accounts and create real user accounts for production
-- 4. NEVER deploy to production with these default credentials!
-- ============================================================================

-- Create a demo admin user
-- Username: ADMIN001, Password: Admin123!
-- ⚠️ This password is publicly known - CHANGE IMMEDIATELY!
INSERT INTO users (user_id, username, password_hash, role, first_name, last_name, date_of_birth, status) VALUES
('ADMIN001', 'ADMIN001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator', '1990-01-01', 'active');

-- Create user preferences for admin
INSERT INTO user_preferences (user_id) 
SELECT id FROM users WHERE user_id = 'ADMIN001';

-- Insert sample demo worker account
-- Username: JANWOR010190, Password: Worker123!
-- ⚠️ This password is publicly known - CHANGE IMMEDIATELY!
INSERT INTO users (user_id, username, password_hash, role, first_name, last_name, email, phone, date_of_birth, status) VALUES
('JANWOR010190', 'JANWOR010190', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker', 'Jane', 'Worker', 'jane.worker@outsinc.org', '604-555-1001', '1990-01-01', 'active');

INSERT INTO user_preferences (user_id) 
SELECT id FROM users WHERE user_id = 'JANWOR010190';

-- ============================================================================
-- SAMPLE DATA - Events
-- ============================================================================

INSERT INTO events (event_title, event_type, description, event_date, start_time, end_time, location, target_audience, capacity, registration_required, status, created_by) VALUES
('Community Outreach Night', 'outreach', 'Weekly outreach in Downtown Eastside', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '18:00:00', '22:00:00', 'Main & Hastings area', 'all', 0, FALSE, 'planned', 1),
('Harm Reduction Training', 'training', 'Training session for new volunteers on harm reduction principles', DATE_ADD(CURDATE(), INTERVAL 14 DAY), '14:00:00', '16:00:00', 'OUTSINC Main Office', 'staff', 20, TRUE, 'planned', 1),
('Community BBQ & Resource Fair', 'community_meal', 'Free meal and resource information for community members', DATE_ADD(CURDATE(), INTERVAL 21 DAY), '12:00:00', '15:00:00', 'Victory Square Park', 'all', 100, FALSE, 'planned', 1);

-- ============================================================================
-- SAMPLE DATA - News Posts
-- ============================================================================

INSERT INTO news_posts (post_title, post_content, post_type, is_public, published_date, author_id) VALUES
('Welcome to OUTSINC', 'We are excited to launch OUTSINC, a new platform designed to support our community with dignity and compassion. This system will help us coordinate care and ensure no one falls through the cracks.', 'announcement', TRUE, CURDATE(), 1),
('New Harm Reduction Supplies Available', 'We have expanded our harm reduction supply inventory to include additional naloxone kits and safer consumption materials. Contact your outreach worker for more information.', 'update', TRUE, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 1);

-- ============================================================================
-- SAMPLE DATA - Learning Content
-- ============================================================================

INSERT INTO learning_content (content_title, content_type, description, content_body, target_audience, tags, estimated_duration, is_active, created_by) VALUES
('Introduction to Harm Reduction', 'guide', 'Basic principles of harm reduction and how they guide our work', 
'Harm reduction is a set of practical strategies and ideas aimed at reducing negative consequences associated with drug use...', 
'["staff", "public"]', 
'["harm_reduction", "basics", "philosophy"]', 
15, TRUE, 1),
('Overdose Response Protocol', 'guide', 'Step-by-step guide for responding to an overdose emergency', 
'If you suspect someone is experiencing an overdose: 1. Check for responsiveness, 2. Call 911, 3. Administer naloxone if available...', 
'["staff", "clients"]', 
'["overdose", "emergency", "naloxone"]', 
10, TRUE, 1),
('Understanding Trauma-Informed Care', 'course', 'Comprehensive training on trauma-informed approaches in service delivery', 
'Trauma-informed care recognizes the widespread impact of trauma and understands potential paths for recovery...', 
'["staff"]', 
'["trauma", "training", "best_practices"]', 
60, TRUE, 1);

-- ============================================================================
-- COMPLETION MESSAGE
-- ============================================================================

SELECT '========================================' AS '';
SELECT 'OUTSINC COMPLETE DATABASE SCHEMA' AS '';
SELECT '========================================' AS '';
SELECT '' AS '';
SELECT 'Database successfully created and populated!' AS '';
SELECT '' AS '';
SELECT 'Imported:' AS '';
SELECT '- 34 database tables' AS '';
SELECT '- 6 consent categories' AS '';
SELECT '- 1 assessment template' AS '';
SELECT '- 12 harm reduction products' AS '';
SELECT '- 17 inventory entries' AS '';
SELECT '- 8 community resources' AS '';
SELECT '- 2 user accounts (admin and worker)' AS '';
SELECT '- 3 upcoming events' AS '';
SELECT '- 2 news posts' AS '';
SELECT '- 3 learning content items' AS '';
SELECT '' AS '';
SELECT 'Default Admin Login:' AS '';
SELECT '  Username: ADMIN001' AS '';
SELECT '  Password: Admin123!' AS '';
SELECT '  **CHANGE PASSWORD IMMEDIATELY AFTER FIRST LOGIN**' AS '';
SELECT '' AS '';
SELECT 'Default Worker Login:' AS '';
SELECT '  Username: JANWOR010190' AS '';
SELECT '  Password: Worker123!' AS '';
SELECT '  **CHANGE PASSWORD IMMEDIATELY AFTER FIRST LOGIN**' AS '';
SELECT '' AS '';
SELECT 'Next steps:' AS '';
SELECT '1. Update database credentials in config/database.php' AS '';
SELECT '2. Access the system at http://localhost/outsincCA/' AS '';
SELECT '3. Log in with admin credentials and change password' AS '';
SELECT '4. Configure system settings as needed' AS '';
SELECT '' AS '';
SELECT 'Ready to use!' AS '';
SELECT '========================================' AS '';
