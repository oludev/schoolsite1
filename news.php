<?php $title = 'News & events'; require 'includes/header.php';
$news = q("SELECT * FROM posts WHERE category='News' ORDER BY created_at DESC LIMIT 12")->fetchAll();
$up = q("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date")->fetchAll();
$past = q("SELECT * FROM events WHERE event_date < CURDATE() ORDER BY event_date DESC LIMIT 5")->fetchAll(); ?>
<div class="pagehead"><div class="wrap"><h1>News & events</h1><p>What is happening around the school.</p></div></div>
<section class="s"><div class="wrap split">
 <div><h2>News</h2><div class="grid" style="grid-template-columns:1fr">
  <?php foreach ($news as $n): ?><article class="card"><?php if ($n['image']): ?><img src="<?= img($n['image']) ?>" alt=""><?php endif; ?><div class="pad"><div class="meta"><?= date('j F Y', strtotime($n['created_at'])) ?></div>
   <h3><?= e($n['title']) ?></h3><p><?= e($n['excerpt']) ?></p><a class="more" href="<?= url('post.php?id=' . $n['id']) ?>">Read more</a></div></article>
  <?php endforeach; if (!$news) echo '<p>No news yet.</p>'; ?></div></div>
 <div><h2>Upcoming events</h2><?php foreach ($up as $ev): $t = strtotime($ev['event_date']); ?>
  <div class="date"><div class="d"><b><?= date('d', $t) ?></b><?= date('M', $t) ?></div><div><strong><?= e($ev['title']) ?></strong><br><span style="color:var(--mute)"><?= e($ev['location']) ?></span><p><?= e($ev['description']) ?></p></div></div>
 <?php endforeach; if (!$up) echo '<p>No upcoming events.</p>'; ?>
 <?php if ($past): ?><h3 style="margin-top:30px">Recent events</h3><?php foreach ($past as $ev): ?><p style="margin:0 0 6px"><?= date('j M Y', strtotime($ev['event_date'])) ?>: <?= e($ev['title']) ?></p><?php endforeach; endif; ?></div></div></section>
<?php require 'includes/footer.php'; ?>
