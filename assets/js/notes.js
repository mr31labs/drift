/**
 * Drift — Notes Module
 * Handles CRUD operations, rendering, and interactions for notes
 */

const DriftNotes = {
    notes: [],
    currentMood: '',
    filterMood: '',
    searchQuery: '',
    editingId: null,

    /**
     * Initialize notes module
     */
    init() {
        this.bindEvents();
    },

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Capture input auto-resize
        const captureInput = document.getElementById('capture-input');
        captureInput.addEventListener('input', () => {
            captureInput.style.height = 'auto';
            captureInput.style.height = Math.min(captureInput.scrollHeight, 200) + 'px';
        });

        // Send note
        document.getElementById('capture-send').addEventListener('click', () => this.createNote());
        captureInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                this.createNote();
            }
        });

        // Mood picker for capture
        document.querySelectorAll('#capture-mood-picker .mood-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('#capture-mood-picker .mood-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.currentMood = btn.dataset.mood;
            });
        });

        // Search
        let searchTimer;
        document.getElementById('search-input').addEventListener('input', (e) => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                this.searchQuery = e.target.value.trim();
                this.loadNotes();
            }, 300);
        });

        // Filter bar
        document.getElementById('filter-btn').addEventListener('click', () => {
            const bar = document.getElementById('filter-bar');
            bar.style.display = bar.style.display === 'none' ? 'flex' : 'none';
        });
        document.getElementById('filter-close').addEventListener('click', () => {
            document.getElementById('filter-bar').style.display = 'none';
            this.filterMood = '';
            document.querySelectorAll('.mood-filter-btn').forEach(b => b.classList.remove('active'));
            document.querySelector('.mood-filter-btn[data-mood=""]').classList.add('active');
            this.loadNotes();
        });
        document.querySelectorAll('.mood-filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.mood-filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.filterMood = btn.dataset.mood;
                this.loadNotes();
            });
        });
    },

    /**
     * Load all notes from server
     */
    async loadNotes() {
        const stream = document.getElementById('note-stream');
        const empty = document.getElementById('empty-state');
        const loading = document.getElementById('loading-state');

        loading.style.display = 'flex';
        empty.style.display = 'none';
        stream.innerHTML = '';

        try {
            let url = 'api/notes.php';
            const params = new URLSearchParams();
            if (this.searchQuery) params.set('q', this.searchQuery);
            if (this.filterMood) params.set('mood', this.filterMood);
            const qs = params.toString();
            if (qs) url += '?' + qs;

            const res = await fetch(url);
            const data = await res.json();

            if (res.status === 401) {
                DriftAuth.logout();
                return;
            }

            this.notes = data.notes || [];
            this.renderNotes();

            // Smart Search fallback: if text search returned no results, try AI
            if (this.searchQuery && this.notes.length === 0) {
                DriftApp.toast('🔍 Trying AI-powered search...', 'info');
                DriftAI.smartSearch(this.searchQuery);
            } else {
                document.getElementById('smart-search-results').style.display = 'none';
            }
        } catch (err) {
            DriftApp.toast('Failed to load notes', 'error');
        } finally {
            loading.style.display = 'none';
        }
    },

    /**
     * Create a new note
     */
    async createNote() {
        const input = document.getElementById('capture-input');
        let content = input.value.trim();

        if (!content) {
            input.focus();
            return;
        }

        // Smart Capture: polish content with AI before saving
        if (DriftAI.isSmartCaptureOn()) {
            DriftApp.toast('✨ AI is polishing your note...', 'info');
            content = await DriftAI.polishContent(content);
        }

        try {
            const res = await fetch('api/notes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    content: content,
                    mood: this.currentMood
                })
            });

            const data = await res.json();

            if (data.note) {
                input.value = '';
                input.style.height = 'auto';
                this.currentMood = '';
                document.querySelectorAll('#capture-mood-picker .mood-btn').forEach(b => b.classList.remove('active'));
                document.querySelector('#capture-mood-picker .mood-btn[data-mood=""]').classList.add('active');

                // Add to beginning if no filter active
                if (!this.searchQuery && !this.filterMood) {
                    this.notes.unshift(data.note);
                    this.renderNotes();
                } else {
                    this.loadNotes();
                }

                DriftApp.toast('Thought captured ✨', 'success');

                // Auto-mood detection (async, non-blocking)
                if (!data.note.mood) {
                    DriftAI.autoDetectMood(data.note.id, data.note.content);
                }
            } else {
                DriftApp.toast(data.error || 'Failed to save note', 'error');
            }
        } catch (err) {
            DriftApp.toast('Connection error', 'error');
        }
    },

    /**
     * Update a note
     */
    async updateNote(id, updates) {
        try {
            const res = await fetch(`api/notes.php?id=${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updates)
            });

            const data = await res.json();

            if (data.note) {
                // Update in local array
                const idx = this.notes.findIndex(n => n.id === id);
                if (idx >= 0) {
                    this.notes[idx] = data.note;
                }
                this.renderNotes();
                return data.note;
            } else {
                DriftApp.toast(data.error || 'Update failed', 'error');
            }
        } catch (err) {
            DriftApp.toast('Connection error', 'error');
        }
        return null;
    },

    /**
     * Delete a note
     */
    async deleteNote(id) {
        try {
            const res = await fetch(`api/notes.php?id=${id}`, {
                method: 'DELETE'
            });

            const data = await res.json();

            if (data.success) {
                this.notes = this.notes.filter(n => n.id !== id);
                this.renderNotes();
                DriftApp.toast('Note removed', 'success');
            } else {
                DriftApp.toast(data.error || 'Delete failed', 'error');
            }
        } catch (err) {
            DriftApp.toast('Connection error', 'error');
        }
    },

    /**
     * Render all notes to the stream
     */
    renderNotes() {
        const stream = document.getElementById('note-stream');
        const empty = document.getElementById('empty-state');

        stream.innerHTML = '';

        if (this.notes.length === 0) {
            empty.style.display = 'block';
            return;
        }

        empty.style.display = 'none';

        // Sort: pinned first, then by date
        const sorted = [...this.notes].sort((a, b) => {
            if (a.pinned !== b.pinned) return b.pinned ? 1 : -1;
            return new Date(b.created_at) - new Date(a.created_at);
        });

        sorted.forEach(note => {
            const card = this.createNoteCard(note);
            stream.appendChild(card);
        });
    },

    /**
     * Create a note card DOM element
     */
    createNoteCard(note) {
        const template = document.getElementById('note-template');
        const clone = template.content.cloneNode(true);
        const card = clone.querySelector('.note-card');

        card.dataset.id = note.id;
        card.dataset.mood = note.mood || '';

        if (note.pinned) {
            card.classList.add('pinned');
        }

        // Content (render markdown)
        const contentEl = card.querySelector('.note-content');
        contentEl.innerHTML = DriftMarkdown.render(note.content);

        // Store raw content for editing
        card.dataset.rawContent = note.content;

        // Time
        const timeEl = card.querySelector('.note-time');
        timeEl.textContent = this.formatTime(note.created_at);
        timeEl.setAttribute('datetime', note.created_at);

        // Pin badge
        if (note.pinned) {
            card.querySelector('.note-pin-badge').style.display = 'inline';
        }

        // Pin button text
        const pinBtn = card.querySelector('.note-pin-btn');
        pinBtn.title = note.pinned ? 'Unpin note' : 'Pin note';

        // ─── Event Listeners ───

        // AI button
        card.querySelector('.note-ai-btn').addEventListener('click', () => {
            DriftAI.openPanel(note.id, note.content);
        });

        // Mood change
        card.querySelector('.note-mood-change').addEventListener('click', (e) => {
            this.showMoodDropdown(card, note.id, e);
        });

        // Pin toggle
        pinBtn.addEventListener('click', () => {
            this.updateNote(note.id, { pinned: !note.pinned });
        });

        // Edit
        card.querySelector('.note-edit-btn').addEventListener('click', () => {
            this.startEditing(card, note);
        });

        // Delete
        card.querySelector('.note-delete-btn').addEventListener('click', () => {
            if (confirm('Delete this note? This cannot be undone.')) {
                card.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => this.deleteNote(note.id), 250);
            }
        });

        return card;
    },

    /**
     * Show mood dropdown on a note card
     */
    showMoodDropdown(card, noteId, event) {
        // Remove any existing dropdown
        document.querySelectorAll('.note-mood-dropdown').forEach(d => d.remove());

        const dropdown = document.createElement('div');
        dropdown.className = 'note-mood-dropdown';

        const moods = [
            { mood: '', label: 'None', icon: '○' },
            { mood: 'urgent', label: 'Urgent', icon: '🔴' },
            { mood: 'idea', label: 'Idea', icon: '🟣' },
            { mood: 'task', label: 'Task', icon: '🟡' },
            { mood: 'calm', label: 'Calm', icon: '🟢' }
        ];

        moods.forEach(({ mood, label, icon }) => {
            const btn = document.createElement('button');
            btn.className = 'mood-btn';
            btn.title = label;
            if (mood) {
                btn.innerHTML = `<span class="mood-dot mood-${mood}"></span>`;
            } else {
                btn.textContent = icon;
                btn.style.fontSize = '12px';
            }
            btn.addEventListener('click', () => {
                this.updateNote(noteId, { mood });
                dropdown.remove();
            });
            dropdown.appendChild(btn);
        });

        card.appendChild(dropdown);

        // Close on outside click
        const close = (e) => {
            if (!dropdown.contains(e.target)) {
                dropdown.remove();
                document.removeEventListener('click', close);
            }
        };
        setTimeout(() => document.addEventListener('click', close), 10);
    },

    /**
     * Start inline editing a note
     */
    startEditing(card, note) {
        if (this.editingId) return; // only one edit at a time
        this.editingId = note.id;

        card.classList.add('editing');
        const contentEl = card.querySelector('.note-content');
        const originalHtml = contentEl.innerHTML;

        contentEl.innerHTML = '';

        const textarea = document.createElement('textarea');
        textarea.className = 'note-edit-textarea';
        textarea.value = card.dataset.rawContent || note.content;
        contentEl.appendChild(textarea);

        const actions = document.createElement('div');
        actions.className = 'note-edit-actions';

        const saveBtn = document.createElement('button');
        saveBtn.className = 'btn btn-primary btn-sm';
        saveBtn.textContent = 'Save';
        saveBtn.addEventListener('click', async () => {
            const newContent = textarea.value.trim();
            if (newContent) {
                const updated = await this.updateNote(note.id, { content: newContent });
                if (updated) {
                    this.editingId = null;
                }
            }
        });

        const cancelBtn = document.createElement('button');
        cancelBtn.className = 'btn btn-ghost btn-sm';
        cancelBtn.textContent = 'Cancel';
        cancelBtn.addEventListener('click', () => {
            contentEl.innerHTML = originalHtml;
            card.classList.remove('editing');
            this.editingId = null;
        });

        actions.appendChild(cancelBtn);
        actions.appendChild(saveBtn);
        contentEl.appendChild(actions);

        textarea.focus();
        textarea.setSelectionRange(textarea.value.length, textarea.value.length);

        // Ctrl+Enter to save
        textarea.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                saveBtn.click();
            }
            if (e.key === 'Escape') {
                cancelBtn.click();
            }
        });
    },

    /**
     * Format timestamp to relative time
     */
    formatTime(isoString) {
        const date = new Date(isoString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays < 7) return `${diffDays}d ago`;

        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
        });
    }
};
