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
<?php $icons = [
  'book' => '<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z M4 19V5 M8 7h7 M8 11h7"/>',
  'calc' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 7h8 M8 12h2 M14 12h2 M8 16h2 M14 16h2"/>',
  'flask' => '<path d="M9 3h6 M10 3v6l-5 9a2 2 0 0 0 2 3h10a2 2 0 0 0 2-3l-5-9V3 M8 15h8"/>',
  'heart' => '<path d="M12 20s-8-5-8-11a4.5 4.5 0 0 1 8-2.5A4.5 4.5 0 0 1 20 9c0 6-8 11-8 11z"/>',
  'palette' => '<path d="M12 3a9 9 0 1 0 0 18c1.4 0 2-1 1.5-2.2-.5-1.2.2-2.3 1.5-2.3H17a4 4 0 0 0 4-4C21 6.6 17 3 12 3z"/><circle cx="7.5" cy="11" r="1"/><circle cx="10.5" cy="7.5" r="1"/><circle cx="15" cy="8" r="1"/>',
  'ball' => '<circle cx="12" cy="12" r="9"/><path d="M12 3v18 M3 12h18 M5.6 5.6c3 3 3 10.8 0 12.8 M18.4 5.6c-3 3-3 10.8 0 12.8"/>',
  'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0 M16 5a3 3 0 0 1 0 6 M18 20a6 6 0 0 0-3-5"/>',
  'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
  'award' => '<circle cx="12" cy="9" r="5"/><path d="M8.5 13.5L7 21l5-3 5 3-1.5-7.5"/>'];
$svg = fn($k, $n = 30) => '<svg viewBox="0 0 24 24" width="' . $n . '" height="' . $n . '" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[$k] . '</svg>';
$facts = [['book', (string)count(CLASSES), 'Classes, from Nursery 1 to SS 3'], ['users', setting('stat_ratio', '1'), 'Students'],
          ['clock', setting('stat_years', '25+'), 'Years of teaching'], ['award', setting('stat_pass', '98%'), 'Pass rate in external exams']]; ?>
<script>document.documentElement.classList.add('js-reveal');</script>
<section class="fx-band"><div class="wrap"><div class="fx-grid">
<?php foreach ($facts as $k => [$ic, $val, $label]): $val = trim($val); $anim = preg_match('/^(\d+(?:\.\d+)?)(\D*)$/', $val, $m); ?>
  <div class="fx" style="--d:<?= $k * 0.12 ?>s"><span class="fx-chip"><?= $svg($ic, 32) ?></span>
    <b class="fx-n"<?= $anim ? ' data-count="' . e($m[1]) . '" data-suffix="' . e($m[2]) . '"' : '' ?>><?= e($val) ?></b><span class="fx-l"><?= e($label) ?></span></div>
<?php endforeach; ?></div></div></section>
<?php $wpic = 'https://images.pexels.com/photos/5905937/pexels-photo-5905937.jpeg?auto=compress&cs=tinysrgb&w=1000'; ?>
<section class="s welcome"><div class="wrap">
  <div class="w-text reveal">
    <h2>Where curiosity meets care</h2>
    <p class="lead">We combine strong teaching with warm, personal attention, so every child feels known, challenged and proud of what they can do.</p>
    <ul class="ticks">
      <li>Caring teachers who know every pupil by name</li>
      <li>A broad curriculum from Primary 1 to SS 3, built on reading, mathematics and science</li>
      <li>Clubs, sports and the arts that build confidence and teamwork</li>
      <li>Clear communication with parents through our e-portal</li>
    </ul>
    <div class="acts"><a class="btn" href="<?= url('about.php') ?>">Discover our school</a><a class="btn ghost" href="<?= url('contact.php') ?>">Book a visit</a></div>
  </div>
  <div class="w-pic reveal" style="transition-delay:.15s">
    <div class="squircle w-frame"><img src="<?= e($wpic) ?>" alt="A teacher and pupils learning together in class" loading="lazy"></div>
    <div class="badge b1"><b><?= e(setting('stat_years', '25+')) ?></b><span>Years of teaching</span></div>
    <div class="badge b2"><b><?= e(setting('stat_age', '5-16')) ?></b><span>Target age group</span></div>
  </div>
</div></section>
<?php $ph = q("SELECT * FROM gallery WHERE album='Home Page' ORDER BY id DESC LIMIT 6")->fetchAll();
$learn = [['Reading opens every door', 'Ten minutes of reading a day grows every subject.', 'book'],
  ['Numbers build thinking', 'Mathematics teaches us to reason and solve real problems.', 'calc'],
  ['Science starts with a question', 'We help pupils ask why and test their ideas.', 'flask'],
  ['Creativity finds its voice', 'Art, music and drama give every child a way to shine.', 'palette'],
  ['Play builds strong minds', 'Sport teaches teamwork, effort and fair play.', 'ball'],
  ['Character is taught daily', 'Honesty and kindness are practised in class and on the field.', 'heart']];
$cols = ['c1', 'c4', 'c2', 'c3', 'c4', 'c1']; ?>
<section class="s"><div class="wrap"><h2 class="reveal">Learning that lasts a lifetime</h2><p class="reveal">Our girls and boys learn to read well, think clearly and treat others with respect.</p>
<div class="sq-grid"><?php foreach ($learn as $i => [$t, $d, $ic]): $x = $ph[$i] ?? null; ?>
  <div class="squircle sqt <?= $cols[$i] ?><?= $x ? ' has-img' : '' ?>" style="--d:<?= $i * 0.12 ?>s">
    <?php if ($x): ?><img src="<?= img($x['image']) ?>" alt="<?= e($x['caption'] ?: $t) ?>" loading="lazy"><?php else: ?><span class="sq-ic"><?= $svg($ic, 56) ?></span><?php endif; ?>
    <div class="sq-txt"><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div></div>
