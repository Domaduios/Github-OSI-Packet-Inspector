<?php
header('Content-Type: application/json');
session_start();
include 'config.php';

// Protect API — must be authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        // Packets
        case 'capturePacket':   capturePacket($conn); break;
        case 'getPacket':       getPacket($conn);     break;
        case 'getPackets':      getPackets($conn);    break;
        case 'getStats':        getStats($conn);      break;
        case 'deletePacket':    deletePacket($conn);  break;
        case 'clearAll':        clearAll($conn);      break;
        case 'autoGenerate':    autoGenerate($conn);  break;
        // Quiz
        case 'getQuizQuestions': getQuizQuestions($conn); break;
        // Utilities (no DB)
        case 'subnetCalc':      subnetCalc(); break;
        case 'pingSimulate':    pingSimulate(); break;
        case 'portScan':        portScan(); break;
        default: echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/* ───────────── PACKETS ───────────── */
function capturePacket($conn) {
    $protocol = $_POST['protocol'] ?? 'HTTP';
    $method   = $_POST['method']   ?? 'GET';
    $url      = $_POST['url']      ?? '/';
    $srcIP    = $_POST['srcIP']    ?? '192.168.1.10';
    $destIP   = $_POST['destIP']   ?? '142.250.190.46';

    $protoMap = [
        'HTTP'  => ['transport'=>'TCP','destPort'=>80, 'flags'=>'PSH,ACK','size'=>rand(200,1500)],
        'HTTPS' => ['transport'=>'TCP','destPort'=>443,'flags'=>'PSH,ACK','size'=>rand(300,1500)],
        'DNS'   => ['transport'=>'UDP','destPort'=>53, 'flags'=>'—',     'size'=>rand(60,120)],
        'FTP'   => ['transport'=>'TCP','destPort'=>21, 'flags'=>'SYN',   'size'=>rand(80,800)],
        'SSH'   => ['transport'=>'TCP','destPort'=>22, 'flags'=>'SYN',   'size'=>rand(80,600)],
        'SMTP'  => ['transport'=>'TCP','destPort'=>25, 'flags'=>'PSH,ACK','size'=>rand(500,2000)],
        'ICMP'  => ['transport'=>'—',  'destPort'=>0,  'flags'=>'—',     'size'=>64],
        'Telnet'=> ['transport'=>'TCP','destPort'=>23, 'flags'=>'PSH',   'size'=>rand(80,400)],
        'POP3'  => ['transport'=>'TCP','destPort'=>110,'flags'=>'PSH,ACK','size'=>rand(200,800)],
        'IMAP'  => ['transport'=>'TCP','destPort'=>143,'flags'=>'PSH,ACK','size'=>rand(300,1200)],
    ];
    $cfg = $protoMap[$protocol] ?? $protoMap['HTTP'];

    $srcPort = $protocol === 'ICMP' ? 0 : rand(49152, 65535);
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0', 0, 100);

    $stmt = $conn->prepare("
        INSERT INTO Packets
          (AppProtocol, HttpMethod, UrlPath, UserAgent,
           TransportProto, SourcePort, DestPort, TcpFlags,
           SourceIP, DestIP, TTL,
           SourceMAC, DestMAC, EtherType,
           Medium, LinkSpeed,
           PacketSize, Direction, Status, Notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $tp = $cfg['transport']; $dp = $cfg['destPort']; $fl = $cfg['flags']; $sz = $cfg['size'];
    $sm = randomMAC(); $dm = randomMAC();
    $et = '0x0800'; $md = 'Copper'; $ls = '1 Gbps'; $dir = 'Outbound'; $st = 'Delivered';
    $notes = "Captured by Inspector — $protocol $method"; $ttl = 64;

    $stmt->bind_param('sssssiisssississiisss',
        $protocol, $method, $url, $userAgent,
        $tp, $srcPort, $dp, $fl,
        $srcIP, $destIP, $ttl,
        $sm, $dm, $et,
        $md, $ls,
        $sz, $dir, $st, $notes
    );

    if ($stmt->execute()) {
        echo json_encode(['success'=>true,'packetID'=>$conn->insert_id]);
    } else {
        echo json_encode(['success'=>false,'message'=>$stmt->error]);
    }
}

function getPacket($conn) {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) { echo json_encode(['success'=>false]); return; }
    $stmt = $conn->prepare("SELECT * FROM Packets WHERE PacketID = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $r = $stmt->get_result();
    if ($row = $r->fetch_assoc()) echo json_encode(['success'=>true,'packet'=>$row]);
    else echo json_encode(['success'=>false,'message'=>'Not found']);
}

function getPackets($conn) {
    $limit = (int)($_GET['limit'] ?? 100);
    $r = $conn->query("SELECT * FROM Packets ORDER BY CapturedAt DESC LIMIT $limit");
    $rows = []; while ($row = $r->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success'=>true,'packets'=>$rows]);
}

function getStats($conn) {
    $stats = [];
    $r = $conn->query("SELECT COUNT(*) c FROM Packets"); $stats['total'] = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) c FROM Packets WHERE CapturedAt >= NOW() - INTERVAL 1 HOUR"); $stats['lastHour'] = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(DISTINCT SourceIP) c FROM Packets"); $stats['uniqueIPs'] = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT SUM(PacketSize) s FROM Packets"); $stats['totalBytes'] = (int)($r->fetch_assoc()['s'] ?? 0);
    $r = $conn->query("SELECT AppProtocol, COUNT(*) c FROM Packets GROUP BY AppProtocol ORDER BY c DESC LIMIT 1");
    $stats['topProto'] = ($row = $r->fetch_assoc()) ? $row['AppProtocol'] : 'N/A';
    $r = $conn->query("SELECT AppProtocol, COUNT(*) c FROM Packets GROUP BY AppProtocol ORDER BY c DESC");
    $byProto = []; while ($row = $r->fetch_assoc()) $byProto[] = $row;
    $stats['byProto'] = $byProto;
    echo json_encode(['success'=>true,'stats'=>$stats]);
}

function deletePacket($conn) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) $conn->query("DELETE FROM Packets WHERE PacketID = $id");
    echo json_encode(['success'=>true]);
}

