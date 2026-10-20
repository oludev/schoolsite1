<?php require '_menu.php'; require_perm('dashboard'); app_top('Dashboard', $menu, $area);
$c = fn($sql) => (int)q($sql)->fetchColumn(); [$st, $msg] = admissions_state();
$recent = q('SELECT * FROM applications ORDER BY id DESC LIMIT 6')->fetchAll(); ?>
<h1>Dashboard</h1>
<div class="stats">
 <div class="stat"><b><?= $c("SELECT COUNT(*) FROM applications WHERE status='New'") ?></b>New applications</div>
 <div class="stat"><b><?= $c("SELECT COUNT(*) FROM users WHERE role='student' AND active=1") ?></b>Students</div>
 <div class="stat"><b><?= $c("SELECT COUNT(*) FROM users WHERE role='teacher' AND active=1") ?></b>Teachers</div>
 <div class="stat"><b><?= $c("SELECT COUNT(*) FROM messages WHERE is_read=0") ?></b>Unread messages</div></div>
<div class="panel"><span class="pill <?= $st ?>"><?= ucfirst($st) ?></span> <?= e($msg) ?> <a href="settings.php">Change dates</a></div>
<div class="panel"><h3>Latest applications</h3><div class="tw"><table><thead><tr><th>Ref</th><th>Child</th><th>Class</th><th>Parent</th><th>Status</th></tr></thead><tbody>
<?php foreach ($recent as $r) echo '<tr><td>' . e($r['ref_no']) . '</td><td>' . e($r['child_name']) . '</td><td>' . e($r['class_applied']) . '</td><td>' . e($r['parent_name']) . '</td><td>' . e($r['status']) . '</td></tr>';
if (!$recent) echo '<tr><td colspan="5">No applications yet.</td></tr>'; ?></tbody></table></div></div>
<?php app_bottom();
