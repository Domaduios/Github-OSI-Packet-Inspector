<?php
/**
 * ===========================================================
 *  ACTIVITY LOGGER
 * ===========================================================
 *  Records every meaningful action the user performs.
 *  Captures: who, what, when, where (IP), how (HTTP method).
 *
 *  Usage:
 *      include_once 'logger.php';
 *      logActivity($conn, 'Added student', 'Create', 'Student', $studentId);
 *
 *  Make sure ActivityLog table exists (run activity_log.sql).
 * ===========================================================
 */

if (!function_exists('getLoggerClientIP')) {
    function getLoggerClientIP() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $first = trim($parts[0]);
            if (filter_var($first, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) $ip = $first;
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
            if ($ip === '::1') $ip = '127.0.0.1';
            elseif (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ip = '192.168.1.' . (abs(crc32($ip)) % 254 + 1);
            }
        }
        return $ip ?: '0.0.0.0';
    }
}

/**
 * Log an activity entry.
 *
 * @param mysqli $conn       Database connection
 * @param string $action     Short description, e.g. "Added student"
 * @param string $category   One of: Auth | Create | Update | Delete | View | Network | Security | System
 * @param string|null $targetType   e.g. "Student", "Course", "Grade"
 * @param string|null $targetId     Affected record ID
 * @param string|null $details      Optional extra info
 * @param int    $status     HTTP-style status code (200 success, 401 auth fail, etc.)
 */
function logActivity($conn, $action, $category = 'General', $targetType = null, $targetId = null, $details = null, $status = 200) {
    if (!$conn) return false;

    // Check session for who is doing the action
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $username = $_SESSION['username'] ?? 'Anonymous';
    $role     = $_SESSION['role']     ?? 'Guest';

    $ip        = getLoggerClientIP();
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 250);
    $method    = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    $stmt = $conn->prepare("
        INSERT INTO ActivityLog
            (Username, UserRole, Action, Category, TargetType, TargetID, IPAddress, UserAgent, Method, StatusCode, Details)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) return false;

    $stmt->bind_param(
        "sssssssssis",
        $username, $role, $action, $category,
        $targetType, $targetId, $ip, $userAgent,
        $method, $status, $details
    );
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
