<?php require '_menu.php'; if ($u['role'] !== 'teacher') go('portal/index.php');
$sid = (int)($_REQUEST['session'] ?? 0); $cl = $_REQUEST['class'] ?? ''; $rate = ['' => 'Not rated', 'Excellent' => 'Excellent', 'Very Good' => 'Very Good', 'Good' => 'Good', 'Fair' => 'Fair', 'Needs improvement' => 'Needs improvement'];
$rows = ($sid && $cl) ? q("SELECT u.id,u.full_name,t.* FROM users u LEFT JOIN term_records t ON t.student_id=u.id AND t.session_id=? WHERE u.role='student' AND u.active=1 AND u.class_name=? ORDER BY u.full_name", [$sid, $cl])->fetchAll() : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $rows) { csrf_check(); $open = trim($_POST['days_opened'] ?? ''); $ids = array_map('intval', array_column($rows, 'id')); $n = 0; $bad = [];
    foreach ($_POST['p'] ?? [] as $id => $pres) { $id = (int)$id; if (!in_array($id, $ids, true)) continue;
        $pres = trim($pres); $h = trim($_POST['h'][$id] ?? ''); $w = trim($_POST['w'][$id] ?? ''); $r = $_POST['r'][$id] ?? ''; $sp = trim($_POST['s'][$id] ?? ''); $cb = trim($_POST['c'][$id] ?? '');
        if ($pres === '' && $h === '' && $w === '' && $r === '' && $sp === '' && $cb === '') continue;
        if ($pres !== '' && ($open === '' || !ctype_digit($pres) || (int)$pres > (int)$open)) { $bad[] = q('SELECT full_name FROM users WHERE id=?', [$id])->fetchColumn(); continue; }
        q('INSERT INTO term_records (student_id,session_id,days_opened,days_present,height_cm,weight_kg,physical_rating,sports,clubs) VALUES (?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE days_opened=VALUES(days_opened),days_present=VALUES(days_present),height_cm=VALUES(height_cm),weight_kg=VALUES(weight_kg),physical_rating=VALUES(physical_rating),sports=VALUES(sports),clubs=VALUES(clubs)',
          [$id, $sid, $open === '' ? null : (int)$open, $pres === '' ? null : (int)$pres, is_numeric($h) ? $h : null, is_numeric($w) ? $w : null, isset($rate[$r]) ? $r : '', mb_substr($sp, 0, 300), mb_substr($cb, 0, 300)]); $n++; }
    audit('term_records_saved', "$n students, $cl, session $sid"); flash("Saved $n student(s)." . ($bad ? ' Check days present (it must be a whole number, not more than school days): ' . implode(', ', $bad) . '.' : ''), $bad ? 'err' : 'ok');
    go("portal/term_records.php?session=$sid&class=" . urlencode($cl)); }
[, , $pick] = picker(); app_top('Attendance & activities', $menu, $area); ?>
<h1>Attendance, physical development, sports and clubs</h1><p class="mini">These appear on each student's report card.</p><?= $pick ?>
<?php if ($rows): $open = ''; foreach ($rows as $r) if ($r['days_opened']) { $open = $r['days_opened']; break; } ?>
<form method="post" class="panel"><?= csrf_field() ?><input type="hidden" name="session" value="<?= $sid ?>"><input type="hidden" name="class" value="<?= e($cl) ?>">
<div class="f" style="max-width:260px;margin-bottom:14px"><div><label>Days the school opened this term</label><input type="number" min="0" name="days_opened" value="<?= e($open) ?>"></div></div>
<div class="tw scroll"><table><thead><tr><th>Student</th><th>Days present</th><th>Height (cm)</th><th>Weight (kg)</th><th>Physical development</th><th>Sports</th><th>Clubs and societies</th></tr></thead><tbody>
<?php foreach ($rows as $r): $i = $r['id']; ?><tr><td><?= e($r['full_name']) ?></td><td><input type="number" min="0" name="p[<?= $i ?>]" value="<?= e($r['days_present']) ?>"></td><td><input type="number" step="0.1" min="0" name="h[<?= $i ?>]" value="<?= e($r['height_cm']) ?>"></td><td><input type="number" step="0.1" min="0" name="w[<?= $i ?>]" value="<?= e($r['weight_kg']) ?>"></td>
<td><select name="r[<?= $i ?>]" style="min-width:150px"><?= options($rate, $r['physical_rating']) ?></select></td><td><input name="s[<?= $i ?>]" maxlength="300" value="<?= e($r['sports']) ?>" style="min-width:180px" placeholder="e.g. Football, 100m"></td><td><input name="c[<?= $i ?>]" maxlength="300" value="<?= e($r['clubs']) ?>" style="min-width:180px" placeholder="e.g. Debate club"></td></tr><?php endforeach; ?></tbody></table></div>
<p style="margin-top:14px"><button class="btn">Save records</button></p></form><?php elseif ($sid && $cl): echo '<div class="note err">No students in this class.</div>'; endif; app_bottom();
