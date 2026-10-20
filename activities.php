<?php $title = 'Extra-curricular activities'; require 'includes/header.php'; $rows = q('SELECT * FROM activities ORDER BY name')->fetchAll(); ?>
<div class="pagehead"><div class="wrap"><h1>Extra-curricular activities</h1><p>Learning does not stop at the classroom door.</p></div></div>
<section class="s"><div class="wrap"><div class="grid">
<?php foreach ($rows as $r): ?><article class="card"><?php if ($r['image']): ?><img src="<?= img($r['image']) ?>" alt=""><?php endif; ?><div class="pad"><h3><?= e($r['name']) ?></h3><p><?= e($r['description']) ?></p></div></article>
<?php endforeach; if (!$rows) echo '<p>Activities will be listed soon.</p>'; ?></div></div></section>
<?php require 'includes/footer.php'; ?>
