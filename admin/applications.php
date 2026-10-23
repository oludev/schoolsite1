<?php require '_menu.php'; require_perm('applications');
if (isset($_GET['export'])) {
    header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="applications.csv"');
    $o = fopen('php://output', 'w'); $rows = q('SELECT ref_no,child_name,dob,gender,section,class_applied,previous_school,parent_name,phone,email,address,status,created_at FROM applications ORDER BY id DESC')->fetchAll();
    fputcsv($o, ['Ref', 'Child', 'DOB', 'Gender', 'Section', 'Class', 'Previous school', 'Parent', 'Phone', 'Email', 'Address', 'Status', 'Applied'], ',', '"', '');
    foreach ($rows as $r) fputcsv($o, $r, ',', '"', ''); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    if (isset($_POST['delete'])) q('DELETE FROM applications WHERE id=?', [(int)$_POST['delete']]);
    elseif (in_array($_POST['status'] ?? '', ['New', 'Reviewing', 'Admitted', 'Declined'], true)) q('UPDATE applications SET status=? WHERE id=?', [$_POST['status'], (int)$_POST['id']]);
    audit('application_updated', $_POST['id'] ?? ($_POST['delete'] ?? '')); flash('Updated.'); go('admin/applications.php' . (!empty($_GET['s']) ? '?s=' . urlencode($_GET['s']) : '')); }
$s = $_GET['s'] ?? ''; $rows = $s ? q('SELECT * FROM applications WHERE status=? ORDER BY id DESC', [$s])->fetchAll() : q('SELECT * FROM applications ORDER BY id DESC')->fetchAll();
app_top('Applications', $menu, $area); ?>
<h1>Applications</h1>
<div class="chips"><a href="applications.php" class="<?= $s ? '' : 'on' ?>">All</a><?php foreach (['New', 'Reviewing', 'Admitted', 'Declined'] as $x) echo '<a href="applications.php?s=' . $x . '" class="' . ($s === $x ? 'on' : '') . '">' . $x . '</a>'; ?><a href="applications.php?export=1">Download CSV</a></div>
<div class="tw"><table><thead><tr><th>Ref</th><th>Child</th><th>Class</th><th>Parent and contact</th><th>Applied</th><th>Status</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['ref_no']) ?></td><td><strong><?= e($r['child_name']) ?></strong><br><span style="color:var(--mute)"><?= e($r['gender']) ?>, born <?= date('j M Y', strtotime($r['dob'])) ?></span></td>
<td><?= e($r['class_applied']) ?><br><span style="color:var(--mute)"><?= e($r['previous_school']) ?></span></td><td><?= e($r['parent_name']) ?><br><?= e($r['phone']) ?><br><?= e($r['email']) ?></td><td><?= date('j M Y', strtotime($r['created_at'])) ?><br><a href="application.php?id=<?= $r['id'] ?>" target="_blank">View form</a></td>
<td><form method="post" class="acts"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><select name="status" onchange="this.form.submit()" style="width:130px"><?php foreach (['New', 'Reviewing', 'Admitted', 'Declined'] as $x) echo '<option ' . ($x === $r['status'] ? 'selected' : '') . '>' . $x . '</option>'; ?></select>
<button class="btn small danger" name="delete" value="<?= $r['id'] ?>" onclick="return confirm('Delete this application?')">Delete</button></form></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="6">No applications found.</td></tr>'; ?></tbody></table></div>
<?php app_bottom();
