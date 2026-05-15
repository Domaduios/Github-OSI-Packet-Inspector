<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$activeTab = 'info';

$serverAddr = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
$clientAddr = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$userAgent  = htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Info — Student Management System</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .flow { display: flex; flex-direction: column; align-items: center; gap: 0; }
        .flow-step {
            display: flex; align-items: center; gap: 12px;
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 12px; padding: 13px 20px;
            min-width: 320px; transition: border-color .2s;
        }
        .flow-step:hover { border-color: var(--border2); }
        .flow-step.hl { border-color: rgba(0,212,170,.4); background: rgba(0,212,170,.05); }
        .flow-step-icon { font-size: 20px; }
        .flow-step-title { font-size: 13px; font-weight: 600; color: var(--text); }
        .flow-step-sub { font-size: 11px; color: var(--muted); margin-top: 2px; font-family: var(--mono); }

        .flow-arrow { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 4px 0; }
        .flow-arrow-line { width: 1px; height: 14px; background: var(--border2); }
        .flow-arrow-label {
            font-size: 10px; font-family: var(--mono); color: var(--muted);
            background: var(--surface2); border: 1px solid var(--border);
            padding: 2px 8px; border-radius: 4px;
        }

        .info-list { display: flex; flex-direction: column; gap: 12px; }
        .info-row { display: flex; align-items: flex-start; gap: 14px; }
        .info-row-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--accent); margin-top: 7px; flex-shrink: 0;
            box-shadow: 0 0 5px rgba(0,212,170,.4);
        }
        .info-row-content { font-size: 13px; color: var(--muted2); line-height: 1.7; }
        .info-row-content strong { color: var(--text); font-weight: 600; }

        .sec-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .sec-item {
            display: flex; align-items: center; gap: 10px;
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 10px; padding: 12px 14px;
            font-size: 12px; color: var(--muted2); transition: border-color .2s;
        }
        .sec-item:hover { border-color: var(--border2); }
        .sec-item .sec-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--accent); flex-shrink: 0; }

        @media (max-width: 540px) {
            .sec-grid { grid-template-columns: 1fr; }
            .flow-step { min-width: 0; width: 100%; }
        }
    </style>
</head>
<body>

<?php include '_navbar.php'; ?>

<main class="page" style="max-width:820px;">

    <div class="page-header fade-in">
        <div class="page-title-group">
            <div>
                <div class="page-title">◊ Network Architecture</div>
                <div class="page-sub">How this system communicates over the network</div>
            </div>
        </div>
    </div>

    <!-- Flow -->
    <div class="card fade-in">
        <div class="card-header">
            <span>🔄</span>
            <h3>Request / Response Flow</h3>
            <span class="card-tag">CLIENT → SERVER</span>
        </div>
        <div class="flow">
            <div class="flow-step">
                <div class="flow-step-icon">💻</div>
                <div>
                    <div class="flow-step-title">Browser (Client)</div>
                    <div class="flow-step-sub"><?php echo htmlspecialchars($clientAddr); ?></div>
                </div>
            </div>
            <div class="flow-arrow"><div class="flow-arrow-line"></div><div class="flow-arrow-label">↓ HTTP Request — Port 80/443</div><div class="flow-arrow-line"></div></div>
            <div class="flow-step hl">
                <div class="flow-step-icon">🖥️</div>
                <div>
                    <div class="flow-step-title">Apache + PHP Web Server</div>
                    <div class="flow-step-sub"><?php echo htmlspecialchars($serverAddr); ?></div>
                </div>
            </div>
            <div class="flow-arrow"><div class="flow-arrow-line"></div><div class="flow-arrow-label">↓ SQL Query</div><div class="flow-arrow-line"></div></div>
            <div class="flow-step">
                <div class="flow-step-icon">🗄️</div>
                <div>
                    <div class="flow-step-title">MySQL Database</div>
                    <div class="flow-step-sub">localhost:3306</div>
                </div>
            </div>
            <div class="flow-arrow"><div class="flow-arrow-line"></div><div class="flow-arrow-label">↓ JSON Response</div><div class="flow-arrow-line"></div></div>
            <div class="flow-step">
                <div class="flow-step-icon">🖥️</div>
                <div>
                    <div class="flow-step-title">Browser</div>
                    <div class="flow-step-sub">Renders UI</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Components -->
    <div class="card fade-in">
        <div class="card-header">
            <span>📡</span>
            <h3>Network Components</h3>
        </div>
        <div class="info-list">
            <div class="info-row"><span class="info-row-dot"></span>
                <div class="info-row-content"><strong>Client-Server Model:</strong> Browser communicates bidirectionally with the Web Server.</div>
            </div>
            <div class="info-row"><span class="info-row-dot"></span>
                <div class="info-row-content"><strong>Protocol:</strong> HTTP/HTTPS over <span class="chip-mono">Port 80/443</span></div>
            </div>
            <div class="info-row"><span class="info-row-dot"></span>
                <div class="info-row-content"><strong>Server IP:</strong> <span class="chip-mono"><?php echo htmlspecialchars($serverAddr); ?></span></div>
            </div>
            <div class="info-row"><span class="info-row-dot"></span>
                <div class="info-row-content"><strong>Client IP:</strong> <span class="chip-mono"><?php echo htmlspecialchars($clientAddr); ?></span></div>
            </div>
            <div class="info-row"><span class="info-row-dot"></span>
                <div class="info-row-content"><strong>User Agent:</strong> <span class="chip-mono" style="word-break:break-all;"><?php echo $userAgent; ?></span></div>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="card fade-in">
        <div class="card-header">
            <span>🔗</span>
            <h3>How Network Enables Each Feature</h3>
        </div>
        <div class="info-list">
            <div class="info-row"><span class="info-row-dot"></span>
                <div class="info-row-content"><strong>View Students:</strong> GET request → api.php → Database → JSON response → Rendered table</div>
            </div>
            <div class="info-row"><span class="info-row-dot"></span>
                <div class="info-row-content"><strong>Add Grade:</strong> POST request → api.php → INSERT query → Success response</div>
            </div>
            <div class="info-row"><span class="info-row-dot"></span>
                <div class="info-row-content"><strong>Login:</strong> POST username + password → authenticate.php → Session token created</div>
            </div>
            <div class="info-row"><span class="info-row-dot"></span>
                <div class="info-row-content"><strong>Concurrent Users:</strong> Multiple clients access the same server simultaneously from different IPs.</div>
            </div>
        </div>
    </div>

    <!-- Security -->
    <div class="card fade-in">
        <div class="card-header">
            <span>🛡️</span>
            <h3>Security Measures</h3>
        </div>
        <div class="sec-grid">
            <div class="sec-item"><span class="sec-dot"></span>Session-based authentication</div>
            <div class="sec-item"><span class="sec-dot"></span>Role-based access control</div>
            <div class="sec-item"><span class="sec-dot"></span>SQL injection prevention</div>
            <div class="sec-item"><span class="sec-dot"></span>JSON API with input validation</div>
        </div>
    </div>

</main>

</body>
</html>
