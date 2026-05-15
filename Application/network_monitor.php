<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';
include 'logger.php';
$activeTab = 'monitor';

// Log this page view
logActivity($conn, 'Viewed network monitor', 'View', null, null, 'Network monitoring dashboard accessed', 200);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Monitor — Live Dashboard</title>
    <link rel="stylesheet" href="theme.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .monitor-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        @media (max-width: 1000px) {
            .monitor-grid { grid-template-columns: 1fr; }
        }

        .chart-wrap { position: relative; height: 280px; }

        /* Live ticker */
        .ticker {
            max-height: 360px;
            overflow-y: auto;
        }

        .ticker-item {
            display: flex;
            gap: 12px;
            padding: 12px 14px;
            border-bottom: 1px solid var(--border);
            font-size: 12px;
            transition: background .15s;
        }

        .ticker-item:hover { background: var(--surface2); }
        .ticker-item:last-child { border-bottom: none; }

        .ticker-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }

        .ticker-dot.Auth     { background: var(--accent2); box-shadow: 0 0 6px rgba(59,130,246,.5); }
        .ticker-dot.Create   { background: var(--accent);  box-shadow: 0 0 6px rgba(0,212,170,.5); }
        .ticker-dot.Update   { background: var(--accent3); box-shadow: 0 0 6px rgba(245,158,11,.5); }
        .ticker-dot.Delete   { background: var(--danger);  box-shadow: 0 0 6px rgba(239,68,68,.5); }
        .ticker-dot.Security { background: var(--danger);  box-shadow: 0 0 8px rgba(239,68,68,.7); animation: pulse 1.2s infinite; }
        .ticker-dot.View     { background: var(--muted2); }
        .ticker-dot.Network  { background: var(--cyan);    box-shadow: 0 0 6px rgba(34,211,238,.5); }
        .ticker-dot.System   { background: var(--purple);  box-shadow: 0 0 6px rgba(167,139,250,.5); }

        .ticker-content { flex: 1; min-width: 0; }
        .ticker-action { color: var(--text); font-weight: 500; }
        .ticker-meta {
            color: var(--muted);
            font-family: var(--mono);
            font-size: 10px;
            margin-top: 3px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .ticker-meta .ip { color: var(--accent); }
        .ticker-meta .user { color: var(--accent2); }

        /* Top IPs */
        .ip-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .ip-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 12px;
        }

        .ip-row .ip-addr {
            font-family: var(--mono);
            color: var(--accent);
            font-weight: 600;
            min-width: 110px;
        }

        .ip-row .ip-bar {
            flex: 1;
            height: 6px;
            background: var(--surface3);
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }

        .ip-row .ip-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent), var(--accent2));
            transition: width .6s ease;
        }

        .ip-row .ip-hits {
            font-family: var(--mono);
            color: var(--muted2);
            min-width: 50px;
            text-align: right;
        }

        .auto-refresh {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: var(--muted);
            font-family: var(--mono);
        }

        .auto-refresh-toggle {
            position: relative;
            width: 32px; height: 18px;
            background: var(--surface3);
            border-radius: 9px;
            cursor: pointer;
            transition: background .2s;
        }
        .auto-refresh-toggle.on { background: var(--accent); }
        .auto-refresh-toggle::after {
            content: '';
            position: absolute;
            top: 2px; left: 2px;
            width: 14px; height: 14px;
            background: var(--text);
            border-radius: 50%;
            transition: left .2s;
        }
        .auto-refresh-toggle.on::after { left: 16px; background: var(--bg); }
    </style>
</head>
<body>

<?php include '_navbar.php'; ?>

