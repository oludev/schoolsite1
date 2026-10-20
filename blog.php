<?php $title = 'Blog'; require 'includes/header.php';
$posts = q("SELECT * FROM posts WHERE category='Blog' ORDER BY created_at DESC")->fetchAll(); ?>
<div class="pagehead"><div class="wrap"><h1>Blog</h1><p>Stories, tips and reflections from our teachers and pupils.</p></div></div>
<section class="s"><div class="wrap"><div class="grid">
<?php foreach ($posts as $p): ?><article class="card"><?php if ($p['image']): ?><img src="<?= img($p['image']) ?>" alt=""><?php endif; ?><div class="pad"><div class="meta"><?= date('j F Y', strtotime($p['created_at'])) ?></div>
<h3><?= e($p['title']) ?></h3><p><?= e($p['excerpt']) ?></p><a class="more" href="<?= url('post.php?id=' . $p['id']) ?>">Read post</a></div></article>
<?php endforeach; if (!$posts) echo '<p>No blog posts yet.</p>'; ?></div></div></section>
<?php require 'includes/footer.php'; ?>
