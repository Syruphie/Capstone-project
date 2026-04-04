document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;

    const menuBindings = [];

    document.querySelectorAll('[data-menu-toggle]').forEach(function(toggle) {
        const panelId = toggle.getAttribute('data-menu-target');
        const panel = panelId ? document.getElementById(panelId) : null;
        const activeClass = toggle.getAttribute('data-menu-active-class') || 'is-open';
        const bodyClass = toggle.getAttribute('data-menu-body-class') || '';

        if (!panel) {
            return;
        }

        function setOpen(isOpen) {
            toggle.classList.toggle(activeClass, isOpen);
            panel.classList.toggle(activeClass, isOpen);
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

            if (bodyClass) {
                body.classList.toggle(bodyClass, isOpen);
                document.documentElement.classList.toggle(bodyClass, isOpen);
            }

            body.classList.toggle('menu-overlay-open', isOpen);
            document.documentElement.classList.toggle('menu-overlay-open', isOpen);
        }

        toggle.addEventListener('click', function() {
            setOpen(!panel.classList.contains(activeClass));
        });

        panel.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                setOpen(false);
            });
        });

        menuBindings.push(setOpen);
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            menuBindings.forEach(function(setOpen) {
                setOpen(false);
            });
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            menuBindings.forEach(function(setOpen) {
                setOpen(false);
            });
        }
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc3545';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    });

    // Password confirmation validation
    const confirmPassword = document.getElementById('confirm_password');
    const password = document.getElementById('password');

    if (confirmPassword && password) {
        confirmPassword.addEventListener('input', function() {
            if (this.value !== password.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });

    // Dashboard card animations
    const dashboardCards = document.querySelectorAll('.dashboard-card');
    dashboardCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s, transform 0.5s';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });

    console.log('GlobenTech Laboratory Order Management System - Initialized');
});

// Utility functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transition = 'opacity 0.5s';
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 3000);
}

function confirmAction(message) {
    return confirm(message);
}

function formatDate(date) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(date).toLocaleDateString('en-US', options);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// ── Input validation helpers ─────────────────────────────────────────────────

const OFFENSIVE_WORDS = [
    'fuck','fucking','fucker','shit','bitch','bastard','cunt','dick',
    'pussy','nigger','nigga','faggot','fag','retard','whore','slut',
    'piss','cock','asshole','motherfucker','wanker','twat','prick','arse','arsehole'
];

function containsOffensiveContent(text) {
    const lower = text.toLowerCase();
    return OFFENSIVE_WORDS.some(function(word) {
        return new RegExp('\\b' + word + '\\b').test(lower);
    });
}

// Phone fields — digits only, max 15
document.querySelectorAll('input[name="phone"]').forEach(function(input) {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 15);
    });
});

// Register form — hate-word + email @ check on submit
(function() {
    var registerBtn = document.querySelector('button[name="register"]');
    if (!registerBtn) return;
    var form = registerBtn.closest('form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        var textFields = ['full_name', 'company_name', 'address'];
        for (var i = 0; i < textFields.length; i++) {
            var el = form.elements[textFields[i]];
            if (el && el.value && containsOffensiveContent(el.value)) {
                e.preventDefault();
                alert('Offensive or inappropriate language is not allowed in the ' +
                      textFields[i].replace('_', ' ') + ' field.');
                el.focus();
                return;
            }
        }
        var emailEl = form.elements['email'];
        if (emailEl && emailEl.value && !emailEl.value.includes('@')) {
            e.preventDefault();
            alert('Please enter a valid email address containing @');
            emailEl.focus();
        }
    });
})();

// ===================== DARK / LIGHT MODE TOGGLE =====================
(function () {
    /**
     * Apply the given theme to <html data-theme="...">
     * and sync the toggle checkbox if it's on the page.
     */
    function applyTheme(dark) {
        document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
        var toggle = document.getElementById('themeToggle');
        if (toggle) toggle.checked = dark;
    }

    // Called once on every page to sync checkbox state with stored preference
    document.addEventListener('DOMContentLoaded', function () {
        var isDark = false;
        try {
            isDark = localStorage.getItem('theme') === 'dark';
        } catch (e) {
            isDark = false;
        }
        applyTheme(isDark);

        var toggle = document.getElementById('themeToggle');
        if (!toggle) return;

        toggle.addEventListener('change', function () {
            var dark = toggle.checked;
            try {
                localStorage.setItem('theme', dark ? 'dark' : 'light');
            } catch (e) {
                // Theme still applies for current page even if persistence fails.
            }
            applyTheme(dark);
        });
    });
})();
