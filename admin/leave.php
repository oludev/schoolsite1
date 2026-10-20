<?php require '_menu.php'; require_perm('leave_admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check(); $st = $_POST['decision'] ?? '';
    if (in_array($st, ['Approved', 'Declined'], true)) { q("UPDATE leave_requests SET status=?, decided_by=? WHERE id=? AND status='Pending'", [$st, $u['id'], (int)$_POST['id']]); audit('leave_' . strtolower($st), 'request ' . $_POST['id']); flash("Request $st."); }
    go('admin/leave.php'); }
$rows = q('SELECT l.*, u.full_name FROM leave_requests l JOIN users u ON u.id=l.user_id ORDER BY l.status="Pending" DESC, l.id DESC LIMIT 100')->fetchAll();
app_top('Leave requests', $menu, $area); ?><h1>Leave requests</h1>
<div class="tw scroll"><table><thead><tr><th>Staff</th><th>Type</th><th>Dates</th><th>Days</th><th>Reason</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): $days = (int)((strtotime($r['end_date']) - strtotime($r['start_date'])) / 86400) + 1; ?><tr><td><?= e($r['full_name']) ?></td><td><?= e($r['leave_type']) ?></td><td><?= date('j M', strtotime($r['start_date'])) ?> to <?= date('j M Y', strtotime($r['end_date'])) ?></td><td><?= $days ?></td><td><?= e($r['reason']) ?></td>
<td><span class="pill <?= $r['status'] === 'Approved' ? 'open' : ($r['status'] === 'Pending' ? 'soon' : 'closed') ?>"><?= e($r['status']) ?></span></td>
<td><?php if ($r['status'] === 'Pending'): ?><form method="post" class="acts"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn small" name="decision" value="Approved">Approve</button><button class="btn small danger" name="decision" value="Declined">Decline</button></form><?php endif; ?></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="7">No leave requests.</td></tr>'; ?></tbody></table></div><?php app_bottom();
