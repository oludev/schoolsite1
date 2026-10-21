<?php require '_menu.php';
$stu = acting_student($u);
$s = q("SELECT id,full_name,class_name FROM users WHERE id=? AND role='student'", [$stu])->fetch(); if (!$s) go('portal/index.php');
$sessions = q('SELECT DISTINCT s.id, s.title FROM exam_sessions s JOIN results r ON r.session_id=s.id WHERE r.student_id=? ORDER BY s.id', [$stu])->fetchAll();
$rows = q('SELECT session_id, subject, score FROM results WHERE student_id=?', [$stu])->fetchAll(); $by = [];
foreach ($rows as $r) $by[$r['subject']][$r['session_id']] = $r['score']; ksort($by);
$labels = array_column($sessions, 'title'); $gp = []; $av = [];
foreach ($sessions as $x) { $gp[] = gpa($stu, $x['id']); $av[] = round(array_sum(array_column(array_filter($rows, fn($r) => $r['session_id'] == $x['id']), 'score')) / max(1, count(array_filter($rows, fn($r) => $r['session_id'] == $x['id']))), 1); }
app_top('Grade tracking', $menu, $area); ?>
<h1>Grade tracking: <?= e($s['full_name']) ?></h1><?= child_switcher($u, $stu) ?><p>Class: <?= e($s['class_name']) ?> &middot; CGPA: <strong><?= $rows ? number_format(gpa($stu), 2) : '-' ?></strong> out of 5.0</p>
<?php if (!$sessions): echo '<div class="panel">No results to track yet.</div>'; else: ?>
<div class="split"><div class="panel"><h3>GPA by exam</h3><?= trend_svg($labels, $gp, 5) ?></div><div class="panel"><h3>Average score by exam (%)</h3><?= trend_svg($labels, $av, 100, '%') ?></div></div>
<div class="panel"><h3>Subject progress</h3><div class="tw scroll"><table><thead><tr><th>Subject</th><?php foreach ($labels as $l) echo '<th>' . e($l) . '</th>'; ?><th>Trend</th></tr></thead><tbody>
<?php foreach ($by as $sub => $sc): echo '<tr><td>' . e($sub) . '</td>'; $last = null; $prev = null;
  foreach ($sessions as $x) { $v = $sc[$x['id']] ?? null; echo '<td class="' . ($v !== null && $v < 40 ? 'low' : '') . '">' . ($v === null ? '-' : e($v + 0)) . '</td>'; if ($v !== null) { $prev = $last; $last = $v; } }
  $d = ($last !== null && $prev !== null) ? $last - $prev : null;
  echo '<td>' . ($d === null ? '-' : ($d > 0 ? '<span class="good">Up ' . round($d, 1) . '</span>' : ($d < 0 ? '<span class="low">Down ' . round(-$d, 1) . '</span>' : 'Steady'))) . '</td></tr>'; endforeach; ?></tbody></table></div></div>
<?php endif; app_bottom();
