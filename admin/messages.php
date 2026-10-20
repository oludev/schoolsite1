<?php require '_menu.php'; require_perm('content');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    if (isset($_POST['delete'])) q('DELETE FROM messages WHERE id=?', [(int)$_POST['delete']]); else q('UPDATE messages SET is_read=1 WHERE id=?', [(int)$_POST['read']]);
    go('admin/messages.php'); }
$rows = q('SELECT * FROM messages ORDER BY id DESC')->fetchAll(); app_top('Messages', $menu, $area); ?>
<h1>Messages</h1>
<?php foreach ($rows as $m): ?><div class="panel" style="<?= $m['is_read'] ? '' : 'border-left:6px solid var(--wine)' ?>"><strong><?= e($m['subject'] ?: '(no subject)') ?></strong>
<p style="color:var(--mute)"><?= e($m['name']) ?> &middot; <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a> &middot; <?= date('j M Y, g:i a', strtotime($m['created_at'])) ?></p><p><?= nl2br(e($m['body'])) ?></p>
<form method="post" class="acts"><?= csrf_field() ?><?php if (!$m['is_read']): ?><button class="btn small ghost" name="read" value="<?= $m['id'] ?>">Mark as read</button><?php endif; ?><button class="btn small danger" name="delete" value="<?= $m['id'] ?>" onclick="return confirm('Delete this message?')">Delete</button></form></div>
<?php endforeach; if (!$rows) echo '<div class="panel">No messages yet.</div>'; app_bottom();
