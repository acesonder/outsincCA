<?php
require_once '../../config/config.php';
require_once '../../includes/Auth.php';

$auth = new Auth();
$user = $auth->getUser();

if (!$user) {
    header('Location: /index.php');
    exit;
}

// Get client ID from session or URL
$clientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : ($user['role'] === 'client' ? $user['user_id'] : null);

if (!$clientId) {
    header('Location: /public/dashboard.php');
    exit;
}

$pageTitle = 'Needs Assessment & Smart Surveys';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/needs-assessment.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include '../includes/nav.php'; ?>
    
    <main class="assessment-container">
        <div class="assessment-header">
            <h1>📋 Needs Assessment</h1>
            <p class="assessment-subtitle">Help us understand your current situation across different life areas. You can skip any question you're not comfortable answering.</p>
            
            <div class="assessment-controls">
                <button id="viewHistoryBtn" class="btn-secondary">
                    <span class="icon">📊</span> View History
                </button>
                <button id="exportPdfBtn" class="btn-secondary">
                    <span class="icon">📄</span> Export PDF
                </button>
                <button id="compareBtn" class="btn-secondary" style="display: none;">
                    <span class="icon">📈</span> Compare to Baseline
                </button>
            </div>
        </div>

        <div class="assessment-progress-bar">
            <div class="progress-fill" id="overallProgress"></div>
            <span class="progress-text" id="progressText">0% Complete</span>
        </div>

        <div class="assessment-info-banner">
            <span class="icon">ℹ️</span>
            <p><strong>This assessment helps us:</strong> Understand your needs, identify areas where you might benefit from support, and connect you with relevant resources. All answers are confidential and you control who sees them.</p>
        </div>

        <!-- Domain 1: Housing Stability -->
        <div class="domain-card" data-domain="housing">
            <div class="domain-header" onclick="toggleDomain('housing')">
                <h2>
                    <span class="domain-icon">🏠</span>
                    Housing Stability
                    <span class="domain-status" id="housing-status">Not Started</span>
                </h2>
                <div class="domain-score" id="housing-score">
                    <span class="score-label">Risk Score:</span>
                    <span class="score-value">—</span>
                </div>
            </div>
            <div class="domain-content" id="housing-content">
                <p class="domain-description">Questions about your current housing situation and stability.</p>
                <div class="questions-container" id="housing-questions"></div>
            </div>
        </div>

        <!-- Domain 2: Substance Use Patterns -->
        <div class="domain-card" data-domain="substances">
            <div class="domain-header" onclick="toggleDomain('substances')">
                <h2>
                    <span class="domain-icon">💊</span>
                    Substance Use Patterns
                    <span class="domain-status" id="substances-status">Not Started</span>
                </h2>
                <div class="domain-score" id="substances-score">
                    <span class="score-label">Risk Score:</span>
                    <span class="score-value">—</span>
                </div>
            </div>
            <div class="domain-content" id="substances-content">
                <p class="domain-description">Questions about substance use and harm reduction practices.</p>
                <div class="questions-container" id="substances-questions"></div>
            </div>
        </div>

        <!-- Domain 3: Mental Health -->
        <div class="domain-card" data-domain="mental_health">
            <div class="domain-header" onclick="toggleDomain('mental_health')">
                <h2>
                    <span class="domain-icon">🧠</span>
                    Mental Health
                    <span class="domain-status" id="mental_health-status">Not Started</span>
                </h2>
                <div class="domain-score" id="mental_health-score">
                    <span class="score-label">Risk Score:</span>
                    <span class="score-value">—</span>
                </div>
            </div>
            <div class="domain-content" id="mental_health-content">
                <p class="domain-description">Questions about mental health symptoms and support.</p>
                <div class="questions-container" id="mental_health-questions"></div>
            </div>
        </div>

        <!-- Domain 4: Safety & Violence -->
        <div class="domain-card" data-domain="safety">
            <div class="domain-header" onclick="toggleDomain('safety')">
                <h2>
                    <span class="domain-icon">🛡️</span>
                    Safety & Violence
                    <span class="domain-status" id="safety-status">Not Started</span>
                </h2>
                <div class="domain-score" id="safety-score">
                    <span class="score-label">Risk Score:</span>
                    <span class="score-value">—</span>
                </div>
            </div>
            <div class="domain-content" id="safety-content">
                <p class="domain-description">Questions about safety concerns and violence exposure.</p>
                <div class="questions-container" id="safety-questions"></div>
            </div>
        </div>

        <!-- Domain 5: Social Support Networks -->
        <div class="domain-card" data-domain="social_support">
            <div class="domain-header" onclick="toggleDomain('social_support')">
                <h2>
                    <span class="domain-icon">👥</span>
                    Social Support Networks
                    <span class="domain-status" id="social_support-status">Not Started</span>
                </h2>
                <div class="domain-score" id="social_support-score">
                    <span class="score-label">Risk Score:</span>
                    <span class="score-value">—</span>
                </div>
            </div>
            <div class="domain-content" id="social_support-content">
                <p class="domain-description">Questions about your social connections and support system.</p>
                <div class="questions-container" id="social_support-questions"></div>
            </div>
        </div>

        <div class="assessment-actions">
            <button id="saveProgressBtn" class="btn-secondary">
                <span class="icon">💾</span> Save Progress
            </button>
            <button id="completeAssessmentBtn" class="btn-primary" disabled>
                <span class="icon">✅</span> Complete Assessment
            </button>
        </div>

        <!-- Comparison Chart Modal -->
        <div id="comparisonModal" class="modal">
            <div class="modal-content modal-large">
                <span class="close" onclick="closeComparisonModal()">&times;</span>
                <h2>📈 Assessment Comparison</h2>
                <p>Comparing current assessment to your baseline assessment.</p>
                <canvas id="comparisonChart"></canvas>
            </div>
        </div>

        <!-- History Modal -->
        <div id="historyModal" class="modal">
            <div class="modal-content modal-large">
                <span class="close" onclick="closeHistoryModal()">&times;</span>
                <h2>📊 Assessment History</h2>
                <div id="historyTimeline"></div>
                <canvas id="historyChart"></canvas>
            </div>
        </div>
    </main>

    <div id="toastContainer"></div>

    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/needs-assessment.js"></script>
    <script>
        const clientId = <?php echo $clientId; ?>;
        const assessment = new NeedsAssessment(clientId);
        assessment.init();

        function toggleDomain(domain) {
            assessment.toggleDomain(domain);
        }

        function closeComparisonModal() {
            assessment.closeComparisonModal();
        }

        function closeHistoryModal() {
            assessment.closeHistoryModal();
        }
    </script>
</body>
</html>
