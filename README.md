# Drift

**A minimalist, AI-powered note-taking app.** Capture thoughts instantly, let AI organize and enhance them.

![Drift — Clean light UI](https://img.shields.io/badge/PHP-Backend-777BB4?logo=php&logoColor=white) ![JavaScript](https://img.shields.io/badge/Vanilla-JavaScript-F7DF1E?logo=javascript&logoColor=black) ![CSS](https://img.shields.io/badge/CSS-Design_System-1572B6?logo=css3&logoColor=white)

---

## ✨ Features

### Core
- **Instant Capture** — write a thought and press `Ctrl+Enter` to save
- **Mood Tagging** — color-code notes as Urgent 🔴, Idea 🟣, Task 🟡, or Calm 🟢
- **Markdown Rendering** — full support for headers, lists, code blocks, links, and more
- **Pin & Search** — pin important notes to the top, search across all content
- **Inline Editing** — click edit on any note to modify it in place
- **Filter by Mood** — quickly filter your stream by mood category

### AI-Powered (7 Features)
Requires an OpenAI or Google Gemini API key (configured in Settings).

| Feature | Description |
|---------|-------------|
| **Auto-Mood Detection** | AI automatically tags each note's mood based on content |
| **Smart Capture** | Toggle `✨ AI` ON → text is polished and improved before saving |
| **Daily Digest** | Summarizes all notes into themes, key ideas, and action items |
| **Action Extraction** | Pulls to-do items from a single note or ALL notes at once |
| **AI Chat** | Persistent sidebar chat — ask questions about your notes |
| **Writing Coach** | Get feedback on clarity, tone, and structure with a rewritten version |
| **Smart Search** | When text search finds nothing, AI finds semantically related notes |

### Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl+N` | Focus capture input |
| `Ctrl+K` | Focus search |
| `Ctrl+Enter` | Send note |
| `Ctrl+D` | Open Daily Digest |
| `Ctrl+J` | Open AI Chat |
| `Ctrl+,` | Open Settings |
| `Esc` | Close panel/modal |

---

## 🏗️ Architecture

```
drift/
├── index.php              # Single-page application (HTML + PHP session check)
├── .htaccess              # Apache URL rewriting & security headers
├── api/
│   ├── config.php         # Session management, JSON helpers, data paths
│   ├── auth.php           # Registration, login, logout (bcrypt hashing)
│   ├── notes.php          # CRUD for notes + search/filter + all_content endpoint
│   ├── settings.php       # User settings (display name, AI provider, API key)
│   └── ai.php             # AI proxy — 10 actions for OpenAI & Gemini
├── assets/
│   ├── css/drift.css      # Design system — light minimalist theme
│   └── js/
│       ├── app.js         # Boot, global events, keyboard shortcuts, toasts
│       ├── auth.js        # Auth form handling (login/register/logout)
│       ├── notes.js       # Note CRUD, rendering, inline editing, AI integration
│       ├── ai.js          # AI module — all 7 features + panel/chat/digest UI
│       ├── settings.js    # Settings panel management
│       └── markdown.js    # Lightweight Markdown → HTML renderer
└── data/                  # User data (gitignored)
    ├── users.json         # User accounts
    ├── rate_limits.json   # API rate limiting
    └── users/             # Per-user directories (notes.json, settings.json)
```

### Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8+ (no framework, no database) |
| **Frontend** | Vanilla HTML, CSS, JavaScript (no build step) |
| **Storage** | JSON files on disk |
| **Auth** | PHP sessions + bcrypt password hashing |
| **AI** | OpenAI GPT-4o-mini / Google Gemini 2.0 Flash |
| **API Keys** | AES-256-CBC encrypted, stored server-side |
| **Design** | Custom CSS design system with Inter font |

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.0+ with `openssl` and `json` extensions
- A web server (Apache with mod_rewrite, or PHP built-in server)

### Local Development

```bash
# Clone the repository
git clone git@github.com:mr31labs/drift.git
cd drift

# Start PHP development server
php -S localhost:8080

# Open in browser
open http://localhost:8080
```

### Production (Apache / Shared Hosting)

1. Upload all files to your web root
2. Ensure `data/` directory is writable by the web server
3. Ensure `.htaccess` is being processed (AllowOverride All)
4. Create an account at the registration screen

### AI Setup

1. Register and log in
2. Click your avatar → **Settings**
3. Select your AI provider (OpenAI or Gemini)
4. Paste your API key and click **Save Key**
5. AI features are now active across the app

---

## 🔒 Security

- Passwords hashed with `bcrypt` (cost factor 12)
- API keys encrypted with **AES-256-CBC** before storage
- Session-based authentication with CSRF protection via `SameSite` cookies
- Rate limiting on auth endpoints (5 attempts/minute)
- No database exposure — flat file storage in `data/` directory
- `.htaccess` blocks direct access to `data/` and `api/config.php`

---

## 🎨 Design

The UI follows a **minimalist, content-first** design philosophy:

- Clean light theme with white backgrounds and subtle gray borders
- **Inter** typeface for excellent readability
- Typography-driven hierarchy — no decorative elements
- Invisible chrome — action buttons appear only on hover
- Responsive layout — works on desktop and mobile
- Soft shadows and 120ms transitions for subtle polish

---

## 📄 License

MIT License — free to use, modify, and distribute.
