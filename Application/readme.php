<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';
$activeTab = 'readme';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>README — Student Management System</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .hero {
            background: linear-gradient(135deg, rgba(0,212,170,.08), rgba(59,130,246,.08));
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 36px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%; left: -10%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(0,212,170,.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero h1 {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -.5px;
            margin-bottom: 8px;
            position: relative;
        }

        .hero p {
            color: var(--muted2);
            font-size: 14px;
            max-width: 700px;
            position: relative;
        }

        .hero-tags {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            flex-wrap: wrap;
            position: relative;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 14px;
        }

        .feature {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            transition: all .2s;
        }

        .feature:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .feature h4 {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .feature p {
            font-size: 12px;
            color: var(--muted2);
            line-height: 1.6;
        }

        .feature ul {
            list-style: none;
            padding: 0;
            margin-top: 8px;
        }

        .feature ul li {
            font-size: 12px;
            color: var(--muted2);
            padding: 3px 0;
            font-family: var(--mono);
        }

        .feature ul li::before {
            content: '›';
            color: var(--accent);
            margin-right: 8px;
            font-weight: 700;
        }

        /* Code block */
        .code {
            background: #04060b;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px 22px;
            font-family: var(--mono);
            font-size: 12px;
            line-height: 1.7;
            color: var(--text);
            overflow-x: auto;
            white-space: pre;
        }

        .code .cmd { color: var(--accent3); font-weight: 700; }
        .code .comment { color: var(--accent); font-style: italic; }

        /* Steps */
        .steps {
            counter-reset: step;
            list-style: none;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .steps li {
            counter-increment: step;
            display: flex;
            gap: 14px;
            align-items: flex-start;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 18px;
        }

        .steps li::before {
            content: counter(step);
            width: 26px; height: 26px;
            border-radius: 50%;
            background: var(--accent);
            color: var(--bg);
            font-family: var(--mono);
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .steps li .step-text {
            color: var(--muted2);
            font-size: 13px;
            line-height: 1.6;
        }

        .steps li code {
            background: var(--surface);
            color: var(--accent);
            font-family: var(--mono);
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid var(--border);
        }

        /* Flow diagram ASCII */
        .flow-ascii {
            background: #04060b;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            font-family: var(--mono);
            font-size: 11px;
            color: var(--accent);
            line-height: 1.5;
            overflow-x: auto;
            text-align: center;
            white-space: pre;
        }
    </style>
</head>
<body>

<?php include '_navbar.php'; ?>

<main class="page">

    <!-- Hero -->
    <div class="hero fade-in">
        <h1>📚 Student Management System</h1>
        <p>A complete Computer Networks project combining a full-stack PHP application with a Cisco Packet Tracer network simulation. The project demonstrates how application-layer services run on top of a properly designed network infrastructure with VLANs, subnetting, ACLs, NAT, and DHCP.</p>
        <div class="hero-tags">
            <span class="badge badge-accent">PHP 8.x</span>
            <span class="badge badge-blue">MySQL</span>
            <span class="badge badge-amber">Cisco Packet Tracer</span>
            <span class="badge badge-purple">VLANs / VLSM</span>
            <span class="badge badge-cyan">REST-style API</span>
        </div>
    </div>

    <!-- Overview -->
    <div class="card fade-in">
        <div class="card-header">
            <span>🎯</span>
            <h2>Project Overview</h2>
        </div>
        <div class="grid-2">
            <div class="feature">
                <h4>💻 Application Layer</h4>
                <p>PHP 8.x backend + MySQL database powering student registration, courses, grades, attendance, and user management with role-based access control.</p>
            </div>
            <div class="feature">
                <h4>🌐 Network Layer</h4>
                <p>Cisco Packet Tracer simulation featuring VLANs, VLSM subnetting, ACLs, NAT/PAT, DHCP, DNS, and Inter-VLAN Routing.</p>
            </div>
        </div>
    </div>

    <!-- Tech Stack -->
    <div class="card fade-in">
        <div class="card-header">
            <span>🛠️</span>
            <h2>Technologies Used</h2>
        </div>
        <div class="grid-2">
            <div class="feature">
                <h4>📱 Application Stack</h4>
                <ul>
                    <li>PHP 8.x (Backend)</li>
                    <li>MySQL (Database)</li>
                    <li>HTML5, CSS3, JavaScript</li>
                    <li>AJAX / Fetch API</li>
                </ul>
            </div>
            <div class="feature">
                <h4>🌐 Network Simulation</h4>
                <ul>
                    <li>Cisco Packet Tracer 8.x</li>
                    <li>Cisco IOS CLI</li>
                    <li>Routers (ISR 4321)</li>
                    <li>L3 / L2 Switches</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Network Features -->
    <div class="card fade-in">
        <div class="card-header">
            <span>🌍</span>
            <h2>Network Features Implemented</h2>
        </div>
        <div class="grid-2">
            <div class="feature">
                <h4>🔹 Layer 2 Features</h4>
                <p>VLANs (10, 20, 30, 40, 50, 99), STP (Spanning Tree), Port Security, Trunk Links</p>
            </div>
            <div class="feature">
                <h4>🔹 Layer 3 Features</h4>
                <p>Inter-VLAN Routing (Router-on-a-Stick), Static & Dynamic Routing (RIPv2)</p>
            </div>
            <div class="feature">
                <h4>🔹 Security</h4>
                <p>Access Control Lists (ACL), SSH for remote management, Port Security on access ports</p>
            </div>
            <div class="feature">
                <h4>🔹 Network Services</h4>
                <p>DHCP Server (per-VLAN pools), DNS Server, NAT/PAT for Internet access</p>
            </div>
            <div class="feature">
                <h4>🔹 IP Planning</h4>
                <p>VLSM Subnetting, Classless Inter-Domain Routing (CIDR)</p>
            </div>
            <div class="feature">
                <h4>🔹 Application Layer</h4>
                <p>HTTP (Port 80), HTTPS (Port 443), MySQL (Port 3306)</p>
            </div>
        </div>
    </div>

    <!-- IP Plan -->
    <div class="card fade-in">
        <div class="card-header">
            <span>📊</span>
            <h2>IP Addressing Plan</h2>
            <span class="card-tag">VLSM SUBNETTING</span>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>VLAN</th><th>Purpose</th><th>Network</th><th>CIDR</th><th>Subnet Mask</th><th>Usable IPs</th><th>Gateway</th></tr>
                </thead>
                <tbody>
                    <tr><td><span class="badge badge-accent">VLAN 10</span></td><td><strong>Admin</strong></td><td><span class="chip-mono">192.168.10.0</span></td><td>/24</td><td>255.255.255.0</td><td>254</td><td><span class="chip-mono">192.168.10.1</span></td></tr>
                    <tr><td><span class="badge badge-blue">VLAN 20</span></td><td><strong>Staff</strong></td><td><span class="chip-mono">192.168.20.0</span></td><td>/25</td><td>255.255.255.128</td><td>126</td><td><span class="chip-mono">192.168.20.1</span></td></tr>
                    <tr><td><span class="badge badge-amber">VLAN 30</span></td><td><strong>Students</strong></td><td><span class="chip-mono">192.168.30.0</span></td><td>/25</td><td>255.255.255.128</td><td>126</td><td><span class="chip-mono">192.168.30.1</span></td></tr>
                    <tr><td><span class="badge badge-purple">VLAN 40</span></td><td><strong>Teachers</strong></td><td><span class="chip-mono">192.168.40.0</span></td><td>/26</td><td>255.255.255.192</td><td>62</td><td><span class="chip-mono">192.168.40.1</span></td></tr>
                    <tr><td><span class="badge badge-cyan">VLAN 50</span></td><td><strong>Servers</strong></td><td><span class="chip-mono">172.16.1.0</span></td><td>/24</td><td>255.255.255.0</td><td>254</td><td><span class="chip-mono">172.16.1.1</span></td></tr>
                    <tr><td><span class="badge badge-danger">VLAN 99</span></td><td><strong>Management</strong></td><td><span class="chip-mono">10.10.99.0</span></td><td>/28</td><td>255.255.255.240</td><td>14</td><td><span class="chip-mono">10.10.99.1</span></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- VLAN Switch Config -->
    <div class="card fade-in">
        <div class="card-header">
            <span>🔧</span>
            <h2>VLAN Configuration (Switch Commands)</h2>
        </div>
<pre class="code"><span class="cmd">enable
configure terminal</span>

<span class="comment">! Create VLANs</span>
vlan 10
 name Admin
vlan 20
 name Staff
vlan 30
 name Students
vlan 40
 name Teachers
vlan 50
 name Servers
vlan 99
 name Management

<span class="comment">! Configure Trunk Ports</span>
interface gigabitethernet 0/1
 switchport mode trunk
 switchport trunk allowed vlan 10,20,30,40,50,99

<span class="comment">! Configure Access Ports</span>
interface fastethernet 0/1
 switchport mode access
 switchport access vlan 10
 switchport port-security
 switchport port-security maximum 1
</pre>
    </div>

    <!-- ACL Rules -->
    <div class="card fade-in">
        <div class="card-header">
            <span>🔐</span>
            <h2>Access Control List Rules</h2>
            <span class="card-tag">SECURITY POLICY</span>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>#</th><th>Rule</th><th>Source</th><th>Destination</th><th>Action</th><th>Purpose</th></tr>
                </thead>
                <tbody>
                    <tr><td>1</td><td>Students → Admin</td><td><span class="chip-mono">192.168.30.0/25</span></td><td><span class="chip-mono">192.168.10.0/24</span></td><td><span class="badge badge-danger">DENY</span></td><td>Students cannot access Admin VLAN</td></tr>
                    <tr><td>2</td><td>Students → Teachers</td><td><span class="chip-mono">192.168.30.0/25</span></td><td><span class="chip-mono">192.168.40.0/26</span></td><td><span class="badge badge-danger">DENY</span></td><td>Students cannot access Teachers VLAN</td></tr>
                    <tr><td>3</td><td>Staff → Servers</td><td><span class="chip-mono">192.168.20.0/25</span></td><td><span class="chip-mono">172.16.1.0/24</span></td><td><span class="badge badge-success">ALLOW</span></td><td>Staff can access servers</td></tr>
                    <tr><td>4</td><td>All → Internet</td><td>ALL</td><td>ANY</td><td><span class="badge badge-success">PERMIT</span></td><td>After NAT, all VLANs reach internet</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- App Features -->
    <div class="card fade-in">
        <div class="card-header">
            <span>📱</span>
            <h2>Application Features</h2>
        </div>
        <div class="grid-2">
            <div class="feature"><h4>✅ Student Management</h4><p>Add, view, delete students. Each student's IP is captured at registration.</p></div>
            <div class="feature"><h4>✅ Course Management</h4><p>Create courses with code, name, credits, instructor, and semester.</p></div>
            <div class="feature"><h4>✅ Enrollment System</h4><p>Register students to courses and track enrollment status.</p></div>
            <div class="feature"><h4>✅ Grade Management</h4><p>Record midterm, final, and assignment grades. Auto-calculates GPA.</p></div>
            <div class="feature"><h4>✅ Attendance Tracking</h4><p>Record daily attendance with status (Present / Absent / Late) and notes.</p></div>
            <div class="feature"><h4>✅ Authentication</h4><p>Role-based access (Admin / Staff / Teacher) with session management.</p></div>
        </div>
    </div>

    <!-- How to Run -->
    <div class="card fade-in">
        <div class="card-header">
            <span>🚀</span>
            <h2>How to Run</h2>
        </div>

        <h3 style="font-size:13px;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;">📌 PHP Application</h3>
        <ol class="steps" style="margin-bottom:24px;">
            <li><div class="step-text">Install <strong>XAMPP</strong> or <strong>WAMP</strong> (Apache + MySQL)</div></li>
            <li><div class="step-text">Copy the project folder to <code>htdocs/</code> or <code>www/</code></div></li>
            <li><div class="step-text">Import <code>database.sql</code> via phpMyAdmin</div></li>
            <li><div class="step-text">Open browser: <code>http://localhost/project_folder/login.php</code></div></li>
            <li><div class="step-text">Login with: <code>admin / admin123</code></div></li>
        </ol>

        <h3 style="font-size:13px;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;">📌 Packet Tracer Network</h3>
        <ol class="steps">
            <li><div class="step-text">Open <code>University_Network.pkt</code> in Cisco Packet Tracer 8.x+</div></li>
            <li><div class="step-text">Verify configurations on routers and switches</div></li>
            <li><div class="step-text">Test ping between devices in different VLANs</div></li>
            <li><div class="step-text">Test ACLs (e.g. Students cannot ping Admin)</div></li>
            <li><div class="step-text">Verify DHCP — PCs should automatically receive IPs</div></li>
        </ol>
    </div>

    <!-- Data Flow Diagram -->
    <div class="card fade-in">
        <div class="card-header">
            <span>📡</span>
            <h2>Data Flow Diagram</h2>
            <span class="card-tag">NETWORK PERSPECTIVE</span>
        </div>
<pre class="flow-ascii">┌─────────────┐    HTTP Request    ┌─────────────┐    SQL Query    ┌─────────────┐
│   Client    │ ─────────────────► │   Router    │ ──────────────► │  Database   │
│ (Laptop/PC) │      Port 80       │  (Gateway)  │    Port 3306    │   Server    │
│ VLAN 10/20/ │                    │             │                 │  VLAN 50    │
│   30/40     │                    └──────┬──────┘                 └──────┬──────┘
└─────────────┘                           │                               │
        ▲                                 │                               │
        │                                 │ ◄─────────────────────────────┘
        │         HTTP Response           │         Data Result
        └─────────────────────────────────┘
                     (JSON/HTML)</pre>
    </div>

</main>

</body>
</html>
