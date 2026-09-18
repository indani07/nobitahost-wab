# NobitaHost — PHP Edition

A complete PHP conversion of [NobitaHost](https://github.com/nobita329/nobitahost-wab) — a glassy dark hosting panel.

## 🔑 Default Login

| Field    | Value              |
|----------|--------------------|
| Username | `admin`            |
| Password | `admin123`         |

> **Change your password immediately after first login!**

## 🚀 Quick Setup

### Requirements
- PHP 8.0+ with the following extensions: `pdo`, `pdo_sqlite`, `fileinfo`, `mbstring`
- Apache with `mod_rewrite` enabled **OR** PHP built-in server (for local dev)
- No Composer, no Node.js, no npm — pure PHP!

### Option A — Apache / XAMPP / Laragon

1. Copy the `nobitahost-php/` folder to your web server's document root
   - XAMPP: `C:\xampp\htdocs\nobitahost-php\`
   - Laragon: `C:\laragon\www\nobitahost-php\`
2. Open your browser: `http://localhost/nobitahost-php/`
3. The SQLite database is created automatically on first visit!

### Option B — PHP Built-in Server (Local Dev)

```bash
cd nobitahost-php
php -S localhost:8080
```
Then open: `http://localhost:8080/`

> **Note**: With the built-in server you don't need `.htaccess` — PHP handles routing natively via `index.php`.

### Option C — Any PHP Host (cPanel, etc.)

1. Upload all files via FTP to `public_html/` or a subdirectory
2. Make sure `data/` and `public/uploads/` are writable (chmod 755)
3. Visit your site URL

---

## 📁 Structure

```
nobitahost-php/
├── index.php           # Front controller / router
├── config.php          # App constants & session start
├── db.php              # PDO SQLite + schema + seed data
├── auth.php            # Session-based auth helpers
├── settings.php        # Settings read/write from DB
├── helpers.php         # Misc utility functions
├── mailer.php          # Email (PHP mail() or PHPMailer)
├── .htaccess           # Apache URL routing
│
├── pages/
│   ├── auth/           # Login, Register, Forgot, Reset, 2FA
│   ├── user/           # Home, Profile, Team, Blog, etc.
│   └── admin/          # Dashboard, Users, Settings, Content...
│
├── views/
│   ├── layout.php      # Main shell (sidebar + topbar)
│   ├── auth_layout.php # Auth pages shell
│   └── partials/       # sidebar, topbar, footer
│
├── public/
│   ├── css/style.css   # All styles (glassmorphism dark UI)
│   ├── js/app.js       # Client-side JS
│   ├── img/            # SVG assets
│   └── uploads/        # Uploaded files (auto-created)
│
└── data/
    └── nobitahost.db   # SQLite database (auto-created)
```

---

## ⚙️ Everything Editable in the Browser

Go to **Admin → Settings** to configure:

| Tab         | What you can change |
|-------------|---------------------|
| General     | Panel name, tagline, logo (emoji / URL / upload), favicon |
| Appearance  | Theme, accent color, blur, card radius, glass transparency |
| Background  | Image / video / URL background, overlay toggle |
| Music       | Background music URL, volume |
| Widgets     | Discord server widget, YouTube channel |
| Social      | Instagram handle, GitHub username & token |
| Mail        | SMTP host/port/user/pass, from address, enable/disable |
| Panel       | Toggle registration, maintenance mode, install script |

Go to **Admin → Content** to edit:
- About page
- Terms of Service
- Footer text
- Navbar brand
- Hub description

---

## 🔒 Security Notes

- The `data/` directory containing the SQLite DB is protected by `.htaccess`
- Always change the default admin password
- Set `display_errors = 0` in production
- Use HTTPS in production

---

## 📧 Email Setup (Optional)

1. Go to **Admin → Settings → Mail**
2. Enable email sending
3. Enter your SMTP credentials (Gmail, SendGrid, etc.)
4. For Gmail: use App Password, not your main password
5. For Gmail SMTP: host=`smtp.gmail.com`, port=`587`, secure=`TLS`

---

## 📋 Features

- ✅ Auth: Login, Register, Forgot/Reset Password, 2FA TOTP
- ✅ User: Home, Profile, Team, Tutorials, Projects, Links, Blog, Activity, About, Terms
- ✅ Admin: Dashboard, Users (CRUD), Settings (all tabs), Content Editor, Blog, Tutorials, Links, Projects, Roles, Activity Log
- ✅ Everything editable from the admin panel — no file editing needed
- ✅ SQLite database — no MySQL setup needed
- ✅ Glassmorphism dark UI with accent color, blur, radius controls
- ✅ File uploads for logo, favicon, background, profile pictures
- ✅ Discord & YouTube widgets
- ✅ Background music player
- ✅ Maintenance mode
- ✅ Responsive mobile layout
