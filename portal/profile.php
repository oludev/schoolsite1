<?php require '_menu.php'; if ($u['role'] !== 'student') go('portal/index.php');
$p = q('SELECT u.full_name,u.class_name,u.username,p.* FROM users u LEFT JOIN student_profiles p ON p.user_id=u.id WHERE u.id=?', [$u['id']])->fetch();
app_top('My record', $menu, $area); ?><h1>My record</h1><p class="mini">This is the information the school holds about you. Ask the school office to correct anything that is wrong.</p>
<div class="panel"><div class="split">
<div><p><strong>Name:</strong> <?= e($p['full_name']) ?><br><strong>Class:</strong> <?= e($p['class_name']) ?><br><strong>Admission no:</strong> <?= e($p['admission_no'] ?: '-') ?><br><strong>Date of birth:</strong> <?= $p['dob'] ? date('j F Y', strtotime($p['dob'])) : '-' ?><br><strong>Gender:</strong> <?= e($p['gender'] ?: '-') ?><br><strong>Admitted:</strong> <?= $p['admitted_on'] ? date('j F Y', strtotime($p['admitted_on'])) : '-' ?></p></div>
<div><p><strong>Parent or guardian:</strong> <?= e($p['guardian_name'] ?: '-') ?><br><strong>Phone:</strong> <?= e($p['guardian_phone'] ?: '-') ?><br><strong>Email:</strong> <?= e($p['guardian_email'] ?: '-') ?><br><strong>Address:</strong> <?= e($p['address'] ?: '-') ?></p></div></div></div>
<?php app_bottom();