<?php endforeach; ?></div></div></section>
<?php $board = visible_notices(null, 3); if ($board): ?><section class="s"><div class="wrap"><h2>Notice board</h2><?= notice_cards($board) ?></div></section><?php endif; ?>
<?php try { $tm = q('SELECT * FROM testimonials ORDER BY id DESC LIMIT 8')->fetchAll(); } catch (Throwable $x) { $tm = []; }
if (!$tm) $tm = [ /* SAMPLE wording, shown only until real testimonials are added in Admin > Testimonials */
  ['parent_name' => 'A parent', 'role_line' => 'Parent of a Primary 3 pupil', 'quote' => 'Our daughter looks forward to school every morning. Her reading has grown so much, and her teachers always keep us informed.', 'image' => ''],
  ['parent_name' => 'A parent', 'role_line' => 'Parent of a JSS 2 student', 'quote' => 'The teachers know our son by name and push him to do his best. The e-portal makes it easy to follow his results.', 'image' => ''],
  ['parent_name' => 'A parent', 'role_line' => 'Parent of an SS 1 student', 'quote' => 'The clubs and sports have built his confidence. We are glad we chose this school for him.', 'image' => ''],
]; ?>
<section class="s testi"><span class="squircle t-deco d1"></span><span class="squircle t-deco d2"></span><div class="wrap">
  <h2 class="reveal">What parents say</h2><p class="reveal" style="margin-inline:auto;color:#EBD3D8">Families trust us with their children every day.</p>
  <div class="tcar" role="region" aria-roledescription="carousel" aria-label="Parent testimonials">
    <button class="tnav prev" type="button" aria-label="Previous testimonial">&#8249;</button>
    <div class="tslides"><?php foreach ($tm as $k => $t): ?>
      <figure class="tslide<?= $k === 0 ? ' on' : '' ?>" aria-hidden="<?= $k === 0 ? 'false' : 'true' ?>">
        <div class="t-photo"><?php if ($t['image']): ?><img src="<?= img($t['image']) ?>" alt="<?= e($t['parent_name']) ?>"><?php else: echo e(mb_strtoupper(mb_substr($t['parent_name'], 0, 1))); endif; ?></div>
        <blockquote><?= e($t['quote']) ?></blockquote>
        <figcaption><b><?= e($t['parent_name']) ?></b><span><?= e($t['role_line']) ?></span></figcaption></figure>
    <?php endforeach; ?></div>
    <button class="tnav next" type="button" aria-label="Next testimonial">&#8250;</button>
  </div>
</div></section>
<section class="s tint"><div class="wrap split">
  <div><h2>Latest news</h2><?php foreach ($news as $n): ?>
    <div class="date"><div><h3 style="margin:0"><a href="<?= url('post.php?id=' . $n['id']) ?>"><?= e($n['title']) ?></a></h3><p style="margin:0;color:var(--mute)"><?= e($n['excerpt']) ?></p></div></div>
  <?php endforeach; if (!$news) echo '<p>No news yet. Check back soon.</p>'; ?><p><a href="<?= url('news.php') ?>">All news and events</a></p></div>
  <div><h2>Coming up</h2><?php foreach ($events as $ev): $t = strtotime($ev['event_date']); ?>
    <div class="date"><div class="d"><b><?= date('d', $t) ?></b><?= date('M', $t) ?></div><div><strong><?= e($ev['title']) ?></strong><br><span style="color:var(--mute)"><?= e($ev['location']) ?></span></div></div>
  <?php endforeach; if (!$events) echo '<p>No upcoming events.</p>'; ?></div></div></section>
<script>
(function () {
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  function count(el) {
    var to = parseFloat(el.dataset.count), suf = el.dataset.suffix || '', dec = (el.dataset.count.split('.')[1] || '').length, t0 = null;
    el.textContent = (0).toFixed(dec) + suf;
    (function step(t) { if (!t0) t0 = t; var p = Math.min((t - t0) / 1800, 1), e = 1 - Math.pow(1 - p, 3); el.textContent = (to * e).toFixed(dec) + suf; if (p < 1) requestAnimationFrame(step); })(performance.now());
  }
  function show(e) { e.classList.add('in'); if (!reduce) e.querySelectorAll('[data-count]').forEach(count); }
  var els = document.querySelectorAll('.reveal,.fx,.sqt');
  if (!('IntersectionObserver' in window)) els.forEach(show);
  else { var io = new IntersectionObserver(function (en) { en.forEach(function (x) { if (x.isIntersecting) { show(x.target); io.unobserve(x.target); } }); }, { threshold: 0.2 }); els.forEach(function (e) { io.observe(e); }); }
  var car = document.querySelector('.tcar'); if (!car) return;
  var s = car.querySelectorAll('.tslide'), i = 0, timer = null;
  if (s.length < 2) { car.querySelectorAll('.tnav').forEach(function (b) { b.style.display = 'none'; }); return; }
  function go(n) { s[i].classList.remove('on'); s[i].setAttribute('aria-hidden', 'true'); i = (n + s.length) % s.length; s[i].classList.add('on'); s[i].setAttribute('aria-hidden', 'false'); }
  function start() { if (!timer) timer = setInterval(function () { go(i + 1); }, 7000); }
  function stop() { clearInterval(timer); timer = null; }
  car.querySelector('.prev').addEventListener('click', function () { go(i - 1); stop(); start(); });
  car.querySelector('.next').addEventListener('click', function () { go(i + 1); stop(); start(); });
  car.addEventListener('focusin', stop); car.addEventListener('focusout', start); start();
})();
</script>
<?php require 'includes/footer.php'; ?>