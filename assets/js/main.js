/**
 * OUTSINC - Main JavaScript
 * Outreach Someone In Need of Change
 * Client-side functionality for UI interactions, AJAX, and animations
 */

// ===== GLOBAL APP OBJECT =====
const OUTSINC = {
    config: {
        apiUrl: '/api',
        animationDuration: 300,
        toastDuration: 5000
    },
    
    init() {
        this.initTheme();
        this.initNavigation();
        this.initModals();
        this.initForms();
        this.initToasts();
        this.initBackToTop();
        this.initAccessibility();
        console.log('OUTSINC initialized');
    },
    
    // ===== THEME MANAGEMENT =====
    initTheme() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        const savedContrast = localStorage.getItem('contrast') || 'normal';
        const savedFontSize = localStorage.getItem('fontSize') || 'medium';
        const savedDyslexia = localStorage.getItem('dyslexia') === 'true';
        
        this.setTheme(savedTheme);
        this.setContrast(savedContrast);
        this.setFontSize(savedFontSize);
        if (savedDyslexia) this.setDyslexiaFriendly(true);
        
        // System theme detection
        if (savedTheme === 'auto') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.setTheme(prefersDark ? 'dark' : 'light');
            
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (localStorage.getItem('theme') === 'auto') {
                    this.setTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    },
    
    setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    },
    
    toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme');
        const newTheme = current === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    },
    
    setContrast(contrast) {
        document.documentElement.setAttribute('data-contrast', contrast);
        localStorage.setItem('contrast', contrast);
    },
    
    setFontSize(size) {
        document.body.classList.remove('font-small', 'font-medium', 'font-large');
        document.body.classList.add(`font-${size}`);
        localStorage.setItem('fontSize', size);
    },
    
    setDyslexiaFriendly(enabled) {
        if (enabled) {
            document.body.classList.add('dyslexia-friendly');
        } else {
            document.body.classList.remove('dyslexia-friendly');
        }
        localStorage.setItem('dyslexia', enabled);
    },
    
    // ===== NAVIGATION =====
    initNavigation() {
        // Hamburger menu
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.navbar-menu');
        
        if (hamburger && navMenu) {
            hamburger.addEventListener('click', () => {
                hamburger.classList.toggle('active');
                navMenu.classList.toggle('active');
            });
        }
        
        // Close mobile menu when clicking a link
        document.querySelectorAll('.navbar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    hamburger?.classList.remove('active');
                    navMenu?.classList.remove('active');
                }
            });
        });
        
        // Active page highlighting
        const currentPath = window.location.pathname;
        document.querySelectorAll('.navbar-link').forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });
    },
    
    // ===== MODAL MANAGEMENT =====
    initModals() {
        // Close modal on backdrop click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-backdrop')) {
                this.closeModal(e.target.querySelector('.modal').id);
            }
        });
        
        // Close modal on close button click
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.modal');
                this.closeModal(modal.id);
            });
        });
        
        // ESC key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal-backdrop.show');
                if (openModal) {
                    const modal = openModal.querySelector('.modal');
                    this.closeModal(modal.id);
                }
            }
        });
    },
    
    showModal(modalId) {
        const backdrop = document.getElementById(modalId)?.closest('.modal-backdrop');
        if (backdrop) {
            backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    },
    
    closeModal(modalId) {
        const backdrop = document.getElementById(modalId)?.closest('.modal-backdrop');
        if (backdrop) {
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }
    },
    
    // ===== FORM HANDLING =====
    initForms() {
        // Form validation
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
        
        // Real-time validation
        document.querySelectorAll('.form-control[required]').forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
        });
        
        // Character counters
        document.querySelectorAll('.form-control[data-max-length]').forEach(input => {
            const maxLength = input.getAttribute('data-max-length');
            const counter = document.createElement('div');
            counter.className = 'form-help text-right';
            counter.textContent = `0 / ${maxLength}`;
            input.parentNode.appendChild(counter);
            
            input.addEventListener('input', () => {
                const length = input.value.length;
                counter.textContent = `${length} / ${maxLength}`;
                counter.style.color = length > maxLength ? 'var(--error-color)' : '';
            });
        });
        
        // Autosave functionality
        document.querySelectorAll('form[data-autosave]').forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    this.autoSaveForm(form);
                });
            });
        });
    },
    
    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('.form-control[required]');
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    validateField(input) {
        let isValid = true;
        let errorMessage = '';
        
        // Remove previous error
        const existingError = input.parentNode.querySelector('.form-error');
        if (existingError) existingError.remove();
        input.classList.remove('error', 'success');
        
        // Check required
        if (input.hasAttribute('required') && !input.value.trim()) {
            isValid = false;
            errorMessage = 'This field is required';
        }
        
        // Check email
        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        }
        
        // Check min length
        if (input.hasAttribute('minlength') && input.value.length < input.getAttribute('minlength')) {
            isValid = false;
            errorMessage = `Minimum ${input.getAttribute('minlength')} characters required`;
        }
        
        // Check password match
        if (input.id === 'confirm_password') {
            const password = document.getElementById('password');
            if (password && input.value !== password.value) {
                isValid = false;
                errorMessage = 'Passwords do not match';
            }
        }
        
        // Display error or success
        if (!isValid) {
            input.classList.add('error');
            const error = document.createElement('div');
            error.className = 'form-error';
            error.textContent = errorMessage;
            input.parentNode.appendChild(error);
        } else if (input.value) {
            input.classList.add('success');
        }
        
        return isValid;
    },
    
    autoSaveForm(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        // Save to localStorage
        const formId = form.id || 'autosave-form';
        localStorage.setItem(`autosave-${formId}`, JSON.stringify(data));
        
        // Show save indicator
        this.showToast('Draft saved', 'success', 2000);
    },
    
    loadAutoSavedForm(formId) {
        const saved = localStorage.getItem(`autosave-${formId}`);
        if (saved) {
            const data = JSON.parse(saved);
            const form = document.getElementById(formId);
            
            Object.keys(data).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) input.value = data[key];
            });
            
            return true;
        }
        return false;
    },
    
    clearAutoSave(formId) {
        localStorage.removeItem(`autosave-${formId}`);
    },
    
    // ===== TOAST NOTIFICATIONS =====
    initToasts() {
        // Create toast container if it doesn't exist
        if (!document.querySelector('.toast-container')) {
            const container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
    },
    
    showToast(message, type = 'info', duration = 5000) {
        const container = document.querySelector('.toast-container');
        const toast = document.createElement('div');
        toast.className = `toast alert alert-${type}`;
        toast.innerHTML = `
            <span>${message}</span>
            <button class="modal-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        container.appendChild(toast);
        
        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 300ms ease-in-out';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
    },
    
    // ===== BACK TO TOP =====
    initBackToTop() {
        const backToTop = document.querySelector('.back-to-top');
        if (!backToTop) return;
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
        
        backToTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    },
    
    // ===== ACCESSIBILITY =====
    initAccessibility() {
        // Keyboard navigation for dropdowns
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            toggle?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    menu?.classList.toggle('show');
                }
            });
        });
        
        // Focus trap for modals
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    const focusableElements = modal.querySelectorAll(
                        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                    );
                    const firstElement = focusableElements[0];
                    const lastElement = focusableElements[focusableElements.length - 1];
                    
                    if (e.shiftKey && document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    } else if (!e.shiftKey && document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            });
        });
    },
    
    // ===== AJAX UTILITIES =====
    async request(url, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(url, options);
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Request failed:', error);
            this.showToast('An error occurred. Please try again.', 'error');
            return { success: false, error: error.message };
        }
    },
    
    // ===== UTILITY FUNCTIONS =====
    formatDate(date, format = 'YYYY-MM-DD') {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        
        return format
            .replace('YYYY', year)
            .replace('MM', month)
            .replace('DD', day);
    },
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    generateId() {
        return `id-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    }
};

