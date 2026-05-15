<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'osi_inspector');

define('APP_NAME', 'OSI Packet Inspector');
define('APP_VERSION', '2.1');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) throw new Exception('DB Connection Failed: ' . $conn->connect_error);
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    die('<div style="background:#fef2f2;color:#991b1b;padding:30px;font-family:monospace;border:1px solid #fca5a5;margin:30px;border-radius:8px;">
            <strong>⚠ Database Error</strong><br><br>'
            . htmlspecialchars($e->getMessage()) .
        '<br><br><span style="color:#7f1d1d;">Did you import database.sql AND auth_migration.sql in phpMyAdmin?</span>
        </div>');
}

// ─────────────────────────────────────────────
// Auto-seed default users on first boot
// ─────────────────────────────────────────────
seedDefaultUsers($conn);

function seedDefaultUsers($conn) {
    $r = $conn->query("SELECT COUNT(*) c FROM Users");
    if (!$r) return; // Users table might not exist yet
    $count = (int) $r->fetch_assoc()['c'];
    if ($count > 0) return; // Already seeded

    $defaults = [
        ['admin', 'admin@osi.local', 'admin123', 'Administrator', 'Admin'],
        ['demo',  'demo@osi.local',  'demo123',  'Demo User',     'User'],
    ];

    $stmt = $conn->prepare("INSERT INTO Users (Username, Email, Password, FullName, Role) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaults as $u) {
        $hash = password_hash($u[2], PASSWORD_DEFAULT);
        $stmt->bind_param('sssss', $u[0], $u[1], $hash, $u[3], $u[4]);
        @$stmt->execute();
    }
    $stmt->close();
}

// ─────────────────────────────────────────────
// Helper: get the visiting client's IP
// ─────────────────────────────────────────────
function getClientIP() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if ($ip === '::1') $ip = '127.0.0.1';
    return $ip;
}
?>
