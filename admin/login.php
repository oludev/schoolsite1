<?php require '../config.php'; require '../includes/app.php';
if (user() && in_array(user()['role'], ['admin', 'principal', 'hr'], true)) go('admin/index.php');
do_login(['admin', 'principal', 'hr'], 'admin/index.php', 'Admin login', 'For school administrators, the principal and the HR office.');
