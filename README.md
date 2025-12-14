# OUTSINC - Outreach Someone In Need of Change

**Tagline:** Meeting people where they're at, walking with them where they want to go.

## Overview

OUTSINC is a unified, trauma-informed platform designed to support people facing homelessness, substance use, mental health challenges, poverty, and related crises. It helps outreach/shelter staff and service providers coordinate care, gives businesses/public a safe way to report concerns, and turns real experiences into data that drives systemic change.

## Core Principles

- **Person-centered, harm reduction, trauma-informed**
- **Lived experience drives design and feedback**
- **"Tell your story once"** – no more re-traumatizing info dump every new worker
- **Tech supports human relationships**, not the other way around

## Key Features

### User Roles
- **Client / Peer** - People accessing help and services
- **Outreach / Case Worker / Shelter Staff** - Direct support providers
- **Service Provider / Partner Agency** - External services and referral partners
- **Admin / System Coordinator** - System management and analytics
- **Public / Business / Community Member** - Reporting and information access

### Core Modules

1. **Access & Authentication** - Secure, role-based access control
2. **Client Intake & Registration** - Gentle, trauma-informed onboarding
3. **Consent & Privacy Management** - Explicit, changeable consent controls
4. **Needs Assessment & Smart Surveys** - Multi-dimensional assessment tools
5. **Risk Assessment & Safety Planning** - Structured risk identification and mitigation
6. **Quality of Life Tracking (QOL)** - Meaningful progress measurement
7. **Case Management & Notes** - Comprehensive client tracking
8. **Harm Reduction Ordering & Inventory** - Supply distribution and tracking
9. **Resource Directory & Referral Engine** - Service connection and coordination
10. **Public / Business Reporting & Response** - Community engagement and support
11. **Events, News & Community Engagement** - Information sharing and updates
12. **eLearning & Knowledge Library** - Educational resources and training
13. **Dashboards & Analytics** - Data visualization and reporting
14. **Admin & Configuration** - System management and customization
15. **Client Self-Service Portal** - Client access to their own information

## Technology Stack

- **Backend:** PHP 7.4+ with PDO
- **Database:** MySQL 5.7+ / MariaDB 10.3+
- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Design:** Responsive, mobile-first, 3D UI elements, gradients
- **Accessibility:** WCAG 2.1 AA compliant, screen reader support, keyboard navigation

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.3 or higher
- Apache or Nginx web server
- Composer (optional, for dependency management)

### Setup Instructions

1. **Clone the repository:**
   ```bash
   git clone https://github.com/acesonder/outsincCA.git
   cd outsincCA
   ```

2. **Configure the database:**
   - Create a new MySQL database:
     ```sql
     CREATE DATABASE outsinc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     ```
   - Import the database schema:
     ```bash
     mysql -u your_username -p outsinc_db < database/schema.sql
     ```

3. **Update configuration:**
   - Edit `config/database.php` with your database credentials:
     ```php
     private $host = 'localhost';
     private $db_name = 'outsinc_db';
     private $username = 'your_username';
     private $password = 'your_password';
     ```

4. **Set up web server:**
   - Point your web server document root to the repository directory
   - Ensure `.htaccess` is enabled (for Apache) or configure URL rewriting (for Nginx)
   - Set appropriate permissions:
     ```bash
     chmod -R 755 .
     chmod -R 775 uploads/
     ```

5. **Access the application:**
   - Navigate to `http://localhost` (or your configured domain)
   - The login/registration page will appear

### Creating the First Admin User

To create an admin user, you can either:

**Option 1: Register through the UI and manually update the database:**
1. Register a new account through the web interface
2. Note the generated user_id
3. Update the user role in the database:
   ```sql
   UPDATE users SET role = 'admin' WHERE user_id = 'YOUR_USER_ID';
   ```

**Option 2: Manually insert into the database:**
```sql
INSERT INTO users (user_id, username, password_hash, role, first_name, last_name, date_of_birth, status)
VALUES ('ADMIN01', 'ADMIN01', '$2y$10$your_hashed_password_here', 'admin', 'Admin', 'User', '1990-01-01', 'active');
```

## Usage

### For Clients

1. **Create an Account:**
   - Click "Get Support / Create Account" on the homepage
   - Fill in basic information (name, date of birth, security question)
   - Your unique username will be generated automatically (e.g., MICBRO050684)
   - Save your username - you'll need it to log in

2. **Access Your Dashboard:**
   - View your goals, appointments, and connected resources
   - Update your information and preferences
   - Access learning materials and resources

### For Workers/Staff

1. **Client Management:**
   - Add new clients through intake process
   - Track case notes, goals, and tasks
   - Create referrals to partner services
   - Manage harm reduction supply orders

2. **Documentation:**
   - Record contact logs and case notes
   - Complete assessments (needs, risk, QOL)
   - Track outcomes and progress

### For Administrators

1. **System Configuration:**
   - Manage user accounts and roles
   - Configure modules and features
   - Customize forms and workflows
   - View analytics and generate reports

2. **Data Management:**
   - Export reports for funders
   - Monitor system usage
   - Review audit logs

## Design Features

### 3D UI Elements
- Buttons with 3D perspective effects
- Cards with depth and hover animations
- Gradient backgrounds throughout
- Smooth transitions and animations

### Accessibility Features
- Light and dark mode (with auto-detection)
- High contrast mode
- Adjustable font sizes (small, medium, large)
- Dyslexia-friendly font option
- Keyboard navigation support
- Screen reader compatible
- ARIA labels and landmarks

### Responsive Design
- Mobile-first approach
- Hamburger menu for mobile
- Touch-friendly interface
- Adaptive layouts for all screen sizes

## Security Features

- Password hashing with bcrypt
- Session management with timeout
- Failed login attempt tracking and account lockout
- Role-based access control (RBAC)
- Audit logging for all actions
- SQL injection prevention with prepared statements
- XSS protection
- CSRF token support (to be implemented)

## Privacy & Consent

OUTSINC takes privacy seriously:
- Clients control who can access their information
- Per-agency consent management
- History of consent changes tracked
- Emergency/legal exception notices
- Data retention policies
- Right to be forgotten support

## Support & Resources

- **Crisis Line:** 1-800-XXX-XXXX (24/7)
- **Email:** support@outsinc.org
- **Documentation:** [Link to full documentation]
- **Training Materials:** Available in the eLearning module

## Contributing

We welcome contributions from the community, especially those with lived experience. Please see CONTRIBUTING.md for guidelines.

## License

[To be determined - consider using a license that supports community use and social good]

## Acknowledgments

Built by and for people with lived experience of homelessness, substance use, mental health challenges, and systemic barriers. This platform exists because of the courage, resilience, and wisdom of those who navigate these systems every day.

## Vision

We envision a community where:
- No one is left outside without options
- People can access help without shame, judgement, or impossible rules
- Lived and living experience is treated as expertise
- Systems work together instead of pushing people around
- Housing, health, safety, and belonging are treated as basic human rights

---

**OUTSINC - Lived experience. Real support. No wrong door.** 
