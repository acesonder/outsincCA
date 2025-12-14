/**
 * OUTSINC - Authentication JavaScript
 * Handles login, registration, and password recovery
 */

// Login Form Handler
document.getElementById('login-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const messageEl = document.getElementById('login-message');
    
    try {
        const response = await fetch('/api/auth/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            messageEl.innerHTML = '<div class="alert alert-success">Login successful! Redirecting...</div>';
            setTimeout(() => {
                window.location.href = '/public/dashboard.php';
            }, 1000);
        } else {
            messageEl.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
        }
    } catch (error) {
        messageEl.innerHTML = '<div class="alert alert-error">An error occurred. Please try again.</div>';
    }
});

// Registration Form Handler
document.getElementById('register-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const messageEl = document.getElementById('register-message');
    
    // Validate password match
    if (data.password !== data.confirm_password) {
        messageEl.innerHTML = '<div class="alert alert-error">Passwords do not match.</div>';
        return;
    }
    
    try {
        const response = await fetch('/api/auth/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            messageEl.innerHTML = `
                <div class="alert alert-success">
                    <strong>Registration successful!</strong><br>
                    Your username is: <strong>${result.user_id}</strong><br>
                    Please save this username. Redirecting to login...
                </div>
            `;
            setTimeout(() => {
                OUTSINC.closeModal('register-modal');
                OUTSINC.showModal('login-modal');
                document.getElementById('login_username').value = result.user_id;
            }, 3000);
        } else {
            messageEl.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
        }
    } catch (error) {
        messageEl.innerHTML = '<div class="alert alert-error">An error occurred. Please try again.</div>';
    }
});

// Forgot Password - Step 1
document.getElementById('forgot-password-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const messageEl = document.getElementById('forgot-message');
    
    try {
        const response = await fetch('/api/auth/get-security-question.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show step 2
            document.getElementById('reset-step-1').style.display = 'none';
            document.getElementById('reset-step-2').style.display = 'block';
            
            document.getElementById('recovered-username').textContent = result.username;
            document.getElementById('reset_user_id').value = result.user_id;
            document.getElementById('security-question-label').textContent = result.security_question;
        } else {
            messageEl.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
        }
    } catch (error) {
        messageEl.innerHTML = '<div class="alert alert-error">An error occurred. Please try again.</div>';
    }
});

// Reset Password - Step 2
document.getElementById('reset-password-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const messageEl = document.getElementById('reset-message');
    
    // Validate password match
    if (data.new_password !== data.confirm_new_password) {
        messageEl.innerHTML = '<div class="alert alert-error">Passwords do not match.</div>';
        return;
    }
    
    try {
        const response = await fetch('/api/auth/reset-password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            messageEl.innerHTML = '<div class="alert alert-success">Password reset successful! Redirecting to login...</div>';
            setTimeout(() => {
                OUTSINC.closeModal('forgot-password-modal');
                OUTSINC.showModal('login-modal');
                // Reset form
                document.getElementById('reset-step-1').style.display = 'block';
                document.getElementById('reset-step-2').style.display = 'none';
                document.getElementById('forgot-password-form').reset();
                document.getElementById('reset-password-form').reset();
            }, 2000);
        } else {
            messageEl.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
        }
    } catch (error) {
        messageEl.innerHTML = '<div class="alert alert-error">An error occurred. Please try again.</div>';
    }
});
