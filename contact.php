<?php $title = 'Contact'; require 'includes/header.php'; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check(); $f = array_map('trim', $_POST);
  if (!$f['name'] || !filter_var($f['email'] ?? '', FILTER_VALIDATE_EMAIL) || !$f['body']) $err = 'Enter your name, a valid email and your message.';
  else { q('INSERT INTO messages (name,email,subject,body) VALUES (?,?,?,?)', [$f['name'], $f['email'], $f['subject'] ?? '', $f['body']]); flash('Thank you. We have received your message and will reply soon.'); go('contact.php'); } } ?>
<div class="pagehead"><div class="wrap"><h1>Contact us</h1><p>We usually reply within one working day.</p></div></div>
<section class="s"><div class="wrap split"><div><?= flash() ?><?php if ($err) echo '<div class="note err">' . e($err) . '</div>'; ?>
<form class="f" method="post"><?= csrf_field() ?><div class="row"><div><label>Your name</label><input name="name" required></div><div><label>Email</label><input type="email" name="email" required></div></div>
<div><label>Subject</label><input name="subject"></div><div><label>Message</label><textarea name="body" required></textarea></div><div><button class="btn">Send message</button></div></form></div>
<div class="panel"><h3>Visit or call</h3><p><?= e(setting('address', '12 School Road, Ikorodu, Lagos')) ?></p><p><?= e(setting('phone', '+234 800 000 0000')) ?><br><?= e(setting('email', 'info@wineridge.edu.ng')) ?></p><p>Office hours: Monday to Friday, 8:00 am to 4:00 pm.</p></div></div></section>
<?php require 'includes/footer.php'; ?>
