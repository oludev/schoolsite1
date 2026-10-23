<?php
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    }
    return $pdo;
}
function q(string $sql, array $a = []): PDOStatement { $s = db()->prepare($sql); $s->execute($a); return $s; }
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function url(string $p = ''): string { return BASE_URL . '/' . ltrim($p, '/'); }
function go(string $p): void { header('Location: ' . url($p)); exit; }
function setting(string $k, string $d = ''): string {
    static $c;
    if ($c === null) { try { $c = q('SELECT k,v FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR); } catch (Exception $x) { $c = []; } }
    return $c[$k] ?? $d;
}
function save_setting(string $k, string $v): void {
    q('INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)', [$k, $v]);
}
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf() . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Your session expired. Go back, refresh the page and try again.'); }
}
function flash(?string $m = null, string $type = 'ok') {
    if ($m !== null) { $_SESSION['flash'] = [$m, $type]; return; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
    return $f ? '<div class="note ' . $f[1] . '">' . e($f[0]) . '</div>' : '';
}
function user(): ?array { return $_SESSION['user'] ?? null; }
function require_role(array $roles, string $login): array {
    $u = user();
    if (!$u || !in_array($u['role'], $roles, true)) go($login);
    if (time() - ($_SESSION['last'] ?? time()) > 1800) { audit('timeout'); unset($_SESSION['user']); flash('You were logged out after 30 minutes of inactivity.', 'err'); go($login); }
    $_SESSION['last'] = time();
    if (!empty($u['must_change']) && !in_array(basename($_SERVER['SCRIPT_NAME']), ['password.php', 'logout.php'], true))
        go(in_array($u['role'], ['teacher', 'student', 'parent'], true) ? 'portal/password.php' : 'admin/password.php');
    return $u;
}
const PERMS = [
    'admin' => ['*'],
    'principal' => ['dashboard', 'applications', 'students', 'grades', 'reports', 'schedule', 'notices'],
    'hr' => ['dashboard', 'staff', 'payroll', 'leave_admin'],
];
function can(string $p): bool { $x = PERMS[user()['role'] ?? ''] ?? []; return in_array('*', $x, true) || in_array($p, $x, true); }
function require_perm(string $p): void {
    if (!can($p)) { audit('access_denied', $p . ' ' . $_SERVER['SCRIPT_NAME']); http_response_code(403); exit('<p style="font:18px sans-serif;padding:40px">You do not have access to this page. <a href="index.php">Go back</a></p>'); }
}
function audit(string $action, string $detail = ''): void {
    try { q('INSERT INTO audit_log (user_id,username,action,detail,ip) VALUES (?,?,?,?,?)', [user()['id'] ?? null, user()['username'] ?? '', $action, mb_substr($detail, 0, 300), $_SERVER['REMOTE_ADDR'] ?? '']); } catch (Throwable $e) {}
}
function admissions_state(): array {
    $o = setting('apply_open'); $c = setting('apply_close'); $t = date('Y-m-d');
    if (!$o || !$c) return ['closed', 'Applications are not open yet.', $o, $c];
    if ($t < $o) return ['soon', 'Applications open on ' . date('j F Y', strtotime($o)) . '.', $o, $c];
    if ($t > $c) return ['closed', 'The application window closed on ' . date('j F Y', strtotime($c)) . '.', $o, $c];
    return ['open', 'Applications are open until ' . date('j F Y', strtotime($c)) . '.', $o, $c];
}
function grade_for(float $s): array {
    if ($s >= 70) return ['A', 'Excellent'];
    if ($s >= 60) return ['B', 'Very good'];
    if ($s >= 50) return ['C', 'Good'];
    if ($s >= 45) return ['D', 'Fair'];
    if ($s >= 40) return ['E', 'Pass'];
    return ['F', 'Fail'];
}
function upload_image(string $field): ?string {
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) || $_FILES[$field]['size'] > 4 * 1024 * 1024) return null;
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    move_uploaded_file($_FILES[$field]['tmp_name'], __DIR__ . '/../uploads/' . $name);
    return $name;
}
function img(?string $f): string { return $f ? url('uploads/' . $f) : ''; }

function grade_point(string $g): float { return ['A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1, 'F' => 0][$g] ?? 0; }
function gpa(int $stu, ?int $session = null, ?int $upto = null): ?float {
    $sql = 'SELECT grade FROM results WHERE student_id=?'; $p = [$stu];
    if ($session) { $sql .= ' AND session_id=?'; $p[] = $session; }
    if ($upto) { $sql .= ' AND session_id<=?'; $p[] = $upto; }
    $g = q($sql, $p)->fetchAll(PDO::FETCH_COLUMN);
    return $g ? round(array_sum(array_map('grade_point', $g)) / count($g), 2) : null;
}
function ordinal(int $n): string {
    $s = ['th', 'st', 'nd', 'rd']; $v = $n % 100;
    return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
}
function class_positions(string $class, int $session): array {
    $rows = q("SELECT r.student_id, AVG(r.score) a FROM results r WHERE r.class_name=? AND r.session_id=? GROUP BY r.student_id ORDER BY a DESC", [$class, $session])->fetchAll();
    $out = []; $pos = 0; $last = null; $i = 0;
    foreach ($rows as $r) { $i++; if ($last === null || round($r['a'], 2) != $last) { $pos = $i; $last = round($r['a'], 2); } $out[$r['student_id']] = ['avg' => round($r['a'], 1), 'pos' => $pos, 'of' => count($rows)]; }
    return $out;
}
const CLASSES = ['Primary 1', 'Primary 2', 'Primary 3', 'Primary 4', 'Primary 5', 'Primary 6', 'JSS 1', 'JSS 2', 'JSS 3', 'SS 1', 'SS 2', 'SS 3'];
function class_list(): array { $ex = q("SELECT DISTINCT class_name FROM users WHERE role='student' AND active=1 AND class_name<>'' ORDER BY class_name")->fetchAll(PDO::FETCH_COLUMN); return array_values(array_unique(array_merge(CLASSES, $ex))); }
function session_list(): array { return q('SELECT id,title FROM exam_sessions ORDER BY id DESC')->fetchAll(); }
function money($n): string { return "\u{20A6}" . number_format((float)$n, 2); }
function options(array $list, $sel = null): string { $o = ''; foreach ($list as $v => $l) $o .= '<option value="' . e($v) . '" ' . ((string)$v === (string)$sel ? 'selected' : '') . '>' . e($l) . '</option>'; return $o; }
function visible_notices(?array $u, int $limit = 0): array {
    $r = $u['role'] ?? 'guest'; $p = [];
    if ($r === 'guest') $w = "n.audience='public'";
    elseif ($r === 'student') { $w = "(n.audience IN ('public','all') OR (n.audience='students' AND (n.class_name IS NULL OR n.class_name='' OR n.class_name=?)))"; $p[] = $u['class_name'] ?? ''; }
    elseif ($r === 'teacher') $w = "n.audience IN ('public','all','teachers')";
    else $w = '1=1';
    $sql = "SELECT n.*, a.full_name author FROM notices n LEFT JOIN users a ON a.id=n.posted_by WHERE (n.expires_on IS NULL OR n.expires_on>=CURDATE()) AND $w ORDER BY n.pinned DESC, n.created_at DESC" . ($limit ? " LIMIT " . (int)$limit : '');
    try { return q($sql, $p)->fetchAll(); } catch (Throwable $e) { return []; }
}
function notice_cards(array $rows, ?callable $actions = null): string {
    if (!$rows) return '<p>No notices right now.</p>';
    $h = '';
    foreach ($rows as $n) {
        $h .= '<article class="notice' . ($n['pinned'] ? ' pin' : '') . '"><div class="meta">' . ($n['pinned'] ? '<span class="pill soon">Pinned</span> ' : '') . date('j M Y', strtotime($n['created_at'])) . ' &middot; ' . e($n['author'] ?: 'School office') . ($n['class_name'] ? ' &middot; ' . e($n['class_name']) : '') . ' &middot; ' . e(ucfirst($n['audience'])) . '</div><h3>' . e($n['title']) . '</h3><div>' . nl2br(e($n['body'])) . '</div>' . ($actions ? $actions($n) : '') . '</article>';
    }
    return $h;
}
function trend_svg(array $labels, array $vals, float $max, string $unit = ''): string {
    $n = count($vals); if (!$n) return '';
    $w = 560; $h = 220; $pl = 40; $pb = 34; $pw = $w - $pl - 20;
    $xs = fn($i) => $pl + ($n > 1 ? $i * $pw / ($n - 1) : $pw / 2);
    $ys = fn($v) => ($h - $pb) - ($v / $max) * ($h - $pb - 14);
    $o = '<svg viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="Trend chart" style="width:100%;max-width:' . $w . 'px">';
    foreach ([0, .5, 1] as $g) { $y = $ys($g * $max); $o .= '<line x1="' . $pl . '" x2="' . ($w - 20) . '" y1="' . $y . '" y2="' . $y . '" stroke="#E8D5D9"/><text x="' . ($pl - 6) . '" y="' . ($y + 4) . '" text-anchor="end" font-size="11" fill="#6F5A5E">' . round($g * $max, 1) . '</text>'; }
    $pts = []; foreach ($vals as $i => $v) $pts[] = round($xs($i), 1) . ',' . round($ys($v), 1);
    $o .= '<polyline fill="none" stroke="#6B1E2E" stroke-width="3" points="' . implode(' ', $pts) . '"/>';
    foreach ($vals as $i => $v) $o .= '<circle cx="' . $xs($i) . '" cy="' . $ys($v) . '" r="5" fill="#fff" stroke="#6B1E2E" stroke-width="3"><title>' . e($labels[$i]) . ': ' . $v . $unit . '</title></circle><text x="' . $xs($i) . '" y="' . ($h - 10) . '" text-anchor="middle" font-size="11" fill="#2A1A1D">' . e(mb_strimwidth($labels[$i], 0, 16, '..')) . '</text><text x="' . $xs($i) . '" y="' . ($ys($v) - 10) . '" text-anchor="middle" font-size="12" font-weight="600" fill="#4A1220">' . $v . '</text>';
    return $o . '</svg>';
}

const SUBJECTS = ['Arabic Language (Optional)', 'Basic Science and Technology', 'Biology', 'Business Studies', 'Chemistry', 'Civic Education', 'Commerce and Business Studies',
    'Cultural and Creative Arts (CCA)', 'Data Processing', 'English', 'Financial Accounting', 'French', 'Further Mathematics', 'Geography', 'Government', 'History', 'Humanities',
    'Mathematics', 'Nigerian Language', 'Physical and Health Education', 'Physics', 'Pre-Vocational Studies', 'Religion and National Values', 'Social Studies',
    'Trade/Entrepreneurship Studies', 'Visual Arts'];
const NIGERIAN_LANGS = ['Hausa', 'Igbo', 'Yoruba'];
function ca_max(): float { return (float)setting('ca_max', '40') ?: 40.0; }
function exam_max(): float { return (float)setting('exam_max', '60') ?: 60.0; }
function subject_label(string $base, string $lang = ''): string { return ($base === 'Nigerian Language' && $lang) ? "Nigerian Language ($lang)" : $base; }
function split_subject(string $s): array { return preg_match('/^Nigerian Language \((.+)\)$/', $s, $m) ? ['Nigerian Language', $m[1]] : [$s, '']; }
function subject_select(string $name, string $sel = ''): string {
    $o = '<option value="">Choose subject</option>';
    if ($sel !== '' && !in_array($sel, SUBJECTS, true)) $o .= '<option selected>' . e($sel) . '</option>';
    foreach (SUBJECTS as $s) $o .= '<option' . ($s === $sel ? ' selected' : '') . '>' . e($s) . '</option>';
    return '<select name="' . e($name) . '" class="subj">' . $o . '</select>';
}
function lang_select(string $name, string $sel = '', bool $show = false): string {
    $o = '<option value="">Choose language</option>'; foreach (NIGERIAN_LANGS as $l) $o .= '<option' . ($l === $sel ? ' selected' : '') . '>' . e($l) . '</option>';
    return '<select name="' . e($name) . '" class="lang" style="' . ($show ? '' : 'display:none;') . 'margin-top:6px">' . $o . '</select>';
}
function save_score(int $stu, int $sid, string $subject, float $ca, float $exam, int $teacher): void {
    $tot = $ca + $exam; [$g, $rm] = grade_for($tot); $cl = q('SELECT class_name FROM users WHERE id=?', [$stu])->fetchColumn();
    q('INSERT INTO results (student_id,session_id,subject,ca,exam,score,grade,remark,teacher_id,class_name) VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE ca=VALUES(ca),exam=VALUES(exam),score=VALUES(score),grade=VALUES(grade),remark=VALUES(remark),teacher_id=VALUES(teacher_id)',
      [$stu, $sid, $subject, $ca, $exam, $tot, $g, $rm, $teacher, $cl]);
}

function children_of(int $pid): array {
    return q("SELECT u.id,u.full_name,u.class_name FROM parent_children pc JOIN users u ON u.id=pc.student_id WHERE pc.parent_id=? AND u.role='student' ORDER BY u.full_name", [$pid])->fetchAll();
}
function student_info(int $id): ?array { return q("SELECT id,full_name,class_name FROM users WHERE id=? AND role='student'", [$id])->fetch() ?: null; }
/* Which student's data is being viewed: a student sees themselves, a parent sees one of their own linked children, a teacher picks with ?student= */
function acting_student(array $u): int {
    if ($u['role'] === 'student') return (int)$u['id'];
    $want = (int)($_GET['student'] ?? 0);
    if ($u['role'] === 'parent') {
        $ids = array_map('intval', array_column(children_of((int)$u['id']), 'id')); if (!$ids) return 0;
        if (!$want) $want = (int)($_SESSION['child'] ?? 0);
        $c = in_array($want, $ids, true) ? $want : $ids[0]; $_SESSION['child'] = $c; return $c;
    }
    return $want;
}
function child_switcher(array $u, int $stu): string {
    if ($u['role'] !== 'parent') return ''; $k = children_of((int)$u['id']); if (count($k) < 2) return '';
    return '<form method="get" class="noprint" style="margin:0 0 16px"><label class="mini">Viewing</label><select name="student" onchange="this.form.submit()" style="max-width:320px">' . options(array_column($k, 'full_name', 'id'), $stu) . '</select></form>';
}
function notice_user(array $u): array { if ($u['role'] !== 'parent') return $u; $c = student_info(acting_student($u)); return ['role' => 'student', 'class_name' => $c['class_name'] ?? '']; }
function link_children(int $pid, string $csv): int {
    if (!q("SELECT 1 FROM users WHERE id=? AND role='parent'", [$pid])->fetch()) return 0;
    q('DELETE FROM parent_children WHERE parent_id=?', [$pid]); $n = 0;
    foreach (array_filter(array_map('trim', explode(',', $csv))) as $un) {
        $s = q("SELECT id FROM users WHERE username=? AND role='student'", [$un])->fetchColumn();
        if ($s) $n += q('INSERT IGNORE INTO parent_children (parent_id,student_id) VALUES (?,?)', [$pid, (int)$s])->rowCount();
    }
    return $n;
}

/* images managed in Admin > Logo & images */
function site_image(string $key, string $fallback = ''): string {
    $f = basename(setting($key));
    return ($f !== '' && is_file(__DIR__ . '/../uploads/' . $f)) ? url('uploads/' . $f) : $fallback;
}
function logo_path(): string {
    $f = basename(setting('logo_file'));
    if ($f !== '' && is_file(__DIR__ . '/../uploads/' . $f)) return 'uploads/' . $f;
    return is_file(__DIR__ . '/../assets/logo.png') ? 'assets/logo.png' : '';
}
function site_logo(): string { $p = logo_path(); return $p ? url($p) : ''; }
function brand_mark(string $name): string {
    $l = site_logo();
    return $l ? '<img class="logo" src="' . e($l) . '" alt="">' : '<span class="crest">' . e(mb_substr($name, 0, 1)) . '</span>';
}
