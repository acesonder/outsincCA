/**
 * OUTSINC - Orders JavaScript
 * Client-side functionality for the harm reduction ordering system
 */

// Order state
const orderState = {
    selectedClient: null,
    selectedProducts: {},
    deliveryMethod: null,
    deliveryDetails: {}
};

// Initialize order page
document.addEventListener('DOMContentLoaded', function() {
    initializeOrderPage();
});

function initializeOrderPage() {
    // Initialize product cards
    initializeProductCards();
    
    // Initialize search and filter
    initializeSearchFilter();
    
    // Generate time slots
    generateTimeSlots();
    
    // Load clients for dropdown
    loadClients();
    
    // Initialize new client form
    initializeNewClientForm();
}

// Product Card Interactions
function initializeProductCards() {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        card.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            
            if (orderState.selectedProducts[productId]) {
                // Increment quantity
                orderState.selectedProducts[productId].quantity++;
            } else {
                // Add new product
                orderState.selectedProducts[productId] = {
                    id: productId,
                    name: productName,
                    quantity: 1
                };
            }
            
            updateProductCard(card, productId);
            updateOrderSummary();
        });
    });
}

function updateProductCard(card, productId) {
    const product = orderState.selectedProducts[productId];
    
    // Remove existing badge
    const existingBadge = card.querySelector('.product-quantity-badge');
    if (existingBadge) {
        existingBadge.remove();
    }
    
    // Add or update badge
    if (product && product.quantity > 0) {
        card.classList.add('selected');
        const badge = document.createElement('div');
        badge.className = 'product-quantity-badge';
        badge.textContent = product.quantity;
        card.appendChild(badge);
    } else {
        card.classList.remove('selected');
    }
}

// Search and Filter
function initializeSearchFilter() {
    const searchInput = document.getElementById('product_search');
    const categoryFilter = document.getElementById('category_filter');
    
    // Create debounced version of filterProducts
    const debouncedFilter = OUTSINC.debounce(filterProducts, 300);
    
    searchInput.addEventListener('input', debouncedFilter);
    categoryFilter.addEventListener('change', filterProducts);
}

