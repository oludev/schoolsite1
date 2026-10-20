<?php require_once __DIR__ . '/../config.php';
$title = $title ?? 'Home'; $here = basename($_SERVER['SCRIPT_NAME']);
$name = setting('school_name', 'Wineridge Academy');
$nav = ['index.php'=>'Home','academics.php'=>'Academics','admissions.php'=>'Admissions',
  'activities.php'=>'Activities','news.php'=>'News & events','gallery.php'=>'Gallery','contact.php'=>'Contact'];
?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> | <?= e($name) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/style.css') ?>"></head><body>
<a class="skip" href="#main">Skip to content</a>
<header class="top"><div class="wrap bar">
  <a class="brand" href="<?= url('index.php') ?>"><span class="crest"><?= e(mb_substr($name, 0, 1)) ?></span><span><?= e($name) ?></span></a>
  <button class="burger" aria-label="Menu" onclick="document.body.classList.toggle('nav-open')">Menu</button>
  <nav class="nav" aria-label="Main">
    <?php foreach ($nav as $f => $l): ?><a href="<?= url($f) ?>" <?= $here === $f ? 'aria-current="page"' : '' ?>><?= e($l) ?></a><?php endforeach; ?>
    <a class="btn small ghost" href="<?= url('portal/login.php') ?>">E-portal</a>
    <a class="btn small" href="<?= url('apply.php') ?>">Apply now</a>
  </nav></div></header>
<main id="main">
