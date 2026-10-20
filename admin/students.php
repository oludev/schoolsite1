<?php require '_menu.php'; require_perm('students'); $f = trim($_GET['q'] ?? ''); $cl = $_GET['class'] ?? '';
$w = "u.role='student'"; $p = [];
if ($f) { $w .= ' AND (u.full_name LIKE ? OR u.username LIKE ? OR p.admission_no LIKE ?)'; array_push($p, "%$f%", "%$f%", "%$f%"); }
if ($cl) { $w .= ' AND u.class_name=?'; $p[] = $cl; }
$rows = q("SELECT u.*, p.admission_no, p.guardian_phone FROM users u LEFT JOIN student_profiles p ON p.user_id=u.id WHERE $w ORDER BY u.class_name, u.full_name", $p)->fetchAll();
$cnt = q("SELECT class_name, COUNT(*) FROM users WHERE role='student' AND active=1 GROUP BY class_name")->fetchAll(PDO::FETCH_KEY_PAIR);
app_top('Student records', $menu, $area); ?><h1>Student records</h1>
<div class="chips noprint"><a href="students.php" class="<?= $cl ? '' : 'on' ?>">All classes</a><?php foreach (class_list() as $c) echo '<a href="students.php?class=' . urlencode($c) . '" class="' . ($c === $cl ? 'on' : '') . '">' . e($c) . ' (' . (int)($cnt[$c] ?? 0) . ')</a>'; ?></div>
<?php if (can('users')): ?><p class="mini noprint">Have a list of students? <a href="import.php">Import them from a spreadsheet</a> in one go.</p><?php endif; ?>
<form class="panel f noprint" style="max-width:none"><div class="row"><div><label>Search name, username or admission no</label><input name="q" value="<?= e($f) ?>"></div><div><label>Class</label><select name="class"><option value="">All classes</option><?= options(array_combine(class_list(), class_list()), $cl) ?></select></div></div><div><button class="btn">Search</button></div></form>
<p class="mini">New students are created under Accounts & roles, then completed here.</p>
<div class="tw scroll"><table><thead><tr><th>Name</th><th>Class</th><th>Admission no</th><th>Guardian phone</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $s) echo '<tr><td>' . e($s['full_name']) . '</td><td>' . e($s['class_name']) . '</td><td>' . e($s['admission_no']) . '</td><td>' . e($s['guardian_phone']) . '</td><td>' . ($s['active'] ? 'Active' : 'Disabled') . '</td><td><a href="student.php?id=' . $s['id'] . '">Open record</a></td></tr>'; if (!$rows) echo '<tr><td colspan="6">No students found.</td></tr>'; ?></tbody></table></div><?php app_bottom();
