<?php
require_once '../../config/config.php';
require_once '../../includes/Auth.php';

// Check if user is logged in
Auth::checkAuth();

$pageTitle = 'Risk Assessment & Safety Planning - OUTSINC';
$currentPage = 'risk-assessment';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/risk-assessment.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <div class="container">
        <div class="risk-assessment-wrapper">
            <!-- Header -->
            <div class="assessment-header">
                <h1>Risk Assessment & Safety Planning</h1>
                <p class="subtitle">Let's identify risks together and create a plan to keep you safe.</p>
            </div>

            <!-- Information Banner -->
            <div class="info-banner">
                <div class="info-icon">ℹ️</div>
                <div class="info-content">
                    <h3>About This Assessment</h3>
                    <p>This assessment helps us understand any immediate safety concerns and work with you to create a personalized safety plan. Your honesty helps us support you better. Everything you share is confidential unless there's an immediate risk to your safety or someone else's.</p>
                    <p><strong>You can skip any question</strong> - only answer what feels safe to share today.</p>
                </div>
            </div>

            <!-- Risk Assessment Section -->
            <div id="riskAssessmentSection" class="section-card">
                <div class="section-header">
                    <h2>Risk Assessment</h2>
                    <div class="risk-summary" id="riskSummary" style="display: none;">
                        <span class="risk-label">Overall Risk:</span>
                        <span class="risk-badge" id="overallRiskBadge">Not Assessed</span>
                    </div>
                </div>

                <!-- Risk Categories -->
                <div class="risk-categories">
                    <!-- Self-Harm Risk -->
                    <div class="risk-category" data-category="self_harm">
                        <div class="category-header">
                            <h3>Self-Harm Risk</h3>
                            <span class="risk-level" data-level="not-assessed">Not Assessed</span>
                        </div>
                        <div class="category-questions">
                            <div class="question">
                                <label>In the past 2 weeks, have you had thoughts of harming yourself?</label>
                                <select name="self_harm_thoughts" class="risk-input">
                                    <option value="">Select...</option>
                                    <option value="no">No</option>
                                    <option value="rarely">Rarely (1-2 times)</option>
                                    <option value="sometimes">Sometimes (3-5 times)</option>
                                    <option value="often">Often (6+ times)</option>
                                    <option value="skip">Prefer not to say</option>
                                </select>
                            </div>
                            <div class="question conditional" data-show-if="self_harm_thoughts:rarely,sometimes,often">
                                <label>Do you have a specific plan?</label>
                                <select name="self_harm_plan" class="risk-input">
                                    <option value="">Select...</option>
                                    <option value="no">No</option>
                                    <option value="vague">Vague thoughts</option>
                                    <option value="specific">Specific plan</option>
                                    <option value="skip">Prefer not to say</option>
                                </select>
                            </div>
                            <div class="question">
                                <label>Have you attempted to harm yourself in the past 6 months?</label>
                                <select name="self_harm_recent" class="risk-input">
                                    <option value="">Select...</option>
                                    <option value="no">No</option>
                                    <option value="yes">Yes</option>
                                    <option value="skip">Prefer not to say</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Overdose Risk -->
                    <div class="risk-category" data-category="overdose">
                        <div class="category-header">
                            <h3>Overdose Risk</h3>
                            <span class="risk-level" data-level="not-assessed">Not Assessed</span>
                        </div>
                        <div class="category-questions">
                            <div class="question">
                                <label>Do you currently use opioids or other substances?</label>
                                <select name="substance_use" class="risk-input">
                                    <option value="">Select...</option>
                                    <option value="no">No</option>
                                    <option value="occasional">Occasionally</option>
                                    <option value="regular">Regularly</option>
                                    <option value="daily">Daily</option>
                                    <option value="skip">Prefer not to say</option>
                                </select>
                            </div>
                            <div class="question conditional" data-show-if="substance_use:occasional,regular,daily">
                                <label>Have you overdosed in the past 6 months?</label>
                                <select name="recent_overdose" class="risk-input">
                                    <option value="">Select...</option>
                                    <option value="no">No</option>
                                    <option value="yes">Yes</option>
                                    <option value="skip">Prefer not to say</option>
                                </select>
                            </div>
                            <div class="question conditional" data-show-if="substance_use:occasional,regular,daily">
                                <label>Do you use alone?</label>
                                <select name="use_alone" class="risk-input">
                                    <option value="">Select...</option>
                                    <option value="never">Never</option>
                                    <option value="sometimes">Sometimes</option>
                                    <option value="usually">Usually</option>
                                    <option value="always">Always</option>
                                    <option value="skip">Prefer not to say</option>
                                </select>
                            </div>
                            <div class="question conditional" data-show-if="substance_use:occasional,regular,daily">
                                <label>Do you have access to naloxone (Narcan)?</label>
                                <select name="naloxone_access" class="risk-input">
                                    <option value="">Select...</option>
                                    <option value="yes">Yes, I have it</option>
                                    <option value="no">No</option>
                                    <option value="not_sure">Not sure what it is</option>
                                    <option value="skip">Prefer not to say</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Domestic Violence Risk -->
                    <div class="risk-category" data-category="domestic_violence">
                        <div class="category-header">
                            <h3>Safety & Violence</h3>
                            <span class="risk-level" data-level="not-assessed">Not Assessed</span>
                        </div>
                        <div class="category-questions">
                            <div class="question">
                                <label>Are you currently in a situation where you feel unsafe with a partner or family member?</label>
                                <select name="domestic_violence_current" class="risk-input">
                                    <option value="">Select...</option>
                                    <option value="no">No, I feel safe</option>
                                    <option value="sometimes">Sometimes feel unsafe</option>
                                    <option value="yes">Yes, often feel unsafe</option>
                                    <option value="skip">Prefer not to say</option>
                                </select>
                            </div>
                            <div class="question conditional" data-show-if="domestic_violence_current:sometimes,yes">
                                <label>Has this person physically harmed you in the past month?</label>
                                <select name="physical_harm_recent" class="risk-input">
                                    <option value="">Select...</option>
                                    <option value="no">No</option>
                                    <option value="yes">Yes</option>
                                    <option value="skip">Prefer not to say</option>
                                </select>
                            </div>
                            <div class="question conditional" data-show-if="domestic_violence_current:sometimes,yes">
                                <label>Are there children in the home?</label>
                                <select name="children_present" class="risk-input">
                                    <option value="">Select...</option>
                                    <option value="no">No</option>
                                    <option value="yes">Yes</option>
                                    <option value="skip">Prefer not to say</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Housing Instability Risk -->
                    <div class="risk-category" data-category="housing">
                        <div class="category-header">
                            <h3>Housing Risk</h3>
                            <span class="risk-level" data-level="not-assessed">Not Assessed</span>
                        </div>
                        <div class="category-questions">
                            <div class="question">
                                <label>Are you at risk of losing your current housing?</label>
                                <select name="housing_risk" class="risk-input">
                                    <option value="">Select...</option>
                                    <option value="stable">No, housing is stable</option>
                                    <option value="uncertain">Uncertain/worried</option>
                                    <option value="imminent">Yes, within 30 days</option>
                                    <option value="homeless">Already homeless</option>
                                    <option value="skip">Prefer not to say</option>
                                </select>
                            </div>
                            <div class="question conditional" data-show-if="housing_risk:uncertain,imminent">
                                <label>Have you received an eviction notice?</label>
                                <select name="eviction_notice" class="risk-input">
                                    <option value="">Select...</option>
                                    <option value="no">No</option>
                                    <option value="yes">Yes</option>
                                    <option value="skip">Prefer not to say</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-actions">
                    <button type="button" class="btn btn-primary" id="saveRisksBtn">Save Risk Assessment</button>
                </div>
            </div>

            <!-- Safety Plan Section -->
            <div id="safetyPlanSection" class="section-card" style="display: none;">
                <div class="section-header">
                    <h2>Safety Plan</h2>
                    <p class="subtitle">Let's create a plan to help keep you safe during difficult times.</p>
                </div>

                <div class="safety-plan-sections">
                    <!-- Warning Signs -->
                    <div class="plan-section">
                        <h3>1. Warning Signs</h3>
                        <p>What are early signs that you're starting to feel unsafe or in crisis?</p>
                        <textarea name="warning_signs" class="plan-input" rows="3" placeholder="E.g., feeling overwhelmed, isolating, not sleeping..."></textarea>
                    </div>

                    <!-- Internal Coping -->
                    <div class="plan-section">
                        <h3>2. Things I Can Do to Help Myself</h3>
                        <p>What coping strategies work for you?</p>
                        <textarea name="coping_strategies" class="plan-input" rows="3" placeholder="E.g., deep breathing, listening to music, going for a walk..."></textarea>
                    </div>

                    <!-- Social Distractions -->
                    <div class="plan-section">
                        <h3>3. People and Places That Help</h3>
                        <p>Where can you go or who can you be around (without talking about the crisis)?</p>
                        <textarea name="social_distractions" class="plan-input" rows="3" placeholder="E.g., coffee shop, friend's house, library..."></textarea>
                    </div>

                    <!-- People Who Can Help -->
                    <div class="plan-section">
                        <h3>4. People I Can Ask for Help</h3>
                        <div class="contact-inputs">
                            <div class="contact-row">
                                <input type="text" name="contact_1_name" class="plan-input" placeholder="Name">
                                <input type="tel" name="contact_1_phone" class="plan-input" placeholder="Phone">
                            </div>
                            <div class="contact-row">
                                <input type="text" name="contact_2_name" class="plan-input" placeholder="Name">
                                <input type="tel" name="contact_2_phone" class="plan-input" placeholder="Phone">
                            </div>
                            <div class="contact-row">
                                <input type="text" name="contact_3_name" class="plan-input" placeholder="Name">
                                <input type="tel" name="contact_3_phone" class="plan-input" placeholder="Phone">
                            </div>
                        </div>
                    </div>

                    <!-- Professional Contacts -->
                    <div class="plan-section">
                        <h3>5. Professional & Crisis Contacts</h3>
                        <div class="crisis-contacts">
                            <div class="crisis-contact">
                                <strong>Crisis Text Line:</strong> Text HOME to 741741
                            </div>
                            <div class="crisis-contact">
                                <strong>National Suicide Prevention Lifeline:</strong> 1-800-273-8255
                            </div>
                            <div class="crisis-contact">
                                <strong>Domestic Violence Hotline:</strong> 1-800-799-7233
                            </div>
                            <div class="crisis-contact">
                                <strong>Your Worker:</strong> <span id="workerContact"></span>
                            </div>
                        </div>
                        <div class="contact-row">
                            <input type="text" name="therapist_name" class="plan-input" placeholder="Therapist/Counselor Name">
                            <input type="tel" name="therapist_phone" class="plan-input" placeholder="Phone">
                        </div>
                    </div>

                    <!-- Safe Environment -->
                    <div class="plan-section">
                        <h3>6. Making My Environment Safer</h3>
                        <p>What can you do to reduce access to means of self-harm?</p>
                        <textarea name="safe_environment" class="plan-input" rows="3" placeholder="E.g., give medications to trusted person, remove sharp objects..."></textarea>
                    </div>
                </div>

                <div class="section-actions">
                    <button type="button" class="btn btn-secondary" id="backToRiskBtn">Back to Risk Assessment</button>
                    <button type="button" class="btn btn-primary" id="savePlanBtn">Save Safety Plan</button>
                    <button type="button" class="btn btn-success" id="completePlanBtn" style="display: none;">Complete & Print Wallet Card</button>
                </div>
            </div>

            <!-- Risk History Section -->
            <div id="riskHistorySection" class="section-card" style="display: none;">
                <div class="section-header">
                    <h2>Risk Assessment History</h2>
                </div>
                <div id="riskHistoryContent">
                    <p class="text-center">No previous risk assessments found.</p>
                </div>
                <canvas id="riskTrendChart" style="display: none; max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Wallet Card Print Template -->
    <div id="walletCardTemplate" style="display: none;">
        <div class="wallet-card">
            <div class="card-header">
                <h4>Safety Plan - <?php echo date('m/d/Y'); ?></h4>
            </div>
            <div class="card-section">
                <strong>Crisis Contacts:</strong>
                <div>Crisis Text: 741741</div>
                <div>Suicide Line: 1-800-273-8255</div>
                <div id="walletContacts"></div>
            </div>
            <div class="card-section">
                <strong>Coping Strategies:</strong>
                <div id="walletCoping"></div>
            </div>
            <div class="card-section">
                <strong>Safe Places:</strong>
                <div id="walletPlaces"></div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/risk-assessment.js"></script>
    <script>
        // Initialize Risk Assessment
        document.addEventListener('DOMContentLoaded', function() {
            const riskAssessment = new RiskAssessment();
        });
    </script>
</body>
</html>
