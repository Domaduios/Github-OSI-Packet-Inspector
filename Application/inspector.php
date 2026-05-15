<?php
include 'auth_check.php';
include 'config.php';
$activeTab = 'anatomy';

$packetID = (int)($_GET['id'] ?? 0);
$packet = null;
if ($packetID > 0) {
    $stmt = $conn->prepare("SELECT * FROM Packets WHERE PacketID = ?");
    $stmt->bind_param('i', $packetID);
    $stmt->execute();
    $packet = $stmt->get_result()->fetch_assoc();
}
if (!$packet) {
    $r = $conn->query("SELECT * FROM Packets ORDER BY CapturedAt DESC LIMIT 1");
    $packet = $r ? $r->fetch_assoc() : null;
    if ($packet) $packetID = $packet['PacketID'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packet Anatomy — OSI Inspector</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .layer-card {
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-left: 4px solid var(--border-strong);
            border-radius: var(--radius-md);
            margin-bottom: 12px;
            overflow: hidden;
            transition: transform .15s;
        }

        .layer-card:hover { transform: translateX(2px); }

        .layer-card[data-l="7"] { border-left-color: var(--L7); }
        .layer-card[data-l="6"] { border-left-color: var(--L6); }
        .layer-card[data-l="5"] { border-left-color: var(--L5); }
        .layer-card[data-l="4"] { border-left-color: var(--L4); }
        .layer-card[data-l="3"] { border-left-color: var(--L3); }
        .layer-card[data-l="2"] { border-left-color: var(--L2); }
        .layer-card[data-l="1"] { border-left-color: var(--L1); }

        .layer-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: var(--bg-sidebar);
            border-bottom: 1px solid var(--border);
        }

        .layer-num {
            width: 32px; height: 32px;
            border-radius: var(--radius);
            background: var(--bg-elevated);
            display: grid;
            place-items: center;
            font-family: var(--mono);
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }
        .layer-card[data-l="7"] .layer-num { color: var(--L7); border: 1px solid var(--L7); }
        .layer-card[data-l="6"] .layer-num { color: var(--L6); border: 1px solid var(--L6); }
        .layer-card[data-l="5"] .layer-num { color: var(--L5); border: 1px solid var(--L5); }
        .layer-card[data-l="4"] .layer-num { color: var(--L4); border: 1px solid var(--L4); }
        .layer-card[data-l="3"] .layer-num { color: var(--L3); border: 1px solid var(--L3); }
        .layer-card[data-l="2"] .layer-num { color: var(--L2); border: 1px solid var(--L2); }
        .layer-card[data-l="1"] .layer-num { color: var(--L1); border: 1px solid var(--L1); }

        .layer-title { font-size: 14px; font-weight: 600; }
        .layer-desc  { font-size: 11px; color: var(--text-muted); margin-top: 1px; font-family: var(--mono); }

        .layer-body { padding: 14px 16px; }

        .hdr-table {
            display: grid;
            grid-template-columns: minmax(150px, 1fr) 2fr;
            gap: 1px;
            background: var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            font-size: 12px;
            font-family: var(--mono);
        }
        .hdr-table > div {
            padding: 8px 12px;
            background: var(--bg-elevated);
        }
        .hdr-table .hk { color: var(--text-muted); font-weight: 500; }
        .hdr-table .hv { color: var(--text); font-weight: 600; }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
        }
        .summary-cell {
            padding: 10px 12px;
            background: var(--bg-sidebar);
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }
        .summary-cell .label {
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .summary-cell .value {
            font-size: 13px;
            font-weight: 600;
            font-family: var(--mono);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-muted);
        }
        .empty-state-icon { font-size: 48px; opacity: .3; margin-bottom: 14px; }
    </style>
</head>
<body>

