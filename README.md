# OSI Packet Inspector v2.1 — Pro Edition

A professional networking toolkit for visualizing the OSI model, with full user authentication.

## ✨ What's New in v2.1
- 🔐 **User authentication** (login + register)
- 👤 **User accounts** with sessions
- 🛡️ **Protected pages** — must sign in to access tools
- 🚪 **Secure logout**

## 📁 Project Files (20 total)

```
osi-inspector/
│
├── 🔐 AUTH (5 files)
│   ├── login.php              ← sign in page
│   ├── register.php           ← create account
│   ├── auth.php               ← login/register handler
│   ├── auth_check.php         ← guards protected pages
│   └── logout.php             ← sign out
│
├── 🛠️ TOOLS (3 files)
│   ├── index.php              ← Packet Inspector (home)
│   ├── history.php            ← Capture History
│   └── inspector.php          ← Packet Anatomy
│
├── 🌐 UTILITIES (4 files)
│   ├── subnet.php             ← Subnet Calculator
│   ├── ping.php               ← Ping & Traceroute
│   ├── portscan.php           ← Port Scanner
│   └── compare.php            ← TCP vs UDP
│
├── 📚 LEARN (2 files)
│   ├── learn.php              ← OSI Model reference
│   └── quiz.php               ← Knowledge Quiz
│
├── ⚙️ BACKEND (3 files)
│   ├── api.php                ← AJAX endpoints (protected)
│   ├── config.php             ← DB connection + helpers
│   └── _sidebar.php           ← shared navigation
│
├── 🎨 STYLE
│   └── theme.css              ← design system
│
└── 🗄️ DATABASE
    ├── database.sql           ← main schema (tools data)
    └── auth_migration.sql     ← Users table
```

## 🚀 Setup Instructions

### Step 1 — Install XAMPP
Download from https://www.apachefriends.org

### Step 2 — Place project files
Copy the entire `osi-inspector` folder to:
```
C:\xampp\htdocs\osi-inspector\
```

⚠️ All files must be in **one flat folder** — no subfolders.

### Step 3 — Start services
In **XAMPP Control Panel**, click Start:
- ✅ Apache
- ✅ MySQL

### Step 4 — Import the databases (in this order!)

In **phpMyAdmin** (`http://localhost/phpmyadmin`):

**4a. First import:**
1. Click **"Import"** tab
2. Choose: `database.sql`
3. Click **"Go"** ✅

**4b. Second import:**
1. Click on `osi_inspector` (left sidebar)
2. Click **"Import"** tab
3. Choose: `auth_migration.sql`
4. Click **"Go"** ✅

### Step 5 — Open the app
```
http://localhost/osi-inspector/
```

You'll be redirected to the login page automatically.

## 🔑 Demo Accounts

```
Username: admin       Password: admin123       Role: Admin
Username: demo        Password: demo123        Role: User
```

Or click **"Create one"** on the login page to register your own account.

## 📊 Database Tables (5 tables)

| Table | Purpose |
|---|---|
| `OSILayers` | Reference data for all 7 layers |
| `Protocols` | 18+ networking protocols cheat sheet |
| `Packets` | Captured packet log |
| `QuizQuestions` | 15 quiz questions |
| `Users` | User accounts (passwords hashed) |

## 🔐 Authentication Flow

```
┌─────────────┐
│  /index.php │  ← Protected page
└──────┬──────┘
       │ checks session
       ▼
   logged in?
   ┌───┴───┐
   YES     NO
    │       │
    ▼       ▼
  show   redirect
  page   to login
```

## 🎯 Features

### 🔬 Tools
- **Packet Inspector** — Build packets and animate through OSI layers
- **Capture History** — Wireshark-style log with filters & CSV export
- **Packet Anatomy** — Full header breakdown

### 🛠️ Network Utilities
- **Subnet Calculator** — VLSM/CIDR with binary visualization
- **Ping & Traceroute** — ICMP simulator with terminal output
- **Port Scanner** — 13 common ports with status
- **TCP vs UDP** — Side-by-side comparison + 3-way handshake

### 📚 Learning
- **OSI Reference** — All 7 layers explained
- **Knowledge Quiz** — 15 randomized questions

## 🐛 Troubleshooting

| Problem | Solution |
|---|---|
| Database Connection Failed | MySQL not running |
| Tables don't exist | Did you import BOTH SQL files? |
| Can't login with admin/admin123 | Make sure `auth_migration.sql` imported successfully |
| 404 Not Found | Folder must be `osi-inspector` exactly |
| Already-registered error | Username/email taken — try another |

## 🔒 Security Notes

✅ Passwords are hashed with `password_hash()` (bcrypt)
✅ SQL injection protection via prepared statements
✅ Session-based authentication
✅ CSRF protection via session checks
✅ All API endpoints require authentication

## 📐 Tech Stack

- **PHP 7.4+ / 8.x** — Backend
- **MySQL 5.7+ / MariaDB** — Database
- **Vanilla JavaScript** — Frontend (no frameworks)
- **Bcrypt** — Password hashing

---

**For your Computer Networks course presentation:**

This project demonstrates:
- 🌐 OSI Model (interactive)
- 🔄 TCP/UDP (with 3-way handshake)
- #️⃣ Subnetting & VLSM
- 🛣️ Routing (traceroute)
- 🏓 Diagnostics (ping)
- 🛡️ Security (port scanning)
- 🔐 Authentication (sessions, hashing)
- 📦 Encapsulation/Decapsulation
