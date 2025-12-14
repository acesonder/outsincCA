# OUTSINC Testing & Verification Checklist

This document provides a comprehensive checklist for testing and verifying the OUTSINC platform before deployment.

## Pre-Deployment Verification

### ✅ Phase 1: File Integrity

- [x] All PHP files have valid syntax
- [x] All JavaScript files are present and properly formatted
- [x] All CSS files contain valid styles
- [x] Database schema is complete with all required tables
- [x] API endpoints are present and accessible
- [x] Documentation files are complete

**Status**: ✓ PASSED - All files verified (see `./verify.sh`)

### ✅ Phase 2: Database Schema

- [x] Schema creates 23 tables successfully
- [x] Foreign key constraints are properly defined (36 constraints)
- [x] Indexes are created for performance (51 indexes)
- [x] UTF-8 character set configured
- [x] All required tables present:
  - users, clients, user_preferences
  - needs_assessments, risk_assessments, qol_tracking
  - case_notes, goals, tasks
  - products, inventory, orders, order_items
  - resources, referrals
  - public_reports, events, news_posts
  - learning_content, messages, notifications
  - consents, audit_log

**Status**: ✓ PASSED - Schema validated (see `./test-db-import.sh`)

## Manual Testing Checklist

### Phase 3: Authentication & User Management

#### Registration Flow
- [ ] Navigate to homepage (index.php)
- [ ] Click "Get Support / Create Account"
- [ ] Fill in registration form with test data
- [ ] Verify unique user ID is generated (format: FIRSTLASTMMDDYY)
- [ ] Verify password requirements (minimum 8 characters)
- [ ] Complete registration
- [ ] Verify success message displays with generated user ID
- [ ] Note the user ID for login

**Expected Result**: User account created successfully with auto-generated ID

#### Login Flow
- [ ] Navigate to homepage
- [ ] Click "Sign In"
- [ ] Enter username (user ID from registration)
- [ ] Enter password
- [ ] Click "Sign In" button
- [ ] Verify redirect to dashboard
- [ ] Verify user name displays in navigation

**Expected Result**: Successful login and redirect to role-appropriate dashboard

#### Password Recovery Flow
- [ ] Click "Forgot your password?"
- [ ] Enter first name, last name, date of birth
- [ ] Click "Continue"
- [ ] Verify username is displayed
- [ ] Verify security question is shown
- [ ] Answer security question
- [ ] Enter new password
- [ ] Confirm new password
- [ ] Submit password reset
- [ ] Verify success message
- [ ] Login with new password

**Expected Result**: Password reset successful and can login with new credentials

#### Failed Login Attempt Protection
- [ ] Attempt login with wrong password 5 times
- [ ] Verify account lockout message appears
- [ ] Wait 15 minutes or reset via database
- [ ] Verify can login after lockout period

**Expected Result**: Account locks after 5 failed attempts

### Phase 4: Dashboard & Navigation

#### Client Dashboard
- [ ] Login as client role user
- [ ] Verify dashboard shows:
  - Active goals count
  - Upcoming appointments
  - Connected resources
  - Unread messages
- [ ] Verify quick actions are present:
  - Update My Info
  - Set New Goal
  - Book Appointment
  - Send Message
- [ ] Click each navigation menu item
- [ ] Verify appropriate pages load

**Expected Result**: Client dashboard displays with role-appropriate content

#### Worker Dashboard
- [ ] Login as worker role user (create via database)
- [ ] Verify dashboard shows:
  - Active clients count
  - Pending tasks
  - Due items today
  - New reports
- [ ] Verify quick actions are present:
  - Add Client
  - New Order
  - Add Case Note
  - View Reports
- [ ] Verify navigation includes:
  - Clients menu (dropdown)
  - Orders link
- [ ] Click each navigation menu item
- [ ] Verify appropriate pages load

**Expected Result**: Worker dashboard displays with worker-appropriate content

### Phase 5: Harm Reduction Ordering System

