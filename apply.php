<?php $title = 'Apply now'; require 'includes/header.php'; [$st, $msg] = admissions_state();
$err = ''; $done = '';
if ($st === 'open' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $f = array_map('trim', $_POST);
    foreach (['child_name','dob','section','class_applied','parent_name','phone'] as $k) if (empty($f[$k])) $err = 'Please fill in all fields marked required.';
    if (!$err && !empty($f['email']) && !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $err = 'Enter a valid email address.';
    if (!$err) {
        $ref = 'APP' . date('y') . strtoupper(bin2hex(random_bytes(3)));
        q('INSERT INTO applications (ref_no,child_name,dob,gender,section,class_applied,previous_school,parent_name,phone,email,address) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
          [$ref, $f['child_name'], $f['dob'], $f['gender'] ?? '', $f['section'], $f['class_applied'], $f['previous_school'] ?? '', $f['parent_name'], $f['phone'], $f['email'] ?? '', $f['address'] ?? '']);
        $done = $ref;
    }
}
$classes = array_merge(['Nursery 1', 'Nursery 2'], CLASSES);
?>
<div class="pagehead"><div class="wrap"><h1>Apply now</h1><p><?= e($msg) ?></p></div></div>
<section class="s"><div class="wrap">
<?php if ($done): ?>
  <div class="note ok"><strong>Application received.</strong> Your reference number is <strong><?= e($done) ?></strong>. Keep it safe. We will contact you by phone or email about the next step.</div>
<?php elseif ($st !== 'open'): ?>
  <div class="panel"><h2>Applications are closed right now</h2><p><?= e($msg) ?></p><a class="btn" href="<?= url('contact.php') ?>">Contact admissions</a></div>
<?php else: ?>
  <?php if ($err) echo '<div class="note err">' . e($err) . '</div>'; ?>
  <form class="f" method="post"><?= csrf_field() ?>
   <h2>Child's details</h2>
   <div class="row"><div><label>Full name *</label><input name="child_name" required value="<?= e($_POST['child_name'] ?? '') ?>"></div><div><label>Date of birth *</label><input type="date" name="dob" required value="<?= e($_POST['dob'] ?? '') ?>"></div></div>
   <div class="row"><div><label>Gender</label><select name="gender"><option>Female</option><option>Male</option></select></div>
     <div><label>Section *</label><select name="section" required><option>Primary</option><option>Secondary</option></select></div></div>
   <div class="row"><div><label>Class applying for *</label><select name="class_applied" required><?php foreach ($classes as $c) echo '<option>' . e($c) . '</option>'; ?></select></div>
     <div><label>Previous school</label><input name="previous_school"></div></div>
   <h2>Parent or guardian</h2>
   <div class="row"><div><label>Full name *</label><input name="parent_name" required></div><div><label>Phone *</label><input name="phone" type="tel" required></div></div>
   <div class="row"><div><label>Email</label><input name="email" type="email"></div><div><label>Home address</label><input name="address"></div></div>
   <div><button class="btn" type="submit">Submit application</button></div></form>
<?php endif; ?></div></section>
<?php require 'includes/footer.php'; ?>
