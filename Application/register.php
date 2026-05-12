<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — OSI Inspector</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
        }
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #0a0a0a 0%, #1e1b4b 100%);
        }

        .auth-shell {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 920px;
            width: 100%;
            background: var(--bg-elevated);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,.15);
            overflow: hidden;
            border: 1px solid var(--border);
        }
        @media (max-width: 800px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-banner { display: none; }
        }

        .auth-banner {
            background: linear-gradient(135deg, #10b981, #3b82f6);
            padding: 50px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .auth-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .banner-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            position: relative;
        }
        .banner-logo-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,.15);
            border-radius: 11px;
            display: grid;
            place-items: center;
            font-size: 22px;
        }
        .banner-logo-text { font-size: 16px; font-weight: 700; }
        .banner-logo-sub { font-size: 11px; opacity: .8; font-family: var(--mono); }

        .banner-content { position: relative; }
        .banner-title { font-size: 28px; font-weight: 800; line-height: 1.2; margin-bottom: 14px; letter-spacing: -.5px; }
        .banner-desc { font-size: 14px; opacity: .9; line-height: 1.6; margin-bottom: 24px; }

        .banner-features {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .banner-features li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            opacity: .95;
        }
        .banner-features .check {
            width: 20px; height: 20px;
            background: rgba(255,255,255,.2);
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 10px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .banner-footer { font-size: 11px; font-family: var(--mono); opacity: .7; position: relative; }

        .auth-form-wrap {
            padding: 40px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-title { font-size: 22px; font-weight: 700; letter-spacing: -.3px; margin-bottom: 6px; }
        .auth-subtitle { font-size: 13px; color: var(--text-muted); margin-bottom: 20px; }

        .auth-form { display: flex; flex-direction: column; gap: 12px; }
        .auth-form .input {
            height: 40px;
            font-size: 14px;
            font-family: var(--sans);
            padding: 0 14px;
        }
        .auth-form .btn {
            height: 44px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 6px;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0 14px;
            font-size: 11px;
            color: var(--text-muted);
            font-family: var(--mono);
            letter-spacing: 1px;
            font-weight: 600;
        }
        .auth-divider::before, .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .auth-link-row { text-align: center; font-size: 13px; color: var(--text-muted); }
        .auth-link-row a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-left: 4px;
        }
        .auth-link-row a:hover { text-decoration: underline; }

        .alert {
            padding: 10px 14px;
            border-radius: var(--radius);
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }
        .alert-error { background: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger); }

        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .theme-toggle-corner {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            color: var(--text-muted);
            width: 36px; height: 36px;
            border-radius: var(--radius);
            cursor: pointer;
            display: grid;
            place-items: center;
            box-shadow: var(--shadow);
            font-size: 16px;
            z-index: 100;
        }
    </style>
</head>
<body>

<button class="theme-toggle-corner" onclick="toggleTheme()">
    <span id="themeIcon">🌙</span>
</button>

<div class="auth-shell">

    <div class="auth-banner">
        <div class="banner-logo">
            <div class="banner-logo-icon">◍</div>
            <div>
                <div class="banner-logo-text">OSI Inspector</div>
                <div class="banner-logo-sub">v<?php echo APP_VERSION; ?> · pro</div>
            </div>
        </div>

        <div class="banner-content">
            <div class="banner-title">Join the toolkit.</div>
            <div class="banner-desc">Create a free account to start exploring networking concepts hands-on.</div>

            <ul class="banner-features">
                <li><span class="check">✓</span> Save your packet captures</li>
                <li><span class="check">✓</span> Track your quiz progress</li>
                <li><span class="check">✓</span> Personal dashboard</li>
                <li><span class="check">✓</span> Free forever</li>
            </ul>
        </div>

        <div class="banner-footer">© 2025 · Computer Networks Project</div>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-title">Create your account</div>
        <div class="auth-subtitle">Get started with OSI Inspector in seconds</div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <span>⊗</span> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="auth.php" class="auth-form">
            <input type="hidden" name="action" value="register">

            <div class="field">
                <label class="field-label">Full Name</label>
                <input type="text" name="fullname" class="input" placeholder="Mohamed Ahmed">
            </div>

            <div class="row-2">
                <div class="field">
                    <label class="field-label">Username *</label>
                    <input type="text" name="username" class="input" required placeholder="mohamed">
                </div>
                <div class="field">
                    <label class="field-label">Email *</label>
                    <input type="email" name="email" class="input" required placeholder="me@example.com">
                </div>
            </div>

            <div class="row-2">
                <div class="field">
                    <label class="field-label">Password *</label>
                    <input type="password" name="password" class="input" required placeholder="At least 4 chars">
                </div>
                <div class="field">
                    <label class="field-label">Confirm *</label>
                    <input type="password" name="confirm" class="input" required placeholder="Repeat password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create Account →</button>
        </form>

        <div class="auth-divider">OR</div>

        <div class="auth-link-row">
            Already have an account?<a href="login.php">Sign in</a>
        </div>
    </div>

</div>

<script>
function toggleTheme() {
    const html = document.documentElement;
    const cur = html.getAttribute('data-theme');
    const nxt = cur === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', nxt);
    localStorage.setItem('osi-theme', nxt);
    document.getElementById('themeIcon').textContent = nxt === 'dark' ? '☀️' : '🌙';
}
(function() {
    const saved = localStorage.getItem('osi-theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    document.getElementById('themeIcon').textContent = saved === 'dark' ? '☀️' : '🌙';
})();
</script>

</body>
</html>
