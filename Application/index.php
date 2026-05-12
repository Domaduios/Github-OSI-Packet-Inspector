<?php
include 'auth_check.php';
include 'config.php';
$activeTab = 'inspector';

$layers = [];
$r = $conn->query("SELECT * FROM OSILayers ORDER BY LayerNum DESC");
while ($row = $r->fetch_assoc()) $layers[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packet Inspector — OSI Inspector Pro</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .builder-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 14px; }
        .builder-actions { display: flex; gap: 8px; align-items: center; padding-top: 12px; border-top: 1px solid var(--border); }

        /* OSI Stack */
        .osi-section { display: grid; grid-template-columns: 1fr 80px 1fr; gap: 0; align-items: stretch; padding: 18px; }
        @media (max-width: 1100px) { .osi-section { grid-template-columns: 1fr; gap: 14px; } .osi-channel { display: none; } }

        .osi-side-label {
            text-align: center;
            font-size: 11px;
            color: var(--text-muted);
            font-family: var(--mono);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .osi-stack { display: flex; flex-direction: column; gap: 5px; }

        .osi-layer {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-left: 4px solid var(--border-strong);
            border-radius: var(--radius);
            padding: 11px 14px;
            transition: all .25s;
        }

        .osi-layer.active {
            background: var(--bg-active);
            border-color: currentColor;
            transform: translateX(3px);
            box-shadow: 0 0 0 3px color-mix(in srgb, currentColor 15%, transparent);
        }

        .osi-layer.passed {
            background: var(--bg-sidebar);
            opacity: .7;
        }

        .layer-num {
            width: 28px; height: 28px;
            border-radius: 6px;
            background: var(--bg-sidebar);
            border: 1px solid currentColor;
            display: grid;
            place-items: center;
            font-family: var(--mono);
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .layer-info { flex: 1; min-width: 0; }
        .layer-name { font-size: 13px; font-weight: 600; color: var(--text); }
        .layer-meta { font-size: 11px; color: var(--text-muted); font-family: var(--mono); margin-top: 1px; }
        .layer-data {
            font-family: var(--mono);
            font-size: 11px;
            color: currentColor;
            margin-top: 4px;
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: all .25s;
            font-weight: 600;
        }
        .osi-layer.active .layer-data,
        .osi-layer.passed .layer-data {
            opacity: 1;
            max-height: 60px;
        }

        [data-layer="7"] { color: var(--L7); }
        [data-layer="6"] { color: var(--L6); }
        [data-layer="5"] { color: var(--L5); }
        [data-layer="4"] { color: var(--L4); }
        [data-layer="3"] { color: var(--L3); }
        [data-layer="2"] { color: var(--L2); }
        [data-layer="1"] { color: var(--L1); }

        .osi-channel {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-top: 24px;
        }
        .channel-line {
            width: 2px;
            flex: 1;
            background: linear-gradient(to bottom, var(--L7), var(--L6), var(--L5), var(--L4), var(--L3), var(--L2), var(--L1));
            opacity: .25;
        }
        .channel-label {
            writing-mode: vertical-rl;
            font-size: 10px;
            font-family: var(--mono);
            color: var(--text-muted);
            letter-spacing: 1px;
            padding: 6px 0;
            font-weight: 600;
        }

        .packet-icon {
            position: absolute;
            top: 0; left: 50%;
            transform: translate(-50%, 0);
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 8px;
            display: grid;
            place-items: center;
            font-size: 16px;
            color: white;
            box-shadow: 0 4px 14px rgba(59,130,246,.4);
            transition: all .35s ease;
            opacity: 0;
            z-index: 10;
        }
        .packet-icon.show { opacity: 1; }

        /* Speed control */
        .speed-control {
            display: flex; align-items: center; gap: 8px;
            font-size: 11px; font-family: var(--mono); color: var(--text-muted); font-weight: 600;
        }
        .speed-control input[type="range"] {
            accent-color: var(--primary);
            width: 100px;
        }

        /* Phase indicator */
        .phase-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 4px;
            font-family: var(--mono);
            font-size: 11px;
            font-weight: 600;
            background: var(--bg-hover);
            color: var(--text-muted);
        }
        .phase-indicator.encap { background: var(--L7-bg); color: var(--L7); }
        .phase-indicator.transmit { background: var(--info-bg); color: var(--info); }
        .phase-indicator.decap { background: var(--L4-bg); color: var(--L4); }
        .phase-indicator.done { background: var(--success-bg); color: var(--success); }

        /* Auto-capture toggle */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 36px; height: 20px;
        }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: var(--border-strong);
            border-radius: 20px;
            transition: .2s;
        }
        .toggle-slider::before {
            position: absolute;
            content: "";
            height: 14px; width: 14px;
            left: 3px; top: 3px;
            background: white;
            border-radius: 50%;
            transition: .2s;
            box-shadow: 0 1px 3px rgba(0,0,0,.2);
        }
        .toggle-switch input:checked + .toggle-slider { background: var(--primary); }
        .toggle-switch input:checked + .toggle-slider::before { transform: translateX(16px); }
    </style>
