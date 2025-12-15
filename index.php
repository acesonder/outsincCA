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

        /* New landing sections */
        .section {
            padding: 4rem 0;
            background: var(--bg-primary);
        }

        .section.alt {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.05), rgba(124, 58, 237, 0.05));
        }

        .section-heading {
            text-align: center;
            max-width: 900px;
            margin: 0 auto 3rem;
        }

        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: var(--secondary-color);
            font-size: 0.85rem;
        }

        .section-subtitle {
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }

        .navbar-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .nav-cta {
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #fff;
            padding: 0.6rem 1rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 600;
            transition: all var(--transition-fast);
        }

        .nav-cta:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.28);
        }

        .role-grid,
        .workflow-grid,
        .wizard-grid,
        .feature-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
        }

        .role-card,
        .workflow-card,
        .wizard-card,
        .gallery-card {
            background: var(--bg-secondary);
            padding: 1.5rem;
            border-radius: 1.1rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
            transition: all var(--transition-base);
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .role-card:hover,
        .workflow-card:hover,
        .wizard-card:hover,
        .gallery-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-xl);
        }

        .role-badge,
        .step-badge,
        .wizard-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.8rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
            font-weight: 700;
            font-size: 0.85rem;
        }

        .screenshot-frame {
            margin: 1rem 0;
            border-radius: 0.9rem;
            overflow: hidden;
            background: linear-gradient(145deg, rgba(37, 99, 235, 0.2), rgba(124, 58, 237, 0.2));
            padding: 0.75rem;
            position: relative;
            box-shadow: var(--shadow-lg);
        }

        .screenshot-window {
            background: #0f172a;
            border-radius: 0.75rem;
            min-height: 180px;
            position: relative;
            color: #e5e7eb;
            padding: 1rem;
            display: grid;
            gap: 0.65rem;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .screenshot-row {
            display: flex;
            gap: 0.5rem;
        }

        .screenshot-pill {
            height: 10px;
            flex: 1;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            opacity: 0.8;
        }

        .screenshot-block {
            height: 36px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 0.5rem;
        }

        .feature-list {
            list-style: none;
            display: grid;
            gap: 0.45rem;
            color: var(--text-secondary);
            padding-left: 0;
        }

        .feature-list li::before {
            content: '•';
            margin-right: 0.5rem;
            color: var(--secondary-color);
            font-weight: 700;
        }

        .workflow-steps {
            display: grid;
            gap: 0.4rem;
            margin-top: 1rem;
        }

        .workflow-step {
            padding: 0.75rem;
            border-radius: 0.85rem;
            background: var(--bg-primary);
            border: 1px solid rgba(37, 99, 235, 0.08);
            display: flex;
            gap: 0.65rem;
            align-items: center;
        }

        .step-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            box-shadow: var(--shadow-sm);
        }

        .wizard-card h4 {
            margin-top: 0.5rem;
            margin-bottom: 0.35rem;
        }

        .wizard-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            background: rgba(124, 58, 237, 0.1);
            color: var(--secondary-color);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .unlock-card {
            background: var(--bg-secondary);
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 1.5rem;
        }

        .unlock-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .unlock-row input {
            flex: 1;
            min-width: 220px;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border-color);
            font-size: 1rem;
        }

        .alert {
            border-radius: 0.75rem;
            padding: 0.9rem 1rem;
            margin-top: 0.75rem;
            background: rgba(37, 99, 235, 0.08);
            color: var(--text-primary);
        }

        .alert.success {
            background: rgba(16, 185, 129, 0.15);
        }

        .alert.error {
            background: rgba(239, 68, 68, 0.15);
        }

        .gradient-border {
            position: relative;
        }

        .gradient-border::after {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(135deg, rgba(37,99,235,0.4), rgba(124,58,237,0.4));
            -webkit-mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        .microcopy {
            font-size: 0.95rem;
            color: var(--text-secondary);
        }

        @media (max-width: 768px) {
            .section {
                padding: 3rem 0;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .navbar-actions {
                flex-direction: column;
                align-items: flex-start;
            }

            .unlock-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <a id="top"></a>
    <header class="navbar navbar-3d">
        <div class="container navbar-container">
            <a class="navbar-brand" href="#top">
                <img src="/assets/img/logo.svg" alt="OUTSINC logo" class="navbar-logo">
                <span>OUTSINC</span>
            </a>
            <div class="hamburger" aria-label="Toggle navigation" role="button" tabindex="0">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </div>
            <ul class="navbar-menu">
                <li class="navbar-item"><a class="navbar-link" href="#roles">Roles</a></li>
                <li class="navbar-item"><a class="navbar-link" href="#workflows">Workflows</a></li>
                <li class="navbar-item"><a class="navbar-link" href="#install">Install Wizard</a></li>
                <li class="navbar-item"><a class="navbar-link" href="#analytics">Analytics</a></li>
                <li class="navbar-item"><a class="navbar-link" href="#contact">Support</a></li>
                <li class="navbar-item">
                    <div class="navbar-actions">
                        <button class="nav-cta" onclick="OUTSINC.showModal('login-modal')">Sign In</button>
                        <button class="nav-cta" onclick="OUTSINC.showModal('register-modal')">Create Account</button>
                        <button class="nav-cta" onclick="OUTSINC.showModal('forgot-password-modal')">Forgot Password</button>
                    </div>
                </li>
            </ul>
        </div>
    </header>
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
                <button class="hero-button" id="view-install">View Install Wizard</button>
            </div>
        </div>
    </section>

    <!-- Role Experiences Section -->
    <section class="section alt" id="roles">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Role-centered experiences</p>
                <h2>Every account type has a guided, visualized journey</h2>
                <p class="section-subtitle">Screenshots, workflows, and safeguards tailored to clients, staff, service providers, and admins.</p>
            </div>
            <div class="role-grid">
                <div class="role-card gradient-border">
                    <span class="role-badge">Client / Peer</span>
                    <div class="screenshot-frame" aria-label="Client dashboard screenshot">
                        <div class="screenshot-window">
                            <div class="screenshot-row">
                                <div class="screenshot-pill" style="flex:2"></div>
                                <div class="screenshot-pill" style="flex:1"></div>
                            </div>
                            <div class="screenshot-block"></div>
                            <div class="screenshot-block" style="height:22px;width:70%"></div>
                            <div class="screenshot-row">
                                <div class="screenshot-block" style="flex:1"></div>
                                <div class="screenshot-block" style="flex:1"></div>
                            </div>
                            <div class="microcopy">Mobile-first cards for goals, appointments, consent, and safety plans.</div>
                        </div>
                    </div>
                    <ul class="feature-list">
                        <li>Guided intake with trauma-informed prompts</li>
                        <li>Consent + privacy controls per agency</li>
                        <li>Quality of Life tracking with milestones</li>
                        <li>Self-serve learning library & resource links</li>
                    </ul>
                </div>

                <div class="role-card gradient-border">
                    <span class="role-badge">Outreach Staff</span>
                    <div class="screenshot-frame" aria-label="Staff workspace screenshot">
                        <div class="screenshot-window">
                            <div class="screenshot-block" style="height:28px;width:80%"></div>
                            <div class="screenshot-row">
                                <div class="screenshot-block" style="flex:1"></div>
                                <div class="screenshot-block" style="flex:1"></div>
                                <div class="screenshot-block" style="flex:1"></div>
                            </div>
                            <div class="screenshot-block" style="height:28px;width:60%"></div>
                            <div class="microcopy">Case notes, visit logs, harm reduction inventory, and referrals in one view.</div>
                        </div>
                    </div>
                    <ul class="feature-list">
                        <li>Rapid client lookup + intake queue</li>
                        <li>Structured case notes & contact logs</li>
                        <li>Smart referrals with service directory</li>
                        <li>Offline-friendly order entry for harm reduction</li>
                    </ul>
                </div>

                <div class="role-card gradient-border">
                    <span class="role-badge">Service Providers</span>
                    <div class="screenshot-frame" aria-label="Provider portal screenshot">
                        <div class="screenshot-window">
                            <div class="screenshot-row">
                                <div class="screenshot-block" style="flex:1"></div>
                                <div class="screenshot-block" style="flex:1"></div>
                            </div>
                            <div class="screenshot-block" style="height:24px;width:50%"></div>
                            <div class="screenshot-block" style="height:24px;width:70%"></div>
                            <div class="microcopy">Referral intake, scheduling, and status visibility with audit logging.</div>
                        </div>
                    </div>
                    <ul class="feature-list">
                        <li>Referral acceptance and triage workflows</li>
                        <li>Shared care plans with permissions</li>
                        <li>Appointment coordination + notifications</li>
                        <li>Outcome reporting back to referring staff</li>
                    </ul>
                </div>

                <div class="role-card gradient-border">
                    <span class="role-badge">Admins</span>
                    <div class="screenshot-frame" aria-label="Admin analytics screenshot">
                        <div class="screenshot-window">
                            <div class="screenshot-pill" style="width:50%;height:10px"></div>
                            <div class="screenshot-row">
                                <div class="screenshot-block" style="flex:1"></div>
                                <div class="screenshot-block" style="flex:1"></div>
                            </div>
                            <div class="screenshot-block" style="height:70px"></div>
                            <div class="microcopy">Dashboards, audit logs, RBAC, theme controls, and deployment tooling.</div>
                        </div>
                    </div>
                    <ul class="feature-list">
                        <li>Role-based access with granular permissions</li>
                        <li>Configurable forms, themes, and languages</li>
                        <li>Analytics, uptime indicators, and error reporting</li>
                        <li>Bulk account creation and resets</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Workflow Section -->
    <section class="section" id="workflows">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">End-to-end workflows</p>
                <h2>Animations, color transitions, and clear steps for every feature</h2>
                <p class="section-subtitle">Each feature is shown as a guided workflow so teams know what happens next.</p>
            </div>
            <div class="workflow-grid">
                <div class="workflow-card">
                    <span class="step-badge">Client Journey</span>
                    <div class="workflow-steps">
                        <div class="workflow-step"><span class="step-number">1</span> Intake with accessibility toggles + consent capture</div>
                        <div class="workflow-step"><span class="step-number">2</span> Needs & risk assessments with animated status updates</div>
                        <div class="workflow-step"><span class="step-number">3</span> Goal + QOL tracking with color-coded progress</div>
                        <div class="workflow-step"><span class="step-number">4</span> Self-service resources, learning, and check-ins</div>
                    </div>
                </div>
                <div class="workflow-card">
                    <span class="step-badge">Staff & Providers</span>
                    <div class="workflow-steps">
                        <div class="workflow-step"><span class="step-number">1</span> Assign, triage, and schedule outreach visits</div>
                        <div class="workflow-step"><span class="step-number">2</span> Capture case notes and harm reduction orders</div>
                        <div class="workflow-step"><span class="step-number">3</span> Create referrals and monitor provider responses</div>
                        <div class="workflow-step"><span class="step-number">4</span> Close loops with outcome + safety plans</div>
                    </div>
                </div>
                <div class="workflow-card">
                    <span class="step-badge">Admin & Ops</span>
                    <div class="workflow-steps">
                        <div class="workflow-step"><span class="step-number">1</span> Configure modules, RBAC, and UI theme presets</div>
                        <div class="workflow-step"><span class="step-number">2</span> Import/update database tables with rollback safety</div>
                        <div class="workflow-step"><span class="step-number">3</span> Monitor analytics, audit logs, and error reports</div>
                        <div class="workflow-step"><span class="step-number">4</span> Run deployment & troubleshooting checklists</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Feature Gallery -->
    <section class="section alt" id="features">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Feature gallery</p>
                <h2>Screenshots of core modules and UI elements</h2>
                <p class="section-subtitle">Every module highlighted with gradients, micro-animations, and status cues.</p>
            </div>
            <div class="feature-gallery">
                <div class="gallery-card">
                    <div class="screenshot-frame">
                        <div class="screenshot-window">
                            <div class="screenshot-block" style="height:26px;width:60%"></div>
                            <div class="screenshot-row">
                                <div class="screenshot-block" style="flex:1"></div>
                                <div class="screenshot-block" style="flex:1"></div>
                            </div>
                            <div class="screenshot-block" style="height:30px"></div>
                        </div>
                    </div>
                    <h3>Dashboard & Analytics</h3>
                    <p class="microcopy">KPI cards, map tiles, QOL scores, and live service capacity displays.</p>
                </div>
                <div class="gallery-card">
                    <div class="screenshot-frame">
                        <div class="screenshot-window">
                            <div class="screenshot-row">
                                <div class="screenshot-block" style="flex:1"></div>
                                <div class="screenshot-block" style="flex:1"></div>
                            </div>
                            <div class="screenshot-block" style="height:20px;width:70%"></div>
                            <div class="screenshot-block" style="height:38px"></div>
                        </div>
                    </div>
                    <h3>Consent & Privacy</h3>
                    <p class="microcopy">Per-agency consent sliders, history timeline, and role-based visibility.</p>
                </div>
                <div class="gallery-card">
                    <div class="screenshot-frame">
                        <div class="screenshot-window">
                            <div class="screenshot-pill" style="width:55%;height:10px"></div>
                            <div class="screenshot-block" style="height:28px"></div>
                            <div class="screenshot-row">
                                <div class="screenshot-block" style="flex:1"></div>
                                <div class="screenshot-block" style="flex:1"></div>
                            </div>
                        </div>
                    </div>
                    <h3>Orders & Inventory</h3>
                    <p class="microcopy">Harm reduction ordering with signatures, stock counts, and audit trails.</p>
                </div>
                <div class="gallery-card">
                    <div class="screenshot-frame">
                        <div class="screenshot-window">
                            <div class="screenshot-block" style="height:22px;width:70%"></div>
                            <div class="screenshot-block" style="height:50px"></div>
                            <div class="screenshot-row">
                                <div class="screenshot-block" style="flex:1"></div>
                                <div class="screenshot-block" style="flex:1"></div>
                            </div>
                        </div>
                    </div>
                    <h3>Learning & Resources</h3>
                    <p class="microcopy">Micro-courses, policy docs, and quick links with completion tracking.</p>
                </div>
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

    <!-- Install Wizard Section -->
    <section class="section" id="install">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Deployment & install wizard</p>
                <h2>Protected control center for setup, recovery, and customization</h2>
                <p class="section-subtitle">Enter the deployment passcode to unlock database, UI, analytics, networking, and troubleshooting tools.</p>
            </div>

            <div class="unlock-card">
                <form id="install-wizard-form">
                    <label for="wizard-passcode" class="form-label required">Deployment passcode</label>
                    <div class="unlock-row">
                        <input type="password" id="wizard-passcode" name="wizard-passcode" placeholder="Enter deployment passcode" autocomplete="off" required aria-describedby="install-wizard-status">
                        <button type="submit" class="btn btn-primary btn-3d">Unlock tools</button>
                    </div>
                    <div id="install-wizard-status" class="alert">Protected: passcode required to access install and deployment workflows.</div>
                </form>
            </div>

            <div id="install-wizard-protected" hidden>
                <div class="wizard-grid">
                    <div class="wizard-card">
                        <span class="wizard-badge">Database</span>
                        <h4>Connect & import</h4>
                        <p class="microcopy">Guides for connecting databases, importing tables, and applying schema updates safely.</p>
                        <div class="wizard-actions">
                            <span class="pill">Connect database</span>
                            <span class="pill">Import tables</span>
                            <span class="pill">Update schema</span>
                        </div>
                    </div>
                    <div class="wizard-card">
                        <span class="wizard-badge">Recovery</span>
                        <h4>Fix errors & reset accounts</h4>
                        <p class="microcopy">Rollback table updates, repair indices, trigger account creation, and reset credentials.</p>
                        <div class="wizard-actions">
                            <span class="pill">Error fixer</span>
                            <span class="pill">Account creation</span>
                            <span class="pill">Account resets</span>
                        </div>
                    </div>
                    <div class="wizard-card">
                        <span class="wizard-badge">UI & Themes</span>
                        <h4>Customize the experience</h4>
                        <p class="microcopy">Adjust UI elements, gradients, 3D buttons, and theme presets with live previews.</p>
                        <div class="wizard-actions">
                            <span class="pill">UI element editor</span>
                            <span class="pill">Theme configurator</span>
                            <span class="pill">Accessibility presets</span>
                        </div>
                    </div>
                    <div class="wizard-card">
                        <span class="wizard-badge">Observability</span>
                        <h4>Error logs & analytics</h4>
                        <p class="microcopy">Streamlined error log reporting, troubleshooting steps, and analytics dashboards.</p>
                        <div class="wizard-actions">
                            <span class="pill">Error log reporter</span>
                            <span class="pill">Troubleshooter</span>
                            <span class="pill">Analytics</span>
                        </div>
                    </div>
                    <div class="wizard-card">
                        <span class="wizard-badge">Networking</span>
                        <h4>Connectivity & rollout</h4>
                        <p class="microcopy">Status of APIs, webhooks, and partner integrations with quick rollback actions.</p>
                        <div class="wizard-actions">
                            <span class="pill">Network checks</span>
                            <span class="pill">Webhook monitor</span>
                            <span class="pill">Rollback plan</span>
                        </div>
                    </div>
                    <div class="wizard-card">
                        <span class="wizard-badge">Deployment</span>
                        <h4>Final checklist</h4>
                        <p class="microcopy">Pre-flight checks, backups, audit log review, and green/blue deployment toggles.</p>
                        <div class="wizard-actions">
                            <span class="pill">Backup</span>
                            <span class="pill">Audit review</span>
                            <span class="pill">Go-live</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Analytics & Ops Section -->
    <section class="section alt" id="analytics">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Analytics & operations</p>
                <h2>Visibility for admins, networking, and troubleshooting</h2>
                <p class="section-subtitle">Real-time indicators, alerts, and role-based insights ensure smooth operations.</p>
            </div>
            <div class="workflow-grid">
                <div class="workflow-card">
                    <span class="step-badge">Dashboards</span>
                    <p class="microcopy">Track outreach throughput, QOL lifts, referral acceptance, and supply utilization.</p>
                    <div class="workflow-steps">
                        <div class="workflow-step"><span class="step-number">1</span> KPI cards with color transitions</div>
                        <div class="workflow-step"><span class="step-number">2</span> Map layers for hotspots & shelters</div>
                        <div class="workflow-step"><span class="step-number">3</span> Export-ready reports for funders</div>
                    </div>
                </div>
                <div class="workflow-card">
                    <span class="step-badge">Reliability</span>
                    <p class="microcopy">Network checks, uptime beacons, and API health with quick triage buttons.</p>
                    <div class="workflow-steps">
                        <div class="workflow-step"><span class="step-number">1</span> Monitor service latency & errors</div>
                        <div class="workflow-step"><span class="step-number">2</span> Trigger log capture + share</div>
                        <div class="workflow-step"><span class="step-number">3</span> Route incidents to admins</div>
                    </div>
                </div>
                <div class="workflow-card">
                    <span class="step-badge">Support</span>
                    <p class="microcopy">Dedicated contact, admin escalations, and self-serve troubleshooting flows.</p>
                    <div class="workflow-steps">
                        <div class="workflow-step"><span class="step-number">1</span> Guided wizard for common issues</div>
                        <div class="workflow-step"><span class="step-number">2</span> Automated checks & fixes</div>
                        <div class="workflow-step"><span class="step-number">3</span> Live escalation to admins</div>
                    </div>
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
                    <p><a id="contact" href="mailto:support@outsinc.org">Contact</a></p>
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
    <script>
        // Install wizard unlock
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('install-wizard-form');
            const status = document.getElementById('install-wizard-status');
            const protectedPanel = document.getElementById('install-wizard-protected');

            form?.addEventListener('submit', (event) => {
                event.preventDefault();
                const passcode = document.getElementById('wizard-passcode')?.value.trim();

                fetch('/api/auth/unlock-install.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ passcode })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        status.textContent = 'Deployment toolkit unlocked. All install, troubleshooting, and analytics tools are ready.';
                        status.classList.remove('error');
                        status.classList.add('success');
                        protectedPanel?.removeAttribute('hidden');
                        if (window.AnimationUtils && typeof AnimationUtils.fadeIn === 'function') {
                            AnimationUtils.fadeIn(protectedPanel);
                        }
                    } else {
                        status.textContent = data.message || 'Access denied: incorrect deployment passcode.';
                        status.classList.remove('success');
                        status.classList.add('error');
                    }
                })
                .catch(() => {
                    status.textContent = 'Unable to unlock right now. Please try again.';
                    status.classList.remove('success');
                    status.classList.add('error');
                });
            });

            document.getElementById('view-install')?.addEventListener('click', () => {
                const section = document.getElementById('install');
                if (section) {
                    section.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
