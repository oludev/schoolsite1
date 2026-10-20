<?php require '_menu.php'; require_perm('users');
if (isset($_GET['template'])) {
    header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="students-template.csv"'); $o = fopen('php://output', 'w');
    fputcsv($o, ['full_name', 'class', 'admission_no', 'dob', 'gender', 'guardian_name', 'guardian_phone', 'username'], ',', '"', '');
    fputcsv($o, ['Chidi Okeke', 'Primary 1', 'ADM-001', '2019-05-14', 'Male', 'Mr Okeke', '08030000000', ''], ',', '"', '');
    fputcsv($o, ['Zainab Musa', 'SS 3', 'ADM-002', '2008-11-02', 'Female', 'Mrs Musa', '08040000000', ''], ',', '"', ''); exit;
}
$done = []; $skip = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check(); set_time_limit(180); $f = $_FILES['csv'] ?? null;
    if (!$f || $f['error'] !== UPLOAD_ERR_OK || strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)) !== 'csv' || $f['size'] > 2 * 1024 * 1024) flash('Choose a .csv file under 2 MB.', 'err');
    else {
        $h = fopen($f['tmp_name'], 'r'); $cols = fgetcsv($h, 0, ',', '"', '');
        $cols = array_map(fn($c) => strtolower(trim(ltrim((string)$c, "\xEF\xBB\xBF"))), $cols ?: []);
        if (!in_array('full_name', $cols, true) || !in_array('class', $cols, true)) flash('The first row must have the headings full_name and class. Download the template to see the layout.', 'err');
        else {
            $n = 0;
            while (($row = fgetcsv($h, 0, ',', '"', '')) !== false) {
                if (!array_filter($row)) continue;
                if (++$n > 300) { $skip[] = ['(rest of file)', 'Only 300 students can be imported at a time. Upload the remaining rows again.']; break; }
                $r = array_combine($cols, array_pad(array_slice($row, 0, count($cols)), count($cols), ''));
                $name = trim($r['full_name']); $cl = trim(preg_replace('/\s+/', ' ', $r['class']));
                if (!$name || !$cl) { $skip[] = [$name ?: '(blank name)', 'Missing name or class']; continue; }
                if (q("SELECT 1 FROM users WHERE role='student' AND full_name=? AND class_name=?", [$name, $cl])->fetch()) { $skip[] = [$name, 'Already in ' . $cl]; continue; }
                $un = strtolower(trim($r['username'] ?? ''));
                if (!$un) { $p = preg_split('/\s+/', strtolower(trim(preg_replace('/[^a-zA-Z ]/', '', $name)))); $un = count($p) > 1 ? $p[0] . '.' . end($p) : $p[0]; }
                $base = $un ?: 'student'; $un = $base; $i = 1; while (q('SELECT 1 FROM users WHERE username=?', [$un])->fetch()) $un = $base . (++$i);
                $pw = substr(bin2hex(random_bytes(5)), 0, 8);
                q('INSERT INTO users (role,full_name,username,class_name,password_hash,must_change) VALUES (?,?,?,?,?,1)', ['student', $name, $un, $cl, password_hash($pw, PASSWORD_DEFAULT)]); $id = (int)db()->lastInsertId();
                $dob = trim($r['dob'] ?? ''); $dob = ($dob && strtotime($dob)) ? date('Y-m-d', strtotime($dob)) : null;
                q('INSERT INTO student_profiles (user_id,admission_no,dob,gender,guardian_name,guardian_phone,admitted_on) VALUES (?,?,?,?,?,?,CURDATE())', [$id, trim($r['admission_no'] ?? ''), $dob, trim($r['gender'] ?? ''), trim($r['guardian_name'] ?? ''), trim($r['guardian_phone'] ?? '')]);
                $done[] = [$name, $cl, $un, $pw];
            }
            audit('students_imported', count($done) . ' added, ' . count($skip) . ' skipped');
        }
        fclose($h);
    }
}
app_top('Import students', $menu, $area); ?><h1>Import students</h1>
<?php if ($done || $skip): ?>
<div class="panel"><h3><?= count($done) ?> student(s) added</h3><p class="note err">Print or save this list now. The temporary passwords are shown only once. Each student must choose a new password at first login.</p>
<div class="tw scroll"><table><thead><tr><th>Name</th><th>Class</th><th>Username</th><th>Temporary password</th></tr></thead><tbody><?php foreach ($done as $d) echo '<tr><td>' . e($d[0]) . '</td><td>' . e($d[1]) . '</td><td>' . e($d[2]) . '</td><td><code>' . e($d[3]) . '</code></td></tr>'; if (!$done) echo '<tr><td colspan="4">Nobody was added.</td></tr>'; ?></tbody></table></div>
<p class="noprint" style="margin-top:12px"><button class="btn" onclick="window.print()">Print login list</button></p></div>
<?php if ($skip): ?><div class="panel"><h3>Skipped</h3><?php foreach ($skip as $k) echo '<p style="margin:0 0 4px">' . e($k[0]) . ': ' . e($k[1]) . '</p>'; ?></div><?php endif; ?>
<?php endif; ?>
<div class="panel"><h3>How it works</h3><p>1. <a href="import.php?template=1">Download the spreadsheet template</a> and open it in Excel or Google Sheets.<br>2. Fill in one student per row, from Primary 1 to SS 3. Only <strong>full_name</strong> and <strong>class</strong> are required. Write classes exactly as <?= e(implode(', ', CLASSES)) ?>.<br>3. Save as CSV and upload it below. Up to 300 students at a time. Usernames are made from the name (for example <code>chidi.okeke</code>) and each student gets a random temporary password.</p>
<form method="post" enctype="multipart/form-data" class="f"><?= csrf_field() ?><div><label>CSV file</label><input type="file" name="csv" accept=".csv" required></div><div><button class="btn">Import students</button></div></form></div>
<?php app_bottom();
