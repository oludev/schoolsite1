<?php require '_menu.php';
$stu = $u['role'] === 'teacher' ? (int)($_GET['student'] ?? 0) : $u['id']; $sid = (int)($_GET['session'] ?? 0);
if (!$sid) { $sid = (int)q('SELECT MAX(session_id) FROM results WHERE student_id=?', [$stu])->fetchColumn(); }
$sess = q('SELECT DISTINCT s.id,s.title FROM exam_sessions s JOIN results r ON r.session_id=s.id WHERE r.student_id=? ORDER BY s.id DESC', [$stu])->fetchAll();
app_top('Report card', $menu, $area); ?>
<?php if ($sess): ?><form method="get" class="noprint" style="margin-bottom:16px"><input type="hidden" name="student" value="<?= $stu ?>"><select name="session" onchange="this.form.submit()" style="max-width:320px"><?= options(array_column($sess, 'title', 'id'), $sid) ?></select></form><?php endif;
echo $sid ? report_card($stu, $sid) : '<div class="panel">No report card is available yet.</div>'; if ($sid) audit('report_card_view', "student $stu session $sid"); app_bottom();
