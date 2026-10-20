<?php require '_menu.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    if ($u['role'] === 'teacher' && isset($_POST['delete'])) { q('DELETE FROM notices WHERE id=? AND posted_by=?', [(int)$_POST['delete'], $u['id']]); audit('notice_deleted', $_POST['delete']); flash('Notice removed.'); }
    elseif ($u['role'] === 'teacher' && trim($_POST['title'] ?? '') && trim($_POST['class'] ?? '')) {
        q("INSERT INTO notices (title,body,audience,class_name,expires_on,posted_by) VALUES (?,?,'students',?,?,?)", [trim($_POST['title']), trim($_POST['body'] ?? ''), trim($_POST['class']), $_POST['expires'] ?: null, $u['id']]);
        audit('notice_posted', $_POST['title']); flash('Notice posted to ' . $_POST['class'] . '.'); }
    else flash('Add a title and choose a class.', 'err');
    go('portal/notices.php'); }
app_top('Notice board', $menu, $area); ?>
<h1>Notice board</h1>
<?php if ($u['role'] === 'teacher'): ?><form method="post" class="panel f"><?= csrf_field() ?><h3>Post a notice to a class</h3>
<div class="row"><div><label>Title</label><input name="title" required></div><div><label>Class</label><select name="class" required><option value="">Choose</option><?= options(array_combine(class_list(), class_list())) ?></select></div></div>
<div><label>Message</label><textarea name="body"></textarea></div><div><label>Hide after (optional)</label><input type="date" name="expires" style="max-width:220px"></div><div><button class="btn">Post notice</button></div></form><?php endif; ?>
<?= notice_cards(visible_notices($u), fn($n) => ($u['role'] === 'teacher' && $n['posted_by'] == $u['id']) ? '<form method="post" style="margin-top:8px" onsubmit="return confirm(\'Remove this notice?\')">' . csrf_field() . '<button class="btn small danger" name="delete" value="' . $n['id'] . '">Remove</button></form>' : '') ?>
<?php app_bottom();
