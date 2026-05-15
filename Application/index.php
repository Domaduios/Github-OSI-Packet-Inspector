<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$currentDate = date('l, F d, Y');
$userRole    = $_SESSION['role'] ?? 'Guest';
$username    = $_SESSION['username'] ?? 'Unknown';
$activeTab   = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Student Management System</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        /* tab content panels */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; animation: fadeUp .35s ease; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Section header */
        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .section-head h2 {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -.3px;
        }

        .section-head .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        /* Grade circle */
        .grade-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px; height: 36px;
            border-radius: 50%;
            font-family: var(--mono);
            font-weight: 700;
            font-size: 12px;
            color: var(--bg);
        }
        .grade-a { background: linear-gradient(135deg, #10b981, #059669); }
        .grade-b { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .grade-c { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .grade-d { background: linear-gradient(135deg, #ec4899, #db2777); }
        .grade-f { background: linear-gradient(135deg, #ef4444, #dc2626); }

        /* Modal */
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.65);
            backdrop-filter: blur(6px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-backdrop.active { display: flex; }

        .modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 28px;
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalIn .25s ease;
            box-shadow: 0 30px 80px rgba(0,0,0,.6);
        }

        @keyframes modalIn {
            from { opacity: 0; transform: translateY(20px) scale(.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 22px;
        }

        .modal-head h3 {
            font-size: 17px;
            font-weight: 700;
        }

        .close-btn {
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--muted);
            width: 32px; height: 32px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all .15s;
        }
        .close-btn:hover { color: var(--danger); border-color: rgba(239,68,68,.3); }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .form-grid .field.full { grid-column: 1 / -1; }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 18px;
            font-size: 13px;
            font-weight: 500;
            z-index: 2000;
            animation: toastIn .3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 360px;
            box-shadow: 0 12px 32px rgba(0,0,0,.5);
        }
        .toast.success { border-color: rgba(16,185,129,.4); color: var(--success); }
        .toast.error   { border-color: rgba(239,68,68,.4);  color: var(--danger); }

        @keyframes toastIn {
            from { opacity: 0; transform: translateX(40px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* Quick action grid for dashboard */
        .quick-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .quick-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all .15s;
            cursor: pointer;
        }

        .quick-card:hover {
            border-color: var(--accent);
            background: rgba(0,212,170,.04);
            transform: translateY(-2px);
        }

        .quick-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: var(--surface2);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .quick-card:hover .quick-icon {
            background: rgba(0,212,170,.1);
        }

        .quick-title { font-size: 13px; font-weight: 600; }
        .quick-desc { font-size: 11px; color: var(--muted); margin-top: 2px; }

        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
            .modal { padding: 20px; }
        }
    </style>
</head>
<body>

<?php include '_navbar.php'; ?>

<main class="page">

    <!-- DASHBOARD -->
    <div id="dashboard" class="tab-panel active">
        <div class="page-header">
            <div class="page-title-group">
                <div>
                    <div class="page-title">Welcome back, <?php echo htmlspecialchars($username); ?></div>
                    <div class="page-sub"><?php echo $currentDate; ?></div>
                </div>
            </div>
            <div class="live-badge">
                <span class="pulse"></span>
                SYSTEM ONLINE
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid" id="dashboardStats">
            <div class="stat fade-in">
                <div class="stat-icon">🎓</div>
                <div class="stat-num">—</div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat fade-in">
                <div class="stat-icon">📚</div>
                <div class="stat-num">—</div>
                <div class="stat-label">Total Courses</div>
            </div>
            <div class="stat fade-in">
                <div class="stat-icon">📝</div>
                <div class="stat-num">—</div>
                <div class="stat-label">Active Enrollments</div>
            </div>
            <div class="stat fade-in">
                <div class="stat-icon">⭐</div>
                <div class="stat-num">—</div>
                <div class="stat-label">Average GPA</div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="quick-grid">
            <a class="quick-card" onclick="openTab('students')">
                <div class="quick-icon">◉</div>
                <div>
                    <div class="quick-title">Manage Students</div>
                    <div class="quick-desc">Add, view, or remove records</div>
                </div>
            </a>
            <a class="quick-card" href="network_map.php">
                <div class="quick-icon">⌬</div>
                <div>
                    <div class="quick-title">Network Map</div>
                    <div class="quick-desc">Live IP distribution view</div>
                </div>
            </a>
            <a class="quick-card" href="network_topology.php">
                <div class="quick-icon">⎈</div>
                <div>
                    <div class="quick-title">Topology Design</div>
                    <div class="quick-desc">VLANs, subnets, configs</div>
                </div>
            </a>
            <a class="quick-card" href="readme.php">
                <div class="quick-icon">※</div>
                <div>
                    <div class="quick-title">Documentation</div>
                    <div class="quick-desc">Project overview & guide</div>
                </div>
            </a>
        </div>

        <!-- Top students -->
        <div class="card fade-in">
            <div class="card-header">
                <span>🏆</span>
                <h2>Top Students by GPA</h2>
                <span class="card-tag">LEADERBOARD</span>
            </div>
            <div class="table-wrap">
                <table class="table" id="topStudentsTable">
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Department</th><th>Year</th><th>GPA</th></tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="5" class="empty">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- STUDENTS -->
    <div id="students" class="tab-panel">
        <div class="card">
            <div class="section-head">
                <h2>🎓 Students</h2>
                <div class="actions">
                    <input type="text" id="studentSearch" class="input" placeholder="Search students…" style="width:240px;padding:8px 12px;font-size:12px;">
                    <button class="btn btn-primary" onclick="openModal('addStudentModal')">＋ Add Student</button>
                </div>
            </div>
            <div class="table-wrap">
                <table class="table" id="studentsTable">
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Department</th><th>Year</th><th>IP Address</th><th></th></tr>
                    </thead>
                    <tbody><tr><td colspan="8" class="empty">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- COURSES -->
    <div id="courses" class="tab-panel">
        <div class="card">
            <div class="section-head">
                <h2>📚 Courses</h2>
                <button class="btn btn-primary" onclick="openModal('addCourseModal')">＋ Add Course</button>
            </div>
            <div class="table-wrap">
                <table class="table" id="coursesTable">
                    <thead>
                        <tr><th>Code</th><th>Name</th><th>Department</th><th>Credits</th><th>Semester</th><th>Instructor</th><th>Students</th></tr>
                    </thead>
                    <tbody><tr><td colspan="7" class="empty">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ENROLLMENTS -->
    <div id="enrollments" class="tab-panel">
        <div class="card">
            <div class="section-head">
                <h2>📝 Enrollments</h2>
                <button class="btn btn-primary" onclick="openModal('addEnrollmentModal')">＋ New Enrollment</button>
            </div>
            <div class="table-wrap">
                <table class="table" id="enrollmentsTable">
                    <thead>
                        <tr><th>ID</th><th>Student</th><th>Course</th><th>Date</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody><tr><td colspan="6" class="empty">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- GRADES -->
    <div id="grades" class="tab-panel">
        <div class="card">
            <div class="section-head">
                <h2>🏆 Grades</h2>
                <button class="btn btn-primary" onclick="openModal('addGradeModal')">＋ Add Grade</button>
            </div>
            <div class="table-wrap">
                <table class="table" id="gradesTable">
                    <thead>
                        <tr><th>Student</th><th>Course</th><th>Midterm</th><th>Final</th><th>Assignment</th><th>Total</th><th>Letter</th><th>GPA</th></tr>
                    </thead>
                    <tbody><tr><td colspan="8" class="empty">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ATTENDANCE -->
    <div id="attendance" class="tab-panel">
        <div class="card">
            <div class="section-head">
                <h2>✅ Attendance</h2>
                <button class="btn btn-primary" onclick="openModal('addAttendanceModal')">＋ Record</button>
            </div>
            <div class="table-wrap">
                <table class="table" id="attendanceTable">
                    <thead>
                        <tr><th>ID</th><th>Student</th><th>Course</th><th>Date</th><th>Status</th><th>Notes</th></tr>
                    </thead>
                    <tbody><tr><td colspan="6" class="empty">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<!-- ── MODALS ── -->

<div id="addStudentModal" class="modal-backdrop">
    <div class="modal">
        <div class="modal-head">
            <h3>＋ Add New Student</h3>
            <button class="close-btn" onclick="closeModal('addStudentModal')">✕</button>
        </div>
        <form id="addStudentForm">
            <div class="form-grid">
                <div class="field full"><label>FULL NAME *</label><input class="input" type="text" name="name" required></div>
                <div class="field full"><label>EMAIL *</label><input class="input" type="email" name="email" required></div>
                <div class="field"><label>PHONE</label><input class="input" type="text" name="phone"></div>
                <div class="field"><label>DEPARTMENT</label><input class="input" type="text" name="department" value="Computer Science"></div>
                <div class="field"><label>YEAR</label><select class="select" name="year"><option>1</option><option>2</option><option>3</option><option>4</option></select></div>
                <div class="field"><label>DATE OF BIRTH</label><input class="input" type="date" name="dob"></div>
                <div class="field full"><label>ADDRESS</label><textarea class="textarea" name="address" rows="2"></textarea></div>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">💾 Save Student</button>
                <button type="button" class="btn btn-ghost" onclick="closeModal('addStudentModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="addCourseModal" class="modal-backdrop">
    <div class="modal">
        <div class="modal-head">
            <h3>＋ Add New Course</h3>
            <button class="close-btn" onclick="closeModal('addCourseModal')">✕</button>
        </div>
        <form id="addCourseForm">
            <div class="form-grid">
                <div class="field"><label>CODE *</label><input class="input" type="text" name="code" required></div>
                <div class="field"><label>NAME *</label><input class="input" type="text" name="name" required></div>
                <div class="field"><label>DEPARTMENT</label><input class="input" type="text" name="department" value="Computer Science"></div>
                <div class="field"><label>CREDITS</label><input class="input" type="number" name="credits" value="3"></div>
                <div class="field"><label>SEMESTER</label><input class="input" type="text" name="semester" placeholder="Fall 2024"></div>
                <div class="field"><label>INSTRUCTOR</label><input class="input" type="text" name="instructor"></div>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">💾 Save Course</button>
                <button type="button" class="btn btn-ghost" onclick="closeModal('addCourseModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="addEnrollmentModal" class="modal-backdrop">
    <div class="modal">
        <div class="modal-head">
            <h3>＋ New Enrollment</h3>
            <button class="close-btn" onclick="closeModal('addEnrollmentModal')">✕</button>
        </div>
        <form id="addEnrollmentForm">
            <div style="display:flex;flex-direction:column;gap:14px;">
                <div class="field"><label>STUDENT</label><select class="select" name="student_id" id="enrollmentStudent" required><option value="">Select student</option></select></div>
                <div class="field"><label>COURSE</label><select class="select" name="course_id" id="enrollmentCourse" required><option value="">Select course</option></select></div>
                <div class="field"><label>STATUS</label><select class="select" name="status"><option>Active</option><option>Completed</option><option>Withdrawn</option></select></div>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">💾 Enroll</button>
                <button type="button" class="btn btn-ghost" onclick="closeModal('addEnrollmentModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="addGradeModal" class="modal-backdrop">
    <div class="modal">
        <div class="modal-head">
            <h3>＋ Add Grade</h3>
            <button class="close-btn" onclick="closeModal('addGradeModal')">✕</button>
        </div>
        <form id="addGradeForm">
            <div class="field" style="margin-bottom:14px;"><label>ENROLLMENT</label><select class="select" name="enrollment_id" id="gradeEnrollment" required><option value="">Select student – course</option></select></div>
            <div class="form-grid">
                <div class="field"><label>MIDTERM</label><input class="input" type="number" name="midterm" step="0.01" min="0" max="100"></div>
                <div class="field"><label>FINAL</label><input class="input" type="number" name="final" step="0.01" min="0" max="100"></div>
                <div class="field full"><label>ASSIGNMENT</label><input class="input" type="number" name="assignment" step="0.01" min="0" max="100"></div>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">💾 Save Grade</button>
                <button type="button" class="btn btn-ghost" onclick="closeModal('addGradeModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="addAttendanceModal" class="modal-backdrop">
    <div class="modal">
        <div class="modal-head">
            <h3>＋ Record Attendance</h3>
            <button class="close-btn" onclick="closeModal('addAttendanceModal')">✕</button>
        </div>
        <form id="addAttendanceForm">
            <div class="field" style="margin-bottom:14px;"><label>ENROLLMENT</label><select class="select" name="enrollment_id" id="attendanceEnrollment" required><option value="">Select student – course</option></select></div>
            <div class="form-grid">
                <div class="field"><label>DATE</label><input class="input" type="date" name="date" required></div>
                <div class="field"><label>STATUS</label><select class="select" name="status"><option>Present</option><option>Absent</option><option>Late</option></select></div>
                <div class="field full"><label>NOTES</label><textarea class="textarea" name="notes" rows="2"></textarea></div>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">💾 Record</button>
                <button type="button" class="btn btn-ghost" onclick="closeModal('addAttendanceModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
const userRole = '<?php echo $userRole; ?>';
const isAdmin  = userRole === 'Admin';

/* ── TAB SWITCHING via navbar (delegated) ── */
function openTab(id) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-tab').forEach(b => b.classList.remove('active'));
    const panel = document.getElementById(id);
    if (panel) panel.classList.add('active');
    const btn = document.querySelector(`.nav-tab[href*="#${id}"]`);
    if (btn) btn.classList.add('active');

    if (id === 'dashboard')   loadDashboard();
    else if (id === 'students')   loadStudents();
    else if (id === 'courses')    loadCourses();
    else if (id === 'enrollments'){ loadEnrollments(); loadEnrollmentSelects(); }
    else if (id === 'grades')     { loadGrades(); loadGradeSelects(); }
    else if (id === 'attendance') { loadAttendance(); loadAttendanceSelects(); }
}

document.querySelectorAll('.nav-tab[href^="index.php#"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        const id = a.getAttribute('href').split('#')[1];
        history.replaceState(null, '', `#${id}`);
        openTab(id);
    });
});

window.addEventListener('load', () => {
    const hash = location.hash.replace('#','');
    if (hash) openTab(hash); else loadDashboard();
});

/* ── MODALS ── */
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
document.querySelectorAll('.modal-backdrop').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('active'); });
});

