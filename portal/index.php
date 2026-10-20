<?php require '_menu.php'; app_top('Dashboard', $menu, $area);
$notices = visible_notices($u, 3);
$events = q("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date LIMIT 4")->fetchAll(); ?>
<h1>Welcome, <?= e(explode(' ', $u['full_name'])[0]) ?></h1>
<?php if ($u['role'] === 'student'):
  $n = q('SELECT COUNT(*) FROM results WHERE student_id=?', [$u['id']])->fetchColumn();
  $next = q("SELECT e.*, s.title st FROM exam_schedule e JOIN exam_sessions s ON s.id=e.session_id WHERE e.class_name=? AND e.exam_date>=CURDATE() ORDER BY e.exam_date, e.start_time LIMIT 3", [$u['class_name']])->fetchAll(); ?>
  <div class="stats"><div class="stat"><b><?= e($u['class_name'] ?: '-') ?></b>My class</div><div class="stat"><b><?= (int)$n ?></b>Results recorded</div>
   <div class="stat"><b><?= $n ? number_format(gpa($u['id']), 2) : '-' ?></b>CGPA (out of 5.0)</div></div>
  <p><a class="btn" href="reportcard.php">Report card</a> <a class="btn ghost" href="tracking.php">Grade tracking</a></p>
  <div class="panel"><h3>Next exams</h3><?php foreach ($next as $x) echo '<p style="margin:0 0 6px"><strong>' . date('D j M', strtotime($x['exam_date'])) . '</strong>, ' . date('g:i a', strtotime($x['start_time'])) . ' &middot; ' . e($x['subject']) . ' &middot; ' . e($x['venue']) . '</p>'; if (!$next) echo '<p>No exams scheduled yet.</p>'; ?></div>
<?php else:
  $c = q("SELECT COUNT(*) FROM users WHERE role='student' AND active=1")->fetchColumn(); $r = q('SELECT COUNT(*) FROM results WHERE teacher_id=?', [$u['id']])->fetchColumn();
  $duty = q('SELECT e.*, s.title st FROM exam_schedule e JOIN exam_sessions s ON s.id=e.session_id WHERE e.invigilator_id=? AND e.exam_date>=CURDATE() ORDER BY e.exam_date, e.start_time LIMIT 4', [$u['id']])->fetchAll();
  $lv = q("SELECT COUNT(*) FROM leave_requests WHERE user_id=? AND status='Pending'", [$u['id']])->fetchColumn(); ?>
  <div class="stats"><div class="stat"><b><?= (int)$c ?></b>Active students</div><div class="stat"><b><?= (int)$r ?></b>Scores you entered</div><div class="stat"><b><?= (int)$lv ?></b>Leave requests pending</div></div>
  <p><a class="btn" href="scores.php">Enter scores</a> <a class="btn ghost" href="gradebook.php">Open grade book</a></p>
  <div class="panel"><h3>My invigilation duties</h3><?php foreach ($duty as $x) echo '<p style="margin:0 0 6px"><strong>' . date('D j M', strtotime($x['exam_date'])) . '</strong>, ' . date('g:i a', strtotime($x['start_time'])) . ' &middot; ' . e($x['class_name']) . ' ' . e($x['subject']) . ' &middot; ' . e($x['venue']) . '</p>'; if (!$duty) echo '<p>No upcoming duties.</p>'; ?></div>
<?php endif; ?>
<div class="panel"><h3>Notice board</h3><?= notice_cards($notices) ?><p><a href="notices.php">See all notices</a></p></div>
<div class="panel"><h3>School events</h3><?php foreach ($events as $ev) echo '<p style="margin:0 0 6px"><strong>' . date('j M', strtotime($ev['event_date'])) . '</strong> &middot; ' . e($ev['title']) . '</p>'; if (!$events) echo '<p>No events.</p>'; ?></div>
<?php app_bottom();
