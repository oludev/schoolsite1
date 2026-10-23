<?php require __DIR__ . '/_menu.php'; require_once __DIR__ . '/../includes/apply_lib.php'; require_perm('applications');
$keys = ['pay_bank_name', 'pay_account_name', 'pay_account_no', 'pay_paystack_public', 'pay_flw_public', 'pay_ussd_note', 'smtp_host', 'smtp_port', 'smtp_secure', 'smtp_user', 'mail_from', 'mail_from_name'];
$secrets = ['pay_paystack_secret', 'pay_flw_secret', 'smtp_pass'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    if (isset($_POST['savecfg']) && can('settings')) {
        save_setting('apply_fee', (string)max(0, (float)($_POST['apply_fee'] ?? 0)));
        foreach ($keys as $k) save_setting($k, trim($_POST[$k] ?? ''));
        foreach ($secrets as $k) if (trim($_POST[$k] ?? '') !== '') save_setting($k, trim($_POST[$k]));
        audit('payment_settings_saved'); flash('Payment settings saved.');
    } elseif (isset($_POST['confirm'])) {
        $p = q('SELECT * FROM application_payments WHERE id=?', [(int)$_POST['confirm']])->fetch();
        if ($p && in_array($p['status'], ['pending', 'awaiting'], true)) { mark_paid((int)$p['id'], 'bank_transfer', 'manual-' . $p['id']); audit('payment_confirmed', $p['ref']); flash('Payment confirmed. The parent has been emailed the link to the form.'); }
    } elseif (isset($_POST['resend'])) {
        $p = q("SELECT * FROM application_payments WHERE id=? AND status='paid'", [(int)$_POST['resend']])->fetch();
        if ($p) { q('UPDATE application_payments SET emailed=0 WHERE id=?', [$p['id']]); mark_paid((int)$p['id'], (string)$p['method'], (string)$p['provider_ref']); flash('Email sent again. See the email log below to check it went through.'); }
    } elseif (isset($_POST['testmail'])) {
        $to = trim($_POST['testto'] ?? ''); $ok = send_mail($to, 'Test email from ' . setting('school_name', 'the school'), mail_wrap('<p>This is a test. If you can read it, your email settings work.</p>'));
        flash($ok ? "Test email sent to $to." : 'The test email failed. See the email log below for the reason.', $ok ? 'ok' : 'err');
    }
    go('admin/payments.php'); }
$rows = q('SELECT * FROM application_payments ORDER BY id DESC LIMIT 100')->fetchAll();
$log = q('SELECT * FROM email_log ORDER BY id DESC LIMIT 10')->fetchAll();
$s = fn($k) => e(setting($k)); $set = fn($k) => setting($k) !== '' ? ' (saved. Leave blank to keep it)' : '';
app_top('Application payments', $menu, $area); ?>
<h1>Application payments</h1>
<?php if (can('settings')): ?>
<form method="post" class="panel f" style="max-width:none"><?= csrf_field() ?><input type="hidden" name="savecfg" value="1">
  <h3>Application fee</h3>
  <div style="max-width:300px"><label>Fee in naira (enter 0 for a free application)</label><input type="number" step="0.01" min="0" name="apply_fee" value="<?= $s('apply_fee') ?>"></div>
  <h3>Bank transfer details (shown to parents)</h3>
  <div class="row"><div><label>Bank</label><input name="pay_bank_name" value="<?= $s('pay_bank_name') ?>"></div><div><label>Account name</label><input name="pay_account_name" value="<?= $s('pay_account_name') ?>"></div></div>
  <div style="max-width:300px"><label>Account number</label><input name="pay_account_no" value="<?= $s('pay_account_no') ?>"></div>
  <h3>Paystack</h3><p class="mini">Find these in your Paystack dashboard under Settings, API keys. Use the test keys first.</p>
  <div class="row"><div><label>Public key</label><input name="pay_paystack_public" value="<?= $s('pay_paystack_public') ?>" placeholder="pk_..."></div><div><label>Secret key<?= $set('pay_paystack_secret') ?></label><input type="password" name="pay_paystack_secret" autocomplete="new-password" placeholder="sk_..."></div></div>
  <h3>Flutterwave</h3>
  <div class="row"><div><label>Public key</label><input name="pay_flw_public" value="<?= $s('pay_flw_public') ?>" placeholder="FLWPUBK..."></div><div><label>Secret key<?= $set('pay_flw_secret') ?></label><input type="password" name="pay_flw_secret" autocomplete="new-password" placeholder="FLWSECK..."></div></div>
  <h3>USSD</h3><div><label>Instructions shown to parents</label><textarea name="pay_ussd_note" rows="2"><?= $s('pay_ussd_note') ?></textarea></div>
  <h3>Email (for confirmation messages)</h3><p class="mini">Use your school's email account. For Gmail use smtp.gmail.com, port 587, TLS and an app password. If you leave the server blank, PHP's built-in mail is tried, which many hosts do not support.</p>
  <div class="row"><div><label>SMTP server</label><input name="smtp_host" value="<?= $s('smtp_host') ?>" placeholder="smtp.example.com"></div><div><label>Port</label><input name="smtp_port" value="<?= $s('smtp_port') ?>" placeholder="587"></div></div>
  <div class="row"><div><label>Security</label><select name="smtp_secure"><?= options(['tls' => 'TLS (port 587)', 'ssl' => 'SSL (port 465)'], setting('smtp_secure') ?: 'tls') ?></select></div><div><label>Username</label><input name="smtp_user" value="<?= $s('smtp_user') ?>"></div></div>
  <div class="row"><div><label>Password<?= $set('smtp_pass') ?></label><input type="password" name="smtp_pass" autocomplete="new-password"></div><div><label>Send from (email)</label><input type="email" name="mail_from" value="<?= $s('mail_from') ?>"></div></div>
  <div style="max-width:420px"><label>Sender name</label><input name="mail_from_name" value="<?= $s('mail_from_name') ?>" placeholder="<?= $s('school_name') ?>"></div>
  <div><button class="btn">Save payment settings</button></div></form>
