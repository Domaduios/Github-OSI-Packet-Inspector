<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
include 'config.php';
include 'logger.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
    switch($action) {
        case 'getDashboardStats': getDashboardStats($conn); break;
        case 'getTopStudents': getTopStudents($conn); break;
        case 'getStudents': getStudents($conn); break;
        case 'getStudentsForSelect': getStudentsForSelect($conn); break;
        case 'addStudent': addStudent($conn, $_POST); break;
        case 'deleteStudent': deleteStudent($conn, $_POST); break;
        case 'getCourses': getCourses($conn); break;
        case 'getCoursesForSelect': getCoursesForSelect($conn); break;
        case 'addCourse': addCourse($conn, $_POST); break;
        case 'getEnrollments': getEnrollments($conn); break;
        case 'getEnrollmentsForSelect': getEnrollmentsForSelect($conn); break;
        case 'addEnrollment': addEnrollment($conn, $_POST); break;
        case 'deleteEnrollment': deleteEnrollment($conn, $_POST); break;
        // ── COURSE ENROLLMENTS (NEW) ──
        case 'getCourseEnrollmentsList': getCourseEnrollmentsList($conn); break;
        case 'getStudentsInCourse': getStudentsInCourse($conn); break;
        case 'getCoursesForStudent': getCoursesForStudent($conn); break;
        case 'removeStudentFromCourse': removeStudentFromCourse($conn, $_POST); break;
        case 'getGrades': getGrades($conn); break;
        case 'addGrade': addGrade($conn, $_POST); break;
        case 'getAttendance': getAttendance($conn); break;
        case 'addAttendance': addAttendance($conn, $_POST); break;
        // ── NETWORK MONITORING ──
        case 'getNetworkStats':   getNetworkStats($conn);   break;
        case 'getActivityLog':    getActivityLog($conn);    break;
        case 'getRecentActivity': getRecentActivity($conn); break;
        case 'getCategoryBreakdown': getCategoryBreakdown($conn); break;
        case 'getTopIPs': getTopIPs($conn); break;
        case 'getActivityTimeline': getActivityTimeline($conn); break;
        default: echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// ==================== GET CLIENT IP (NETWORK CONCEPT) ====================
// Get client IP address (Network concept) - Force IPv4
function getClientIP() {
    $ipaddress = '';
    
    // Check for IPv4 specifically
    if (isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } 
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Take first IP from X-Forwarded-For (it can be comma-separated)
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $first_ip = trim($ips[0]);
        if(filter_var($first_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipaddress = $first_ip;
        }
    }
    else if(isset($_SERVER['HTTP_X_FORWARDED']) && filter_var($_SERVER['HTTP_X_FORWARDED'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    }
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    else if(isset($_SERVER['HTTP_FORWARDED']) && filter_var($_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    }
    else if(isset($_SERVER['REMOTE_ADDR'])) {
        // Convert IPv6 localhost (::1) to IPv4 (127.0.0.1)
        if($_SERVER['REMOTE_ADDR'] == '::1') {
            $ipaddress = '127.0.0.1';
        }
        // Check if it's a valid IPv4
        else if(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }
        // If it's IPv6 but not localhost, convert to a simulated IPv4
        else if(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Generate a consistent simulated IPv4 from IPv6
            $ipaddress = '192.168.1.' . (abs(crc32($_SERVER['REMOTE_ADDR'])) % 254 + 1);
        }
        else {
            $ipaddress = '127.0.0.1';
        }
    }
    else {
        $ipaddress = '127.0.0.1';
    }
    
    return $ipaddress;
}
// ==================== DASHBOARD ====================
function getDashboardStats($conn) {
    $totalStudents = $conn->query("SELECT COUNT(*) as count FROM Students")->fetch_assoc()['count'];
    $totalCourses = $conn->query("SELECT COUNT(*) as count FROM Courses")->fetch_assoc()['count'];
    $activeEnrollments = $conn->query("SELECT COUNT(*) as count FROM Enrollments WHERE Status='Active'")->fetch_assoc()['count'];
    $avgResult = $conn->query("SELECT AVG(GPA) as avg FROM Grades");
    $averageGPA = ($avgResult && $row = $avgResult->fetch_assoc()) ? $row['avg'] : 0;

    echo json_encode(['success' => true, 'totalStudents' => $totalStudents, 'totalCourses' => $totalCourses, 'activeEnrollments' => $activeEnrollments, 'averageGPA' => round($averageGPA, 2)]);
}

function getTopStudents($conn) {
    $query = "SELECT s.StudentID, s.Name, s.Department, s.Year, ROUND(AVG(g.GPA), 2) as GPA FROM Students s LEFT JOIN Enrollments e ON s.StudentID = e.StudentID LEFT JOIN Grades g ON e.EnrollmentID = g.EnrollmentID WHERE g.GPA IS NOT NULL GROUP BY s.StudentID ORDER BY GPA DESC LIMIT 5";
    $result = $conn->query($query);
    $students = [];
    while($row = $result->fetch_assoc()) $students[] = $row;
    echo json_encode(['success' => true, 'students' => $students]);
}

// ==================== STUDENTS ====================
function getStudents($conn) {
    $result = $conn->query("SELECT StudentID, Name, Email, Phone, Department, Year, IPAddress FROM Students ORDER BY Name ASC");
    $students = [];
    while($row = $result->fetch_assoc()) $students[] = $row;
    echo json_encode(['success' => true, 'students' => $students]);
}

function getStudentsForSelect($conn) {
    $result = $conn->query("SELECT StudentID, Name FROM Students ORDER BY Name ASC");
    $students = [];
    while($row = $result->fetch_assoc()) $students[] = $row;
    echo json_encode(['success' => true, 'students' => $students]);
}

function addStudent($conn, $data) {
    $clientIP = getClientIP();
    $name = $conn->real_escape_string($data['name'] ?? '');
    $email = $conn->real_escape_string($data['email'] ?? '');
    $phone = $conn->real_escape_string($data['phone'] ?? '');
    $department = $conn->real_escape_string($data['department'] ?? 'Computer Science');
    $year = isset($data['year']) ? (int)$data['year'] : 1;
    $dob = $conn->real_escape_string($data['dob'] ?? '');
    $address = $conn->real_escape_string($data['address'] ?? '');

    if (empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Name and Email are required']);
        return;
    }

    $query = "INSERT INTO Students (Name, Email, Phone, Department, Year, DateOfBirth, Address, IPAddress, EnrollmentDate) 
              VALUES ('$name', '$email', '$phone', '$department', $year, '$dob', '$address', '$clientIP', CURRENT_DATE)";

    if ($conn->query($query)) {
        $newId = $conn->insert_id;
        logActivity($conn, 'Added student', 'Create', 'Student', $newId, "Name: $name, Email: $email", 201);
        echo json_encode(['success' => true, 'message' => 'Student added successfully from IP: ' . $clientIP]);
    } else {
        logActivity($conn, 'Failed to add student', 'Security', 'Student', null, 'DB error: ' . $conn->error, 500);
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

function deleteStudent($conn, $data) {
    $id = isset($data['id']) ? (int)$data['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
        return;
    }
    $query = "DELETE FROM Students WHERE StudentID = $id";
    if ($conn->query($query)) {
        logActivity($conn, 'Deleted student', 'Delete', 'Student', $id, "Student ID $id removed", 200);
        echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

// ==================== COURSES ====================
function getCourses($conn) {
    $query = "SELECT c.*, COUNT(e.EnrollmentID) as students FROM Courses c LEFT JOIN Enrollments e ON c.CourseID = e.CourseID GROUP BY c.CourseID ORDER BY c.CourseCode ASC";
    $result = $conn->query($query);
    $courses = [];
    while($row = $result->fetch_assoc()) $courses[] = $row;
    echo json_encode(['success' => true, 'courses' => $courses]);
}

function getCoursesForSelect($conn) {
    $result = $conn->query("SELECT CourseID, CourseCode, CourseName FROM Courses ORDER BY CourseCode ASC");
    $courses = [];
    while($row = $result->fetch_assoc()) $courses[] = $row;
    echo json_encode(['success' => true, 'courses' => $courses]);
}

function addCourse($conn, $data) {
    $code = $conn->real_escape_string($data['code'] ?? '');
    $name = $conn->real_escape_string($data['name'] ?? '');
    $department = $conn->real_escape_string($data['department'] ?? 'Computer Science');
    $credits = isset($data['credits']) ? (int)$data['credits'] : 3;
    $semester = $conn->real_escape_string($data['semester'] ?? '');
    $instructor = $conn->real_escape_string($data['instructor'] ?? '');

    if (empty($code) || empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Course Code and Name are required']);
        return;
    }

    $query = "INSERT INTO Courses (CourseCode, CourseName, Department, Credits, Semester, InstructorName) VALUES ('$code', '$name', '$department', $credits, '$semester', '$instructor')";

    if ($conn->query($query)) {
        $newId = $conn->insert_id;
        logActivity($conn, 'Added course', 'Create', 'Course', $newId, "Code: $code, Name: $name", 201);
        echo json_encode(['success' => true, 'message' => 'Course added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

// ==================== ENROLLMENTS ====================
function getEnrollments($conn) {
    $query = "SELECT e.EnrollmentID, s.StudentID, s.Name as StudentName, c.CourseID, c.CourseName, e.EnrollmentDate, e.Status FROM Enrollments e JOIN Students s ON e.StudentID = s.StudentID JOIN Courses c ON e.CourseID = c.CourseID ORDER BY e.EnrollmentDate DESC";
    $result = $conn->query($query);
    $enrollments = [];
    while($row = $result->fetch_assoc()) $enrollments[] = $row;
    echo json_encode(['success' => true, 'enrollments' => $enrollments]);
}

function getEnrollmentsForSelect($conn) {
    $query = "SELECT e.EnrollmentID, CONCAT(s.Name, ' - ', c.CourseName) as label FROM Enrollments e JOIN Students s ON e.StudentID = s.StudentID JOIN Courses c ON e.CourseID = c.CourseID ORDER BY s.Name ASC";
    $result = $conn->query($query);
    $enrollments = [];
    while($row = $result->fetch_assoc()) $enrollments[] = $row;
    echo json_encode(['success' => true, 'enrollments' => $enrollments]);
}

function addEnrollment($conn, $data) {
    $student_id = isset($data['student_id']) ? (int)$data['student_id'] : 0;
    $course_id = isset($data['course_id']) ? (int)$data['course_id'] : 0;
    $status = $conn->real_escape_string($data['status'] ?? 'Active');

    if ($student_id <= 0 || $course_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Student and Course are required']);
        return;
    }

    $query = "INSERT INTO Enrollments (StudentID, CourseID, Status, EnrollmentDate) VALUES ($student_id, $course_id, '$status', CURRENT_DATE)";

    if ($conn->query($query)) {
        $newId = $conn->insert_id;
        logActivity($conn, 'Added enrollment', 'Create', 'Enrollment', $newId, "Student #$student_id → Course #$course_id", 201);
        echo json_encode(['success' => true, 'message' => 'Enrollment added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

function deleteEnrollment($conn, $data) {
    $id = isset($data['id']) ? (int)$data['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid enrollment ID']);
        return;
    }
    $query = "UPDATE Enrollments SET Status = 'Withdrawn' WHERE EnrollmentID = $id";
    if ($conn->query($query)) {
        logActivity($conn, 'Withdrew enrollment', 'Update', 'Enrollment', $id, "Enrollment $id status set to Withdrawn", 200);
        echo json_encode(['success' => true, 'message' => 'Enrollment withdrawn successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

// ==================== COURSE ENROLLMENTS (NEW) ====================

/**
 * Get list of all courses with count of enrolled students
 */
function getCourseEnrollmentsList($conn) {
    $query = "SELECT 
                c.CourseID,
                c.CourseCode,
                c.CourseName,
                c.Department,
                c.Credits,
                c.Semester,
                c.InstructorName,
                COUNT(CASE WHEN e.Status = 'Active' THEN 1 END) AS ActiveStudents,
                COUNT(CASE WHEN e.Status = 'Completed' THEN 1 END) AS CompletedStudents,
                COUNT(CASE WHEN e.Status = 'Withdrawn' THEN 1 END) AS WithdrawnStudents,
                COUNT(e.EnrollmentID) AS TotalEnrollments
              FROM Courses c
              LEFT JOIN Enrollments e ON c.CourseID = e.CourseID
              GROUP BY c.CourseID
              ORDER BY c.CourseCode ASC";
    
    $result = $conn->query($query);
    $courses = [];
    while($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    logActivity($conn, 'Viewed course enrollments list', 'View', 'CourseEnrollments', null, 'Listed all courses with enrollment counts', 200);
    echo json_encode(['success' => true, 'courses' => $courses]);
}

/**
 * Get all students enrolled in a specific course
 */
function getStudentsInCourse($conn) {
    $courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
    
    if ($courseId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
        return;
    }
    
    // Get course details
    $courseQuery = "SELECT CourseID, CourseCode, CourseName, Department, Credits, InstructorName, Semester FROM Courses WHERE CourseID = $courseId";
    $courseResult = $conn->query($courseQuery);
    
    if ($courseResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Course not found']);
        return;
    }
    
    $course = $courseResult->fetch_assoc();
    
    // Get students enrolled in this course with grades and attendance
    $studentsQuery = "SELECT 
                        s.StudentID,
                        s.Name,
                        s.Email,
                        s.Phone,
                        s.Department,
                        s.Year,
                        s.IPAddress,
                        e.EnrollmentID,
                        e.EnrollmentDate,
                        e.Status AS EnrollmentStatus,
                        g.MidtermGrade,
                        g.FinalGrade,
                        g.AssignmentGrade,
                        g.TotalGrade,
                        g.LetterGrade,
                        g.GPA,
                        (SELECT COUNT(*) FROM Attendance a WHERE a.EnrollmentID = e.EnrollmentID AND a.Status = 'Present') AS PresentDays,
                        (SELECT COUNT(*) FROM Attendance a WHERE a.EnrollmentID = e.EnrollmentID AND a.Status = 'Absent') AS AbsentDays,
                        (SELECT COUNT(*) FROM Attendance a WHERE a.EnrollmentID = e.EnrollmentID) AS TotalDays
                      FROM Enrollments e
                      INNER JOIN Students s ON e.StudentID = s.StudentID
                      LEFT JOIN Grades g ON g.EnrollmentID = e.EnrollmentID
                      WHERE e.CourseID = $courseId
                      ORDER BY s.Name ASC";
    
    $studentsResult = $conn->query($studentsQuery);
    $students = [];
    while($row = $studentsResult->fetch_assoc()) {
        // Calculate attendance percentage
        $totalDays = (int)$row['TotalDays'];
        $row['AttendancePercent'] = $totalDays > 0 
            ? round(($row['PresentDays'] / $totalDays) * 100, 1) 
            : null;
        $students[] = $row;
    }
    
    // Stats
    $stats = [
        'total' => count($students),
        'active' => count(array_filter($students, fn($s) => $s['EnrollmentStatus'] === 'Active')),
        'completed' => count(array_filter($students, fn($s) => $s['EnrollmentStatus'] === 'Completed')),
        'withdrawn' => count(array_filter($students, fn($s) => $s['EnrollmentStatus'] === 'Withdrawn')),
        'avg_gpa' => null
    ];
    
    $gpas = array_filter(array_column($students, 'GPA'), fn($g) => $g !== null);
    if (count($gpas) > 0) {
        $stats['avg_gpa'] = round(array_sum($gpas) / count($gpas), 2);
    }
    
    logActivity($conn, "Viewed students in course #$courseId", 'View', 'Course', $courseId, "Viewed enrollments for course: " . $course['CourseName'], 200);
    
    echo json_encode([
        'success' => true,
        'course' => $course,
        'students' => $students,
        'stats' => $stats
    ]);
}

/**
 * Get all courses a specific student is enrolled in (reverse lookup)
 */
function getCoursesForStudent($conn) {
    $studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
    
    if ($studentId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
        return;
    }
    
    $studentQuery = "SELECT StudentID, Name, Email, Department, Year FROM Students WHERE StudentID = $studentId";
    $studentResult = $conn->query($studentQuery);
    
    if ($studentResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        return;
    }
    
    $student = $studentResult->fetch_assoc();
    
    $coursesQuery = "SELECT 
                       c.CourseID,
                       c.CourseCode,
                       c.CourseName,
                       c.Credits,
                       c.InstructorName,
                       c.Semester,
                       e.EnrollmentID,
                       e.EnrollmentDate,
                       e.Status,
                       g.TotalGrade,
                       g.LetterGrade,
                       g.GPA
                     FROM Enrollments e
                     INNER JOIN Courses c ON e.CourseID = c.CourseID
                     LEFT JOIN Grades g ON g.EnrollmentID = e.EnrollmentID
                     WHERE e.StudentID = $studentId
                     ORDER BY c.CourseCode ASC";
    
    $coursesResult = $conn->query($coursesQuery);
    $courses = [];
    while($row = $coursesResult->fetch_assoc()) {
        $courses[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'student' => $student,
        'courses' => $courses
    ]);
}

// ==================== REMOVE STUDENT FROM COURSE (NEW) ====================

/**
 * Permanently remove a student from a course
 * This deletes the enrollment record (and cascades to delete grades and attendance)
 */
function removeStudentFromCourse($conn, $data) {
    $enrollmentId = isset($data['enrollment_id']) ? (int)$data['enrollment_id'] : 0;
    
    if ($enrollmentId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid enrollment ID']);
        return;
    }
    
    // Get info BEFORE deleting (for logging)
    $infoQuery = "SELECT 
                    e.EnrollmentID,
                    s.Name AS StudentName,
                    s.StudentID,
                    c.CourseName,
                    c.CourseCode,
                    c.CourseID
                  FROM Enrollments e
                  INNER JOIN Students s ON e.StudentID = s.StudentID
                  INNER JOIN Courses c ON e.CourseID = c.CourseID
                  WHERE e.EnrollmentID = $enrollmentId";
    
    $infoResult = $conn->query($infoQuery);
    
    if ($infoResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Enrollment not found']);
        return;
    }
    
    $info = $infoResult->fetch_assoc();
    
    // Delete the enrollment (grades + attendance cascade automatically via ON DELETE CASCADE)
    $deleteQuery = "DELETE FROM Enrollments WHERE EnrollmentID = $enrollmentId";
    
    if ($conn->query($deleteQuery)) {
        $details = "Removed student '" . $info['StudentName'] . "' from course '" . $info['CourseName'] . "' (" . $info['CourseCode'] . ")";
        logActivity($conn, 'Removed student from course', 'Delete', 'Enrollment', $enrollmentId, $details, 200);
        
        echo json_encode([
            'success' => true, 
            'message' => "Successfully removed {$info['StudentName']} from {$info['CourseCode']}",
            'student_name' => $info['StudentName'],
            'course_code' => $info['CourseCode']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove: ' . $conn->error]);
    }
}

// ==================== GRADES ====================
function getGrades($conn) {
    $query = "SELECT g.*, s.Name as StudentName, c.CourseName FROM Grades g JOIN Enrollments e ON g.EnrollmentID = e.EnrollmentID JOIN Students s ON e.StudentID = s.StudentID JOIN Courses c ON e.CourseID = c.CourseID ORDER BY s.Name ASC";
    $result = $conn->query($query);
    $grades = [];
    while($row = $result->fetch_assoc()) $grades[] = $row;
    echo json_encode(['success' => true, 'grades' => $grades]);
}

function addGrade($conn, $data) {
    $enrollment_id = isset($data['enrollment_id']) ? (int)$data['enrollment_id'] : 0;
    $midterm = isset($data['midterm']) && $data['midterm'] !== '' ? (float)$data['midterm'] : null;
    $final = isset($data['final']) && $data['final'] !== '' ? (float)$data['final'] : null;
    $assignment = isset($data['assignment']) && $data['assignment'] !== '' ? (float)$data['assignment'] : null;

    if ($enrollment_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Enrollment is required']);
        return;
    }

    $total = null;
    $letter = 'F';
    $gpa = 0.0;

    if ($midterm !== null && $final !== null && $assignment !== null) {
        $total = round(($midterm + $final + $assignment) / 3, 2);
        
        if ($total >= 90) { $letter = 'A+'; $gpa = 4.0; }
        elseif ($total >= 85) { $letter = 'A'; $gpa = 4.0; }
        elseif ($total >= 80) { $letter = 'A-'; $gpa = 3.67; }
        elseif ($total >= 75) { $letter = 'B+'; $gpa = 3.33; }
        elseif ($total >= 70) { $letter = 'B'; $gpa = 3.0; }
        elseif ($total >= 65) { $letter = 'B-'; $gpa = 2.67; }
        elseif ($total >= 60) { $letter = 'C+'; $gpa = 2.33; }
        elseif ($total >= 55) { $letter = 'C'; $gpa = 2.0; }
        elseif ($total >= 50) { $letter = 'D'; $gpa = 1.0; }
    }

    $midterm_sql = $midterm !== null ? $midterm : 'NULL';
    $final_sql = $final !== null ? $final : 'NULL';
    $assignment_sql = $assignment !== null ? $assignment : 'NULL';
    $total_sql = $total !== null ? $total : 'NULL';

    $query = "INSERT INTO Grades (EnrollmentID, MidtermGrade, FinalGrade, AssignmentGrade, TotalGrade, LetterGrade, GPA) VALUES ($enrollment_id, $midterm_sql, $final_sql, $assignment_sql, $total_sql, '$letter', $gpa)";

    if ($conn->query($query)) {
        $newId = $conn->insert_id;
        logActivity($conn, 'Added grade', 'Create', 'Grade', $newId, "Total: $total, Letter: $letter, GPA: $gpa", 201);
        echo json_encode(['success' => true, 'message' => 'Grade added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

// ==================== ATTENDANCE ====================
function getAttendance($conn) {
    $query = "SELECT a.*, s.Name as StudentName, c.CourseName FROM Attendance a JOIN Enrollments e ON a.EnrollmentID = e.EnrollmentID JOIN Students s ON e.StudentID = s.StudentID JOIN Courses c ON e.CourseID = c.CourseID ORDER BY a.AttendanceDate DESC";
    $result = $conn->query($query);
    $attendance = [];
    while($row = $result->fetch_assoc()) $attendance[] = $row;
    echo json_encode(['success' => true, 'attendance' => $attendance]);
}

function addAttendance($conn, $data) {
    $enrollment_id = isset($data['enrollment_id']) ? (int)$data['enrollment_id'] : 0;
    $date = $conn->real_escape_string($data['date'] ?? '');
    $status = $conn->real_escape_string($data['status'] ?? 'Present');
    $notes = $conn->real_escape_string($data['notes'] ?? '');

    if ($enrollment_id <= 0 || empty($date)) {
        echo json_encode(['success' => false, 'message' => 'Enrollment and Date are required']);
        return;
    }

    $query = "INSERT INTO Attendance (EnrollmentID, AttendanceDate, Status, Notes) VALUES ($enrollment_id, '$date', '$status', '$notes')";

    if ($conn->query($query)) {
        $newId = $conn->insert_id;
        logActivity($conn, 'Recorded attendance', 'Create', 'Attendance', $newId, "Date: $date, Status: $status", 201);
        echo json_encode(['success' => true, 'message' => 'Attendance recorded successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

// ==================== NETWORK MONITORING ====================
function getNetworkStats($conn) {
    $stats = [];

    // Total events ever
    $r = $conn->query("SELECT COUNT(*) AS c FROM ActivityLog");
    $stats['totalEvents'] = $r ? (int)$r->fetch_assoc()['c'] : 0;

    // Events in last 24h
    $r = $conn->query("SELECT COUNT(*) AS c FROM ActivityLog WHERE CreatedAt >= NOW() - INTERVAL 24 HOUR");
    $stats['last24h'] = $r ? (int)$r->fetch_assoc()['c'] : 0;

    // Unique IPs ever
    $r = $conn->query("SELECT COUNT(DISTINCT IPAddress) AS c FROM ActivityLog WHERE IPAddress IS NOT NULL AND IPAddress != ''");
    $stats['uniqueIPs'] = $r ? (int)$r->fetch_assoc()['c'] : 0;

    // Failed logins in last 24h (security threats)
    $r = $conn->query("SELECT COUNT(*) AS c FROM ActivityLog WHERE Category = 'Security' AND CreatedAt >= NOW() - INTERVAL 24 HOUR");
    $stats['threats'] = $r ? (int)$r->fetch_assoc()['c'] : 0;

    // Active users in last hour
    $r = $conn->query("SELECT COUNT(DISTINCT Username) AS c FROM ActivityLog WHERE Username != 'Anonymous' AND CreatedAt >= NOW() - INTERVAL 1 HOUR");
    $stats['activeUsers'] = $r ? (int)$r->fetch_assoc()['c'] : 0;

    // Most active user (last 24h)
    $r = $conn->query("SELECT Username, COUNT(*) AS c FROM ActivityLog WHERE Username != 'Anonymous' AND CreatedAt >= NOW() - INTERVAL 24 HOUR GROUP BY Username ORDER BY c DESC LIMIT 1");
    $stats['topUser'] = ($r && $row = $r->fetch_assoc()) ? "{$row['Username']} ({$row['c']})" : 'N/A';

    echo json_encode(['success' => true, 'stats' => $stats]);
}

function getActivityLog($conn) {
    $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $cat    = $_GET['category'] ?? '';
    $where  = '';
    if (in_array($cat, ['Auth','Create','Update','Delete','View','Network','Security','System'])) {
        $where = "WHERE Category = '" . $conn->real_escape_string($cat) . "'";
    }
    $q = "SELECT LogID, Username, UserRole, Action, Category, TargetType, TargetID, IPAddress, Method, StatusCode, Details, CreatedAt
          FROM ActivityLog $where ORDER BY CreatedAt DESC LIMIT $limit";
    $r = $conn->query($q);
    $logs = [];
    if ($r) while ($row = $r->fetch_assoc()) $logs[] = $row;
    echo json_encode(['success' => true, 'logs' => $logs]);
}

function getRecentActivity($conn) {
    // Last 10 entries — for live ticker
    $q = "SELECT Username, Action, Category, IPAddress, CreatedAt FROM ActivityLog ORDER BY CreatedAt DESC LIMIT 10";
    $r = $conn->query($q);
    $logs = [];
    if ($r) while ($row = $r->fetch_assoc()) $logs[] = $row;
    echo json_encode(['success' => true, 'logs' => $logs]);
}

function getCategoryBreakdown($conn) {
    // For pie / bar chart
    $q = "SELECT Category, COUNT(*) AS count FROM ActivityLog GROUP BY Category ORDER BY count DESC";
    $r = $conn->query($q);
    $rows = [];
    if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success' => true, 'data' => $rows]);
}

function getTopIPs($conn) {
    // Top 8 IPs by activity (last 24h)
    $q = "SELECT IPAddress, COUNT(*) AS hits, MAX(CreatedAt) AS lastSeen
          FROM ActivityLog
          WHERE IPAddress IS NOT NULL AND IPAddress != '' AND CreatedAt >= NOW() - INTERVAL 24 HOUR
          GROUP BY IPAddress ORDER BY hits DESC LIMIT 8";
    $r = $conn->query($q);
    $rows = [];
    if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success' => true, 'data' => $rows]);
}

function getActivityTimeline($conn) {
    // Hourly counts for last 24 hours — for line chart
    $q = "SELECT DATE_FORMAT(CreatedAt, '%H:00') AS hour, COUNT(*) AS count
          FROM ActivityLog
          WHERE CreatedAt >= NOW() - INTERVAL 24 HOUR
          GROUP BY hour ORDER BY MIN(CreatedAt) ASC";
    $r = $conn->query($q);
    $rows = [];
    if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success' => true, 'data' => $rows]);
}

$conn->close();
?>