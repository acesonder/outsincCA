<?php
/**
 * OUTSINC - New Order Page
 * All-in-one harm reduction supply ordering interface
 */

require_once __DIR__ . '/../../config/config.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /index.php');
    exit();
}

// Check if user has permission (worker, admin)
if (!$auth->hasAnyRole([ROLE_WORKER, ROLE_ADMIN])) {
    header('Location: /public/dashboard.php');
    exit();
}

$pageTitle = 'New Order';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/orders.css">
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <!-- Main Content -->
    <div class="container order-page">
        <h1 class="mb-3">📦 New Harm Reduction Order</h1>
        
        <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Left Column: Order Form -->
            <div>
                <!-- Client Selection -->
                <div class="order-section">
                    <h2 class="section-title">1. Select Client</h2>
                    <div class="client-selector">
                        <div class="form-group">
                            <label class="form-label required" for="client_select">Client</label>
                            <select id="client_select" class="form-control client-dropdown">
                                <option value="">Search for a client...</option>
                            </select>
                            <div class="form-help">Start typing to search by name or ID</div>
                        </div>
                        <button class="btn btn-outline new-client-btn" onclick="OUTSINC.showModal('new-client-modal')">
                            ➕ Add New Client
                        </button>
                    </div>
                </div>

                <!-- Product Selection -->
                <div class="order-section">
                    <h2 class="section-title">2. Select Products</h2>
                    
                    <!-- Search and Filter -->
                    <div class="product-search">
                        <div class="search-filter-container">
                            <input type="text" id="product_search" class="form-control search-input" placeholder="🔍 Search products...">
                            <select id="category_filter" class="form-control filter-select">
                                <option value="">All Categories</option>
                                <option value="needles">Needles</option>
                                <option value="stems">Stems</option>
                                <option value="naloxone">Naloxone</option>
                                <option value="condoms">Condoms</option>
                                <option value="pipes">Pipes</option>
                                <option value="cookers">Cookers</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Product Grid -->
                    <div class="product-grid" id="product-grid">
                        <!-- Products will be loaded dynamically -->
                        <div class="product-card" data-product-id="1" data-product-name="Needle Pack (10)" data-category="needles">
                            <div class="product-image">💉</div>
                            <div class="product-name">Needle Pack (10)</div>
                            <div class="product-category">Needles</div>
                        </div>
                        
                        <div class="product-card" data-product-id="2" data-product-name="Glass Stem" data-category="stems">
                            <div class="product-image">🔬</div>
                            <div class="product-name">Glass Stem</div>
                            <div class="product-category">Stems</div>
                        </div>
                        
                        <div class="product-card" data-product-id="3" data-product-name="Naloxone Kit" data-category="naloxone">
                            <div class="product-image">💊</div>
                            <div class="product-name">Naloxone Kit</div>
                            <div class="product-category">Naloxone</div>
                        </div>
                        
                        <div class="product-card" data-product-id="4" data-product-name="Condoms (Pack of 12)" data-category="condoms">
                            <div class="product-image">🛡️</div>
                            <div class="product-name">Condoms (12)</div>
                            <div class="product-category">Condoms</div>
                        </div>
                        
                        <div class="product-card" data-product-id="5" data-product-name="Pipe (Glass)" data-category="pipes">
                            <div class="product-image">🔧</div>
                            <div class="product-name">Glass Pipe</div>
                            <div class="product-category">Pipes</div>
                        </div>
                        
                        <div class="product-card" data-product-id="6" data-product-name="Cooker Pack" data-category="cookers">
                            <div class="product-image">🥄</div>
                            <div class="product-name">Cooker Pack</div>
                            <div class="product-category">Cookers</div>
                        </div>
                        
                        <div class="product-card" data-product-id="7" data-product-name="Cotton Filters" data-category="other">
                            <div class="product-image">⚪</div>
                            <div class="product-name">Cotton Filters</div>
                            <div class="product-category">Other</div>
                        </div>
                        
                        <div class="product-card" data-product-id="8" data-product-name="Alcohol Swabs" data-category="other">
                            <div class="product-image">🧼</div>
                            <div class="product-name">Alcohol Swabs</div>
                            <div class="product-category">Other</div>
                        </div>
                        
                        <div class="product-card" data-product-id="9" data-product-name="Sharps Container" data-category="other">
                            <div class="product-image">🗑️</div>
                            <div class="product-name">Sharps Container</div>
                            <div class="product-category">Other</div>
                        </div>
                        
                        <div class="product-card" data-product-id="10" data-product-name="Tourniquets" data-category="other">
                            <div class="product-image">🔗</div>
                            <div class="product-name">Tourniquets</div>
                            <div class="product-category">Other</div>
                        </div>
                    </div>
                </div>

                <!-- Pickup/Dropoff Details -->
                <div class="order-section">
                    <h2 class="section-title">3. Delivery Details</h2>
                    
                    <div class="delivery-options">
                        <div class="delivery-option" data-option="pickup" onclick="selectDeliveryOption('pickup')">
                            <div class="delivery-icon">🏪</div>
                            <strong>Pickup</strong>
                            <div>Client picks up at location</div>
                        </div>
                        <div class="delivery-option" data-option="dropoff" onclick="selectDeliveryOption('dropoff')">
                            <div class="delivery-icon">🚗</div>
                            <strong>Dropoff</strong>
                            <div>Deliver to client location</div>
                        </div>
                    </div>
                    
                    <div id="pickup-section" style="display: none;">
                        <div class="form-group mt-3">
                            <label class="form-label">Pickup Location</label>
                            <select id="pickup_location" class="form-control">
                                <option value="">Select location...</option>
                                <option value="main_office">Main Office</option>
                                <option value="outreach_van">Outreach Van</option>
                                <option value="drop_in_center">Drop-in Center</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Pickup Time</label>
                            <div class="time-slots" id="time-slots">
                                <!-- Time slots will be generated dynamically -->
                            </div>
                        </div>
                    </div>
                    
                    <div id="dropoff-section" style="display: none;">
                        <div class="form-group mt-3">
                            <label class="form-label">Dropoff Address</label>
                            <textarea id="dropoff_address" class="form-control" rows="3" placeholder="Enter dropoff location..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Preferred Time</label>
                            <input type="datetime-local" id="dropoff_time" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group mt-3">
                        <label class="form-label">Additional Instructions</label>
                        <textarea id="order_instructions" class="form-control" rows="3" placeholder="Any special instructions or notes..."></textarea>
                    </div>
                </div>

                <!-- Case Notes (Optional) -->
                <div class="order-section">
                    <h2 class="section-title">4. Case Notes (Optional)</h2>
                    <div class="form-group">
                        <textarea id="case_notes" class="form-control" rows="4" placeholder="Add any relevant case notes about this contact..."></textarea>
                        <div class="form-help">These notes will be saved to the client's case file</div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div>
                <div class="order-summary">
                    <h3 class="section-title">Order Summary</h3>
                    
                    <div id="order-summary-content">
                        <div class="empty-summary">
                            <p>No items selected</p>
                            <p class="text-secondary">Tap products to add them to your order</p>
                        </div>
                    </div>
                    
                    <div id="order-summary-items" style="display: none;">
                        <div class="order-summary-items" id="selected-items">
                            <!-- Selected items will appear here -->
                        </div>
                        
                        <div style="border-top: 2px solid var(--border-color); padding-top: 1rem; margin-top: 1rem;">
                            <div class="flex justify-between mb-2">
                                <strong>Total Items:</strong>
                                <strong id="total-items">0</strong>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="text-secondary">Client:</span>
                                <span id="selected-client-name">Not selected</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="text-secondary">Delivery:</span>
                                <span id="selected-delivery">Not selected</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Action Bar -->
    <div class="bottom-action-bar" id="action-bar">
        <div class="action-bar-content">
            <div>
                <div class="order-total">
                    <span id="bottom-total-items">0</span> items selected
                </div>
            </div>
            <button class="confirm-order-btn" onclick="confirmOrder()">
                ✓ Confirm Order
            </button>
        </div>
    </div>

    <!-- New Client Modal -->
    <div class="modal-backdrop" id="new-client-backdrop">
        <div class="modal" id="new-client-modal">
            <div class="modal-header">
                <h2 class="modal-title">Add New Client</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="new-client-form">
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label required" for="new_first_name">First Name</label>
                            <input type="text" id="new_first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required" for="new_last_name">Last Name</label>
                            <input type="text" id="new_last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="new_alias">Preferred Name / Alias</label>
                        <input type="text" id="new_alias" name="alias" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="new_phone">Phone Number</label>
                        <input type="tel" id="new_phone" name="phone" class="form-control">
                    </div>
                    
                    <div id="new-client-message"></div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="OUTSINC.closeModal('new-client-modal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Back to Top -->
    <div class="back-to-top">↑</div>

    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/orders.js"></script>
</body>
</html>
