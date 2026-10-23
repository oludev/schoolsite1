<?php require_once __DIR__ . '/../config.php';
$title = $title ?? 'Home'; $here = basename($_SERVER['SCRIPT_NAME']);
$name = setting('school_name', 'Wineridge Academy');
$groups = [
  ['About', 'about.php', ['about.php' => 'About us', 'school-info.php' => 'School information', 'faq.php' => 'FAQ']],
  ['Academics', 'academics.php', ['academics.php' => 'Primary and secondary', 'exam-records.php' => 'Exam records', 'activities.php' => 'Clubs and activities']],
  ['Admissions', 'admissions.php', ['admissions.php' => 'How to apply', 'apply.php' => 'Apply now']],
  ['News & media', 'news.php', ['news.php' => 'News and events', 'blog.php' => 'Blog', 'gallery.php' => 'Gallery']],
];
$logo = site_logo(); /* upload it in Admin > Logo & images */
$ico = fn($d) => '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
$phone = setting('phone', '+234 800 000 0000');
?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> | <?= e($name) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
<?php if ($logo): ?><link rel="icon" href="<?= e($logo) ?>"><?php endif; ?>
<link rel="stylesheet" href="<?= url('assets/style.css') ?>"></head><body>
<a class="skip" href="#main">Skip to content</a>
<div class="utility"><div class="wrap">
  <span class="u-hide"><?= $ico('<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>') ?><?= e(setting('address', '12 School Road, Ikorodu, Lagos')) ?></span>
  <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>"><?= $ico('<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>') ?><?= e($phone) ?></a>
  <a class="u-hide" href="mailto:<?= e(setting('email', 'info@wineridge.edu.ng')) ?>"><?= $ico('<path d="M3 6h18v12H3z"/><path d="M3 7l9 7 9-7"/>') ?><?= e(setting('email', 'info@wineridge.edu.ng')) ?></a>
  <span class="u-right"><a href="<?= url('login.php') ?>"><?= $ico('<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>') ?>Log in</a></span>
</div></div>
<header class="top"><div class="wrap bar">
  <a class="brand" href="<?= url('index.php') ?>"><?= brand_mark($name) ?><span class="b-txt"><b><?= e($name) ?></b><small><?= e(setting('motto', 'Knowledge, character and service.')) ?></small></span></a>
  <button class="burger" aria-label="Menu" onclick="document.body.classList.toggle('nav-open')">Menu</button>
  <nav class="mnav" aria-label="Main">
    <?php foreach ($groups as [$label, $href, $items]): ?>
      <div class="m-item has-sub"><a href="<?= url($href) ?>" <?= isset($items[$here]) ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
        <div class="sub"><?php foreach ($items as $f => $l): ?><a href="<?= url($f) ?>" <?= $here === $f ? 'aria-current="page"' : '' ?>><?= e($l) ?></a><?php endforeach; ?></div></div>
    <?php endforeach; ?>
    <div class="m-item"><a href="<?= url('contact.php') ?>" <?= $here === 'contact.php' ? 'aria-current="page"' : '' ?>>Contact</a></div>
    <a class="btn gold small" href="<?= url('apply.php') ?>">Apply now</a>
  </nav></div></header>
<main id="main">
