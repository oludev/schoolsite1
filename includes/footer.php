</main>
<?php $fname = setting('school_name', 'Wineridge Academy'); $fi = fn($d) => '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>'; ?>
<footer class="foot"><div class="wrap fcols">
  <div class="f-brand"><a class="brand" href="<?= url('index.php') ?>"><?= brand_mark($fname) ?><span class="b-txt"><b><?= e($fname) ?></b></span></a>
    <p><?= e(setting('motto', 'Knowledge, character and service.')) ?></p>
    <div class="f-line"><?= $fi('<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>') ?><span><?= e(setting('address', '12 School Road, Ikorodu, Lagos')) ?></span></div>
    <div class="f-line"><?= $fi('<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>') ?><span><?= e(setting('phone', '+234 800 000 0000')) ?></span></div>
    <div class="f-line"><?= $fi('<path d="M3 6h18v12H3z"/><path d="M3 7l9 7 9-7"/>') ?><span><?= e(setting('email', 'info@wineridge.edu.ng')) ?></span></div></div>
  <div><strong>Explore</strong><a href="<?= url('about.php') ?>">About us</a><a href="<?= url('academics.php') ?>">Academics</a><a href="<?= url('admissions.php') ?>">Admissions</a><a href="<?= url('activities.php') ?>">Clubs and activities</a><a href="<?= url('gallery.php') ?>">Gallery</a></div>
  <div><strong>Parents and students</strong><a href="<?= url('login.php') ?>">Log in</a><a href="<?= url('exam-records.php') ?>">Exam records</a><a href="<?= url('news.php') ?>">News and events</a><a href="<?= url('faq.php') ?>">FAQ</a><a href="<?= url('apply.php') ?>">Apply now</a></div>
  <div><strong>Visit us</strong><p>Office hours<br>Monday to Friday, 8:00 am to 4:00 pm</p><a class="btn gold small" href="<?= url('contact.php') ?>">Book a visit</a></div>
</div><div class="wrap f-copy">&copy; <?= date('Y') ?> <?= e($fname) ?>. All rights reserved.</div></footer>
<script>
/* pauses animations that are scrolled out of view, so scrolling stays smooth */
(function () {
  if (!('IntersectionObserver' in window)) return;
  var io = new IntersectionObserver(function (en) { en.forEach(function (x) { x.target.classList.toggle('is-off', !x.isIntersecting); }); }, { rootMargin: '120px' });
  document.querySelectorAll('.sqt,.fx,.w-pic,.act-media,.t-deco').forEach(function (e) { io.observe(e); });
})();
</script>
</body></html>