</head>
<body>

<div class="app-shell">
    <?php include '_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Packet Inspector</div>
                <div class="topbar-sub">Real-time OSI layer visualization</div>
            </div>
            <div class="topbar-actions">
                <span class="status-pill"><span class="status-dot"></span> READY</span>
            </div>
        </div>

        <div class="content">

            <!-- Stats -->
            <div class="stats fade-in">
                <div class="stat">
                    <div class="stat-label">📦 Total Packets</div>
                    <div class="stat-value" id="stat-total">—</div>
                </div>
                <div class="stat">
                    <div class="stat-label">⚡ Last Hour</div>
                    <div class="stat-value" id="stat-hour">—</div>
                </div>
                <div class="stat">
                    <div class="stat-label">🌐 Unique IPs</div>
                    <div class="stat-value" id="stat-ips">—</div>
                </div>
                <div class="stat">
                    <div class="stat-label">📊 Total Bytes</div>
                    <div class="stat-value" id="stat-bytes">—</div>
                </div>
                <div class="stat">
                    <div class="stat-label">⭐ Top Protocol</div>
                    <div class="stat-value" id="stat-top" style="font-size:18px;">—</div>
                </div>
            </div>

            <!-- Packet Builder -->
            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">⚡</div>
                    <div class="panel-title">Build & Send Packet</div>
                    <span class="panel-tag">SIMULATOR</span>
                </div>
                <div class="panel-body">
                    <div class="builder-grid">
                        <div class="field">
                            <label class="field-label">Protocol</label>
                            <select class="select" id="b-proto">
                                <option value="HTTP">HTTP (port 80)</option>
                                <option value="HTTPS">HTTPS (port 443)</option>
                                <option value="DNS">DNS (port 53)</option>
                                <option value="FTP">FTP (port 21)</option>
                                <option value="SSH">SSH (port 22)</option>
                                <option value="SMTP">SMTP (port 25)</option>
                                <option value="ICMP">ICMP (Ping)</option>
                                <option value="POP3">POP3 (port 110)</option>
                                <option value="IMAP">IMAP (port 143)</option>
                                <option value="Telnet">Telnet (port 23)</option>
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Method</label>
                            <select class="select" id="b-method">
                                <option>GET</option><option>POST</option><option>PUT</option>
                                <option>DELETE</option><option>QUERY</option><option>ECHO</option>
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Source IP</label>
                            <input class="input" id="b-src" value="192.168.10.45">
                        </div>
                        <div class="field">
                            <label class="field-label">Destination IP</label>
                            <input class="input" id="b-dest" value="142.250.190.46">
                        </div>
                        <div class="field" style="grid-column:1/-1;">
                            <label class="field-label">URL Path / Resource</label>
                            <input class="input" id="b-url" value="/api/users">
                        </div>
                    </div>

                    <div class="builder-actions">
                        <div class="speed-control">
                            ⏱ SPEED
                            <input type="range" id="speedRange" min="100" max="800" value="350" step="50">
                            <span id="speedVal">350ms</span>
                        </div>

                        <label class="speed-control" style="margin-left:14px;cursor:pointer;">
                            🔁 AUTO-CAPTURE
                            <span class="toggle-switch">
                                <input type="checkbox" id="autoCapture">
                                <span class="toggle-slider"></span>
                            </span>
                        </label>

                        <div style="margin-left:auto;display:flex;gap:8px;">
                            <button class="btn btn-sm" onclick="generateBatch()">+ Generate 5</button>
                            <button class="btn" onclick="resetAnimation()">↺ Reset</button>
                            <button class="btn btn-primary btn-lg" onclick="sendPacket()">▶ Send Packet</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OSI Visualization -->
            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">📡</div>
                    <div class="panel-title">OSI Encapsulation & Decapsulation</div>
                    <span class="phase-indicator" id="phaseLabel">IDLE</span>
                </div>

                <div class="osi-section">
                    <!-- SENDER -->
                    <div>
                        <div class="osi-side-label">📤 Sender — Encapsulating</div>
                        <div class="osi-stack" id="senderStack">
                            <?php foreach ($layers as $L): ?>
                                <div class="osi-layer" data-layer="<?php echo $L['LayerNum']; ?>" data-side="send" data-num="<?php echo $L['LayerNum']; ?>">
                                    <div class="layer-num">L<?php echo $L['LayerNum']; ?></div>
                                    <div class="layer-info">
                                        <div class="layer-name"><?php echo htmlspecialchars($L['LayerName']); ?></div>
                                        <div class="layer-meta"><?php echo htmlspecialchars($L['DataUnit']); ?> · <?php echo htmlspecialchars(explode(',', $L['Protocols'])[0]); ?></div>
                                        <div class="layer-data" id="send-data-<?php echo $L['LayerNum']; ?>">—</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- CHANNEL -->
                    <div class="osi-channel">
                        <div class="channel-line"></div>
                        <div class="channel-label">PHYSICAL MEDIUM</div>
                        <div class="channel-line"></div>
                        <div class="packet-icon" id="packetIcon">📦</div>
                    </div>

                    <!-- RECEIVER -->
                    <div>
                        <div class="osi-side-label">📥 Receiver — Decapsulating</div>
                        <div class="osi-stack" id="receiverStack">
                            <?php foreach (array_reverse($layers) as $L): ?>
                                <div class="osi-layer" data-layer="<?php echo $L['LayerNum']; ?>" data-side="recv" data-num="<?php echo $L['LayerNum']; ?>">
                                    <div class="layer-num">L<?php echo $L['LayerNum']; ?></div>
                                    <div class="layer-info">
                                        <div class="layer-name"><?php echo htmlspecialchars($L['LayerName']); ?></div>
                                        <div class="layer-meta"><?php echo htmlspecialchars($L['DataUnit']); ?> · <?php echo htmlspecialchars(explode(',', $L['Protocols'])[0]); ?></div>
                                        <div class="layer-data" id="recv-data-<?php echo $L['LayerNum']; ?>">—</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Packet preview -->
            <div class="panel fade-in" id="previewPanel" style="display:none;">
                <div class="panel-head">
                    <div class="panel-icon">🔍</div>
                    <div class="panel-title">Captured Packet</div>
                    <span class="panel-tag">JUST NOW</span>
                </div>
                <div class="panel-body">
                    <div class="code-block" id="packetPreview"></div>
                    <div style="margin-top:14px;display:flex;gap:8px;">
                        <a class="btn btn-primary" id="viewDetailBtn" href="#">🔬 Open Anatomy →</a>
                        <a class="btn" href="history.php">📋 View History</a>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
