<?php $title = 'Gallery'; require 'includes/header.php';
$albums = q('SELECT DISTINCT album FROM gallery ORDER BY album')->fetchAll(PDO::FETCH_COLUMN); $a = $_GET['album'] ?? '';
$rows = $a ? q('SELECT * FROM gallery WHERE album=? ORDER BY id DESC', [$a])->fetchAll() : q('SELECT * FROM gallery ORDER BY id DESC')->fetchAll(); ?>
<div class="pagehead"><div class="wrap"><h1>Gallery</h1><p>Moments from school life.</p></div></div>
<section class="s"><div class="wrap">
<div class="chips"><a href="<?= url('gallery.php') ?>" class="<?= $a ? '' : 'on' ?>">All</a><?php foreach ($albums as $al): ?><a href="<?= url('gallery.php?album=' . urlencode($al)) ?>" class="<?= $a === $al ? 'on' : '' ?>"><?= e($al) ?></a><?php endforeach; ?></div>
<div class="gal"><?php foreach ($rows as $r): ?><figure><img src="<?= img($r['image']) ?>" alt="<?= e($r['caption']) ?>" loading="lazy"><figcaption><?= e($r['caption']) ?></figcaption></figure><?php endforeach; ?></div>
<?php if (!$rows) echo '<p>No photos yet.</p>'; ?></div></section>
<?php require 'includes/footer.php'; ?>
