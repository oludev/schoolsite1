<?php require '_menu.php'; require_perm('notices');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    if (isset($_POST['delete'])) { q('DELETE FROM notices WHERE id=?', [(int)$_POST['delete']]); audit('notice_deleted', $_POST['delete']); flash('Notice removed.'); }
    elseif (isset($_POST['pin'])) { q('UPDATE notices SET pinned=1-pinned WHERE id=?', [(int)$_POST['pin']]); flash('Updated.'); }
    elseif (trim($_POST['title'] ?? '')) { $a = in_array($_POST['audience'], ['public', 'all', 'teachers', 'students'], true) ? $_POST['audience'] : 'all';
        q('INSERT INTO notices (title,body,audience,class_name,pinned,expires_on,posted_by) VALUES (?,?,?,?,?,?,?)', [trim($_POST['title']), trim($_POST['body']), $a, $a === 'students' ? trim($_POST['class']) : null, isset($_POST['pinned']) ? 1 : 0, $_POST['expires'] ?: null, $u['id']]);
        audit('notice_posted', $_POST['title']); flash('Notice posted.'); }
    go('admin/notices.php'); }
app_top('Notice board', $menu, $area); ?><h1>Notice board</h1>
<form method="post" class="panel f"><?= csrf_field() ?><h3>Post a notice</h3><div><label>Title</label><input name="title" required></div><div><label>Message</label><textarea name="body"></textarea></div>
<div class="row"><div><label>Who can see it</label><select name="audience"><option value="public">Everyone, including the public website</option><option value="all">All logged-in users</option><option value="teachers">Teachers only</option><option value="students">Students (all or one class)</option></select></div><div><label>Class (for students only; leave empty for all students)</label><input name="class" list="cl"><datalist id="cl"><?php foreach (class_list() as $c) echo '<option value="' . e($c) . '">'; ?></datalist></div></div>
<div class="row"><div><label>Hide after (optional)</label><input type="date" name="expires"></div><div><label><input type="checkbox" name="pinned" style="width:auto"> Pin to the top</label></div></div><div><button class="btn">Post notice</button></div></form>
<?= notice_cards(visible_notices($u), fn($n) => '<form method="post" class="acts" style="margin-top:8px">' . csrf_field() . '<button class="btn small ghost" name="pin" value="' . $n['id'] . '">' . ($n['pinned'] ? 'Unpin' : 'Pin') . '</button><button class="btn small danger" name="delete" value="' . $n['id'] . '" onclick="return confirm(\'Remove this notice?\')">Remove</button></form>') ?>
<?php app_bottom();
