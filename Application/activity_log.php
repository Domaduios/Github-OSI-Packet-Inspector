<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';
include 'logger.php';
$activeTab = 'logs';

logActivity($conn, 'Viewed activity log', 'View', null, null, 'Activity log accessed', 200);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log — Audit Trail</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .filter-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            padding: 16px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            align-items: center;
        }

        .filter-bar .filter-label {
            font-size: 11px;
            color: var(--muted);
            font-family: var(--mono);
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-right: 6px;
        }

        .cat-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--muted2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            transition: all .15s;
        }

        .cat-pill:hover { border-color: var(--border2); color: var(--text); }
        .cat-pill.active {
            background: rgba(0,212,170,.1);
            border-color: var(--accent);
            color: var(--accent);
        }

        .cat-pill .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--muted);
        }
        .cat-pill[data-cat="Auth"] .dot     { background: #3b82f6; }
        .cat-pill[data-cat="Create"] .dot   { background: #00d4aa; }
        .cat-pill[data-cat="Update"] .dot   { background: #f59e0b; }
        .cat-pill[data-cat="Delete"] .dot   { background: #ef4444; }
        .cat-pill[data-cat="View"] .dot     { background: #9ca3af; }
        .cat-pill[data-cat="Network"] .dot  { background: #22d3ee; }
        .cat-pill[data-cat="Security"] .dot { background: #ec4899; }
        .cat-pill[data-cat="System"] .dot   { background: #a78bfa; }

        .search-input {
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 8px;
            padding: 7px 14px;
            font-family: var(--sans);
            font-size: 12px;
            min-width: 200px;
            margin-left: auto;
        }
        .search-input:focus { outline: none; border-color: var(--accent); }

        /* Status code colors */
        .status-200, .status-201 { color: var(--success); }
        .status-400, .status-401, .status-403, .status-409 { color: var(--accent3); }
        .status-500 { color: var(--danger); }

        .method-tag {
            font-family: var(--mono);
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: 700;
        }
        .method-GET    { background: rgba(59,130,246,.12); color: var(--accent2); }
        .method-POST   { background: rgba(0,212,170,.12); color: var(--accent); }
        .method-PUT    { background: rgba(245,158,11,.12); color: var(--accent3); }
        .method-DELETE { background: rgba(239,68,68,.12); color: var(--danger); }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
        }
        .empty-state-icon { font-size: 36px; margin-bottom: 12px; opacity: .3; }
    </style>
</head>
<body>

<?php include '_navbar.php'; ?>

<main class="page">

    <div class="page-header fade-in">
        <div class="page-title-group">
            <div>
                <div class="page-title">⟐ Activity Log</div>
                <div class="page-sub">Complete audit trail — every action, every IP, every timestamp</div>
            </div>
        </div>
        <button class="btn btn-secondary" onclick="exportCSV()">↓ Export CSV</button>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar fade-in">
        <span class="filter-label">FILTER:</span>
        <button class="cat-pill active" data-cat=""><span class="dot"></span>All</button>
        <button class="cat-pill" data-cat="Auth"><span class="dot"></span>Auth</button>
        <button class="cat-pill" data-cat="Create"><span class="dot"></span>Create</button>
        <button class="cat-pill" data-cat="Update"><span class="dot"></span>Update</button>
        <button class="cat-pill" data-cat="Delete"><span class="dot"></span>Delete</button>
        <button class="cat-pill" data-cat="View"><span class="dot"></span>View</button>
        <button class="cat-pill" data-cat="Network"><span class="dot"></span>Network</button>
        <button class="cat-pill" data-cat="Security"><span class="dot"></span>Security</button>
        <button class="cat-pill" data-cat="System"><span class="dot"></span>System</button>
        <input type="text" id="searchBox" class="search-input" placeholder="Search action, user, IP…">
    </div>

    <!-- Log Table -->
    <div class="card fade-in">
        <div class="card-header">
            <span>📋</span>
            <h2>Recent Activity</h2>
            <span class="card-tag" id="resultCount">— ENTRIES</span>
        </div>
        <div class="table-wrap">
            <table class="table" id="logTable">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Category</th>
                        <th>Target</th>
                        <th>IP Address</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="10" class="empty">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
let currentLogs = [];
let activeCategory = '';

const catBadgeMap = {
    Auth:     'badge-blue',
    Create:   'badge-accent',
    Update:   'badge-amber',
    Delete:   'badge-danger',
    View:     'badge-muted',
    Network:  'badge-cyan',
    Security: 'badge-danger',
    System:   'badge-purple',
    General:  'badge-muted'
};

const roleBadgeMap = {
    Admin:     'badge-purple',
    Staff:     'badge-cyan',
    Teacher:   'badge-success',
    Registrar: 'badge-amber',
    Guest:     'badge-muted',
    Student:   'badge-blue'
};

function fmtDate(ts) {
    const d = new Date(ts.replace(' ', 'T'));
    const diff = (Date.now() - d.getTime()) / 1000;
    if (diff < 60)    return 'just now';
    if (diff < 3600)  return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return d.toLocaleString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

async function loadLogs() {
    const url = activeCategory
        ? `api.php?action=getActivityLog&limit=200&category=${activeCategory}`
        : 'api.php?action=getActivityLog&limit=200';

    try {
        const res = await fetch(url);
        const data = await res.json();
        if (!data.success) {
            document.querySelector('#logTable tbody').innerHTML = '<tr><td colspan="10" class="empty">Failed to load logs</td></tr>';
            return;
        }
        currentLogs = data.logs;
        renderLogs(currentLogs);
    } catch (e) {
        console.error(e);
    }
}

function renderLogs(logs) {
    document.getElementById('resultCount').textContent = `${logs.length} ENTRIES`;
    if (!logs.length) {
        document.querySelector('#logTable tbody').innerHTML = `
            <tr><td colspan="10">
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <div>No activity logs match your filters</div>
                </div>
            </td></tr>
        `;
        return;
    }

    document.querySelector('#logTable tbody').innerHTML = logs.map(l => {
        const catCls  = catBadgeMap[l.Category] || 'badge-muted';
        const roleCls = roleBadgeMap[l.UserRole] || 'badge-muted';
        const target  = l.TargetType ? `${l.TargetType} #${l.TargetID || '—'}` : '—';
        return `<tr>
            <td><span class="chip-mono">${fmtDate(l.CreatedAt)}</span></td>
            <td><strong>${l.Username}</strong></td>
            <td><span class="badge ${roleCls}">${l.UserRole}</span></td>
            <td>${l.Action}</td>
            <td><span class="badge ${catCls}">${l.Category}</span></td>
            <td style="font-size:11px;color:var(--muted2);">${target}</td>
            <td>${l.IPAddress ? `<span class="badge badge-accent">${l.IPAddress}</span>` : '<span style="color:var(--muted)">—</span>'}</td>
            <td><span class="method-tag method-${l.Method}">${l.Method || '—'}</span></td>
            <td class="status-${l.StatusCode}" style="font-family:var(--mono);font-weight:700;">${l.StatusCode}</td>
            <td style="font-size:11px;color:var(--muted2);max-width:280px;">${l.Details || '—'}</td>
        </tr>`;
    }).join('');
}

/* ── FILTER PILLS ── */
document.querySelectorAll('.cat-pill').forEach(p => {
    p.addEventListener('click', () => {
        document.querySelectorAll('.cat-pill').forEach(x => x.classList.remove('active'));
        p.classList.add('active');
        activeCategory = p.dataset.cat;
        loadLogs();
    });
});

/* ── SEARCH ── */
document.getElementById('searchBox').addEventListener('input', e => {
    const q = e.target.value.toLowerCase().trim();
    if (!q) { renderLogs(currentLogs); return; }
    const filtered = currentLogs.filter(l =>
        (l.Action || '').toLowerCase().includes(q) ||
        (l.Username || '').toLowerCase().includes(q) ||
        (l.IPAddress || '').toLowerCase().includes(q) ||
        (l.Details || '').toLowerCase().includes(q)
    );
    renderLogs(filtered);
});

/* ── EXPORT CSV ── */
function exportCSV() {
    if (!currentLogs.length) return;
    const headers = ['Time','User','Role','Action','Category','TargetType','TargetID','IP','Method','Status','Details'];
    const rows = currentLogs.map(l => [
        l.CreatedAt, l.Username, l.UserRole, l.Action, l.Category,
        l.TargetType || '', l.TargetID || '', l.IPAddress || '',
        l.Method || '', l.StatusCode, (l.Details || '').replace(/"/g,'""')
    ]);
    const csv = [headers, ...rows].map(r => r.map(v => `"${v}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
    const url  = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `activity_log_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

loadLogs();
setInterval(loadLogs, 10000); // auto-refresh every 10s
</script>

</body>
</html>
