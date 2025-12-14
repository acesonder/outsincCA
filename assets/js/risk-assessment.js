/**
 * Risk Assessment & Safety Planning
 * Handles risk evaluation, safety plan creation, and crisis protocols
 */

class RiskAssessment {
    constructor() {
        this.clientId = this.getClientId();
        this.assessmentId = null;
        this.risks = {};
        this.safetyPlan = {};
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.loadExistingAssessment();
        this.loadHistory();
        this.updateConditionalQuestions();
    }

    getClientId() {
        // Get from session or URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('client_id') || sessionStorage.getItem('current_client_id');
    }

    attachEventListeners() {
        // Risk input changes
        document.querySelectorAll('.risk-input').forEach(input => {
            input.addEventListener('change', () => {
                this.updateConditionalQuestions();
                this.calculateRiskLevels();
                this.autoSave();
            });
        });

        // Safety plan inputs
        document.querySelectorAll('.plan-input').forEach(input => {
            input.addEventListener('input', () => this.debouncedAutoSave());
        });

        // Buttons
        document.getElementById('saveRisksBtn')?.addEventListener('click', () => this.saveRisks());
        document.getElementById('savePlanBtn')?.addEventListener('click', () => this.saveSafetyPlan());
        document.getElementById('completePlanBtn')?.addEventListener('click', () => this.completeAndPrint());
        document.getElementById('backToRiskBtn')?.addEventListener('click', () => this.showRiskSection());
    }

