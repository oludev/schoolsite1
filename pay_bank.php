<?php require_once __DIR__ . '/includes/apply_lib.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'Invalid request.'], 405);
if (!csrf_ok()) json_out(['ok' => false, 'error' => 'Your session expired. Please refresh the page.'], 419);
$ref = trim($_POST['ref'] ?? ''); $p = payment_by_ref($ref); $sender = mb_substr(trim($_POST['sender'] ?? ''), 0, 120);
if (!$p || ($_SESSION['pay_ref'] ?? '') !== $ref || $p['status'] === 'paid') json_out(['ok' => false, 'error' => 'We could not find this payment.']);
if (!$sender) json_out(['ok' => false, 'error' => 'Please enter the name on the account you paid from.']);
q("UPDATE application_payments SET status='awaiting', method='bank_transfer', bank_sender=? WHERE id=?", [$sender, $p['id']]);
if (setting('email')) send_mail(setting('email'), 'Bank transfer to confirm: ' . $ref, mail_wrap('<p>' . e($p['parent_name']) . ' says they paid <strong>' . e(money($p['amount'])) . '</strong> by bank transfer (sender: ' . e($sender) . ', reference ' . e($ref) . '). Please check the bank statement and confirm in Admin &rarr; Application payments.</p>'));
audit('bank_transfer_reported', $ref); json_out(['ok' => true]);