<form method="post" class="panel f"><?= csrf_field() ?><h3>Check your email settings</h3><div class="row"><div><input type="email" name="testto" placeholder="Send a test email to..." value="<?= $s('email') ?>" required></div><div><button class="btn small ghost" name="testmail" value="1">Send test email</button></div></div></form>
<?php endif; ?>
<h2 style="font-size:1.5rem;margin-top:10px">Payments</h2>
<div class="tw scroll"><table><thead><tr><th>Date</th><th>Reference</th><th>Parent</th><th>Amount</th><th>Method</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): $cls = $r['status'] === 'paid' ? 'open' : ($r['status'] === 'awaiting' ? 'soon' : 'closed'); ?>
<tr><td><?= date('j M Y, g:i a', strtotime($r['created_at'])) ?></td><td><?= e($r['ref']) ?></td><td><?= e($r['parent_name']) ?><br><span class="mini"><?= e($r['email']) ?> &middot; <?= e($r['phone']) ?></span></td><td><?= e(money($r['amount'])) ?></td>
<td><?= e(str_replace('_', ' ', (string)$r['method'])) ?><?= $r['bank_sender'] ? '<br><span class="mini">From: ' . e($r['bank_sender']) . '</span>' : '' ?></td><td><span class="pill <?= $cls ?>"><?= e(ucfirst($r['status'])) ?></span></td>
<td><form method="post" class="acts"><?= csrf_field() ?><?php if (in_array($r['status'], ['pending', 'awaiting'], true)): ?><button class="btn small" name="confirm" value="<?= $r['id'] ?>" onclick="return confirm('Confirm that you have received this payment in the bank account?')">Confirm payment</button><?php elseif ($r['status'] === 'paid'): ?><button class="btn small ghost" name="resend" value="<?= $r['id'] ?>">Resend email</button><?php endif; ?></form></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="7">No payments yet.</td></tr>'; ?></tbody></table></div>
<h2 style="font-size:1.5rem;margin-top:28px">Email log</h2>
<div class="tw scroll"><table><thead><tr><th>When</th><th>To</th><th>Subject</th><th>Result</th></tr></thead><tbody>
<?php foreach ($log as $l) echo '<tr><td>' . date('j M, g:i a', strtotime($l['created_at'])) . '</td><td>' . e($l['to_email']) . '</td><td>' . e($l['subject']) . '</td><td>' . ($l['status'] === 'sent' ? '<span class="good">Sent</span>' : '<span class="low">Failed</span> ' . e($l['error'])) . '</td></tr>'; if (!$log) echo '<tr><td colspan="4">No emails yet.</td></tr>'; ?></tbody></table></div>
<?php app_bottom();
