<?php
include 'auth_check.php';
include 'config.php';
$activeTab = 'learn';

$layers = [];
$r = $conn->query("SELECT * FROM OSILayers ORDER BY LayerNum DESC");
while ($row = $r->fetch_assoc()) $layers[] = $row;

$protocols = [];
$r = $conn->query("SELECT * FROM Protocols ORDER BY LayerNum DESC, Name ASC");
while ($row = $r->fetch_assoc()) $protocols[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Learn OSI — OSI Inspector</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .hero {
            background: linear-gradient(135deg, var(--primary-bg), var(--info-bg));
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 28px;
            margin-bottom: 18px;
        }
        .hero h1 { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
        .hero p { color: var(--text-muted); font-size: 14px; line-height: 1.6; }

        .mnemonic-box {
            background: var(--bg-elevated);
            border: 2px dashed var(--border-strong);
            border-radius: var(--radius-md);
            padding: 22px;
            text-align: center;
            margin-bottom: 18px;
        }
        .mn-label {
            font-size: 11px;
            color: var(--text-muted);
            font-family: var(--mono);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 12px;
            font-weight: 600;
        }
        .mn-phrase {
            font-size: 22px;
            font-weight: 800;
            line-height: 1.4;
        }
        .mn-phrase span { display: inline-block; margin: 0 4px; padding: 4px 8px; border-radius: 4px; }
        .mn1 { background: var(--L1-bg); color: var(--L1); }
        .mn2 { background: var(--L2-bg); color: var(--L2); }
        .mn3 { background: var(--L3-bg); color: var(--L3); }
        .mn4 { background: var(--L4-bg); color: var(--L4); }
        .mn5 { background: var(--L5-bg); color: var(--L5); }
        .mn6 { background: var(--L6-bg); color: var(--L6); }
        .mn7 { background: var(--L7-bg); color: var(--L7); }
        .mn-key {
            font-size: 11px;
            color: var(--text-muted);
            font-family: var(--mono);
            margin-top: 14px;
            letter-spacing: .5px;
        }

        .layer-detail {
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-left: 4px solid var(--border-strong);
            border-radius: var(--radius-md);
            padding: 22px;
            margin-bottom: 12px;
        }
        .layer-detail[data-l="7"] { border-left-color: var(--L7); }
        .layer-detail[data-l="6"] { border-left-color: var(--L6); }
        .layer-detail[data-l="5"] { border-left-color: var(--L5); }
        .layer-detail[data-l="4"] { border-left-color: var(--L4); }
        .layer-detail[data-l="3"] { border-left-color: var(--L3); }
        .layer-detail[data-l="2"] { border-left-color: var(--L2); }
        .layer-detail[data-l="1"] { border-left-color: var(--L1); }

        .layer-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .layer-num {
            width: 38px; height: 38px;
            border-radius: 9px;
            background: var(--bg-sidebar);
            display: grid;
            place-items: center;
            font-family: var(--mono);
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }
        .layer-detail[data-l="7"] .layer-num { color: var(--L7); border: 2px solid var(--L7); }
        .layer-detail[data-l="6"] .layer-num { color: var(--L6); border: 2px solid var(--L6); }
        .layer-detail[data-l="5"] .layer-num { color: var(--L5); border: 2px solid var(--L5); }
        .layer-detail[data-l="4"] .layer-num { color: var(--L4); border: 2px solid var(--L4); }
        .layer-detail[data-l="3"] .layer-num { color: var(--L3); border: 2px solid var(--L3); }
        .layer-detail[data-l="2"] .layer-num { color: var(--L2); border: 2px solid var(--L2); }
        .layer-detail[data-l="1"] .layer-num { color: var(--L1); border: 2px solid var(--L1); }

        .layer-detail h3 { font-size: 16px; font-weight: 700; }
        .layer-detail .meta { font-size: 11px; color: var(--text-muted); font-family: var(--mono); margin-top: 1px; }
        .layer-detail .purpose { color: var(--text-muted); font-size: 14px; line-height: 1.7; margin-bottom: 12px; }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }
        .info-cell {
            background: var(--bg-sidebar);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 10px 12px;
        }
        .info-cell .label {
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .info-cell .value { font-size: 12px; color: var(--text); font-family: var(--mono); }

        .example-box {
            background: var(--bg-sidebar);
            border-left: 3px solid var(--primary);
            border-radius: var(--radius);
            padding: 12px 16px;
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.7;
        }
        .example-box::before {
            content: '💡 Example';
            display: block;
            font-size: 10px;
            color: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

<div class="app-shell">
    <?php include '_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Learn OSI Model</div>
                <div class="topbar-sub">Comprehensive reference for the 7 layers</div>
            </div>
        </div>

        <div class="content">

            <div class="hero fade-in">
                <h1>📚 The OSI Reference Model</h1>
                <p>The Open Systems Interconnection (OSI) Model is a conceptual framework that standardizes how different computer systems communicate. It divides the communication process into 7 distinct layers, each with a specific role.</p>
            </div>

            <!-- Mnemonic -->
            <div class="mnemonic-box fade-in">
                <div class="mn-label">Memorize Bottom-Up (L1 → L7)</div>
                <div class="mn-phrase">
                    <span class="mn1">Please</span>
                    <span class="mn2">Do</span>
                    <span class="mn3">Not</span>
                    <span class="mn4">Throw</span>
                    <span class="mn5">Sausage</span>
                    <span class="mn6">Pizza</span>
                    <span class="mn7">Away</span>
                </div>
                <div class="mn-key">PHYSICAL · DATA LINK · NETWORK · TRANSPORT · SESSION · PRESENTATION · APPLICATION</div>
            </div>

            <!-- All layers -->
            <?php foreach ($layers as $L): ?>
            <div class="layer-detail fade-in" data-l="<?php echo $L['LayerNum']; ?>">
                <div class="layer-head">
                    <div class="layer-num">L<?php echo $L['LayerNum']; ?></div>
                    <div>
                        <h3><?php echo htmlspecialchars($L['LayerName']); ?> Layer</h3>
                        <div class="meta">Data unit: <?php echo htmlspecialchars($L['DataUnit']); ?></div>
                    </div>
                </div>
                <p class="purpose"><?php echo htmlspecialchars($L['Purpose']); ?></p>

                <div class="info-grid">
                    <div class="info-cell">
                        <div class="label">Protocols</div>
                        <div class="value"><?php echo htmlspecialchars($L['Protocols']); ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="label">Devices</div>
                        <div class="value"><?php echo htmlspecialchars($L['Devices']); ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="label">Data Unit</div>
                        <div class="value"><?php echo htmlspecialchars($L['DataUnit']); ?></div>
                    </div>
                </div>

                <div class="example-box">
                    <?php echo htmlspecialchars($L['Example']); ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Protocols Reference -->
            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">📡</div>
                    <div class="panel-title">Common Protocols Cheat Sheet</div>
                    <span class="panel-tag"><?php echo count($protocols); ?> PROTOCOLS</span>
                </div>
                <div class="table-wrap" style="border:none;border-top:1px solid var(--border);border-radius:0;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Protocol</th>
                                <th>Layer</th>
                                <th>Port</th>
                                <th>Transport</th>
                                <th>Description</th>
                                <th>Use Case</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($protocols as $p):
                            $L = $p['LayerNum'];
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($p['Name']); ?></strong></td>
                                <td><span class="tag tag-L<?php echo $L; ?>">L<?php echo $L; ?></span></td>
                                <td class="font-mono text-xs"><?php echo htmlspecialchars($p['Port']); ?></td>
                                <td class="font-mono text-xs"><?php echo htmlspecialchars($p['Transport']); ?></td>
                                <td class="text-xs muted"><?php echo htmlspecialchars($p['Description']); ?></td>
                                <td class="text-xs"><?php echo htmlspecialchars($p['UseCase']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Encapsulation flow -->
            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">📦</div>
                    <div class="panel-title">Encapsulation Process</div>
                </div>
                <div class="panel-body">
                    <p style="color:var(--text-muted);font-size:13px;line-height:1.7;margin-bottom:14px;">
                        As data travels down through the OSI layers, each layer adds its own header (and sometimes a trailer). On the receiving end, the layers strip these headers off — this is <strong>decapsulation</strong>.
                    </p>
                    <div class="code-block">
<span class="L7">[L7 Application]</span>     <span class="v">"GET /index.html HTTP/1.1"</span>
<span class="L6">[L6 Presentation]</span>    <span class="v">+ TLS encryption (if HTTPS)</span>
<span class="L5">[L5 Session]</span>         <span class="v">+ Session token</span>
<span class="L4">[L4 Transport]</span>       <span class="v">+ TCP header (20B): srcPort=52431 destPort=80</span>
<span class="L3">[L3 Network]</span>         <span class="v">+ IP header (20B): srcIP=192.168.1.10 destIP=8.8.8.8</span>
<span class="L2">[L2 Data Link]</span>       <span class="v">+ Ethernet header (14B) + CRC trailer (4B)</span>
<span class="L1">[L1 Physical]</span>        <span class="v">10110010 11001100... (electrical/optical signals)</span></div>
                </div>
            </div>

        </div>
    </main>
</div>

</body>
</html>