const $ = q => document.querySelector(q);
const $$ = q => document.querySelectorAll(q);

let SPEED = 350;
let autoCaptureInterval = null;

$('#speedRange').addEventListener('input', e => {
    SPEED = +e.target.value;
    $('#speedVal').textContent = SPEED + 'ms';
});

$('#autoCapture').addEventListener('change', e => {
    if (e.target.checked) {
        autoCaptureInterval = setInterval(() => sendPacket(true), 4000);
    } else {
        clearInterval(autoCaptureInterval);
    }
});

const sleep = ms => new Promise(r => setTimeout(r, ms));

async function loadStats() {
    try {
        const res = await fetch('api.php?action=getStats');
        const data = await res.json();
        if (!data.success) return;
        $('#stat-total').textContent = data.stats.total.toLocaleString();
        $('#stat-hour').textContent  = data.stats.lastHour.toLocaleString();
        $('#stat-ips').textContent   = data.stats.uniqueIPs;
        $('#stat-bytes').textContent = formatBytes(data.stats.totalBytes);
        $('#stat-top').textContent   = data.stats.topProto;
    } catch (e) { console.error(e); }
}

function formatBytes(b) {
    if (b < 1024) return b + ' B';
    if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
    return (b/1048576).toFixed(2) + ' MB';
}

