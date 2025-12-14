/**
 * Needs Assessment & Smart Surveys
 * Handles multi-domain assessment with conditional logic and risk scoring
 */

class NeedsAssessment {
    constructor(clientId) {
        this.clientId = clientId;
        this.assessmentId = null;
        this.domains = ['housing', 'substances', 'mental_health', 'safety', 'social_support'];
        this.domainData = {};
        this.answers = {};
        this.baselineAssessment = null;
        this.currentScores = {};
        
        // Define assessment questions with conditional logic
        this.questions = this.getQuestions();
        
        // Bind event listeners
        this.bindEvents();
    }

    init() {
        this.startAssessment();
    }

    bindEvents() {
        document.getElementById('viewHistoryBtn').addEventListener('click', () => this.viewHistory());
        document.getElementById('exportPdfBtn').addEventListener('click', () => this.exportPdf());
        document.getElementById('compareBtn').addEventListener('click', () => this.showComparison());
        document.getElementById('saveProgressBtn').addEventListener('click', () => this.saveProgress());
        document.getElementById('completeAssessmentBtn').addEventListener('click', () => this.completeAssessment());
    }

    async startAssessment() {
        try {
            const response = await fetch('/api/assessments/start.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ client_id: this.clientId })
            });

            const data = await response.json();
            
            if (data.success) {
                this.assessmentId = data.assessment_id;
                this.baselineAssessment = data.baseline_assessment;
                
                if (this.baselineAssessment) {
                    document.getElementById('compareBtn').style.display = 'inline-block';
                }
                
                // Load questions for all domains
                this.loadAllQuestions();
                
                // Load any existing answers
                if (data.existing_answers) {
                    this.answers = data.existing_answers;
                    this.populateExistingAnswers();
                }
                
                this.showToast('Assessment started', 'success');
            } else {
                this.showToast(data.message || 'Failed to start assessment', 'error');
            }
        } catch (error) {
            console.error('Error starting assessment:', error);
            this.showToast('Error starting assessment', 'error');
        }
    }

    loadAllQuestions() {
        this.domains.forEach(domain => {
            const container = document.getElementById(`${domain}-questions`);
            const domainQuestions = this.questions[domain];
            
            domainQuestions.forEach((question, index) => {
                const questionEl = this.createQuestionElement(domain, question, index);
                container.appendChild(questionEl);
            });
        });
    }

    createQuestionElement(domain, question, index) {
        const div = document.createElement('div');
        div.className = 'question-item';
        div.dataset.questionId = question.id;
        div.dataset.domain = domain;
        
        if (question.condition) {
            div.dataset.condition = JSON.stringify(question.condition);
            div.style.display = 'none';
        }
        
        let inputHtml = '';
        
        if (question.type === 'select') {
            inputHtml = `
                <select id="${question.id}" name="${question.id}" onchange="assessment.handleAnswer('${domain}', '${question.id}', this.value)">
                    <option value="">Select...</option>
                    ${question.options.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('')}
                    <option value="skip">Skip this question</option>
                    <option value="dont_know">I don't know</option>
                    <option value="prefer_not_say">Prefer not to say</option>
                </select>
            `;
        } else if (question.type === 'radio') {
            inputHtml = `
                <div class="radio-group">
                    ${question.options.map(opt => `
                        <label class="radio-label">
                            <input type="radio" name="${question.id}" value="${opt.value}" 
                                onchange="assessment.handleAnswer('${domain}', '${question.id}', this.value)">
                            ${opt.label}
                        </label>
                    `).join('')}
                    <label class="radio-label special-option">
                        <input type="radio" name="${question.id}" value="skip" 
                            onchange="assessment.handleAnswer('${domain}', '${question.id}', this.value)">
                        Skip this question
                    </label>
                </div>
            `;
        } else if (question.type === 'scale') {
            inputHtml = `
                <div class="scale-container">
                    <input type="range" id="${question.id}" name="${question.id}" 
                        min="${question.min}" max="${question.max}" step="1" value="${question.min}"
                        oninput="assessment.handleAnswer('${domain}', '${question.id}', this.value); 
                                 document.getElementById('${question.id}-value').textContent = this.value;">
                    <div class="scale-labels">
                        <span>${question.minLabel}</span>
                        <span id="${question.id}-value">${question.min}</span>
                        <span>${question.maxLabel}</span>
                    </div>
                </div>
            `;
        } else if (question.type === 'textarea') {
            inputHtml = `
                <textarea id="${question.id}" name="${question.id}" rows="3" 
                    onblur="assessment.handleAnswer('${domain}', '${question.id}', this.value)"
                    placeholder="Your answer..."></textarea>
                <button type="button" class="skip-btn" onclick="assessment.handleAnswer('${domain}', '${question.id}', 'skip')">
                    Skip this question
                </button>
            `;
        }
        
        div.innerHTML = `
            <label class="question-label">
                <span class="question-number">${index + 1}.</span>
                ${question.text}
                ${question.explanation ? `
                    <button type="button" class="why-btn" onclick="assessment.showExplanation('${question.explanation}')">
                        Why?
                    </button>
                ` : ''}
            </label>
            ${inputHtml}
        `;
        
        return div;
    }

    handleAnswer(domain, questionId, value) {
        // Store answer
        this.answers[questionId] = value;
        
        // Update conditional questions
        this.updateConditionalQuestions();
        
        // Calculate domain score
        this.calculateDomainScore(domain);
        
        // Update progress
        this.updateProgress();
        
        // Auto-save after 2 seconds of inactivity
        clearTimeout(this.autoSaveTimeout);
        this.autoSaveTimeout = setTimeout(() => this.saveProgress(), 2000);
    }

    updateConditionalQuestions() {
        document.querySelectorAll('[data-condition]').forEach(questionEl => {
            const condition = JSON.parse(questionEl.dataset.condition);
            const shouldShow = this.evaluateCondition(condition);
            questionEl.style.display = shouldShow ? 'block' : 'none';
        });
    }

    evaluateCondition(condition) {
        const answer = this.answers[condition.question];
        
        if (!answer || answer === 'skip' || answer === 'dont_know' || answer === 'prefer_not_say') {
            return false;
        }
        
        if (condition.operator === '===') {
            return answer === condition.value;
        } else if (condition.operator === '!==') {
            return answer !== condition.value;
        } else if (condition.operator === 'includes') {
            return condition.value.includes(answer);
        }
        
        return false;
    }

    calculateDomainScore(domain) {
        const domainQuestions = this.questions[domain];
        let totalScore = 0;
        let answeredQuestions = 0;
        
        domainQuestions.forEach(question => {
            const answer = this.answers[question.id];
            
            if (answer && answer !== 'skip' && answer !== 'dont_know' && answer !== 'prefer_not_say') {
                if (question.scoring) {
                    const scoreValue = question.scoring[answer] || 0;
                    totalScore += scoreValue;
                    answeredQuestions++;
                }
            }
        });
        
        const avgScore = answeredQuestions > 0 ? Math.round(totalScore / answeredQuestions) : 0;
        this.currentScores[domain] = avgScore;
        
        // Update UI
        this.updateDomainScoreDisplay(domain, avgScore);
        
        // Check for high risk
        if (avgScore >= 7) {
            this.triggerHighRiskAlert(domain, avgScore);
        }
        
        return avgScore;
    }

    updateDomainScoreDisplay(domain, score) {
        const scoreEl = document.querySelector(`#${domain}-score .score-value`);
        const statusEl = document.getElementById(`${domain}-status`);
        
        if (scoreEl) {
            scoreEl.textContent = score;
            scoreEl.className = 'score-value';
            
            if (score >= 7) {
                scoreEl.classList.add('risk-high');
            } else if (score >= 4) {
                scoreEl.classList.add('risk-medium');
            } else {
                scoreEl.classList.add('risk-low');
            }
        }
        
        if (statusEl) {
            const domainQuestions = this.questions[domain];
            const answeredCount = domainQuestions.filter(q => this.answers[q.id]).length;
            const progress = Math.round((answeredCount / domainQuestions.length) * 100);
            
            if (progress === 100) {
                statusEl.textContent = 'Complete';
                statusEl.className = 'domain-status status-complete';
            } else if (progress > 0) {
                statusEl.textContent = `${progress}% Complete`;
                statusEl.className = 'domain-status status-in-progress';
            } else {
                statusEl.textContent = 'Not Started';
                statusEl.className = 'domain-status';
            }
        }
    }

    triggerHighRiskAlert(domain, score) {
        this.showToast(`⚠️ High risk detected in ${this.getDomainLabel(domain)} (Score: ${score}/10). Worker will be notified.`, 'warning', 5000);
        
        // Send alert to backend
        fetch('/api/assessments/alert.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                assessment_id: this.assessmentId,
                client_id: this.clientId,
                domain: domain,
                score: score
            })
        });
    }

    updateProgress() {
        let totalQuestions = 0;
        let answeredQuestions = 0;
        
        this.domains.forEach(domain => {
            const domainQuestions = this.questions[domain];
            totalQuestions += domainQuestions.length;
            answeredQuestions += domainQuestions.filter(q => this.answers[q.id]).length;
        });
        
        const progress = Math.round((answeredQuestions / totalQuestions) * 100);
        
        document.getElementById('overallProgress').style.width = `${progress}%`;
        document.getElementById('progressText').textContent = `${progress}% Complete`;
        
        // Enable complete button if all domains have at least 50% completion
        const allDomainsStarted = this.domains.every(domain => {
            const domainQuestions = this.questions[domain];
            const answeredCount = domainQuestions.filter(q => this.answers[q.id]).length;
            return (answeredCount / domainQuestions.length) >= 0.5;
        });
        
        document.getElementById('completeAssessmentBtn').disabled = !allDomainsStarted;
    }

    async saveProgress() {
        try {
            const response = await fetch('/api/assessments/save-answers.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    assessment_id: this.assessmentId,
                    client_id: this.clientId,
                    answers: this.answers,
                    scores: this.currentScores
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showToast('Progress saved', 'success');
            } else {
                this.showToast(data.message || 'Failed to save progress', 'error');
            }
        } catch (error) {
            console.error('Error saving progress:', error);
            this.showToast('Error saving progress', 'error');
        }
    }

    async completeAssessment() {
        if (!confirm('Are you ready to complete this assessment? You can always come back to update your answers later.')) {
            return;
        }
        
        try {
            const response = await fetch('/api/assessments/complete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    assessment_id: this.assessmentId,
                    client_id: this.clientId,
                    answers: this.answers,
                    scores: this.currentScores
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showToast('Assessment completed! 6-month follow-up has been scheduled.', 'success');
                
                if (data.pdf_url) {
                    // Offer to download PDF
                    if (confirm('Would you like to download a PDF copy of your assessment?')) {
                        window.open(data.pdf_url, '_blank');
                    }
                }
                
                // Redirect after 3 seconds
                setTimeout(() => {
                    window.location.href = '/public/dashboard.php';
                }, 3000);
            } else {
                this.showToast(data.message || 'Failed to complete assessment', 'error');
            }
        } catch (error) {
            console.error('Error completing assessment:', error);
            this.showToast('Error completing assessment', 'error');
        }
    }

    async viewHistory() {
        try {
            const response = await fetch(`/api/assessments/history.php?client_id=${this.clientId}`);
            const data = await response.json();
            
            if (data.success && data.assessments) {
                this.showHistoryModal(data.assessments);
            } else {
                this.showToast('No assessment history found', 'info');
            }
        } catch (error) {
            console.error('Error loading history:', error);
            this.showToast('Error loading history', 'error');
        }
    }

    showHistoryModal(assessments) {
        const modal = document.getElementById('historyModal');
        const timeline = document.getElementById('historyTimeline');
        
        timeline.innerHTML = assessments.map(assessment => `
            <div class="history-item">
                <div class="history-date">${new Date(assessment.completed_at).toLocaleDateString()}</div>
                <div class="history-scores">
                    ${Object.entries(assessment.scores).map(([domain, score]) => `
                        <span class="history-score risk-${score >= 7 ? 'high' : score >= 4 ? 'medium' : 'low'}">
                            ${this.getDomainLabel(domain)}: ${score}/10
                        </span>
                    `).join('')}
                </div>
            </div>
        `).join('');
        
        this.createHistoryChart(assessments);
        modal.style.display = 'block';
    }

    createHistoryChart(assessments) {
        const ctx = document.getElementById('historyChart');
        
        if (this.historyChart) {
            this.historyChart.destroy();
        }
        
        const datasets = this.domains.map((domain, index) => ({
            label: this.getDomainLabel(domain),
            data: assessments.map(a => a.scores[domain] || 0),
            borderColor: this.getColorForDomain(index),
            backgroundColor: this.getColorForDomain(index) + '33',
            tension: 0.4
        }));
        
        this.historyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: assessments.map(a => new Date(a.completed_at).toLocaleDateString()),
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10,
                        title: {
                            display: true,
                            text: 'Risk Score'
                        }
                    }
                }
            }
        });
    }

    showComparison() {
        if (!this.baselineAssessment) {
            this.showToast('No baseline assessment to compare to', 'info');
            return;
        }
        
        const modal = document.getElementById('comparisonModal');
        this.createComparisonChart();
        modal.style.display = 'block';
    }

    createComparisonChart() {
        const ctx = document.getElementById('comparisonChart');
        
        if (this.comparisonChart) {
            this.comparisonChart.destroy();
        }
        
        const baselineScores = this.domains.map(domain => this.baselineAssessment.scores[domain] || 0);
        const currentScores = this.domains.map(domain => this.currentScores[domain] || 0);
        
        this.comparisonChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: this.domains.map(d => this.getDomainLabel(d)),
                datasets: [
                    {
                        label: 'Baseline',
                        data: baselineScores,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Current',
                        data: currentScores,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10,
                        title: {
                            display: true,
                            text: 'Risk Score'
                        }
                    }
                }
            }
        });
    }

    closeHistoryModal() {
        document.getElementById('historyModal').style.display = 'none';
    }

    closeComparisonModal() {
        document.getElementById('comparisonModal').style.display = 'none';
    }

    exportPdf() {
        window.open(`/api/assessments/export-pdf.php?assessment_id=${this.assessmentId}`, '_blank');
    }

    toggleDomain(domain) {
        const content = document.getElementById(`${domain}-content`);
        const card = document.querySelector(`[data-domain="${domain}"]`);
        
        if (content.style.display === 'none' || content.style.display === '') {
            content.style.display = 'block';
            card.classList.add('expanded');
        } else {
            content.style.display = 'none';
            card.classList.remove('expanded');
        }
    }

    showExplanation(explanation) {
        alert(explanation);
    }

    getDomainLabel(domain) {
        const labels = {
            housing: 'Housing',
            substances: 'Substances',
            mental_health: 'Mental Health',
            safety: 'Safety',
            social_support: 'Social Support'
        };
        return labels[domain] || domain;
    }

    getColorForDomain(index) {
        const colors = [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF'
        ];
        return colors[index % colors.length];
    }

    showToast(message, type = 'info', duration = 3000) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    populateExistingAnswers() {
        Object.entries(this.answers).forEach(([questionId, value]) => {
            const input = document.getElementById(questionId) || document.querySelector(`[name="${questionId}"][value="${value}"]`);
            if (input) {
                if (input.type === 'radio') {
                    input.checked = true;
                } else {
                    input.value = value;
                }
            }
        });
        
        this.updateConditionalQuestions();
        this.domains.forEach(domain => this.calculateDomainScore(domain));
        this.updateProgress();
    }

    getQuestions() {
        // Define assessment questions with conditional logic and scoring
        return {
            housing: [
                {
                    id: 'housing_current',
                    text: 'What is your current housing situation?',
                    type: 'select',
                    options: [
                        { value: 'housed', label: 'I have stable housing' },
                        { value: 'temporary', label: 'Temporary housing (shelter, transitional)' },
                        { value: 'couch_surfing', label: 'Staying with friends/family temporarily' },
                        { value: 'homeless', label: 'Homeless (outside, vehicle, etc.)' }
                    ],
                    scoring: { housed: 0, temporary: 4, couch_surfing: 6, homeless: 10 },
                    explanation: 'We ask this to understand your current housing stability and connect you with appropriate resources.'
                },
                {
                    id: 'housing_stability',
                    text: 'How long have you been in your current situation?',
                    type: 'select',
                    options: [
                        { value: 'more_1year', label: 'More than 1 year' },
                        { value: '6_12months', label: '6-12 months' },
                        { value: '3_6months', label: '3-6 months' },
                        { value: '1_3months', label: '1-3 months' },
                        { value: 'less_1month', label: 'Less than 1 month' }
                    ],
                    scoring: { more_1year: 0, '6_12months': 2, '3_6months': 4, '1_3months': 6, less_1month: 8 }
                },
                {
                    id: 'housing_risk',
                    text: 'Are you at risk of losing your current housing?',
                    type: 'select',
                    options: [
                        { value: 'no', label: 'No, housing is secure' },
                        { value: 'maybe', label: 'Possibly/uncertain' },
                        { value: 'yes', label: 'Yes, at immediate risk' }
                    ],
                    scoring: { no: 0, maybe: 5, yes: 10 }
                },
                {
                    id: 'housing_affordability',
                    text: 'Can you afford your housing costs?',
                    type: 'select',
                    condition: { question: 'housing_current', operator: 'includes', value: ['housed', 'temporary'] },
                    options: [
                        { value: 'yes', label: 'Yes, without difficulty' },
                        { value: 'sometimes', label: 'Sometimes struggle' },
                        { value: 'no', label: 'No, cannot afford' }
                    ],
                    scoring: { yes: 0, sometimes: 5, no: 10 }
                }
            ],
            substances: [
                {
                    id: 'substance_use',
                    text: 'Do you currently use any substances?',
                    type: 'select',
                    options: [
                        { value: 'no', label: 'No' },
                        { value: 'yes', label: 'Yes' }
                    ],
                    scoring: { no: 0, yes: 5 },
                    explanation: 'We ask this to offer harm reduction support and connect you with resources if needed. Your answer is confidential.'
                },
                {
                    id: 'substance_frequency',
                    text: 'How often do you use substances?',
                    type: 'select',
                    condition: { question: 'substance_use', operator: '===', value: 'yes' },
                    options: [
                        { value: 'daily', label: 'Daily' },
                        { value: 'weekly', label: 'Several times a week' },
                        { value: 'occasionally', label: 'Occasionally' }
                    ],
                    scoring: { daily: 10, weekly: 7, occasionally: 3 }
                },
                {
                    id: 'harm_reduction',
                    text: 'Do you use harm reduction practices (clean supplies, safe consumption, naloxone)?',
                    type: 'select',
                    condition: { question: 'substance_use', operator: '===', value: 'yes' },
                    options: [
                        { value: 'always', label: 'Always' },
                        { value: 'sometimes', label: 'Sometimes' },
                        { value: 'never', label: 'Never/Rarely' }
                    ],
                    scoring: { always: 0, sometimes: 5, never: 10 }
                }
            ],
            mental_health: [
                {
                    id: 'mh_symptoms',
                    text: 'In the past 2 weeks, how often have you experienced mental health symptoms (anxiety, depression, etc.)?',
                    type: 'select',
                    options: [
                        { value: 'not', label: 'Not at all' },
                        { value: 'few_days', label: 'Several days' },
                        { value: 'more_half', label: 'More than half the days' },
                        { value: 'nearly_every', label: 'Nearly every day' }
                    ],
                    scoring: { not: 0, few_days: 3, more_half: 7, nearly_every: 10 },
                    explanation: 'Mental health is important. We ask this to connect you with appropriate support.'
                },
                {
                    id: 'mh_treatment',
                    text: 'Are you currently receiving mental health treatment or support?',
                    type: 'select',
                    options: [
                        { value: 'yes_engaged', label: 'Yes, actively engaged' },
                        { value: 'yes_not_regular', label: 'Yes, but not regularly' },
                        { value: 'no_want', label: 'No, but I would like to' },
                        { value: 'no_not_want', label: 'No, and not interested' }
                    ],
                    scoring: { yes_engaged: 0, yes_not_regular: 4, no_want: 7, no_not_want: 5 }
                }
            ],
            safety: [
                {
                    id: 'safety_concerns',
                    text: 'Do you have current safety concerns?',
                    type: 'select',
                    options: [
                        { value: 'no', label: 'No concerns' },
                        { value: 'minor', label: 'Minor concerns' },
                        { value: 'significant', label: 'Significant concerns' }
                    ],
                    scoring: { no: 0, minor: 5, significant: 10 },
                    explanation: 'Your safety is important. We ask this to offer appropriate support and resources.'
                },
                {
                    id: 'domestic_violence',
                    text: 'Are you currently experiencing or at risk of domestic violence?',
                    type: 'select',
                    options: [
                        { value: 'no', label: 'No' },
                        { value: 'yes', label: 'Yes' }
                    ],
                    scoring: { no: 0, yes: 10 }
                }
            ],
            social_support: [
                {
                    id: 'social_support_level',
                    text: 'How much social support do you feel you have?',
                    type: 'scale',
                    min: 0,
                    max: 10,
                    minLabel: 'No support',
                    maxLabel: 'Strong support',
                    scoring: (value) => 10 - parseInt(value)
                },
                {
                    id: 'family_contact',
                    text: 'Do you have regular contact with family or friends?',
                    type: 'select',
                    options: [
                        { value: 'yes_regular', label: 'Yes, regularly' },
                        { value: 'yes_occasional', label: 'Yes, occasionally' },
                        { value: 'no', label: 'No, isolated' }
                    ],
                    scoring: { yes_regular: 0, yes_occasional: 5, no: 10 }
                }
            ]
        };
    }
}
