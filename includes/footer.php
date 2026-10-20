</main>
<footer class="foot"><div class="wrap cols">
  <div><strong class="fname"><?= e(setting('school_name', 'Wineridge Academy')) ?></strong>
    <p><?= e(setting('motto', 'Knowledge, character and service.')) ?></p>
    <p><?= e(setting('address', '12 School Road, Ikorodu, Lagos')) ?><br><?= e(setting('phone', '+234 800 000 0000')) ?> &middot; <?= e(setting('email', 'info@wineridge.edu.ng')) ?></p></div>
  <div><strong>Explore</strong><a href="<?= url('about.php') ?>">About Us</a><a href="<?= url('school-info.php') ?>">School Info</a><a href="<?= url('academics.php') ?>">Academics</a><a href="<?= url('admissions.php') ?>">Admissions</a><a href="<?= url('exam-records.php') ?>">Exam records</a><a href="<?= url('blog.php') ?>">Blog</a><a href="<?= url('faq.php') ?>">FAQ</a></div>
  <div><strong>Community</strong><a href="<?= url('activities.php') ?>">News & events</a><a href="<?= url('gallery.php') ?>">Gallery</a><a href="<?= url('portal/login.php') ?>">E-portal login</a><a href="<?= url('apply.php') ?>">Apply now</a><a href="<?= url('contact.php') ?>">Contact us</a></div>
</div><div class="wrap copy">&copy; <?= date('Y') ?> <?= e(setting('school_name', 'Wineridge Academy')) ?>. All rights reserved.</div></footer>
</body></html>
