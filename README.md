# 🎓 Student Management System (SMS)

> A comprehensive web-based database application for managing students, courses, enrollments, grades, and attendance in academic institutions.

[![MySQL](https://img.shields.io/badge/MySQL-8.0+-blue.svg)](https://www.mysql.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2-purple.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-Academic-green.svg)](#)
[![Status](https://img.shields.io/badge/Status-Complete-success.svg)](#)

---

## 👥 Team Members

| # | Name | Student ID |
|---|------|------------|
| 1 | **Domaduios Youssef** 👑 | 24030159 |
| 2 | **Nada Magdy** | 24030024 |
| 3 | **Youssef Adel** | 24030021 |
| 4 | **Salma Ahmed** | 24030027 |
| 5 | **Shahd Mohamed** | 24030032 |

> 👑 = Team Leader

---

## 📋 Project Overview

The **Student Management System** is a fully functional database-driven web application designed to streamline academic administration. It centralizes student data, course management, enrollment tracking, grade recording, and attendance monitoring — all with role-based access control and comprehensive activity logging.

### 🎯 Key Features

- ✅ **Student Management** — Add, edit, view, and remove student profiles
- ✅ **Course Catalog** — Manage course offerings with instructors and credits
- ✅ **Enrollment System** — Many-to-many relationship with duplicate prevention
- ✅ **Grade Recording** — Midterm, Final, Assignments + automatic GPA
- ✅ **Attendance Tracking** — Daily attendance with multiple statuses
- ✅ **Course Roster** — View all students enrolled in each course
- ✅ **Authentication** — Login system with role-based access
- ✅ **Activity Logging** — Complete audit trail with IP tracking
- ✅ **SQL Views** — Pre-built analytical views
- ✅ **Cascade Delete** — Maintain referential integrity

---

## 🗂️ Repository Structure

```
📁 Student-Management-System/
│
├── 📄 README.md              ← You are here
├── 📄 Proposal.md            ← Project proposal
├── 📄 Schema.md              ← Database schema documentation
├── 📄 ERD.png                ← Entity-Relationship Diagram
├── 📄 database.sql           ← Complete SQL file (structure + data + views)
└── 📄 Acknowledgement.md     ← Team credits and acknowledgements
```

---

## 🗃️ Database Schema

The system uses **7 tables** in a normalized (3NF) relational design:

| # | Table | Purpose |
|---|-------|---------|
| 1 | **Students** | Student personal information |
| 2 | **Courses** | Course catalog |
| 3 | **Enrollments** | Junction table (M:N) |
| 4 | **Grades** | Academic performance |
| 5 | **Attendance** | Daily attendance |
| 6 | **Users** | System login accounts |
| 7 | **ActivityLog** | Audit trail |

Plus **4 SQL Views** for reporting:
- `CourseRoster` — Course-student mapping
- `CourseStatistics` — Aggregated course stats
- `StudentCoursesView` — Reverse lookup
- `AttendanceSummary` — Attendance percentages

---

## 🔗 Entity Relationships

```
Students (1) ──< (N) Enrollments (N) >── (1) Courses
                       │
                       ├──< (N) Grades
                       └──< (N) Attendance

Users (Standalone)
ActivityLog (Standalone)
```

### Relationship Details:

- **Students ↔ Courses:** Many-to-Many via `Enrollments`
- **Enrollments → Grades:** One-to-One
- **Enrollments → Attendance:** One-to-Many
- **CASCADE DELETE:** Removing an enrollment auto-removes related grades & attendance

---

## 🚀 Installation & Setup

### Prerequisites
- XAMPP (or any LAMP/WAMP stack)
- MySQL 8.0+
- PHP 8.0+
- Web browser

### Step 1: Clone the Repository
```bash
git clone https://github.com/[your-username]/Student-Management-System.git
```

### Step 2: Set Up the Database
1. Start **Apache** and **MySQL** in XAMPP
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Click **Import** → Select `database.sql` → **Go**
4. The `student_management` database will be created automatically with sample data

### Step 3: Verify Installation
```sql
USE student_management;
SHOW TABLES;
SELECT COUNT(*) FROM Students;
```

---

## 🛠️ Technology Stack

| Layer | Technology |
|-------|------------|
| **Database** | MySQL 8.0+ |
| **Backend** | PHP 8.2 |
| **Frontend** | HTML5, CSS3, JavaScript |
| **Server** | Apache (XAMPP) |
| **DB Admin** | phpMyAdmin |

---

## 📊 Sample Data Included

- **30+ Students** across multiple departments and years
- **15 Courses** across various CS topics
- **20+ Enrollments** linking students to courses
- **20+ Grade records** with GPA calculations
- **20+ Attendance records** with various statuses
- **7 User accounts** with different roles
- **10+ Activity logs** demonstrating audit trail

---

## 🔐 Default Login Credentials

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Admin |
| registrar | reg123 | Registrar |
| dr.mohamed | teach123 | Teacher |
| staff1 | staff123 | Staff |

> ⚠️ For demonstration purposes only. Change in production!

---

## 📐 Normalization

The database adheres to **Third Normal Form (3NF)**:
- ✅ **1NF:** All attributes are atomic
- ✅ **2NF:** No partial dependencies
- ✅ **3NF:** No transitive dependencies

---

## 🎯 Key Design Decisions

1. **Junction Table (Enrollments)**
   - Resolves Many-to-Many between Students and Courses
   - Stores enrollment-specific data (date, status)

2. **Cascade Delete**
   - Removes related Grades & Attendance when enrollment is deleted
   - Maintains data integrity automatically

3. **Indexes for Performance**
   - On frequently queried fields (IP addresses, status, GPA, dates)

4. **SQL Views**
   - Pre-defined complex queries for common reporting needs
   - No data duplication

5. **IP Address Tracking**
   - Network-aware system (integrates with the networking project)
   - Security and audit capability

---

## 📑 Documentation

- 📄 [**Proposal.md**](Proposal.md) — Project proposal and objectives
- 📄 [**Schema.md**](Schema.md) — Detailed database schema
- 🖼️ [**ERD.png**](ERD.png) — Visual entity-relationship diagram
- 🙏 [**Acknowledgement.md**](Acknowledgement.md) — Team credits

---

## 🧪 Testing the System

After installation, test these scenarios:

### Test 1: View All Students
```sql
SELECT * FROM Students;
```

### Test 2: Course Enrollment Statistics
```sql
SELECT * FROM CourseStatistics;
```

### Test 3: Students in a Specific Course
```sql
SELECT * FROM CourseRoster WHERE CourseCode = 'CS101';
```

### Test 4: Student's Courses
```sql
SELECT * FROM StudentCoursesView WHERE StudentID = 1;
```

### Test 5: Attendance Summary
```sql
SELECT * FROM AttendanceSummary;
```

---

## 🎓 Academic Context

**Course:** Database Systems  
**Project Type:** Database Design and Implementation  
**Submission Date:** May 2026

This project demonstrates practical application of:
- Database design principles
- Relational modeling
- SQL programming
- Web integration
- Team collaboration

---

## 📜 License

This project is developed for **academic purposes only**. All data used is fictional and serves educational demonstration.

---

## 🤝 Contributing

This is an academic project submission. While contributions are not actively sought, feedback and suggestions are welcome.

---

## 👏 Acknowledgements

Special thanks to our instructor, classmates, and families. See [Acknowledgement.md](Acknowledgement.md) for full details.

---

<div align="center">

### 🎓 Made with ❤️ by Team SMS

**Domaduios Youssef** 👑 • **Nada Magdy** • **Youssef Adel** • **Salma Ahmed** • **Shahd Mohamed**

</div>
