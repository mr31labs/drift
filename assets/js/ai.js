/**
 * Drift — AI Module (Enhanced)
 * 
 * Features:
 *  - Original: summarize, expand, rewrite, connect, ask
 *  - Auto-Mood Detection
 *  - Smart Capture (polish)
 *  - Daily Digest
 *  - Extract Action Items
 *  - AI Chat (contextual Q&A)
 *  - Writing Coach
 *  - Smart Search fallback
 */

const DriftAI = {
    activeNoteId: null,
    activeContent: '',
    chatHistory: [],
    allNotesCache: null,

    init() {
        this.bindEvents();
        this.bindChatEvents();
        this.bindDigestEvents();
        this.bindSmartSearchEvents();
    },

    /* ────────────────────────────────────────────────
       ORIGINAL AI PANEL EVENTS
       ──────────────────────────────────────────────── */
    bindEvents() {
        // Close panel
        document.getElementById('ai-panel-close').addEventListener('click', () => {
            this.closePanel();
        });

        // Action buttons
        document.querySelectorAll('.ai-action-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const action = btn.dataset.action;
                if (action === 'ask') {
                    document.getElementById('ai-question-wrap').style.display = 'flex';
                    document.getElementById('ai-question-input').focus();
                } else if (action === 'extract_all') {
                    this.extractAllActions();
                } else {
                    this.executeAction(action);
                }
            });
        });

        // Ask question
        document.getElementById('ai-question-send').addEventListener('click', () => {
            const question = document.getElementById('ai-question-input').value.trim();
            if (question) {
                this.executeAction('ask', question);
            }
        });
        document.getElementById('ai-question-input').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('ai-question-send').click();
            }
        });

        // Copy result
        document.getElementById('ai-result-copy').addEventListener('click', () => {
            const content = document.getElementById('ai-result-content').textContent;
            navigator.clipboard.writeText(content).then(() => {
                DriftApp.toast('Copied to clipboard', 'success');
            });
        });

        // Save as new note
        document.getElementById('ai-result-new-note').addEventListener('click', async () => {
            const content = document.getElementById('ai-result-content').textContent;
            try {
                const res = await fetch('api/notes.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ content, mood: 'idea' })
                });
                const data = await res.json();
                if (data.note) {
                    DriftNotes.loadNotes();
                    DriftApp.toast('Saved as new note ✨', 'success');
                    this.closePanel();
                }
            } catch (err) {
                DriftApp.toast('Failed to save note', 'error');
            }
        });
    },

    /* ────────────────────────────────────────────────
       AI PANEL — OPEN / CLOSE / EXECUTE
       ──────────────────────────────────────────────── */
    openPanel(noteId, content) {
        this.activeNoteId = noteId;
        this.activeContent = content;

        document.getElementById('ai-result').style.display = 'none';
        document.getElementById('ai-loading').style.display = 'none';
        document.getElementById('ai-question-wrap').style.display = 'none';
        document.getElementById('ai-question-input').value = '';

        document.getElementById('ai-panel').style.display = 'block';
    },

    closePanel() {
        document.getElementById('ai-panel').style.display = 'none';
        this.activeNoteId = null;
        this.activeContent = '';
    },

    async executeAction(action, question = '') {
        const resultEl = document.getElementById('ai-result');
        const resultContent = document.getElementById('ai-result-content');
        const loadingEl = document.getElementById('ai-loading');

        resultEl.style.display = 'none';
        loadingEl.style.display = 'flex';

        try {
            const body = { content: this.activeContent };
            if (question) body.question = question;

            const res = await fetch(`api/ai.php?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });

            const data = await res.json();

            if (data.result) {
                resultContent.innerHTML = DriftMarkdown.render(data.result);
                resultEl.style.display = 'block';
            } else {
                resultContent.innerHTML = `<p style="color: var(--mood-urgent)">${data.error || 'AI request failed'}</p>`;
                resultEl.style.display = 'block';
            }
        } catch (err) {
            resultContent.innerHTML = '<p style="color: var(--mood-urgent)">Connection error. Please try again.</p>';
            resultEl.style.display = 'block';
        } finally {
            loadingEl.style.display = 'none';
        }
    },

    /* ────────────────────────────────────────────────
       EXTRACT ALL ACTIONS (from all notes)
       ──────────────────────────────────────────────── */
    async extractAllActions() {
        const resultEl = document.getElementById('ai-result');
        const resultContent = document.getElementById('ai-result-content');
        const loadingEl = document.getElementById('ai-loading');

        resultEl.style.display = 'none';
        loadingEl.style.display = 'flex';

        try {
            const allNotes = await this.getAllNotesContext();
            const res = await fetch('api/ai.php?action=extract_actions', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content: '', all_notes: allNotes })
            });
            const data = await res.json();
            if (data.result) {
                resultContent.innerHTML = DriftMarkdown.render(data.result);
                resultEl.style.display = 'block';
            } else {
                resultContent.innerHTML = `<p style="color: var(--mood-urgent)">${data.error || 'Failed to extract actions'}</p>`;
                resultEl.style.display = 'block';
            }
        } catch (err) {
            resultContent.innerHTML = '<p style="color: var(--mood-urgent)">Connection error.</p>';
            resultEl.style.display = 'block';
        } finally {
            loadingEl.style.display = 'none';
        }
    },

    /* ────────────────────────────────────────────────
       AUTO-MOOD DETECTION
       ──────────────────────────────────────────────── */
    async autoDetectMood(noteId, content) {
        try {
            const res = await fetch('api/ai.php?action=auto_mood', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content })
            });
            const data = await res.json();
            if (data.mood && data.mood !== '') {
                // Update the note with detected mood
                await fetch(`api/notes.php?id=${noteId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mood: data.mood })
                });
                // Animate the mood indicator on the card
                const card = document.querySelector(`.note-card[data-id="${noteId}"]`);
                if (card) {
                    card.dataset.mood = data.mood;
                    card.classList.add('mood-detected');
                    setTimeout(() => card.classList.remove('mood-detected'), 800);
                }
                DriftApp.toast(`AI detected mood: ${data.mood}`, 'success');
            }
        } catch (err) {
            // Silent fail — auto-mood is non-critical
            console.log('Auto-mood detection failed:', err);
        }
    },

    /* ────────────────────────────────────────────────
       SMART CAPTURE (POLISH)
       ──────────────────────────────────────────────── */
    isSmartCaptureOn() {
        return document.getElementById('smart-capture-checkbox')?.checked || false;
    },

    async polishContent(content) {
        const container = document.querySelector('.capture-container');
        container?.classList.add('polishing');

        try {
            const res = await fetch('api/ai.php?action=polish', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content })
            });
            const data = await res.json();
            return data.result || content;
        } catch (err) {
            DriftApp.toast('AI polish failed, saving original', 'error');
            return content;
        } finally {
            container?.classList.remove('polishing');
        }
    },

    /* ────────────────────────────────────────────────
       DAILY DIGEST
       ──────────────────────────────────────────────── */
    bindDigestEvents() {
        document.getElementById('digest-btn').addEventListener('click', () => {
            this.openDigest();
        });

        document.getElementById('digest-close').addEventListener('click', () => {
            document.getElementById('digest-modal').style.display = 'none';
        });

        document.getElementById('digest-modal').addEventListener('click', (e) => {
            if (e.target.id === 'digest-modal') {
                document.getElementById('digest-modal').style.display = 'none';
            }
        });

        document.getElementById('digest-save').addEventListener('click', async () => {
            const content = document.getElementById('digest-content').textContent;
            try {
                const res = await fetch('api/notes.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ content: '📋 Daily Digest\n\n' + content, mood: 'idea' })
                });
                const data = await res.json();
                if (data.note) {
                    DriftNotes.loadNotes();
                    DriftApp.toast('Digest saved as note ✨', 'success');
                    document.getElementById('digest-modal').style.display = 'none';
                }
            } catch (err) {
                DriftApp.toast('Failed to save digest', 'error');
            }
        });

        document.getElementById('digest-copy').addEventListener('click', () => {
            const content = document.getElementById('digest-content').textContent;
            navigator.clipboard.writeText(content).then(() => {
                DriftApp.toast('Digest copied', 'success');
            });
        });
    },

    async openDigest() {
        const modal = document.getElementById('digest-modal');
        const loading = document.getElementById('digest-loading');
        const content = document.getElementById('digest-content');
        const actions = document.getElementById('digest-actions');

        modal.style.display = 'flex';
        loading.style.display = 'flex';
        content.style.display = 'none';
        actions.style.display = 'none';

        try {
            const allNotes = await this.getAllNotesContext();
            if (!allNotes) {
                content.innerHTML = '<p style="color: var(--text-tertiary)">No notes to digest yet. Start capturing some thoughts!</p>';
                content.style.display = 'block';
                loading.style.display = 'none';
                return;
            }

            const res = await fetch('api/ai.php?action=digest', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content: '', all_notes: allNotes })
            });
            const data = await res.json();

            if (data.result) {
                content.innerHTML = DriftMarkdown.render(data.result);
                content.style.display = 'block';
                actions.style.display = 'flex';
            } else {
                content.innerHTML = `<p style="color: var(--mood-urgent)">${data.error || 'Digest failed'}</p>`;
                content.style.display = 'block';
            }
        } catch (err) {
            content.innerHTML = '<p style="color: var(--mood-urgent)">Failed to generate digest. Check your API key.</p>';
            content.style.display = 'block';
        } finally {
            loading.style.display = 'none';
        }
    },

    /* ────────────────────────────────────────────────
       AI CHAT SIDEBAR
       ──────────────────────────────────────────────── */
    bindChatEvents() {
        document.getElementById('chat-btn').addEventListener('click', () => {
            this.openChat();
        });

        document.getElementById('chat-close').addEventListener('click', () => {
            this.closeChat();
        });

        document.getElementById('chat-overlay').addEventListener('click', (e) => {
            if (e.target.id === 'chat-overlay') this.closeChat();
        });

        document.getElementById('chat-send').addEventListener('click', () => {
            this.sendChatMessage();
        });

        document.getElementById('chat-input').addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendChatMessage();
            }
        });

        // Auto-resize chat input
        document.getElementById('chat-input').addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    },

    openChat() {
        document.getElementById('chat-overlay').style.display = 'flex';
        document.getElementById('chat-input').focus();
        // Invalidate notes cache when chat opens
        this.allNotesCache = null;
    },

    closeChat() {
        document.getElementById('chat-overlay').style.display = 'none';
    },

    async sendChatMessage() {
        const input = document.getElementById('chat-input');
        const question = input.value.trim();
        if (!question) return;

        const messagesEl = document.getElementById('chat-messages');

        // Add user message
        const userMsg = document.createElement('div');
        userMsg.className = 'chat-message user';
        userMsg.textContent = question;
        messagesEl.appendChild(userMsg);

        // Clear input
        input.value = '';
        input.style.height = 'auto';

        // Add typing indicator
        const typingEl = document.createElement('div');
        typingEl.className = 'chat-message typing';
        typingEl.innerHTML = '<div class="dot"></div><div class="dot"></div><div class="dot"></div>';
        messagesEl.appendChild(typingEl);
        messagesEl.scrollTop = messagesEl.scrollHeight;

        // Build conversation context from history
        const context = this.chatHistory.map(h => `${h.role}: ${h.text}`).join('\n');

        try {
            const allNotes = await this.getAllNotesContext();
            const res = await fetch('api/ai.php?action=chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    content: context,
                    question: question,
                    all_notes: allNotes
                })
            });

            const data = await res.json();

            // Remove typing indicator
            typingEl.remove();

            // Add AI response
            const aiMsg = document.createElement('div');
            aiMsg.className = 'chat-message ai';
            if (data.result) {
                aiMsg.innerHTML = DriftMarkdown.render(data.result);
                this.chatHistory.push({ role: 'user', text: question });
                this.chatHistory.push({ role: 'assistant', text: data.result });
                // Keep only last 10 exchanges for context
                if (this.chatHistory.length > 20) {
                    this.chatHistory = this.chatHistory.slice(-20);
                }
            } else {
                aiMsg.innerHTML = `<p style="color: var(--mood-urgent)">${data.error || 'Chat failed'}</p>`;
            }
            messagesEl.appendChild(aiMsg);
        } catch (err) {
            typingEl.remove();
            const errMsg = document.createElement('div');
            errMsg.className = 'chat-message ai';
            errMsg.innerHTML = '<p style="color: var(--mood-urgent)">Connection error. Please try again.</p>';
            messagesEl.appendChild(errMsg);
        }

        messagesEl.scrollTop = messagesEl.scrollHeight;
    },

    /* ────────────────────────────────────────────────
       SMART SEARCH
       ──────────────────────────────────────────────── */
    bindSmartSearchEvents() {
        document.getElementById('smart-search-close').addEventListener('click', () => {
            document.getElementById('smart-search-results').style.display = 'none';
        });
    },

    async smartSearch(query) {
        try {
            const allNotes = await this.getAllNotesContext(true);
            const res = await fetch('api/ai.php?action=smart_search', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content: '', question: query, all_notes: allNotes })
            });

            const data = await res.json();

            if (data.results && data.results.length > 0) {
                this.showSmartSearchResults(data.results);
                return true;
            }
            return false;
        } catch (err) {
            console.log('Smart search failed:', err);
            return false;
        }
    },

    showSmartSearchResults(results) {
        const container = document.getElementById('smart-search-results');
        const list = document.getElementById('smart-search-list');
        list.innerHTML = '';

        results.forEach(r => {
            // Find the note in the current notes list
            const note = DriftNotes.notes.find(n => n.id === r.id);
            if (!note) return;

            const item = document.createElement('div');
            item.className = 'smart-search-item';
            item.innerHTML = `
                <div class="smart-search-preview">${note.content.substring(0, 120)}</div>
                <div class="smart-search-reason">✨ ${r.reason}</div>
            `;
            item.addEventListener('click', () => {
                // Scroll to the note card
                const card = document.querySelector(`.note-card[data-id="${note.id}"]`);
                if (card) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    card.style.boxShadow = '0 0 0 2px var(--accent-solid), 0 0 20px var(--accent-glow)';
                    setTimeout(() => { card.style.boxShadow = ''; }, 2000);
                }
                container.style.display = 'none';
            });
            list.appendChild(item);
        });

        container.style.display = 'block';
    },

    /* ────────────────────────────────────────────────
       HELPERS
       ──────────────────────────────────────────────── */
    async getAllNotesContext(includeIds = false) {
        try {
            const res = await fetch('api/notes.php?action=all_content');
            const data = await res.json();
            if (!data.notes || data.notes.length === 0) return '';

            return data.notes.map(note => {
                const date = new Date(note.created_at).toLocaleString();
                const mood = note.mood ? ` [${note.mood}]` : '';
                const pin = note.pinned ? ' [pinned]' : '';
                const idPart = includeIds ? `Note ID: ${note.id} | ` : '';
                return `--- ${idPart}${date}${mood}${pin} ---\n${note.content}`;
            }).join('\n\n');
        } catch (err) {
            return '';
        }
    },

    /**
     * Check if AI is configured (has API key)
     */
    async hasApiKey() {
        try {
            const res = await fetch('api/settings.php');
            const data = await res.json();
            return data.settings && data.settings.ai_api_key && data.settings.ai_api_key !== '';
        } catch {
            return false;
        }
    }
};
