<?php
session_start();
include 'config.php';
include 'logger.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT UserID, Username, Role FROM Users WHERE Username = ? AND Password = ?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $_SESSION['user_id']  = $row['UserID'];
    $_SESSION['username'] = $row['Username'];
    $_SESSION['role']     = $row['Role'];

    // ✅ Log successful login
    logActivity($conn, 'User logged in', 'Auth', 'User', $row['UserID'], 'Successful login', 200);

    header("Location: index.php");
    exit();
} else {
    // ❌ Log failed login attempt (uses provided username, no session)
    $_SESSION['username'] = $username ?: 'Anonymous';
    $_SESSION['role']     = 'Guest';
    logActivity($conn, 'Failed login', 'Security', null, null, "Invalid credentials for username: $username", 401);
    unset($_SESSION['username'], $_SESSION['role']);

    header("Location: login.php?error=1");
    exit();
}

$stmt->close();
$conn->close();
?>
