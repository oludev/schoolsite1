<?php require '_menu.php'; if ($u['role'] !== 'teacher') go('portal/index.php');
$sid = (int)($_REQUEST['session'] ?? 0); $cl = $_REQUEST['class'] ?? ''; $base = $_REQUEST['subject'] ?? ''; $lang = $_REQUEST['lang'] ?? '';
$sub = ($base && ($base !== 'Nigerian Language' || in_array($lang, NIGERIAN_LANGS, true))) ? subject_label($base, $lang) : '';
$caM = ca_max(); $exM = exam_max();
$students = ($sid && $cl && $sub) ? q("SELECT u.id,u.full_name,r.ca,r.exam FROM users u LEFT JOIN results r ON r.student_id=u.id AND r.session_id=? AND r.subject=? WHERE u.role='student' AND u.active=1 AND u.class_name=? ORDER BY u.full_name", [$sid, $sub, $cl])->fetchAll() : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ca']) && $students) {
    csrf_check(); $ids = array_column($students, 'id'); $n = 0; $bad = [];
    foreach ($_POST['ca'] as $id => $ca) {
        $ex = trim($_POST['exam'][$id] ?? ''); $ca = trim($ca); if (!in_array((int)$id, array_map('intval', $ids), true) || ($ca === '' && $ex === '')) continue;
        if (!is_numeric($ca) || !is_numeric($ex) || $ca < 0 || $ca > $caM || $ex < 0 || $ex > $exM) { $bad[] = q('SELECT full_name FROM users WHERE id=?', [(int)$id])->fetchColumn(); continue; }
        save_score((int)$id, $sid, $sub, (float)$ca, (float)$ex, $u['id']); $n++;
    }
    audit('scores_saved', "$n scores, $sub, $cl, session $sid");
    flash("Saved $n score(s) for $sub." . ($bad ? ' Not saved (enter both CA and Exam within the limits): ' . implode(', ', $bad) . '.' : ''), $bad ? 'err' : 'ok');
    go("portal/scores_subject.php?session=$sid&class=" . urlencode($cl) . '&subject=' . urlencode($base) . '&lang=' . urlencode($lang));
}
app_top('Enter scores by subject', $menu, $area); ?>
<h1>Enter scores by subject</h1><p class="mini">Pick the exam, class and subject, then enter CA (out of <?= $caM + 0 ?>) and exam (out of <?= $exM + 0 ?>) for every student. Use this when you teach one subject to a whole class.</p>
<form method="get" class="panel f" style="max-width:none"><div class="row"><div><label>Exam</label><select name="session" required><option value="">Choose</option><?= options(array_column(session_list(), 'title', 'id'), $sid) ?></select></div>
<div><label>Class</label><select name="class" required><option value="">Choose</option><?= options(array_combine(class_list(), class_list()), $cl) ?></select></div></div>
<div class="row"><div><label>Subject</label><?= subject_select('subject', $base) ?></div><div><label>Language (Nigerian Language only)</label><?= lang_select('lang', $lang, $base === 'Nigerian Language') ?></div></div>
<div><button class="btn">Load students</button></div></form>
<script>document.querySelector('select.subj').addEventListener('change', function () { document.querySelector('select.lang').style.display = this.value === 'Nigerian Language' ? '' : 'none'; });</script>
<?php if ($students): ?><form method="post" class="panel"><?= csrf_field() ?><input type="hidden" name="session" value="<?= $sid ?>"><input type="hidden" name="class" value="<?= e($cl) ?>"><input type="hidden" name="subject" value="<?= e($base) ?>"><input type="hidden" name="lang" value="<?= e($lang) ?>">
<h3><?= e($sub) ?> &middot; <?= e($cl) ?></h3><div class="tw scroll"><table><thead><tr><th>Student</th><th>CA (<?= $caM + 0 ?>)</th><th>Exam (<?= $exM + 0 ?>)</th><th>Total</th></tr></thead><tbody>
<?php foreach ($students as $s): ?><tr class="srow"><td><?= e($s['full_name']) ?></td><td><input type="number" class="ca" step="0.5" min="0" max="<?= $caM ?>" name="ca[<?= $s['id'] ?>]" value="<?= e($s['ca']) ?>"></td><td><input type="number" class="ex" step="0.5" min="0" max="<?= $exM ?>" name="exam[<?= $s['id'] ?>]" value="<?= e($s['exam']) ?>"></td>
<td><output class="sum"><?= ($s['ca'] !== null && $s['exam'] !== null) ? e($s['ca'] + $s['exam']) : '' ?></output></td></tr><?php endforeach; ?></tbody></table></div>
<p style="margin-top:14px"><button class="btn">Save scores</button></p></form>
<script>document.querySelectorAll('.srow').forEach(function (tr) { tr.addEventListener('input', function () { var c = tr.querySelector('.ca').value, x = tr.querySelector('.ex').value, o = tr.querySelector('.sum'); o.textContent = (c === '' && x === '') ? '' : (parseFloat(c || 0) + parseFloat(x || 0)); o.classList.toggle('over', parseFloat(c || 0) > <?= $caM ?> || parseFloat(x || 0) > <?= $exM ?>); }); });</script>
<?php elseif ($sid && $cl && $sub): echo '<div class="note err">No students found in this class.</div>'; endif; app_bottom();
