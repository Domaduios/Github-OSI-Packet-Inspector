<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
include 'config.php';
$activeTab = 'courseroster';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Enrollments — SMS</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .page { max-width: 1400px; margin: 0 auto; padding: 24px; }
        .page-head { margin-bottom: 24px; }
        .page-title { font-size: 24px; font-weight: 800; color: var(--text); }
        .page-sub { color: var(--text-muted); font-size: 14px; margin-top: 4px; }

        .layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 20px;
        }
        @media (max-width: 1000px) { .layout { grid-template-columns: 1fr; } }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
        }
        .panel-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .badge {
            background: var(--teal-bg);
            color: var(--teal);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .course-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 700px;
            overflow-y: auto;
        }
        .course-item {
            background: var(--bg);
            border: 1px solid var(--border);
            border-left: 3px solid transparent;
            border-radius: 10px;
            padding: 12px 14px;
            cursor: pointer;
            transition: all .15s;
        }
        .course-item:hover {
            border-color: var(--teal);
            border-left-color: var(--teal);
            transform: translateX(2px);
        }
        .course-item.active {
            border-left-color: var(--teal);
            background: var(--teal-bg);
        }
        .course-code {
            font-family: var(--mono);
            font-weight: 700;
            color: var(--teal);
            font-size: 12px;
            margin-bottom: 4px;
        }
        .course-name {
            font-weight: 600;
            color: var(--text);
            font-size: 14px;
            margin-bottom: 6px;
        }
        .course-meta {
            display: flex;
            gap: 10px;
            font-size: 11px;
            color: var(--text-muted);
            flex-wrap: wrap;
        }
        .course-stats {
            margin-top: 8px;
            display: flex;
            gap: 8px;
            font-size: 11px;
            font-family: var(--mono);
        }
        .stat-mini {
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
        }
        .stat-mini.active { background: rgba(34,197,94,.15); color: var(--success); }
        .stat-mini.completed { background: rgba(59,130,246,.15); color: var(--blue); }
        .stat-mini.withdrawn { background: rgba(239,68,68,.15); color: var(--danger); }
        .stat-mini.total { background: var(--surface-2); color: var(--text); }

        .detail-panel {
            min-height: 500px;
        }
        .empty-state {
            display: grid;
            place-items: center;
            min-height: 400px;
            color: var(--text-muted);
            text-align: center;
        }
        .empty-state .icon { font-size: 48px; margin-bottom: 14px; opacity: .5; }

        .course-header {
            background: linear-gradient(135deg, var(--teal-bg), rgba(59,130,246,.1));
            border: 1px solid var(--teal);
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 20px;
        }
        .course-header-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 8px;
        }
        .course-header-sub {
            color: var(--text-muted);
            font-size: 13px;
        }
        .course-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px,1fr));
            gap: 12px;
            margin-top: 16px;
        }
        .info-cell {
            background: var(--surface);
            padding: 12px;
            border-radius: 10px;
        }
        .info-cell .label {
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: .5px;
        }
        .info-cell .value {
            font-weight: 700;
            color: var(--text);
            font-size: 14px;
            margin-top: 4px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
        }
        .stat-card .lbl {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }
        .stat-card .val {
            font-size: 22px;
            font-weight: 800;
            font-family: var(--mono);
            color: var(--text);
            margin-top: 4px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: var(--surface);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
        }
        .table th {
            background: var(--surface-2);
            padding: 11px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
        }
        .table td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            color: var(--text);
        }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td { background: var(--surface-2); }

        .student-name { font-weight: 600; }
        .student-email { font-size: 11px; color: var(--text-muted); }

        .status-badge {
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-Active { background: rgba(34,197,94,.15); color: var(--success); }
        .status-Completed { background: rgba(59,130,246,.15); color: var(--blue); }
        .status-Withdrawn { background: rgba(239,68,68,.15); color: var(--danger); }

        .grade-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: var(--mono);
            font-weight: 700;
            font-size: 11px;
        }
        .grade-A { background: rgba(34,197,94,.2); color: var(--success); }
        .grade-B { background: rgba(59,130,246,.2); color: var(--blue); }
        .grade-C { background: rgba(234,179,8,.2); color: #ca8a04; }
        .grade-D { background: rgba(249,115,22,.2); color: #ea580c; }
        .grade-F { background: rgba(239,68,68,.2); color: var(--danger); }

        .attendance-bar {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .attendance-bar-fill {
            flex: 1;
            height: 6px;
            background: var(--surface-2);
            border-radius: 3px;
            overflow: hidden;
            min-width: 60px;
        }
        .attendance-bar-fill::after {
            content: '';
            display: block;
            height: 100%;
            width: var(--w, 0%);
            background: var(--bar-color, var(--teal));
            transition: width .3s;
        }

        .btn-remove {
            background: rgba(239,68,68,.15);
            color: var(--danger);
            border: 1px solid var(--danger);
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: .5px;
            transition: all .15s;
        }
        .btn-remove:hover {
            background: var(--danger);
            color: white;
        }
        .btn-remove:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: var(--surface);
            border: 1px solid var(--success);
            border-left: 4px solid var(--success);
            padding: 14px 20px;
            border-radius: 8px;
            color: var(--text);
            font-size: 13px;
            box-shadow: 0 8px 20px rgba(0,0,0,.3);
            z-index: 1000;
            display: none;
            animation: slideIn .3s;
        }
        .toast.error {
            border-color: var(--danger);
            border-left-color: var(--danger);
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .search-box {
            width: 100%;
            padding: 10px 14px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 13px;
            margin-bottom: 12px;
        }
        .search-box:focus { outline: 2px solid var(--teal); border-color: var(--teal); }

        .loading {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<?php include '_navbar.php'; ?>

<div class="page">
    <div class="page-head">
        <h1 class="page-title">📋 Course Enrollments</h1>
        <p class="page-sub">View students enrolled in each course with grades and attendance</p>
    </div>

    <div class="layout">
        <!-- Left: Course List -->
        <div class="panel">
            <div class="panel-title">
                <span>📚 Courses</span>
                <span class="badge" id="courseCountBadge">0</span>
            </div>
            <input type="text" id="searchInput" class="search-box" placeholder="🔍 Search courses..." onkeyup="filterCourses()">
            <div class="course-list" id="courseList">
                <div class="loading">⟳ Loading courses...</div>
            </div>
        </div>

        <!-- Right: Details -->
        <div class="panel detail-panel" id="detailPanel">
            <div class="empty-state">
                <div class="icon">📋</div>
                <div style="font-size:16px;font-weight:600;margin-bottom:6px;color:var(--text);">Select a course</div>
                <div style="font-size:13px;">Choose a course from the left to see enrolled students</div>
            </div>
        </div>
    </div>
</div>

<script>
let allCourses = [];

// Load courses list
async function loadCourses() {
    try {
        const res = await fetch('api.php?action=getCourseEnrollmentsList');
        const data = await res.json();

        if (data.success) {
            allCourses = data.courses;
            renderCourseList(allCourses);
            document.getElementById('courseCountBadge').textContent = allCourses.length;
            return data;
        } else {
            document.getElementById('courseList').innerHTML = `<div class="loading">⚠️ ${data.message || 'Failed to load'}</div>`;
        }
    } catch (err) {
        document.getElementById('courseList').innerHTML = `<div class="loading">⚠️ Error loading courses</div>`;
    }
}

function renderCourseList(courses) {
    const list = document.getElementById('courseList');
    if (courses.length === 0) {
        list.innerHTML = '<div class="loading">No courses found</div>';
        return;
    }
    list.innerHTML = courses.map(c => `
        <div class="course-item" data-id="${c.CourseID}" onclick="selectCourse(${c.CourseID}, this)">
            <div class="course-code">${c.CourseCode}</div>
            <div class="course-name">${c.CourseName}</div>
            <div class="course-meta">
                <span>👨‍🏫 ${c.InstructorName || 'TBA'}</span>
                <span>•</span>
                <span>${c.Credits} credits</span>
                <span>•</span>
                <span>${c.Semester || 'Fall 2024'}</span>
            </div>
            <div class="course-stats">
                <span class="stat-mini total">📊 ${c.TotalEnrollments}</span>
                <span class="stat-mini active">✓ ${c.ActiveStudents}</span>
                ${c.CompletedStudents > 0 ? `<span class="stat-mini completed">🎓 ${c.CompletedStudents}</span>` : ''}
                ${c.WithdrawnStudents > 0 ? `<span class="stat-mini withdrawn">✕ ${c.WithdrawnStudents}</span>` : ''}
            </div>
        </div>
    `).join('');
}

function filterCourses() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const filtered = allCourses.filter(c =>
        c.CourseCode.toLowerCase().includes(q) ||
        c.CourseName.toLowerCase().includes(q) ||
        (c.InstructorName || '').toLowerCase().includes(q)
    );
    renderCourseList(filtered);
}

async function selectCourse(courseId, el) {
    // Update active state
    document.querySelectorAll('.course-item').forEach(i => i.classList.remove('active'));
    el.classList.add('active');

    const detail = document.getElementById('detailPanel');
    detail.innerHTML = '<div class="loading">⟳ Loading students...</div>';

    try {
        const res = await fetch(`api.php?action=getStudentsInCourse&course_id=${courseId}`);
        const data = await res.json();

        if (!data.success) {
            detail.innerHTML = `<div class="empty-state"><div class="icon">⚠️</div><div>${data.message}</div></div>`;
            return;
        }

        renderCourseDetails(data);
    } catch (err) {
        detail.innerHTML = `<div class="empty-state"><div class="icon">⚠️</div><div>Error: ${err.message}</div></div>`;
    }
}

function renderCourseDetails(data) {
    const c = data.course;
    const students = data.students;
    const stats = data.stats;

    const html = `
        <!-- Course Header -->
        <div class="course-header">
            <div class="course-header-title">${c.CourseName}</div>
            <div class="course-header-sub">${c.CourseCode} • ${c.Department} • Taught by ${c.InstructorName || 'TBA'}</div>
            <div class="course-info-grid">
                <div class="info-cell">
                    <div class="label">Course ID</div>
                    <div class="value">#${c.CourseID}</div>
                </div>
                <div class="info-cell">
                    <div class="label">Credits</div>
                    <div class="value">${c.Credits}</div>
                </div>
                <div class="info-cell">
                    <div class="label">Semester</div>
                    <div class="value">${c.Semester || 'Fall 2024'}</div>
                </div>
                <div class="info-cell">
                    <div class="label">Department</div>
                    <div class="value">${c.Department}</div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="lbl">📊 Total Enrolled</div>
                <div class="val">${stats.total}</div>
            </div>
            <div class="stat-card">
                <div class="lbl">✅ Active</div>
                <div class="val" style="color:var(--success);">${stats.active}</div>
            </div>
            <div class="stat-card">
                <div class="lbl">🎓 Completed</div>
                <div class="val" style="color:var(--blue);">${stats.completed}</div>
            </div>
            <div class="stat-card">
                <div class="lbl">✕ Withdrawn</div>
                <div class="val" style="color:var(--danger);">${stats.withdrawn}</div>
            </div>
            <div class="stat-card">
                <div class="lbl">📈 Avg GPA</div>
                <div class="val">${stats.avg_gpa !== null ? stats.avg_gpa : '—'}</div>
            </div>
        </div>

        <!-- Students Table -->
        ${students.length === 0 ? `
            <div class="empty-state" style="min-height:200px;">
                <div class="icon">👥</div>
                <div>No students enrolled in this course yet</div>
            </div>
        ` : `
            <h3 style="margin-bottom:14px;color:var(--text);font-size:16px;">👥 Enrolled Students (${students.length})</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Year/Dept</th>
                        <th>Enrolled</th>
                        <th>Status</th>
                        <th>Grade</th>
                        <th>GPA</th>
                        <th>Attendance</th>
                        <th>IP</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    ${students.map(s => renderStudentRow(s)).join('')}
                </tbody>
            </table>
        `}
    `;

    document.getElementById('detailPanel').innerHTML = html;
}

function renderStudentRow(s) {
    const grade = s.LetterGrade ? `<span class="grade-pill grade-${s.LetterGrade.charAt(0)}">${s.LetterGrade}</span>` : '—';
    const gpa = s.GPA !== null ? s.GPA : '—';
    const total = s.TotalGrade !== null ? s.TotalGrade : '—';

    let attendanceHtml = '—';
    if (s.AttendancePercent !== null) {
        const color = s.AttendancePercent >= 80 ? 'var(--success)' :
                      s.AttendancePercent >= 60 ? '#eab308' : 'var(--danger)';
        attendanceHtml = `
            <div class="attendance-bar">
                <span style="font-family:var(--mono);font-size:11px;font-weight:700;">${s.AttendancePercent}%</span>
                <div class="attendance-bar-fill" style="--w:${s.AttendancePercent}%;--bar-color:${color};"></div>
                <span style="font-size:11px;color:var(--text-muted);">${s.PresentDays}/${s.TotalDays}</span>
            </div>
        `;
    }

    return `
        <tr>
            <td>
                <div class="student-name">${s.Name}</div>
                <div class="student-email">${s.Email}</div>
            </td>
            <td style="font-size:12px;">Year ${s.Year || '—'}<br><span style="color:var(--text-muted);">${s.Department}</span></td>
            <td style="font-family:var(--mono);font-size:11px;color:var(--text-muted);">${s.EnrollmentDate || '—'}</td>
            <td><span class="status-badge status-${s.EnrollmentStatus}">${s.EnrollmentStatus}</span></td>
            <td>${grade} ${total !== '—' ? `<span style="font-family:var(--mono);font-size:11px;color:var(--text-muted);">(${total})</span>` : ''}</td>
            <td style="font-family:var(--mono);font-weight:700;">${gpa}</td>
            <td>${attendanceHtml}</td>
            <td style="font-family:var(--mono);font-size:11px;color:var(--text-muted);">${s.IPAddress || '—'}</td>
            <td>
                <button class="btn-remove" onclick="removeStudent(${s.EnrollmentID}, '${s.Name.replace(/'/g, "\\'")}', this)">
                    🗑️ Remove
                </button>
            </td>
        </tr>
    `;
}

// Remove student from course
async function removeStudent(enrollmentId, studentName, btn) {
    if (!confirm(`Are you sure you want to remove ${studentName} from this course?\n\nThis will permanently delete:\n• The enrollment\n• Their grades for this course\n• Their attendance records for this course\n\nThis action cannot be undone.`)) {
        return;
    }

    btn.disabled = true;
    btn.textContent = '⟳ Removing...';

    try {
        const formData = new FormData();
        formData.append('action', 'removeStudentFromCourse');
        formData.append('enrollment_id', enrollmentId);

        const res = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            // Remove the row from table with animation
            const row = btn.closest('tr');
            row.style.transition = 'all .3s';
            row.style.opacity = '0';
            row.style.transform = 'translateX(20px)';
            setTimeout(() => row.remove(), 300);

            showToast(`✅ ${data.message}`, 'success');

            // Reload course list to update counts
            setTimeout(() => {
                const activeCard = document.querySelector('.course-item.active');
                if (activeCard) {
                    const courseId = parseInt(activeCard.dataset.id);
                    loadCourses().then(() => {
                        const newCard = document.querySelector(`.course-item[data-id="${courseId}"]`);
                        if (newCard) selectCourse(courseId, newCard);
                    });
                }
            }, 400);
        } else {
            showToast(`❌ ${data.message || 'Failed to remove'}`, 'error');
            btn.disabled = false;
            btn.innerHTML = '🗑️ Remove';
        }
    } catch (err) {
        showToast(`❌ Error: ${err.message}`, 'error');
        btn.disabled = false;
        btn.innerHTML = '🗑️ Remove';
    }
}

function showToast(message, type) {
    let toast = document.querySelector('.toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast';
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.className = 'toast' + (type === 'error' ? ' error' : '');
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3500);
}

// Init
loadCourses();
</script>

</body>
</html>
