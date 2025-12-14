-- PHASE 1 DATABASE SCHEMA UPDATES
-- Run this after importing the base schema.sql

USE outsinc_db;

-- ===========================================
-- INTAKE WIZARD TABLES
-- ===========================================

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

-- Enhance clients table with new intake fields
ALTER TABLE clients 
ADD COLUMN IF NOT EXISTS preferred_contact_time VARCHAR(50),
ADD COLUMN IF NOT EXISTS intake_completed_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS intake_worker_id INT NULL,
ADD COLUMN IF NOT EXISTS documents_uploaded BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS substance_use_current ENUM('yes', 'no', 'sometimes', 'prefer_not', 'unknown') DEFAULT 'unknown',
ADD COLUMN IF NOT EXISTS substance_types JSON,
ADD COLUMN IF NOT EXISTS naloxone_trained BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS health_card_status ENUM('yes', 'no', 'expired', 'dont_know', 'unknown') DEFAULT 'unknown',
ADD COLUMN IF NOT EXISTS income_source VARCHAR(100),
ADD COLUMN IF NOT EXISTS legal_issues BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS housing_duration VARCHAR(100);

-- ===========================================
-- CONSENT MANAGEMENT TABLES
-- ===========================================

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

-- Insert default consent categories
INSERT INTO consent_categories (category_name, category_type, plain_language_explanation, how_used_explanation) VALUES
('Housing Support', 'housing', 'Permission to share your information with housing providers and shelters', 'We use this to help you find and maintain housing, coordinate with landlords, and connect you with housing support services'),
('Medical Information', 'health', 'Permission to share your health information with doctors, nurses, and clinics', 'We use this to coordinate your healthcare, share medical history with providers, and ensure you get appropriate treatment'),
('Mental Health Support', 'mental_health', 'Permission to share mental health information with counselors and psychiatrists', 'We use this to coordinate mental health services, connect you with therapists, and ensure continuity of care'),
('Legal Services', 'legal', 'Permission to share information with legal aid, lawyers, and court-related services', 'We use this to help with legal issues, coordinate with legal aid, and provide documentation when needed'),
('Financial/Income Support', 'financial', 'Permission to share financial information with income assistance programs', 'We use this to help you apply for benefits, coordinate with social assistance, and access financial supports'),
('General Case Coordination', 'general', 'Permission for OUTSINC staff to share basic information between team members', 'We use this for internal case coordination, ensuring smooth handoffs, and avoiding re-traumatizing intake processes');

-- Enhance consents table
ALTER TABLE consents
ADD COLUMN IF NOT EXISTS category_id INT NULL,
ADD COLUMN IF NOT EXISTS partial_consent JSON,
ADD COLUMN IF NOT EXISTS digital_signature TEXT,
ADD COLUMN IF NOT EXISTS verbal_attestation_by INT NULL,
ADD COLUMN IF NOT EXISTS revoked_by INT NULL,
ADD COLUMN IF NOT EXISTS revoked_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS revocation_reason TEXT,
ADD COLUMN IF NOT EXISTS agencies_notified BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS emergency_override BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS emergency_approved_by INT NULL,
ADD COLUMN IF NOT EXISTS emergency_reason TEXT,
ADD COLUMN IF NOT EXISTS client_notified_of_override BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS time_limit_months INT NULL,
ADD COLUMN IF NOT EXISTS expiry_notification_sent BOOLEAN DEFAULT FALSE,
ADD CONSTRAINT fk_consent_category FOREIGN KEY (category_id) REFERENCES consent_categories(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_verbal_attestation FOREIGN KEY (verbal_attestation_by) REFERENCES users(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_revoked_by FOREIGN KEY (revoked_by) REFERENCES users(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_emergency_approved FOREIGN KEY (emergency_approved_by) REFERENCES users(id) ON DELETE SET NULL;

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

-- ===========================================
-- ASSESSMENTS TABLES
-- ===========================================

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

-- Enhance needs_assessments table
ALTER TABLE needs_assessments
ADD COLUMN IF NOT EXISTS template_id INT,
ADD COLUMN IF NOT EXISTS social_support_score INT,
ADD COLUMN IF NOT EXISTS overall_risk_score INT,
ADD COLUMN IF NOT EXISTS risk_level ENUM('low', 'medium', 'high') DEFAULT 'low',
ADD COLUMN IF NOT EXISTS high_risk_flags JSON,
ADD COLUMN IF NOT EXISTS alerts_sent BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS worker_notified BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS supervisor_reviewed BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS reviewed_by INT NULL,
ADD COLUMN IF NOT EXISTS reviewed_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS follow_up_scheduled_date DATE NULL,
ADD COLUMN IF NOT EXISTS comparison_to_baseline JSON,
ADD CONSTRAINT fk_assessment_template FOREIGN KEY (template_id) REFERENCES assessment_templates(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL;

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

-- ===========================================
-- RISK ASSESSMENT TABLES
-- ===========================================

-- Risk assessments
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
    INDEX idx_date (assessment_date)
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

-- ===========================================
-- QUALITY OF LIFE TABLES
-- ===========================================

-- QOL assessments
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

-- ===========================================
-- NOTIFICATIONS TABLE (for reminders)
-- ===========================================

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

-- ===========================================
-- SCHEDULED TASKS TABLE (for follow-ups)
-- ===========================================

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

-- ===========================================
-- AUDIT LOG ENHANCEMENTS
-- ===========================================

-- Add more detailed columns to audit_log if needed
ALTER TABLE audit_log
ADD COLUMN IF NOT EXISTS emergency_access BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS approved_by INT NULL,
ADD CONSTRAINT fk_audit_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL;

-- ===========================================
-- INDEXES FOR PERFORMANCE
-- ===========================================

-- Additional indexes for common queries
CREATE INDEX IF NOT EXISTS idx_clients_housing ON clients(housing_status);
CREATE INDEX IF NOT EXISTS idx_clients_intake_completed ON clients(intake_completed_at);
CREATE INDEX IF NOT EXISTS idx_consents_expiry ON consents(expiry_date);
CREATE INDEX IF NOT EXISTS idx_consents_status_type ON consents(status, consent_type);
CREATE INDEX IF NOT EXISTS idx_assessments_follow_up ON needs_assessments(follow_up_scheduled_date);
CREATE INDEX IF NOT EXISTS idx_risk_level_date ON risk_assessments(overall_risk_level, assessment_date);

-- ===========================================
-- COMPLETION
-- ===========================================

-- Insert initial assessment template (can be customized later)
INSERT INTO assessment_templates (template_name, template_type, questions, version, active)
VALUES (
    'Standard Needs Assessment v1',
    'needs',
    '{"sections": ["housing", "substances", "mental_health", "physical_health", "safety", "income"]}',
    1,
    TRUE
);

SELECT 'Phase 1 database schema updates completed successfully!' AS status;
