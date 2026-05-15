<?php
// Development-friendly error reporting (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);
/**
 * =============================================
 * STUDENT MANAGEMENT SYSTEM - NETWORK CONFIG
 * =============================================
 * 
 * This file represents the APPLICATION LAYER (Layer 7)
 * The PHP application runs on Web Server (VLAN 50)
 * 
 * Network Details:
 * - Web Server IP: 172.16.1.10/24
 * - Database Server: 172.16.1.20/24 (localhost for single server)
 * - Gateway: 172.16.1.1
 * - DNS Server: 8.8.8.8 (or internal DNS 172.16.1.5)
 * 
 * Communication Flow:
 * Client (PC in VLAN 10/20/30/40) 
 *    → HTTP Request (Port 80)
 *    → Router (Inter-VLAN Routing)
 *    → Web Server (172.16.1.10)
 *    → PHP Execution
 *    → MySQL Query
 *    → JSON Response back to Client
 */

// Database Configuration (local dev)
define('DB_HOST', 'localhost');  // Use 127.0.0.1 if localhost fails
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_management');

// Application Configuration
define('APP_NAME', 'Student Management System');
define('APP_URL', 'http://sms.university.edu');  // DNS will resolve this

// Network Detection
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
$serverPort = $_SERVER['SERVER_PORT'] ?? 80;

// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Database Connection Failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    
    // Log connection from which VLAN/Client (for network tracking)
    error_log("Connection from IP: $clientIP using $protocol on port $serverPort");
    
} catch (Exception $e) {
    // Show a clear error message for debugging (friendly text)
    die('Database Connection Failed: ' . $e->getMessage());
}
?>