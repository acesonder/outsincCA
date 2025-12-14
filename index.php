<?php
/**
 * OUTSINC - Login & Registration Page
 * Outreach Someone In Need of Change
 */

require_once __DIR__ . '/config/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /public/dashboard.php');
    exit();
}

$pageTitle = 'Welcome to OUTSINC';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - OUTSINC</title>
    <meta name="description" content="OUTSINC - Outreach Someone In Need of Change. Person-centered support for homelessness, substance use, mental health, and crisis situations.">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <style>
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
            max-width: 1200px;
            padding: 2rem;
        }
        
        .hero-logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
            color: #2563eb;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        
        .hero-tagline {
            font-size: 1.5rem;
            margin-bottom: 3rem;
            opacity: 0.95;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .hero-button {
            padding: 1rem 2rem;
            font-size: 1.125rem;
            background: white;
            color: #2563eb;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .hero-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }
        
        .hero-button.secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            backdrop-filter: blur(10px);
        }
        
        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
        }
        
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-tagline {
                font-size: 1.25rem;
            }
        }
        
        .info-section {
            padding: 4rem 0;
            background: var(--bg-secondary);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .feature-card {
            background: var(--bg-primary);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .values-section {
            padding: 4rem 0;
            text-align: center;
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .value-card {
            padding: 1.5rem;
        }
        
        .value-card h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-logo">OS</div>
            <h1 class="hero-title">OUTSINC</h1>
            <p class="hero-tagline">Outreach Someone In Need of Change</p>
            <p style="font-size: 1.125rem; margin-bottom: 3rem; opacity: 0.9;">
                Meeting people where they're at, walking with them where they want to go.
            </p>
            <div class="hero-buttons">
                <button class="hero-button" onclick="OUTSINC.showModal('login-modal')">Sign In</button>
                <button class="hero-button secondary" onclick="OUTSINC.showModal('register-modal')">Get Support / Create Account</button>
            </div>
        </div>
    </section>

    <!-- Info Section -->
    <section class="info-section">
        <div class="container">
            <h2 class="section-header text-center">What We Do</h2>
            <p class="text-center" style="max-width: 800px; margin: 0 auto 2rem; font-size: 1.125rem;">
                OUTSINC is a unified, trauma-informed platform designed to support people facing homelessness, 
                substance use, mental health challenges, poverty, and related crises.
            </p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🤝</div>
                    <h3>Person-Centered Support</h3>
                    <p>You are the expert on your own life. We follow your priorities, your pace, and your definition of "better."</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">❤️</div>
                    <h3>Harm Reduction</h3>
                    <p>We support people who use substances in staying as safe as possible. No judgement, no requirements.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Privacy & Consent</h3>
                    <p>Your information is yours. Control who sees what, and change your mind anytime.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🌐</div>
                    <h3>Connected Care</h3>
                    <p>We work with local services to help you move between systems without falling through the cracks.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Accessible Tools</h3>
                    <p>Access your information, track your progress, and stay connected from any device.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">✨</div>
                    <h3>Lived Experience</h3>
                    <p>Built by people who have been there. Our team knows what makes a difference.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <div class="container">
            <h2 class="section-header">Our Core Values</h2>
            <div class="values-grid">
                <div class="value-card">
                    <h3>Acceptance</h3>
                    <p>We work with people exactly where they are, without judgement.</p>
                </div>
                <div class="value-card">
                    <h3>Dignity</h3>
                    <p>Everyone deserves respect, safety, and a place to belong.</p>
                </div>
                <div class="value-card">
                    <h3>Self-Determination</h3>
                    <p>You choose your goals. You can say yes, no, or "not right now."</p>
                </div>
                <div class="value-card">
                    <h3>Accessibility</h3>
                    <p>We remove barriers wherever we can to make help truly accessible.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>OUTSINC</h3>
                    <p>Outreach Someone In Need of Change</p>
                    <p>Lived experience. Real support. No wrong door.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <p><a href="#about">About Us</a></p>
                    <p><a href="#services">Services</a></p>
                    <p><a href="#resources">Resources</a></p>
                    <p><a href="#contact">Contact</a></p>
                </div>
                <div class="footer-section">
                    <h3>Get Help</h3>
                    <p>Crisis Line: 1-800-XXX-XXXX</p>
                    <p>Email: support@outsinc.org</p>
                    <p>Available 24/7</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> OUTSINC. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div class="modal-backdrop" id="login-backdrop">
        <div class="modal" id="login-modal">
            <div class="modal-header">
                <h2 class="modal-title">Sign In</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="login-form" data-validate>
                    <div class="form-group">
                        <label class="form-label required" for="login_username">Username</label>
                        <input type="text" id="login_username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required" for="login_password">Password</label>
                        <input type="password" id="login_password" name="password" class="form-control" required>
                    </div>
                    
                    <div id="login-message"></div>
                    
                    <button type="submit" class="btn btn-primary btn-3d" style="width: 100%;">
                        Sign In
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <a href="#" onclick="OUTSINC.closeModal('login-modal'); OUTSINC.showModal('forgot-password-modal'); return false;">
                        Forgot your password?
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal-backdrop" id="register-backdrop">
        <div class="modal" id="register-modal">
            <div class="modal-header">
                <h2 class="modal-title">Create Account</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Create an account to access support services. Your information is private and secure.</p>
                
                <form id="register-form" data-validate>
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label required" for="reg_first_name">First Name</label>
                            <input type="text" id="reg_first_name" name="first_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required" for="reg_last_name">Last Name</label>
                            <input type="text" id="reg_last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required" for="reg_dob">Date of Birth</label>
                        <input type="date" id="reg_dob" name="date_of_birth" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required" for="reg_security_question">Security Question</label>
                        <select id="reg_security_question" name="security_question" class="form-control" required>
                            <option value="">Choose a question...</option>
                            <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                            <option value="What city were you born in?">What city were you born in?</option>
                            <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                            <option value="What was your favorite food as a child?">What was your favorite food as a child?</option>
                            <option value="What is your favorite color?">What is your favorite color?</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required" for="reg_security_answer">Security Answer</label>
                        <input type="text" id="reg_security_answer" name="security_answer" class="form-control" required>
                        <div class="form-help">You'll use this to recover your account if you forget your password.</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required" for="reg_password">Password</label>
                        <input type="password" id="reg_password" name="password" class="form-control" minlength="8" required>
                        <div class="form-help">At least 8 characters</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required" for="reg_confirm_password">Confirm Password</label>
                        <input type="password" id="reg_confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <input type="hidden" name="role" value="client">
                    
                    <div id="register-message"></div>
                    
                    <button type="submit" class="btn btn-primary btn-3d" style="width: 100%;">
                        Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal-backdrop" id="forgot-password-backdrop">
        <div class="modal" id="forgot-password-modal">
            <div class="modal-header">
                <h2 class="modal-title">Reset Password</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="reset-step-1">
                    <p class="mb-3">Enter your details to verify your identity.</p>
                    
                    <form id="forgot-password-form">
                        <div class="form-group">
                            <label class="form-label required" for="forgot_first_name">First Name</label>
                            <input type="text" id="forgot_first_name" name="first_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required" for="forgot_last_name">Last Name</label>
                            <input type="text" id="forgot_last_name" name="last_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required" for="forgot_dob">Date of Birth</label>
                            <input type="date" id="forgot_dob" name="date_of_birth" class="form-control" required>
                        </div>
                        
                        <div id="forgot-message"></div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Continue
                        </button>
                    </form>
                </div>
                
                <div id="reset-step-2" style="display: none;">
                    <p class="mb-3">Your username is: <strong id="recovered-username"></strong></p>
                    <p class="mb-3">Answer your security question to reset your password.</p>
                    
                    <form id="reset-password-form">
                        <input type="hidden" id="reset_user_id" name="user_id">
                        
                        <div class="form-group">
                            <label class="form-label" id="security-question-label"></label>
                            <input type="text" id="security_answer" name="security_answer" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required" for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" minlength="8" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required" for="confirm_new_password">Confirm New Password</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" required>
                        </div>
                        
                        <div id="reset-message"></div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <div class="back-to-top">↑</div>

    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/auth.js"></script>
</body>
</html>
