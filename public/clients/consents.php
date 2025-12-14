<?php
/**
 * OUTSINC - Consent Management Interface
 * Categorized consent system with digital signatures and revocation
 */
session_start();
require_once '../../config/database.php';
require_once '../../includes/Auth.php';

$auth = new Auth($conn);

if (!$auth->isLoggedIn()) {
    header('Location: ../../index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$pageTitle = 'Consent Management';

// Get client ID (for workers viewing client consent, or client viewing own)
$clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;

// If client role, force to their own ID
if ($userRole === 'client') {
    $stmt = $conn->prepare("SELECT client_id FROM clients WHERE user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $clientId = $row['client_id'];
    }
}

// Get consent categories
$categories = [];
$stmt = $conn->query("SELECT * FROM consent_categories WHERE status = 'active' ORDER BY category_order");
while ($row = $stmt->fetch_assoc()) {
    $categories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - OUTSINC</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/consents.css">
</head>
<body>
    <?php include '../includes/nav.php'; ?>
    
    <div class="consent-container">
        <div class="consent-header">
            <h1>Consent & Privacy Management</h1>
            <p class="subtitle">You decide who we share information with and what we share. You can change your mind anytime.</p>
        </div>
        
        <!-- Information Banner -->
        <div class="info-banner">
            <h3>🔒 Your Privacy Matters</h3>
            <p>
                <strong>What does consent mean?</strong><br>
                Consent means giving us permission to share your information with specific organizations to help you get services. 
                You can say yes or no to each type of sharing, and you can change your decision at any time.
            </p>
            <p>
                <strong>What we NEVER share without your permission:</strong><br>
                Your personal information stays private unless you give us consent. The only exceptions are:
            </p>
            <ul>
                <li>If there's a risk of serious harm to you or someone else</li>
                <li>If required by law (court order)</li>
                <li>If we're legally required to report something (child abuse, elder abuse)</li>
            </ul>
            <p>
                <strong>How long does consent last?</strong><br>
                You choose! You can set consent for 6 months, 1 year, or ongoing. We'll remind you before it expires.
            </p>
        </div>
        
        <!-- Consent Categories -->
        <div class="consent-categories">
            <?php foreach ($categories as $category): ?>
            <div class="consent-card" data-category-id="<?php echo $category['category_id']; ?>">
                <div class="consent-card-header">
                    <span class="consent-icon"><?php echo htmlspecialchars($category['icon']); ?></span>
                    <h3><?php echo htmlspecialchars($category['category_name']); ?></h3>
                    <div class="consent-status" id="status-<?php echo $category['category_id']; ?>">
                        <span class="status-badge status-pending">Not Set</span>
                    </div>
                </div>
                
                <div class="consent-card-body">
                    <p class="consent-description"><?php echo htmlspecialchars($category['description']); ?></p>
                    
                    <div class="consent-details">
                        <p><strong>What gets shared:</strong></p>
                        <ul class="consent-list">
                            <?php 
                            $items = explode(',', $category['items_shared']);
                            foreach ($items as $item): ?>
                                <li><?php echo htmlspecialchars(trim($item)); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <p><strong>How it's used:</strong></p>
                        <p><?php echo htmlspecialchars($category['usage_explanation']); ?></p>
                    </div>
                    
                    <div class="consent-actions">
                        <button class="btn btn-success btn-grant" 
                                data-category-id="<?php echo $category['category_id']; ?>"
                                data-category-name="<?php echo htmlspecialchars($category['category_name']); ?>">
                            ✓ Grant Consent
                        </button>
                        <button class="btn btn-danger btn-revoke" 
                                data-category-id="<?php echo $category['category_id']; ?>"
                                style="display: none;">
                            ✗ Revoke Consent
                        </button>
                        <button class="btn btn-secondary btn-view-history" 
                                data-category-id="<?php echo $category['category_id']; ?>">
                            📋 View History
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Emergency Override Information -->
        <div class="emergency-override-info">
            <h3>⚠️ Emergency Overrides</h3>
            <p>
                In life-threatening emergencies, staff may need to share information without your consent to keep you safe. 
                This requires supervisor approval and is logged. You'll be notified as soon as possible.
            </p>
        </div>
        
        <!-- Consent History Timeline -->
        <div class="consent-history-section" style="display: none;">
            <h2>Consent History</h2>
            <div id="consent-timeline"></div>
        </div>
    </div>
    
    <!-- Grant Consent Modal -->
    <div id="grant-consent-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <h2>Grant Consent: <span id="modal-category-name"></span></h2>
            
            <form id="grant-consent-form">
                <input type="hidden" id="grant-category-id" name="category_id">
                <input type="hidden" id="grant-client-id" name="client_id" value="<?php echo $clientId; ?>">
                
                <div class="form-group">
                    <label>Consent Duration:</label>
                    <select name="duration" class="form-control" required>
                        <option value="6">6 months</option>
                        <option value="12" selected>1 year</option>
                        <option value="99999">Ongoing (until revoked)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="verbal_consent" value="1">
                        This is verbal consent (staff attesting for client)
                    </label>
                </div>
                
                <div class="form-group" id="signature-container">
                    <label>Digital Signature:</label>
                    <canvas id="signature-pad" width="400" height="150"></canvas>
                    <button type="button" id="clear-signature" class="btn btn-secondary">Clear</button>
                    <p class="help-text">Sign above to provide your digital signature</p>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="understand" required>
                        I understand what information will be shared and how it will be used
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="can_revoke" required>
                        I understand I can revoke this consent at any time
                    </label>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="btn btn-success">Grant Consent</button>
                    <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Revoke Consent Modal -->
    <div id="revoke-consent-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <h2>Revoke Consent: <span id="revoke-category-name"></span></h2>
            
            <div class="warning-box">
                <p><strong>Are you sure?</strong></p>
                <p>Revoking this consent means we won't be able to share this type of information anymore. 
                   This might affect your ability to access certain services.</p>
            </div>
            
            <form id="revoke-consent-form">
                <input type="hidden" id="revoke-category-id" name="category_id">
                <input type="hidden" id="revoke-client-id" name="client_id" value="<?php echo $clientId; ?>">
                
                <div class="form-group">
                    <label>Reason for revoking (optional):</label>
                    <textarea name="revoke_reason" class="form-control" rows="3" 
                              placeholder="Help us understand why you're revoking consent..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="confirm_revoke" required>
                        I understand this revocation takes effect immediately
                    </label>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="btn btn-danger">Revoke Consent</button>
                    <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/consents.js"></script>
</body>
</html>
