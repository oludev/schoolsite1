<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/mailer.php';

function json_out(array $d, int $code = 200): void { http_response_code($code); header('Content-Type: application/json'); echo json_encode($d); exit; }
function csrf_ok(): bool { return hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? ''); }
function abs_url(string $p = ''): string { $s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'; return $s . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url($p); }
function app_fee(): float { return max(0, (float)setting('apply_fee', '0')); }
function payment_by_token(string $t): ?array { if (!preg_match('/^[a-f0-9]{40}$/', $t)) return null; return q("SELECT * FROM application_payments WHERE token=? AND status='paid'", [$t])->fetch() ?: null; }
function payment_by_ref(string $r): ?array { return q('SELECT * FROM application_payments WHERE ref=?', [$r])->fetch() ?: null; }

function mark_paid(int $id, string $method, string $providerRef): string {
    $p = q('SELECT * FROM application_payments WHERE id=?', [$id])->fetch();
    $token = $p['token'] ?: bin2hex(random_bytes(20));
    q("UPDATE application_payments SET status='paid', method=?, provider_ref=?, token=?, paid_at=COALESCE(paid_at,NOW()) WHERE id=?", [$method, $providerRef, $token, $id]);
    if (!$p['emailed']) {
        $paid = $method === 'free' ? 'There is no fee for this application.' : 'We have received your application fee of <strong>' . e(money($p['amount'])) . '</strong>.';
        $html = mail_wrap('<h2 style="margin:0 0 10px;color:#102A43">Payment confirmed</h2><p>Dear ' . e($p['parent_name']) . ',</p><p>Thank you. ' . $paid . ' Your reference is <strong>' . e($p['ref']) . '</strong>.</p>'
            . '<p>Your admission application form is ready. Use the button below to complete it online. You can also download a printable copy from that page.</p>'
            . mail_button(abs_url('apply.php?token=' . $token), 'Complete the application form') . '<p style="font-size:13px;color:#52667A">Keep this email. The link is personal to you, so please do not share it.</p>');
        if (send_mail($p['email'], 'Payment confirmed: your application form', $html)) q('UPDATE application_payments SET emailed=1 WHERE id=?', [$id]);
    }
    return $token;
}
function pay_config(): array {
    return ['paystack' => (setting('pay_paystack_public') && setting('pay_paystack_secret')) ? setting('pay_paystack_public') : '',
            'flw' => (setting('pay_flw_public') && setting('pay_flw_secret')) ? setting('pay_flw_public') : '',
            'bank' => ['bank' => setting('pay_bank_name'), 'name' => setting('pay_account_name'), 'number' => setting('pay_account_no')],
            'ussd_note' => setting('pay_ussd_note', 'Choose your bank, then dial the code shown on the next screen using the phone number linked to your bank account.')];
}
function http_json(string $url, array $headers): ?array {
    if (!function_exists('curl_init')) { $GLOBALS['http_err'] = 'The PHP cURL extension is not enabled.'; return null; }
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => array_merge($headers, ['Accept: application/json']), CURLOPT_TIMEOUT => 25, CURLOPT_CONNECTTIMEOUT => 10]);
    $out = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
    if ($out === false) { $GLOBALS['http_err'] = $err; return null; }
    $j = json_decode($out, true); return is_array($j) ? $j : null;
}
function verify_paystack(string $ref, float $amount): array {
    $sk = setting('pay_paystack_secret'); if (!$sk) return [false, 'Paystack is not set up.', ''];
    $r = http_json('https://api.paystack.co/transaction/verify/' . rawurlencode($ref), ['Authorization: Bearer ' . $sk]);
    if (!$r) { audit('pay_verify_error', 'paystack: ' . ($GLOBALS['http_err'] ?? '')); return [false, 'We could not reach Paystack to confirm the payment. Please wait a moment and try again.', '']; }
    $d = $r['data'] ?? [];
    if (($d['status'] ?? '') === 'success' && ($d['reference'] ?? '') === $ref && ($d['currency'] ?? 'NGN') === 'NGN' && (int)($d['amount'] ?? 0) >= (int)round($amount * 100)) return [true, '', 'ps-' . ($d['id'] ?? $ref)];
    return [false, 'Paystack has not confirmed this payment yet.', ''];
}
function verify_flutterwave(string $ref, string $txId, float $amount): array {
    $sk = setting('pay_flw_secret'); if (!$sk) return [false, 'Flutterwave is not set up.', ''];
    if (!ctype_digit($txId)) return [false, 'Missing Flutterwave transaction number.', ''];
    $r = http_json('https://api.flutterwave.com/v3/transactions/' . $txId . '/verify', ['Authorization: Bearer ' . $sk]);
    if (!$r) { audit('pay_verify_error', 'flutterwave: ' . ($GLOBALS['http_err'] ?? '')); return [false, 'We could not reach Flutterwave to confirm the payment. Please wait a moment and try again.', '']; }
    $d = $r['data'] ?? [];
    if (($r['status'] ?? '') === 'success' && ($d['status'] ?? '') === 'successful' && ($d['tx_ref'] ?? '') === $ref && ($d['currency'] ?? '') === 'NGN' && (float)($d['amount'] ?? 0) >= $amount) return [true, '', 'fw-' . $txId];
    return [false, 'Flutterwave has not confirmed this payment yet.', ''];
}
function save_passport(string $field): ?string {
    if (empty($_FILES[$field]['tmp_name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK || $_FILES[$field]['size'] > 2 * 1024 * 1024) return null;
    $info = @getimagesize($_FILES[$field]['tmp_name']); if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) return null;
    $name = 'pp_' . bin2hex(random_bytes(8)) . ($info[2] === IMAGETYPE_PNG ? '.png' : '.jpg');
    return move_uploaded_file($_FILES[$field]['tmp_name'], __DIR__ . '/../uploads/' . $name) ? $name : null;
}
function save_signature(string $dataUrl): ?string {
    $pre = 'data:image/png;base64,'; if (strpos($dataUrl, $pre) !== 0 || strlen($dataUrl) > 400000) return null;
    $bin = base64_decode(substr($dataUrl, strlen($pre)), true); if (!$bin || strpos($bin, "\x89PNG\r\n\x1a\n") !== 0) return null;
    $name = 'sig_' . bin2hex(random_bytes(8)) . '.png'; return file_put_contents(__DIR__ . '/../uploads/' . $name, $bin) ? $name : null;
}
require_once __DIR__ . '/apply_doc.php';

/* safety net: if a parent paid but closed the window before we confirmed it, ask the provider about this reference */
function verify_any(array $p): ?array {
    $amount = (float)$p['amount'];
    if (setting('pay_paystack_secret')) { [$ok, , $pr] = verify_paystack($p['ref'], $amount); if ($ok) return ['paystack', $pr]; }
    if (setting('pay_flw_secret')) {
        $r = http_json('https://api.flutterwave.com/v3/transactions/verify_by_reference?tx_ref=' . rawurlencode($p['ref']), ['Authorization: Bearer ' . setting('pay_flw_secret')]);
        $d = $r['data'] ?? [];
        if (($d['status'] ?? '') === 'successful' && ($d['tx_ref'] ?? '') === $p['ref'] && ($d['currency'] ?? '') === 'NGN' && (float)($d['amount'] ?? 0) >= $amount) return ['flutterwave', 'fw-' . ($d['id'] ?? $p['ref'])];
    }
    return null;
}
