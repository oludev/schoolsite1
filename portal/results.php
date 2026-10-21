<?php require '_menu.php'; if (!in_array($u['role'], ['student', 'parent'], true)) go('portal/index.php');
$stu = acting_student($u); $info = student_info($stu);
$rows = q('SELECT r.*, s.title FROM results r JOIN exam_sessions s ON s.id=r.session_id WHERE r.student_id=? ORDER BY s.id DESC, r.subject', [$stu])->fetchAll();
$by = []; foreach ($rows as $r) $by[$r['title']][] = $r;
app_top('My results', $menu, $area); if (!$info) { echo '<div class="note err">No child is linked to your account yet. Please ask the school office.</div>'; app_bottom(); exit; } ?>
<h1><?= $u['role'] === 'parent' ? e($info['full_name']) . ': results' : 'My results' ?></h1><?= child_switcher($u, $stu) ?><p>Class: <strong><?= e($info['class_name']) ?></strong> &middot; CGPA: <strong><?= $rows ? number_format(gpa($stu), 2) : '-' ?></strong> / 5.0</p>
<?php foreach ($by as $t => $list): $avg = array_sum(array_column($list, 'score')) / count($list); ?>
<div class="panel"><h3><?= e($t) ?></h3><div class="tw scroll"><table><thead><tr><th>Subject</th><th>CA</th><th>Exam</th><th>Total</th><th>Grade</th><th>Remark</th></tr></thead><tbody>
<?php foreach ($list as $r): ?><tr><td><?= e($r['subject']) ?></td><td><?= $r['ca'] === null ? '-' : e($r['ca'] + 0) ?></td><td><?= $r['exam'] === null ? '-' : e($r['exam'] + 0) ?></td><td class="<?= $r['score'] < 40 ? 'low' : '' ?>"><strong><?= e($r['score'] + 0) ?></strong></td><td><strong><?= e($r['grade']) ?></strong></td><td><?= e($r['remark']) ?></td></tr><?php endforeach; ?>
<tr><th>Average</th><th></th><th></th><th><?= round($avg, 1) ?></th><th><?= grade_for($avg)[0] ?></th><th>GPA <?= number_format(array_sum(array_map(fn($x) => grade_point($x['grade']), $list)) / count($list), 2) ?></th></tr></tbody></table></div>
<p style="margin-top:12px"><button class="btn small ghost" onclick="window.print()">Print this result</button></p></div>
<?php endforeach; if (!$by) echo '<div class="panel"><p>No results have been published for you yet.</p></div>'; app_bottom();
