<?php require '_menu.php'; if ($u['role'] !== 'teacher') go('portal/index.php');
$p = q('SELECT s.* FROM staff_profiles s WHERE s.user_id=?', [$u['id']])->fetch() ?: [];
if (isset($_GET['slip'])) { $chk = q("SELECT id FROM payroll WHERE id=? AND user_id=? AND status='Paid'", [(int)$_GET['slip'], $u['id']])->fetch();
    app_top('Payslip', $menu, $area); echo $chk ? payslip_html((int)$chk['id']) : '<div class="note err">Payslip not available.</div>'; audit('payslip_view', $_GET['slip']); app_bottom(); exit; }
$pay = q("SELECT * FROM payroll WHERE user_id=? AND status='Paid' ORDER BY month DESC", [$u['id']])->fetchAll();
app_top('My HR & payslips', $menu, $area); ?>
<h1>My HR record and payslips</h1>
<div class="panel"><h3>Employment details</h3><?php if (!$p): echo '<p>The HR office has not completed your record yet.</p>'; else: ?><div class="split"><div><p><strong>Staff no:</strong> <?= e($p['staff_no'] ?: '-') ?><br><strong>Position:</strong> <?= e($p['position'] ?: '-') ?><br><strong>Department:</strong> <?= e($p['department'] ?: '-') ?><br><strong>Qualification:</strong> <?= e($p['qualification'] ?: '-') ?><br><strong>Date joined:</strong> <?= $p['hired_on'] ? date('j F Y', strtotime($p['hired_on'])) : '-' ?></p></div>
<div><p><strong>Phone:</strong> <?= e($p['phone'] ?: '-') ?><br><strong>Address:</strong> <?= e($p['address'] ?: '-') ?><br><strong>Bank:</strong> <?= e($p['bank_name'] ?: '-') ?> <?= e($p['account_no']) ?><br><strong>Emergency contact:</strong> <?= e($p['emergency_contact'] ?: '-') ?></p></div></div><?php endif; ?></div>
<div class="panel"><h3>Payslips</h3><div class="tw"><table><thead><tr><th>Month</th><th>Net pay</th><th>Paid on</th><th></th></tr></thead><tbody>
<?php foreach ($pay as $x) echo '<tr><td>' . date('F Y', strtotime($x['month'] . '-01')) . '</td><td>' . money($x['net']) . '</td><td>' . ($x['paid_on'] ? date('j M Y', strtotime($x['paid_on'])) : '') . '</td><td><a href="hr.php?slip=' . $x['id'] . '">View / print</a></td></tr>'; if (!$pay) echo '<tr><td colspan="4">No payslips yet.</td></tr>'; ?></tbody></table></div></div>
<?php app_bottom();
