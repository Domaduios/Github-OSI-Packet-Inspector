<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';
$activeTab = 'network';

function getClientIP() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if ($ip === '::1') $ip = '127.0.0.1';
    return $ip;
}

$clientIP    = getClientIP();
$serverAddr  = htmlspecialchars($_SERVER['SERVER_ADDR']    ?? '127.0.0.1');
$serverPort  = htmlspecialchars($_SERVER['SERVER_PORT']    ?? '80');
$serverProto = htmlspecialchars($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1');

$totalStudents = 0; $uniqueIPs = 0; $subnetCount = 0;

$res = $conn->query("SELECT COUNT(*) AS cnt FROM Students");
if ($res) $totalStudents = (int) $res->fetch_assoc()['cnt'];

$res = $conn->query("SELECT COUNT(DISTINCT IPAddress) AS cnt FROM Students WHERE IPAddress IS NOT NULL AND IPAddress != ''");
if ($res) $uniqueIPs = (int) $res->fetch_assoc()['cnt'];

$res = $conn->query("SELECT SUBSTRING_INDEX(IPAddress, '.', 3) AS subnet FROM Students WHERE IPAddress IS NOT NULL AND IPAddress != '' GROUP BY subnet");
if ($res) $subnetCount = $res->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Map — Student Management System</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .session-bar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px; }
        .session-chip {
            display: flex; align-items: center; gap: 8px;
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 8px; padding: 8px 14px;
            font-size: 12px; font-family: var(--mono);
        }
        .session-chip .label { color: var(--muted); }
        .session-chip .value { color: var(--text); }
        .session-chip.you { border-color: rgba(0,212,170,.35); background: rgba(0,212,170,.06); }
        .session-chip.you .value { color: var(--accent); font-weight: 700; }

        .topology { display: flex; flex-direction: column; align-items: center; gap: 0; padding: 12px 0; }
        .topo-node {
            display: flex; align-items: center; gap: 12px;
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 12px; padding: 14px 20px;
            min-width: 280px; transition: border-color .2s;
        }
        .topo-node:hover { border-color: var(--border2); }
        .topo-node.accent { border-color: rgba(0,212,170,.4); background: rgba(0,212,170,.05); }
        .topo-node.blue   { border-color: rgba(59,130,246,.4); background: rgba(59,130,246,.05); }
        .topo-node-icon { font-size: 22px; }
        .topo-node-title { font-size: 13px; font-weight: 600; color: var(--text); }
        .topo-node-sub { font-size: 11px; color: var(--muted); margin-top: 2px; font-family: var(--mono); }

        .topo-connector { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 4px 0; }
        .topo-line { width: 1px; height: 18px; background: var(--border2); }
        .topo-connector-label {
            font-size: 10px; font-family: var(--mono); color: var(--muted);
            background: var(--surface2); border: 1px solid var(--border);
            padding: 2px 8px; border-radius: 4px;
        }

        .topo-branch { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; }
        .topo-branch-node {
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 10px; padding: 12px 16px;
            text-align: center; min-width: 130px; transition: border-color .2s;
        }
        .topo-branch-node:hover { border-color: var(--border2); }
        .topo-branch-node.me { border-color: rgba(0,212,170,.5); background: rgba(0,212,170,.07); }
        .topo-branch-node .icon { font-size: 20px; margin-bottom: 6px; }
        .topo-branch-node .title { font-size: 12px; font-weight: 600; color: var(--text); }
        .topo-branch-node .sub { font-size: 11px; color: var(--muted); margin-top: 3px; font-family: var(--mono); }
        .topo-branch-node .ip { font-size: 11px; color: var(--accent); margin-top: 2px; font-family: var(--mono); }

        .explain-list { list-style: none; display: flex; flex-direction: column; gap: 14px; }
        .explain-list li { display: flex; gap: 14px; align-items: flex-start; color: var(--muted2); font-size: 13px; line-height: 1.7; }
        .explain-list li strong { color: var(--text); }
        .dot-marker {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--accent); margin-top: 7px; flex-shrink: 0;
            box-shadow: 0 0 6px rgba(0,212,170,.5);
        }
    </style>
