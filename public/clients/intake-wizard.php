<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/Auth.php';

$auth = new Auth($conn);

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: ../../index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$pageTitle = 'Client Intake Wizard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - OUTSINC</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/intake-wizard.css">
</head>
<body>
    <?php include '../includes/nav.php'; ?>
    
    <div class="wizard-container">
        <div class="wizard-header">
            <h1>Welcome to OUTSINC</h1>
            <p class="subtitle">Tell us only what you're comfortable sharing today. You can skip any question.</p>
            
            <!-- Accessibility Controls -->
            <div class="accessibility-controls">
                <label>
                    <input type="checkbox" id="tts-toggle"> Text-to-Speech
                </label>
                <select id="language-selector">
                    <option value="en">English</option>
                    <option value="es">Español</option>
                    <option value="fr">Français</option>
                </select>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="progress-bar-container">
            <div class="progress-bar">
                <div class="progress-fill" style="width: 14%;"></div>
            </div>
            <p class="progress-text">Step 1 of 7 (14%)</p>
        </div>
        
        <!-- Step 1: Basic Information -->
        <div id="step-1" class="wizard-step">
            <h2>Step 1: Basic Information</h2>
            <p class="step-description">Let's start with some basic information so we can help you.</p>
            
            <div class="question-block">
                <label class="question-label">What name would you like us to use? <span class="required">*</span></label>
                <input type="text" name="preferred_name" class="form-control" required>
                <button class="why-asking-btn" data-question="name">Why ask?</button>
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">First Name (legal)</label>
                <input type="text" name="first_name" class="form-control">
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">Last Name (legal)</label>
                <input type="text" name="last_name" class="form-control">
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control">
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">How can we contact you?</label>
                <input type="tel" name="phone" class="form-control" placeholder="Phone number">
                <input type="email" name="email" class="form-control" placeholder="Email (optional)">
                <textarea name="other_contact" class="form-control" rows="2" placeholder="Other way to reach you (e.g., 'Find me at library on Tuesdays')"></textarea>
                <button class="btn-skip">Skip</button>
            </div>
        </div>
        
        <!-- Step 2: Housing & Safety -->
        <div id="step-2" class="wizard-step" style="display: none;">
            <h2>Step 2: Housing & Safety</h2>
            <p class="step-description">Understanding your housing situation helps us connect you with shelter and support.</p>
            
            <div class="question-block">
                <label class="question-label">Where are you staying tonight?</label>
                <button class="why-asking-btn" data-question="housing">Why ask?</button>
                <select name="housing_status" class="form-control">
                    <option value="">Select...</option>
                    <option value="homeless">Outside / no shelter</option>
                    <option value="shelter">Emergency shelter</option>
                    <option value="couch_surfing">Couch surfing / temporary</option>
                    <option value="unstable">Have housing but unstable</option>
                    <option value="stable">Stable housing</option>
                    <option value="prefer_not_say">Prefer not to say</option>
                    <option value="unknown">I don't know</option>
                </select>
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block" data-show-if="housing_status=homeless">
                <label class="question-label">How long have you been without shelter?</label>
                <select name="homeless_duration" class="form-control">
                    <option value="">Select...</option>
                    <option value="less_week">Less than a week</option>
                    <option value="1_4_weeks">1-4 weeks</option>
                    <option value="1_6_months">1-6 months</option>
                    <option value="6_12_months">6-12 months</option>
                    <option value="over_year">Over a year</option>
                </select>
            </div>
            
            <div class="question-block">
                <label class="question-label">Do you feel safe where you're staying?</label>
                <div class="radio-group">
                    <label><input type="radio" name="feel_safe" value="yes"> Yes</label>
                    <label><input type="radio" name="feel_safe" value="no"> No</label>
                    <label><input type="radio" name="feel_safe" value="sometimes"> Sometimes</label>
                    <label><input type="radio" name="feel_safe" value="unknown"> I don't know</label>
                </div>
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">Emergency Contact (optional)</label>
                <input type="text" name="emergency_contact" class="form-control" placeholder="Name">
                <input type="tel" name="emergency_phone" class="form-control" placeholder="Phone">
                <button class="btn-skip">Skip</button>
            </div>
        </div>
        
        <!-- Step 3: Health & Substances -->
        <div id="step-3" class="wizard-step" style="display: none;">
            <h2>Step 3: Health & Substance Use</h2>
            <p class="step-description">This helps us provide harm reduction supplies and connect you with health services. All confidential.</p>
            
            <div class="question-block">
                <label class="question-label">Do you use any substances?</label>
                <button class="why-asking-btn" data-question="substances">Why ask?</button>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="substances[]" value="alcohol"> Alcohol</label>
                    <label><input type="checkbox" name="substances[]" value="cannabis"> Cannabis</label>
                    <label><input type="checkbox" name="substances[]" value="opioids"> Opioids (heroin, fentanyl)</label>
                    <label><input type="checkbox" name="substances[]" value="stimulants"> Stimulants (meth, crack, cocaine)</label>
                    <label><input type="checkbox" name="substances[]" value="benzos"> Benzos</label>
                    <label><input type="checkbox" name="substances[]" value="other"> Other</label>
                    <label><input type="checkbox" name="substances[]" value="none"> None</label>
                </div>
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">Would you like harm reduction supplies? (needles, naloxone, safer smoking equipment, etc.)</label>
                <div class="radio-group">
                    <label><input type="radio" name="harm_reduction" value="yes"> Yes</label>
                    <label><input type="radio" name="harm_reduction" value="no"> No</label>
                    <label><input type="radio" name="harm_reduction" value="later"> Maybe later</label>
                </div>
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">Do you have any health concerns right now?</label>
                <button class="why-asking-btn" data-question="physical_health">Why ask?</button>
                <textarea name="health_concerns" class="form-control" rows="3" placeholder="Any injuries, pain, or health issues you want help with"></textarea>
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">Do you have a family doctor or clinic?</label>
                <div class="radio-group">
                    <label><input type="radio" name="has_doctor" value="yes"> Yes</label>
                    <label><input type="radio" name="has_doctor" value="no"> No</label>
                    <label><input type="radio" name="has_doctor" value="need_one"> No, but I need one</label>
                </div>
                <button class="btn-skip">Skip</button>
            </div>
        </div>
        
        <!-- Step 4: Mental Health & Support -->
        <div id="step-4" class="wizard-step" style="display: none;">
            <h2>Step 4: Mental Health & Well-being</h2>
            <p class="step-description">We can connect you with counseling, crisis support, or just someone to talk to.</p>
            
            <div class="question-block">
                <label class="question-label">Would you like support for mental health or emotional well-being?</label>
                <button class="why-asking-btn" data-question="mental_health">Why ask?</button>
                <div class="radio-group">
                    <label><input type="radio" name="mental_health_support" value="yes"> Yes</label>
                    <label><input type="radio" name="mental_health_support" value="no"> No</label>
                    <label><input type="radio" name="mental_health_support" value="maybe"> Maybe / Not sure</label>
                </div>
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">Are you currently receiving mental health services?</label>
                <div class="radio-group">
                    <label><input type="radio" name="receiving_mh_services" value="yes"> Yes</label>
                    <label><input type="radio" name="receiving_mh_services" value="no"> No</label>
                    <label><input type="radio" name="receiving_mh_services" value="past"> In the past</label>
                </div>
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">On a scale of 1-10, how would you rate your stress level right now?</label>
                <input type="range" name="qol_stress" min="1" max="10" value="5" class="slider">
                <output>5</output>
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">On a scale of 1-10, how would you rate your mood lately?</label>
                <input type="range" name="qol_mood" min="1" max="10" value="5" class="slider">
                <output>5</output>
                <button class="btn-skip">Skip</button>
            </div>
        </div>
        
        <!-- Step 5: Income & Legal -->
        <div id="step-5" class="wizard-step" style="display: none;">
            <h2>Step 5: Income & Legal Support</h2>
            <p class="step-description">This helps us find financial support, benefits help, and legal services if you need them.</p>
            
            <div class="question-block">
                <label class="question-label">Do you have any source of income?</label>
                <button class="why-asking-btn" data-question="income">Why ask?</button>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="income_sources[]" value="employment"> Employment</label>
                    <label><input type="checkbox" name="income_sources[]" value="disability"> Disability benefits</label>
                    <label><input type="checkbox" name="income_sources[]" value="welfare"> Welfare / social assistance</label>
                    <label><input type="checkbox" name="income_sources[]" value="pension"> Pension</label>
                    <label><input type="checkbox" name="income_sources[]" value="informal"> Informal work</label>
                    <label><input type="checkbox" name="income_sources[]" value="none"> No income</label>
                </div>
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">Do you have government-issued ID?</label>
                <div class="radio-group">
                    <label><input type="radio" name="has_id" value="yes"> Yes</label>
                    <label><input type="radio" name="has_id" value="no"> No</label>
                    <label><input type="radio" name="has_id" value="need_help"> No, and I need help getting one</label>
                </div>
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">Do you need legal support? (eviction, charges, benefits appeals, etc.)</label>
                <div class="radio-group">
                    <label><input type="radio" name="legal_support" value="yes"> Yes</label>
                    <label><input type="radio" name="legal_support" value="no"> No</label>
                    <label><input type="radio" name="legal_support" value="maybe"> Maybe / Not sure</label>
                </div>
                <button class="btn-skip">Skip</button>
            </div>
        </div>
        
        <!-- Step 6: Goals & What You Need -->
        <div id="step-6" class="wizard-step" style="display: none;">
            <h2>Step 6: Your Goals & What You Need</h2>
            <p class="step-description">This is about what YOU want. We're here to help you work toward your goals.</p>
            
            <div class="question-block">
                <label class="question-label">What's most important to you right now? (Choose up to 3)</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="priorities[]" value="housing"> Finding housing</label>
                    <label><input type="checkbox" name="priorities[]" value="income"> Getting income/benefits</label>
                    <label><input type="checkbox" name="priorities[]" value="health"> Health care</label>
                    <label><input type="checkbox" name="priorities[]" value="mental_health"> Mental health support</label>
                    <label><input type="checkbox" name="priorities[]" value="addiction"> Addiction treatment</label>
                    <label><input type="checkbox" name="priorities[]" value="legal"> Legal help</label>
                    <label><input type="checkbox" name="priorities[]" value="id"> Getting ID</label>
                    <label><input type="checkbox" name="priorities[]" value="safety"> Feeling safe</label>
                    <label><input type="checkbox" name="priorities[]" value="connections"> Building connections</label>
                    <label><input type="checkbox" name="priorities[]" value="other"> Something else</label>
                </div>
                <button class="btn-skip">Skip</button>
            </div>
            
            <div class="question-block">
                <label class="question-label">Is there anything else you want us to know or any way we can help?</label>
                <textarea name="additional_info" class="form-control" rows="4" placeholder="Share whatever feels right..."></textarea>
                <button class="btn-skip">Skip</button>
            </div>
        </div>
        
        <!-- Step 7: Documents & Summary -->
        <div id="step-7" class="wizard-step" style="display: none;">
            <h2>Step 7: Documents & Review</h2>
            <p class="step-description">You can upload any documents if you have them (ID, prescriptions, letters, etc.).</p>
            
            <div class="question-block">
                <label class="question-label">Upload Documents (Optional)</label>
                <input type="file" id="document-upload" multiple accept="image/*,.pdf" class="form-control">
                <p class="help-text">Accepted: Images (JPG, PNG) and PDF. Max 5MB per file.</p>
            </div>
            
            <div class="question-block">
                <h3>Summary</h3>
                <p>You're almost done! When you click "Complete Intake", we'll:</p>
                <ul>
                    <li>Save all your information securely</li>
                    <li>Suggest resources that might help</li>
                    <li>Create a PDF copy for you</li>
                    <li>Connect you with a support worker if you want one</li>
                </ul>
                <p><strong>Remember:</strong> You can update any of this information later. You're in control.</p>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="wizard-navigation">
            <button id="btn-prev" class="btn btn-secondary">← Previous</button>
            <button id="btn-save-exit" class="btn btn-outline">Save & Exit</button>
            <button id="btn-next" class="btn btn-primary">Next →</button>
            <button id="btn-complete" class="btn btn-success" style="display: none;">Complete Intake ✓</button>
        </div>
    </div>
    
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/intake-wizard.js"></script>
    
    <script>
        // Update slider output values
        document.querySelectorAll('.slider').forEach(slider => {
            const output = slider.nextElementSibling;
            slider.addEventListener('input', () => {
                output.textContent = slider.value;
            });
        });
    </script>
</body>
</html>
