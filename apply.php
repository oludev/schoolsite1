<?php
require_once __DIR__ . '/config.php'; require_once __DIR__ . '/includes/apply_lib.php';
[$st, $msg] = admissions_state(); $fee = app_fee(); $resumeErr = '';
if (isset($_GET['resume_ref'])) {
    $p = q('SELECT * FROM application_payments WHERE ref=? AND email=?', [trim($_GET['resume_ref']), trim($_GET['resume_email'] ?? '')])->fetch();
    if ($p && $p['status'] !== 'paid') { try { $v = verify_any($p); if ($v) mark_paid((int)$p['id'], $v[0], $v[1]); } catch (Throwable $e) {} }
    $t = $p ? q("SELECT token FROM application_payments WHERE id=? AND status='paid'", [$p['id']])->fetchColumn() : false;
    if ($t) { header('Location: ' . url('apply.php?token=' . $t)); exit; }
    $resumeErr = 'We could not find a confirmed payment with that reference and email. If you paid by bank transfer, please wait for the school to confirm it.';
}
$token = (string)($_GET['token'] ?? $_POST['token'] ?? '');
$pay = $token ? payment_by_token($token) : null;
$app = ($pay && $pay['application_id']) ? (q('SELECT * FROM applications WHERE id=?', [$pay['application_id']])->fetch() ?: null) : null;
$err = ''; $f = [];
if ($pay && !$app && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_app'])) {
    csrf_check();
    foreach ($_POST as $k => $x) $f[$k] = is_string($x) ? trim($x) : '';
    foreach (['surname', 'first_name', 'dob', 'gender', 'nationality', 'section', 'class_applied', 'parent_name', 'relationship', 'address', 'phone'] as $k) if (($f[$k] ?? '') === '') $err = 'Please fill in every field marked with a star.';
    if (!$err && (!strtotime($f['dob']) || $f['dob'] > date('Y-m-d'))) $err = 'Please enter a valid date of birth.';
    if (!$err && !in_array($f['class_applied'], CLASSES, true)) $err = 'Please choose a class.';
    if (!$err && empty($f['signed_name'])) $err = 'Please type your full name next to your signature.';
    if (!$err && empty($f['agree'])) $err = 'Please tick the declaration box.';
    $photo = $err ? null : save_passport('passport'); if (!$err && !$photo) $err = 'Please upload a passport photograph (JPG or PNG, up to 2 MB).';
    $sig = $err ? null : save_signature($_POST['signature'] ?? ''); if (!$err && !$sig) $err = 'Please sign in the signature box.';
    if (!$err) {
        $ref = 'ADM' . date('y') . strtoupper(bin2hex(random_bytes(3)));
        $child = trim($f['first_name'] . ' ' . ($f['other_names'] ?? '') . ' ' . $f['surname']);
        q('INSERT INTO applications (ref_no,child_name,dob,gender,section,class_applied,previous_school,parent_name,phone,email,address,surname,first_name,other_names,nationality,state_origin,religion,previous_class,blood_group,genotype,medical_notes,passport_photo,relationship,occupation,alt_phone,emergency_name,emergency_phone,signature_img,signed_name,signed_on,payment_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,CURDATE(),?)',
          [$ref, $child, $f['dob'], $f['gender'], $f['section'], $f['class_applied'], $f['previous_school'] ?? '', $f['parent_name'], $f['phone'], $pay['email'], $f['address'], $f['surname'], $f['first_name'], $f['other_names'] ?? '', $f['nationality'], $f['state_origin'] ?? '', $f['religion'] ?? '', $f['previous_class'] ?? '', $f['blood_group'] ?? '', $f['genotype'] ?? '', mb_substr($f['medical_notes'] ?? '', 0, 300), $photo, $f['relationship'], $f['occupation'] ?? '', $f['alt_phone'] ?? '', $f['emergency_name'] ?? '', $f['emergency_phone'] ?? '', $sig, $f['signed_name'], $pay['id']]);
        $aid = (int)db()->lastInsertId(); q('UPDATE application_payments SET application_id=? WHERE id=?', [$aid, $pay['id']]);
        $app = q('SELECT * FROM applications WHERE id=?', [$aid])->fetch(); $pay['application_id'] = $aid; audit('application_submitted', $ref);
        send_mail($pay['email'], 'Application received: ' . $ref, mail_wrap('<h2 style="margin:0 0 10px;color:#102A43">Application received</h2><p>Dear ' . e($pay['parent_name']) . ',</p><p>We have received the admission application for <strong>' . e($child) . '</strong>. Your application number is <strong>' . e($ref) . '</strong>. The school will contact you about the next step.</p>'
            . mail_button(abs_url('application-print.php?token=' . $token . '&format=print'), 'Open or print your application form')));
    }
}
function apply_area($pay, $app, $err, $f, $st, $msg, $fee, $resumeErr): string {
    ob_start();
    $tk = $pay['token'] ?? '';
    $links = '<div class="acts"><a class="btn gold" target="_blank" href="' . e(url('application-print.php?token=' . $tk . '&format=print')) . '">Print / Save as PDF</a><a class="btn ghost" href="' . e(url('application-print.php?token=' . $tk . '&format=doc')) . '">Download Word (.doc)</a></div>';
    if ($pay && $app): ?>
      <div class="apply-card"><div class="note ok"><strong>Application submitted.</strong> Your application number is <strong><?= e($app['ref_no']) ?></strong>. We have emailed a confirmation to <?= e($pay['email']) ?>.</div>
        <h2>Your application form</h2><p>Keep a copy of the completed form. Print it, or save it as a PDF from the print window.</p><?= $links ?>
        <p class="mini" style="margin-top:16px">The school will contact you about the entrance assessment. Please quote your application number in every message.</p></div>
    <?php elseif ($pay):
        $v = fn($k, $d = '') => e($f[$k] ?? $d);
        $o = function ($k, $list, $d = '') use ($f) { $s = $f[$k] ?? $d; $h = '<option value="">Choose</option>'; foreach ($list as $x) $h .= '<option' . ($x === $s ? ' selected' : '') . '>' . e($x) . '</option>'; return $h; }; ?>
      <div class="apply-card"><div class="note ok"><strong>Payment confirmed.</strong> Thank you. Please complete the form below. A link to this page was also sent to <?= e($pay['email']) ?>.</div>
        <h2>Admission application form</h2>
        <p class="mini">Fields marked * are required. You can also download a blank copy to fill in by hand:</p><?= $links ?>
        <?php if ($err) echo '<div class="note err" style="margin-top:16px">' . e($err) . '</div>'; ?>
        <form method="post" enctype="multipart/form-data" class="afm" id="app-form"><?= csrf_field() ?><input type="hidden" name="token" value="<?= e($tk) ?>"><input type="hidden" name="submit_app" value="1"><input type="hidden" name="signature" id="sigdata">
          <h3>A. Student's details</h3>
          <div class="row"><div><label>Surname *</label><input name="surname" value="<?= $v('surname') ?>" required></div><div><label>First name *</label><input name="first_name" value="<?= $v('first_name') ?>" required></div></div>
          <div class="row"><div><label>Other names</label><input name="other_names" value="<?= $v('other_names') ?>"></div><div><label>Date of birth *</label><input type="date" name="dob" value="<?= $v('dob') ?>" max="<?= date('Y-m-d') ?>" required></div></div>
          <div class="row"><div><label>Gender *</label><select name="gender" required><?= $o('gender', ['Female', 'Male']) ?></select></div><div><label>Nationality *</label><input name="nationality" value="<?= $v('nationality', 'Nigerian') ?>" required></div></div>
          <div class="row"><div><label>State of origin</label><input name="state_origin" value="<?= $v('state_origin') ?>"></div><div><label>Religion</label><select name="religion"><?= $o('religion', ['Christianity', 'Islam', 'Other']) ?></select></div></div>
          <div class="row"><div><label>Section *</label><select name="section" required><?= $o('section', ['Primary', 'Secondary']) ?></select></div><div><label>Class applying for *</label><select name="class_applied" required><?= $o('class_applied', CLASSES) ?></select></div></div>
          <div class="row"><div><label>Previous school</label><input name="previous_school" value="<?= $v('previous_school') ?>"></div><div><label>Last class attended</label><input name="previous_class" value="<?= $v('previous_class') ?>"></div></div>
          <div class="row"><div><label>Blood group</label><select name="blood_group"><?= $o('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']) ?></select></div><div><label>Genotype</label><select name="genotype"><?= $o('genotype', ['AA', 'AS', 'SS', 'AC', 'SC']) ?></select></div></div>
          <div><label>Medical conditions or allergies</label><textarea name="medical_notes" maxlength="300"><?= $v('medical_notes') ?></textarea></div>
          <div class="pp"><div><label>Passport photograph * (JPG or PNG, up to 2 MB)</label><input type="file" name="passport" id="passport" accept="image/jpeg,image/png" required></div><img id="pp-prev" alt="Passport photograph preview" hidden></div>
          <h3>B. Parent or guardian</h3>
          <div class="row"><div><label>Full name *</label><input name="parent_name" value="<?= $v('parent_name', $pay['parent_name']) ?>" required></div><div><label>Relationship to student *</label><select name="relationship" required><?= $o('relationship', ['Father', 'Mother', 'Guardian', 'Other']) ?></select></div></div>
          <div class="row"><div><label>Occupation</label><input name="occupation" value="<?= $v('occupation') ?>"></div><div><label>Telephone *</label><input type="tel" name="phone" value="<?= $v('phone', $pay['phone']) ?>" required></div></div>
          <div><label>Home address *</label><textarea name="address" required><?= $v('address') ?></textarea></div>
          <div class="row"><div><label>Alternative telephone</label><input type="tel" name="alt_phone" value="<?= $v('alt_phone') ?>"></div><div><label>Email address</label><input value="<?= e($pay['email']) ?>" readonly></div></div>
          <div class="row"><div><label>Emergency contact name</label><input name="emergency_name" value="<?= $v('emergency_name') ?>"></div><div><label>Emergency contact phone</label><input type="tel" name="emergency_phone" value="<?= $v('emergency_phone') ?>"></div></div>
          <h3>C. Declaration and signature</h3>
          <label class="chk"><input type="checkbox" name="agree" value="1" required> I declare that the information on this form is true and complete, and I agree to follow the rules of the school if a place is offered.</label>
          <div><label>Parent or guardian's signature * (draw with your mouse or finger)</label><canvas id="sigpad" width="640" height="180" aria-label="Signature box"></canvas><button type="button" class="btn small ghost" id="sig-clear">Clear signature</button></div>
          <div class="row"><div><label>Full name (as signature) *</label><input name="signed_name" value="<?= $v('signed_name') ?>" required></div><div><label>Date</label><input value="<?= date('j F Y') ?>" readonly></div></div>
          <p class="mini">The headteacher and principal sign the printed copy when the school processes your application.</p>
          <div><button class="btn gold" type="submit">Submit application</button></div>
        </form></div>
    <?php elseif ($st !== 'open'): ?>
      <div class="apply-card"><h2>Applications are closed right now</h2><p><?= e($msg) ?></p><a class="btn" href="<?= url('contact.php') ?>">Contact admissions</a></div>
    <?php else: ?>
      <div class="apply-card"><h2>Start your application</h2><p><?= e($msg) ?> Pay the application fee online to receive the admission form.</p>
        <div class="fee-box"><span>Application fee</span><strong><?= $fee > 0 ? e(money($fee)) : 'No fee' ?></strong></div>
        <form id="start-form" class="f" style="max-width:none">
          <div><label>Parent or guardian's full name</label><input name="parent_name" required autocomplete="name"></div>
          <div class="row"><div><label>Email address</label><input type="email" name="email" required autocomplete="email"></div><div><label>Phone number</label><input type="tel" name="phone" required autocomplete="tel"></div></div>
          <p class="mini">Your payment confirmation and a link to the form will be emailed to you.</p>
          <p class="note err" id="start-err" hidden></p>
          <div><button class="btn gold" type="submit"><?= $fee > 0 ? 'Pay application fee' : 'Get the application form' ?></button></div>
        </form>
        <details class="resume"><summary>Already paid? Continue your application</summary>
          <?php if ($resumeErr) echo '<div class="note err">' . e($resumeErr) . '</div>'; ?>
          <form method="get" class="f" style="max-width:none"><div class="row"><div><label>Payment reference</label><input name="resume_ref" placeholder="APPXXXXXXXXXX" required></div><div><label>Email used</label><input type="email" name="resume_email" required></div></div><div><button class="btn small">Continue</button></div></form></details>
      </div>
    <?php endif;
    return ob_get_clean();
}
if (isset($_GET['fragment'])) { echo apply_area($pay, $app, $err, $f, $st, $msg, $fee, $resumeErr); exit; }
$title = 'Apply now'; require 'includes/header.php'; $cfg = pay_config(); ?>
<div class="pagehead"><div class="wrap"><h1>Apply now</h1><p><?= e($msg) ?></p></div></div>
<section class="s"><div class="wrap apply-grid">
  <div id="apply-area"><?= apply_area($pay, $app, $err, $f, $st, $msg, $fee, $resumeErr) ?></div>
  <aside class="apply-pic">
    <div class="squircle ap-glass"><div class="squircle ap-photo"><img src="<?= e(site_image('apply_photo', 'https://images.pexels.com/photos/5905932/pexels-photo-5905932.jpeg?auto=compress&cs=tinysrgb&w=900')) ?>" alt="A student at the school" loading="lazy" onerror="this.style.display='none'"></div></div>
    <ol class="steps"><li>Pay the application fee</li><li>Get a confirmation email</li><li>Fill in the form and add a passport photo</li><li>Download your copy</li></ol>
  </aside>
