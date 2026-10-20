<?php require '_menu.php'; if ($u['role'] !== 'teacher') go('portal/index.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check(); $f = array_map('trim', $_POST);
    if (isset($f['cancel'])) { q("DELETE FROM leave_requests WHERE id=? AND user_id=? AND status='Pending'", [(int)$f['cancel'], $u['id']]); flash('Request cancelled.'); }
    elseif (!$f['start'] || !$f['end'] || $f['end'] < $f['start'] || $f['start'] < date('Y-m-d')) flash('Choose valid dates. The end date cannot be before the start date.', 'err');
    else { q('INSERT INTO leave_requests (user_id,leave_type,start_date,end_date,reason) VALUES (?,?,?,?,?)', [$u['id'], $f['type'], $f['start'], $f['end'], $f['reason']]); audit('leave_requested', $f['start'] . ' to ' . $f['end']); flash('Leave request sent to HR.'); }
    go('portal/leave.php'); }
$rows = q('SELECT * FROM leave_requests WHERE user_id=? ORDER BY id DESC', [$u['id']])->fetchAll(); app_top('Leave', $menu, $area); ?>
<h1>Leave</h1><form method="post" class="panel f"><?= csrf_field() ?><h3>Request leave</h3>
<div class="row"><div><label>Type</label><select name="type"><?= options(array_combine(['Annual', 'Sick', 'Casual', 'Maternity/Paternity', 'Study', 'Other'], ['Annual', 'Sick', 'Casual', 'Maternity/Paternity', 'Study', 'Other'])) ?></select></div><div></div></div>
<div class="row"><div><label>From</label><input type="date" name="start" required></div><div><label>To</label><input type="date" name="end" required></div></div>
<div><label>Reason</label><input name="reason" maxlength="300"></div><div><button class="btn">Send request</button></div></form>
<div class="tw scroll"><table><thead><tr><th>Type</th><th>Dates</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($rows as $r) echo '<tr><td>' . e($r['leave_type']) . '</td><td>' . date('j M', strtotime($r['start_date'])) . ' to ' . date('j M Y', strtotime($r['end_date'])) . '</td><td><span class="pill ' . ($r['status'] === 'Approved' ? 'open' : ($r['status'] === 'Pending' ? 'soon' : 'closed')) . '">' . e($r['status']) . '</span></td><td>' . ($r['status'] === 'Pending' ? '<form method="post">' . csrf_field() . '<button class="btn small ghost" name="cancel" value="' . $r['id'] . '">Cancel</button></form>' : '') . '</td></tr>'; if (!$rows) echo '<tr><td colspan="4">No leave requests yet.</td></tr>'; ?></tbody></table></div>
<?php app_bottom();
