<?php require '_menu.php'; if ($u['role'] !== 'teacher') go('portal/index.php');
$sid = (int)($_REQUEST['session'] ?? 0); $cl = $_REQUEST['class'] ?? ''; $stu = (int)($_REQUEST['student'] ?? 0);
$students = $cl ? q("SELECT id,full_name FROM users WHERE role='student' AND active=1 AND class_name=? ORDER BY full_name", [$cl])->fetchAll() : [];
$sel = null; foreach ($students as $s) if ((int)$s['id'] === $stu) $sel = $s;
$caM = ca_max(); $exM = exam_max(); $prefill = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rows']) && $sid && $sel) {
    csrf_check(); $errs = []; $seen = []; $ok = []; $del = [];
    $existing = q('SELECT subject FROM results WHERE student_id=? AND session_id=?', [$stu, $sid])->fetchAll(PDO::FETCH_COLUMN);
    foreach ($_POST['rows'] as $r) {
        if (!empty($r['del']) && !empty($r['id'])) { $del[] = (int)$r['id']; continue; }
        $b = trim($r['subject'] ?? ''); $ca = trim($r['ca'] ?? ''); $ex = trim($r['exam'] ?? '');
        if ($b === '') { if ($ca !== '' || $ex !== '') $errs[] = 'A row has scores but no subject.'; continue; }
        if ($b === 'Nigerian Language' && !in_array($r['lang'] ?? '', NIGERIAN_LANGS, true)) { $errs[] = 'Choose Yoruba, Igbo or Hausa for Nigerian Language.'; continue; }
        $name = subject_label($b, trim($r['lang'] ?? ''));
        if (!in_array($b, SUBJECTS, true) && !in_array($name, $existing, true)) { $errs[] = "$name is not a valid subject."; continue; }
        if ($ca === '' && $ex === '') continue;
        if (!is_numeric($ca) || !is_numeric($ex) || $ca < 0 || $ca > $caM || $ex < 0 || $ex > $exM) { $errs[] = "$name: enter CA from 0 to $caM and Exam from 0 to $exM (use 0 if the student missed one)."; continue; }
        if (isset($seen[$name])) { $errs[] = "$name appears twice."; continue; } $seen[$name] = 1; $ok[] = [$name, (float)$ca, (float)$ex];
    }
    if ($errs) flash(implode(' ', array_unique($errs)), 'err');
    else {
        foreach ($ok as [$n, $ca, $ex]) save_score($stu, $sid, $n, $ca, $ex, $u['id']);
        if ($del) q('DELETE FROM results WHERE student_id=? AND session_id=? AND id IN (' . implode(',', array_map('intval', $del)) . ')', [$stu, $sid]);
        audit('scores_saved', count($ok) . ' saved, ' . count($del) . ' removed, student ' . $stu . ', session ' . $sid);
        flash('Saved ' . count($ok) . ' subject(s) for ' . $sel['full_name'] . (count($del) ? ' and removed ' . count($del) . '.' : '.'));
        go("portal/scores.php?session=$sid&class=" . urlencode($cl) . "&student=$stu");
    }
    $prefill = $_POST['rows'];
}
$d = [];
if ($prefill !== null) { foreach ($prefill as $r) $d[] = ['id' => (int)($r['id'] ?? 0), 'subject' => $r['subject'] ?? '', 'lang' => $r['lang'] ?? '', 'ca' => $r['ca'] ?? '', 'exam' => $r['exam'] ?? '', 'del' => !empty($r['del'])]; }
elseif ($sid && $sel) {
    foreach (q('SELECT * FROM results WHERE student_id=? AND session_id=? ORDER BY subject', [$stu, $sid])->fetchAll() as $r) { [$b, $l] = split_subject($r['subject']); $d[] = ['id' => (int)$r['id'], 'subject' => $b, 'lang' => $l, 'ca' => $r['ca'] ?? '', 'exam' => $r['exam'] ?? '', 'score' => $r['score']]; }
    for ($k = 0, $n = $d ? 2 : 4; $k < $n; $k++) $d[] = ['id' => 0, 'subject' => '', 'lang' => '', 'ca' => '', 'exam' => ''];
}
$rowHtml = function ($i, $x) use ($caM, $exM) {
    $tot = ($x['ca'] !== '' && $x['exam'] !== '') ? e((float)$x['ca'] + (float)$x['exam']) : (isset($x['score']) ? e($x['score'] + 0) : '');
    return '<tr class="srow"><td><input type="hidden" name="rows[' . $i . '][id]" value="' . (int)$x['id'] . '">' . subject_select("rows[$i][subject]", $x['subject']) . lang_select("rows[$i][lang]", $x['lang'], $x['subject'] === 'Nigerian Language') . '</td>'
      . '<td><input type="number" class="ca" step="0.5" min="0" max="' . $caM . '" name="rows[' . $i . '][ca]" value="' . e($x['ca']) . '"></td>'
      . '<td><input type="number" class="ex" step="0.5" min="0" max="' . $exM . '" name="rows[' . $i . '][exam]" value="' . e($x['exam']) . '"></td>'
      . '<td><output class="sum">' . $tot . '</output></td><td>'
      . ($x['id'] ? '<label style="font-weight:400"><input type="checkbox" name="rows[' . $i . '][del]" value="1" style="width:auto"' . (!empty($x['del']) ? ' checked' : '') . '> Remove</label>' : '<button type="button" class="btn small ghost rm">Remove row</button>') . '</td></tr>';
};
app_top('Enter scores', $menu, $area); ?>
<h1>Enter scores by student</h1><p class="mini">Add one row for each subject. CA is marked out of <?= $caM + 0 ?> and the exam out of <?= $exM + 0 ?>. The total is added for you and the grade is worked out automatically.</p>
<form method="get" class="panel f" style="max-width:none"><div class="row"><div><label>Exam</label><select name="session" required><option value="">Choose</option><?= options(array_column(session_list(), 'title', 'id'), $sid) ?></select></div>
<div><label>Class</label><select name="class" required onchange="this.form.student.value='';this.form.submit()"><option value="">Choose</option><?= options(array_combine(class_list(), class_list()), $cl) ?></select></div></div>
<div><label>Student</label><select name="student" required><option value="">Choose student</option><?= options(array_column($students, 'full_name', 'id'), $stu) ?></select></div><div><button class="btn">Open scores</button></div></form>
<?php if ($sid && $sel): ?>
<form method="post" class="panel"><?= csrf_field() ?><input type="hidden" name="session" value="<?= $sid ?>"><input type="hidden" name="class" value="<?= e($cl) ?>"><input type="hidden" name="student" value="<?= $stu ?>">
<h3><?= e($sel['full_name']) ?> &middot; <?= e($cl) ?></h3>
<div class="tw scroll"><table><thead><tr><th>Subject</th><th>CA (<?= $caM + 0 ?>)</th><th>Exam (<?= $exM + 0 ?>)</th><th>Total</th><th></th></tr></thead><tbody id="rows"><?php foreach ($d as $i => $x) echo $rowHtml($i, $x); ?></tbody></table></div>
<p style="margin-top:14px" class="acts"><button type="button" class="btn ghost" id="add">Add another subject</button><button class="btn">Save scores</button></p></form>
<script type="text/template" id="tpl"><?= $rowHtml('__i__', ['id' => 0, 'subject' => '', 'lang' => '', 'ca' => '', 'exam' => '']) ?></script>
<script>
(function () {
  var body = document.getElementById('rows'), n = <?= count($d) + 100 ?>, caM = <?= $caM ?>, exM = <?= $exM ?>;
  function sum(tr) { var c = tr.querySelector('.ca'), x = tr.querySelector('.ex'), o = tr.querySelector('.sum'); if (c.value === '' && x.value === '') return; o.textContent = (parseFloat(c.value || 0) + parseFloat(x.value || 0)); o.classList.toggle('over', parseFloat(c.value || 0) > caM || parseFloat(x.value || 0) > exM); }
  body.addEventListener('input', function (e) { var tr = e.target.closest('tr'); if (tr) sum(tr); });
  body.addEventListener('change', function (e) { if (e.target.classList.contains('subj')) e.target.closest('tr').querySelector('.lang').style.display = e.target.value === 'Nigerian Language' ? '' : 'none'; });
  body.addEventListener('click', function (e) { if (e.target.classList.contains('rm')) e.target.closest('tr').remove(); });
  document.getElementById('add').addEventListener('click', function () { var t = document.createElement('tbody'); t.innerHTML = document.getElementById('tpl').textContent.replace(/__i__/g, n++); body.appendChild(t.firstElementChild); });
})();
</script>
<?php endif; app_bottom();
