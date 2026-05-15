<?php
include 'auth_check.php';
include 'config.php';
$activeTab = 'history';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capture History — OSI Inspector</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .filter-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            padding: 12px 16px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            margin-bottom: 14px;
        }
        .filter-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            font-family: var(--mono);
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--bg-sidebar);
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            transition: all .12s;
            font-family: var(--mono);
        }
        .pill:hover { background: var(--bg-hover); }
        .pill.active {
            background: var(--primary-bg);
            border-color: transparent;
            color: var(--primary);
            font-weight: 600;
        }

        .search-box {
            margin-left: auto;
            position: relative;
        }
        .search-box .input { padding-left: 32px; min-width: 220px; }
        .search-box::before {
            content: '🔍';
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            opacity: .5;
            font-size: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }
        .empty-state-icon {
            font-size: 40px;
            opacity: .3;
            margin-bottom: 14px;
        }

        /* Wireshark-style row colors */
        tr[data-proto="HTTP"]   { background: rgba(239,68,68,.04); }
        tr[data-proto="HTTPS"]  { background: rgba(249,115,22,.04); }
        tr[data-proto="DNS"]    { background: rgba(234,179,8,.04); }
        tr[data-proto="ICMP"]   { background: rgba(168,85,247,.04); }
        tr[data-proto="SSH"]    { background: rgba(34,197,94,.04); }
        tr[data-proto="FTP"]    { background: rgba(6,182,212,.04); }
        tr[data-proto="SMTP"]   { background: rgba(59,130,246,.04); }

        .proto-tag {
            font-family: var(--mono);
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 700;
        }
        .pt-HTTP   { background: var(--L7-bg); color: var(--L7); }
        .pt-HTTPS  { background: var(--L6-bg); color: var(--L6); }
        .pt-DNS    { background: var(--L5-bg); color: var(--L5); }
        .pt-FTP    { background: var(--L3-bg); color: var(--L3); }
        .pt-SSH    { background: var(--L4-bg); color: var(--L4); }
        .pt-SMTP   { background: var(--L2-bg); color: var(--L2); }
        .pt-ICMP   { background: var(--L1-bg); color: var(--L1); }
        .pt-POP3, .pt-IMAP, .pt-Telnet { background: var(--bg-hover); color: var(--text-muted); }
    </style>
</head>
<body>

<div class="app-shell">
    <?php include '_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Capture History</div>
                <div class="topbar-sub">All recorded packets · click row for details</div>
            </div>
            <div class="topbar-actions">
                <button class="btn" onclick="exportCSV()">↓ Export CSV</button>
                <button class="btn btn-danger" onclick="clearAll()">🗑 Clear All</button>
            </div>
        </div>

        <div class="content">

            <!-- Filters -->
            <div class="filter-row fade-in">
                <span class="filter-label">FILTER:</span>
                <button class="pill active" data-p="">All</button>
                <button class="pill" data-p="HTTP">HTTP</button>
                <button class="pill" data-p="HTTPS">HTTPS</button>
                <button class="pill" data-p="DNS">DNS</button>
                <button class="pill" data-p="FTP">FTP</button>
                <button class="pill" data-p="SSH">SSH</button>
                <button class="pill" data-p="SMTP">SMTP</button>
                <button class="pill" data-p="ICMP">ICMP</button>

                <div class="search-box">
                    <input type="text" class="input" id="search" placeholder="Search IP, URL...">
                </div>
            </div>

            <!-- Packets table -->
            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">📋</div>
                    <div class="panel-title">All Packets</div>
                    <span class="panel-tag" id="count">— ENTRIES</span>
                </div>
                <div class="table-wrap" style="border-radius:0;border:none;border-top:1px solid var(--border);">
                    <table class="table" id="pktTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Time</th>
                                <th>Protocol</th>
                                <th>Method</th>
                                <th>Source</th>
                                <th></th>
                                <th>Destination</th>
                                <th>Length</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="10" class="text-center muted" style="padding:24px;">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
let allPackets = [];
let filterProto = '';

function fmtTime(ts) {
    const d = new Date(ts.replace(' ', 'T'));
    const diff = (Date.now() - d.getTime()) / 1000;
    if (diff < 60)    return 'just now';
    if (diff < 3600)  return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return d.toLocaleDateString();
}

async function loadPackets() {
    try {
        const res = await fetch('api.php?action=getPackets&limit=200');
        const data = await res.json();
        if (data.success) { allPackets = data.packets; render(); }
    } catch (e) { console.error(e); }
}

function render() {
    let list = allPackets;
    const q = document.getElementById('search').value.toLowerCase().trim();
    if (filterProto) list = list.filter(p => p.AppProtocol === filterProto);
    if (q) list = list.filter(p =>
        (p.SourceIP || '').toLowerCase().includes(q) ||
        (p.DestIP   || '').toLowerCase().includes(q) ||
        (p.UrlPath  || '').toLowerCase().includes(q)
    );

    document.getElementById('count').textContent = `${list.length} ENTRIES`;

    if (!list.length) {
        document.querySelector('#pktTable tbody').innerHTML = `
            <tr><td colspan="10">
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <div>No packets match your filters</div>
                </div>
            </td></tr>`;
        return;
    }

    document.querySelector('#pktTable tbody').innerHTML = list.map(p => `
        <tr data-proto="${p.AppProtocol}" onclick="location='inspector.php?id=${p.PacketID}'" style="cursor:pointer;">
            <td class="muted font-mono text-xs">#${p.PacketID}</td>
            <td class="font-mono text-xs muted">${fmtTime(p.CapturedAt)}</td>
            <td><span class="proto-tag pt-${p.AppProtocol}">${p.AppProtocol}</span></td>
            <td class="font-mono text-xs">${p.HttpMethod}</td>
            <td class="font-mono text-xs">${p.SourceIP}<span class="muted">:${p.SourcePort}</span></td>
            <td class="muted">→</td>
            <td class="font-mono text-xs">${p.DestIP}<span class="muted">:${p.DestPort}</span></td>
            <td class="font-mono text-xs">${p.PacketSize}B</td>
            <td><span class="tag tag-success">${p.Status}</span></td>
            <td><a href="inspector.php?id=${p.PacketID}" style="color:var(--primary);text-decoration:none;font-size:12px;">view →</a></td>
        </tr>
    `).join('');
}

document.querySelectorAll('.pill').forEach(p => {
    p.addEventListener('click', () => {
        document.querySelectorAll('.pill').forEach(x => x.classList.remove('active'));
        p.classList.add('active');
        filterProto = p.dataset.p;
        render();
    });
});

document.getElementById('search').addEventListener('input', render);

async function clearAll() {
    if (!confirm('Delete ALL captured packets? This cannot be undone.')) return;
    const fd = new FormData();
    fd.append('action', 'clearAll');
    await fetch('api.php', { method: 'POST', body: fd });
    loadPackets();
}

function exportCSV() {
    const headers = ['ID','Time','Protocol','Method','SourceIP','SourcePort','DestIP','DestPort','Size','Status','URL'];
    const rows = allPackets.map(p => [
        p.PacketID, p.CapturedAt, p.AppProtocol, p.HttpMethod,
        p.SourceIP, p.SourcePort, p.DestIP, p.DestPort,
        p.PacketSize, p.Status, p.UrlPath
    ]);
    const csv = [headers, ...rows].map(r => r.map(v => `"${v}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `packets_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

loadPackets();
setInterval(loadPackets, 8000);
</script>

</body>
</html>
