<?php /* Menu and access check for the E-PORTAL (teachers and students). The other folder has its own _menu.php: that is correct. */ require_once __DIR__ . '/../config.php'; require_once __DIR__ . '/../includes/app.php'; require_once __DIR__ . '/../includes/academic.php'; require_once __DIR__ . '/../includes/hr.php';
$u = require_role(['teacher', 'student'], 'portal/login.php');
$menu = $u['role'] === 'teacher'
  ? ['index.php' => 'Dashboard', 'scores.php' => 'Enter scores (by student)', 'scores_subject.php' => 'Enter scores (by subject)', 'term_records.php' => 'Attendance & activities', 'gradebook.php' => 'Grade book', 'reportcards.php' => 'Report cards', 'timetable.php' => 'Exam timetable', 'students.php' => 'Student records', 'notices.php' => 'Notice board', 'hr.php' => 'My HR & payslips', 'leave.php' => 'Leave', 'password.php' => 'Password']
  : ['index.php' => 'Dashboard', 'results.php' => 'My results', 'tracking.php' => 'Grade tracking', 'reportcard.php' => 'Report card', 'timetable.php' => 'Exam timetable', 'notices.php' => 'Notice board', 'profile.php' => 'My record', 'password.php' => 'Password'];
$area = $u['role'] === 'teacher' ? 'Teacher portal' : 'Student portal';
