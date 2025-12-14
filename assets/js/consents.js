/**
 * Consent Management System
 * Handles consent granting, revocation, history, and digital signatures
 */

class ConsentManager {
    constructor() {
        this.currentClientId = null;
        this.signaturePad = null;
        this.consents = [];
        this.init();
    }

    init() {
        // Get client ID from URL or session
        const urlParams = new URLSearchParams(window.location.search);
        this.currentClientId = urlParams.get('client_id');

        if (!this.currentClientId) {
            this.showToast('No client ID provided', 'error');
            return;
        }

        // Load consents
        this.loadConsents();

        // Initialize event listeners
        this.initEventListeners();

        // Initialize Signature Pad
        this.initSignaturePad();
    }

    initEventListeners() {
        // Grant consent buttons
        document.querySelectorAll('.grant-consent-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const category = e.target.closest('.consent-card').dataset.category;
                this.openGrantModal(category);
            });
        });

        // Revoke consent buttons
        document.querySelectorAll('.revoke-consent-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const consentId = e.target.dataset.consentId;
                this.openRevokeModal(consentId);
            });
        });

        // View history buttons
        document.querySelectorAll('.view-history-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const category = e.target.closest('.consent-card').dataset.category;
                this.viewHistory(category);
            });
        });

        // Modal close buttons
        document.querySelectorAll('.modal-close, .modal-cancel').forEach(btn => {
            btn.addEventListener('click', () => this.closeModals());
        });

        // Grant consent form submit
        const grantForm = document.getElementById('grant-consent-form');
        if (grantForm) {
            grantForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.grantConsent();
            });
        }

        // Revoke consent form submit
        const revokeForm = document.getElementById('revoke-consent-form');
        if (revokeForm) {
            revokeForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.revokeConsent();
            });
        }

        // Signature type toggle
        const signatureType = document.getElementById('signature-type');
        if (signatureType) {
            signatureType.addEventListener('change', (e) => {
                this.toggleSignatureType(e.target.value);
            });
        }

        // Clear signature button
        const clearSigBtn = document.getElementById('clear-signature');
        if (clearSigBtn) {
            clearSigBtn.addEventListener('click', () => {
                if (this.signaturePad) {
                    this.signaturePad.clear();
                }
            });
        }

        // Consent duration change
        const durationSelect = document.getElementById('consent-duration');
        if (durationSelect) {
            durationSelect.addEventListener('change', (e) => {
                this.updateExpiryDate(e.target.value);
            });
        }
    }

    initSignaturePad() {
        const canvas = document.getElementById('signature-canvas');
        if (!canvas) return;

        this.signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });

        // Resize canvas
        this.resizeCanvas();
        window.addEventListener('resize', () => this.resizeCanvas());
    }

    resizeCanvas() {
        const canvas = document.getElementById('signature-canvas');
        if (!canvas) return;

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        
        if (this.signaturePad) {
            this.signaturePad.clear();
        }
    }

    async loadConsents() {
        try {
            const response = await fetch(`/api/consents/list.php?client_id=${this.currentClientId}`);
            const data = await response.json();

            if (data.success) {
                this.consents = data.consents;
                this.updateConsentCards();
            } else {
                this.showToast(data.message || 'Failed to load consents', 'error');
            }
        } catch (error) {
            console.error('Error loading consents:', error);
            this.showToast('Error loading consents', 'error');
        }
    }

    updateConsentCards() {
        this.consents.forEach(consent => {
            const card = document.querySelector(`.consent-card[data-category="${consent.category}"]`);
            if (!card) return;

            const statusBadge = card.querySelector('.consent-status');
            const grantBtn = card.querySelector('.grant-consent-btn');
            const revokeBtn = card.querySelector('.revoke-consent-btn');

            if (consent.status === 'active') {
                statusBadge.textContent = 'Active';
                statusBadge.className = 'consent-status status-active';
                grantBtn.style.display = 'none';
                revokeBtn.style.display = 'inline-block';
                revokeBtn.dataset.consentId = consent.consent_id;

                // Show expiry date if applicable
                if (consent.expiry_date) {
                    const expiryInfo = document.createElement('div');
                    expiryInfo.className = 'consent-expiry';
                    expiryInfo.textContent = `Expires: ${new Date(consent.expiry_date).toLocaleDateString()}`;
                    card.querySelector('.consent-card-content').appendChild(expiryInfo);
                }
            } else if (consent.status === 'revoked') {
                statusBadge.textContent = 'Revoked';
                statusBadge.className = 'consent-status status-revoked';
                grantBtn.style.display = 'inline-block';
                revokeBtn.style.display = 'none';
            } else if (consent.status === 'expired') {
                statusBadge.textContent = 'Expired';
                statusBadge.className = 'consent-status status-expired';
                grantBtn.style.display = 'inline-block';
                revokeBtn.style.display = 'none';
            }
        });
    }

    openGrantModal(category) {
        const modal = document.getElementById('grant-consent-modal');
        const categoryTitle = document.getElementById('consent-category-title');
        const categoryInput = document.getElementById('consent-category');

        categoryTitle.textContent = this.getCategoryName(category);
        categoryInput.value = category;

        modal.style.display = 'flex';
    }

    openRevokeModal(consentId) {
        const modal = document.getElementById('revoke-consent-modal');
        const consentIdInput = document.getElementById('revoke-consent-id');

        consentIdInput.value = consentId;
        modal.style.display = 'flex';
    }

    closeModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
    }

    toggleSignatureType(type) {
        const digitalSection = document.getElementById('digital-signature-section');
        const verbalSection = document.getElementById('verbal-attestation-section');

        if (type === 'digital') {
            digitalSection.style.display = 'block';
            verbalSection.style.display = 'none';
        } else {
            digitalSection.style.display = 'none';
            verbalSection.style.display = 'block';
        }
    }

    updateExpiryDate(duration) {
        const expiryDateDisplay = document.getElementById('expiry-date-display');
        if (!expiryDateDisplay) return;

        let expiryDate = new Date();
        
        if (duration === '6months') {
            expiryDate.setMonth(expiryDate.getMonth() + 6);
        } else if (duration === '1year') {
            expiryDate.setFullYear(expiryDate.getFullYear() + 1);
        } else {
            expiryDateDisplay.textContent = 'No expiry';
            return;
        }

        expiryDateDisplay.textContent = `Will expire on: ${expiryDate.toLocaleDateString()}`;
    }

    async grantConsent() {
        const category = document.getElementById('consent-category').value;
        const duration = document.getElementById('consent-duration').value;
        const signatureType = document.getElementById('signature-type').value;

        let signatureData = null;
        let workerAttestation = null;

        if (signatureType === 'digital') {
            if (this.signaturePad.isEmpty()) {
                this.showToast('Please provide a signature', 'error');
                return;
            }
            signatureData = this.signaturePad.toDataURL();
        } else {
            const workerName = document.getElementById('worker-name').value;
            const workerNote = document.getElementById('worker-attestation-note').value;
            
            if (!workerName) {
                this.showToast('Please enter worker name for attestation', 'error');
                return;
            }

            workerAttestation = {
                worker_name: workerName,
                note: workerNote
            };
        }

        const consentData = {
            client_id: this.currentClientId,
            category: category,
            duration: duration,
            signature_type: signatureType,
            signature_data: signatureData,
            worker_attestation: workerAttestation
        };

        try {
            const response = await fetch('/api/consents/grant.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(consentData)
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('Consent granted successfully', 'success');
                this.closeModals();
                this.loadConsents();
            } else {
                this.showToast(data.message || 'Failed to grant consent', 'error');
            }
        } catch (error) {
            console.error('Error granting consent:', error);
            this.showToast('Error granting consent', 'error');
        }
    }

    async revokeConsent() {
        const consentId = document.getElementById('revoke-consent-id').value;
        const reason = document.getElementById('revoke-reason').value;

        if (!reason) {
            this.showToast('Please provide a reason for revocation', 'error');
            return;
        }

        try {
            const response = await fetch('/api/consents/revoke.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    consent_id: consentId,
                    reason: reason
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('Consent revoked successfully', 'success');
                this.closeModals();
                this.loadConsents();
            } else {
                this.showToast(data.message || 'Failed to revoke consent', 'error');
            }
        } catch (error) {
            console.error('Error revoking consent:', error);
            this.showToast('Error revoking consent', 'error');
        }
    }

    async viewHistory(category) {
        try {
            const response = await fetch(`/api/consents/history.php?client_id=${this.currentClientId}&category=${category}`);
            const data = await response.json();

            if (data.success) {
                this.displayHistory(data.history, category);
            } else {
                this.showToast(data.message || 'Failed to load history', 'error');
            }
        } catch (error) {
            console.error('Error loading history:', error);
            this.showToast('Error loading history', 'error');
        }
    }

    displayHistory(history, category) {
        const modal = document.getElementById('history-modal');
        const historyContainer = document.getElementById('history-timeline');
        const categoryTitle = document.getElementById('history-category-title');

        categoryTitle.textContent = this.getCategoryName(category);
        historyContainer.innerHTML = '';

        if (history.length === 0) {
            historyContainer.innerHTML = '<p>No history available for this consent category.</p>';
        } else {
            history.forEach(item => {
                const entry = document.createElement('div');
                entry.className = 'history-entry';
                entry.innerHTML = `
                    <div class="history-date">${new Date(item.action_date).toLocaleString()}</div>
                    <div class="history-action">${item.action}</div>
                    <div class="history-details">${item.details || ''}</div>
                `;
                historyContainer.appendChild(entry);
            });
        }

        modal.style.display = 'flex';
    }

    getCategoryName(category) {
        const names = {
            housing: 'Housing Services',
            health: 'Health Services',
            mental_health: 'Mental Health Services',
            legal: 'Legal Services',
            financial: 'Financial Services',
            general: 'General Information Sharing'
        };
        return names[category] || category;
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    new ConsentManager();
});