<main class="page">

    <div class="page-header fade-in">
        <div class="page-title-group">
            <div>
                <div class="page-title">◍ Network Monitor</div>
                <div class="page-sub">Real-time activity tracking, traffic analysis, and security alerts</div>
            </div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <div class="auto-refresh">
                AUTO-REFRESH
                <div class="auto-refresh-toggle on" id="refreshToggle"></div>
            </div>
            <div class="live-badge"><span class="pulse"></span> LIVE</div>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid fade-in" id="netStats">
        <div class="stat"><div class="stat-icon">📊</div><div class="stat-num" id="stat-total">—</div><div class="stat-label">Total Events</div></div>
        <div class="stat"><div class="stat-icon">⏱️</div><div class="stat-num" id="stat-24h">—</div><div class="stat-label">Last 24 Hours</div></div>
        <div class="stat"><div class="stat-icon">📡</div><div class="stat-num" id="stat-ips">—</div><div class="stat-label">Unique IPs</div></div>
        <div class="stat"><div class="stat-icon">⚠️</div><div class="stat-num" id="stat-threats">—</div><div class="stat-label">Security Events (24h)</div></div>
    </div>

    <!-- Timeline + Ticker -->
    <div class="monitor-grid">
        <!-- Timeline chart -->
        <div class="card fade-in">
            <div class="card-header">
                <span>📈</span>
                <h2>Activity Timeline</h2>
                <span class="card-tag">LAST 24 HOURS</span>
            </div>
            <div class="chart-wrap">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>

        <!-- Live ticker -->
        <div class="card fade-in">
            <div class="card-header">
                <span>🔴</span>
                <h2>Live Activity</h2>
                <span class="card-tag" id="lastUpdate">—</span>
            </div>
            <div class="ticker" id="ticker">
                <div class="ticker-item"><div class="ticker-content" style="color:var(--muted)">Loading…</div></div>
            </div>
        </div>
    </div>

    <!-- Category breakdown + Top IPs -->
    <div class="monitor-grid">
        <!-- Category pie -->
        <div class="card fade-in">
            <div class="card-header">
                <span>📊</span>
                <h2>Activity by Category</h2>
                <span class="card-tag">DISTRIBUTION</span>
            </div>
            <div class="chart-wrap">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Top IPs -->
        <div class="card fade-in">
            <div class="card-header">
                <span>🌐</span>
                <h2>Top Active IPs</h2>
                <span class="card-tag">LAST 24H</span>
            </div>
            <div class="ip-list" id="ipList">
                <div class="ip-row" style="color:var(--muted);justify-content:center;">Loading…</div>
            </div>
        </div>
    </div>

</main>

<script>
let timelineChart, categoryChart;
let autoRefresh = true;
const refreshInterval = 5000; // 5 seconds

document.getElementById('refreshToggle').addEventListener('click', () => {
    autoRefresh = !autoRefresh;
    document.getElementById('refreshToggle').classList.toggle('on', autoRefresh);
});

/* ── COMMON CHART OPTIONS ── */
Chart.defaults.font.family = "'Outfit', sans-serif";
Chart.defaults.color = '#9ca3af';
Chart.defaults.borderColor = '#1f2937';

function fmtTime(ts) {
    const d = new Date(ts.replace(' ', 'T'));
    const diff = (Date.now() - d.getTime()) / 1000;
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return d.toLocaleDateString();
}

/* ── LOAD STATS ── */
async function loadStats() {
    try {
        const res = await fetch('api.php?action=getNetworkStats');
        const data = await res.json();
        if (!data.success) return;
        document.getElementById('stat-total').textContent   = data.stats.totalEvents.toLocaleString();
        document.getElementById('stat-24h').textContent     = data.stats.last24h.toLocaleString();
        document.getElementById('stat-ips').textContent     = data.stats.uniqueIPs;
        document.getElementById('stat-threats').textContent = data.stats.threats;
    } catch (e) { console.error(e); }
}

/* ── TIMELINE CHART ── */
async function loadTimeline() {
    try {
        const res = await fetch('api.php?action=getActivityTimeline');
        const data = await res.json();
        if (!data.success) return;
        const labels = data.data.map(d => d.hour);
        const counts = data.data.map(d => parseInt(d.count));

        if (!timelineChart) {
            timelineChart = new Chart(document.getElementById('timelineChart'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Events',
                        data: counts,
                        borderColor: '#00d4aa',
                        backgroundColor: 'rgba(0,212,170,0.1)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#00d4aa',
                        pointBorderColor: '#06080f',
                        pointBorderWidth: 2,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: '#0d1117', borderColor: '#1f2937', borderWidth: 1, padding: 10, titleColor: '#f9fafb', bodyColor: '#9ca3af' }
                    },
                    scales: {
                        x: { grid: { color: '#1f2937' }, ticks: { font: { size: 10 } } },
                        y: { grid: { color: '#1f2937' }, ticks: { font: { size: 10 }, precision: 0 }, beginAtZero: true }
                    }
                }
            });
        } else {
            timelineChart.data.labels = labels;
            timelineChart.data.datasets[0].data = counts;
            timelineChart.update('none');
        }
    } catch (e) { console.error(e); }
}