loadStats();

function resetAnimation() {
    $$('.osi-layer').forEach(el => {
        el.classList.remove('active', 'passed');
        const num = el.dataset.num, side = el.dataset.side;
        document.getElementById(`${side}-data-${num}`).textContent = '—';
    });
    $('#packetIcon').classList.remove('show');
    $('#packetIcon').style.top = '0';
    $('#previewPanel').style.display = 'none';
    setPhase('IDLE', '');
}

function setPhase(text, cls) {
    const el = $('#phaseLabel');
    el.textContent = text;
    el.className = 'phase-indicator ' + cls;
}

function buildLayerData(p, layer) {
    switch (layer) {
        case 7: return `${p.proto} ${p.method} ${p.url}`;
        case 6: return p.proto === 'HTTPS' ? 'TLS encrypted' : 'plaintext';
        case 5: return `Session: SID-${Math.floor(Math.random()*9000+1000)}`;
        case 4: return `${p.transport} ${p.srcPort} → ${p.destPort}`;
        case 3: return `${p.srcIP} → ${p.destIP} TTL=64`;
        case 2: return `${p.srcMAC} → ${p.destMAC}`;
        case 1: return `${p.size}B over copper @ 1Gbps`;
    }
    return '—';
}

async function sendPacket(silent = false) {
    if (!silent) resetAnimation();
    else { resetAnimation(); }

    const proto = $('#b-proto').value;
    const method = $('#b-method').value;
    const srcIP = $('#b-src').value;
    const destIP = $('#b-dest').value;
    const url = $('#b-url').value;

    const protoMap = {
        HTTP:  { transport: 'TCP', destPort: 80,  size: 800 },
        HTTPS: { transport: 'TCP', destPort: 443, size: 900 },
        DNS:   { transport: 'UDP', destPort: 53,  size: 80 },
        FTP:   { transport: 'TCP', destPort: 21,  size: 400 },
        SSH:   { transport: 'TCP', destPort: 22,  size: 200 },
        SMTP:  { transport: 'TCP', destPort: 25,  size: 600 },
        ICMP:  { transport: '—',   destPort: 0,   size: 64 },
        POP3:  { transport: 'TCP', destPort: 110, size: 500 },
        IMAP:  { transport: 'TCP', destPort: 143, size: 700 },
        Telnet:{ transport: 'TCP', destPort: 23,  size: 200 }
    };
    const cfg = protoMap[proto];

    const packet = {
        proto, method, url, srcIP, destIP,
        transport: cfg.transport,
        srcPort: proto === 'ICMP' ? 0 : Math.floor(Math.random() * 16383 + 49152),
        destPort: cfg.destPort,
        size: cfg.size,
        srcMAC: randMAC(),
        destMAC: randMAC()
    };

    /* ENCAPSULATION */
    setPhase('ENCAPSULATING', 'encap');
    for (let layer = 7; layer >= 1; layer--) {
        const el = document.querySelector(`.osi-layer[data-side="send"][data-num="${layer}"]`);
        el.classList.add('active');
        document.getElementById(`send-data-${layer}`).textContent = buildLayerData(packet, layer);
        await sleep(SPEED);
        el.classList.remove('active');
        el.classList.add('passed');
    }

    /* TRANSMISSION */
    setPhase('TRANSMITTING', 'transmit');
    $('#packetIcon').classList.add('show');
    $('#packetIcon').style.top = '0';
    await sleep(50);
    $('#packetIcon').style.transition = `top ${SPEED * 1.5}ms ease`;
    $('#packetIcon').style.top = 'calc(100% - 32px)';
    await sleep(SPEED * 1.5);

    /* DECAPSULATION */
    setPhase('DECAPSULATING', 'decap');
    for (let layer = 1; layer <= 7; layer++) {
        const el = document.querySelector(`.osi-layer[data-side="recv"][data-num="${layer}"]`);
        el.classList.add('active');
        document.getElementById(`recv-data-${layer}`).textContent = buildLayerData(packet, layer);
        await sleep(SPEED);
        el.classList.remove('active');
        el.classList.add('passed');
    }

    setPhase('DELIVERED ✓', 'done');

    /* SAVE TO DB */
    const fd = new FormData();
    fd.append('action', 'capturePacket');
    fd.append('protocol', proto);
    fd.append('method', method);
    fd.append('url', url);
    fd.append('srcIP', srcIP);
    fd.append('destIP', destIP);

    try {
        const res = await fetch('api.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            $('#previewPanel').style.display = 'block';
            $('#packetPreview').innerHTML = renderPreview(packet, data.packetID);
            $('#viewDetailBtn').href = `inspector.php?id=${data.packetID}`;
            loadStats();
        }
    } catch (e) { console.error(e); }
}

