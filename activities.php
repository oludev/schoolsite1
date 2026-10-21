<?php $title = 'Extra-curricular activities'; require 'includes/header.php'; $rows = q('SELECT * FROM activities ORDER BY name')->fetchAll();
/* Photo for each activity: the photo uploaded in Admin > Activities comes first.
   If none was uploaded, a Pexels photo is chosen by a keyword in the activity name.
   To change a photo, replace its number below with the number from any Pexels photo address. */
$px = fn($id) => 'https://images.pexels.com/photos/' . $id . '/pexels-photo-' . $id . '.jpeg?auto=compress&cs=tinysrgb&w=1100';
$keywords = ['football' => 3697601, 'soccer' => 3697601, 'sport' => 3697601, 'athletic' => 3697601, 'track' => 3697601, 'netball' => 3697601, 'basketball' => 3697601,
             'debate' => 5905569, 'speaking' => 5905569, 'quiz' => 5905569, 'coding' => 5905932, 'computer' => 5905932, 'robot' => 5905932];
$spare = [5905530, 5905937, 5905932, 5905569];
$photo = function ($r, $i) use ($px, $keywords, $spare) {
    if ($r['image']) return img($r['image']);
    foreach ($keywords as $k => $id) if (stripos($r['name'], $k) !== false) return $px($id);
    return $px($spare[$i % count($spare)]);
}; ?>
<script>document.documentElement.classList.add('js-reveal');</script>
<div class="pagehead"><div class="wrap"><h1>Extra-curricular activities</h1><p>Learning does not stop at the classroom door.</p></div></div>
<?php foreach ($rows as $i => $r): ?>
<section class="act g<?= $i % 3 + 1 ?><?= $i % 2 ? ' flip' : '' ?>">
  <span class="act-n" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
  <div class="wrap act-in">
    <div class="act-text reveal">
      <h2><?= e($r['name']) ?></h2>
      <p><?= e($r['description']) ?></p>
      <a class="btn" href="<?= url('contact.php') ?>">Ask about joining</a>
    </div>
    <div class="act-media reveal" style="transition-delay:.15s">
      <span class="squircle act-blob b1"></span><span class="squircle act-blob b2"></span>
      <div class="squircle act-glass"><div class="squircle act-pic"><img src="<?= e($photo($r, $i)) ?>" alt="<?= e($r['name']) ?>" loading="<?= $i ? 'lazy' : 'eager' ?>"></div></div>
    </div>
  </div>
</section>
<?php endforeach; ?>
<?php if (!$rows): ?><section class="s"><div class="wrap"><p>Activities will be listed soon.</p></div></section><?php endif; ?>
<script>
(function () {
  var els = document.querySelectorAll('.reveal');
  if (!('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('in'); }); return; }
  var io = new IntersectionObserver(function (en) { en.forEach(function (x) { if (x.isIntersecting) { x.target.classList.add('in'); io.unobserve(x.target); } }); }, { threshold: 0.15 });
  els.forEach(function (e) { io.observe(e); });
})();
</script>
<?php require 'includes/footer.php'; ?>
