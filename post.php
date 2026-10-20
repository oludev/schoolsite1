<?php require 'config.php'; $p = q('SELECT * FROM posts WHERE id=?', [(int)($_GET['id'] ?? 0)])->fetch();
if (!$p) { http_response_code(404); } $title = $p['title'] ?? 'Not found'; require 'includes/header.php'; ?>
<section class="s"><div class="wrap" style="max-width:760px">
<?php if (!$p): ?><h1>Post not found</h1><p><a href="<?= url('news.php') ?>">Back to news</a></p>
<?php else: ?><p style="color:var(--mute)"><?= e($p['category']) ?> &middot; <?= date('j F Y', strtotime($p['created_at'])) ?></p><h1><?= e($p['title']) ?></h1>
<?php if ($p['image']): ?><img src="<?= img($p['image']) ?>" alt="" style="border-radius:12px;margin-bottom:24px"><?php endif; ?>
<div style="max-width:none"><?= nl2br(e($p['body'])) ?></div><p style="margin-top:30px"><a href="<?= url($p['category'] === 'Blog' ? 'blog.php' : 'news.php') ?>">Back to <?= strtolower($p['category']) ?></a></p><?php endif; ?></div></section>
<?php require 'includes/footer.php'; ?>