/* ── CATEGORY CHART ── */
const catColors = {
    Auth:     '#3b82f6',
    Create:   '#00d4aa',
    Update:   '#f59e0b',
    Delete:   '#ef4444',
    View:     '#9ca3af',
    Network:  '#22d3ee',
    Security: '#ec4899',
    System:   '#a78bfa',
    General:  '#6b7280'
};

async function loadCategoryChart() {
    try {
        const res = await fetch('api.php?action=getCategoryBreakdown');
        const data = await res.json();
        if (!data.success) return;
        const labels = data.data.map(d => d.Category);
        const counts = data.data.map(d => parseInt(d.count));
        const colors = labels.map(l => catColors[l] || '#6b7280');

        if (!categoryChart) {
            categoryChart = new Chart(document.getElementById('categoryChart'), {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data: counts,
                        backgroundColor: colors,
                        borderColor: '#06080f',
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { padding: 12, font: { size: 11 }, boxWidth: 12, boxHeight: 12 }
                        },
                        tooltip: { backgroundColor: '#0d1117', borderColor: '#1f2937', borderWidth: 1, padding: 10 }
                    }
                }
            });
        } else {
            categoryChart.data.labels = labels;
            categoryChart.data.datasets[0].data = counts;
            categoryChart.data.datasets[0].backgroundColor = colors;
            categoryChart.update('none');
        }
    } catch (e) { console.error(e); }
}

/* ── LIVE TICKER ── */
async function loadTicker() {
    try {
        const res = await fetch('api.php?action=getRecentActivity');
        const data = await res.json();
        if (!data.success) return;
        const html = data.logs.map(l => `
            <div class="ticker-item">
                <div class="ticker-dot ${l.Category}"></div>
                <div class="ticker-content">
                    <div class="ticker-action">${l.Action}</div>
                    <div class="ticker-meta">
                        <span class="user">${l.Username}</span>
                        <span class="ip">${l.IPAddress || '—'}</span>
                        <span>${fmtTime(l.CreatedAt)}</span>
                    </div>
                </div>
            </div>
        `).join('');
        document.getElementById('ticker').innerHTML = html || '<div class="ticker-item"><div class="ticker-content" style="color:var(--muted)">No recent activity</div></div>';
        document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
    } catch (e) { console.error(e); }
}

/* ── TOP IPs ── */
async function loadTopIPs() {
    try {
        const res = await fetch('api.php?action=getTopIPs');
        const data = await res.json();
        if (!data.success) return;
        if (!data.data.length) {
            document.getElementById('ipList').innerHTML = '<div class="ip-row" style="color:var(--muted);justify-content:center;">No activity yet</div>';
            return;
        }
        const max = Math.max(...data.data.map(d => parseInt(d.hits)));
        document.getElementById('ipList').innerHTML = data.data.map(d => {
            const pct = (parseInt(d.hits) / max) * 100;
            return `<div class="ip-row">
                <span class="ip-addr">${d.IPAddress}</span>
                <div class="ip-bar"><div class="ip-bar-fill" style="width:${pct}%"></div></div>
                <span class="ip-hits">${d.hits} hits</span>
            </div>`;
        }).join('');
    } catch (e) { console.error(e); }
}

/* ── REFRESH ALL ── */
function refreshAll() {
    loadStats();
    loadTimeline();
    loadCategoryChart();
    loadTicker();
    loadTopIPs();
}

refreshAll();
setInterval(() => { if (autoRefresh) refreshAll(); }, refreshInterval);
</script>

</body>
</html>