<div class="app-shell">
    <?php include '_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">
                    Packet Anatomy
                    <?php if ($packet): ?><span class="tag tag-primary">#<?php echo $packetID; ?></span><?php endif; ?>
                </div>
                <div class="topbar-sub">Layer-by-layer header breakdown</div>
            </div>
            <?php if ($packet): ?>
            <div class="topbar-actions">
                <a class="btn btn-sm" href="?id=<?php echo max(1, $packetID - 1); ?>">← Prev</a>
                <a class="btn btn-sm" href="?id=<?php echo $packetID + 1; ?>">Next →</a>
                <a class="btn" href="history.php">📋 All Packets</a>
            </div>
            <?php endif; ?>
        </div>

        <div class="content">

            <?php if (!$packet): ?>
                <div class="panel fade-in">
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <div style="font-size:16px;font-weight:600;margin-bottom:6px;">No Packet Found</div>
                        <p style="font-size:13px;margin-bottom:16px;">No packets have been captured yet, or the requested ID doesn't exist.</p>
                        <a class="btn btn-primary" href="index.php">▶ Capture a Packet</a>
                    </div>
                </div>
            <?php else: ?>

            <!-- Summary -->
            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">📊</div>
                    <div class="panel-title">Summary</div>
                    <span class="panel-tag"><?php echo htmlspecialchars($packet['CapturedAt']); ?></span>
                </div>
                <div class="panel-body">
                    <div class="summary-grid">
                        <div class="summary-cell">
                            <div class="label">Protocol</div>
                            <div class="value"><span class="tag tag-primary"><?php echo htmlspecialchars($packet['AppProtocol']); ?></span></div>
                        </div>
                        <div class="summary-cell">
                            <div class="label">Direction</div>
                            <div class="value"><?php echo htmlspecialchars($packet['Direction']); ?></div>
                        </div>
                        <div class="summary-cell">
                            <div class="label">Status</div>
                            <div class="value"><span class="tag tag-success"><?php echo htmlspecialchars($packet['Status']); ?></span></div>
                        </div>
                        <div class="summary-cell">
                            <div class="label">Size</div>
                            <div class="value"><?php echo (int)$packet['PacketSize']; ?> bytes</div>
                        </div>
                        <div class="summary-cell">
                            <div class="label">Source → Dest</div>
                            <div class="value text-xs"><?php echo htmlspecialchars($packet['SourceIP']); ?> → <?php echo htmlspecialchars($packet['DestIP']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layer 7 -->
            <div class="layer-card fade-in" data-l="7">
                <div class="layer-head">
                    <div class="layer-num">L7</div>
                    <div>
                        <div class="layer-title">Application Layer</div>
                        <div class="layer-desc">User-facing protocols · Data unit: Data</div>
                    </div>
                </div>
                <div class="layer-body">
                    <div class="hdr-table">
                        <div class="hk">Protocol</div>           <div class="hv"><?php echo htmlspecialchars($packet['AppProtocol']); ?></div>
                        <div class="hk">Method</div>             <div class="hv"><?php echo htmlspecialchars($packet['HttpMethod']); ?></div>
                        <div class="hk">URL Path</div>           <div class="hv"><?php echo htmlspecialchars($packet['UrlPath']); ?></div>
                        <div class="hk">User Agent</div>         <div class="hv text-xs"><?php echo htmlspecialchars($packet['UserAgent']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Layer 6 -->
            <div class="layer-card fade-in" data-l="6">
                <div class="layer-head">
                    <div class="layer-num">L6</div>
                    <div>
                        <div class="layer-title">Presentation Layer</div>
                        <div class="layer-desc">Encryption · encoding · compression</div>
                    </div>
                </div>
                <div class="layer-body">
                    <div class="hdr-table">
                        <div class="hk">Encryption</div> <div class="hv"><?php echo $packet['AppProtocol'] === 'HTTPS' ? 'TLS 1.3 (AES-256-GCM)' : 'None — plaintext'; ?></div>
                        <div class="hk">Encoding</div>   <div class="hv">UTF-8</div>
                        <div class="hk">Compression</div> <div class="hv"><?php echo $packet['PacketSize'] > 500 ? 'gzip' : 'none'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Layer 5 -->
            <div class="layer-card fade-in" data-l="5">
                <div class="layer-head">
                    <div class="layer-num">L5</div>
                    <div>
                        <div class="layer-title">Session Layer</div>
                        <div class="layer-desc">Establish · maintain · terminate sessions</div>
                    </div>
                </div>
                <div class="layer-body">
                    <div class="hdr-table">
                        <div class="hk">Session ID</div> <div class="hv">SID-<?php echo substr(md5($packet['PacketID']), 0, 8); ?></div>
                        <div class="hk">State</div>      <div class="hv">ESTABLISHED</div>
                        <div class="hk">Mode</div>       <div class="hv">Full-duplex</div>
                    </div>
                </div>
            </div>

            <!-- Layer 4 -->
            <div class="layer-card fade-in" data-l="4">
                <div class="layer-head">
                    <div class="layer-num">L4</div>
                    <div>
                        <div class="layer-title">Transport Layer</div>
                        <div class="layer-desc">End-to-end delivery · ports · Data unit: Segment</div>
                    </div>
                </div>
                <div class="layer-body">
                    <div class="hdr-table">
                        <div class="hk">Protocol</div>         <div class="hv"><?php echo htmlspecialchars($packet['TransportProto']); ?></div>
                        <div class="hk">Source Port</div>      <div class="hv"><?php echo (int)$packet['SourcePort']; ?></div>
                        <div class="hk">Destination Port</div> <div class="hv"><?php echo (int)$packet['DestPort']; ?></div>
                        <div class="hk">TCP Flags</div>        <div class="hv"><?php echo htmlspecialchars($packet['TcpFlags']); ?></div>
                        <div class="hk">Reliability</div>      <div class="hv"><?php echo $packet['TransportProto'] === 'TCP' ? 'Reliable (3-way handshake)' : 'Best-effort'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Layer 3 -->
            <div class="layer-card fade-in" data-l="3">
                <div class="layer-head">
                    <div class="layer-num">L3</div>
                    <div>
                        <div class="layer-title">Network Layer</div>
                        <div class="layer-desc">Logical addressing · routing · Data unit: Packet</div>
                    </div>
                </div>
                <div class="layer-body">
                    <div class="hdr-table">
                        <div class="hk">Source IP</div>      <div class="hv"><?php echo htmlspecialchars($packet['SourceIP']); ?></div>
                        <div class="hk">Destination IP</div> <div class="hv"><?php echo htmlspecialchars($packet['DestIP']); ?></div>
                        <div class="hk">IP Version</div>     <div class="hv">IPv<?php echo (int)$packet['IpVersion']; ?></div>
                        <div class="hk">TTL</div>            <div class="hv"><?php echo (int)$packet['TTL']; ?> hops</div>
                    </div>
                </div>
            </div>

            <!-- Layer 2 -->
            <div class="layer-card fade-in" data-l="2">
                <div class="layer-head">
                    <div class="layer-num">L2</div>
                    <div>
                        <div class="layer-title">Data Link Layer</div>
                        <div class="layer-desc">MAC addressing · framing · Data unit: Frame</div>
                    </div>
                </div>
                <div class="layer-body">
                    <div class="hdr-table">
                        <div class="hk">Source MAC</div>      <div class="hv"><?php echo htmlspecialchars($packet['SourceMAC']); ?></div>
                        <div class="hk">Destination MAC</div> <div class="hv"><?php echo htmlspecialchars($packet['DestMAC']); ?></div>
                        <div class="hk">EtherType</div>       <div class="hv"><?php echo htmlspecialchars($packet['EtherType']); ?> (IPv4)</div>
                        <div class="hk">Frame check</div>     <div class="hv">CRC-32 OK</div>
                    </div>
                </div>
            </div>

            <!-- Layer 1 -->
            <div class="layer-card fade-in" data-l="1">
                <div class="layer-head">
                    <div class="layer-num">L1</div>
                    <div>
                        <div class="layer-title">Physical Layer</div>
                        <div class="layer-desc">Bits transmission · Data unit: Bits</div>
                    </div>
                </div>
                <div class="layer-body">
                    <div class="hdr-table">
                        <div class="hk">Medium</div>     <div class="hv"><?php echo htmlspecialchars($packet['Medium']); ?></div>
                        <div class="hk">Link Speed</div> <div class="hv"><?php echo htmlspecialchars($packet['LinkSpeed']); ?></div>
                        <div class="hk">Bits transmitted</div> <div class="hv"><?php echo ((int)$packet['PacketSize']) * 8; ?> bits</div>
                        <div class="hk">Encoding</div>   <div class="hv">Manchester / 8b/10b</div>
                    </div>

                    <div class="code-block" style="margin-top:14px;">
<span class="k">0x0000:</span> <span class="v"><?php echo str_replace(':',' ', substr($packet['DestMAC'].' '.$packet['SourceMAC'].' 08 00 45 00', 0, 47)); ?></span>  <span class="k">........E.</span>
<span class="k">0x0010:</span> <span class="v">00 3c 1c 46 40 00 40 06 b1 e6 c0 a8 0a 2d 8e fa</span>  <span class="k">.&lt;.F@.@....-..</span>
<span class="k">0x0020:</span> <span class="v">be 2e c8 5f 00 50 00 00 00 00 00 00 00 00 a0 02</span>  <span class="k">..._.P..........</span>
<span class="k">0x0030:</span> <span class="v">72 10 e2 50 00 00 02 04 05 b4 04 02 08 0a 1f bf</span>  <span class="k">r..P............</span></div>
                </div>
            </div>

            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>
