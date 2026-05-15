<?php
include 'auth_check.php';
include 'config.php';
$activeTab = 'portscan';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Port Scanner — OSI Inspector</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .scan-row { display: flex; gap: 10px; align-items: end; }
        .scan-row .field { flex: 1; }

        .port-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 8px;
            margin-top: 14px;
        }
        .port-cell {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 10px 12px;
            background: var(--bg-elevated);
            transition: all .15s;
        }
        .port-cell.open    { border-color: var(--success); background: var(--success-bg); }
        .port-cell.closed  { opacity: .5; }
        .port-cell.filtered { border-color: var(--warning); background: var(--warning-bg); }

        .port-num {
            font-family: var(--mono);
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .port-cell.open .port-num { color: var(--success); }
        .port-cell.filtered .port-num { color: var(--warning); }

        .port-svc {
            font-size: 11px;
            color: var(--text-muted);
            font-family: var(--mono);
        }

        .port-status {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-top: 4px;
        }

        .scanning {
            position: relative;
            overflow: hidden;
        }
        .scanning::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59,130,246,.15), transparent);
            animation: scan 1.4s linear infinite;
        }
        @keyframes scan { to { left: 100%; } }
    </style>
</head>
<body>

<div class="app-shell">
    <?php include '_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Port Scanner</div>
                <div class="topbar-sub">Scan common ports on a target host (simulated)</div>
            </div>
        </div>

        <div class="content">

            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">⊕</div>
                    <div class="panel-title">Target</div>
                </div>
                <div class="panel-body">
                    <div class="scan-row">
                        <div class="field">
                            <label class="field-label">Target IP / Hostname</label>
                            <input class="input" id="target" value="192.168.1.10">
                        </div>
                        <button class="btn btn-primary btn-lg" onclick="runScan()">🔍 Scan Ports</button>
                    </div>
                </div>
            </div>

            <div class="panel fade-in" id="resultsPanel" style="display:none;">
                <div class="panel-head">
                    <div class="panel-icon">📊</div>
                    <div class="panel-title">Scan Results</div>
                    <span class="panel-tag" id="resultsTag">—</span>
                </div>
                <div class="panel-body">
                    <div class="port-grid" id="portGrid"></div>
                </div>
            </div>

            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">ⓘ</div>
                    <div class="panel-title">Common Ports Reference</div>
                </div>
                <div class="panel-body" style="font-size:13px;line-height:1.8;color:var(--text-muted);">
                    Port scanners check whether specific TCP/UDP ports are <span class="tag tag-success">open</span> (accepting connections),
                    <span class="tag tag-warning">filtered</span> (firewall blocking),
                    or <span class="tag">closed</span> (no service listening).
                    This is essential for <strong>network audits</strong> and <strong>security assessments</strong>.
                </div>
            </div>

        </div>
    </main>
</div>

<script>
async function runScan() {
    const target = document.getElementById('target').value;
    document.getElementById('resultsPanel').style.display = 'block';
    document.getElementById('resultsTag').textContent = `Scanning ${target}...`;

    const grid = document.getElementById('portGrid');
    grid.innerHTML = '<div class="port-cell scanning" style="grid-column:1/-1;text-align:center;padding:20px;color:var(--text-muted);">⚡ Scanning ports on ' + target + '...</div>';

    const fd = new FormData();
    fd.append('action', 'portScan');
    fd.append('target', target);

    await new Promise(r => setTimeout(r, 1200));

    try {
        const res = await fetch('api.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.success) return;

        const openCount = data.results.filter(r => r.status === 'open').length;
        document.getElementById('resultsTag').textContent = `${openCount} open / ${data.results.length} scanned`;

        grid.innerHTML = data.results.map(r => `
            <div class="port-cell ${r.status}">
                <div class="port-num">:${r.port}</div>
                <div class="port-svc">${r.service}</div>
                <div class="port-status">${r.status === 'open' ? '✓ ' + r.status : '✗ ' + r.status}</div>
                <div class="text-xs muted" style="font-family:var(--mono);margin-top:2px;">${r.latency}</div>
            </div>
        `).join('');
    } catch (e) { console.error(e); }
}
</script>

</body>
</html>
