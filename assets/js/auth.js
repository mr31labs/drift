/**
 * Drift — Authentication Module
 */

const DriftAuth = {
    currentUser: null,

    /**
     * Initialize auth module
     */
    init() {
        this.bindEvents();
        this.checkSession();
    },

    /**
     * Bind form events
     */
    bindEvents() {
        // Toggle between login/register
        document.getElementById('show-register').addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('login-form').classList.remove('active');
            document.getElementById('register-form').classList.add('active');
        });

        document.getElementById('show-login').addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('register-form').classList.remove('active');
            document.getElementById('login-form').classList.add('active');
        });

        // Login form
        document.getElementById('login-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.login();
        });

        // Register form
        document.getElementById('register-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.register();
        });

        // Password visibility toggles
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', () => {
                const field = btn.closest('.password-field');
                const input = field.querySelector('input');
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                field.querySelector('.eye-open').style.display = isPassword ? 'none' : 'block';
                field.querySelector('.eye-closed').style.display = isPassword ? 'block' : 'none';
            });
        });

        // Password strength meter
        document.getElementById('reg-password').addEventListener('input', (e) => {
            this.updatePasswordStrength(e.target.value);
        });
    },

    /**
     * Check if session is active
     */
    async checkSession() {
        try {
            const res = await fetch('api/auth.php?action=check');
            const data = await res.json();

            if (data.authenticated) {
                this.currentUser = data.user;
                this.csrfToken = data.csrf_token;
                DriftApp.showApp(data.user);
            } else {
                DriftApp.showAuth();
            }
        } catch (err) {
            DriftApp.showAuth();
        }
    },

    /**
     * Login
     */
    async login() {
        const btn = document.getElementById('login-btn');
        const errorEl = document.getElementById('login-error');
        errorEl.textContent = '';

        this.setLoading(btn, true);

        const username = document.getElementById('login-username').value.trim();
        const password = document.getElementById('login-password').value;

        try {
            const res = await fetch('api/auth.php?action=login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });

            const data = await res.json();

            if (data.success) {
                this.currentUser = data.user;
                this.csrfToken = data.csrf_token;
                DriftApp.showApp(data.user);
            } else {
                errorEl.textContent = data.error || 'Login failed';
                this.shakeElement(errorEl);
            }
        } catch (err) {
            errorEl.textContent = 'Connection error. Please try again.';
        } finally {
            this.setLoading(btn, false);
        }
    },

    /**
     * Register
     */
    async register() {
        const btn = document.getElementById('register-btn');
        const errorEl = document.getElementById('register-error');
        errorEl.textContent = '';

        this.setLoading(btn, true);

        const username = document.getElementById('reg-username').value.trim();
        const password = document.getElementById('reg-password').value;
        const display_name = document.getElementById('reg-display').value.trim() || username;

        try {
            const res = await fetch('api/auth.php?action=register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password, display_name })
            });

            const data = await res.json();

            if (data.success) {
                this.currentUser = data.user;
                this.csrfToken = data.csrf_token;
                DriftApp.showApp(data.user);
            } else {
                errorEl.textContent = data.error || 'Registration failed';
                this.shakeElement(errorEl);
            }
        } catch (err) {
            errorEl.textContent = 'Connection error. Please try again.';
        } finally {
            this.setLoading(btn, false);
        }
    },

    /**
     * Logout
     */
    async logout() {
        try {
            await fetch('api/auth.php?action=logout', { method: 'POST' });
        } catch (e) { }
        this.currentUser = null;
        DriftApp.showAuth();
    },

    /**
     * Update password strength indicator
     */
    updatePasswordStrength(password) {
        const bars = document.querySelectorAll('#password-strength .strength-bar');
        const label = document.querySelector('#password-strength .strength-label');

        let score = 0;
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^a-zA-Z0-9]/.test(password)) score++;

        const levels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
        const classes = ['', 'active-weak', 'active-fair', 'active-good', 'active-strong'];

        bars.forEach((bar, i) => {
            bar.className = 'strength-bar';
            if (i < score) {
                bar.classList.add(classes[score]);
            }
        });

        label.textContent = password.length > 0 ? levels[score] : '';
    },

    /**
     * Toggle loading state on button
     */
    setLoading(btn, loading) {
        const text = btn.querySelector('.btn-text');
        const loader = btn.querySelector('.btn-loader');
        btn.disabled = loading;
        text.style.display = loading ? 'none' : 'inline';
        loader.style.display = loading ? 'inline-block' : 'none';
    },

    /**
     * Shake animation for errors
     */
    shakeElement(el) {
        el.style.animation = 'none';
        el.offsetHeight; // trigger reflow
        el.style.animation = 'shake 0.4s ease';
    }
};
