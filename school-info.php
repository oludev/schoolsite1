<?php $title = 'School information'; require 'includes/header.php'; ?>
<div class="pagehead"><div class="wrap"><h1>School information</h1><p>Everything parents and pupils need to know day to day.</p></div></div>
<section class="s"><div class="wrap"><div class="grid">
 <div class="panel"><h3>School hours</h3><p>Monday to Friday<br>Primary: 8:00 am to 2:30 pm<br>Secondary: 7:45 am to 3:30 pm</p></div>
 <div class="panel"><h3>Terms</h3><p>Three terms each year: September to December, January to April, and May to July. Dates are posted under News & events.</p></div>
 <div class="panel"><h3>Uniform</h3><p>Wine and white uniform on school days, sports wear on Fridays. Uniforms are available from the school store.</p></div>
 <div class="panel"><h3>School fees</h3><p>Fees depend on class level. Contact the bursar for the current fee schedule and payment options.</p></div>
 <div class="panel"><h3>Health and safety</h3><p>A trained nurse is on site, and parents are called immediately if a child is unwell.</p></div>
 <div class="panel"><h3>Contact the office</h3><p><?= e(setting('address', '12 School Road, Ikorodu, Lagos')) ?><br><?= e(setting('phone', '+234 800 000 0000')) ?></p></div>
</div></div></section>
<?php require 'includes/footer.php'; ?>
