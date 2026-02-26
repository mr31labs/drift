/**
 * Drift — Main Application Controller
 */

const DriftApp = {
    /**
     * Initialize the application
     */
    init() {
        DriftAuth.init();
        DriftNotes.init();
        DriftAI.init();
        DriftSettings.init();
        this.bindGlobalEvents();
    },

    /**
     * Show auth screen
     */
    showAuth() {
        document.getElementById('auth-screen').style.display = 'flex';
        document.getElementById('app-screen').style.display = 'none';
    },

    /**
     * Show main app
     */
    showApp(user) {
        document.getElementById('auth-screen').style.display = 'none';
        document.getElementById('app-screen').style.display = 'block';

        // Set user info
        const initial = (user.display_name || user.username || '?')[0].toUpperCase();
        document.getElementById('user-avatar').textContent = initial;
        document.getElementById('user-menu-avatar').textContent = initial;
        document.getElementById('user-menu-name').textContent = user.display_name || user.username;
        document.getElementById('user-menu-username').textContent = '@' + user.username;

        // Load notes
        DriftNotes.loadNotes();
    },

    /**
     * Bind global events
     */
    bindGlobalEvents() {
        // User menu toggle
        document.getElementById('user-menu-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            const menu = document.getElementById('user-menu');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        });

        // Close user menu on outside click
        document.addEventListener('click', (e) => {
            const menu = document.getElementById('user-menu');
            const btn = document.getElementById('user-menu-btn');
            if (!menu.contains(e.target) && !btn.contains(e.target)) {
                menu.style.display = 'none';
            }
        });

        // Logout
        document.getElementById('menu-logout').addEventListener('click', () => {
            this.closeUserMenu();
            DriftAuth.logout();
        });

        // Shortcuts modal
        document.getElementById('menu-shortcuts').addEventListener('click', () => {
            this.closeUserMenu();
            document.getElementById('shortcuts-modal').style.display = 'flex';
        });
        document.getElementById('shortcuts-close').addEventListener('click', () => {
            document.getElementById('shortcuts-modal').style.display = 'none';
        });
        document.getElementById('shortcuts-modal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('shortcuts-modal')) {
                document.getElementById('shortcuts-modal').style.display = 'none';
            }
        });

        // Global keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Only when app is visible
            if (document.getElementById('app-screen').style.display === 'none') return;

            const isInput = e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT';

            // Ctrl+N — Focus capture input
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                document.getElementById('capture-input').focus();
            }

            // Ctrl+K — Focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('search-input').focus();
            }

            // Ctrl+, — Open settings
            if ((e.ctrlKey || e.metaKey) && e.key === ',') {
                e.preventDefault();
                DriftSettings.open();
            }

            // Ctrl+D — Daily Digest
            if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                e.preventDefault();
                DriftAI.openDigest();
            }

            // Ctrl+J — AI Chat
            if ((e.ctrlKey || e.metaKey) && e.key === 'j') {
                e.preventDefault();
                DriftAI.openChat();
            }

            // Escape — Close panels/modals
            if (e.key === 'Escape') {
                if (document.getElementById('digest-modal').style.display !== 'none') {
                    document.getElementById('digest-modal').style.display = 'none';
                } else if (document.getElementById('chat-overlay').style.display !== 'none') {
                    DriftAI.closeChat();
                } else if (document.getElementById('shortcuts-modal').style.display !== 'none') {
                    document.getElementById('shortcuts-modal').style.display = 'none';
                } else if (document.getElementById('settings-overlay').style.display !== 'none') {
                    DriftSettings.close();
                } else if (document.getElementById('ai-panel').style.display !== 'none') {
                    DriftAI.closePanel();
                } else if (document.getElementById('user-menu').style.display !== 'none') {
                    document.getElementById('user-menu').style.display = 'none';
                } else if (isInput) {
                    e.target.blur();
                }
            }
        });
    },

    /**
     * Close user menu
     */
    closeUserMenu() {
        document.getElementById('user-menu').style.display = 'none';
    },

    /**
     * Show toast notification
     */
    toast(message, type = 'info') {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
};

// Boot the app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    DriftApp.init();
});
