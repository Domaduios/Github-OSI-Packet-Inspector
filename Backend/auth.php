<?php
session_start();
include 'config.php';

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    handleLogin($conn);
} elseif ($action === 'register') {
    handleRegister($conn);
} else {
    header('Location: login.php');
    exit();
}

/* ─────────── LOGIN ─────────── */
function handleLogin($conn) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        header('Location: login.php?error=' . urlencode('Please fill in all fields'));
        exit();
    }

    $stmt = $conn->prepare("SELECT UserID, Username, Email, Password, FullName, Role FROM Users WHERE Username = ? OR Email = ?");
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['Password'])) {
            $_SESSION['user_id']  = $row['UserID'];
            $_SESSION['username'] = $row['Username'];
            $_SESSION['email']    = $row['Email'];
            $_SESSION['fullname'] = $row['FullName'];
            $_SESSION['role']     = $row['Role'];

            // Update last login
            $ip = getClientIP();
            $stmt2 = $conn->prepare("UPDATE Users SET LastLogin = NOW(), LastIP = ? WHERE UserID = ?");
            $stmt2->bind_param('si', $ip, $row['UserID']);
            $stmt2->execute();

            header('Location: index.php');
            exit();
        }
    }

    header('Location: login.php?error=' . urlencode('Invalid username or password'));
    exit();
}

/* ─────────── REGISTER ─────────── */
function handleRegister($conn) {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    $errors = [];
    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (strlen($password) < 4) $errors[] = 'Password must be at least 4 characters';
    if ($password !== $confirm) $errors[] = 'Passwords do not match';

    if (!empty($errors)) {
        header('Location: register.php?error=' . urlencode(implode(', ', $errors)));
        exit();
    }

    // Check uniqueness
    $stmt = $conn->prepare("SELECT UserID FROM Users WHERE Username = ? OR Email = ?");
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        header('Location: register.php?error=' . urlencode('Username or email already taken'));
        exit();
    }
    $stmt->close();

    // Insert
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'User';
    $stmt = $conn->prepare("INSERT INTO Users (Username, Email, Password, FullName, Role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $username, $email, $hash, $fullname, $role);

    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        $_SESSION['user_id']  = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['email']    = $email;
        $_SESSION['fullname'] = $fullname;
        $_SESSION['role']     = $role;

        header('Location: index.php?welcome=1');
        exit();
    } else {
        header('Location: register.php?error=' . urlencode('Registration failed: ' . $conn->error));
        exit();
    }
}
?>
