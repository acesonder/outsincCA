# Phase 1 Implementation Plan
## Based on Questionnaire Responses

**Start Date**: December 14, 2024  
**Target Completion**: 10-12 weeks  
**Priority**: HIGH

---

## Implementation Order

### Week 1-2: Database Schema Updates & Core Infrastructure

1. **Database Changes**
   - Add `intake_sessions` table for pause/resume
   - Add fields to `clients` table for new intake data
   - Add `consent_categories` and enhance `consents` table
   - Add `assessment_templates` and `assessment_responses` tables
   - Add `risk_assessments` and `safety_plans` tables
   - Add `qol_assessments` table

2. **API Structure**
   - Create `/api/intake/` directory with endpoints
   - Create `/api/consents/` directory with endpoints
   - Create `/api/assessments/` directory with endpoints
   - Create `/api/risk/` directory with endpoints
   - Create `/api/qol/` directory with endpoints

### Week 3-4: Client Intake Wizard

**Features to Implement:**
- ✅ Multi-step wizard (7 steps)
- ✅ Self-service mode (all optional except: name, DOB, password, security Q&A)
- ✅ Worker-assisted mode
- ✅ Pause and resume at any time
- ✅ Reminder notifications for incomplete intake
- ✅ Skip / "I don't know" / "Prefer not to say" options
- ✅ Visual progress bar
- ✅ Auto-save after each page
- ✅ Summary/confirmation before submission
- ✅ PDF download capability
- ✅ Worker print capability
- ✅ Multi-language support
- ✅ Text-to-speech accessibility
- ✅ Photo/document upload
- ✅ Resource suggestions based on answers
- ✅ "Why are we asking?" explanations for each question

**Files to Create:**
- `/public/clients/intake-wizard.php` - Main wizard interface
- `/assets/js/intake-wizard.js` - Client-side logic
- `/assets/css/intake.css` - Styles
- `/api/intake/save-step.php` - Save progress
- `/api/intake/resume.php` - Resume session
- `/api/intake/complete.php` - Finalize intake
- `/api/intake/generate-pdf.php` - Generate PDF
- `/api/intake/upload-document.php` - Document upload

**Content Coverage:**
- Step 1: Basic Info (name, language, interpreter needs)
- Step 2: Contact & Location
- Step 3: Housing (status, duration)
- Step 4: Substance Use (harm reduction focused)
- Step 5: Physical & Mental Health (ID, health card, support)
- Step 6: Income & Legal
- Step 7: Review & Submit

### Week 5-6: Consent & Privacy Management

**Features to Implement:**
- ✅ Consent categorized by type (housing, medical, legal, etc.)
- ✅ Time limits on consent (client-set)
- ✅ Notifications before expiry
- ✅ Plain-language explanations
- ✅ Partial consent capability
- ✅ Digital signature capture
- ✅ Verbal consent with staff attestation
- ✅ Online revocation capability
- ✅ Confirmation step for revocation
- ✅ Immediate revocation effect
- ✅ Automatic notifications to agencies
- ✅ Complete history of consent changes
- ✅ Emergency override with supervisor approval
- ✅ Automatic audit log entries
- ✅ Legal exception tracking
- ✅ Client notification of emergency overrides
- ✅ Visual indicators (colors/icons) for status
- ✅ View exactly what information was shared
- ✅ Worker alerts when consent revoked
- ✅ Downloadable/printable consent forms

**Files to Create:**
- `/public/clients/consents.php` - Consent dashboard
- `/assets/js/consents.js` - Client-side logic
- `/assets/css/consents.css` - Styles
- `/api/consents/list.php` - Get client consents
- `/api/consents/grant.php` - Grant new consent
- `/api/consents/revoke.php` - Revoke consent
- `/api/consents/history.php` - Consent change history
- `/api/consents/emergency-override.php` - Emergency access
- `/api/consents/signature.php` - Save digital signature

### Week 7-8: Needs Assessment & Smart Surveys

**Features to Implement:**
- ✅ Assessment covering: housing, substances, mental health, safety, social support
- ✅ Dynamic questions based on previous answers
- ✅ Automatic skip of irrelevant sections
- ✅ Follow-up questions based on risk flags
- ✅ Multiple-choice and open-ended questions
- ✅ Validation for completeness
- ✅ Automatic risk score calculation per domain
- ✅ Immediate alerts to workers for high-risk answers
- ✅ Flag clients needing urgent intervention
- ✅ Risk scores visible to clients
- ✅ Automatic comparison to previous assessments
- ✅ Baseline required at intake
- ✅ Follow-up assessments every 6 months (auto-scheduled)
- ✅ Worker reminders for periodic assessments
- ✅ Client-initiated assessment updates
- ✅ Track completion rates for reporting
- ✅ Visual charts/graphs of results
- ✅ Client progress dashboard over time
- ✅ Side-by-side comparison for workers
- ✅ PDF export for external providers
- ✅ Feed into aggregate analytics

