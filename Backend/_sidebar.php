<?php
$activeTab = $activeTab ?? '';
$navUser = $_SESSION['username'] ?? 'Guest';
$navRole = $_SESSION['role'] ?? 'User';
$navName = $_SESSION['fullname'] ?? $navUser;
$navInit = strtoupper(substr($navUser, 0, 1));

$navTools = [
    'inspector' => ['label' => 'Packet Inspector', 'icon' => '◍', 'href' => 'index.php',     'badge' => 'LIVE'],
    'history'   => ['label' => 'Capture History',  'icon' => '⟐', 'href' => 'history.php',   'badge' => null],
    'anatomy'   => ['label' => 'Packet Anatomy',   'icon' => '◊', 'href' => 'inspector.php', 'badge' => null],
];

$navUtils = [
    'subnet'   => ['label' => 'Subnet Calculator',  'icon' => '#', 'href' => 'subnet.php',    'badge' => null],
    'ping'     => ['label' => 'Ping & Traceroute',  'icon' => '⚡','href' => 'ping.php',      'badge' => null],
    'portscan' => ['label' => 'Port Scanner',       'icon' => '⊕', 'href' => 'portscan.php',  'badge' => null],
    'compare'  => ['label' => 'TCP vs UDP',         'icon' => '⇄', 'href' => 'compare.php',   'badge' => null],
];

$navLearn = [
    'learn' => ['label' => 'OSI Model',     'icon' => '※', 'href' => 'learn.php', 'badge' => null],
    'quiz'  => ['label' => 'Knowledge Quiz', 'icon' => '✓', 'href' => 'quiz.php',  'badge' => 'NEW'],
];

function renderLink($key, $tab, $activeTab) {
    $active = $activeTab === $key ? 'active' : '';
    $badge = $tab['badge'] ? '<span class="sidebar-link-badge">'.$tab['badge'].'</span>' : '';
    return '<a href="'.$tab['href'].'" class="sidebar-link '.$active.'">
                <span class="sidebar-link-icon">'.$tab['icon'].'</span>
                <span>'.$tab['label'].'</span>'.$badge.'
            </a>';
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">◍</div>
        <div class="brand-info">
            <div class="brand-name">OSI Inspector</div>
            <div class="brand-version">v<?php echo APP_VERSION; ?> · pro</div>
        </div>
    </div>

    <!-- User card -->
    <div style="padding:12px 16px;border-bottom:1px solid var(--border);background:var(--bg-elevated);">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#8b5cf6);color:white;display:grid;place-items:center;font-weight:700;font-size:14px;flex-shrink:0;">
                <?php echo htmlspecialchars($navInit); ?>
            </div>
            <div style="min-width:0;flex:1;">
                <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($navName); ?></div>
                <div style="font-size:10px;color:var(--text-muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:.5px;"><?php echo htmlspecialchars($navRole); ?></div>
            </div>
            <a href="logout.php" title="Sign out" style="background:transparent;border:1px solid var(--border);color:var(--text-muted);width:28px;height:28px;border-radius:6px;display:grid;place-items:center;text-decoration:none;font-size:13px;flex-shrink:0;">
                ↗
            </a>
        </div>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-label">Tools</div>
        <?php foreach ($navTools as $k => $t) echo renderLink($k, $t, $activeTab); ?>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-label">Network Utilities</div>
        <?php foreach ($navUtils as $k => $t) echo renderLink($k, $t, $activeTab); ?>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-label">Learn</div>
        <?php foreach ($navLearn as $k => $t) echo renderLink($k, $t, $activeTab); ?>
    </div>

    <div class="sidebar-footer">
        <div style="font-size:11px;color:var(--text-subtle);font-family:var(--mono);">
            ⓘ Connected
        </div>
        <button class="theme-toggle" onclick="toggleTheme()" title="Toggle theme">
            <span id="themeIcon">🌙</span>
        </button>
    </div>
</aside>

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
    const icon = document.getElementById('themeIcon');
    if (icon) icon.textContent = saved === 'dark' ? '☀️' : '🌙';
})();
</script>
