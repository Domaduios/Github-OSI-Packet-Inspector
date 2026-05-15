-- ====================================================
--  OSI PACKET INSPECTOR v2.0 — Database Schema
-- ====================================================

CREATE DATABASE IF NOT EXISTS osi_inspector
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE osi_inspector;

DROP TABLE IF EXISTS QuizAttempts;
DROP TABLE IF EXISTS QuizQuestions;
DROP TABLE IF EXISTS Packets;
DROP TABLE IF EXISTS Protocols;
DROP TABLE IF EXISTS OSILayers;

-- =========================
-- OSI Layers
-- =========================
CREATE TABLE OSILayers (
    LayerNum    INT PRIMARY KEY,
    LayerName   VARCHAR(50)  NOT NULL,
    Purpose     VARCHAR(255),
    DataUnit    VARCHAR(30),
    Protocols   VARCHAR(255),
    Devices     VARCHAR(255),
    Example     TEXT,
    ColorHex    VARCHAR(10)
);

INSERT INTO OSILayers VALUES
(7, 'Application',  'Provides services directly to user applications and APIs.',
    'Data',    'HTTP, HTTPS, DNS, FTP, SMTP, SSH',  'Web browser, Web server, Email client',
    'When you type a URL in the browser, the Application Layer creates an HTTP GET request.', '#ef4444'),
(6, 'Presentation', 'Translates, encrypts, and compresses data.',
    'Data',    'SSL/TLS, JPEG, MPEG, ASCII, Unicode', 'Encryption gateways, Codecs',
    'When you visit an HTTPS site, this layer encrypts the data using TLS.', '#f97316'),
(5, 'Session',      'Establishes, manages, and terminates communication sessions.',
    'Data',    'NetBIOS, RPC, PPTP, SOCKS',          'Session-aware firewalls',
    'When you log into a website, this layer keeps track of your session until you log out.', '#eab308'),
(4, 'Transport',    'Ensures end-to-end delivery, error recovery, and flow control.',
    'Segment', 'TCP, UDP, SCTP',                     'Stateful firewalls, Load balancers',
    'TCP guarantees in-order delivery; UDP is faster but offers no guarantees.', '#22c55e'),
(3, 'Network',      'Logical addressing and routing between networks.',
    'Packet',  'IP, ICMP, IPsec, OSPF, BGP, ARP',    'Router, Layer 3 Switch',
    'The Network Layer decides which path the packet takes to reach 142.250.190.46.', '#06b6d4'),
(2, 'Data Link',    'Physical addressing using MAC, framing, and error detection.',
    'Frame',   'Ethernet, PPP, HDLC, Wi-Fi (802.11)','Switch, Bridge, NIC',
    'This layer wraps the packet in a frame with source/destination MAC addresses.', '#3b82f6'),
(1, 'Physical',     'Transmits raw bits over the physical medium.',
    'Bits',    'Ethernet cables, Fiber optic, Radio waves', 'Hub, Repeater, Cables, NIC',
    'Bits travel as electrical signals (copper), light pulses (fiber), or radio waves.', '#a855f7');

-- =========================
-- Protocols cheatsheet
-- =========================
CREATE TABLE Protocols (
    ProtocolID   INT PRIMARY KEY AUTO_INCREMENT,
    Name         VARCHAR(20)  NOT NULL,
    LayerNum     INT          NOT NULL,
    Port         VARCHAR(20),
    Transport    VARCHAR(10),
    Description  TEXT,
    UseCase      VARCHAR(255)
);

INSERT INTO Protocols (Name, LayerNum, Port, Transport, Description, UseCase) VALUES
('HTTP',      7, '80',     'TCP', 'HyperText Transfer Protocol — unencrypted web pages.',          'Web browsing (insecure)'),
('HTTPS',     7, '443',    'TCP', 'HTTP over TLS — encrypted web traffic.',                       'Secure web browsing'),
('DNS',       7, '53',     'UDP', 'Domain Name System — translates domain names to IP addresses.','Resolving google.com → 142.250.190.46'),
('FTP',       7, '21',     'TCP', 'File Transfer Protocol — uploads and downloads files.',        'File transfers'),
('SSH',       7, '22',     'TCP', 'Secure Shell — encrypted remote command-line access.',         'Remote server admin'),
('Telnet',    7, '23',     'TCP', 'Unencrypted remote terminal — legacy, insecure.',              'Legacy device access'),
('SMTP',      7, '25',     'TCP', 'Simple Mail Transfer — sends email.',                          'Sending mail'),
('POP3',      7, '110',    'TCP', 'Post Office Protocol — downloads email.',                      'Receiving mail'),
('IMAP',      7, '143',    'TCP', 'Internet Message Access Protocol — manages mail on server.',   'Modern mail clients'),
('DHCP',      7, '67/68',  'UDP', 'Dynamic Host Configuration — assigns IPs automatically.',      'Auto-IP on Wi-Fi'),
('TLS',       6, '—',      '—',   'Transport Layer Security — encrypts data in transit.',         'HTTPS, secure email'),
('TCP',       4, '—',      '—',   'Reliable, connection-oriented, with retransmission.',          'Web, email, file transfer'),
('UDP',       4, '—',      '—',   'Fast, connectionless, no delivery guarantees.',                'Streaming, gaming, DNS'),
('IP',        3, '—',      '—',   'Internet Protocol — logical addressing and routing.',          'All internet traffic'),
('ICMP',      3, '—',      '—',   'Used by ping and traceroute for diagnostics.',                 'Network troubleshooting'),
('ARP',       2, '—',      '—',   'Address Resolution Protocol — IP to MAC mapping.',             'Local network discovery'),
('Ethernet',  2, '—',      '—',   'Most common Layer 2 protocol for wired LANs.',                 'All wired networks'),
('Wi-Fi',     2, '—',      '—',   'IEEE 802.11 — wireless Layer 2.',                              'Wireless networks');

