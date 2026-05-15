<?php
session_start();
include 'config.php';
include 'logger.php';

function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))  $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))      $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))    $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))           $ipaddress = $_SERVER['REMOTE_ADDR'];
    else                                              $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

$clientIP = getClientIP();
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$email    = trim($_POST['email']    ?? '');
$role     = trim($_POST['role']     ?? '');

$errors = [];

if (empty($username) || strlen($username) < 3)               $errors[] = "Username must be at least 3 characters";
if (empty($password) || strlen($password) < 4)               $errors[] = "Password must be at least 4 characters";
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
if (empty($role) || !in_array($role, ['Student', 'Teacher', 'Staff'])) $errors[] = "Valid role is required";

if (!empty($errors)) {
    // Log failed validation
    $_SESSION['username'] = $username ?: 'Anonymous';
    $_SESSION['role']     = 'Guest';
    logActivity($conn, 'Registration failed', 'Security', null, null, 'Validation: ' . implode(', ', $errors), 400);
    unset($_SESSION['username'], $_SESSION['role']);

    header("Location: register.php?error=1&message=" . urlencode(implode(", ", $errors)));
    exit();
}

$check = $conn->prepare("SELECT UserID FROM Users WHERE Username = ?");
$check->bind_param("s", $username);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $_SESSION['username'] = $username;
    $_SESSION['role']     = 'Guest';
    logActivity($conn, 'Registration failed', 'Security', null, null, 'Username already exists', 409);
    unset($_SESSION['username'], $_SESSION['role']);

    header("Location: register.php?error=1&message=Username already exists");
    exit();
}
$check->close();

$stmt = $conn->prepare("INSERT INTO Users (Username, Password, Role, Email, IPAddress, RegistrationDate) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssss", $username, $password, $role, $email, $clientIP);

if ($stmt->execute()) {
    $newId = $conn->insert_id;
    $_SESSION['username'] = $username;
    $_SESSION['role']     = $role;
    logActivity($conn, 'User registered', 'Auth', 'User', $newId, "New $role account created", 201);
    unset($_SESSION['username'], $_SESSION['role']);

    header("Location: register.php?success=1");
} else {
    logActivity($conn, 'Registration failed', 'Security', null, null, 'DB error: ' . $conn->error, 500);
    header("Location: register.php?error=1&message=" . urlencode($conn->error));
}

$stmt->close();
$conn->close();
?>
