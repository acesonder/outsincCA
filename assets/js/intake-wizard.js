/**
 * OUTSINC - Client Intake Wizard JavaScript
 * Handles multi-step intake process with auto-save, resume, and conditional logic
 */

class IntakeWizard {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 7;
        this.sessionId = null;
        this.formData = {};
        this.autoSaveInterval = null;
        this.isWorkerAssisted = false;
        this.clientId = null;
        
        this.init();
    }
    
    init() {
        this.checkForExistingSession();
        this.setupEventListeners();
        this.startAutoSave();
        this.loadStepContent(this.currentStep);
        this.updateProgressBar();
    }
    
    setupEventListeners() {
        // Navigation buttons
        document.getElementById('btn-next')?.addEventListener('click', () => this.nextStep());
        document.getElementById('btn-prev')?.addEventListener('click', () => this.prevStep());
        document.getElementById('btn-save-exit')?.addEventListener('click', () => this.saveAndExit());
        document.getElementById('btn-complete')?.addEventListener('click', () => this.completeIntake());
        
        // Skip question buttons
        document.querySelectorAll('.btn-skip').forEach(btn => {
            btn.addEventListener('click', (e) => this.skipQuestion(e.target.closest('.question-block')));
        });
        
        // Form inputs - capture changes
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('change', (e) => this.captureFieldData(e.target));
        });
        
        // "Why are we asking?" help buttons
        document.querySelectorAll('.why-asking-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.showWhyAsking(e.target.dataset.question));
        });
        
        // Language selector
        document.getElementById('language-selector')?.addEventListener('change', (e) => {
            this.changeLanguage(e.target.value);
        });
        
        // Text-to-speech toggle
        document.getElementById('tts-toggle')?.addEventListener('change', (e) => {
            this.toggleTextToSpeech(e.target.checked);
        });
        
        // File upload
        document.getElementById('document-upload')?.addEventListener('change', (e) => {
            this.handleFileUpload(e.target.files);
        });
    }
    
    async checkForExistingSession() {
        try {
            const response = await fetch('/api/intake/check-session.php', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });
            
            const data = await response.json();
            
            if (data.success && data.session) {
                if (confirm('You have an incomplete intake session. Would you like to resume?')) {
                    this.resumeSession(data.session);
                }
            }
        } catch (error) {
            console.error('Error checking for existing session:', error);
        }
    }
    
    async resumeSession(session) {
        this.sessionId = session.session_id;
        this.currentStep = session.current_step || 1;
        this.formData = JSON.parse(session.form_data || '{}');
        this.clientId = session.client_id;
        
        // Populate form with saved data
        this.populateFormData();
        this.loadStepContent(this.currentStep);
        this.updateProgressBar();
        
        this.showToast('Session resumed', 'info');
    }
    
    populateFormData() {
        Object.keys(this.formData).forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                if (field.type === 'checkbox') {
                    field.checked = this.formData[fieldName];
                } else if (field.type === 'radio') {
                    if (field.value === this.formData[fieldName]) {
                        field.checked = true;
                    }
                } else {
                    field.value = this.formData[fieldName];
                }
            }
        });
    }
    
    captureFieldData(field) {
        const fieldName = field.name;
        let value;
        
        if (field.type === 'checkbox') {
            value = field.checked;
        } else if (field.type === 'radio') {
            value = field.checked ? field.value : this.formData[fieldName];
        } else {
            value = field.value;
        }
        
        this.formData[fieldName] = value;
    }
    
    startAutoSave() {
        // Auto-save every 30 seconds
        this.autoSaveInterval = setInterval(() => {
            this.saveProgress();
        }, 30000);
    }
    
    async saveProgress() {
        if (!this.sessionId && Object.keys(this.formData).length === 0) {
            return; // Nothing to save yet
        }
        
        try {
            const response = await fetch('/api/intake/save-step.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: this.sessionId,
                    current_step: this.currentStep,
                    form_data: this.formData,
                    client_id: this.clientId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.sessionId = data.session_id;
                console.log('Progress auto-saved');
            }
        } catch (error) {
            console.error('Error saving progress:', error);
        }
    }
    
    async nextStep() {
        // Validate current step
        if (!this.validateStep(this.currentStep)) {
            this.showToast('Please complete required fields or skip them', 'warning');
            return;
        }
        
        // Save progress
        await this.saveProgress();
        
        if (this.currentStep < this.totalSteps) {
            this.currentStep++;
            this.loadStepContent(this.currentStep);
            this.updateProgressBar();
            window.scrollTo(0, 0);
        }
    }
    
    prevStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.loadStepContent(this.currentStep);
            this.updateProgressBar();
            window.scrollTo(0, 0);
        }
    }
    
    validateStep(step) {
        const stepContainer = document.getElementById(`step-${step}`);
        if (!stepContainer) return true;
        
        const requiredFields = stepContainer.querySelectorAll('[required]:not([data-skipped])');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value && field.type !== 'checkbox' && field.type !== 'radio') {
                isValid = false;
                field.classList.add('error');
            } else {
                field.classList.remove('error');
            }
        });
        
        return isValid;
    }
    
    loadStepContent(step) {
        // Hide all steps
        document.querySelectorAll('.wizard-step').forEach(s => {
            s.style.display = 'none';
        });
        
        // Show current step
        const currentStepElement = document.getElementById(`step-${step}`);
        if (currentStepElement) {
            currentStepElement.style.display = 'block';
        }
        
        // Update button visibility
        document.getElementById('btn-prev').style.display = step === 1 ? 'none' : 'inline-block';
        document.getElementById('btn-next').style.display = step === this.totalSteps ? 'none' : 'inline-block';
        document.getElementById('btn-complete').style.display = step === this.totalSteps ? 'inline-block' : 'none';
        
        // Apply conditional logic
        this.applyConditionalLogic(step);
    }
    
    applyConditionalLogic(step) {
        // Show/hide questions based on previous answers
        const conditions = document.querySelectorAll(`#step-${step} [data-show-if]`);
        
        conditions.forEach(element => {
            const condition = element.dataset.showIf;
            const [fieldName, expectedValue] = condition.split('=');
            
            if (this.formData[fieldName] == expectedValue) {
                element.style.display = 'block';
            } else {
                element.style.display = 'none';
            }
        });
    }
    
    updateProgressBar() {
        const progress = (this.currentStep / this.totalSteps) * 100;
        const progressBar = document.querySelector('.progress-fill');
        const progressText = document.querySelector('.progress-text');
        
        if (progressBar) {
            progressBar.style.width = `${progress}%`;
        }
        
        if (progressText) {
            progressText.textContent = `Step ${this.currentStep} of ${this.totalSteps} (${Math.round(progress)}%)`;
        }
    }
    
    skipQuestion(questionBlock) {
        const field = questionBlock.querySelector('input, select, textarea');
        if (field) {
            field.setAttribute('data-skipped', 'true');
            field.removeAttribute('required');
            field.value = '';
            this.formData[field.name] = 'SKIPPED';
            questionBlock.style.opacity = '0.5';
        }
    }
    
    showWhyAsking(questionId) {
        const explanations = {
            'housing': 'We ask about housing to connect you with shelter, housing support, and to understand your immediate safety needs.',
            'substances': 'This helps us provide harm reduction supplies, overdose prevention support, and connect you with treatment if you want it. All answers are confidential.',
            'mental_health': 'Understanding your mental health helps us connect you with counseling, crisis support, and appropriate care. You can skip any questions.',
            'physical_health': 'This helps us understand if you need medical care, prescriptions, or health services. We can help connect you.',
            'income': 'Understanding your income and benefits helps us find financial support, application help, and emergency funds if available.'
        };
        
        const message = explanations[questionId] || 'This information helps us provide better support and connect you with relevant services.';
        this.showModal('Why we ask this', message);
    }
    
    async saveAndExit() {
        await this.saveProgress();
        this.showToast('Progress saved. You can resume anytime.', 'success');
        
        // Schedule reminder notification
        await this.scheduleReminder();
        
        setTimeout(() => {
            window.location.href = '/public/dashboard.php';
        }, 2000);
    }
    
    async scheduleReminder() {
        try {
            await fetch('/api/intake/schedule-reminder.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: this.sessionId
                })
            });
        } catch (error) {
            console.error('Error scheduling reminder:', error);
        }
    }
    
    async completeIntake() {
        // Show confirmation
        if (!confirm('Are you ready to submit your intake? You can review and update your information later.')) {
            return;
        }
        
        // Save final progress
        await this.saveProgress();
        
        try {
            const response = await fetch('/api/intake/complete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: this.sessionId,
                    form_data: this.formData,
                    client_id: this.clientId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showToast('Intake completed successfully!', 'success');
                
                // Show resource suggestions
                if (data.suggested_resources && data.suggested_resources.length > 0) {
                    this.showResourceSuggestions(data.suggested_resources);
                }
                
                // Offer PDF download
                if (data.pdf_url) {
                    this.offerPDFDownload(data.pdf_url);
                }
                
                // Clear auto-save interval
                clearInterval(this.autoSaveInterval);
                
                // Redirect after delay
                setTimeout(() => {
                    window.location.href = '/public/dashboard.php';
                }, 3000);
            } else {
                this.showToast(data.message || 'Error completing intake', 'error');
            }
        } catch (error) {
            console.error('Error completing intake:', error);
            this.showToast('Network error. Please try again.', 'error');
        }
    }
    
    showResourceSuggestions(resources) {
        let html = '<div class="resource-suggestions"><h3>Based on your intake, these resources might help:</h3><ul>';
        resources.forEach(resource => {
            html += `<li><strong>${resource.name}</strong><br>${resource.description}<br><small>${resource.contact}</small></li>`;
        });
        html += '</ul></div>';
        
        this.showModal('Suggested Resources', html);
    }
    
    offerPDFDownload(pdfUrl) {
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = 'intake-summary.pdf';
        link.textContent = 'Download your intake summary (PDF)';
        link.className = 'btn btn-primary';
        
        const container = document.createElement('div');
        container.className = 'pdf-download-offer';
        container.innerHTML = '<p>Your intake summary is ready:</p>';
        container.appendChild(link);
        
        document.querySelector('.wizard-container').appendChild(container);
    }
    
    changeLanguage(lang) {
        // In production, this would load translations
        this.showToast(`Language changed to ${lang}`, 'info');
        // TODO: Implement actual translation system
    }
    
    toggleTextToSpeech(enabled) {
        if (enabled) {
            this.enableTextToSpeech();
        } else {
            this.disableTextToSpeech();
        }
    }
    
    enableTextToSpeech() {
        document.querySelectorAll('.question-label').forEach(label => {
            const speakBtn = document.createElement('button');
            speakBtn.className = 'speak-btn';
            speakBtn.innerHTML = '🔊';
            speakBtn.onclick = () => {
                const text = label.textContent;
                const utterance = new SpeechSynthesisUtterance(text);
                window.speechSynthesis.speak(utterance);
            };
            label.appendChild(speakBtn);
        });
    }
    
    disableTextToSpeech() {
        document.querySelectorAll('.speak-btn').forEach(btn => btn.remove());
    }
    
    async handleFileUpload(files) {
        const formData = new FormData();
        formData.append('session_id', this.sessionId);
        
        Array.from(files).forEach((file, index) => {
            formData.append(`file_${index}`, file);
        });
        
        try {
            const response = await fetch('/api/intake/upload-document.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showToast('Documents uploaded successfully', 'success');
            } else {
                this.showToast(data.message || 'Upload failed', 'error');
            }
        } catch (error) {
            console.error('Error uploading files:', error);
            this.showToast('Upload error. Please try again.', 'error');
        }
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
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    showModal(title, content) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content">
                <h2>${title}</h2>
                <div class="modal-body">${content}</div>
                <button class="btn btn-primary modal-close">Close</button>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.modal-close').addEventListener('click', () => {
            modal.remove();
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }
}

// Initialize wizard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.intakeWizard = new IntakeWizard();
});