function clearAll($conn) {
    $conn->query("TRUNCATE TABLE Packets");
    echo json_encode(['success'=>true]);
}

function autoGenerate($conn) {
    $count = max(1, min(20, (int)($_POST['count'] ?? 5)));
    $protocols = ['HTTP','HTTPS','DNS','FTP','SSH','SMTP','ICMP'];
    $sourceIPs = ['192.168.10.45','10.0.0.12','192.168.1.100','172.16.5.20','192.168.50.30'];
    $destIPs = ['142.250.190.46','8.8.8.8','172.16.1.10','152.199.4.33','93.184.216.34'];
    $methods = ['GET','POST','PUT','QUERY','ECHO'];
    $urls = ['/api/users','/index.html','/login','/static/main.js','/api/data','/'];

    for ($i = 0; $i < $count; $i++) {
        $_POST['protocol'] = $protocols[array_rand($protocols)];
        $_POST['method']   = $methods[array_rand($methods)];
        $_POST['srcIP']    = $sourceIPs[array_rand($sourceIPs)];
        $_POST['destIP']   = $destIPs[array_rand($destIPs)];
        $_POST['url']      = $urls[array_rand($urls)];
        // Inline call (don't echo)
        ob_start(); capturePacket($conn); ob_end_clean();
    }
    echo json_encode(['success'=>true,'generated'=>$count]);
}

