<?php
// _navbar.php — shared top navigation
// Include AFTER session_start() and after defining $activeTab
$activeTab = $activeTab ?? '';
$navUser   = $_SESSION['username'] ?? 'Guest';
$navRole   = $_SESSION['role'] ?? 'User';
$navInit   = strtoupper(substr($navUser, 0, 1));

$tabs = [
    'dashboard'   => ['label' => 'Dashboard',    'icon' => '◐', 'href' => 'index.php#dashboard'],
    'students'    => ['label' => 'Students',     'icon' => '◉', 'href' => 'index.php#students'],
    'courses'     => ['label' => 'Courses',      'icon' => '◈', 'href' => 'index.php#courses'],
    'enrollments' => ['label' => 'Enrollments',  'icon' => '◇', 'href' => 'index.php#enrollments'],
    'courseroster'=> ['label' => 'Course Roster','icon' => '📋', 'href' => 'course_enrollments.php'],
    'grades'      => ['label' => 'Grades',       'icon' => '◆', 'href' => 'index.php#grades'],
    'attendance'  => ['label' => 'Attendance',   'icon' => '◎', 'href' => 'index.php#attendance'],
    'monitor'     => ['label' => 'Monitor',      'icon' => '◍', 'href' => 'network_monitor.php'],
    'logs'        => ['label' => 'Activity Log', 'icon' => '⟐', 'href' => 'activity_log.php'],
    'network'     => ['label' => 'Network Map',  'icon' => '⌬', 'href' => 'network_map.php'],
    'topology'    => ['label' => 'Topology',     'icon' => '⎈', 'href' => 'network_topology.php'],
];

// New Network Concepts pages (dropdown)
$concepts = [
    'vpn'      => ['label' => 'VPN Status',      'icon' => '🔐', 'href' => 'vpn_status.php'],
    'voip'     => ['label' => 'VoIP Directory',  'icon' => '📞', 'href' => 'voip_directory.php'],
    'qos'      => ['label' => 'QoS Monitor',     'icon' => '⚡', 'href' => 'qos_monitor.php'],
    'routing'  => ['label' => 'Routing Table',   'icon' => '🛣️', 'href' => 'routing_table.php'],
    'dhcp'     => ['label' => 'DHCP Leases',     'icon' => '📡', 'href' => 'dhcp_leases.php'],
    'security' => ['label' => 'Security Center', 'icon' => '🛡️', 'href' => 'security_center.php'],
];
?>
<style>
.nav-shell {
    position: sticky;
    top: 0;
    z-index: 100;
    background: var(--surface, #0f1729);
    border-bottom: 1px solid var(--border, #1f2a44);
    backdrop-filter: blur(12px);
}
.nav-inner {
    max-width: 1600px;
    margin: 0 auto;
    padding: 12px 24px;
    display: flex;
    align-items: center;
    gap: 18px;
}
.nav-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    color: var(--text, #e6edf7);
    font-size: 14px;
    letter-spacing: -.2px;
}
.nav-brand-logo {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, var(--teal, #14b8a6), var(--blue, #3b82f6));
    border-radius: 8px;
    display: grid;
    place-items: center;
    color: white;
    font-weight: 800;
}
.nav-tabs {
    display: flex;
    gap: 2px;
    flex: 1;
    overflow-x: auto;
    scrollbar-width: none;
}
.nav-tabs::-webkit-scrollbar { display: none; }
.nav-tab {
    padding: 8px 12px;
    border-radius: 7px;
    color: var(--text-muted, #94a3b8);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
    transition: all .15s;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}
.nav-tab:hover { background: var(--surface-2, #182338); color: var(--text, #e6edf7); }
.nav-tab.active {
    background: var(--teal-bg, rgba(20,184,166,.15));
    color: var(--teal, #14b8a6);
}
.nav-tab-icon { font-size: 12px; opacity: .8; }

/* Dropdown for Network Concepts */
.nav-dropdown {
    position: relative;
}
.nav-dropdown-trigger {
    cursor: pointer;
}
.nav-dropdown-menu {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    background: var(--surface-2, #182338);
    border: 1px solid var(--border, #1f2a44);
    border-radius: 10px;
    padding: 6px;
    min-width: 220px;
    display: none;
    flex-direction: column;
    gap: 1px;
    box-shadow: 0 12px 28px rgba(0,0,0,.4);
    z-index: 110;
}
.nav-dropdown:hover .nav-dropdown-menu { display: flex; }
.nav-dropdown-menu .nav-tab {
    border-radius: 6px;
    padding: 9px 12px;
}
.nav-dropdown-label {
    font-size: 10px;
    color: var(--text-muted, #94a3b8);
    padding: 6px 12px 4px;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 700;
}

.nav-user {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 5px 12px 5px 5px;
    background: var(--surface-2, #182338);
    border: 1px solid var(--border, #1f2a44);
    border-radius: 20px;
    color: var(--text, #e6edf7);
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    flex-shrink: 0;
}
.nav-user-avatar {
    width: 26px; height: 26px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--teal, #14b8a6), var(--blue, #3b82f6));
    display: grid;
    place-items: center;
    color: white;
    font-weight: 700;
    font-size: 11px;
}
.nav-logout {
    color: var(--danger, #ef4444);
    text-decoration: none;
    font-size: 12px;
    padding: 4px 10px;
    border: 1px solid var(--border, #1f2a44);
    border-radius: 7px;
    transition: all .15s;
}
.nav-logout:hover { background: rgba(239,68,68,.1); border-color: var(--danger, #ef4444); }
</style>

<div class="nav-shell">
    <div class="nav-inner">
        <a href="index.php" class="nav-brand">
            <span class="nav-brand-logo">SM</span>
            <span>Student Management</span>
        </a>

        <nav class="nav-tabs">
            <?php foreach ($tabs as $key => $tab):
                $active = $activeTab === $key ? 'active' : '';
            ?>
                <a href="<?php echo $tab['href']; ?>" class="nav-tab <?php echo $active; ?>">
                    <span class="nav-tab-icon"><?php echo $tab['icon']; ?></span>
                    <span><?php echo $tab['label']; ?></span>
                </a>
            <?php endforeach; ?>

            <!-- Network Concepts Dropdown -->
            <div class="nav-dropdown">
                <a href="#" class="nav-tab nav-dropdown-trigger <?php echo array_key_exists($activeTab, $concepts) ? 'active' : ''; ?>">
                    <span class="nav-tab-icon">⚙️</span>
                    <span>Network Concepts ▾</span>
                </a>
                <div class="nav-dropdown-menu">
                    <div class="nav-dropdown-label">17 Concepts Coverage</div>
                    <?php foreach ($concepts as $key => $tab):
                        $active = $activeTab === $key ? 'active' : '';
                    ?>
                        <a href="<?php echo $tab['href']; ?>" class="nav-tab <?php echo $active; ?>">
                            <span class="nav-tab-icon"><?php echo $tab['icon']; ?></span>
                            <span><?php echo $tab['label']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </nav>

        <span class="nav-user">
            <span class="nav-user-avatar"><?php echo $navInit; ?></span>
            <span><?php echo htmlspecialchars($navUser); ?></span>
        </span>
        <a href="logout.php" class="nav-logout">Logout</a>
    </div>
</div>
