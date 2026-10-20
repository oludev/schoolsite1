<?php require '_menu.php'; if ($u['role'] !== 'teacher') go('portal/index.php');
$id = (int)($_GET['id'] ?? 0); $p = q("SELECT u.id,u.full_name,u.class_name,p.* FROM users u LEFT JOIN student_profiles p ON p.user_id=u.id WHERE u.id=? AND u.role='student'", [$id])->fetch(); if (!$p) go('portal/students.php');
audit('student_record_view', 'student ' . $id); app_top('Student record', $menu, $area); ?>
<h1><?= e($p['full_name']) ?></h1><p><a href="tracking.php?student=<?= $id ?>">Grade tracking</a> &middot; <a href="reportcard.php?student=<?= $id ?>">Report card</a> &middot; <a href="students.php?class=<?= urlencode($p['class_name']) ?>">Back to class</a></p>
<div class="panel"><div class="split"><div><p><strong>Class:</strong> <?= e($p['class_name']) ?><br><strong>Admission no:</strong> <?= e($p['admission_no'] ?: '-') ?><br><strong>Date of birth:</strong> <?= $p['dob'] ? date('j F Y', strtotime($p['dob'])) : '-' ?><br><strong>Gender:</strong> <?= e($p['gender'] ?: '-') ?><br><strong>CGPA:</strong> <?= number_format((float)gpa($id), 2) ?></p></div>
<div><p><strong>Guardian:</strong> <?= e($p['guardian_name'] ?: '-') ?><br><strong>Phone:</strong> <?= e($p['guardian_phone'] ?: '-') ?><br><strong>Address:</strong> <?= e($p['address'] ?: '-') ?><br><strong>Medical notes:</strong> <?= e($p['medical_notes'] ?: 'None recorded') ?></p></div></div></div>
<?php app_bottom();
