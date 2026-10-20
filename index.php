<?php $title = 'Home'; require 'includes/header.php';
[$st, $msg] = admissions_state();
$news = q("SELECT * FROM posts WHERE category='News' ORDER BY created_at DESC LIMIT 3")->fetchAll();
$events = q("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date LIMIT 4")->fetchAll(); ?>
<?php $px = fn($id) => 'https://images.pexels.com/photos/' . $id . '/pexels-photo-' . $id . '.jpeg?auto=compress&cs=tinysrgb&w=1800';
$slides = [
  ['img' => $px(5905569), 'alt' => 'A schoolgirl raising her hand during a lesson', 'title' => setting('hero_title', 'A school where every child is known by name.'), 'text' => setting('hero_text', 'From the first day in Primary 1 to the final paper in SS3, we teach with care, high standards and a lot of heart.'), 'b1' => ['Apply now', 'apply.php'], 'b2' => ['Meet our school', 'about.php']],
  ['img' => $px(5905932), 'alt' => 'Children writing in their notebooks in class', 'title' => 'Small classes. Big attention.', 'text' => 'Teachers who notice every pupil, from Primary 1 to SS 3, and help each one go further.', 'b1' => ['See our academics', 'academics.php'], 'b2' => ['School information', 'school-info.php']],
  ['img' => $px(5905530), 'alt' => 'A teacher asking pupils a question in class', 'title' => 'Ready for exams. Ready for life.', 'text' => 'We prepare pupils for the common entrance, WAEC and NECO, and for the world beyond the classroom.', 'b1' => ['Exam records', 'exam-records.php'], 'b2' => ['Clubs and activities', 'activities.php']],
]; ?>
<section class="banner" aria-roledescription="carousel" aria-label="School highlights">
  <div class="slides">
  <?php foreach ($slides as $i => $sl): ?>
    <div class="slide<?= $i === 0 ? ' on' : '' ?>" aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>"><img src="<?= e($sl['img']) ?>" alt="<?= e($sl['alt']) ?>" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
      <div class="wrap"><div class="copy"><?= $i === 0 ? '<h1>' : '<h2 class="big">' ?><?= e($sl['title']) ?><?= $i === 0 ? '</h1>' : '</h2>' ?><p><?= e($sl['text']) ?></p>
        <div class="btns"><a class="btn" href="<?= url($sl['b1'][1]) ?>"><?= e($sl['b1'][0]) ?></a><a class="btn ghost" href="<?= url($sl['b2'][1]) ?>"><?= e($sl['b2'][0]) ?></a></div></div></div></div>
  <?php endforeach; ?>
  </div>
  <aside class="window banner-card"><span class="pill <?= $st ?>"><?= $st === 'open' ? 'Admissions open' : ($st === 'soon' ? 'Opening soon' : 'Admissions closed') ?></span>
    <h2>Admission window</h2><p><?= e($msg) ?></p>
    <?php if (setting('apply_open')): ?><p>Opens <strong><?= date('j M Y', strtotime(setting('apply_open'))) ?></strong><br>Closes <strong><?= date('j M Y', strtotime(setting('apply_close'))) ?></strong></p><?php endif; ?>
    <a class="btn" href="<?= url($st === 'open' ? 'apply.php' : 'admissions.php') ?>"><?= $st === 'open' ? 'Start your application' : 'See admission details' ?></a></aside>