function renderPreview(p, id) {
    return `<span class="k">┌── Packet #${id} ──────────────────────────┐</span>
<span class="k">│</span> <span class="L7">[L7 Application]</span>  <span class="v">${p.proto} ${p.method} ${p.url}</span>
<span class="k">│</span> <span class="L6">[L6 Presentation]</span> <span class="v">${p.proto === 'HTTPS' ? 'TLS 1.3 (encrypted)' : 'plain text'}</span>
<span class="k">│</span> <span class="L5">[L5 Session]</span>      <span class="v">established</span>
<span class="k">│</span> <span class="L4">[L4 Transport]</span>    <span class="v">${p.transport} src:${p.srcPort} dst:${p.destPort}</span>
<span class="k">│</span> <span class="L3">[L3 Network]</span>      <span class="v">${p.srcIP} → ${p.destIP} (TTL=64)</span>
<span class="k">│</span> <span class="L2">[L2 Data Link]</span>    <span class="v">${p.srcMAC} → ${p.destMAC}</span>
<span class="k">│</span> <span class="L1">[L1 Physical]</span>     <span class="v">${p.size} bytes over copper</span>
<span class="k">└─────────────────────────────────────────┘</span>`;
}

async function generateBatch() {
    const fd = new FormData();
    fd.append('action', 'autoGenerate');
    fd.append('count', '5');
    await fetch('api.php', { method: 'POST', body: fd });
    loadStats();
}

function randMAC() {
    const hex = '0123456789ABCDEF';
    let mac = '';
    for (let i = 0; i < 6; i++) {
        if (i) mac += ':';
        mac += hex[Math.floor(Math.random()*16)] + hex[Math.floor(Math.random()*16)];
    }
    return mac;
}
</script>

</body>
</html>