#### Creating a New Order
- [ ] Login as worker
- [ ] Navigate to Orders → New Order (or click "New Order" from dashboard)
- [ ] Verify order page loads correctly
- [ ] Verify all 10 product cards are displayed
- [ ] Click on a product card
- [ ] Verify quantity badge appears on card
- [ ] Click same product again
- [ ] Verify quantity increments
- [ ] Verify order summary updates in real-time
- [ ] Select multiple different products
- [ ] Verify all appear in order summary

**Expected Result**: Products can be selected and order summary updates

#### Client Selection
- [ ] On order page, click "Add New Client" button
- [ ] Fill in client information:
  - First name
  - Last name
  - Preferred name (optional)
  - Phone (optional)
- [ ] Submit form
- [ ] Verify client added successfully
- [ ] Verify client appears in dropdown
- [ ] Verify client is auto-selected
- [ ] Select a different client from dropdown
- [ ] Verify selected client name updates in summary

**Expected Result**: Can add new clients and select from dropdown

#### Product Search & Filter
- [ ] Type "needle" in search box
- [ ] Verify only needle products are shown
- [ ] Clear search
- [ ] Select "Naloxone" from category filter
- [ ] Verify only naloxone products are shown
- [ ] Select "All Categories"
- [ ] Verify all products are shown again

**Expected Result**: Search and filter work correctly

#### Delivery Options
- [ ] Select "Pickup" option
- [ ] Verify pickup section displays
- [ ] Select a pickup location
- [ ] Click a time slot
- [ ] Verify time slot is highlighted
- [ ] Switch to "Dropoff" option
- [ ] Verify dropoff section displays
- [ ] Enter dropoff address
- [ ] Select dropoff time
- [ ] Verify delivery method updates in summary

**Expected Result**: Pickup and dropoff options work correctly

#### Order Submission
- [ ] Fill in additional instructions (optional)
- [ ] Add case notes (optional)
- [ ] Verify bottom action bar shows total items
- [ ] Click "Confirm Order" button
- [ ] Verify success message displays
- [ ] Verify redirect to order view page (once implemented)

**Expected Result**: Order is created successfully

#### Order Summary Controls
- [ ] Add products to order
- [ ] In order summary, click "-" button
- [ ] Verify quantity decreases
- [ ] Click "+" button
- [ ] Verify quantity increases
- [ ] Click delete button on an item
- [ ] Verify item is removed from summary
- [ ] Verify product card is deselected

**Expected Result**: Order summary controls work correctly

### Phase 6: About Page & Public Content

#### About Page
- [ ] Navigate to /about.php
- [ ] Verify page loads correctly
- [ ] Verify all sections are present:
  - Our Story
  - What We Do
  - Our Mission
  - Our Vision
  - Our Core Values
  - Our Approach
  - Get Involved
- [ ] Click navigation links
- [ ] Verify smooth scrolling to sections
- [ ] Click "Get Support" button
- [ ] Verify redirects to registration

**Expected Result**: About page displays correctly with all content

### Phase 7: Responsive Design

#### Mobile Testing
- [ ] Open site on mobile device or use browser dev tools
- [ ] Set viewport to mobile size (375px width)
- [ ] Verify hamburger menu appears
- [ ] Click hamburger menu
- [ ] Verify menu opens
- [ ] Test all pages:
  - Homepage
  - About
  - Login/Registration modals
  - Dashboard
  - Order page
- [ ] Verify product grid adjusts for mobile
- [ ] Verify order summary is scrollable
- [ ] Verify bottom action bar appears
- [ ] Verify touch interactions work

**Expected Result**: All pages are mobile-responsive

#### Tablet Testing
- [ ] Set viewport to tablet size (768px width)
- [ ] Test all pages
- [ ] Verify layouts adapt appropriately
- [ ] Verify navigation works

**Expected Result**: All pages work well on tablet

### Phase 8: Accessibility Features

#### Theme Switching
- [ ] Open any page
- [ ] Open browser console
- [ ] Type: `OUTSINC.setTheme('dark')`
- [ ] Verify page switches to dark mode
- [ ] Type: `OUTSINC.setTheme('light')`
- [ ] Verify page switches to light mode

**Expected Result**: Theme switching works