</section>
<script>
(function () {
  var slides = document.querySelectorAll('.banner .slide');
  if (slides.length < 2) return;
  var i = 0, timer = null, b = document.querySelector('.banner');
  function show(n) {
    slides[i].classList.remove('on'); slides[i].setAttribute('aria-hidden', 'true');
    i = n; slides[i].classList.add('on'); slides[i].setAttribute('aria-hidden', 'false');
  }
  function start() { if (!timer) timer = setInterval(function () { show((i + 1) % slides.length); }, 6000); }
  function stop() { clearInterval(timer); timer = null; }
  b.addEventListener('focusin', stop); b.addEventListener('focusout', start);
  document.addEventListener('visibilitychange', function () { document.hidden ? stop() : start(); });
  start();
})();
</script>
<?php $ph = q("SELECT * FROM gallery WHERE album='Home Page' ORDER BY id DESC LIMIT 4")->fetchAll();
$icons = ['book' => '<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z M4 19V5 M8 7h7 M8 11h7"/>', 'calc' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 7h8 M8 12h2 M14 12h2 M8 16h2 M14 16h2"/>', 'flask' => '<path d="M9 3h6 M10 3v6l-5 9a2 2 0 0 0 2 3h10a2 2 0 0 0 2-3l-5-9V3 M8 15h8"/>', 'heart' => '<path d="M12 20s-8-5-8-11a4.5 4.5 0 0 1 8-2.5A4.5 4.5 0 0 1 20 9c0 6-8 11-8 11z"/>'];
$learn = [['Reading opens every door', 'Children who read for ten minutes a day grow stronger in every subject.', 'book'],
  ['Numbers build thinking', 'Mathematics teaches us to reason step by step and to solve real problems.', 'calc'],
  ['Science starts with a question', 'We help pupils ask why, test their ideas and learn from their mistakes.', 'flask'],
  ['Character is taught daily', 'Honesty, kindness and teamwork are practised in class and on the field.', 'heart']]; ?>
<section class="s"><div class="wrap"><h2>Learning that lasts a lifetime</h2><p>Our girls and boys learn to read well, think clearly and treat others with respect.</p>
<div class="grid learn-grid"><?php foreach ($learn as $i => [$t, $d, $ic]): $x = $ph[$i] ?? null; ?>
<figure class="learn"><?php if ($x): ?><img src="<?= img($x['image']) ?>" alt="<?= e($x['caption'] ?: $t) ?>" loading="lazy"><?php else: ?><div class="ph"><svg viewBox="0 0 24 24" width="84" height="84" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $icons[$ic] ?></svg></div><?php endif; ?>
<figcaption><h3><?= e($t) ?></h3><p><?= e($d) ?></p></figcaption></figure><?php endforeach; ?></div></div></section>
<?php $board = visible_notices(null, 3); if ($board): ?><section class="s"><div class="wrap"><h2>Notice board</h2><?= notice_cards($board) ?></div></section><?php endif; ?>
<section class="s"><div class="wrap"><div class="facts">
  <div class="fact"><b>Primary 1 to SS3</b>One campus, one community</div><div class="fact"><b><?= e(setting('stat_ratio', '1 : 20')) ?></b>Teacher to pupil ratio</div>
  <div class="fact"><b><?= e(setting('stat_years', '25+')) ?></b>Years of teaching</div><div class="fact"><b><?= e(setting('stat_pass', '98%')) ?></b>Pass rate in external exams</div></div></div></section>
<section class="s tint"><div class="wrap split">
  <div><h2>Latest news</h2><?php foreach ($news as $n): ?>
    <div class="date"><div><h3 style="margin:0"><a href="<?= url('post.php?id=' . $n['id']) ?>"><?= e($n['title']) ?></a></h3><p style="margin:0;color:var(--mute)"><?= e($n['excerpt']) ?></p></div></div>
  <?php endforeach; if (!$news) echo '<p>No news yet. Check back soon.</p>'; ?><p><a href="<?= url('news.php') ?>">All news and events</a></p></div>
  <div><h2>Coming up</h2><?php foreach ($events as $ev): $t = strtotime($ev['event_date']); ?>
    <div class="date"><div class="d"><b><?= date('d', $t) ?></b><?= date('M', $t) ?></div><div><strong><?= e($ev['title']) ?></strong><br><span style="color:var(--mute)"><?= e($ev['location']) ?></span></div></div>
  <?php endforeach; if (!$events) echo '<p>No upcoming events.</p>'; ?></div></div></section>
<?php require 'includes/footer.php'; ?>