/* ───────────── QUIZ ───────────── */
function getQuizQuestions($conn) {
    $r = $conn->query("SELECT QuestionID, Question, OptionA, OptionB, OptionC, OptionD, CorrectAnswer, Explanation, Difficulty, Topic FROM QuizQuestions ORDER BY RAND() LIMIT 10");
    $rows = []; while ($row = $r->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success'=>true,'questions'=>$rows]);
}

/* ───────────── UTILITIES ───────────── */
function subnetCalc() {
    $ip   = $_POST['ip']   ?? '192.168.1.0';
    $cidr = (int)($_POST['cidr'] ?? 24);
    if ($cidr < 0 || $cidr > 32) { echo json_encode(['success'=>false,'message'=>'CIDR must be 0-32']); return; }

    $parts = explode('.', $ip);
    if (count($parts) !== 4) { echo json_encode(['success'=>false,'message'=>'Invalid IP']); return; }
    foreach ($parts as $p) if (!is_numeric($p) || $p < 0 || $p > 255) { echo json_encode(['success'=>false,'message'=>'Invalid IP']); return; }

    $ipLong   = ip2long($ip);
    $maskLong = $cidr === 0 ? 0 : (-1 << (32 - $cidr)) & 0xFFFFFFFF;
    $netLong  = $ipLong & $maskLong;
    $brdLong  = $netLong | (~$maskLong & 0xFFFFFFFF);
    $hosts    = $cidr >= 31 ? 0 : (1 << (32 - $cidr)) - 2;
    $totalIPs = $cidr === 0 ? 4294967296 : (1 << (32 - $cidr));

    echo json_encode([
        'success'   => true,
        'network'   => long2ip($netLong),
        'broadcast' => long2ip($brdLong),
        'mask'      => long2ip($maskLong),
        'wildcard'  => long2ip(~$maskLong & 0xFFFFFFFF),
        'firstHost' => $cidr >= 31 ? '—' : long2ip($netLong + 1),
        'lastHost'  => $cidr >= 31 ? '—' : long2ip($brdLong - 1),
        'hosts'     => $hosts,
        'totalIPs'  => $totalIPs,
        'binary'    => sprintf('%08b.%08b.%08b.%08b', $parts[0], $parts[1], $parts[2], $parts[3]),
        'cidr'      => $cidr,
        'class'     => ipClass($parts[0]),
        'private'   => isPrivate($ipLong)
    ]);
}

function ipClass($firstOctet) {
    if ($firstOctet < 128) return 'A';
    if ($firstOctet < 192) return 'B';
    if ($firstOctet < 224) return 'C';
    if ($firstOctet < 240) return 'D (Multicast)';
    return 'E (Reserved)';
}

function isPrivate($ipLong) {
    if ($ipLong >= ip2long('10.0.0.0')    && $ipLong <= ip2long('10.255.255.255'))  return true;
    if ($ipLong >= ip2long('172.16.0.0')  && $ipLong <= ip2long('172.31.255.255'))  return true;
    if ($ipLong >= ip2long('192.168.0.0') && $ipLong <= ip2long('192.168.255.255')) return true;
    return false;
}

function pingSimulate() {
    $target = $_POST['target'] ?? '8.8.8.8';
    $count  = max(1, min(10, (int)($_POST['count'] ?? 4)));
    $hops = [];
    $base = rand(1, 5);
    $hopList = ['192.168.1.1', '10.0.0.1', '172.16.0.1', '203.0.113.45', '142.250.46.1', $target];
    for ($i = 0; $i < $count; $i++) {
        $hops[] = [
            'seq'     => $i + 1,
            'rtt'     => round($base + (rand(0, 30) / 10), 2),
            'ttl'     => 64 - rand(0, 4),
            'size'    => 64,
            'success' => rand(1, 100) > 5
        ];
        $base += rand(-1, 2);
        if ($base < 1) $base = 1;
    }
    $route = [];
    $hopCount = min(rand(4, 7), count($hopList));
    for ($i = 0; $i < $hopCount; $i++) {
        $route[] = ['hop' => $i + 1, 'ip' => $hopList[$i] ?? '*', 'rtt' => round(rand(10, 200) / 10, 2)];
    }
    echo json_encode(['success'=>true, 'pings'=>$hops, 'route'=>$route, 'target'=>$target]);
}

function portScan() {
    $target = $_POST['target'] ?? '192.168.1.1';
    $commonPorts = [
        21   => ['name'=>'FTP',     'open'=>rand(0,1)],
        22   => ['name'=>'SSH',     'open'=>1],
        23   => ['name'=>'Telnet',  'open'=>0],
        25   => ['name'=>'SMTP',    'open'=>rand(0,1)],
        53   => ['name'=>'DNS',     'open'=>1],
        80   => ['name'=>'HTTP',    'open'=>1],
        110  => ['name'=>'POP3',    'open'=>0],
        143  => ['name'=>'IMAP',    'open'=>rand(0,1)],
        443  => ['name'=>'HTTPS',   'open'=>1],
        445  => ['name'=>'SMB',     'open'=>0],
        3306 => ['name'=>'MySQL',   'open'=>rand(0,1)],
        3389 => ['name'=>'RDP',     'open'=>0],
        8080 => ['name'=>'HTTP-Alt','open'=>rand(0,1)],
    ];
    $results = [];
    foreach ($commonPorts as $port => $info) {
        $results[] = [
            'port'    => $port,
            'service' => $info['name'],
            'status'  => $info['open'] ? 'open' : 'closed',
            'latency' => $info['open'] ? round(rand(1,30) / 10, 2) . 'ms' : '—'
        ];
    }
    echo json_encode(['success'=>true, 'target'=>$target, 'results'=>$results]);
}

/* ───────────── HELPERS ───────────── */
function randomMAC() {
    $hex = '0123456789ABCDEF'; $mac = '';
    for ($i = 0; $i < 6; $i++) { if ($i) $mac .= ':'; $mac .= $hex[rand(0,15)] . $hex[rand(0,15)]; }
    return $mac;
}

$conn->close();
?>
