<?php require '_menu.php'; require_perm('students'); $id = (int)($_GET['id'] ?? 0);
$s = q("SELECT u.*, p.* , u.id uid FROM users u LEFT JOIN student_profiles p ON p.user_id=u.id WHERE u.id=? AND u.role='student'", [$id])->fetch(); if (!$s) go('admin/students.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check(); $f = array_map('trim', $_POST);
    q('UPDATE users SET full_name=?, class_name=? WHERE id=?', [$f['full_name'], $f['class_name'], $id]);
    q('INSERT INTO student_profiles (user_id,admission_no,dob,gender,guardian_name,guardian_phone,guardian_email,address,medical_notes,admitted_on) VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE admission_no=VALUES(admission_no),dob=VALUES(dob),gender=VALUES(gender),guardian_name=VALUES(guardian_name),guardian_phone=VALUES(guardian_phone),guardian_email=VALUES(guardian_email),address=VALUES(address),medical_notes=VALUES(medical_notes),admitted_on=VALUES(admitted_on)',
      [$id, $f['admission_no'], $f['dob'] ?: null, $f['gender'], $f['guardian_name'], $f['guardian_phone'], $f['guardian_email'], $f['address'], $f['medical_notes'], $f['admitted_on'] ?: null]);
    audit('student_record_saved', 'student ' . $id); flash('Record saved.'); go('admin/student.php?id=' . $id); }
$sess = q('SELECT DISTINCT s.id,s.title FROM exam_sessions s JOIN results r ON r.session_id=s.id WHERE r.student_id=? ORDER BY s.id DESC', [$id])->fetchAll();
app_top('Student record', $menu, $area); ?><h1><?= e($s['full_name']) ?></h1><p><a href="students.php">Back to students</a></p>
<div class="panel"><h3>Academic summary</h3><p>CGPA: <strong><?= $sess ? number_format(gpa($id), 2) : '-' ?></strong> / 5.0</p>
<?php foreach ($sess as $x) echo '<p style="margin:0 0 4px">' . e($x['title']) . ': GPA ' . number_format(gpa($id, $x['id']), 2) . ' &middot; <a href="reportcard.php?student=' . $id . '&session=' . $x['id'] . '">Report card</a></p>'; if (!$sess) echo '<p>No results yet.</p>'; ?></div>
<form method="post" class="panel f" style="max-width:none"><?= csrf_field() ?><h3>Student details</h3>
<div class="row"><div><label>Full name</label><input name="full_name" required value="<?= e($s['full_name']) ?>"></div><div><label>Class</label><input name="class_name" value="<?= e($s['class_name']) ?>"></div></div>
<div class="row"><div><label>Admission number</label><input name="admission_no" value="<?= e($s['admission_no']) ?>"></div><div><label>Date admitted</label><input type="date" name="admitted_on" value="<?= e($s['admitted_on']) ?>"></div></div>
<div class="row"><div><label>Date of birth</label><input type="date" name="dob" value="<?= e($s['dob']) ?>"></div><div><label>Gender</label><select name="gender"><?= options(['' => 'Not set', 'Female' => 'Female', 'Male' => 'Male'], $s['gender']) ?></select></div></div>
<div class="row"><div><label>Parent or guardian</label><input name="guardian_name" value="<?= e($s['guardian_name']) ?>"></div><div><label>Guardian phone</label><input name="guardian_phone" value="<?= e($s['guardian_phone']) ?>"></div></div>
<div class="row"><div><label>Guardian email</label><input type="email" name="guardian_email" value="<?= e($s['guardian_email']) ?>"></div><div><label>Home address</label><input name="address" value="<?= e($s['address']) ?>"></div></div>
<div><label>Medical notes (allergies, conditions)</label><textarea name="medical_notes"><?= e($s['medical_notes']) ?></textarea></div><div><button class="btn">Save record</button></div></form><?php app_bottom();
