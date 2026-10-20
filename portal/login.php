<?php require '../config.php'; require '../includes/app.php';
if (user() && in_array(user()['role'], ['teacher', 'student'], true)) go('portal/index.php');
do_login(['teacher', 'student'], 'portal/index.php', 'E-portal login', 'For teachers and students only. Use the username given to you by the school.');