// ===== ANIMATION UTILITIES =====
const AnimationUtils = {
    fadeIn(element, duration = 300) {
        element.style.opacity = 0;
        element.style.display = 'block';
        
        let start = null;
        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const progress = timestamp - start;
            element.style.opacity = Math.min(progress / duration, 1);
            
            if (progress < duration) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    },
    
    fadeOut(element, duration = 300) {
        let start = null;
        const initialOpacity = element.style.opacity || 1;
        
        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const progress = timestamp - start;
            element.style.opacity = initialOpacity - (progress / duration);
            
            if (progress < duration) {
                requestAnimationFrame(animate);
            } else {
                element.style.display = 'none';
            }
        };
        
        requestAnimationFrame(animate);
    },
    
    slideDown(element, duration = 300) {
        element.style.height = '0';
        element.style.overflow = 'hidden';
        element.style.display = 'block';
        
        const targetHeight = element.scrollHeight;
        let start = null;
        
        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const progress = timestamp - start;
            element.style.height = Math.min((progress / duration) * targetHeight, targetHeight) + 'px';
            
            if (progress < duration) {
                requestAnimationFrame(animate);
            } else {
                element.style.height = 'auto';
            }
        };
        
        requestAnimationFrame(animate);
    },
    
    slideUp(element, duration = 300) {
        const initialHeight = element.offsetHeight;
        element.style.height = initialHeight + 'px';
        element.style.overflow = 'hidden';
        
        let start = null;
        
        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const progress = timestamp - start;
            element.style.height = initialHeight - ((progress / duration) * initialHeight) + 'px';
            
            if (progress < duration) {
                requestAnimationFrame(animate);
            } else {
                element.style.display = 'none';
            }
        };
        
        requestAnimationFrame(animate);
    }
};

// ===== INITIALIZE ON DOM READY =====
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => OUTSINC.init());
} else {
    OUTSINC.init();
}

// Export for use in other scripts
window.OUTSINC = OUTSINC;
window.AnimationUtils = AnimationUtils;