-- =========================
-- Packet Capture Log
-- =========================
CREATE TABLE Packets (
    PacketID         INT PRIMARY KEY AUTO_INCREMENT,
    AppProtocol      VARCHAR(20)  DEFAULT 'HTTP',
    HttpMethod       VARCHAR(10)  DEFAULT 'GET',
    UrlPath          VARCHAR(255) DEFAULT '/',
    UserAgent        VARCHAR(120) DEFAULT 'Mozilla/5.0',
    TransportProto   VARCHAR(10)  DEFAULT 'TCP',
    SourcePort       INT          DEFAULT 0,
    DestPort         INT          DEFAULT 80,
    TcpFlags         VARCHAR(20)  DEFAULT 'PSH,ACK',
    SourceIP         VARCHAR(45),
    DestIP           VARCHAR(45),
    TTL              INT          DEFAULT 64,
    IpVersion        INT          DEFAULT 4,
    SourceMAC        VARCHAR(17),
    DestMAC          VARCHAR(17),
    EtherType        VARCHAR(10)  DEFAULT '0x0800',
    Medium           VARCHAR(20)  DEFAULT 'Copper',
    LinkSpeed        VARCHAR(20)  DEFAULT '1 Gbps',
    PacketSize       INT          DEFAULT 64,
    Direction        VARCHAR(10)  DEFAULT 'Outbound',
    Status           VARCHAR(20)  DEFAULT 'Delivered',
    Notes            VARCHAR(255),
    CapturedAt       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_pkt_time ON Packets(CapturedAt);
CREATE INDEX idx_pkt_src  ON Packets(SourceIP);
CREATE INDEX idx_pkt_pro  ON Packets(AppProtocol);

-- =========================
-- Quiz Questions
-- =========================
CREATE TABLE QuizQuestions (
    QuestionID    INT PRIMARY KEY AUTO_INCREMENT,
    Question      TEXT NOT NULL,
    OptionA       VARCHAR(255),
    OptionB       VARCHAR(255),
    OptionC       VARCHAR(255),
    OptionD       VARCHAR(255),
    CorrectAnswer CHAR(1),
    Explanation   TEXT,
    Difficulty    VARCHAR(10) DEFAULT 'Easy',
    Topic         VARCHAR(50)
);

INSERT INTO QuizQuestions (Question, OptionA, OptionB, OptionC, OptionD, CorrectAnswer, Explanation, Difficulty, Topic) VALUES
('Which OSI layer is responsible for logical addressing (IP)?', 'Layer 2 (Data Link)', 'Layer 3 (Network)', 'Layer 4 (Transport)', 'Layer 7 (Application)', 'B', 'The Network Layer (L3) handles logical addressing using IP addresses and routing.', 'Easy', 'OSI Layers'),
('Which protocol uses port 443?', 'HTTP', 'FTP', 'HTTPS', 'SSH', 'C', 'HTTPS uses TCP port 443 for encrypted web traffic.', 'Easy', 'Ports'),
('What is the data unit at the Transport Layer?', 'Frame', 'Packet', 'Segment', 'Bits', 'C', 'Layer 4 calls its data unit a Segment (TCP) or Datagram (UDP).', 'Medium', 'OSI Layers'),
('Which protocol is connectionless?', 'TCP', 'UDP', 'HTTP', 'FTP', 'B', 'UDP is connectionless and offers no delivery guarantees, unlike TCP.', 'Easy', 'Transport'),
('A MAC address operates at which layer?', 'Layer 1', 'Layer 2', 'Layer 3', 'Layer 4', 'B', 'MAC addresses are physical addresses at Layer 2 (Data Link).', 'Easy', 'OSI Layers'),
('What does TTL stand for in IP?', 'Total Transmission Length', 'Time To Live', 'Transport Type Layer', 'Trace To Localhost', 'B', 'TTL = Time To Live. It limits how many hops a packet can travel.', 'Medium', 'Network'),
('Which protocol is used by ping?', 'TCP', 'UDP', 'ICMP', 'ARP', 'C', 'Ping uses ICMP Echo Request/Reply messages for diagnostics.', 'Easy', 'Network'),
('What does DHCP do?', 'Resolves domain names', 'Assigns IP addresses automatically', 'Encrypts traffic', 'Routes packets', 'B', 'DHCP (Dynamic Host Configuration Protocol) auto-assigns IPs to devices.', 'Easy', 'Application'),
('Which device operates at Layer 3?', 'Hub', 'Switch', 'Router', 'Repeater', 'C', 'Routers operate at Layer 3 — they read IP addresses and route packets.', 'Easy', 'Devices'),
('A subnet mask of /24 means how many host bits?', '6', '8', '10', '24', 'B', '/24 = 24 network bits, leaving 32-24 = 8 host bits (256 addresses).', 'Hard', 'Subnetting'),
('Which OSI layer encrypts data?', 'Layer 4', 'Layer 5', 'Layer 6', 'Layer 7', 'C', 'Layer 6 (Presentation) handles encryption, encoding, and compression.', 'Medium', 'OSI Layers'),
('What is the well-known port for SMTP?', '21', '22', '25', '53', 'C', 'SMTP uses TCP port 25 for sending mail.', 'Easy', 'Ports'),
('TCP three-way handshake order is:', 'SYN, ACK, FIN', 'SYN, SYN-ACK, ACK', 'ACK, SYN, FIN', 'FIN, ACK, SYN', 'B', 'TCP handshake: client sends SYN, server replies SYN-ACK, client confirms with ACK.', 'Medium', 'Transport'),
('Which protocol maps IP to MAC?', 'DNS', 'DHCP', 'ARP', 'ICMP', 'C', 'ARP (Address Resolution Protocol) maps IP addresses to MAC addresses on a LAN.', 'Medium', 'Network'),
('Maximum size of an IPv4 address?', '16 bits', '32 bits', '64 bits', '128 bits', 'B', 'IPv4 is 32 bits (4 octets). IPv6 is 128 bits.', 'Easy', 'Network');

-- =========================
-- Sample packets
-- =========================
INSERT INTO Packets
(AppProtocol, HttpMethod, UrlPath, TransportProto, SourcePort, DestPort, TcpFlags, SourceIP, DestIP, SourceMAC, DestMAC, PacketSize, Direction, Status, Notes, CapturedAt) VALUES
('HTTPS','GET','/api/stats','TCP',52431,443,'SYN','192.168.10.45','142.250.190.46','00:1A:2B:3C:4D:5E','00:50:56:8C:17:91',74,'Outbound','Delivered','Initial handshake', NOW() - INTERVAL 30 MINUTE),
('HTTPS','GET','/api/stats','TCP',443,52431,'SYN,ACK','142.250.190.46','192.168.10.45','00:50:56:8C:17:91','00:1A:2B:3C:4D:5E',74,'Inbound','Delivered','Handshake response', NOW() - INTERVAL 30 MINUTE),
('DNS', 'QUERY','google.com','UDP',58220,53,'—','192.168.10.45','8.8.8.8','00:1A:2B:3C:4D:5E','00:1A:2B:FF:00:01',82,'Outbound','Delivered','DNS lookup', NOW() - INTERVAL 25 MINUTE),
('HTTP','POST','/api/login','TCP',52440,80,'PSH,ACK','10.0.0.12','172.16.1.10','AA:BB:CC:11:22:33','00:50:56:8C:17:91',512,'Outbound','Delivered','Login request', NOW() - INTERVAL 15 MINUTE),
('HTTP','GET','/api/users','TCP',52441,80,'ACK','10.0.0.12','172.16.1.10','AA:BB:CC:11:22:33','00:50:56:8C:17:91',1420,'Inbound','Delivered','Data response', NOW() - INTERVAL 12 MINUTE),
('ICMP','ECHO','ping','—',0,0,'—','192.168.10.45','8.8.8.8','00:1A:2B:3C:4D:5E','00:1A:2B:FF:00:01',64,'Outbound','Delivered','Ping request', NOW() - INTERVAL 8 MINUTE),
('SSH','SYN','/connect','TCP',54331,22,'SYN','192.168.1.100','192.168.1.10','00:1A:2B:3C:4D:5E','00:1A:2B:11:22:33',60,'Outbound','Delivered','SSH connection', NOW() - INTERVAL 7 MINUTE),
('FTP','GET','/files/report.pdf','TCP',55021,21,'PSH,ACK','192.168.1.50','192.168.1.20','AA:BB:CC:DD:EE:FF','00:50:56:8C:17:91',1500,'Outbound','Delivered','File download', NOW() - INTERVAL 5 MINUTE),
('SMTP','SEND','/mail','TCP',56012,25,'PSH','10.0.0.55','172.217.16.46','11:22:33:44:55:66','00:1A:2B:33:44:55',890,'Outbound','Delivered','Email sent', NOW() - INTERVAL 3 MINUTE),
('ICMP','REPLY','ping','—',0,0,'—','8.8.8.8','192.168.10.45','00:1A:2B:FF:00:01','00:1A:2B:3C:4D:5E',64,'Inbound','Delivered','Pong reply', NOW() - INTERVAL 2 MINUTE);