**Files to Create:**
- `/public/assessments/needs.php` - Assessment interface
- `/public/assessments/view-results.php` - Results viewer
- `/assets/js/assessments.js` - Logic and conditional branching
- `/assets/css/assessments.css` - Styles
- `/api/assessments/templates.php` - Available assessment types
- `/api/assessments/start.php` - Begin new assessment
- `/api/assessments/save-answers.php` - Save responses
- `/api/assessments/results.php` - Get scored results
- `/api/assessments/compare.php` - Compare assessments
- `/api/assessments/schedule.php` - Schedule follow-ups
- `/api/assessments/export-pdf.php` - Generate PDF

### Week 9-10: Risk Assessment & Safety Planning

**Features to Implement:**
- ✅ Risk assessment for: self-harm, harm to others, overdose, domestic violence, housing instability
- ✅ Automatic risk level assignment (low/medium/high)
- ✅ Color-coded risk levels
- ✅ High-risk flags on client profiles
- ✅ Automatic workflow actions (notifications, tasks)
- ✅ Supervisor review for high-risk cases
- ✅ Safety plans with: emergency contacts, crisis hotlines, coping strategies, safe places
- ✅ Printable wallet-sized safety plan cards
- ✅ Immediate notifications to assigned workers
- ✅ Notifications to supervisors/managers
- ✅ Crisis resource suggestions based on risk type
- ✅ Shareable with emergency services (with consent)
- ✅ Track crisis incidents and outcomes
- ✅ Timeline of all risk assessments
- ✅ Risk trend graphs (improving/worsening)
- ✅ Alerts for significant risk level changes
- ✅ Risk reduction tracking as outcome metric
- ✅ Client view of own risk info (simplified)

**Files to Create:**
- `/public/risk/assessment.php` - Risk assessment form
- `/public/risk/safety-plan.php` - Safety plan builder
- `/public/risk/timeline.php` - Risk history timeline
- `/assets/js/risk-assessment.js` - Logic and calculations
- `/assets/css/risk.css` - Styles
- `/api/risk/assess.php` - Submit risk assessment
- `/api/risk/calculate-level.php` - Calculate risk level
- `/api/risk/safety-plan.php` - Save/update safety plan
- `/api/risk/notify.php` - Send risk notifications
- `/api/risk/timeline.php` - Get risk history
- `/api/risk/trends.php` - Get trend data
- `/api/risk/print-card.php` - Generate wallet card

### Week 11-12: Quality of Life Tracking & Integration

**Features to Implement:**
- QOL baseline at intake
- Periodic QOL surveys (6 months)
- Simple client-rated scales (stress, mood, safety, hope, connection)
- Behavioral indicators (ER visits, arrests, nights outside)
- Before/after graphs
- Integration with all modules
- Notifications and reminders system
- PDF generation for all modules
- Testing and bug fixes

**Files to Create:**
- `/public/qol/survey.php` - QOL survey interface
- `/public/qol/results.php` - QOL results viewer
- `/assets/js/qol.js` - QOL logic
- `/assets/css/qol.css` - Styles
- `/api/qol/submit.php` - Submit QOL survey
- `/api/qol/results.php` - Get QOL results
- `/api/qol/trends.php` - Get trend data

---

## Database Schema Updates Required

