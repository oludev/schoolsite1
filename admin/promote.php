<?php require '_menu.php'; require_perm('users');
$next = ['Primary 1' => 'Primary 2', 'Primary 2' => 'Primary 3', 'Primary 3' => 'Primary 4', 'Primary 4' => 'Primary 5', 'Primary 5' => 'Primary 6', 'Primary 6' => 'JSS 1',
         'JSS 1' => 'JSS 2', 'JSS 2' => 'JSS 3', 'JSS 3' => 'SS 1', 'SS 1' => 'SS 2', 'SS 2' => 'SS 3', 'SS 3' => '__grad'];
$by = [];
foreach (q("SELECT id, full_name, class_name FROM users WHERE role='student' AND active=1 AND class_name<>'' ORDER BY full_name")->fetchAll() as $s) $by[$s['class_name']][] = $s;
uksort($by, function ($a, $b) { $x = array_search($a, CLASSES); $y = array_search($b, CLASSES); return [$x === false ? 99 : $x, $a] <=> [$y === false ? 99 : $y, $b]; });
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    $hold = array_map('intval', $_POST['hold'] ?? []); $map = $_POST['map'] ?? []; $valid = class_list(); $moved = 0; $grad = 0; $held = 0;
    db()->beginTransaction();
    try {
        foreach ($by as $c => $studs) {
            $t = $map[$c] ?? ''; if ($t === '' || $t === $c || ($t !== '__grad' && !in_array($t, $valid, true))) continue;
            $ids = array_values(array_filter(array_column($studs, 'id'), fn($i) => !in_array((int)$i, $hold, true))); $held += count($studs) - count($ids);
            if (!$ids) continue; $in = implode(',', array_fill(0, count($ids), '?'));
            if ($t === '__grad') { q("UPDATE users SET active=0, class_name=? WHERE id IN ($in)", ['Graduated ' . date('Y'), ...$ids]); $grad += count($ids); }
            else { q("UPDATE users SET class_name=? WHERE id IN ($in)", [$t, ...$ids]); $moved += count($ids); }
        }
        db()->commit();
    } catch (Throwable $e) { db()->rollBack(); flash('Promotion failed and nothing was changed: ' . $e->getMessage(), 'err'); go('admin/promote.php'); }
    audit('class_promotion', "$moved moved up, $grad graduated, $held held back"); flash("Done. $moved student(s) moved up, $grad graduated, $held held back."); go('admin/promote.php'); }
$targets = ['' => 'Stay in this class'] + array_combine(class_list(), class_list()) + ['__grad' => 'Graduate (leave school)'];
app_top('Promote classes', $menu, $area); ?><h1>Promote classes</h1>
<div class="panel"><p>Use this at the end of the school year, <strong>after</strong> the final results and report cards are done. Every class moves up in one step. Old results and report cards keep the class the student was in at the time.</p>
<p class="mini">Graduates are disabled so they can no longer log in, but their records and results are kept. To keep a repeater in the same class, tick <em>Hold back</em> under that class. To reverse a mistake, change the student's class under Student records.</p></div>
<?php if (!$by): echo '<div class="panel">There are no active students yet. <a href="import.php">Import students</a> first.</div>'; else: ?>
<form method="post" onsubmit="return confirm('Promote the classes as shown? This changes every student listed.')"><?= csrf_field() ?>
<div class="tw scroll"><table><thead><tr><th>Current class</th><th>Students</th><th>Move to</th><th>Hold back</th></tr></thead><tbody>
<?php foreach ($by as $c => $studs): ?><tr><td><strong><?= e($c) ?></strong></td><td><?= count($studs) ?></td><td><select name="map[<?= e($c) ?>]"><?= options($targets, $next[$c] ?? '') ?></select></td>
<td><details><summary>Choose students</summary><?php foreach ($studs as $s) echo '<label style="font-weight:400;display:flex;gap:8px;align-items:center;margin:4px 0"><input type="checkbox" name="hold[]" value="' . $s['id'] . '" style="width:auto"> ' . e($s['full_name']) . '</label>'; ?></details></td></tr>
<?php endforeach; ?></tbody></table></div>
<p style="margin-top:16px"><button class="btn">Promote classes</button></p></form><?php endif; app_bottom();
