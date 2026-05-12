<?php
include 'auth_check.php';
include 'config.php';
$activeTab = 'ping';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ping & Traceroute — OSI Inspector</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .term {
            background: var(--bg-code);
            color: #e2e8f0;
            border-radius: var(--radius);
            padding: 16px;
            font-family: var(--mono);
            font-size: 12px;
            line-height: 1.7;
            min-height: 280px;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        .term .ok   { color: #5eead4; }
        .term .info { color: #93c5fd; }
        .term .warn { color: #fde047; }
        .term .err  { color: #fca5a5; }
        .term .muted { color: #64748b; }

        .input-row { display: flex; gap: 10px; align-items: end; }
        .input-row .field { flex: 1; }

        .hop-bar {
            display: inline-block;
            height: 4px;
            border-radius: 2px;
            background: linear-gradient(90deg, #22c55e, #eab308, #ef4444);
            margin-left: 8px;
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
            margin-top: 14px;
        }
        .stat-block {
            background: var(--bg-sidebar);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 10px 14px;
            text-align: center;
        }
        .stat-block .label {
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 4px;
        }
        .stat-block .value {
            font-family: var(--mono);
            font-weight: 700;
            font-size: 18px;
        }
    </style>
</head>
<body>

<div class="app-shell">
    <?php include '_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Ping & Traceroute</div>
                <div class="topbar-sub">Test reachability and trace network paths (simulated)</div>
            </div>
        </div>

        <div class="content">

            <!-- Input -->
            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">⚡</div>
                    <div class="panel-title">Target Configuration</div>
                </div>
                <div class="panel-body">
                    <div class="input-row">
                        <div class="field">
                            <label class="field-label">Target IP / Hostname</label>
                            <input class="input" id="target" value="8.8.8.8">
                        </div>
                        <div class="field" style="flex:0 0 120px;">
                            <label class="field-label">Count</label>
                            <select class="select" id="count">
                                <option>4</option><option>6</option><option>8</option><option>10</option>
                            </select>
                        </div>
                        <button class="btn btn-primary btn-lg" onclick="runPing()">📡 Ping</button>
                        <button class="btn btn-success btn-lg" onclick="runTrace()">🗺 Traceroute</button>
                    </div>
                </div>
            </div>

            <!-- Output -->
            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">▶</div>
                    <div class="panel-title">Output</div>
                    <span class="panel-tag" id="modeTag">READY</span>
                </div>
                <div class="panel-body">
                    <div class="term" id="term">$ <span class="muted">// Click Ping or Traceroute to begin...</span></div>
                    <div class="stats-summary" id="summary" style="display:none;">
                        <div class="stat-block"><div class="label">Sent</div><div class="value" id="s-sent">—</div></div>
                        <div class="stat-block"><div class="label">Received</div><div class="value" id="s-recv">—</div></div>
                        <div class="stat-block"><div class="label">Loss</div><div class="value" id="s-loss">—</div></div>
                        <div class="stat-block"><div class="label">Min RTT</div><div class="value" id="s-min">—</div></div>
                        <div class="stat-block"><div class="label">Avg RTT</div><div class="value" id="s-avg">—</div></div>
                        <div class="stat-block"><div class="label">Max RTT</div><div class="value" id="s-max">—</div></div>
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">ⓘ</div>
                    <div class="panel-title">About these tools</div>
                </div>
                <div class="panel-body" style="font-size:13px;line-height:1.7;color:var(--text-muted);">
                    <p style="margin-bottom:10px;"><strong>Ping</strong> — Sends ICMP Echo Request packets to test if a host is reachable and measures Round-Trip Time (RTT). Operates at <span class="tag tag-L3">Layer 3</span>.</p>
                    <p><strong>Traceroute</strong> — Discovers the path packets take through the network by exploiting TTL. Each router decrements TTL by 1 — when it reaches 0, the router replies with ICMP "Time Exceeded", revealing its IP.</p>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
const term = document.getElementById('term');
function termWrite(html) { term.innerHTML += html; }
function termClear() { term.innerHTML = ''; }
const sleep = ms => new Promise(r => setTimeout(r, ms));

async function runPing() {
    termClear();
    document.getElementById('modeTag').textContent = 'PING';
    document.getElementById('summary').style.display = 'none';
    const target = document.getElementById('target').value;
    const count  = document.getElementById('count').value;

    termWrite(`<span class="info">$ ping ${target} -c ${count}</span>\n`);
    termWrite(`<span class="muted">PING ${target} 56(84) bytes of data.</span>\n\n`);

    const fd = new FormData();
    fd.append('action', 'pingSimulate');
    fd.append('target', target);
    fd.append('count', count);
    const data = await (await fetch('api.php', { method: 'POST', body: fd })).json();
    if (!data.success) return;

    let recv = 0, sent = data.pings.length, rtts = [];

    for (const p of data.pings) {
        await sleep(700);
        if (p.success) {
            termWrite(`<span class="ok">64 bytes from ${target}:</span> icmp_seq=${p.seq} ttl=${p.ttl} time=${p.rtt} ms\n`);
            recv++;
            rtts.push(p.rtt);
        } else {
            termWrite(`<span class="err">Request timeout for icmp_seq=${p.seq}</span>\n`);
        }
    }

    const loss = ((sent - recv) / sent * 100).toFixed(0);
    const minR = rtts.length ? Math.min(...rtts).toFixed(2) : 0;
    const maxR = rtts.length ? Math.max(...rtts).toFixed(2) : 0;
    const avgR = rtts.length ? (rtts.reduce((a,b)=>a+b,0)/rtts.length).toFixed(2) : 0;

    termWrite(`\n<span class="warn">--- ${target} ping statistics ---</span>\n`);
    termWrite(`${sent} packets transmitted, ${recv} received, ${loss}% packet loss\n`);
    termWrite(`<span class="info">rtt min/avg/max = ${minR}/${avgR}/${maxR} ms</span>\n`);

    document.getElementById('summary').style.display = 'grid';
    document.getElementById('s-sent').textContent = sent;
    document.getElementById('s-recv').textContent = recv;
    document.getElementById('s-loss').textContent = loss + '%';
    document.getElementById('s-min').textContent = minR + 'ms';
    document.getElementById('s-avg').textContent = avgR + 'ms';
    document.getElementById('s-max').textContent = maxR + 'ms';
}

async function runTrace() {
    termClear();
    document.getElementById('modeTag').textContent = 'TRACEROUTE';
    document.getElementById('summary').style.display = 'none';
    const target = document.getElementById('target').value;

    termWrite(`<span class="info">$ traceroute ${target}</span>\n`);
    termWrite(`<span class="muted">traceroute to ${target}, 30 hops max, 60 byte packets</span>\n\n`);

    const fd = new FormData();
    fd.append('action', 'pingSimulate');
    fd.append('target', target);
    const data = await (await fetch('api.php', { method: 'POST', body: fd })).json();
    if (!data.success) return;

    for (const h of data.route) {
        await sleep(800);
        const pad = String(h.hop).padStart(2);
        termWrite(`<span class="warn">${pad}</span>  <span class="ok">${h.ip.padEnd(18)}</span>  ${h.rtt.toFixed(2)} ms\n`);
    }
    termWrite(`\n<span class="info">Trace complete.</span>\n`);
}
</script>

</body>
</html>
