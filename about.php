<?php $title = 'About us'; require 'includes/header.php'; ?>
<div class="pagehead"><div class="wrap"><h1>About us</h1><p><?= e(setting('motto', 'Knowledge, character and service.')) ?></p></div></div>
<section class="s"><div class="wrap split">
  <div><h2>Our story</h2><p><?= nl2br(e(setting('about_story', 'Founded over two decades ago, we began with one classroom and a belief that good teaching changes lives. Today we serve families from nursery through senior secondary, with the same belief at the centre of everything we do.'))) ?></p>
    <h2>Our mission</h2><p><?= e(setting('mission', 'To give every learner a strong academic foundation, sound character and the confidence to lead.')) ?></p>
    <h2>Our vision</h2><p><?= e(setting('vision', 'Graduates who think clearly, act kindly and serve their communities well.')) ?></p></div>
  <div class="panel"><h3>Our core values</h3><p><strong>Integrity.</strong> We do the right thing when nobody is watching.</p><p><strong>Excellence.</strong> We expect effort and celebrate progress.</p><p><strong>Respect.</strong> We listen to each other and value every voice.</p><p><strong>Service.</strong> We use what we learn to help others.</p></div></div></section>
<?php require 'includes/footer.php'; ?>
