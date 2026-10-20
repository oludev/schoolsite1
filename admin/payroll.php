<?php require '_menu.php'; require_perm('payroll');
$month = preg_match('/^\d{4}-\d{2}$/', $_GET['month'] ?? '') ? $_GET['month'] : date('Y-m'); $self = 'admin/payroll.php?month=' . $month;
if (isset($_GET['export'])) { require_perm('payroll'); audit('payroll_export', $month); header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="payroll-' . $month . '.csv"');
    $o = fopen('php://output', 'w'); fputcsv($o, ['Name', 'Bank', 'Account', 'Net pay', 'Status'], ',', '"', '');
    foreach (q('SELECT u.full_name,s.bank_name,s.account_no,p.net,p.status FROM payroll p JOIN users u ON u.id=p.user_id LEFT JOIN staff_profiles s ON s.user_id=u.id WHERE p.month=? ORDER BY u.full_name', [$month])->fetchAll() as $r) fputcsv($o, $r, ',', '"', ''); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    if (isset($_POST['rates'])) { save_setting('pension_pct', (string)max(0, (float)$_POST['pension'])); save_setting('tax_pct', (string)max(0, (float)$_POST['tax'])); flash('Deduction rates saved.'); }
    elseif (isset($_POST['generate'])) { $pc = (float)setting('pension_pct', '8') + (float)setting('tax_pct', '0'); $n = 0;
        foreach (q("SELECT u.id,s.basic_salary,s.allowances FROM users u JOIN staff_profiles s ON s.user_id=u.id WHERE u.active=1 AND u.role IN ('teacher','principal','hr') AND s.basic_salary>0")->fetchAll() as $r) {
            $ded = round($r['basic_salary'] * $pc / 100, 2); $net = $r['basic_salary'] + $r['allowances'] - $ded;
            $n += q('INSERT IGNORE INTO payroll (user_id,month,basic,allowances,bonus,deductions,net) VALUES (?,?,?,?,0,?,?)', [$r['id'], $month, $r['basic_salary'], $r['allowances'], $ded, $net])->rowCount(); }
        audit('payroll_generated', "$month: $n rows"); flash("Payroll generated for $n staff member(s)."); }
    elseif (isset($_POST['save'])) { $id = (int)$_POST['save']; $bonus = max(0, (float)$_POST['bonus'][$id]); $ded = max(0, (float)$_POST['ded'][$id]);
        q("UPDATE payroll SET bonus=?, deductions=?, net=basic+allowances+?-? WHERE id=? AND status='Draft'", [$bonus, $ded, $bonus, $ded, $id]); audit('payroll_edited', 'row ' . $id); flash('Saved.'); }
    elseif (isset($_POST['pay'])) { q("UPDATE payroll SET status='Paid', paid_on=CURDATE() WHERE id=? AND status='Draft'", [(int)$_POST['pay']]); audit('payroll_paid', 'row ' . $_POST['pay']); flash('Marked as paid. The staff member can now see the payslip.'); }
    elseif (isset($_POST['payall'])) { $n = q("UPDATE payroll SET status='Paid', paid_on=CURDATE() WHERE month=? AND status='Draft'", [$month])->rowCount(); audit('payroll_paid_all', "$month: $n"); flash("$n payslip(s) marked as paid."); }
    elseif (isset($_POST['remove'])) { q("DELETE FROM payroll WHERE id=? AND status='Draft'", [(int)$_POST['remove']]); audit('payroll_removed', 'row ' . $_POST['remove']); flash('Draft removed.'); }
    go($self); }
$rows = q('SELECT p.*, u.full_name FROM payroll p JOIN users u ON u.id=p.user_id WHERE p.month=? ORDER BY u.full_name', [$month])->fetchAll();
app_top('Payroll', $menu, $area); ?><h1>Payroll</h1>
<div class="split"><form method="get" class="panel f"><h3>Month</h3><div><input type="month" name="month" value="<?= e($month) ?>"></div><div><button class="btn small">Open month</button></div></form>
<form method="post" class="panel f"><?= csrf_field() ?><h3>Deduction rates</h3><div class="row"><div><label>Pension %</label><input type="number" step="0.1" min="0" name="pension" value="<?= e(setting('pension_pct', '8')) ?>"></div><div><label>Tax %</label><input type="number" step="0.1" min="0" name="tax" value="<?= e(setting('tax_pct', '0')) ?>"></div></div><div><button class="btn small ghost" name="rates" value="1">Save rates</button></div></form></div>
<form method="post" class="panel acts"><?= csrf_field() ?><button class="btn" name="generate" value="1">Generate <?= date('F Y', strtotime($month . '-01')) ?> payroll</button><?php if ($rows): ?><button class="btn ghost" name="payall" value="1" onclick="return confirm('Mark every draft as paid?')">Mark all as paid</button><a class="btn ghost" href="payroll.php?month=<?= e($month) ?>&export=1">Download bank list (CSV)</a><?php endif; ?></form>
<form method="post"><?= csrf_field() ?><div class="tw scroll"><table><thead><tr><th>Staff</th><th>Basic</th><th>Allowances</th><th>Bonus</th><th>Deductions</th><th>Net pay</th><th>Status</th><th></th></tr></thead><tbody>
<?php $tot = 0; foreach ($rows as $r): $tot += $r['net']; $d = $r['status'] === 'Draft'; ?><tr><td><?= e($r['full_name']) ?></td><td><?= money($r['basic']) ?></td><td><?= money($r['allowances']) ?></td>
<td><?= $d ? '<input type="number" step="0.01" min="0" name="bonus[' . $r['id'] . ']" value="' . e($r['bonus']) . '" style="width:120px">' : money($r['bonus']) ?></td><td><?= $d ? '<input type="number" step="0.01" min="0" name="ded[' . $r['id'] . ']" value="' . e($r['deductions']) . '" style="width:120px">' : money($r['deductions']) ?></td>
<td><strong><?= money($r['net']) ?></strong></td><td><span class="pill <?= $d ? 'soon' : 'open' ?>"><?= e($r['status']) ?></span></td>
<td><div class="acts"><?php if ($d): ?><button class="btn small ghost" name="save" value="<?= $r['id'] ?>">Save</button><button class="btn small" name="pay" value="<?= $r['id'] ?>">Mark paid</button><button class="btn small danger" name="remove" value="<?= $r['id'] ?>" onclick="return confirm('Remove this draft?')">Remove</button><?php endif; ?><a class="btn small ghost" href="payslip.php?id=<?= $r['id'] ?>">Payslip</a></div></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="8">No payroll for this month yet. Click Generate.</td></tr>'; else echo '<tr><th colspan="5">Total net pay</th><th colspan="3">' . money($tot) . '</th></tr>'; ?></tbody></table></div></form>
<?php app_bottom();