```sql
-- Add intake_sessions table
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

-- Enhance clients table
ALTER TABLE clients 
ADD COLUMN preferred_contact_time VARCHAR(50),
ADD COLUMN intake_completed_at TIMESTAMP NULL,
ADD COLUMN intake_worker_id INT NULL,
ADD COLUMN documents_uploaded BOOLEAN DEFAULT FALSE,
ADD COLUMN substance_use_current ENUM('yes', 'no', 'sometimes', 'prefer_not', 'unknown') DEFAULT 'unknown',
ADD COLUMN naloxone_trained BOOLEAN DEFAULT FALSE,
ADD COLUMN health_card_status ENUM('yes', 'no', 'expired', 'dont_know', 'unknown') DEFAULT 'unknown',
ADD COLUMN income_source VARCHAR(100),
ADD COLUMN legal_issues BOOLEAN DEFAULT FALSE;

-- Add consent_categories table
CREATE TABLE IF NOT EXISTS consent_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    category_type ENUM('housing', 'health', 'mental_health', 'legal', 'financial', 'general') NOT NULL,
    description TEXT,
    plain_language_explanation TEXT,
    how_used_explanation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhance consents table
ALTER TABLE consents
ADD COLUMN category_id INT NULL,
ADD COLUMN partial_consent JSON,
ADD COLUMN digital_signature TEXT,
ADD COLUMN verbal_attestation_by INT NULL,
ADD COLUMN revoked_by INT NULL,
ADD COLUMN revoked_at TIMESTAMP NULL,
ADD COLUMN revocation_reason TEXT,
ADD COLUMN agencies_notified BOOLEAN DEFAULT FALSE,
ADD COLUMN emergency_override BOOLEAN DEFAULT FALSE,
ADD COLUMN emergency_approved_by INT NULL,
ADD COLUMN emergency_reason TEXT,
ADD COLUMN client_notified_of_override BOOLEAN DEFAULT FALSE,
ADD COLUMN time_limit_months INT NULL,
ADD COLUMN expiry_notification_sent BOOLEAN DEFAULT FALSE,
ADD FOREIGN KEY (category_id) REFERENCES consent_categories(id),
ADD FOREIGN KEY (verbal_attestation_by) REFERENCES users(id),
ADD FOREIGN KEY (revoked_by) REFERENCES users(id),
ADD FOREIGN KEY (emergency_approved_by) REFERENCES users(id);

-- Add assessment_templates table
CREATE TABLE IF NOT EXISTS assessment_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL,
    template_type ENUM('needs', 'risk', 'qol') NOT NULL,
    questions JSON NOT NULL,
    conditional_logic JSON,
    scoring_rules JSON,
    version INT DEFAULT 1,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhance needs_assessments table
ALTER TABLE needs_assessments
ADD COLUMN template_id INT,
ADD COLUMN social_support_score INT,
ADD COLUMN overall_risk_score INT,
ADD COLUMN risk_level ENUM('low', 'medium', 'high') DEFAULT 'low',
ADD COLUMN high_risk_flags JSON,
ADD COLUMN alerts_sent BOOLEAN DEFAULT FALSE,
ADD COLUMN worker_notified BOOLEAN DEFAULT FALSE,
ADD COLUMN supervisor_reviewed BOOLEAN DEFAULT FALSE,
ADD COLUMN reviewed_by INT NULL,
ADD COLUMN reviewed_at TIMESTAMP NULL,
ADD COLUMN follow_up_scheduled_date DATE NULL,
ADD COLUMN comparison_to_baseline JSON,
ADD FOREIGN KEY (template_id) REFERENCES assessment_templates(id),
ADD FOREIGN KEY (reviewed_by) REFERENCES users(id);

-- Add assessment_responses table
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
    FOREIGN KEY (assessment_id) REFERENCES needs_assessments(id) ON DELETE CASCADE,
    INDEX idx_assessment (assessment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add risk_assessments table
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

-- Add safety_plans table
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

-- Add qol_assessments table
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

-- Add intake_documents table
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
```

---

## Success Criteria

**Intake Wizard:**
- [ ] Client can complete entire intake self-service with only required fields
- [ ] Worker can assist client through intake
- [ ] Session can be paused and resumed successfully
- [ ] Auto-save works on every step
- [ ] PDF generation works
- [ ] Multi-language interface works
- [ ] Text-to-speech works
- [ ] Document upload works
- [ ] Resource suggestions appear based on answers

**Consent Management:**
- [ ] Client can grant/revoke consent online
- [ ] Digital signatures capture correctly
- [ ] Consent history displays accurately
- [ ] Emergency overrides require supervisor approval
- [ ] Notifications sent when consent changes
- [ ] Visual indicators show status clearly

**Needs Assessment:**
- [ ] Conditional logic works (questions appear/hide based on answers)
- [ ] Risk scores calculate automatically
- [ ] High-risk alerts trigger immediately
- [ ] Assessments schedule automatically every 6 months
- [ ] Comparison to previous assessments works
- [ ] Visual charts display correctly

**Risk Assessment & Safety Planning:**
- [ ] Risk levels calculate automatically
- [ ] High-risk cases notify supervisor
- [ ] Safety plans save and display correctly
- [ ] Wallet cards print correctly
- [ ] Risk timeline shows trend accurately
- [ ] Client view shows simplified risk info

**QOL Tracking:**
- [ ] Baseline QOL captured at intake
- [ ] Follow-up surveys schedule at 6 months
- [ ] Graphs show progress over time
- [ ] Behavioral indicators track correctly

---

## Next Steps After This Document

1. Update database schema with migration script
2. Begin Week 3-4: Implement intake wizard fully
3. Create all API endpoints systematically
4. Add comprehensive testing
5. Update documentation
6. Deploy to staging environment
7. User acceptance testing
8. Production deployment

**Estimated Total Effort**: 10-12 weeks with 1 full-time developer  
**Budget**: $40,000 - $50,000 (at $40-50/hour)
