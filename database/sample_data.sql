-- OUTSINC Sample Data
-- This file provides sample data for testing the OUTSINC platform
-- Run this AFTER importing schema.sql

USE outsinc_db;

-- Insert sample products for harm reduction ordering
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

-- Insert inventory for products
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

-- Insert sample resources
INSERT INTO resources (resource_name, category, description, address, city, postal_code, phone, email, website, hours_of_operation, accepts_walkins, wheelchair_accessible, status) VALUES
('Sunrise Emergency Shelter', 'shelter', 'Emergency overnight shelter with meals', '123 Main Street', 'Vancouver', 'V6B 1A1', '604-555-0101', 'info@sunriseshelter.org', 'https://sunriseshelter.org', 'Open 24/7', TRUE, TRUE, 'active'),
('Hope Recovery Clinic', 'health', 'Walk-in medical clinic specializing in addiction medicine', '456 East Hastings', 'Vancouver', 'V6A 1P4', '604-555-0202', 'clinic@hoperecovery.ca', 'https://hoperecovery.ca', 'Mon-Fri 9am-5pm', TRUE, TRUE, 'active'),
('Mental Health Access Center', 'mental_health', 'Crisis intervention and mental health support', '789 Commercial Drive', 'Vancouver', 'V5L 3Y2', '604-555-0303', 'access@mhac.org', 'https://mhac.org', 'Mon-Fri 8am-8pm, Sat-Sun 10am-6pm', TRUE, TRUE, 'active'),
('Community Legal Clinic', 'legal', 'Free legal advice and representation', '321 Broadway', 'Vancouver', 'V5Y 1P3', '604-555-0404', 'help@communitylegal.ca', 'https://communitylegal.ca', 'Mon-Thu 9am-4pm', FALSE, TRUE, 'active'),
('Daily Bread Food Bank', 'food', 'Food hampers and hot meals', '654 Powell Street', 'Vancouver', 'V6A 1G8', '604-555-0505', 'info@dailybread.org', 'https://dailybread.org', 'Mon-Fri 10am-2pm', TRUE, FALSE, 'active'),
('ID Recovery Program', 'id_clinic', 'Assistance obtaining government ID and documents', '987 Granville Street', 'Vancouver', 'V6Z 1K3', '604-555-0606', 'id@recoveryprogram.ca', NULL, 'Tue-Thu 1pm-4pm (by appointment)', FALSE, TRUE, 'active'),
('Street Outreach Team', 'outreach', 'Mobile outreach and harm reduction services', 'Various locations', 'Vancouver', 'V6B 0A1', '604-555-0707', 'outreach@streetteam.org', NULL, 'Daily 6pm-2am', TRUE, FALSE, 'active'),
('Safe Haven Drop-In', 'drop_in', 'Daytime drop-in center with showers, laundry, meals', '147 Abbott Street', 'Vancouver', 'V6B 2K7', '604-555-0808', 'welcome@safehaven.org', 'https://safehaven.org', 'Daily 8am-6pm', TRUE, TRUE, 'active');

-- Create a demo admin user
-- Username: ADMIN001, Password: Admin123! (should be changed immediately)
INSERT INTO users (user_id, username, password_hash, role, first_name, last_name, date_of_birth, status) VALUES
('ADMIN001', 'ADMIN001', '$2y$10$YourHashedPasswordHere', 'admin', 'System', 'Administrator', '1990-01-01', 'active');

-- Note: The password hash above is a placeholder. To create a real admin user:
-- 1. Register through the web interface, or
-- 2. Use PHP to generate a proper hash:
--    php -r "echo password_hash('YourPassword', PASSWORD_DEFAULT);"
-- 3. Then update this file with the real hash

-- Create user preferences for admin
INSERT INTO user_preferences (user_id) 
SELECT id FROM users WHERE user_id = 'ADMIN001';

-- Insert sample demo worker account
INSERT INTO users (user_id, username, password_hash, role, first_name, last_name, email, phone, date_of_birth, status) VALUES
('JANWOR010190', 'JANWOR010190', '$2y$10$YourHashedPasswordHere', 'worker', 'Jane', 'Worker', 'jane.worker@outsinc.org', '604-555-1001', '1990-01-01', 'active');

INSERT INTO user_preferences (user_id) 
SELECT id FROM users WHERE user_id = 'JANWOR010190';

-- Insert sample events
INSERT INTO events (event_title, event_type, description, event_date, start_time, end_time, location, target_audience, capacity, registration_required, status, created_by) VALUES
('Community Outreach Night', 'outreach', 'Weekly outreach in Downtown Eastside', '2024-01-20', '18:00:00', '22:00:00', 'Main & Hastings area', 'all', 0, FALSE, 'planned', 1),
('Harm Reduction Training', 'training', 'Training session for new volunteers on harm reduction principles', '2024-01-25', '14:00:00', '16:00:00', 'OUTSINC Main Office', 'staff', 20, TRUE, 'planned', 1),
('Community BBQ & Resource Fair', 'community_meal', 'Free meal and resource information for community members', '2024-02-01', '12:00:00', '15:00:00', 'Victory Square Park', 'all', 100, FALSE, 'planned', 1);

-- Insert sample news/updates
INSERT INTO news_posts (post_title, post_content, post_type, is_public, published_date, author_id) VALUES
('Welcome to OUTSINC', 'We are excited to launch OUTSINC, a new platform designed to support our community with dignity and compassion. This system will help us coordinate care and ensure no one falls through the cracks.', 'announcement', TRUE, '2024-01-01', 1),
('New Harm Reduction Supplies Available', 'We have expanded our harm reduction supply inventory to include additional naloxone kits and safer consumption materials. Contact your outreach worker for more information.', 'update', TRUE, '2024-01-10', 1);

-- Insert sample learning content
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

-- Add helpful comments
SELECT '========================================' AS '';
SELECT 'OUTSINC Sample Data Import Complete' AS '';
SELECT '========================================' AS '';
SELECT '' AS '';
SELECT 'Imported:' AS '';
SELECT '- 12 harm reduction products' AS '';
SELECT '- 17 inventory entries' AS '';
SELECT '- 8 community resources' AS '';
SELECT '- 2 user accounts (admin and worker)' AS '';
SELECT '- 3 upcoming events' AS '';
SELECT '- 2 news posts' AS '';
SELECT '- 3 learning content items' AS '';
SELECT '' AS '';
SELECT 'Next steps:' AS '';
SELECT '1. Update admin password through web interface' AS '';
SELECT '2. Configure database credentials in config/database.php' AS '';
SELECT '3. Access the system at http://your-domain.com' AS '';
SELECT '' AS '';
