<?php
function payslip_html(int $id): string {
    $p = q('SELECT p.*, u.full_name, u.role, s.staff_no, s.position, s.department, s.bank_name, s.account_no FROM payroll p JOIN users u ON u.id=p.user_id LEFT JOIN staff_profiles s ON s.user_id=u.id WHERE p.id=?', [$id])->fetch();
    if (!$p) return '<div class="note err">Payslip not found.</div>';
    $r = fn($l, $v, $b = false) => '<tr><' . ($b ? 'th' : 'td') . '>' . $l . '</' . ($b ? 'th' : 'td') . '><' . ($b ? 'th' : 'td') . ' style="text-align:right">' . $v . '</' . ($b ? 'th' : 'td') . '></tr>';
    return '<div class="rc" style="max-width:640px"><h2>' . e(setting('school_name', 'Wineridge Academy')) . '</h2><p class="sub"><strong>Payslip &middot; ' . date('F Y', strtotime($p['month'] . '-01')) . '</strong></p>
<div class="info"><div>Name: <strong>' . e($p['full_name']) . '</strong></div><div>Staff no: ' . e($p['staff_no'] ?: '-') . '</div><div>Position: ' . e($p['position'] ?: ucfirst($p['role'])) . '</div><div>Department: ' . e($p['department'] ?: '-') . '</div><div>Bank: ' . e($p['bank_name'] ?: '-') . '</div><div>Account: ' . e($p['account_no'] ?: '-') . '</div></div>
<table><tbody>' . $r('Basic salary', money($p['basic'])) . $r('Allowances', money($p['allowances'])) . $r('Bonus', money($p['bonus'])) . $r('Deductions (pension, tax, other)', '-' . money($p['deductions'])) . $r('Net pay', money($p['net']), true) . '</tbody></table>
<p class="mini" style="margin-top:12px">Status: ' . e($p['status']) . ($p['paid_on'] ? ' on ' . date('j M Y', strtotime($p['paid_on'])) : '') . '</p></div><p class="noprint" style="margin-top:14px"><button class="btn" onclick="window.print()">Print payslip</button></p>';
}
