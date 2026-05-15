DROP TABLE IF EXISTS Attendance;
DROP TABLE IF EXISTS Grades;
DROP TABLE IF EXISTS Enrollments;
DROP TABLE IF EXISTS Courses;
DROP TABLE IF EXISTS Students;
DROP TABLE IF EXISTS Users;
DROP TABLE IF EXISTS ActivityLog;

CREATE TABLE Students (
    StudentID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE,
    Phone VARCHAR(20),
    Department VARCHAR(50) DEFAULT 'Computer Science',
    Year INT,
    DateOfBirth DATE,
    Address VARCHAR(200),
    EnrollmentDate DATE DEFAULT CURRENT_DATE,
    IPAddress VARCHAR(45) DEFAULT NULL
);

CREATE TABLE Courses (
    CourseID INT PRIMARY KEY AUTO_INCREMENT,
    CourseCode VARCHAR(20) UNIQUE NOT NULL,
    CourseName VARCHAR(100) NOT NULL,
    Department VARCHAR(50) DEFAULT 'Computer Science',
    Credits INT,
    Semester VARCHAR(20),
    InstructorName VARCHAR(100)
);

CREATE TABLE Enrollments (
    EnrollmentID INT PRIMARY KEY AUTO_INCREMENT,
    StudentID INT NOT NULL,
    CourseID INT NOT NULL,
    EnrollmentDate DATE DEFAULT CURRENT_DATE,
    Status VARCHAR(20) DEFAULT 'Active',
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (CourseID) REFERENCES Courses(CourseID) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (StudentID, CourseID)
);

CREATE TABLE Grades (
    GradeID INT PRIMARY KEY AUTO_INCREMENT,
    EnrollmentID INT NOT NULL,
    MidtermGrade DECIMAL(5,2),
    FinalGrade DECIMAL(5,2),
    AssignmentGrade DECIMAL(5,2),
    TotalGrade DECIMAL(5,2),
    LetterGrade CHAR(2),
    GPA DECIMAL(3,2),
    FOREIGN KEY (EnrollmentID) REFERENCES Enrollments(EnrollmentID) ON DELETE CASCADE
);

CREATE TABLE Attendance (
    AttendanceID INT PRIMARY KEY AUTO_INCREMENT,
    EnrollmentID INT NOT NULL,
    AttendanceDate DATE NOT NULL,
    Status VARCHAR(20) DEFAULT 'Present',
    Notes VARCHAR(200),
    FOREIGN KEY (EnrollmentID) REFERENCES Enrollments(EnrollmentID) ON DELETE CASCADE
);

