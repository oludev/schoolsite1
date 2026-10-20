<?php /* Menu and access check for the ADMIN area (admin, principal, HR). The other folder has its own _menu.php: that is correct. */ require_once __DIR__ . '/../config.php'; require_once __DIR__ . '/../includes/app.php'; require_once __DIR__ . '/../includes/academic.php'; require_once __DIR__ . '/../includes/hr.php';
$u = require_role(['admin', 'principal', 'hr'], 'admin/login.php');
$all = ['index.php' => ['Dashboard', 'dashboard'], 'applications.php' => ['Applications', 'applications'], 'students.php' => ['Student records', 'students'], 'import.php' => ['Import students', 'users'], 'promote.php' => ['Promote classes', 'users'], 'grades.php' => ['Grade book', 'grades'],
  'reportcards.php' => ['Report cards', 'reports'], 'schedule.php' => ['Exam timetable', 'schedule'], 'notices.php' => ['Notice board', 'notices'],
  'staff.php' => ['Staff & HR', 'staff'], 'payroll.php' => ['Payroll', 'payroll'], 'leave.php' => ['Leave requests', 'leave_admin'], 'users.php' => ['Accounts & roles', 'users'],
  'manage.php?t=posts' => ['News & blog', 'content'], 'manage.php?t=events' => ['Events', 'content'], 'manage.php?t=exams' => ['Exam sessions', 'content'], 'manage.php?t=gallery' => ['Gallery', 'content'],
  'manage.php?t=activities' => ['Activities', 'content'], 'manage.php?t=faqs' => ['FAQs', 'content'], 'manage.php?t=testimonials' => ['Testimonials', 'content'], 'messages.php' => ['Messages', 'content'], 'audit.php' => ['Audit log', 'audit'], 'settings.php' => ['Settings', 'settings']];
$menu = []; foreach ($all as $h => [$l, $p]) if (can($p)) $menu[$h] = $l; $menu['password.php'] = 'My password';
$area = ['admin' => 'Admin', 'principal' => 'Principal', 'hr' => 'HR & payroll'][$u['role']];
