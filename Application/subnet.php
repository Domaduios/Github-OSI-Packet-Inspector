<?php
include 'auth_check.php';
include 'config.php';
$activeTab = 'subnet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subnet Calculator — OSI Inspector</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .calc-input {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 10px;
            align-items: end;
        }
        .result-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        .result-card {
            background: var(--bg-sidebar);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 14px 16px;
        }
        .result-card .label {
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .result-card .value {
            font-family: var(--mono);
            font-size: 15px;
            font-weight: 700;
            color: var(--primary);
        }
        .result-card.big .value { font-size: 18px; }

        .binary-display {
            background: var(--bg-code);
            color: white;
            padding: 16px;
            border-radius: var(--radius);
            font-family: var(--mono);
            font-size: 14px;
            text-align: center;
            margin-top: 16px;
            letter-spacing: 2px;
        }
        .binary-display .net { color: #5eead4; }
        .binary-display .host { color: #fdba74; }

        .vlsm-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            font-family: var(--mono);
            margin-top: 12px;
        }
        .vlsm-table th { background: var(--bg-sidebar); padding: 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        .vlsm-table td { padding: 8px; border-bottom: 1px solid var(--border); }
        .vlsm-table tr:hover { background: var(--bg-hover); }
    </style>
</head>
<body>

<div class="app-shell">
    <?php include '_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Subnet Calculator</div>
                <div class="topbar-sub">VLSM / CIDR · IPv4 subnetting tool</div>
            </div>
        </div>

        <div class="content">

            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">#</div>
                    <div class="panel-title">IP / CIDR Input</div>
                </div>
                <div class="panel-body">
                    <div class="calc-input">
                        <div class="field">
                            <label class="field-label">IP Address</label>
                            <input class="input" id="ip" value="192.168.1.10" placeholder="e.g. 192.168.1.10">
                        </div>
                        <div class="field">
                            <label class="field-label">CIDR Prefix</label>
                            <select class="select" id="cidr">
                                <?php for ($i = 32; $i >= 8; $i--): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i === 24 ? 'selected' : ''; ?>>/<?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary btn-lg" onclick="calc()">⚡ Calculate</button>
                    </div>
                </div>
            </div>

            <div class="panel fade-in" id="resultPanel" style="display:none;">
                <div class="panel-head">
                    <div class="panel-icon">📐</div>
                    <div class="panel-title">Calculation Results</div>
                    <span class="panel-tag" id="cidrTag">/24</span>
                </div>
                <div class="panel-body">
                    <div class="result-grid">
                        <div class="result-card big"><div class="label">Network Address</div><div class="value" id="r-net">—</div></div>
                        <div class="result-card big"><div class="label">Broadcast Address</div><div class="value" id="r-brd">—</div></div>
                        <div class="result-card"><div class="label">Subnet Mask</div><div class="value" id="r-mask">—</div></div>
                        <div class="result-card"><div class="label">Wildcard Mask</div><div class="value" id="r-wc">—</div></div>
                        <div class="result-card"><div class="label">First Usable</div><div class="value" id="r-first">—</div></div>
                        <div class="result-card"><div class="label">Last Usable</div><div class="value" id="r-last">—</div></div>
                        <div class="result-card"><div class="label">Total IPs</div><div class="value" id="r-total">—</div></div>
                        <div class="result-card"><div class="label">Usable Hosts</div><div class="value" id="r-hosts">—</div></div>
                        <div class="result-card"><div class="label">IP Class</div><div class="value" id="r-class">—</div></div>
                        <div class="result-card"><div class="label">Type</div><div class="value" id="r-type">—</div></div>
                    </div>

                    <div class="binary-display" id="binDisplay"></div>
                </div>
            </div>

            <!-- Common subnets reference -->
            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">📚</div>
                    <div class="panel-title">Common Subnet Reference</div>
                    <span class="panel-tag">CHEAT SHEET</span>
                </div>
                <div class="panel-body">
                    <table class="vlsm-table">
                        <thead>
                            <tr>
                                <th>CIDR</th>
                                <th>Subnet Mask</th>
                                <th>Total IPs</th>
                                <th>Usable Hosts</th>
                                <th>Common Use</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>/8</td>  <td>255.0.0.0</td>       <td>16,777,216</td><td>16,777,214</td><td>Class A</td></tr>
                            <tr><td>/16</td> <td>255.255.0.0</td>     <td>65,536</td>    <td>65,534</td>    <td>Class B</td></tr>
                            <tr><td>/24</td> <td>255.255.255.0</td>   <td>256</td>       <td>254</td>       <td>Class C / typical LAN</td></tr>
                            <tr><td>/25</td> <td>255.255.255.128</td> <td>128</td>       <td>126</td>       <td>Half a /24</td></tr>
                            <tr><td>/26</td> <td>255.255.255.192</td> <td>64</td>        <td>62</td>        <td>Small subnet</td></tr>
                            <tr><td>/27</td> <td>255.255.255.224</td> <td>32</td>        <td>30</td>        <td>Small office</td></tr>
                            <tr><td>/28</td> <td>255.255.255.240</td> <td>16</td>        <td>14</td>        <td>Tiny subnet</td></tr>
                            <tr><td>/29</td> <td>255.255.255.248</td> <td>8</td>         <td>6</td>         <td>Point-to-point + few hosts</td></tr>
                            <tr><td>/30</td> <td>255.255.255.252</td> <td>4</td>         <td>2</td>         <td>Point-to-point link</td></tr>
                            <tr><td>/32</td> <td>255.255.255.255</td> <td>1</td>         <td>1</td>         <td>Single host route</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
async function calc() {
    const ip = document.getElementById('ip').value.trim();
    const cidr = document.getElementById('cidr').value;

    const fd = new FormData();
    fd.append('action', 'subnetCalc');
    fd.append('ip', ip);
    fd.append('cidr', cidr);

    try {
        const res = await fetch('api.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.success) { alert(data.message); return; }

        document.getElementById('resultPanel').style.display = 'block';
        document.getElementById('cidrTag').textContent = '/' + data.cidr;
        document.getElementById('r-net').textContent = data.network;
        document.getElementById('r-brd').textContent = data.broadcast;
        document.getElementById('r-mask').textContent = data.mask;
        document.getElementById('r-wc').textContent = data.wildcard;
        document.getElementById('r-first').textContent = data.firstHost;
        document.getElementById('r-last').textContent = data.lastHost;
        document.getElementById('r-total').textContent = data.totalIPs.toLocaleString();
        document.getElementById('r-hosts').textContent = data.hosts.toLocaleString();
        document.getElementById('r-class').textContent = data.class;
        document.getElementById('r-type').textContent = data.private ? '🔒 Private' : '🌐 Public';

        // Binary display - color-code network vs host bits
        const bin = data.binary.replace(/\./g, '');
        const netBits = bin.substr(0, data.cidr);
        const hostBits = bin.substr(data.cidr);
        document.getElementById('binDisplay').innerHTML =
            `<span class="net">${formatBin(netBits)}</span><span class="host">${formatBin(hostBits)}</span>` +
            `<div style="font-size:10px;margin-top:8px;color:#94a3b8;">↑ <span style="color:#5eead4;">network bits (${data.cidr})</span> · <span style="color:#fdba74;">host bits (${32 - data.cidr})</span></div>`;
    } catch (e) { console.error(e); }
}

function formatBin(bits) {
    let r = '';
    for (let i = 0; i < bits.length; i++) {
        if (i > 0 && i % 8 === 0) r += '.';
        r += bits[i];
    }
    return r;
}

calc();
</script>

</body>
</html>
