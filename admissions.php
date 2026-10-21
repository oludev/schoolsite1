<?php $title = 'Admissions'; require 'includes/header.php'; [$st, $msg] = admissions_state(); ?>
<div class="pagehead"><div class="wrap"><h1>Admissions</h1><p>How to join our school.</p></div></div>
<section class="s"><div class="wrap split">
 <div><h2>How it works</h2><p><strong>1. Apply online</strong> while the window is open.</p><p><strong>2. Entrance assessment.</strong> We invite shortlisted children for a short assessment and a friendly chat.</p><p><strong>3. Offer.</strong> Successful applicants receive an offer and fee details by phone and email.</p><p><strong>4. Enrol.</strong> Pay fees and complete registration to secure the place.</p>
  <h3>Documents to prepare</h3><p>Birth certificate, two passport photographs, last school report, and immunisation record.</p></div>
 <div class="window panel" style="border-top:8px solid var(--wine)"><span class="pill <?= $st ?>"><?= $st === 'open' ? 'Open' : ($st === 'soon' ? 'Opening soon' : 'Closed') ?></span>
  <h2>Admission window</h2><p><?= e($msg) ?></p>
  <?php if (setting('apply_open')): ?><p>Opens: <strong><?= date('j F Y', strtotime(setting('apply_open'))) ?></strong><br>Closes: <strong><?= date('j F Y', strtotime(setting('apply_close'))) ?></strong></p><?php endif; ?>
  <?php if ($st === 'open'): ?><a class="btn" href="<?= url('apply.php') ?>">Apply now</a><?php else: ?><a class="btn ghost" href="<?= url('contact.php') ?>">Ask about the next intake</a><?php endif; ?></div></div></section>
    <?php require 'includes/footer.php'; ?>
