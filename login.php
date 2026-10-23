<?php require __DIR__ . '/config.php'; require __DIR__ . '/includes/app.php';
// One login page for everyone. Each role is sent to its own area after signing in.
$dest = ['admin' => 'admin/index.php', 'principal' => 'admin/index.php', 'hr' => 'admin/index.php', 'teacher' => 'portal/index.php', 'student' => 'portal/index.php', 'parent' => 'portal/index.php'];
if (user()) go($dest[user()['role']] ?? 'index.php');
do_login(array_keys($dest), $dest, 'Login', 'School Management System, Administrators, teachers, students and parents all sign in here. Use the username the school gave you.');
