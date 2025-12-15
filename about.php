<?php
/**
 * OUTSINC - About Page
 * Public information about OUTSINC
 */

$pageTitle = 'About OUTSINC';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - OUTSINC</title>
    <meta name="description" content="Learn about OUTSINC - a trauma-informed platform for supporting people facing homelessness, substance use, and mental health challenges.">
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/styles.css'); ?>">
</head>
<body>
    <!-- Simple Top Bar for Public Pages -->
    <nav class="navbar navbar-3d">
        <div class="container">
            <div class="navbar-container">
                <a href="<?php echo asset_url('index.php'); ?>" class="navbar-brand">
                    <span>OUTSINC</span>
                </a>
                <ul class="navbar-menu">
                    <li class="navbar-item"><a href="<?php echo asset_url('index.php'); ?>" class="navbar-link">Home</a></li>
                    <li class="navbar-item"><a href="<?php echo asset_url('about.php'); ?>" class="navbar-link active">About</a></li>
                    <li class="navbar-item"><a href="#services" class="navbar-link">Services</a></li>
                    <li class="navbar-item"><a href="#contact" class="navbar-link">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 4rem 0; color: white; text-align: center;">
        <div class="container">
            <h1 style="font-size: 3rem; margin-bottom: 1rem;">About OUTSINC</h1>
            <p style="font-size: 1.25rem; opacity: 0.95;">Outreach Someone In Need of Change</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container" style="padding: 4rem 0;">
        <div style="max-width: 900px; margin: 0 auto;">
            <section style="margin-bottom: 3rem;">
                <h2 class="section-header">Our Story</h2>
                <p style="font-size: 1.125rem; line-height: 1.8;">
                    OUTSINC (Outreach Someone In Need of Change) was created by people who have lived it – 
                    sleeping outside, navigating shelters, facing addiction, fighting for housing, and trying 
                    to make sense of systems that often feel cold and confusing.
                </p>
                <p style="font-size: 1.125rem; line-height: 1.8;">
                    We know what it's like to tell your story over and over and still not get the help you need. 
                    We also know what a difference it makes when even one person shows up, listens without judgement, 
                    and walks through the hard parts with you instead of talking down at you.
                </p>
                <p style="font-size: 1.125rem; line-height: 1.8;">
                    OUTSINC exists to be that kind of support.
                </p>
            </section>

            <section style="margin-bottom: 3rem;">
                <h2 class="section-header">What We Do</h2>
                <p style="font-size: 1.125rem; line-height: 1.8;">
                    We are a community-based, peer-informed support and navigation service. Our team works 
                    alongside people who are homeless, under-housed, using substances, dealing with mental 
                    health challenges, or simply feeling stuck. We help you figure out what you want to work 
                    on next, what options are available, and how to get there — step by step, at your pace.
                </p>
                <p style="font-size: 1.125rem; line-height: 1.8;">
                    <strong>We are not here to "fix" you. We are here to stand with you.</strong>
                </p>
            </section>

            <section style="margin-bottom: 3rem;">
                <h2 class="section-header">Our Mission</h2>
                <div class="card" style="padding: 2rem; background: linear-gradient(135deg, var(--bg-secondary), var(--bg-tertiary));">
                    <p style="font-size: 1.125rem; line-height: 1.8; margin: 0;">
                        To provide compassionate, person-centered support and navigation for people impacted 
                        by homelessness, substance use, mental health challenges, poverty, and systemic barriers — 
                        meeting people where they are, helping them define their own goals, and walking with them 
                        as they build safer, more stable, and more hopeful lives.
                    </p>
                </div>
            </section>

            <section style="margin-bottom: 3rem;">
                <h2 class="section-header">Our Vision</h2>
                <p style="font-size: 1.125rem; line-height: 1.8;">
                    We envision a community where:
                </p>
                <ul style="font-size: 1.125rem; line-height: 2;">
                    <li>No one is left outside without options</li>
                    <li>People can access help without shame, judgement, or impossible rules</li>
                    <li>Lived and living experience is treated as expertise</li>
                    <li>Systems work together instead of pushing people around</li>
                    <li>Housing, health, safety, and belonging are treated as basic human rights</li>
                </ul>
            </section>

            <section style="margin-bottom: 3rem;">
                <h2 class="section-header">Our Core Values</h2>
                <div class="grid grid-2" style="gap: 2rem; margin-top: 2rem;">
                    <div class="card" style="padding: 1.5rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">🤝 Person-Centered</h3>
                        <p>You are the expert on your own life. We follow your priorities, your pace, and your definition of "better."</p>
                    </div>
                    
                    <div class="card" style="padding: 1.5rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">✨ Lived Experience</h3>
                        <p>Many of our team members and peers have been homeless, used substances, or navigated the same systems. That experience shapes everything we do.</p>
                    </div>
                    
                    <div class="card" style="padding: 1.5rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">❤️ Harm Reduction</h3>
                        <p>We support people who use substances in staying as safe as possible. We don't require abstinence to offer help.</p>
                    </div>
                    
                    <div class="card" style="padding: 1.5rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">♿ Accessibility</h3>
                        <p>We remove barriers wherever we can: flexible contact options, multiple ways to connect, plain language, mobile-friendly tools.</p>
                    </div>
                    
                    <div class="card" style="padding: 1.5rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">🎯 Self-Determination</h3>
                        <p>You choose your goals. You can say yes, no, or "not right now." We help you make informed choices, not choices for you.</p>
                    </div>
                    
                    <div class="card" style="padding: 1.5rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">🙏 Acceptance</h3>
                        <p>We work with people exactly where they are, without judgement. Everyone deserves dignity, safety, and belonging.</p>
                    </div>
                </div>
            </section>

            <section style="margin-bottom: 3rem;">
                <h2 class="section-header">Our Approach</h2>
                <div class="card" style="padding: 2rem;">
                    <p style="font-size: 1.125rem; line-height: 1.8; margin-bottom: 1rem;">
                        <strong>We partner</strong> with local services, outreach teams, shelters, hospitals, 
                        and community groups to make it easier to move between systems without falling through 
                        the cracks.
                    </p>
                    <p style="font-size: 1.125rem; line-height: 1.8; margin-bottom: 1rem;">
                        <strong>We work</strong> with businesses, neighbours, and community members who want to 
                        respond to visible homelessness, drug use, and crisis with care instead of shame.
                    </p>
                    <p style="font-size: 1.125rem; line-height: 1.8; margin: 0;">
                        <strong>We build</strong> on harm reduction, human rights, and the belief that everyone 
                        deserves dignity, safety, and a place to belong — regardless of income, substance use, 
                        housing status, or what their life looks like right now.
                    </p>
                </div>
            </section>

            <section style="margin-bottom: 3rem; text-align: center; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 3rem; border-radius: 1rem; color: white;">
                <h2 style="margin-bottom: 1rem;">Get Involved</h2>
                <p style="font-size: 1.125rem; margin-bottom: 2rem;">
                    Whether you need support, want to volunteer, or are interested in partnering with us, 
                    we'd love to hear from you.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="<?php echo asset_url('index.php'); ?>" class="btn" style="background: white; color: var(--primary-color); text-decoration: none;">
                        Get Support
                    </a>
                    <a href="#contact" class="btn btn-outline" style="border-color: white; color: white; text-decoration: none;">
                        Contact Us
                    </a>
                </div>
            </section>
        </div>
    </div>

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
                    <p><a href="<?php echo asset_url('index.php'); ?>">Home</a></p>
                    <p><a href="<?php echo asset_url('about.php'); ?>">About Us</a></p>
                    <p><a href="#services">Services</a></p>
                    <p><a href="#resources">Resources</a></p>
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

    <!-- Back to Top -->
    <div class="back-to-top">↑</div>

    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