</div></section>
<div class="pay-modal" id="pay-modal" hidden role="dialog" aria-modal="true" aria-labelledby="pay-title"><div class="pay-box">
  <button type="button" class="pay-x" aria-label="Close">&times;</button>
  <h2 id="pay-title">Pay application fee</h2>
  <p class="pay-sum">Amount <strong class="amt"></strong> &middot; Reference <strong id="pay-ref"></strong></p>
  <div class="pay-tabs" role="tablist"><button type="button" class="on" data-tab="bank">Bank transfer</button><button type="button" data-tab="paystack">Card / Paystack</button><button type="button" data-tab="flw">Flutterwave</button><button type="button" data-tab="ussd">USSD</button></div>
  <div class="pay-panel" data-panel="bank">
    <p>Transfer exactly <strong class="amt"></strong> to this account:</p>
    <dl class="bank-dl"><dt>Bank</dt><dd id="b-bank"></dd><dt>Account name</dt><dd id="b-name"></dd><dt>Account number</dt><dd id="b-num"></dd><dt>Narration</dt><dd id="b-ref"></dd></dl>
    <p class="mini">Put the reference in the transfer narration so we can match your payment.</p>
    <div id="bank-form" class="f" style="max-width:none"><div><label for="b-sender">Name on the account you paid from</label><input id="b-sender"></div><div><button type="button" class="btn gold" id="b-done">I have made the transfer</button></div></div>
    <p id="b-wait" class="note ok" hidden>Thank you. We are waiting for the bank to confirm your transfer. Keep this window open and the form will appear as soon as it is confirmed, or use the link we email you.</p>
  </div>
  <div class="pay-panel" data-panel="paystack" hidden><p>Pay with a debit card or your bank using Paystack. A secure Paystack window will open.</p><button type="button" class="btn gold" id="ps-btn">Pay with Paystack</button></div>
  <div class="pay-panel" data-panel="flw" hidden><p>Pay with a card, bank transfer or USSD using Flutterwave. A secure Flutterwave window will open.</p><button type="button" class="btn gold" id="fw-btn">Pay with Flutterwave</button></div>
  <div class="pay-panel" data-panel="ussd" hidden><p id="ussd-note"></p><div class="acts"><button type="button" class="btn gold" id="ussd-ps">Pay with USSD (Paystack)</button><button type="button" class="btn ghost" id="ussd-fw">Pay with USSD (Flutterwave)</button></div></div>
  <p class="pay-msg" id="pay-msg" role="status"></p>
</div></div>
<script>window.APPLY = <?= json_encode(['csrf' => csrf(), 'school' => setting('school_name', 'School'), 'paystack' => $cfg['paystack'], 'flw' => $cfg['flw'], 'bank' => $cfg['bank'], 'ussd_note' => $cfg['ussd_note'],
  'urls' => ['start' => url('pay_start.php'), 'verify' => url('pay_verify.php'), 'bank' => url('pay_bank.php'), 'status' => url('pay_status.php'), 'page' => url('apply.php')]], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="<?= url('assets/apply.js') ?>"></script>
<?php require 'includes/footer.php'; ?>