</head>
<body>

<?php include '_navbar.php'; ?>

<main class="page">

    <div class="page-header fade-in">
        <div class="page-title-group">
            <div>
                <div class="page-title">⌬ Network Map</div>
                <div class="page-sub">Real-time IP distribution across the campus network</div>
            </div>
        </div>
        <div class="live-badge">
            <span class="pulse"></span>
            LIVE
        </div>
    </div>

    <div class="session-bar fade-in">
        <div class="session-chip you"><span class="label">YOUR IP</span><span class="value"><?php echo htmlspecialchars($clientIP); ?></span></div>
        <div class="session-chip"><span class="label">SERVER</span><span class="value"><?php echo $serverAddr; ?></span></div>
        <div class="session-chip"><span class="label">PORT</span><span class="value"><?php echo $serverPort; ?></span></div>
        <div class="session-chip"><span class="label">PROTO</span><span class="value"><?php echo $serverProto; ?></span></div>
    </div>

    <div class="stats-grid fade-in">
        <div class="stat"><div class="stat-icon">🎓</div><div class="stat-num"><?php echo $totalStudents; ?></div><div class="stat-label">Total Students</div></div>
        <div class="stat"><div class="stat-icon">📡</div><div class="stat-num"><?php echo $uniqueIPs; ?></div><div class="stat-label">Unique IP Addresses</div></div>
        <div class="stat"><div class="stat-icon">🔀</div><div class="stat-num"><?php echo $subnetCount; ?></div><div class="stat-label">Active Subnets</div></div>
    </div>

    <div class="card fade-in">
        <div class="card-header">
            <span>📡</span>
            <h2>Network Topology</h2>
            <span class="card-tag">STAR TOPOLOGY</span>
        </div>
        <div class="topology">
            <div class="topo-node accent">
                <div class="topo-node-icon">🌍</div>
                <div>
                    <div class="topo-node-title">Internet / Campus Network</div>
                    <div class="topo-node-sub">External Gateway</div>
                </div>
            </div>
            <div class="topo-connector"><div class="topo-line"></div><div class="topo-connector-label">↓ WAN</div><div class="topo-line"></div></div>
            <div class="topo-node blue">
                <div class="topo-node-icon">🔀</div>
                <div>
                    <div class="topo-node-title">Main Router / Gateway</div>
                    <div class="topo-node-sub">192.168.1.1 — DHCP Server</div>
                </div>
            </div>
            <div class="topo-connector"><div class="topo-line"></div><div class="topo-connector-label">↓ LAN</div><div class="topo-line"></div></div>
            <div class="topo-branch">
                <div class="topo-branch-node">
                    <div class="icon">🖥️</div>
                    <div class="title">Web Server</div>
                    <div class="sub">Apache / PHP</div>
                    <div class="ip"><?php echo $serverAddr; ?>:<?php echo $serverPort; ?></div>
                </div>
                <div class="topo-branch-node">
                    <div class="icon">🗄️</div>
                    <div class="title">Database</div>
                    <div class="sub">MySQL</div>
                    <div class="ip">127.0.0.1:3306</div>
                </div>
                <div class="topo-branch-node me">
                    <div class="icon">💻</div>
                    <div class="title">Your Device</div>
                    <div class="sub">Connected</div>
                    <div class="ip"><?php echo htmlspecialchars($clientIP); ?></div>
                </div>
                <div class="topo-branch-node">
                    <div class="icon">👥</div>
                    <div class="title">Other Clients</div>
                    <div class="sub">192.168.x.x</div>
                    <div class="ip">172.16.x.x ...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card fade-in">
        <div class="card-header">
            <span>🎓</span>
            <h2>Students — IP Distribution</h2>
            <span class="card-tag">REAL NETWORK DATA</span>
        </div>
        <?php
        $result = $conn->query("SELECT StudentID, Name, Department, Year, IPAddress FROM Students ORDER BY INET_ATON(NULLIF(IPAddress, ''))");
        if ($result && $result->num_rows > 0):
        ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Department</th><th>Year</th><th>IP Address</th><th>Subnet</th></tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()):
                    $ip = !empty($row['IPAddress']) ? $row['IPAddress'] : null;
                    $subnet = $ip ? substr($ip, 0, strrpos($ip, '.')) . '.0/24' : null;
                ?>
                    <tr>
                        <td style="font-family:var(--mono);font-size:12px;"><?php echo htmlspecialchars($row['StudentID']); ?></td>
                        <td><strong><?php echo htmlspecialchars($row['Name']); ?></strong></td>
                        <td><span class="badge badge-blue"><?php echo htmlspecialchars($row['Department']); ?></span></td>
                        <td><?php echo htmlspecialchars($row['Year']); ?></td>
                        <td><?php echo $ip ? '<span class="badge badge-accent">' . htmlspecialchars($ip) . '</span>' : '<span style="color:var(--muted)">—</span>'; ?></td>
                        <td><?php echo $subnet ? '<span class="badge badge-blue">' . htmlspecialchars($subnet) . '</span>' : '<span style="color:var(--muted)">—</span>'; ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="empty" style="color:var(--muted);text-align:center;padding:24px;">No students found.</p>
        <?php endif; ?>
    </div>

    <div class="card fade-in">
        <div class="card-header">
            <span>👤</span>
            <h2>System Users — Registration IPs</h2>
            <span class="card-tag">NETWORK TRACKING</span>
        </div>
        <?php
        $result = $conn->query("SELECT Username, Role, Email, IPAddress, RegistrationDate FROM Users ORDER BY RegistrationDate DESC");
        if ($result && $result->num_rows > 0):
        ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Username</th><th>Role</th><th>Email</th><th>Registration IP</th><th>Date</th></tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()):
                    $ip = !empty($row['IPAddress']) ? $row['IPAddress'] : null;
                    $role = htmlspecialchars($row['Role']);
                    $rmap = ['Admin' => 'badge-purple', 'Staff' => 'badge-cyan', 'Teacher' => 'badge-success', 'Registrar' => 'badge-amber'];
                    $rcls = $rmap[$role] ?? 'badge-muted';
                ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['Username']); ?></strong></td>
                        <td><span class="badge <?php echo $rcls; ?>"><?php echo $role; ?></span></td>
                        <td><?php echo htmlspecialchars($row['Email']); ?></td>
                        <td><?php echo $ip ? '<span class="badge badge-accent">' . htmlspecialchars($ip) . '</span>' : '<span style="color:var(--muted)">—</span>'; ?></td>
                        <td style="font-family:var(--mono);font-size:11px;"><?php echo htmlspecialchars($row['RegistrationDate']); ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="empty">No users found.</p>
        <?php endif; ?>
    </div>

    <div class="card fade-in">
        <div class="card-header">
            <span>🔗</span>
            <h2>How IP Distribution Works</h2>
        </div>
        <ul class="explain-list">
            <li><span class="dot-marker"></span><div><strong>Unique IP per student</strong> — each device is assigned an IP at registration time, captured automatically by the server.</div></li>
            <li><span class="dot-marker"></span><div><strong>Subnet segmentation</strong> — ranges like 192.168.1.x, 192.168.2.x, 10.0.0.x, and 172.16.1.x represent separate classrooms or buildings.</div></li>
            <li><span class="dot-marker"></span><div><strong>Request logging</strong> — every HTTP request is recorded with the client's IP, enabling audit trails.</div></li>
            <li><span class="dot-marker"></span><div><strong>Concurrent access</strong> — the server handles multiple simultaneous clients across different subnets.</div></li>
            <li><span class="dot-marker"></span><div><strong>Analytics-ready</strong> — IP data enables security tracking, network health monitoring, and anomaly detection.</div></li>
        </ul>
    </div>

</main>

</body>
</html>
