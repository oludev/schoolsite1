<?php require '_menu.php'; require_perm('content');
$T = [
 'posts' => ['News & blog posts', 'posts', [['title', 'text', 'Title'], ['category', 'select', 'Type', ['News', 'Blog']], ['excerpt', 'text', 'Short summary'], ['body', 'textarea', 'Full text'], ['image', 'file', 'Image (optional)']], ['title', 'category', 'created_at'], 'created_at DESC'],
 'events' => ['Events', 'events', [['title', 'text', 'Event name'], ['event_date', 'date', 'Date'], ['location', 'text', 'Location'], ['description', 'textarea', 'Details']], ['title', 'event_date', 'location'], 'event_date DESC'],
 'exams' => ['Exam sessions', 'exam_sessions', [['title', 'text', 'Title (e.g. First Term Exams 2026)'], ['section', 'select', 'Section', ['Both', 'Primary', 'Secondary']], ['start_date', 'date', 'Start date'], ['end_date', 'date', 'End date'], ['notes', 'textarea', 'Notes']], ['title', 'section', 'start_date'], 'start_date DESC'],
 'gallery' => ['Gallery photos', 'gallery', [['caption', 'text', 'Caption'], ['album', 'text', 'Album (e.g. Sports Day. Use "Home Page" to show the photo on the home page)'], ['image', 'file', 'Photo']], ['caption', 'album', 'image'], 'id DESC'],
 'activities' => ['Activities', 'activities', [['name', 'text', 'Activity name'], ['description', 'textarea', 'Description'], ['image', 'file', 'Image (optional)']], ['name'], 'name'],
 'testimonials' => ['Parent testimonials', 'testimonials', [['parent_name', 'text', 'Parent name (for example Mrs. Adeyemi)'], ['role_line', 'text', 'Line under the name (for example Parent of a JSS 2 student)'], ['quote', 'textarea', 'What the parent said'], ['image', 'file', 'Photo (optional)']], ['parent_name', 'role_line'], 'id DESC'],
 'faqs' => ['FAQs', 'faqs', [['question', 'text', 'Question'], ['answer', 'textarea', 'Answer'], ['sort_order', 'number', 'Order (1 shows first)']], ['question', 'sort_order'], 'sort_order, id'],
];
$t = $_GET['t'] ?? 'posts'; if (!isset($T[$t])) go('admin/index.php');
[$label, $table, $fields, $cols, $order] = $T[$t]; $self = "manage.php?t=$t";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['delete'])) { q("DELETE FROM `$table` WHERE id=?", [(int)$_POST['delete']]); flash('Deleted.'); go("admin/$self"); }
    $id = (int)($_POST['id'] ?? 0); $names = []; $vals = [];
    $old = $id ? q("SELECT * FROM `$table` WHERE id=?", [$id])->fetch() : [];
    foreach ($fields as $f) {
        if ($f[1] === 'file') { $name = upload_image($f[0]) ?: ($old[$f[0]] ?? ''); if (!$name && $t === 'gallery') { flash('Choose a JPG, PNG, WEBP or GIF photo under 4 MB.', 'err'); go("admin/$self&a=new"); } $v = $name; }
        else $v = trim($_POST[$f[0]] ?? '');
        if ($v === '' && $f[1] === 'date') $v = null;
        if ($v === '' && $f[1] === 'number') $v = 0;
        $names[] = $f[0]; $vals[] = $v;
    }
    if ($id) { q("UPDATE `$table` SET " . implode(',', array_map(fn($n) => "`$n`=?", $names)) . ' WHERE id=?', [...$vals, $id]); }
    else { q("INSERT INTO `$table` (`" . implode('`,`', $names) . '`) VALUES (' . implode(',', array_fill(0, count($names), '?')) . ')', $vals); }
    flash('Saved.'); go("admin/$self");
}
$a = $_GET['a'] ?? ''; $row = $a === 'edit' ? q("SELECT * FROM `$table` WHERE id=?", [(int)($_GET['id'] ?? 0)])->fetch() : [];
app_top($label, $menu, $area); ?>
<h1><?= e($label) ?></h1>
<?php if ($a === 'new' || ($a === 'edit' && $row)): ?>
<form class="panel f" method="post" enctype="multipart/form-data" style="max-width:760px"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($row['id'] ?? 0) ?>">
<?php foreach ($fields as $f): [$n, $type, $lab] = $f; $v = $row[$n] ?? ''; ?><div><label><?= e($lab) ?></label>
 <?php if ($type === 'textarea') echo '<textarea name="' . $n . '" rows="6">' . e($v) . '</textarea>';
 elseif ($type === 'select') { echo '<select name="' . $n . '">'; foreach ($f[3] as $o) echo '<option ' . ($o === $v ? 'selected' : '') . '>' . e($o) . '</option>'; echo '</select>'; }
 elseif ($type === 'file') echo '<input type="file" name="' . $n . '" accept="image/*">' . ($v ? '<p style="margin:6px 0 0;color:var(--mute)">Current file kept unless you choose a new one.</p>' : '');
 else echo '<input type="' . $type . '" name="' . $n . '" value="' . e($v) . '">'; ?></div><?php endforeach; ?>
<div class="acts"><button class="btn">Save</button><a class="btn ghost" href="<?= $self ?>">Cancel</a></div></form>
<?php else: $rows = q("SELECT * FROM `$table` ORDER BY $order")->fetchAll(); ?>
<p><a class="btn" href="<?= $self ?>&a=new">Add new</a></p>
<div class="tw"><table><thead><tr><?php foreach ($cols as $c) echo '<th>' . e(ucfirst(str_replace('_', ' ', $c))) . '</th>'; ?><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><?php foreach ($cols as $c): ?><td><?= $c === 'image' ? '<img src="' . img($r[$c]) . '" alt="" style="width:70px;border-radius:6px">' : e(mb_strimwidth((string)$r[$c], 0, 70, '...')) ?></td><?php endforeach; ?>
<td><div class="acts"><?php if ($t !== 'gallery'): ?><a class="btn small ghost" href="<?= $self ?>&a=edit&id=<?= $r['id'] ?>">Edit</a><?php endif; ?>
<form method="post" onsubmit="return confirm('Delete this item?')"><?= csrf_field() ?><button class="btn small danger" name="delete" value="<?= $r['id'] ?>">Delete</button></form></div></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="' . (count($cols) + 1) . '">Nothing here yet. Click Add new.</td></tr>'; ?></tbody></table></div>
<?php endif; app_bottom();