    updateConditionalQuestions() {
        document.querySelectorAll('.conditional').forEach(question => {
            const condition = question.dataset.showIf;
            if (condition) {
                const [fieldName, values] = condition.split(':');
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    const fieldValue = field.value;
                    const shouldShow = values.split(',').includes(fieldValue);
                    question.style.display = shouldShow ? 'block' : 'none';
                    if (!shouldShow) {
                        // Clear hidden field
                        const input = question.querySelector('.risk-input');
                        if (input) input.value = '';
                    }
                }
            }
        });
    }

    calculateRiskLevels() {
        const categories = {
            self_harm: this.calculateSelfHarmRisk(),
            overdose: this.calculateOverdoseRisk(),
            domestic_violence: this.calculateDomesticViolenceRisk(),
            housing: this.calculateHousingRisk()
        };

        // Update UI for each category
        Object.keys(categories).forEach(category => {
            const level = categories[category];
            const element = document.querySelector(`[data-category="${category}"] .risk-level`);
            if (element) {
                element.textContent = this.formatRiskLevel(level);
                element.dataset.level = level;
            }
        });

        // Calculate overall risk
        const overallRisk = this.calculateOverallRisk(categories);
        const overallBadge = document.getElementById('overallRiskBadge');
        if (overallBadge) {
            overallBadge.textContent = this.formatRiskLevel(overallRisk);
            overallBadge.dataset.level = overallRisk;
        }
        document.getElementById('riskSummary').style.display = 'flex';

        this.risks = categories;
        return categories;
    }

    calculateSelfHarmRisk() {
        const thoughts = document.querySelector('[name="self_harm_thoughts"]')?.value;
        const plan = document.querySelector('[name="self_harm_plan"]')?.value;
        const recent = document.querySelector('[name="self_harm_recent"]')?.value;

        if (thoughts === 'skip' || !thoughts) return 'not-assessed';
        
        // High risk: Often thoughts + specific plan OR recent attempt
        if ((thoughts === 'often' && plan === 'specific') || recent === 'yes') {
            return 'high';
        }
        
        // Medium risk: Sometimes/often thoughts OR vague plan
        if (thoughts === 'sometimes' || thoughts === 'often' || plan === 'vague') {
            return 'medium';
        }
        
        // Low risk: Rarely or no thoughts
        return 'low';
    }

    calculateOverdoseRisk() {
        const use = document.querySelector('[name="substance_use"]')?.value;
        const overdose = document.querySelector('[name="recent_overdose"]')?.value;
        const alone = document.querySelector('[name="use_alone"]')?.value;
        const naloxone = document.querySelector('[name="naloxone_access"]')?.value;

        if (use === 'skip' || !use || use === 'no') return 'not-assessed';
        
        // High risk: Recent OD OR daily use alone without naloxone
        if (overdose === 'yes' || (use === 'daily' && (alone === 'always' || alone === 'usually') && naloxone === 'no')) {
            return 'high';
        }
        
        // Medium risk: Regular use OR use alone sometimes
        if (use === 'regular' || use === 'daily' || alone === 'sometimes' || alone === 'usually') {
            return 'medium';
        }
        
        // Low risk: Occasional use with safety measures
        return 'low';
    }

    calculateDomesticViolenceRisk() {
        const current = document.querySelector('[name="domestic_violence_current"]')?.value;
        const harm = document.querySelector('[name="physical_harm_recent"]')?.value;
        const children = document.querySelector('[name="children_present"]')?.value;

        if (current === 'skip' || !current) return 'not-assessed';
        
        // High risk: Often unsafe + recent harm OR children present
        if (current === 'yes' && (harm === 'yes' || children === 'yes')) {
            return 'high';
        }
        
        // Medium risk: Sometimes unsafe OR often unsafe without recent escalation
        if (current === 'sometimes' || current === 'yes') {
            return 'medium';
        }
        
        // Low risk: Feels safe
        return 'low';
    }

    calculateHousingRisk() {
        const risk = document.querySelector('[name="housing_risk"]')?.value;
        const eviction = document.querySelector('[name="eviction_notice"]')?.value;

        if (risk === 'skip' || !risk) return 'not-assessed';
        
        // High risk: Already homeless OR imminent risk with eviction notice
        if (risk === 'homeless' || (risk === 'imminent' && eviction === 'yes')) {
            return 'high';
        }
        
        // Medium risk: Imminent or uncertain
        if (risk === 'imminent' || risk === 'uncertain') {
            return 'medium';
        }
        
        // Low risk: Stable housing
        return 'low';
    }

    calculateOverallRisk(categories) {
        const levels = Object.values(categories).filter(l => l !== 'not-assessed');
        if (levels.length === 0) return 'not-assessed';
        
        // If any high, overall is high
        if (levels.includes('high')) return 'high';
        
        // If 2+ medium, overall is high
        const mediumCount = levels.filter(l => l === 'medium').length;
        if (mediumCount >= 2) return 'high';
        
        // If any medium, overall is medium
        if (levels.includes('medium')) return 'medium';
        
        // All low
        return 'low';
    }

    formatRiskLevel(level) {
        const labels = {
            'not-assessed': 'Not Assessed',
            'low': 'Low Risk',
            'medium': 'Medium Risk',
            'high': 'High Risk'
        };
        return labels[level] || 'Unknown';
    }

    async saveRisks() {
        try {
            // Collect all risk data
            const riskData = {};
            document.querySelectorAll('.risk-input').forEach(input => {
                riskData[input.name] = input.value;
            });

            const response = await fetch('/api/risks/save-risks.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    client_id: this.clientId,
                    assessment_id: this.assessmentId,
                    risks: riskData,
                    risk_levels: this.risks
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.assessmentId = result.assessment_id;
                this.showToast('Risk assessment saved', 'success');
                
                // If high risk, show alert and notifications
                if (this.calculateOverallRisk(this.risks) === 'high') {
                    this.handleHighRiskAlert();
                }
                
                // Show safety plan section
                document.getElementById('safetyPlanSection').style.display = 'block';
                document.getElementById('safetyPlanSection').scrollIntoView({ behavior: 'smooth' });
            } else {
                this.showToast(result.message || 'Error saving assessment', 'error');
            }
        } catch (error) {
            console.error('Save error:', error);
            this.showToast('Error saving risk assessment', 'error');
        }
    }

    async saveSafetyPlan() {
        try {
            // Collect safety plan data
            const planData = {};
            document.querySelectorAll('.plan-input').forEach(input => {
                planData[input.name] = input.value;
            });

            const response = await fetch('/api/risks/save-plan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    client_id: this.clientId,
                    assessment_id: this.assessmentId,
                    plan: planData
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.safetyPlan = planData;
                this.showToast('Safety plan saved', 'success');
                document.getElementById('completePlanBtn').style.display = 'inline-block';
            } else {
                this.showToast(result.message || 'Error saving plan', 'error');
            }
        } catch (error) {
            console.error('Save error:', error);
            this.showToast('Error saving safety plan', 'error');
        }
    }

    async completeAndPrint() {
        try {
            const response = await fetch('/api/risks/complete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    client_id: this.clientId,
                    assessment_id: this.assessmentId
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.showToast('Assessment complete!', 'success');
                this.generateWalletCard();
                setTimeout(() => window.print(), 500);
            }
        } catch (error) {
            console.error('Complete error:', error);
            this.showToast('Error completing assessment', 'error');
        }
    }

    generateWalletCard() {
        // Populate wallet card template
        const contacts = [];
        for (let i = 1; i <= 3; i++) {
            const name = document.querySelector(`[name="contact_${i}_name"]`)?.value;
            const phone = document.querySelector(`[name="contact_${i}_phone"]`)?.value;
            if (name && phone) {
                contacts.push(`${name}: ${phone}`);
            }
        }
        document.getElementById('walletContacts').innerHTML = contacts.join('<br>');
        
        const coping = document.querySelector('[name="coping_strategies"]')?.value;
        document.getElementById('walletCoping').textContent = coping?.substring(0, 100) || 'See full plan';
        
        const places = document.querySelector('[name="social_distractions"]')?.value;
        document.getElementById('walletPlaces').textContent = places?.substring(0, 100) || 'See full plan';
        
        document.getElementById('walletCardTemplate').style.display = 'block';
    }

    handleHighRiskAlert() {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content">
                <h2 style="color: #dc3545;">High Risk Detected</h2>
                <p>This assessment indicates high risk. Your assigned worker and supervisor have been notified and will reach out within 1 hour.</p>
                <p><strong>If you need immediate help:</strong></p>
                <ul>
                    <li>Crisis Text Line: Text HOME to 741741</li>
                    <li>National Suicide Prevention Lifeline: 1-800-273-8255</li>
                    <li>Call 911 for emergency services</li>
                </ul>
                <button class="btn btn-primary" onclick="this.closest('.modal-overlay').remove()">I Understand</button>
            </div>
        `;
        document.body.appendChild(modal);
    }

    showRiskSection() {
        document.getElementById('riskAssessmentSection').scrollIntoView({ behavior: 'smooth' });
    }

    async loadExistingAssessment() {
        if (!this.clientId) return;
        
        try {
            const response = await fetch(`/api/risks/start.php?client_id=${this.clientId}`);
            const result = await response.json();
            
            if (result.success && result.assessment) {
                this.assessmentId = result.assessment.id;
                // Populate form with existing data
                if (result.assessment.risks) {
                    Object.keys(result.assessment.risks).forEach(key => {
                        const input = document.querySelector(`[name="${key}"]`);
                        if (input) input.value = result.assessment.risks[key];
                    });
                    this.updateConditionalQuestions();
                    this.calculateRiskLevels();
                }
                if (result.assessment.plan) {
                    Object.keys(result.assessment.plan).forEach(key => {
                        const input = document.querySelector(`[name="${key}"]`);
                        if (input) input.value = result.assessment.plan[key];
                    });
                    document.getElementById('safetyPlanSection').style.display = 'block';
                }
            }
        } catch (error) {
            console.error('Load error:', error);
        }
    }

    async loadHistory() {
        if (!this.clientId) return;
        
        try {
            const response = await fetch(`/api/risks/history.php?client_id=${this.clientId}`);
            const result = await response.json();
            
            if (result.success && result.history && result.history.length > 0) {
                this.displayHistory(result.history);
                this.displayTrendChart(result.history);
            }
        } catch (error) {
            console.error('History load error:', error);
        }
    }

    displayHistory(history) {
        const container = document.getElementById('riskHistoryContent');
        container.innerHTML = history.map(assessment => `
            <div class="history-item">
                <div class="history-date">${new Date(assessment.created_at).toLocaleDateString()}</div>
                <div class="history-risks">
                    ${Object.keys(assessment.risk_levels || {}).map(category => `
                        <span class="risk-badge" data-level="${assessment.risk_levels[category]}">
                            ${category}: ${this.formatRiskLevel(assessment.risk_levels[category])}
                        </span>
                    `).join('')}
                </div>
            </div>
        `).join('');
        
        document.getElementById('riskHistorySection').style.display = 'block';
    }

    displayTrendChart(history) {
        const canvas = document.getElementById('riskTrendChart');
        canvas.style.display = 'block';
        
        const ctx = canvas.getContext('2d');
        const dates = history.map(h => new Date(h.created_at).toLocaleDateString());
        
        // Convert risk levels to numeric scores
        const riskToScore = { 'low': 1, 'medium': 2, 'high': 3, 'not-assessed': 0 };
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Overall Risk Level',
                    data: history.map(h => riskToScore[h.overall_risk] || 0),
                    borderColor: '#4CAF50',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 3,
                        ticks: {
                            callback: function(value) {
                                const labels = ['N/A', 'Low', 'Medium', 'High'];
                                return labels[value] || '';
                            }
                        }
                    }
                }
            }
        });
    }

    autoSave() {
        // Auto-save implementation
        clearTimeout(this.autoSaveTimeout);
        this.autoSaveTimeout = setTimeout(() => {
            if (this.assessmentId) {
                this.saveRisks();
            }
        }, 2000);
    }

    debouncedAutoSave() {
        clearTimeout(this.debounceTimeout);
        this.debounceTimeout = setTimeout(() => this.autoSave(), 2000);
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}