function filterProducts() {
    const searchTerm = document.getElementById('product_search').value.toLowerCase();
    const category = document.getElementById('category_filter').value;
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        const productName = card.getAttribute('data-product-name').toLowerCase();
        const productCategory = card.getAttribute('data-category');
        
        const matchesSearch = !searchTerm || productName.includes(searchTerm);
        const matchesCategory = !category || productCategory === category;
        
        if (matchesSearch && matchesCategory) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Order Summary
function updateOrderSummary() {
    const summaryContent = document.getElementById('order-summary-content');
    const summaryItems = document.getElementById('order-summary-items');
    const selectedItemsContainer = document.getElementById('selected-items');
    const totalItems = document.getElementById('total-items');
    const bottomTotalItems = document.getElementById('bottom-total-items');
    const actionBar = document.getElementById('action-bar');
    
    const products = Object.values(orderState.selectedProducts);
    const totalCount = products.reduce((sum, product) => sum + product.quantity, 0);
    
    if (products.length === 0) {
        summaryContent.style.display = 'block';
        summaryItems.style.display = 'none';
        actionBar.classList.remove('show');
    } else {
        summaryContent.style.display = 'none';
        summaryItems.style.display = 'block';
        actionBar.classList.add('show');
        
        // Build items list
        selectedItemsContainer.innerHTML = '';
        products.forEach(product => {
            const itemEl = document.createElement('div');
            itemEl.className = 'order-item';
            itemEl.innerHTML = `
                <div class="order-item-info">
                    <div class="order-item-name">${product.name}</div>
                </div>
                <div class="order-item-controls">
                    <div class="quantity-control">
                        <button class="quantity-btn" onclick="decrementQuantity('${product.id}')">-</button>
                        <span class="quantity-value">${product.quantity}</span>
                        <button class="quantity-btn" onclick="incrementQuantity('${product.id}')">+</button>
                    </div>
                    <button class="remove-item-btn" onclick="removeProduct('${product.id}')" title="Remove">
                        🗑️
                    </button>
                </div>
            `;
            selectedItemsContainer.appendChild(itemEl);
        });
        
        totalItems.textContent = totalCount;
        bottomTotalItems.textContent = totalCount;
    }
    
    // Update delivery info
    const selectedDelivery = document.getElementById('selected-delivery');
    if (orderState.deliveryMethod) {
        selectedDelivery.textContent = orderState.deliveryMethod.charAt(0).toUpperCase() + orderState.deliveryMethod.slice(1);
    } else {
        selectedDelivery.textContent = 'Not selected';
    }
}

function incrementQuantity(productId) {
    if (orderState.selectedProducts[productId]) {
        orderState.selectedProducts[productId].quantity++;
        const card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
        updateProductCard(card, productId);
        updateOrderSummary();
    }
}

function decrementQuantity(productId) {
    if (orderState.selectedProducts[productId]) {
        orderState.selectedProducts[productId].quantity--;
        
        if (orderState.selectedProducts[productId].quantity <= 0) {
            delete orderState.selectedProducts[productId];
        }
        
        const card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
        updateProductCard(card, productId);
        updateOrderSummary();
    }
}

function removeProduct(productId) {
    delete orderState.selectedProducts[productId];
    const card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
    updateProductCard(card, productId);
    updateOrderSummary();
}

// Delivery Options
function selectDeliveryOption(option) {
    orderState.deliveryMethod = option;
    
    // Update UI
    document.querySelectorAll('.delivery-option').forEach(el => {
        el.classList.remove('selected');
    });
    document.querySelector(`.delivery-option[data-option="${option}"]`).classList.add('selected');
    
    // Show/hide sections
    const pickupSection = document.getElementById('pickup-section');
    const dropoffSection = document.getElementById('dropoff-section');
    
    if (option === 'pickup') {
        pickupSection.style.display = 'block';
        dropoffSection.style.display = 'none';
    } else {
        pickupSection.style.display = 'none';
        dropoffSection.style.display = 'block';
    }
    
    updateOrderSummary();
}

// Time Slots
function generateTimeSlots() {
    const timeSlotsContainer = document.getElementById('time-slots');
    const times = ['9:00 AM', '10:00 AM', '11:00 AM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM'];
    
    times.forEach(time => {
        const slot = document.createElement('div');
        slot.className = 'time-slot';
        slot.textContent = time;
        slot.onclick = function() {
            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
            this.classList.add('selected');
            orderState.deliveryDetails.pickupTime = time;
        };
        timeSlotsContainer.appendChild(slot);
    });
}

// Load Clients
async function loadClients() {
    try {
        const response = await fetch('/api/clients/list.php');
        const result = await response.json();
        
        if (result.success && result.clients) {
            const select = document.getElementById('client_select');
            result.clients.forEach(client => {
                const option = document.createElement('option');
                option.value = client.id;
                option.textContent = `${client.first_name} ${client.last_name} (${client.user_id})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading clients:', error);
    }
    
    // Handle client selection
    document.getElementById('client_select').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (this.value) {
            orderState.selectedClient = {
                id: this.value,
                name: selectedOption.textContent
            };
            document.getElementById('selected-client-name').textContent = selectedOption.textContent;
        } else {
            orderState.selectedClient = null;
            document.getElementById('selected-client-name').textContent = 'Not selected';
        }
    });
}

// New Client Form
function initializeNewClientForm() {
    document.getElementById('new-client-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        const messageEl = document.getElementById('new-client-message');
        
        try {
            const response = await fetch('/api/clients/add.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                messageEl.innerHTML = '<div class="alert alert-success">Client added successfully!</div>';
                
                // Add to dropdown
                const select = document.getElementById('client_select');
                const option = document.createElement('option');
                option.value = result.client.id;
                option.textContent = `${result.client.first_name} ${result.client.last_name}`;
                option.selected = true;
                select.appendChild(option);
                
                orderState.selectedClient = {
                    id: result.client.id,
                    name: option.textContent
                };
                document.getElementById('selected-client-name').textContent = option.textContent;
                
                // Close modal after delay
                setTimeout(() => {
                    OUTSINC.closeModal('new-client-modal');
                    this.reset();
                    messageEl.innerHTML = '';
                }, 1500);
            } else {
                messageEl.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
            }
        } catch (error) {
            messageEl.innerHTML = '<div class="alert alert-error">An error occurred. Please try again.</div>';
        }
    });
}

// Confirm Order
async function confirmOrder() {
    // Validate
    if (!orderState.selectedClient) {
        OUTSINC.showToast('Please select a client', 'error');
        return;
    }
    
    if (Object.keys(orderState.selectedProducts).length === 0) {
        OUTSINC.showToast('Please select at least one product', 'error');
        return;
    }
    
    if (!orderState.deliveryMethod) {
        OUTSINC.showToast('Please select pickup or dropoff', 'error');
        return;
    }
    
    // Prepare order data
    const orderData = {
        client_id: orderState.selectedClient.id,
        products: Object.values(orderState.selectedProducts),
        delivery_method: orderState.deliveryMethod,
        pickup_location: document.getElementById('pickup_location')?.value,
        dropoff_address: document.getElementById('dropoff_address')?.value,
        dropoff_time: document.getElementById('dropoff_time')?.value,
        instructions: document.getElementById('order_instructions')?.value,
        case_notes: document.getElementById('case_notes')?.value,
        pickup_time: orderState.deliveryDetails.pickupTime
    };
    
    try {
        const response = await fetch('/api/orders/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            OUTSINC.showToast('Order created successfully!', 'success');
            
            // Redirect after short delay
            setTimeout(() => {
                window.location.href = `/public/orders/view.php?id=${result.order_id}`;
            }, 1500);
        } else {
            OUTSINC.showToast(result.message || 'Failed to create order', 'error');
        }
    } catch (error) {
        OUTSINC.showToast('An error occurred. Please try again.', 'error');
        console.error('Error creating order:', error);
    }
}
