<?php require '_menu.php';
$sessions = q('SELECT DISTINCT s.id,s.title FROM exam_sessions s JOIN exam_schedule e ON e.session_id=s.id ORDER BY s.id DESC')->fetchAll();
$sid = (int)($_GET['session'] ?? ($sessions[0]['id'] ?? 0)); $cl = $u['role'] === 'student' ? $u['class_name'] : ($u['role'] === 'parent' ? (student_info(acting_student($u))['class_name'] ?? '') : ($_GET['class'] ?? ''));
$rows = ($sid && $cl) ? q('SELECT e.*, i.full_name inv FROM exam_schedule e LEFT JOIN users i ON i.id=e.invigilator_id WHERE e.session_id=? AND e.class_name=? ORDER BY e.exam_date, e.start_time', [$sid, $cl])->fetchAll() : [];
$duty = $u['role'] === 'teacher' ? q('SELECT e.* FROM exam_schedule e WHERE e.invigilator_id=? AND e.exam_date>=CURDATE() ORDER BY e.exam_date, e.start_time', [$u['id']])->fetchAll() : [];
app_top('Exam timetable', $menu, $area); ?>
<h1>Exam timetable</h1><?= child_switcher($u, acting_student($u)) ?>
<form method="get" class="panel f noprint" style="max-width:none"><div class="row"><div><label>Exam</label><select name="session"><?= options(array_column($sessions, 'title', 'id'), $sid) ?></select></div>
<?php if ($u['role'] === 'teacher'): ?><div><label>Class</label><select name="class"><option value="">Choose</option><?= options(array_combine(class_list(), class_list()), $cl) ?></select></div><?php endif; ?></div><div><button class="btn">Show timetable</button></div></form>
<?php if ($duty): ?><div class="panel"><h3>My invigilation duties</h3><?php foreach ($duty as $x) echo '<p style="margin:0 0 6px"><strong>' . date('D j M', strtotime($x['exam_date'])) . '</strong>, ' . date('g:i', strtotime($x['start_time'])) . ' to ' . date('g:i a', strtotime($x['end_time'])) . ' &middot; ' . e($x['class_name']) . ' ' . e($x['subject']) . ' &middot; ' . e($x['venue']) . '</p>'; ?></div><?php endif; ?>
<?php if ($cl): ?><div class="panel"><h3><?= e($cl) ?></h3><div class="tw scroll"><table><thead><tr><th>Date</th><th>Time</th><th>Subject</th><th>Venue</th><th>Invigilator</th></tr></thead><tbody>
<?php foreach ($rows as $x) echo '<tr><td>' . date('D j M Y', strtotime($x['exam_date'])) . '</td><td>' . date('g:i', strtotime($x['start_time'])) . ' to ' . date('g:i a', strtotime($x['end_time'])) . '</td><td>' . e($x['subject']) . '</td><td>' . e($x['venue']) . '</td><td>' . e($x['inv']) . '</td></tr>';
if (!$rows) echo '<tr><td colspan="5">No papers scheduled for this class yet.</td></tr>'; ?></tbody></table></div><p class="noprint"><button class="btn small ghost" onclick="window.print()">Print timetable</button></p></div><?php endif; app_bottom();
