<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — Student Management System</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .3;
            pointer-events: none;
            z-index: 0;
        }
        .orb-1 { width: 420px; height: 420px; background: var(--accent2); top: -150px; right: -150px; animation: float 18s ease-in-out infinite; }
        .orb-2 { width: 380px; height: 380px; background: var(--accent);  bottom: -120px; left: -120px; animation: float 22s ease-in-out infinite reverse; }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(-40px, 30px) scale(1.1); }
        }

        .auth-card {
            position: relative;
            z-index: 1;
            background: rgba(13,17,23,.85);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px 36px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 30px 80px rgba(0,0,0,.5);
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }

        .auth-brand-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            box-shadow: var(--shadow-glow);
        }

        .auth-brand-name { font-size: 16px; font-weight: 700; }
        .auth-brand-sub  { font-size: 11px; color: var(--muted); font-family: var(--mono); letter-spacing: .8px; text-transform: uppercase; }

        .auth-title {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -.5px;
            margin-bottom: 6px;
        }

        .auth-sub {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 24px;
        }

        .auth-form { display: flex; flex-direction: column; gap: 14px; }

        .auth-form .input,
        .auth-form .select { padding: 11px 14px; font-size: 13px; }

        .role-hint {
            font-size: 11px;
            color: var(--muted);
            font-family: var(--mono);
            margin-top: 4px;
        }

        .submit-btn {
            margin-top: 8px;
            padding: 13px;
            background: linear-gradient(135deg, var(--accent), #00b894);
            color: var(--bg);
            border: none;
            border-radius: 10px;
            font-family: var(--sans);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-btn:hover {
            box-shadow: 0 0 20px rgba(0,212,170,.4);
            transform: translateY(-1px);
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0 16px;
            font-size: 11px;
            color: var(--muted);
            font-family: var(--mono);
            letter-spacing: 1px;
        }

        .auth-divider::before, .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .auth-link-row {
            text-align: center;
            font-size: 13px;
            color: var(--muted);
        }

        .auth-link-row a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            margin-left: 4px;
        }

        .auth-link-row a:hover { text-decoration: underline; }

        .alert a {
            color: inherit;
            font-weight: 700;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="auth-card">

    <div class="auth-brand">
        <div class="auth-brand-icon">🎓</div>
        <div>
            <div class="auth-brand-name">Student Management System</div>
            <div class="auth-brand-sub">Network Edition</div>
        </div>
    </div>

    <h1 class="auth-title">Create your account</h1>
    <p class="auth-sub">Get started with the system in seconds</p>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <span>✓</span> Account created. <a href="login.php">Sign in →</a>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <span>⊗</span> <?php echo htmlspecialchars($_GET['message'] ?? 'Registration failed.'); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register_action.php" class="auth-form">
        <div class="field">
            <label>USERNAME *</label>
            <input type="text" name="username" class="input" required placeholder="At least 3 characters">
        </div>
        <div class="field">
            <label>PASSWORD *</label>
            <input type="password" name="password" class="input" required placeholder="At least 4 characters">
        </div>
        <div class="field">
            <label>EMAIL *</label>
            <input type="email" name="email" class="input" required placeholder="you@university.edu">
        </div>
        <div class="field">
            <label>ROLE *</label>
            <select name="role" class="select" required>
                <option value="">Select your role</option>
                <option value="Student">🎓 Student</option>
                <option value="Teacher">👨‍🏫 Teacher</option>
                <option value="Staff">👔 Staff</option>
            </select>
            <div class="role-hint">// Admin accounts are created by existing admins only</div>
        </div>
        <button type="submit" class="submit-btn">
            Create Account <span>→</span>
        </button>
    </form>

    <div class="auth-divider">OR</div>

    <div class="auth-link-row">
        Already have an account? <a href="login.php">Sign in →</a>
    </div>

</div>

</body>
</html>
