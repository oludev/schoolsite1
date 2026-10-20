<?php require '_menu.php'; require_perm('users');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    if (isset($_POST['toggle'])) { q('UPDATE users SET active=1-active WHERE id=? AND role<>\'admin\'', [(int)$_POST['toggle']]); audit('account_toggled', $_POST['toggle']); flash('Account updated.'); }
    elseif (isset($_POST['reset'])) { q('UPDATE users SET must_change=1, password_hash=? WHERE id=? AND role<>\'admin\'', [password_hash('Password@123', PASSWORD_DEFAULT), (int)$_POST['reset']]); audit('password_reset', $_POST['reset']); flash('Password reset to Password@123. Ask the user to change it after login.'); }
    else { $f = array_map('trim', $_POST);
        if (!$f['full_name'] || !$f['username'] || strlen($f['password']) < 6 || !in_array($f['role'], ['teacher', 'student', 'principal', 'hr'], true)) flash('Fill in name, username, role and a password of at least 6 characters.', 'err');
        elseif (q('SELECT 1 FROM users WHERE username=?', [$f['username']])->fetch()) flash('That username is taken.', 'err');
        else { q('INSERT INTO users (role,full_name,username,email,class_name,password_hash,must_change) VALUES (?,?,?,?,?,?,1)', [$f['role'], $f['full_name'], $f['username'], $f['email'], $f['class_name'], password_hash($f['password'], PASSWORD_DEFAULT)]); audit('account_created', $f['username'] . ' (' . $f['role'] . ')'); flash('Account created. The user must choose a new password at first login.'); } }
    go('admin/users.php'); }
$r = $_GET['r'] ?? ''; $rows = in_array($r, ['teacher', 'student', 'principal', 'hr'], true) ? q("SELECT * FROM users WHERE role=? ORDER BY class_name, full_name", [$r])->fetchAll() : q("SELECT * FROM users WHERE role<>'admin' ORDER BY role, class_name, full_name")->fetchAll();
app_top('Teachers & students', $menu, $area); ?>
<h1>Accounts & roles</h1><p class="mini">Admin: everything. Principal: grades, report cards, students, exam timetable, notices, applications. HR / Accountant: staff records, payroll, leave. Teachers and students use the e-portal.</p>
<form method="post" class="panel f" style="max-width:none"><?= csrf_field() ?><h3>Create an account</h3>
<div class="row"><div><label>Full name</label><input name="full_name" required></div><div><label>Role</label><select name="role"><option value="student">Student</option><option value="teacher">Teacher</option><option value="principal">Principal</option><option value="hr">HR / Accountant</option></select></div></div>
<div class="row"><div><label>Username (used to log in)</label><input name="username" required></div><div><label>Temporary password</label><input name="password" required minlength="6"></div></div>
<div class="row"><div><label>Class (students only, e.g. JSS 1)</label><input name="class_name"></div><div><label>Email (optional)</label><input type="email" name="email"></div></div>
<div><button class="btn">Create account</button></div></form>
<div class="chips"><a href="users.php" class="<?= $r ? '' : 'on' ?>">Everyone</a><a href="users.php?r=teacher" class="<?= $r === 'teacher' ? 'on' : '' ?>">Teachers</a><a href="users.php?r=student" class="<?= $r === 'student' ? 'on' : '' ?>">Students</a><a href="users.php?r=principal" class="<?= $r === 'principal' ? 'on' : '' ?>">Principal</a><a href="users.php?r=hr" class="<?= $r === 'hr' ? 'on' : '' ?>">HR</a></div>
<div class="tw"><table><thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Class</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $x): ?><tr><td><?= e($x['full_name']) ?></td><td><?= e($x['username']) ?></td><td><?= e(ucfirst($x['role'])) ?></td><td><?= e($x['class_name']) ?></td><td><?= $x['active'] ? 'Active' : 'Disabled' ?></td>
<td><form method="post" class="acts"><?= csrf_field() ?><button class="btn small ghost" name="toggle" value="<?= $x['id'] ?>"><?= $x['active'] ? 'Disable' : 'Enable' ?></button><button class="btn small ghost" name="reset" value="<?= $x['id'] ?>" onclick="return confirm('Reset this password to Password@123?')">Reset password</button></form></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="6">No accounts yet.</td></tr>'; ?></tbody></table></div>
<?php app_bottom();
