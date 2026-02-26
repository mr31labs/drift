<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Drift — A stream-based note-taking app that thinks with you.">
    <title>Drift — Let your thoughts flow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/drift.css">
</head>

<body>
    <!-- ═══════════════════════════════════════ -->
    <!-- AUTH SCREEN                             -->
    <!-- ═══════════════════════════════════════ -->
    <div id="auth-screen" class="screen">
        <div class="auth-container">
            <div class="auth-brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="24" cy="24" r="20" stroke="url(#brand-grad)" stroke-width="2.5" opacity="0.3" />
                        <path d="M14 24C14 18.477 18.477 14 24 14C29.523 14 34 18.477 34 24" stroke="url(#brand-grad)"
                            stroke-width="2.5" stroke-linecap="round" />
                        <path d="M18 24C18 20.686 20.686 18 24 18C27.314 18 30 20.686 30 24C30 27.314 27.314 30 24 30"
                            stroke="url(#brand-grad)" stroke-width="2.5" stroke-linecap="round" />
                        <circle cx="24" cy="24" r="3" fill="url(#brand-grad)" />
                        <defs>
                            <linearGradient id="brand-grad" x1="8" y1="8" x2="40" y2="40"
                                gradientUnits="userSpaceOnUse">
                                <stop stop-color="#a78bfa" />
                                <stop offset="1" stop-color="#60a5fa" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <h1 class="brand-name">Drift</h1>
                <p class="brand-tagline">Let your thoughts flow</p>
            </div>

            <!-- Login Form -->
            <form id="login-form" class="auth-form active" autocomplete="on">
                <h2 class="auth-title">Welcome back</h2>
                <div class="form-group">
                    <label for="login-username">Username</label>
                    <input type="text" id="login-username" name="username" autocomplete="username" required
                        minlength="3" maxlength="30" placeholder="Enter your username">
                </div>
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <div class="password-field">
                        <input type="password" id="login-password" name="password" autocomplete="current-password"
                            required minlength="8" placeholder="Enter your password">
                        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" style="display:none">
                                <path
                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                <line x1="1" y1="1" x2="23" y2="23" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="login-error" class="form-error" role="alert"></div>
                <button type="submit" class="btn btn-primary btn-full" id="login-btn">
                    <span class="btn-text">Sign In</span>
                    <span class="btn-loader" style="display:none"></span>
                </button>
                <p class="auth-switch">Don't have an account? <a href="#" id="show-register">Create one</a></p>
            </form>

            <!-- Register Form -->
            <form id="register-form" class="auth-form" autocomplete="on">
                <h2 class="auth-title">Create your space</h2>
                <div class="form-group">
                    <label for="reg-username">Username</label>
                    <input type="text" id="reg-username" name="username" autocomplete="username" required minlength="3"
                        maxlength="30" placeholder="Choose a username" pattern="[a-zA-Z0-9_]+">
                    <span class="form-hint">3-30 characters, letters, numbers & underscores</span>
                </div>
                <div class="form-group">
                    <label for="reg-display">Display Name</label>
                    <input type="text" id="reg-display" name="display_name" placeholder="How should we call you?"
                        maxlength="50">
                </div>
                <div class="form-group">
                    <label for="reg-password">Password</label>
                    <div class="password-field">
                        <input type="password" id="reg-password" name="password" autocomplete="new-password" required
                            minlength="8" maxlength="128" placeholder="Create a strong password">
                        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" style="display:none">
                                <path
                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                <line x1="1" y1="1" x2="23" y2="23" />
                            </svg>
                        </button>
                    </div>
                    <div class="password-strength" id="password-strength">
                        <div class="strength-bars">
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                        </div>
                        <span class="strength-label"></span>
                    </div>
                </div>
                <div id="register-error" class="form-error" role="alert"></div>
                <button type="submit" class="btn btn-primary btn-full" id="register-btn">
                    <span class="btn-text">Create Account</span>
                    <span class="btn-loader" style="display:none"></span>
                </button>
                <p class="auth-switch">Already have an account? <a href="#" id="show-login">Sign in</a></p>
            </form>
        </div>
        <div class="auth-decorative">
            <div class="floating-orb orb-1"></div>
            <div class="floating-orb orb-2"></div>
            <div class="floating-orb orb-3"></div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- MAIN APP                               -->
    <!-- ═══════════════════════════════════════ -->
    <div id="app-screen" class="screen" style="display:none">
        <!-- Top Bar -->
        <header class="topbar">
            <div class="topbar-left">
                <div class="topbar-brand">
                    <svg class="topbar-logo" viewBox="0 0 48 48" fill="none">
                        <circle cx="24" cy="24" r="20" stroke="url(#tb-grad)" stroke-width="2.5" opacity="0.3" />
                        <path d="M14 24C14 18.477 18.477 14 24 14C29.523 14 34 18.477 34 24" stroke="url(#tb-grad)"
                            stroke-width="2.5" stroke-linecap="round" />
                        <path d="M18 24C18 20.686 20.686 18 24 18C27.314 18 30 20.686 30 24C30 27.314 27.314 30 24 30"
                            stroke="url(#tb-grad)" stroke-width="2.5" stroke-linecap="round" />
                        <circle cx="24" cy="24" r="3" fill="url(#tb-grad)" />
                        <defs>
                            <linearGradient id="tb-grad" x1="8" y1="8" x2="40" y2="40">
                                <stop stop-color="#a78bfa" />
                                <stop offset="1" stop-color="#60a5fa" />
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="topbar-title">Drift</span>
                </div>
            </div>
            <div class="topbar-center">
                <div class="search-box" id="search-box">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input type="text" id="search-input" placeholder="Search thoughts... (Ctrl+K)" autocomplete="off">
                    <kbd class="search-shortcut">⌘K</kbd>
                </div>
            </div>
            <div class="topbar-right">
                <button class="topbar-btn" id="digest-btn" title="Daily Digest (Ctrl+D)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                        <line x1="10" y1="9" x2="8" y2="9" />
                    </svg>
                </button>
                <button class="topbar-btn" id="chat-btn" title="AI Chat (Ctrl+J)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                    </svg>
                </button>
                <button class="topbar-btn" id="filter-btn" title="Filter by mood">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                    </svg>
                </button>
                <button class="topbar-btn" id="settings-btn" title="Settings">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3" />
                        <path
                            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                    </svg>
                </button>
                <div class="topbar-user" id="user-menu-btn">
                    <div class="user-avatar" id="user-avatar"></div>
                </div>
            </div>
        </header>

        <!-- Filter Bar (hidden by default) -->
        <div class="filter-bar" id="filter-bar" style="display:none">
            <span class="filter-label">Filter:</span>
            <button class="mood-filter-btn active" data-mood="">All</button>
            <button class="mood-filter-btn" data-mood="urgent"><span class="mood-dot mood-urgent"></span>Urgent</button>
            <button class="mood-filter-btn" data-mood="idea"><span class="mood-dot mood-idea"></span>Idea</button>
            <button class="mood-filter-btn" data-mood="task"><span class="mood-dot mood-task"></span>Task</button>
            <button class="mood-filter-btn" data-mood="calm"><span class="mood-dot mood-calm"></span>Calm</button>
            <button class="filter-close" id="filter-close">&times;</button>
        </div>

        <!-- User Menu Dropdown -->
        <div class="user-menu" id="user-menu" style="display:none">
            <div class="user-menu-info">
                <div class="user-menu-avatar" id="user-menu-avatar"></div>
                <div class="user-menu-details">
                    <span class="user-menu-name" id="user-menu-name"></span>
                    <span class="user-menu-username" id="user-menu-username"></span>
                </div>
            </div>
            <hr class="menu-divider">
            <button class="menu-item" id="menu-settings">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3" />
                    <path
                        d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
                </svg>
                Settings
            </button>
            <button class="menu-item" id="menu-shortcuts">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2" />
                    <line x1="6" y1="8" x2="6" y2="8.01" />
                    <line x1="10" y1="8" x2="10" y2="8.01" />
                    <line x1="14" y1="8" x2="14" y2="8.01" />
                    <line x1="18" y1="8" x2="18" y2="8.01" />
                    <line x1="6" y1="12" x2="18" y2="12" />
                    <line x1="8" y1="16" x2="16" y2="16" />
                </svg>
                Keyboard Shortcuts
            </button>
            <hr class="menu-divider">
            <button class="menu-item menu-item-danger" id="menu-logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                </svg>
                Sign Out
            </button>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Capture Bar -->
            <div class="capture-area">
                <div class="capture-container">
                    <div class="capture-input-wrap">
                        <textarea id="capture-input" class="capture-input" placeholder="What's on your mind? (Ctrl+N)"
                            rows="1"></textarea>
                        <div class="capture-actions">
                            <div class="mood-picker" id="capture-mood-picker">
                                <button type="button" class="mood-btn active" data-mood="" title="No mood">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                    </svg>
                                </button>
                                <button type="button" class="mood-btn" data-mood="urgent" title="Urgent">
                                    <span class="mood-dot mood-urgent"></span>
                                </button>
                                <button type="button" class="mood-btn" data-mood="idea" title="Idea">
                                    <span class="mood-dot mood-idea"></span>
                                </button>
                                <button type="button" class="mood-btn" data-mood="task" title="Task">
                                    <span class="mood-dot mood-task"></span>
                                </button>
                                <button type="button" class="mood-btn" data-mood="calm" title="Calm">
                                    <span class="mood-dot mood-calm"></span>
                                </button>
                            </div>
                            <div class="capture-right-actions">
                                <label class="smart-capture-toggle" title="Smart Capture: AI polishes your text">
                                    <input type="checkbox" id="smart-capture-checkbox">
                                    <span class="smart-capture-slider"></span>
                                    <span class="smart-capture-label">✨ AI</span>
                                </label>
                                <button type="button" class="capture-send" id="capture-send" title="Send (Ctrl+Enter)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="22" y1="2" x2="11" y2="13" />
                                        <polygon points="22 2 15 22 11 13 2 9 22 2" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Note Stream -->
            <div class="stream" id="note-stream">
                <!-- Notes render here -->
            </div>

            <!-- Empty State -->
            <div class="empty-state" id="empty-state" style="display:none">
                <div class="empty-icon">
                    <svg viewBox="0 0 80 80" fill="none">
                        <circle cx="40" cy="40" r="35" stroke="currentColor" stroke-width="1.5" opacity="0.2" />
                        <path d="M25 40C25 31.716 31.716 25 40 25C48.284 25 55 31.716 55 40" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" opacity="0.3" />
                        <circle cx="40" cy="40" r="5" fill="currentColor" opacity="0.15" />
                    </svg>
                </div>
                <h3>Your stream is empty</h3>
                <p>Start typing above to capture your first thought.</p>
                <p class="empty-hint">Press <kbd>Ctrl</kbd>+<kbd>N</kbd> to focus the input anytime.</p>
            </div>

            <!-- Loading State -->
            <div class="loading-state" id="loading-state" style="display:none">
                <div class="loader"></div>
                <span>Loading your thoughts...</span>
            </div>
        </main>

        <!-- Settings Panel (Slide-out) -->
        <div class="settings-overlay" id="settings-overlay" style="display:none">
            <div class="settings-panel">
                <div class="settings-header">
                    <h2>Settings</h2>
                    <button class="settings-close" id="settings-close">&times;</button>
                </div>
                <div class="settings-body">
                    <section class="settings-section">
                        <h3>AI Integration</h3>
                        <p class="settings-desc">Connect an AI provider to unlock smart features on your notes.</p>
                        <div class="form-group">
                            <label for="ai-provider">Provider</label>
                            <div class="select-wrap">
                                <select id="ai-provider">
                                    <option value="openai">OpenAI (GPT-4o mini)</option>
                                    <option value="gemini">Google Gemini (2.0 Flash)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="ai-key">API Key</label>
                            <div class="password-field">
                                <input type="password" id="ai-key" placeholder="Paste your API key here">
                                <button type="button" class="toggle-password" aria-label="Toggle visibility">
                                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" style="display:none">
                                        <path
                                            d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                        <line x1="1" y1="1" x2="23" y2="23" />
                                    </svg>
                                </button>
                            </div>
                            <span class="form-hint" id="ai-key-status"></span>
                        </div>
                        <div class="settings-actions">
                            <button class="btn btn-primary" id="save-ai-settings">Save AI Settings</button>
                            <button class="btn btn-ghost" id="clear-ai-key" style="display:none">Remove Key</button>
                        </div>
                    </section>

                    <section class="settings-section">
                        <h3>About Drift</h3>
                        <p class="settings-desc">A stream-based note-taking app that thinks with you. v1.0</p>
                        <p class="settings-desc" style="opacity:0.5">Built with 💜</p>
                    </section>
                </div>
            </div>
        </div>

        <!-- Shortcuts Modal -->
        <div class="modal-overlay" id="shortcuts-modal" style="display:none">
            <div class="modal">
                <div class="modal-header">
                    <h2>Keyboard Shortcuts</h2>
                    <button class="modal-close" id="shortcuts-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="shortcut-grid">
                        <div class="shortcut-item"><kbd>Ctrl</kbd>+<kbd>N</kbd><span>New note</span></div>
                        <div class="shortcut-item"><kbd>Ctrl</kbd>+<kbd>K</kbd><span>Search</span></div>
                        <div class="shortcut-item"><kbd>Ctrl</kbd>+<kbd>Enter</kbd><span>Send note</span></div>
                        <div class="shortcut-item"><kbd>Ctrl</kbd>+<kbd>D</kbd><span>Daily Digest</span></div>
                        <div class="shortcut-item"><kbd>Ctrl</kbd>+<kbd>J</kbd><span>AI Chat</span></div>
                        <div class="shortcut-item"><kbd>Ctrl</kbd>+<kbd>,</kbd><span>Settings</span></div>
                        <div class="shortcut-item"><kbd>Esc</kbd><span>Close panel/menu</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Panel (appears on note) -->
        <div class="ai-panel" id="ai-panel" style="display:none">
            <div class="ai-panel-header">
                <span class="ai-panel-title">✨ AI Assistant</span>
                <button class="ai-panel-close" id="ai-panel-close">&times;</button>
            </div>
            <div class="ai-actions">
                <button class="ai-action-btn" data-action="summarize">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                    Summarize
                </button>
                <button class="ai-action-btn" data-action="expand">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 3 21 3 21 9" />
                        <polyline points="9 21 3 21 3 15" />
                        <line x1="21" y1="3" x2="14" y2="10" />
                        <line x1="3" y1="21" x2="10" y2="14" />
                    </svg>
                    Expand
                </button>
                <button class="ai-action-btn" data-action="rewrite">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                    Rewrite
                </button>
                <button class="ai-action-btn" data-action="connect">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="18" cy="5" r="3" />
                        <circle cx="6" cy="12" r="3" />
                        <circle cx="18" cy="19" r="3" />
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                    </svg>
                    Connect Ideas
                </button>
                <button class="ai-action-btn" data-action="ask">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                    Ask a Question
                </button>
                <button class="ai-action-btn" data-action="extract_actions">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 11l3 3L22 4" />
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                    </svg>
                    Extract Actions
                </button>
                <button class="ai-action-btn" data-action="coach">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                    </svg>
                    Writing Coach
                </button>
                <button class="ai-action-btn ai-action-btn-wide" data-action="extract_all">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <line x1="8" y1="12" x2="16" y2="12" />
                        <line x1="12" y1="8" x2="12" y2="16" />
                    </svg>
                    Extract Actions from ALL Notes
                </button>
            </div>
            <div class="ai-question-wrap" id="ai-question-wrap" style="display:none">
                <input type="text" id="ai-question-input" placeholder="Ask anything about this note...">
                <button class="btn btn-primary btn-sm" id="ai-question-send">Ask</button>
            </div>
            <div class="ai-result" id="ai-result" style="display:none">
                <div class="ai-result-content" id="ai-result-content"></div>
                <div class="ai-result-actions">
                    <button class="btn btn-ghost btn-sm" id="ai-result-copy">Copy</button>
                    <button class="btn btn-ghost btn-sm" id="ai-result-new-note">Save as Note</button>
                </div>
            </div>
            <div class="ai-loading" id="ai-loading" style="display:none">
                <div class="loader loader-sm"></div>
                <span>Thinking...</span>
            </div>
        </div>

        <!-- AI Chat Sidebar -->
        <div class="chat-overlay" id="chat-overlay" style="display:none">
            <div class="chat-sidebar">
                <div class="chat-header">
                    <div class="chat-header-left">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18"
                            height="18">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        </svg>
                        <h3>AI Chat</h3>
                    </div>
                    <button class="chat-close" id="chat-close">&times;</button>
                </div>
                <div class="chat-hint">Ask anything about your notes. I can search, analyze, and connect your thoughts.
                </div>
                <div class="chat-messages" id="chat-messages"></div>
                <div class="chat-input-area">
                    <textarea id="chat-input" class="chat-input" placeholder="Ask about your notes..."
                        rows="1"></textarea>
                    <button class="chat-send" id="chat-send">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13" />
                            <polygon points="22 2 15 22 11 13 2 9 22 2" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Digest Modal -->
        <div class="modal-overlay" id="digest-modal" style="display:none">
            <div class="modal modal-lg">
                <div class="modal-header">
                    <h2>📋 Daily Digest</h2>
                    <button class="modal-close" id="digest-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="digest-loading" id="digest-loading">
                        <div class="loader"></div>
                        <span>Analyzing your notes...</span>
                    </div>
                    <div class="digest-content" id="digest-content" style="display:none"></div>
                    <div class="digest-actions" id="digest-actions" style="display:none">
                        <button class="btn btn-primary btn-sm" id="digest-save">Save as Note</button>
                        <button class="btn btn-ghost btn-sm" id="digest-copy">Copy</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Smart Search Results -->
        <div class="smart-search-results" id="smart-search-results" style="display:none">
            <div class="smart-search-header">
                <span>🔍 AI found these related notes:</span>
                <button class="smart-search-close" id="smart-search-close">&times;</button>
            </div>
            <div class="smart-search-list" id="smart-search-list"></div>
        </div>
    </div>

    <!-- Note Template (used by JS) -->
    <template id="note-template">
        <article class="note-card">
            <div class="note-mood-indicator"></div>
            <div class="note-content"></div>
            <div class="note-meta">
                <time class="note-time"></time>
                <span class="note-pin-badge" style="display:none">📌 Pinned</span>
            </div>
            <div class="note-actions">
                <button class="note-action-btn note-ai-btn" title="AI Assistant">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z" />
                        <path d="M2 17l10 5 10-5" />
                        <path d="M2 12l10 5 10-5" />
                    </svg>
                </button>
                <button class="note-action-btn note-mood-change" title="Change mood">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M8 14s1.5 2 4 2 4-2 4-2" />
                        <line x1="9" y1="9" x2="9.01" y2="9" />
                        <line x1="15" y1="9" x2="15.01" y2="9" />
                    </svg>
                </button>
                <button class="note-action-btn note-pin-btn" title="Pin note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 4.5l-4 4L7 10l-1.5 1.5 7 7L14 17l1.5-4 4-4" />
                        <line x1="9" y1="15" x2="4.5" y2="19.5" />
                        <line x1="14.5" y1="4" x2="20" y2="9.5" />
                    </svg>
                </button>
                <button class="note-action-btn note-edit-btn" title="Edit note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                </button>
                <button class="note-action-btn note-delete-btn" title="Delete note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    </svg>
                </button>
            </div>
        </article>
    </template>

    <script src="assets/js/markdown.js"></script>
    <script src="assets/js/auth.js"></script>
    <script src="assets/js/notes.js"></script>
    <script src="assets/js/ai.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/app.js"></script>
</body>

</html>