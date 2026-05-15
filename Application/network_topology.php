<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';
$activeTab = 'topology';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Topology — University Network Design</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        /* Topology diagram */
        .topo-box {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 28px 20px;
            text-align: center;
        }

        .topo-tier {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .topo-line-v {
            width: 1px; height: 24px;
            background: var(--border2);
            margin: 6px auto;
        }

        .topo-device {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 18px;
            min-width: 140px;
            transition: all .2s;
        }

        .topo-device:hover { border-color: var(--border2); transform: translateY(-1px); }

        .topo-device .icon { font-size: 22px; margin-bottom: 4px; }
        .topo-device .label { font-size: 12px; font-weight: 600; }
        .topo-device .meta { font-size: 10px; color: var(--muted); font-family: var(--mono); margin-top: 3px; }

        .topo-device.router { border-color: rgba(0,212,170,.4); background: rgba(0,212,170,.05); }
        .topo-device.switch { border-color: rgba(59,130,246,.4); background: rgba(59,130,246,.05); }
        .topo-device.vlan   { border-color: rgba(245,158,11,.4); background: rgba(245,158,11,.05); }

        /* Legend */
        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: center;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
        }

        .legend-item {
            display: flex; align-items: center; gap: 8px;
            font-size: 12px;
            color: var(--muted2);
        }

        .legend-color {
            width: 12px; height: 12px;
            border-radius: 3px;
        }

        /* VLAN Cards */
        .vlan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
        }

        .vlan-card {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            position: relative;
            transition: all .2s;
        }

        .vlan-card:hover { border-color: var(--accent); transform: translateY(-2px); }

        .vlan-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: var(--accent);
        }

        .vlan-card[data-vlan="20"]::before { background: var(--accent2); }
        .vlan-card[data-vlan="30"]::before { background: var(--accent3); }
        .vlan-card[data-vlan="40"]::before { background: var(--purple); }
        .vlan-card[data-vlan="50"]::before { background: var(--cyan); }
        .vlan-card[data-vlan="99"]::before { background: var(--danger); }

        .vlan-card h3 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
            font-family: var(--mono);
        }

        .vlan-card .role {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .vlan-card .row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-family: var(--mono);
            padding: 4px 0;
        }

        .vlan-card .row .k { color: var(--muted); }
        .vlan-card .row .v { color: var(--accent); }

        /* Code config */
        .cfg {
            background: #04060b;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 24px;
            font-family: var(--mono);
            font-size: 12px;
            color: var(--text);
            line-height: 1.7;
            overflow-x: auto;
            white-space: pre;
        }

        .cfg .cmd     { color: var(--accent3); font-weight: 700; }
        .cfg .comment { color: var(--accent); font-style: italic; }
        .cfg .keyword { color: var(--accent2); }
        .cfg .ip      { color: #ff6b9d; }

        @media (max-width: 700px) {
            .topo-tier { flex-direction: column; }
        }
    </style>
</head>
<body>

<?php include '_navbar.php'; ?>

<main class="page">

    <div class="page-header fade-in">
        <div class="page-title-group">
            <div>
                <div class="page-title">⎈ Network Topology</div>
                <div class="page-sub">Complete network design — VLANs, subnetting, and inter-VLAN routing</div>
            </div>
        </div>
        <span class="badge badge-accent" style="padding:6px 12px;">Cisco Packet Tracer 8.x</span>
    </div>

    <!-- Topology Diagram -->
    <div class="card fade-in">
        <div class="card-header">
            <span>🗺️</span>
            <h2>Hierarchical Network Diagram</h2>
            <span class="card-tag">3-TIER MODEL</span>
        </div>
        <div class="topo-box">
            <!-- Internet -->
            <div class="topo-tier">
                <div class="topo-device" style="border-color:var(--muted);">
                    <div class="icon">🌍</div>
                    <div class="label">Internet</div>
                    <div class="meta">ISP</div>
                </div>
            </div>
            <div class="topo-line-v"></div>

            <!-- Core Router -->
            <div class="topo-tier">
                <div class="topo-device router">
                    <div class="icon">🔀</div>
                    <div class="label">Core Router</div>
                    <div class="meta">Cisco ISR 4321</div>
                </div>
            </div>
            <div class="topo-line-v"></div>

            <!-- Distribution -->
            <div class="topo-tier">
                <div class="topo-device switch">
                    <div class="icon">🔌</div>
                    <div class="label">Distribution SW1</div>
                    <div class="meta">L3 Switch</div>
                </div>
                <div class="topo-device switch">
                    <div class="icon">🔌</div>
                    <div class="label">Distribution SW2</div>
                    <div class="meta">L3 Switch</div>
                </div>
                <div class="topo-device switch">
                    <div class="icon">🔌</div>
                    <div class="label">Distribution SW3</div>
                    <div class="meta">L3 Switch</div>
                </div>
            </div>
            <div class="topo-line-v"></div>

            <!-- Access / VLANs -->
            <div class="topo-tier">
                <div class="topo-device vlan"><div class="icon">👨‍💼</div><div class="label">VLAN 10</div><div class="meta">Admin</div></div>
                <div class="topo-device vlan"><div class="icon">👔</div><div class="label">VLAN 20</div><div class="meta">Staff</div></div>
                <div class="topo-device vlan"><div class="icon">🎓</div><div class="label">VLAN 30</div><div class="meta">Students</div></div>
                <div class="topo-device vlan"><div class="icon">👨‍🏫</div><div class="label">VLAN 40</div><div class="meta">Teachers</div></div>
                <div class="topo-device vlan"><div class="icon">🖥️</div><div class="label">VLAN 50</div><div class="meta">Servers</div></div>
                <div class="topo-device vlan"><div class="icon">⚙️</div><div class="label">VLAN 99</div><div class="meta">Mgmt</div></div>
            </div>

            <div class="legend">
                <div class="legend-item"><div class="legend-color" style="background:var(--accent);"></div> Core Router</div>
                <div class="legend-item"><div class="legend-color" style="background:var(--accent2);"></div> Distribution Switches</div>
                <div class="legend-item"><div class="legend-color" style="background:var(--accent3);"></div> Access / VLANs</div>
            </div>
        </div>
    </div>

    <!-- VLAN Configuration -->
    <div class="card fade-in">
        <div class="card-header">
            <span>🔧</span>
            <h2>VLAN Configuration Details</h2>
            <span class="card-tag">VLSM SUBNETTING</span>
        </div>
        <div class="vlan-grid">
            <div class="vlan-card" data-vlan="10">
                <h3>VLAN 10</h3>
                <div class="role">Admin Department</div>
                <div class="row"><span class="k">Network</span><span class="v">192.168.10.0/24</span></div>
                <div class="row"><span class="k">Gateway</span><span class="v">192.168.10.1</span></div>
                <div class="row"><span class="k">DHCP</span><span class="v">Yes</span></div>
            </div>
            <div class="vlan-card" data-vlan="20">
                <h3>VLAN 20</h3>
                <div class="role">Staff Department</div>
                <div class="row"><span class="k">Network</span><span class="v">192.168.20.0/25</span></div>
                <div class="row"><span class="k">Gateway</span><span class="v">192.168.20.1</span></div>
                <div class="row"><span class="k">DHCP</span><span class="v">Yes</span></div>
            </div>
            <div class="vlan-card" data-vlan="30">
                <h3>VLAN 30</h3>
                <div class="role">Students</div>
                <div class="row"><span class="k">Network</span><span class="v">192.168.30.0/25</span></div>
                <div class="row"><span class="k">Gateway</span><span class="v">192.168.30.1</span></div>
                <div class="row"><span class="k">DHCP</span><span class="v">Yes</span></div>
            </div>
            <div class="vlan-card" data-vlan="40">
                <h3>VLAN 40</h3>
                <div class="role">Teachers</div>
                <div class="row"><span class="k">Network</span><span class="v">192.168.40.0/26</span></div>
                <div class="row"><span class="k">Gateway</span><span class="v">192.168.40.1</span></div>
                <div class="row"><span class="k">DHCP</span><span class="v">Yes</span></div>
            </div>
            <div class="vlan-card" data-vlan="50">
                <h3>VLAN 50</h3>
                <div class="role">Servers</div>
                <div class="row"><span class="k">Network</span><span class="v">172.16.1.0/24</span></div>
                <div class="row"><span class="k">Gateway</span><span class="v">172.16.1.1</span></div>
                <div class="row"><span class="k">IP</span><span class="v">Static</span></div>
            </div>
            <div class="vlan-card" data-vlan="99">
                <h3>VLAN 99</h3>
                <div class="role">Management</div>
                <div class="row"><span class="k">Network</span><span class="v">10.10.99.0/28</span></div>
                <div class="row"><span class="k">Gateway</span><span class="v">10.10.99.1</span></div>
                <div class="row"><span class="k">Access</span><span class="v">SSH Only</span></div>
            </div>
        </div>
    </div>

    <!-- Device Inventory -->
    <div class="card fade-in">
        <div class="card-header">
            <span>🖥️</span>
            <h2>Device Inventory</h2>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Device</th><th>Model</th><th>Qty</th><th>VLAN</th><th>IP Range</th></tr>
                </thead>
                <tbody>
                    <tr><td><strong>Core Router</strong></td><td>Cisco ISR 4321</td><td>1</td><td>—</td><td><span class="badge badge-accent">192.168.1.1</span></td></tr>
                    <tr><td><strong>Distribution Switch</strong></td><td>Cisco L3 Switch</td><td>3</td><td>—</td><td><span class="badge badge-purple">VLAN 99</span></td></tr>
                    <tr><td><strong>Access Switch</strong></td><td>Cisco L2 Switch</td><td>6</td><td>10,20,30,40,50,99</td><td>Per VLAN</td></tr>
                    <tr><td><strong>Admin PCs</strong></td><td>Desktop</td><td>5</td><td>10</td><td><span class="badge badge-accent">192.168.10.10–14</span></td></tr>
                    <tr><td><strong>Staff PCs</strong></td><td>Desktop</td><td>10</td><td>20</td><td><span class="badge badge-accent">192.168.20.10–19</span></td></tr>
                    <tr><td><strong>Student PCs</strong></td><td>Desktop / Laptop</td><td>30</td><td>30</td><td><span class="badge badge-accent">192.168.30.10–39</span></td></tr>
                    <tr><td><strong>Teacher PCs</strong></td><td>Desktop</td><td>8</td><td>40</td><td><span class="badge badge-accent">192.168.40.10–17</span></td></tr>
                    <tr><td><strong>Web Server</strong></td><td>Server</td><td>1</td><td>50</td><td><span class="badge badge-accent">172.16.1.10</span></td></tr>
                    <tr><td><strong>Database Server</strong></td><td>Server</td><td>1</td><td>50</td><td><span class="badge badge-accent">172.16.1.20</span></td></tr>
                    <tr><td><strong>DHCP / DNS Server</strong></td><td>Server</td><td>1</td><td>50</td><td><span class="badge badge-accent">172.16.1.5</span></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Core Router Config -->
    <div class="card fade-in">
        <div class="card-header">
            <span>⚙️</span>
            <h2>Core Router Configuration</h2>
            <span class="card-tag">CISCO IOS CLI</span>
        </div>
<pre class="cfg"><span class="cmd">enable
configure terminal
hostname UniversityRouter</span>

<span class="comment">! Configure WAN Interface to ISP</span>
<span class="keyword">interface</span> gig0/0
 ip address <span class="ip">192.168.1.1 255.255.255.0</span>
 no shutdown

<span class="comment">! Sub-interfaces for Inter-VLAN Routing (Router-on-a-Stick)</span>
<span class="keyword">interface</span> gig0/1.10
 encapsulation dot1Q 10
 ip address <span class="ip">192.168.10.1 255.255.255.0</span>

<span class="keyword">interface</span> gig0/1.20
 encapsulation dot1Q 20
 ip address <span class="ip">192.168.20.1 255.255.255.128</span>

<span class="keyword">interface</span> gig0/1.30
 encapsulation dot1Q 30
 ip address <span class="ip">192.168.30.1 255.255.255.128</span>

<span class="keyword">interface</span> gig0/1.40
 encapsulation dot1Q 40
 ip address <span class="ip">192.168.40.1 255.255.255.192</span>

<span class="keyword">interface</span> gig0/1.50
 encapsulation dot1Q 50
 ip address <span class="ip">172.16.1.1 255.255.255.0</span>

<span class="comment">! DHCP Pools for each VLAN</span>
ip dhcp pool AdminPool
 network <span class="ip">192.168.10.0 255.255.255.0</span>
 default-router <span class="ip">192.168.10.1</span>
 dns-server <span class="ip">8.8.8.8</span>

ip dhcp pool StaffPool
 network <span class="ip">192.168.20.0 255.255.255.128</span>
 default-router <span class="ip">192.168.20.1</span>

ip dhcp pool StudentsPool
 network <span class="ip">192.168.30.0 255.255.255.128</span>
 default-router <span class="ip">192.168.30.1</span>

ip dhcp pool TeachersPool
 network <span class="ip">192.168.40.0 255.255.255.192</span>
 default-router <span class="ip">192.168.40.1</span>

<span class="comment">! ACL — block Students from Admin and Teacher VLANs</span>
access-list 100 deny   ip <span class="ip">192.168.30.0 0.0.0.127</span> <span class="ip">192.168.10.0 0.0.0.255</span>
access-list 100 deny   ip <span class="ip">192.168.30.0 0.0.0.127</span> <span class="ip">192.168.40.0 0.0.0.63</span>
access-list 100 permit ip any any

<span class="keyword">interface</span> gig0/1.30
 ip access-group 100 in

<span class="comment">! NAT/PAT for Internet access</span>
access-list 1 permit <span class="ip">192.168.10.0 0.0.0.255</span>
access-list 1 permit <span class="ip">192.168.20.0 0.0.0.127</span>
access-list 1 permit <span class="ip">192.168.30.0 0.0.0.127</span>
access-list 1 permit <span class="ip">192.168.40.0 0.0.0.63</span>
access-list 1 permit <span class="ip">172.16.1.0   0.0.0.255</span>

ip nat inside source list 1 interface gig0/0 overload

<span class="comment">! Default route to ISP</span>
ip route <span class="ip">0.0.0.0 0.0.0.0 192.168.1.2</span>

<span class="comment">! SSH for secure management</span>
ip domain-name university.local
crypto key generate rsa modulus 2048
username admin secret admin123
line vty 0 4
 transport input ssh
 login local
</pre>
    </div>

    <!-- Distribution Switch Config -->
    <div class="card fade-in">
        <div class="card-header">
            <span>🔌</span>
            <h2>Distribution Switch Configuration</h2>
            <span class="card-tag">L2 / STP</span>
        </div>
<pre class="cfg"><span class="cmd">enable
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

<span class="comment">! Trunk port to Router</span>
<span class="keyword">interface</span> gig1/0/1
 switchport mode trunk
 switchport trunk allowed vlan 10,20,30,40,50,99

<span class="comment">! Access ports with port-security</span>
<span class="keyword">interface</span> fast0/1
 switchport mode access
 switchport access vlan 10
 switchport port-security
 switchport port-security maximum 1
 switchport port-security violation shutdown

<span class="keyword">interface</span> fast0/2
 switchport mode access
 switchport access vlan 20
 switchport port-security
 switchport port-security maximum 1

<span class="comment">! STP — set this switch as Root Bridge for VLAN 10</span>
spanning-tree vlan 10 root primary
spanning-tree vlan 20 root secondary
</pre>
    </div>

</main>

</body>
</html>
