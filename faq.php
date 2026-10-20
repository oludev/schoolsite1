<?php $title = 'FAQ'; require 'includes/header.php'; $rows = q('SELECT * FROM faqs ORDER BY sort_order, id')->fetchAll(); ?>
<div class="pagehead"><div class="wrap"><h1>Frequently asked questions</h1><p>Quick answers for parents and visitors.</p></div></div>
<section class="s"><div class="wrap" style="max-width:820px">
<?php foreach ($rows as $r): ?><details><summary><?= e($r['question']) ?></summary><p style="margin-top:10px"><?= nl2br(e($r['answer'])) ?></p></details>
<?php endforeach; if (!$rows) echo '<p>No questions yet.</p>'; ?>
<p style="margin-top:24px">Still have a question? <a href="<?= url('contact.php') ?>">Send us a message</a>.</p></div></section>
<?php require 'includes/footer.php'; ?>