/* ── TOAST ── */
function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<span>${type === 'success' ? '✓' : '⊗'}</span> ${msg}`;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 2800);
}

/* ── DATA LOADERS ── */
function loadDashboard() {
    fetch('api.php?action=getDashboardStats')
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            document.getElementById('dashboardStats').innerHTML = `
                <div class="stat fade-in"><div class="stat-icon">🎓</div><div class="stat-num">${data.totalStudents}</div><div class="stat-label">Total Students</div></div>
                <div class="stat fade-in"><div class="stat-icon">📚</div><div class="stat-num">${data.totalCourses}</div><div class="stat-label">Total Courses</div></div>
                <div class="stat fade-in"><div class="stat-icon">📝</div><div class="stat-num">${data.activeEnrollments}</div><div class="stat-label">Active Enrollments</div></div>
                <div class="stat fade-in"><div class="stat-icon">⭐</div><div class="stat-num">${data.averageGPA}</div><div class="stat-label">Average GPA</div></div>
            `;
        });
    fetch('api.php?action=getTopStudents')
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.students) return;
            let html = '';
            data.students.forEach(s => {
                html += `<tr><td><strong>${s.StudentID}</strong></td><td>${s.Name}</td><td><span class="badge badge-blue">${s.Department}</span></td><td>${s.Year}</td><td><span class="badge badge-accent">${s.GPA}</span></td></tr>`;
            });
            document.querySelector('#topStudentsTable tbody').innerHTML = html || '<tr><td colspan="5" class="empty">No data</td></tr>';
        });
}

function loadStudents() {
    fetch('api.php?action=getStudents')
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                document.querySelector('#studentsTable tbody').innerHTML = `<tr><td colspan="8" class="empty">Error: ${data.message || 'Unknown'}</td></tr>`;
                return;
            }
            let html = '';
            (data.students || []).forEach(s => {
                html += `<tr>
                    <td><strong>${s.StudentID}</strong></td>
                    <td>${s.Name}</td>
                    <td>${s.Email}</td>
                    <td>${s.Phone || '—'}</td>
                    <td><span class="badge badge-blue">${s.Department}</span></td>
                    <td>${s.Year}</td>
                    <td>${s.IPAddress ? `<span class="badge badge-accent">${s.IPAddress}</span>` : '<span style="color:var(--muted)">—</span>'}</td>
                    <td>${isAdmin ? `<button class="btn btn-danger btn-sm" onclick="deleteStudent(${s.StudentID})">Delete</button>` : '—'}</td>
                </tr>`;
            });
            document.querySelector('#studentsTable tbody').innerHTML = html || '<tr><td colspan="8" class="empty">No students found</td></tr>';
        })
        .catch(err => {
            document.querySelector('#studentsTable tbody').innerHTML = `<tr><td colspan="8" class="empty">Network error</td></tr>`;
        });
}

document.getElementById('studentSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#studentsTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

function deleteStudent(id) {
    if (!confirm('Delete this student?')) return;
    fetch('api.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: `action=deleteStudent&id=${id}` })
        .then(r => r.json())
        .then(data => {
            if (data.success) { showToast('Student deleted'); loadStudents(); }
            else showToast(data.message, 'error');
        });
}

function loadCourses() {
    fetch('api.php?action=getCourses').then(r => r.json()).then(data => {
        if (!data.success) return;
        let html = '';
        data.courses.forEach(c => {
            html += `<tr>
                <td><span class="badge badge-amber">${c.CourseCode}</span></td>
                <td><strong>${c.CourseName}</strong></td>
                <td>${c.Department}</td>
                <td>${c.Credits}</td>
                <td>${c.Semester}</td>
                <td>${c.InstructorName}</td>
                <td><span class="badge badge-accent">${c.students || 0}</span></td>
            </tr>`;
        });
        document.querySelector('#coursesTable tbody').innerHTML = html || '<tr><td colspan="7" class="empty">No courses</td></tr>';
    });
}

function loadEnrollments() {
    fetch('api.php?action=getEnrollments').then(r => r.json()).then(data => {
        if (!data.success) return;
        let html = '';
        data.enrollments.forEach(e => {
            const cls = e.Status === 'Active' ? 'badge-success' : (e.Status === 'Completed' ? 'badge-blue' : 'badge-danger');
            html += `<tr>
                <td><strong>${e.EnrollmentID}</strong></td>
                <td>${e.StudentName}</td>
                <td>${e.CourseName}</td>
                <td>${e.EnrollmentDate}</td>
                <td><span class="badge ${cls}">${e.Status}</span></td>
                <td>${isAdmin ? `<button class="btn btn-danger btn-sm" onclick="deleteEnrollment(${e.EnrollmentID})">Withdraw</button>` : '—'}</td>
            </tr>`;
        });
        document.querySelector('#enrollmentsTable tbody').innerHTML = html || '<tr><td colspan="6" class="empty">No enrollments</td></tr>';
    });
}

function deleteEnrollment(id) {
    if (!confirm('Withdraw this enrollment?')) return;
    fetch('api.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: `action=deleteEnrollment&id=${id}` })
        .then(r => r.json())
        .then(data => {
            if (data.success) { showToast('Withdrawn'); loadEnrollments(); }
            else showToast(data.message, 'error');
        });
}

function loadEnrollmentSelects() {
    fetch('api.php?action=getStudentsForSelect').then(r => r.json()).then(data => {
        if (!data.success) return;
        document.getElementById('enrollmentStudent').innerHTML =
            '<option value="">Select student</option>' + data.students.map(s => `<option value="${s.StudentID}">${s.Name}</option>`).join('');
    });
    fetch('api.php?action=getCoursesForSelect').then(r => r.json()).then(data => {
        if (!data.success) return;
        document.getElementById('enrollmentCourse').innerHTML =
            '<option value="">Select course</option>' + data.courses.map(c => `<option value="${c.CourseID}">${c.CourseCode} – ${c.CourseName}</option>`).join('');
    });
}

function loadGrades() {
    fetch('api.php?action=getGrades').then(r => r.json()).then(data => {
        if (!data.success) return;
        let html = '';
        data.grades.forEach(g => {
            let cls = 'grade-a';
            if (g.LetterGrade?.includes('B')) cls = 'grade-b';
            else if (g.LetterGrade?.includes('C')) cls = 'grade-c';
            else if (g.LetterGrade === 'D') cls = 'grade-d';
            else if (g.LetterGrade === 'F') cls = 'grade-f';
            html += `<tr>
                <td><strong>${g.StudentName}</strong></td>
                <td>${g.CourseName}</td>
                <td>${g.MidtermGrade ?? '—'}</td>
                <td>${g.FinalGrade ?? '—'}</td>
                <td>${g.AssignmentGrade ?? '—'}</td>
                <td><strong>${g.TotalGrade ?? '—'}</strong></td>
                <td><span class="grade-circle ${cls}">${g.LetterGrade || 'N/A'}</span></td>
                <td><span class="badge badge-accent">${g.GPA ?? '—'}</span></td>
            </tr>`;
        });
        document.querySelector('#gradesTable tbody').innerHTML = html || '<tr><td colspan="8" class="empty">No grades</td></tr>';
    });
}

function loadGradeSelects() {
    fetch('api.php?action=getEnrollmentsForSelect').then(r => r.json()).then(data => {
        if (!data.success) return;
        document.getElementById('gradeEnrollment').innerHTML =
            '<option value="">Select student – course</option>' + data.enrollments.map(e => `<option value="${e.EnrollmentID}">${e.label}</option>`).join('');
    });
}

function loadAttendance() {
    fetch('api.php?action=getAttendance').then(r => r.json()).then(data => {
        if (!data.success) return;
        let html = '';
        data.attendance.forEach(a => {
            const cls = a.Status === 'Present' ? 'badge-success' : (a.Status === 'Absent' ? 'badge-danger' : 'badge-warning');
            html += `<tr>
                <td><strong>${a.AttendanceID}</strong></td>
                <td>${a.StudentName}</td>
                <td>${a.CourseName}</td>
                <td>${a.AttendanceDate}</td>
                <td><span class="badge ${cls}">${a.Status}</span></td>
                <td>${a.Notes || '—'}</td>
            </tr>`;
        });
        document.querySelector('#attendanceTable tbody').innerHTML = html || '<tr><td colspan="6" class="empty">No records</td></tr>';
    });
}

function loadAttendanceSelects() {
    fetch('api.php?action=getEnrollmentsForSelect').then(r => r.json()).then(data => {
        if (!data.success) return;
        document.getElementById('attendanceEnrollment').innerHTML =
            '<option value="">Select student – course</option>' + data.enrollments.map(e => `<option value="${e.EnrollmentID}">${e.label}</option>`).join('');
    });
}

/* ── FORM SUBMISSIONS ── */
function bindForm(formId, action, modalId, onSuccess) {
    document.getElementById(formId)?.addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('action', action);
        fetch('api.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Saved successfully');
                    closeModal(modalId);
                    this.reset();
                    onSuccess?.();
                } else showToast(data.message || 'Failed', 'error');
            });
    });
}

bindForm('addStudentForm',    'addStudent',    'addStudentModal',    () => loadStudents());
bindForm('addCourseForm',     'addCourse',     'addCourseModal',     () => loadCourses());
bindForm('addEnrollmentForm', 'addEnrollment', 'addEnrollmentModal', () => { loadEnrollments(); loadGradeSelects(); loadAttendanceSelects(); });
bindForm('addGradeForm',      'addGrade',      'addGradeModal',      () => { loadGrades(); loadDashboard(); });
bindForm('addAttendanceForm', 'addAttendance', 'addAttendanceModal', () => loadAttendance());
</script>

</body>
</html>
