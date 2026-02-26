/**
 * Drift — Settings Module
 */

const DriftSettings = {
    /**
     * Initialize settings module
     */
    init() {
        this.bindEvents();
    },

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Open settings
        document.getElementById('settings-btn').addEventListener('click', () => this.open());
        document.getElementById('menu-settings').addEventListener('click', () => {
            DriftApp.closeUserMenu();
            this.open();
        });

        // Close settings
        document.getElementById('settings-close').addEventListener('click', () => this.close());
        document.getElementById('settings-overlay').addEventListener('click', (e) => {
            if (e.target === document.getElementById('settings-overlay')) {
                this.close();
            }
        });

        // Save AI settings
        document.getElementById('save-ai-settings').addEventListener('click', () => this.saveAISettings());

        // Clear API key
        document.getElementById('clear-ai-key').addEventListener('click', () => this.clearAPIKey());
    },

    /**
     * Open settings panel
     */
    async open() {
        document.getElementById('settings-overlay').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        await this.loadSettings();
    },

    /**
     * Close settings panel
     */
    close() {
        document.getElementById('settings-overlay').style.display = 'none';
        document.body.style.overflow = '';
    },

    /**
     * Load settings from server
     */
    async loadSettings() {
        try {
            const res = await fetch('api/settings.php');
            const data = await res.json();

            if (data.settings) {
                const s = data.settings;
                document.getElementById('ai-provider').value = s.ai_provider || 'openai';

                const keyStatus = document.getElementById('ai-key-status');
                const clearBtn = document.getElementById('clear-ai-key');

                if (s.has_api_key) {
                    keyStatus.textContent = `Current key: ${s.ai_api_key_masked}`;
                    keyStatus.style.color = 'var(--mood-calm)';
                    clearBtn.style.display = 'inline-flex';
                    document.getElementById('ai-key').placeholder = 'Enter new key to replace...';
                } else {
                    keyStatus.textContent = 'No API key configured';
                    keyStatus.style.color = 'var(--text-tertiary)';
                    clearBtn.style.display = 'none';
                    document.getElementById('ai-key').placeholder = 'Paste your API key here';
                }
            }
        } catch (err) {
            DriftApp.toast('Failed to load settings', 'error');
        }
    },

    /**
     * Save AI settings
     */
    async saveAISettings() {
        const provider = document.getElementById('ai-provider').value;
        const apiKey = document.getElementById('ai-key').value.trim();

        const body = { ai_provider: provider };
        if (apiKey) {
            body.ai_api_key = apiKey;
        }

        try {
            const res = await fetch('api/settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });

            const data = await res.json();

            if (data.success) {
                document.getElementById('ai-key').value = '';
                await this.loadSettings();
                DriftApp.toast('Settings saved ✓', 'success');
            } else {
                DriftApp.toast('Failed to save settings', 'error');
            }
        } catch (err) {
            DriftApp.toast('Connection error', 'error');
        }
    },

    /**
     * Clear the stored API key
     */
    async clearAPIKey() {
        if (!confirm('Remove your API key? AI features will stop working.')) return;

        try {
            const res = await fetch('api/settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ clear_api_key: true })
            });

            const data = await res.json();

            if (data.success) {
                await this.loadSettings();
                DriftApp.toast('API key removed', 'success');
            }
        } catch (err) {
            DriftApp.toast('Connection error', 'error');
        }
    }
};
