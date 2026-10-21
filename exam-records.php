<?php $title = 'Exam records'; require 'includes/header.php';
$rows = q("SELECT s.*, COUNT(DISTINCT r.student_id) cands, ROUND(AVG(r.score),1) avg_score, ROUND(100*SUM(r.score>=40)/NULLIF(COUNT(r.id),0),0) pass_rate
  FROM exam_sessions s LEFT JOIN results r ON r.session_id=s.id GROUP BY s.id ORDER BY s.start_date DESC")->fetchAll(); ?>
<div class="pagehead"><div class="wrap"><h1>Exam records</h1><p>Examination periods and school-wide performance. Individual results are private and available in the e-portal.</p></div></div>
<section class="s"><div class="wrap"><div class="tw"><table><thead><tr><th>Examination</th><th>Section</th><th>Dates</th><th>Candidates</th><th>Average score</th><th>Pass rate</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><strong><?= e($r['title']) ?></strong><br><span style="color:var(--mute)"><?= e($r['notes']) ?></span></td><td><?= e($r['section']) ?></td>
<td><?= $r['start_date'] ? date('j M', strtotime($r['start_date'])) . ' to ' . date('j M Y', strtotime($r['end_date'] ?: $r['start_date'])) : '' ?></td>
<td><?= (int)$r['cands'] ?: '-' ?></td><td><?= $r['avg_score'] !== null ? e($r['avg_score']) . '%' : '-' ?></td><td><?= $r['pass_rate'] !== null ? e($r['pass_rate']) . '%' : '-' ?></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="6">No exam records published yet.</td></tr>'; ?></tbody></table></div>
<p style="margin-top:20px"><a class="btn" href="<?= url('login.php') ?>">Check my results in the e-portal</a></p></div></section>
<?php require 'includes/footer.php'; ?>