#### High Contrast Mode
- [ ] In console, type: `OUTSINC.setContrast('high')`
- [ ] Verify colors change to high contrast
- [ ] Verify text is easily readable

**Expected Result**: High contrast mode works

#### Font Size Adjustment
- [ ] In console, type: `OUTSINC.setFontSize('large')`
- [ ] Verify text size increases
- [ ] Type: `OUTSINC.setFontSize('small')`
- [ ] Verify text size decreases
- [ ] Type: `OUTSINC.setFontSize('medium')`
- [ ] Verify text returns to normal

**Expected Result**: Font size adjustment works

#### Keyboard Navigation
- [ ] Use Tab key to navigate through forms
- [ ] Verify focus indicators are visible
- [ ] Use Enter key to submit forms
- [ ] Use Esc key to close modals
- [ ] Verify all interactive elements are reachable

**Expected Result**: Full keyboard navigation works

### Phase 9: Security Testing

#### SQL Injection Prevention
- [ ] Try login with: `' OR '1'='1` as username
- [ ] Verify login fails
- [ ] Try registration with SQL in fields
- [ ] Verify data is escaped properly

**Expected Result**: SQL injection attempts fail

#### XSS Prevention
- [ ] Try entering: `<script>alert('XSS')</script>` in form fields
- [ ] Verify script is not executed
- [ ] Verify data is escaped in display

**Expected Result**: XSS attempts are prevented

#### Session Management
- [ ] Login successfully
- [ ] Note session ID from cookies
- [ ] Close browser
- [ ] Reopen and try to access dashboard directly
- [ ] Verify session is maintained (if recent)
- [ ] Wait for session timeout
- [ ] Try to access dashboard
- [ ] Verify redirect to login

**Expected Result**: Session management works correctly

## Database Import Testing

### Import Schema
```bash
# Create test database
mysql -u root -p -e "CREATE DATABASE outsinc_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p outsinc_test < database/schema.sql

# Verify tables
mysql -u root -p outsinc_test -e "SHOW TABLES;"

# Check for errors
mysql -u root -p outsinc_test -e "SHOW WARNINGS;"
```

**Expected Result**: All 23 tables created with no errors

### Import Sample Data
```bash
# Import sample data
mysql -u root -p outsinc_test < database/sample_data.sql

# Verify data
mysql -u root -p outsinc_test -e "SELECT COUNT(*) FROM products;"
mysql -u root -p outsinc_test -e "SELECT COUNT(*) FROM resources;"
```

**Expected Result**: Sample data imported successfully

## Automated Testing Results

### Verification Script
```bash
./verify.sh
```

**Expected Output**: All 10 tests pass

### Database Import Test
```bash
./test-db-import.sh
```

**Expected Output**: Schema validation passes

## Known Limitations & Future Work

### Current Limitations
- [ ] No actual email sending (password recovery is manual)
- [ ] Products are sample data only (need to add real inventory)
- [ ] No real-time notifications (placeholder implementation)
- [ ] No file upload functionality yet
- [ ] Limited validation on some forms

### Recommended Next Steps
1. Implement remaining core modules:
   - Client intake wizard
   - Consent management
   - Needs/risk assessments
   - Case management interface
2. Add email integration
3. Implement real-time notifications
4. Add file upload capability
5. Enhance form validation
6. Create admin user management interface
7. Build analytics and reporting dashboards

## Sign-off

### Developer Testing
- [ ] All automated tests pass
- [ ] All manual tests completed
- [ ] Known issues documented
- [ ] Code reviewed and approved

**Tested by**: _________________
**Date**: _________________
**Signature**: _________________

### Stakeholder Review
- [ ] Features meet requirements
- [ ] User interface is acceptable
- [ ] Documentation is complete
- [ ] Ready for deployment

**Reviewed by**: _________________
**Date**: _________________
**Signature**: _________________

## Support & Issues

If you encounter any issues during testing:
1. Check the INSTALL.md for setup instructions
2. Review the IMPLEMENTATION_SUMMARY.md for feature details
3. Open an issue on GitHub: https://github.com/acesonder/outsincCA/issues
4. Contact: support@outsinc.org

---

**OUTSINC - Lived experience. Real support. No wrong door.**