CREATE TABLE Users (
    UserID INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) UNIQUE NOT NULL,
    Password VARCHAR(100) NOT NULL,
    Role VARCHAR(20) NOT NULL,
    Email VARCHAR(100),
    IPAddress VARCHAR(45) DEFAULT NULL,   
    RegistrationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ActivityLog (
    LogID         INT PRIMARY KEY AUTO_INCREMENT,
    Username      VARCHAR(50)  DEFAULT 'Anonymous',
    UserRole      VARCHAR(20)  DEFAULT 'Guest',
    Action           VARCHAR(100) NOT NULL,
    Category      VARCHAR(30)  DEFAULT 'General',
    TargetType    VARCHAR(50)  DEFAULT NULL,
    TargetID      VARCHAR(50)  DEFAULT NULL,
    IPAddress     VARCHAR(45)  DEFAULT NULL,
    UserAgent     VARCHAR(255) DEFAULT NULL,
    Method        VARCHAR(10)  DEFAULT NULL,
    StatusCode    INT          DEFAULT 200,
    Details       TEXT         DEFAULT NULL,
    CreatedAt     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX idx_student_ip ON Students(IPAddress);
CREATE INDEX idx_user_ip ON Users(IPAddress);
CREATE INDEX idx_enrollment_status ON Enrollments(Status);
CREATE INDEX idx_grade_gpa ON Grades(GPA);
CREATE INDEX idx_log_user      ON ActivityLog(Username);
CREATE INDEX idx_log_ip        ON ActivityLog(IPAddress);
CREATE INDEX idx_log_category  ON ActivityLog(Category);
CREATE INDEX idx_log_date      ON ActivityLog(CreatedAt);

INSERT INTO Students (Name, Email, Phone, Department, Year, DateOfBirth, Address, IPAddress) VALUES
('Ahmed Mohamed Ali', 'ahmed.ali@cs.edu', '01012345678', 'Computer Science', 2, '2003-05-15', 'Cairo, Egypt', '192.168.1.101'),
('Sara Hassan Ibrahim', 'sara.hassan@cs.edu', '01023456789', 'Computer Science', 1, '2004-08-22', 'Giza, Egypt', '192.168.1.102'),
('Omar Khaled Mahmoud', 'omar.khaled@cs.edu', '01034567890', 'Computer Science', 1, '2004-12-10', 'Cairo, Egypt', '192.168.1.103'),
('Fatma Ahmed Said', 'fatma.ahmed@cs.edu', '01045678901', 'Computer Science', 3, '2002-03-18', 'Alexandria, Egypt', '192.168.1.104'),
('Mohamed Youssef Abdullah', 'mohamed.youssef@cs.edu', '01056789012', 'Computer Science', 2, '2003-07-25', 'Giza, Egypt', '192.168.1.105'),
('Mariam Sami Hassan', 'mariam.sami@cs.edu', '01067890123', 'Computer Science', 2, '2003-11-30', 'Cairo, Egypt', '192.168.1.106'),
('Youssef Mahmoud Ahmed', 'youssef.mahmoud@cs.edu', '01078901234', 'Computer Science', 3, '2002-01-14', 'Giza, Egypt', '192.168.1.107'),
('Noureldeen Tarek', 'noureldeen.tarek@cs.edu', '01089012345', 'Computer Science', 1, '2004-09-05', 'Cairo, Egypt', '192.168.1.108'),
('Yasmin Adel Fahmy', 'yasmin.adel@cs.edu', '01090123456', 'Computer Science', 2, '2003-04-20', 'Giza, Egypt', '192.168.1.109'),
('Karim Hossam Eldin', 'karim.hossam@cs.edu', '01001234567', 'Computer Science', 3, '2002-06-12', 'Cairo, Egypt', '192.168.1.110'),
('Dina Salah Abdelaziz', 'dina.salah@cs.edu', '01112345678', 'Computer Science', 1, '2004-10-08', 'Alexandria, Egypt', '192.168.1.111'),
('Hossam Eldin Ramy', 'hossam.ramy@cs.edu', '01123456789', 'Computer Science', 2, '2003-02-28', 'Cairo, Egypt', '192.168.1.112'),
('Nada Walid Mohamed', 'nada.walid@cs.edu', '01134567890', 'Computer Science', 3, '2002-12-03', 'Giza, Egypt', '192.168.1.113'),
('Tarek Faisal Ahmed', 'tarek.faisal@cs.edu', '01145678901', 'Computer Science', 2, '2003-08-17', 'Cairo, Egypt', '192.168.1.114'),
('Lamiaa Essam Mahmoud', 'lamiaa.essam@cs.edu', '01156789012', 'Computer Science', 1, '2004-05-25', 'Alexandria, Egypt', '192.168.1.115'),
('Abdelrahman Samir', 'abdelrahman.samir@cs.edu', '01167890123', 'Computer Science', 3, '2002-11-19', 'Giza, Egypt', '192.168.1.116'),
('Heba Maged Ali', 'heba.maged@cs.edu', '01178901234', 'Computer Science', 2, '2003-03-07', 'Cairo, Egypt', '192.168.1.117'),
('Amir Mohamed Saad', 'amir.mohamed@cs.edu', '01189012345', 'Computer Science', 1, '2004-07-14', 'Giza, Egypt', '192.168.1.118'),
('Rana Khaled Hassan', 'rana.khaled@cs.edu', '01190123456', 'Computer Science', 2, '2003-09-22', 'Cairo, Egypt', '192.168.1.119'),
('Ziad Ahmed Farouk', 'ziad.ahmed@cs.edu', '01201234567', 'Computer Science', 3, '2002-04-30', 'Alexandria, Egypt', '192.168.1.120'),
('Shimaa Hussein Ali', 'shimaa.hussein@cs.edu', '01212345678', 'Computer Science', 1, '2004-11-11', 'Cairo, Egypt', '192.168.1.121'),
('Bilal Omar Sayed', 'bilal.omar@cs.edu', '01223456789', 'Computer Science', 2, '2003-01-26', 'Giza, Egypt', '192.168.1.122'),
('Iman Sami Mohamed', 'iman.sami@cs.edu', '01234567890', 'Computer Science', 3, '2002-08-09', 'Cairo, Egypt', '192.168.1.123'),
('Adam Mahmoud Reda', 'adam.mahmoud@cs.edu', '01245678901', 'Computer Science', 1, '2004-06-16', 'Giza, Egypt', '192.168.1.124'),
('Salma Ahmed Hassan', 'salma.ahmed@cs.edu', '01256789012', 'Computer Science', 2, '2003-10-23', 'Alexandria, Egypt', '192.168.1.125'),
('Moaaz Youssef Ali', 'moaaz.youssef@cs.edu', '01267890123', 'Computer Science', 3, '2002-02-05', 'Cairo, Egypt', '192.168.1.126'),
('Jana Mohamed Samir', 'jana.mohamed@cs.edu', '01278901234', 'Computer Science', 1, '2004-12-18', 'Giza, Egypt', '192.168.1.127'),
('Hamza Tarek Fahmy', 'hamza.tarek@cs.edu', '01289012345', 'Computer Science', 2, '2003-05-29', 'Cairo, Egypt', '192.168.1.128'),
('Ruqaya Hossam Adel', 'ruqaya.hossam@cs.edu', '01290123456', 'Computer Science', 3, '2002-09-13', 'Alexandria, Egypt', '192.168.1.129'),
('Oday Walid Salah', 'oday.walid@cs.edu', '01301234567', 'Computer Science', 2, '2003-03-21', 'Cairo, Egypt', '192.168.1.130');


INSERT INTO Courses (CourseCode, CourseName, Department, Credits, Semester, InstructorName) VALUES
('CS101', 'Introduction to Programming', 'Computer Science', 3, 'Fall 2024', 'Dr. Mohamed Abdelrahman'),
('CS102', 'Data Structures', 'Computer Science', 4, 'Fall 2024', 'Dr. Ahmed Hassan'),
('CS201', 'Database Systems', 'Computer Science', 3, 'Spring 2025', 'Dr. Sara Mahmoud'),
('CS202', 'Web Development', 'Computer Science', 3, 'Spring 2025', 'Dr. Khaled Youssef'),
('CS203', 'Object-Oriented Programming', 'Computer Science', 4, 'Fall 2024', 'Dr. Fatma Ahmed'),
('CS301', 'Artificial Intelligence', 'Computer Science', 4, 'Fall 2024', 'Dr. Iman Sami'),
('CS302', 'Machine Learning', 'Computer Science', 3, 'Spring 2025', 'Dr. Omar Suleiman'),
('CS303', 'Computer Networks', 'Computer Science', 3, 'Fall 2024', 'Dr. Nour Elhoda'),
('CS304', 'Operating Systems', 'Computer Science', 4, 'Spring 2025', 'Dr. Hossam Eldin'),
('CS305', 'Software Engineering', 'Computer Science', 3, 'Fall 2024', 'Dr. Ramy Salah'),
('CS401', 'Computer Graphics', 'Computer Science', 3, 'Spring 2025', 'Dr. Dina Walid'),
('CS402', 'Cybersecurity', 'Computer Science', 4, 'Fall 2024', 'Dr. Tarek Faisal'),
('CS403', 'Cloud Computing', 'Computer Science', 3, 'Spring 2025', 'Dr. Lamiaa Essam'),
('CS404', 'Mobile App Development', 'Computer Science', 3, 'Fall 2024', 'Dr. Abdelrahman Samir'),
('CS405', 'Data Science', 'Computer Science', 4, 'Spring 2025', 'Dr. Heba Maged');

INSERT INTO Enrollments (StudentID, CourseID, EnrollmentDate, Status) VALUES
(1, 1, '2024-09-01', 'Active'),
(1, 2, '2024-09-01', 'Active'),
(2, 1, '2024-09-01', 'Active'),
(2, 3, '2024-09-01', 'Active'),
(3, 1, '2024-09-01', 'Active'),
(3, 2, '2024-09-01', 'Active'),
(4, 6, '2024-09-01', 'Active'),
(4, 7, '2024-09-01', 'Active'),
(5, 2, '2024-09-01', 'Active'),
(5, 3, '2024-09-01', 'Active'),
(6, 1, '2024-09-01', 'Active'),
(6, 2, '2024-09-01', 'Active'),
(7, 6, '2024-09-01', 'Active'),
(7, 8, '2024-09-01', 'Active'),
(8, 1, '2024-09-01', 'Active'),
(8, 5, '2024-09-01', 'Active'),
(9, 2, '2024-09-01', 'Active'),
(9, 3, '2024-09-01', 'Active'),
(10, 6, '2024-09-01', 'Active'),
(10, 7, '2024-09-01', 'Active');


INSERT INTO Grades (EnrollmentID, MidtermGrade, FinalGrade, AssignmentGrade, TotalGrade, LetterGrade, GPA) VALUES
(1, 85.5, 88.0, 90.0, 87.83, 'A-', 3.67),
(2, 92.0, 95.0, 93.0, 93.33, 'A', 4.00),
(3, 78.0, 82.5, 85.0, 81.83, 'B+', 3.33),
(4, 88.0, 90.0, 87.5, 88.50, 'A-', 3.67),
(5, 72.0, 75.0, 78.0, 75.00, 'B', 3.00),
(6, 95.0, 97.0, 96.0, 96.00, 'A+', 4.00),
(7, 80.0, 83.0, 85.0, 82.67, 'B+', 3.33),
(8, 88.5, 91.0, 89.0, 89.50, 'A-', 3.67),
(9, 76.0, 79.0, 80.0, 78.33, 'B', 3.00),
(10, 93.0, 96.0, 94.5, 94.50, 'A', 4.00),
(11, 84.0, 87.0, 86.0, 85.67, 'A-', 3.67),
(12, 90.0, 92.5, 91.0, 91.17, 'A', 4.00),
(13, 77.5, 80.0, 82.0, 79.83, 'B+', 3.33),
(14, 86.0, 89.0, 88.0, 87.67, 'A-', 3.67),
(15, 91.0, 94.0, 92.0, 92.33, 'A', 4.00),
(16, 75.0, 78.0, 80.0, 77.67, 'B', 3.00),
(17, 89.0, 91.5, 90.0, 90.17, 'A', 4.00),
(18, 82.0, 85.0, 84.0, 83.67, 'B+', 3.33),
(19, 94.0, 96.5, 95.0, 95.17, 'A+', 4.00),
(20, 87.0, 90.0, 88.5, 88.50, 'A-', 3.67);


INSERT INTO Attendance (EnrollmentID, AttendanceDate, Status, Notes) VALUES
(1, '2024-09-01', 'Present', NULL),
(1, '2024-09-03', 'Present', NULL),
(1, '2024-09-05', 'Present', NULL),
(2, '2024-09-01', 'Present', NULL),
(2, '2024-09-03', 'Absent', 'Medical excuse'),
(2, '2024-09-05', 'Present', NULL),
(3, '2024-09-01', 'Present', NULL),
(3, '2024-09-03', 'Present', NULL),
(3, '2024-09-05', 'Late', 'Late 10 minutes'),
(4, '2024-09-01', 'Present', NULL),
(4, '2024-09-03', 'Present', NULL),
(4, '2024-09-05', 'Present', NULL),
(5, '2024-09-01', 'Absent', 'No excuse'),
(5, '2024-09-03', 'Present', NULL),
(5, '2024-09-05', 'Present', NULL),
(6, '2024-09-01', 'Present', NULL),
(6, '2024-09-03', 'Present', NULL),
(6, '2024-09-05', 'Present', NULL),
(7, '2024-09-01', 'Present', NULL),
(7, '2024-09-03', 'Present', NULL);


INSERT INTO Users (Username, Password, Role, Email, IPAddress, RegistrationDate) VALUES
('admin', 'admin123', 'Admin', 'admin@cs.edu', '192.168.1.1', '2024-01-15 09:00:00'),
('registrar', 'reg123', 'Registrar', 'registrar@cs.edu', '192.168.1.2', '2024-01-16 10:30:00'),
('dr.mohamed', 'teach123', 'Teacher', 'dr.mohamed@cs.edu', '192.168.1.50', '2024-01-20 11:15:00'),
('dr.ahmed', 'teach123', 'Teacher', 'dr.ahmed@cs.edu', '192.168.1.51', '2024-01-21 09:45:00'),
('dr.sara', 'teach123', 'Teacher', 'dr.sara@cs.edu', '192.168.1.52', '2024-01-22 14:20:00'),
('staff1', 'staff123', 'Staff', 'staff1@cs.edu', '192.168.1.100', '2024-02-01 08:00:00'),
('staff2', 'staff123', 'Staff', 'staff2@cs.edu', '192.168.1.101', '2024-02-02 09:30:00');


INSERT INTO ActivityLog (Username, UserRole, Action, Category, IPAddress, Method, Details, CreatedAt) VALUES
('admin',     'Admin',     'System initialized',  'System',   '192.168.1.1',   'GET',  'Database setup complete', NOW() - INTERVAL 2 DAY),
('admin',     'Admin',     'User logged in',      'Auth',     '192.168.1.1',   'POST', 'Successful login',        NOW() - INTERVAL 1 DAY),
('registrar', 'Registrar', 'User logged in',      'Auth',     '192.168.1.2',   'POST', 'Successful login',        NOW() - INTERVAL 12 HOUR),
('admin',     'Admin',     'Viewed dashboard',    'View',     '192.168.1.1',   'GET',  'Dashboard accessed',      NOW() - INTERVAL 6 HOUR),
('staff1',    'Staff',     'User registered',     'Auth',     '192.168.1.100', 'POST', 'New account created',     NOW() - INTERVAL 4 HOUR),
('admin',     'Admin',     'Added student',       'Create',   '192.168.1.1',   'POST', 'Student record created',  NOW() - INTERVAL 2 HOUR),
('dr.mohamed','Teacher',   'User logged in',      'Auth',     '192.168.1.50',  'POST', 'Successful login',        NOW() - INTERVAL 1 HOUR),
('dr.mohamed','Teacher',   'Recorded attendance', 'Create',   '192.168.1.50',  'POST', 'Attendance marked',       NOW() - INTERVAL 30 MINUTE),
('admin',     'Admin',     'Viewed network map',  'Network',  '192.168.1.1',   'GET',  'Network monitoring',      NOW() - INTERVAL 10 MINUTE),
('staff1',    'Staff',     'Failed login',        'Security', '192.168.1.105', 'POST', 'Invalid credentials',     NOW() - INTERVAL 5 MINUTE);

SELECT COUNT(*) AS TotalStudents FROM Students;

SELECT StudentID, Name, Department, Year, IPAddress FROM Students LIMIT 5;

SELECT Username, Role, Email, IPAddress, RegistrationDate FROM Users;


