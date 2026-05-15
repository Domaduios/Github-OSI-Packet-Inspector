<?php
include 'auth_check.php';
include 'config.php';
$activeTab = 'compare';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TCP vs UDP — OSI Inspector</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .vs-grid {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 16px;
            align-items: start;
        }
        @media (max-width: 800px) { .vs-grid { grid-template-columns: 1fr; } .vs-divider { display: none; } }

        .protocol-card {
            background: var(--bg-elevated);
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            padding: 22px;
        }

        .protocol-card.tcp { border-color: var(--L4); background: var(--L4-bg); }
        .protocol-card.udp { border-color: var(--L3); background: var(--L3-bg); }

        .protocol-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 6px;
            font-family: var(--mono);
        }
        .protocol-card.tcp .protocol-title { color: var(--L4); }
        .protocol-card.udp .protocol-title { color: var(--L3); }

        .protocol-tag {
            font-size: 11px;
            color: var(--text-muted);
            font-family: var(--mono);
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 16px;
        }

        .feature-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .feature-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13px;
            line-height: 1.5;
            padding: 10px 12px;
            background: var(--bg-elevated);
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        .feature-list .ic {
            font-size: 14px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .vs-divider {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-muted);
            text-align: center;
            padding: 60px 8px;
        }

        .compare-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .compare-table th {
            background: var(--bg-sidebar);
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }
        .compare-table td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            font-family: var(--mono);
            font-size: 12px;
        }
        .compare-table .feat { font-weight: 600; color: var(--text); font-family: var(--sans); }
        .compare-table .yes { color: var(--success); font-weight: 700; }
        .compare-table .no  { color: var(--danger); font-weight: 700; }

        .handshake {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .hs-step {
            text-align: center;
            font-family: var(--mono);
            font-size: 11px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 10px 16px;
            min-width: 100px;
        }
        .hs-step .arrow { font-size: 16px; color: var(--primary); }
        .hs-step .label { font-weight: 700; margin-top: 4px; }
    </style>
</head>
<body>

<div class="app-shell">
    <?php include '_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">TCP vs UDP</div>
                <div class="topbar-sub">Compare the two main Transport Layer protocols</div>
            </div>
        </div>

        <div class="content">

            <!-- VS Comparison -->
            <div class="vs-grid fade-in" style="margin-bottom:20px;">
                <div class="protocol-card tcp">
                    <div class="protocol-title">TCP</div>
                    <div class="protocol-tag">Transmission Control Protocol · Layer 4</div>
                    <ul class="feature-list">
                        <li><span class="ic">🔗</span><div><strong>Connection-oriented</strong> — establishes a connection before sending data (3-way handshake).</div></li>
                        <li><span class="ic">✅</span><div><strong>Reliable</strong> — guarantees delivery, in-order, and re-transmits lost packets.</div></li>
                        <li><span class="ic">🔢</span><div><strong>Sequence numbers</strong> — every byte numbered for ordering.</div></li>
                        <li><span class="ic">🌊</span><div><strong>Flow control</strong> — adjusts speed based on receiver capacity.</div></li>
                        <li><span class="ic">🐢</span><div><strong>Slower</strong> — overhead of acknowledgments and retransmissions.</div></li>
                        <li><span class="ic">📦</span><div><strong>Larger header</strong> — 20+ bytes per segment.</div></li>
                    </ul>
                </div>

                <div class="vs-divider">VS</div>

                <div class="protocol-card udp">
                    <div class="protocol-title">UDP</div>
                    <div class="protocol-tag">User Datagram Protocol · Layer 4</div>
                    <ul class="feature-list">
                        <li><span class="ic">⚡</span><div><strong>Connectionless</strong> — no handshake, sends data immediately.</div></li>
                        <li><span class="ic">⚠️</span><div><strong>Unreliable</strong> — no guarantee of delivery or order.</div></li>
                        <li><span class="ic">🚀</span><div><strong>Fast</strong> — minimal overhead, best for real-time use.</div></li>
                        <li><span class="ic">📭</span><div><strong>No flow control</strong> — sender doesn't slow down for receiver.</div></li>
                        <li><span class="ic">🪶</span><div><strong>Lightweight</strong> — only 8-byte header.</div></li>
                        <li><span class="ic">🎮</span><div><strong>Best for</strong> — streaming, gaming, DNS, VoIP.</div></li>
                    </ul>
                </div>
            </div>

            <!-- Detailed comparison table -->
            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">📊</div>
                    <div class="panel-title">Detailed Comparison</div>
                </div>
                <div class="panel-body">
                    <table class="compare-table">
                        <thead>
                            <tr>
                                <th style="width:30%;">Feature</th>
                                <th style="width:35%;color:var(--L4);">TCP</th>
                                <th style="width:35%;color:var(--L3);">UDP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="feat">Connection</td>            <td>Connection-oriented</td><td>Connectionless</td></tr>
                            <tr><td class="feat">Reliability</td>           <td class="yes">✓ Reliable</td><td class="no">✗ Unreliable</td></tr>
                            <tr><td class="feat">Ordering</td>              <td class="yes">✓ In-order</td><td class="no">✗ No order</td></tr>
                            <tr><td class="feat">Error Recovery</td>        <td class="yes">✓ Retransmission</td><td class="no">✗ None</td></tr>
                            <tr><td class="feat">Flow Control</td>          <td class="yes">✓ Yes</td><td class="no">✗ No</td></tr>
                            <tr><td class="feat">Congestion Control</td>    <td class="yes">✓ Yes</td><td class="no">✗ No</td></tr>
                            <tr><td class="feat">Header Size</td>           <td>20-60 bytes</td><td>8 bytes</td></tr>
                            <tr><td class="feat">Speed</td>                 <td>Slower</td><td>Faster</td></tr>
                            <tr><td class="feat">Use Cases</td>             <td>Web, Email, FTP, SSH</td><td>DNS, Streaming, VoIP, Games</td></tr>
                            <tr><td class="feat">Handshake</td>             <td>3-way (SYN, SYN-ACK, ACK)</td><td>None</td></tr>
                            <tr><td class="feat">Broadcasting</td>          <td class="no">✗ No</td><td class="yes">✓ Yes</td></tr>
                            <tr><td class="feat">Port range</td>            <td>0-65535</td><td>0-65535</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TCP Handshake -->
            <div class="panel fade-in">
                <div class="panel-head">
                    <div class="panel-icon">🤝</div>
                    <div class="panel-title">TCP Three-Way Handshake</div>
                    <span class="panel-tag">CONNECTION ESTABLISHMENT</span>
                </div>
                <div class="panel-body">
                    <p style="font-size:13px;color:var(--text-muted);line-height:1.7;margin-bottom:12px;">
                        Before TCP transfers any data, the client and server exchange three packets to synchronize sequence numbers and establish a reliable connection.
                    </p>
                    <div class="handshake">
                        <div class="hs-step">
                            <div class="arrow">→</div>
                            <div class="label">SYN</div>
                            <div class="muted text-xs">Client wants to connect</div>
                        </div>
                        <div class="hs-step">
                            <div class="arrow">←</div>
                            <div class="label">SYN-ACK</div>
                            <div class="muted text-xs">Server agrees</div>
                        </div>
                        <div class="hs-step">
                            <div class="arrow">→</div>
                            <div class="label">ACK</div>
                            <div class="muted text-xs">Client confirms</div>
                        </div>
                    </div>
                    <p style="font-size:12px;color:var(--text-muted);margin-top:14px;text-align:center;font-family:var(--mono);">
                        ↑ After this, data can flow in both directions
                    </p>
                </div>
            </div>

        </div>
    </main>
</div>

</body>
</html>
