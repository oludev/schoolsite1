<?php
function gradebook(string $class, int $sid): array {
    $rows = q("SELECT u.id, u.full_name, r.subject, r.score, r.grade FROM users u JOIN results r ON r.student_id=u.id WHERE u.role='student' AND r.class_name=? AND r.session_id=? ORDER BY u.full_name", [$class, $sid])->fetchAll();
    $subjects = []; $stu = [];
    foreach ($rows as $r) { $subjects[$r['subject']] = 1; $stu[$r['id']]['name'] = $r['full_name']; $stu[$r['id']]['s'][$r['subject']] = $r['score']; $stu[$r['id']]['g'][] = $r['grade']; }
    ksort($subjects); $pos = class_positions($class, $sid);
    foreach ($stu as $id => &$s) { $s['avg'] = round(array_sum($s['s']) / count($s['s']), 1); $s['gpa'] = round(array_sum(array_map('grade_point', $s['g'])) / count($s['g']), 2); $s['pos'] = $pos[$id]['pos'] ?? 0; }
    unset($s); uasort($stu, fn($a, $b) => $a['pos'] <=> $b['pos']);
    return [array_keys($subjects), $stu];
}
function gradebook_html(string $class, int $sid, string $trackUrl = ''): string {
    [$subs, $stu] = gradebook($class, $sid);
    if (!$stu) return '<div class="note err">No scores have been entered for this class and exam yet.</div>';
    $h = '<div class="tw scroll"><table><thead><tr><th>Pos</th><th>Student</th>';
    foreach ($subs as $s) $h .= '<th>' . e($s) . '</th>'; $h .= '<th>Average</th><th>GPA</th></tr></thead><tbody>';
    foreach ($stu as $id => $s) {
        $h .= '<tr><td>' . e(ordinal($s['pos'])) . '</td><td>' . ($trackUrl ? '<a href="' . e($trackUrl) . $id . '">' . e($s['name']) . '</a>' : e($s['name'])) . '</td>';
        foreach ($subs as $sub) { $v = $s['s'][$sub] ?? null; $h .= '<td class="' . ($v !== null && $v < 40 ? 'low' : '') . '">' . ($v === null ? '-' : e($v + 0)) . '</td>'; }
        $h .= '<td><strong>' . e($s['avg']) . '</strong></td><td>' . number_format($s['gpa'], 2) . '</td></tr>';
    }
    $h .= '<tr><th colspan="2">Class average</th>';
    foreach ($subs as $sub) { $v = array_filter(array_column(array_column($stu, 's'), $sub), fn($x) => $x !== null); $h .= '<th>' . ($v ? round(array_sum($v) / count($v), 1) : '-') . '</th>'; }
    return $h . '<th>' . round(array_sum(array_column($stu, 'avg')) / count($stu), 1) . '</th><th>' . number_format(array_sum(array_column($stu, 'gpa')) / count($stu), 2) . '</th></tr></tbody></table></div><p class="mini">Scores under 40 are shown in red. GPA is on a 5.0 scale (A=5, B=4, C=3, D=2, E=1, F=0).</p>';
}
function picker(bool $withClass = true, bool $withSession = true, bool $fixedClass = false): array {
    $sid = (int)($_GET['session'] ?? 0); $cl = $_GET['class'] ?? '';
    $h = '<form method="get" class="panel f noprint" style="max-width:none"><div class="row">';
    if ($withSession) $h .= '<div><label>Exam</label><select name="session" required><option value="">Choose</option>' . options(array_column(session_list(), 'title', 'id'), $sid) . '</select></div>';
    if ($withClass) $h .= '<div><label>Class</label><select name="class" required><option value="">Choose</option>' . options(array_combine(class_list(), class_list()), $cl) . '</select></div>';
    $h .= '</div><div><button class="btn">Show</button></div></form>';
    return [$sid, $cl, $h];
}
function report_card(int $stu, int $sid): string {
    $u = q("SELECT u.*, p.admission_no, p.dob, p.gender FROM users u LEFT JOIN student_profiles p ON p.user_id=u.id WHERE u.id=? AND u.role='student'", [$stu])->fetch();
    $s = q('SELECT * FROM exam_sessions WHERE id=?', [$sid])->fetch();
    if (!$u || !$s) return '<div class="note err">Report card not found.</div>';
    $rows = q('SELECT r.*, (SELECT ROUND(AVG(r2.score),1) FROM results r2 WHERE r2.session_id=r.session_id AND r2.subject=r.subject AND r2.class_name<=>r.class_name) cavg FROM results r WHERE r.student_id=? AND r.session_id=? ORDER BY r.subject', [$stu, $sid])->fetchAll();
    if (!$rows) return '<div class="note err">No results have been entered for this exam yet.</div>';
    $u['class_name'] = $rows[0]['class_name'] ?: $u['class_name'];
    $c = q('SELECT * FROM report_comments WHERE student_id=? AND session_id=?', [$stu, $sid])->fetch() ?: [];
    $pos = class_positions((string)$u['class_name'], $sid)[$stu] ?? null;
    $total = array_sum(array_column($rows, 'score')); $avg = round($total / count($rows), 1);
    $h = '<div class="rc"><h2>' . e(setting('school_name', 'Wineridge Academy')) . '</h2><p class="sub">' . e(setting('address', '')) . '<br><strong>Student report card &middot; ' . e($s['title']) . '</strong></p>
<div class="info"><div>Name: <strong>' . e($u['full_name']) . '</strong></div><div>Class: <strong>' . e($u['class_name']) . '</strong></div><div>Admission no: ' . e($u['admission_no'] ?: '-') . '</div><div>Gender: ' . e($u['gender'] ?: '-') . '</div></div>
<div class="tw scroll"><table><thead><tr><th>Subject</th><th>CA</th><th>Exam</th><th>Total</th><th>Class avg</th><th>Grade</th><th>Points</th><th>Remark</th></tr></thead><tbody>';
    foreach ($rows as $r) $h .= '<tr><td>' . e($r['subject']) . '</td><td>' . ($r['ca'] === null ? '-' : e($r['ca'] + 0)) . '</td><td>' . ($r['exam'] === null ? '-' : e($r['exam'] + 0)) . '</td><td class="' . ($r['score'] < 40 ? 'low' : '') . '">' . e($r['score'] + 0) . '</td><td>' . e($r['cavg']) . '</td><td><strong>' . e($r['grade']) . '</strong></td><td>' . grade_point($r['grade']) . '</td><td>' . e($r['remark']) . '</td></tr>';
    $h .= '</tbody></table></div><div class="sum"><div><b>' . e($total + 0) . '</b>Total score</div><div><b>' . $avg . '%</b>Average</div><div><b>' . number_format((float)gpa($stu, $sid), 2) . '</b>GPA this exam</div><div><b>' . number_format((float)gpa($stu, null, $sid), 2) . '</b>CGPA to date</div><div><b>' . ($pos ? e(ordinal($pos['pos'])) . ' of ' . $pos['of'] : '-') . '</b>Position in class</div></div>
' . term_section($stu, $sid) . '<p><strong>Class teacher\'s comment:</strong> ' . e($c['teacher_comment'] ?? '') . '</p><p><strong>Principal\'s comment:</strong> ' . e($c['principal_comment'] ?? '') . '</p>
<div class="sign"><div>Class teacher</div><div>Principal</div></div><p class="mini">Grading: A 70-100, B 60-69, C 50-59, D 45-49, E 40-44, F below 40. GPA scale 5.0.</p></div>
<p class="noprint" style="margin-top:14px"><button class="btn" onclick="window.print()">Print report card</button></p>';
    return $h;
}
function rc_list(string $mode, string $self, string $view): void {
    $sid = (int)($_REQUEST['session'] ?? 0); $cl = $_REQUEST['class'] ?? ''; $col = $mode === 'teacher' ? 'teacher_comment' : 'principal_comment';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        foreach (($_POST['c'] ?? []) as $id => $txt) q("INSERT INTO report_comments (student_id,session_id,$col) VALUES (?,?,?) ON DUPLICATE KEY UPDATE $col=VALUES($col)", [(int)$id, $sid, mb_substr(trim($txt), 0, 300)]);
        audit('report_comments', "$mode $cl session $sid"); flash('Comments saved.'); go($self . '?session=' . $sid . '&class=' . urlencode($cl));
    }
    [, , $pick] = picker(); echo $pick;
    if (!$sid || !$cl) return;
    $rows = q("SELECT u.id,u.full_name,c.teacher_comment,c.principal_comment, (SELECT COUNT(*) FROM results r WHERE r.student_id=u.id AND r.session_id=?) n FROM users u LEFT JOIN report_comments c ON c.student_id=u.id AND c.session_id=? WHERE u.role='student' AND u.active=1 AND u.class_name=? ORDER BY u.full_name", [$sid, $sid, $cl])->fetchAll();
    echo '<form method="post" class="panel">' . csrf_field() . '<input type="hidden" name="session" value="' . $sid . '"><input type="hidden" name="class" value="' . e($cl) . '"><h3>Report cards for ' . e($cl) . '</h3><p class="mini">Write a short comment for each student, then save. Report cards build themselves from the scores.</p><div class="tw scroll"><table><thead><tr><th>Student</th><th>Your comment (' . ($mode === 'teacher' ? "class teacher" : 'principal') . ')</th><th></th></tr></thead><tbody>';
    foreach ($rows as $r) echo '<tr><td>' . e($r['full_name']) . '</td><td><input name="c[' . $r['id'] . ']" maxlength="300" value="' . e($r[$col]) . '"></td><td>' . ($r['n'] ? '<a href="' . $view . '?student=' . $r['id'] . '&session=' . $sid . '">View / print</a>' : '<span class="mini">No scores</span>') . '</td></tr>';
    if (!$rows) echo '<tr><td colspan="3">No students in this class.</td></tr>';
    echo '</tbody></table></div><p style="margin-top:14px"><button class="btn">Save comments</button></p></form>';
}

function term_section(int $stu, int $sid): string {
    $t = q('SELECT * FROM term_records WHERE student_id=? AND session_id=?', [$stu, $sid])->fetch(); if (!$t) return '';
    $att = $t['days_opened'] ? e($t['days_present']) . ' of ' . e($t['days_opened']) . ' days (' . round(100 * $t['days_present'] / $t['days_opened']) . '%)' : '-';
    $phys = trim(($t['height_cm'] ? 'Height ' . e($t['height_cm'] + 0) . ' cm. ' : '') . ($t['weight_kg'] ? 'Weight ' . e($t['weight_kg'] + 0) . ' kg. ' : '') . ($t['physical_rating'] ? 'Rating: ' . e($t['physical_rating']) : '')) ?: '-';
    return '<div class="tw scroll" style="margin-top:14px"><table><tbody><tr><th style="width:200px">Attendance</th><td>' . $att . '</td></tr><tr><th>Physical development</th><td>' . $phys . '</td></tr><tr><th>Sports</th><td>' . (e($t['sports']) ?: '-') . '</td></tr><tr><th>Clubs and societies</th><td>' . (e($t['clubs']) ?: '-') . '</td></tr></tbody></table></div>';
}
