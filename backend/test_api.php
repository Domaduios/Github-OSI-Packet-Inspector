<?php
header('Content-Type: application/json');
include 'config.php';

$result = $conn->query("SELECT StudentID, Name, Email, Phone, Department, Year, IPAddress FROM Students LIMIT 5");
$students = [];
while($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode(['success' => true, 'students' => $students, 'count' => count($students)]);
?>