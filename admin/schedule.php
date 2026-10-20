<?php require '_menu.php'; require_perm('schedule');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    if (isset($_POST['delete'])) { q('DELETE FROM exam_schedule WHERE id=?', [(int)$_POST['delete']]); audit('schedule_deleted', $_POST['delete']); flash('Paper removed.'); }
    else { $f = array_map('trim', $_POST); $inv = (int)($f['inv'] ?? 0) ?: null;
        if (!$f['session'] || !$f['class'] || !$f['subject'] || !$f['date'] || $f['end'] <= $f['start']) flash('Fill in every field. The end time must be after the start time.', 'err');
        else {
            $clash = q('SELECT e.*, s.title FROM exam_schedule e JOIN exam_sessions s ON s.id=e.session_id WHERE e.exam_date=? AND e.start_time<? AND e.end_time>? AND (e.class_name=? OR (e.venue=? AND e.venue<>"") OR (e.invigilator_id=? AND ?>0))', [$f['date'], $f['end'], $f['start'], $f['class'], $f['venue'], $inv ?: 0, $inv ?: 0])->fetch();
            if ($clash) flash('Clash with ' . $clash['class_name'] . ' ' . $clash['subject'] . ' (' . date('g:i', strtotime($clash['start_time'])) . ' to ' . date('g:i a', strtotime($clash['end_time'])) . ', ' . $clash['venue'] . '). A class, hall or invigilator is already booked then.', 'err');
            else { q('INSERT INTO exam_schedule (session_id,class_name,subject,exam_date,start_time,end_time,venue,invigilator_id) VALUES (?,?,?,?,?,?,?,?)', [(int)$f['session'], $f['class'], $f['subject'], $f['date'], $f['start'], $f['end'], $f['venue'], $inv]); audit('schedule_added', $f['class'] . ' ' . $f['subject']); flash('Paper scheduled.'); } } }
    go('admin/schedule.php?session=' . (int)($_POST['session'] ?? 0)); }
$sid = (int)($_GET['session'] ?? 0); $sess = session_list(); if (!$sid && $sess) $sid = (int)$sess[0]['id'];
$teachers = q("SELECT id,full_name FROM users WHERE role='teacher' AND active=1 ORDER BY full_name")->fetchAll();
$rows = q('SELECT e.*, i.full_name inv FROM exam_schedule e LEFT JOIN users i ON i.id=e.invigilator_id WHERE e.session_id=? ORDER BY e.exam_date, e.start_time, e.class_name', [$sid])->fetchAll();
app_top('Exam timetable', $menu, $area); ?><h1>Exam timetable</h1>
<?php if (!$sess) echo '<div class="note err">Create an exam session first (Exam sessions in the menu).</div>'; else: ?>
<form method="post" class="panel f noprint" style="max-width:none"><?= csrf_field() ?><h3>Schedule a paper</h3><p class="mini">The system blocks a paper if the class, the hall or the invigilator is already booked at an overlapping time.</p>
<div class="row"><div><label>Exam</label><select name="session"><?= options(array_column($sess, 'title', 'id'), $sid) ?></select></div><div><label>Class</label><input name="class" list="cl" required placeholder="e.g. JSS 1"><datalist id="cl"><?php foreach (class_list() as $c) echo '<option value="' . e($c) . '">'; ?></datalist></div></div>
<div class="row"><div><label>Subject</label><input name="subject" required></div><div><label>Date</label><input type="date" name="date" required></div></div>
<div class="row"><div><label>Starts</label><input type="time" name="start" required></div><div><label>Ends</label><input type="time" name="end" required></div></div>
<div class="row"><div><label>Venue</label><input name="venue" placeholder="Main hall"></div><div><label>Invigilator</label><select name="inv"><option value="">None yet</option><?= options(array_column($teachers, 'full_name', 'id')) ?></select></div></div><div><button class="btn">Add to timetable</button></div></form>
<form method="get" class="noprint" style="margin:16px 0"><select name="session" onchange="this.form.submit()" style="max-width:320px"><?= options(array_column($sess, 'title', 'id'), $sid) ?></select></form>
<div class="tw scroll"><table><thead><tr><th>Date</th><th>Time</th><th>Class</th><th>Subject</th><th>Venue</th><th>Invigilator</th><th class="noprint"></th></tr></thead><tbody>
<?php foreach ($rows as $x): ?><tr><td><?= date('D j M Y', strtotime($x['exam_date'])) ?></td><td><?= date('g:i', strtotime($x['start_time'])) ?> to <?= date('g:i a', strtotime($x['end_time'])) ?></td><td><?= e($x['class_name']) ?></td><td><?= e($x['subject']) ?></td><td><?= e($x['venue']) ?></td><td><?= e($x['inv']) ?></td>
<td class="noprint"><form method="post" onsubmit="return confirm('Remove this paper?')"><?= csrf_field() ?><button class="btn small danger" name="delete" value="<?= $x['id'] ?>">Remove</button></form></td></tr><?php endforeach; if (!$rows) echo '<tr><td colspan="7">No papers scheduled for this exam.</td></tr>'; ?></tbody></table></div>
<p class="noprint"><button class="btn small ghost" onclick="window.print()">Print timetable</button></p><?php endif; app_bottom();
