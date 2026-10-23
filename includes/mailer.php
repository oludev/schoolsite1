<?php
/* Sends email through your own SMTP account (set in Admin > Application payments) or, if none is set, through PHP's mail(). Every attempt is logged. */
function mail_wrap(string $inner): string {
    $n = htmlspecialchars(setting('school_name', 'Wineridge Academy'));
    return '<div style="background:#F3F6FA;padding:24px;font-family:Arial,sans-serif"><div style="max-width:560px;margin:auto;background:#fff;border-radius:12px;overflow:hidden">'
        . '<div style="background:#102A43;color:#fff;padding:18px 24px;border-bottom:4px solid #D4AF37;font-size:20px;font-weight:bold">' . $n . '</div>'
        . '<div style="padding:24px;color:#1F2D3D;line-height:1.6">' . $inner . '</div>'
        . '<div style="padding:14px 24px;background:#0B1F33;color:#C9D6E4;font-size:12px">' . htmlspecialchars(setting('address', '')) . ' &middot; ' . htmlspecialchars(setting('phone', '')) . '</div></div></div>';
}
function mail_button(string $href, string $label): string {
    return '<p><a href="' . htmlspecialchars($href) . '" style="display:inline-block;background:#D4AF37;color:#0B1F33;padding:12px 22px;border-radius:8px;text-decoration:none;font-weight:bold">' . htmlspecialchars($label) . '</a></p>';
}
function smtp_read($fp): string { $r = ''; while (($l = fgets($fp, 515)) !== false) { $r .= $l; if (strlen($l) < 4 || $l[3] === ' ') break; } return $r; }
function smtp_cmd($fp, string $cmd, array $ok): string {
    if ($cmd !== '') fwrite($fp, $cmd . "\r\n");
    $r = smtp_read($fp);
    if (!in_array((int)substr($r, 0, 3), $ok, true)) throw new Exception('SMTP said: ' . trim($r));
    return $r;
}
function mail_headers(string $to, string $subject, string $from, string $fname, bool $withTo = true): string {
    $h = 'From: =?UTF-8?B?' . base64_encode($fname) . '?= <' . $from . ">\r\n";
    if ($withTo) $h .= 'To: <' . $to . ">\r\n";
    $h .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\nDate: " . date('r') . "\r\n";
    return $h;
}
function smtp_send(string $to, string $subject, string $html, string $from, string $fname): bool {
    $host = setting('smtp_host'); $port = (int)(setting('smtp_port') ?: 587); $sec = strtolower(setting('smtp_secure') ?: 'tls');
    $fp = @stream_socket_client(($sec === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port, $en, $es, 20);
    if (!$fp) throw new Exception("Cannot connect to $host:$port ($es)");
    stream_set_timeout($fp, 20); $dom = $_SERVER['HTTP_HOST'] ?? 'localhost';
    smtp_cmd($fp, '', [220]); smtp_cmd($fp, 'EHLO ' . $dom, [250]);
    if ($sec === 'tls') { smtp_cmd($fp, 'STARTTLS', [220]); if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) throw new Exception('Could not start a secure connection.'); smtp_cmd($fp, 'EHLO ' . $dom, [250]); }
    if (setting('smtp_user') !== '') { smtp_cmd($fp, 'AUTH LOGIN', [334]); smtp_cmd($fp, base64_encode(setting('smtp_user')), [334]); smtp_cmd($fp, base64_encode(setting('smtp_pass')), [235]); }
    smtp_cmd($fp, 'MAIL FROM:<' . $from . '>', [250]); smtp_cmd($fp, 'RCPT TO:<' . $to . '>', [250, 251]); smtp_cmd($fp, 'DATA', [354]);
    fwrite($fp, mail_headers($to, $subject, $from, $fname) . "\r\n" . chunk_split(base64_encode($html)) . "\r\n.\r\n");
    smtp_cmd($fp, '', [250]); smtp_cmd($fp, 'QUIT', [221]); fclose($fp);
    return true;
}
function send_mail(string $to, string $subject, string $html): bool {
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
    $from = setting('mail_from') ?: (setting('email') ?: 'noreply@localhost'); $fname = setting('mail_from_name') ?: setting('school_name', 'School');
    $err = ''; $ok = false;
    try {
        if (setting('smtp_host')) $ok = smtp_send($to, $subject, $html, $from, $fname);
        else { $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', chunk_split(base64_encode($html)), mail_headers($to, $subject, $from, $fname, false)); if (!$ok) $err = 'PHP mail() is not set up on this server. Add SMTP details in Application payments.'; }
    } catch (Throwable $e) { $err = $e->getMessage(); }
    try { q('INSERT INTO email_log (to_email,subject,status,error) VALUES (?,?,?,?)', [$to, mb_substr($subject, 0, 200), $ok ? 'sent' : 'failed', mb_substr($err, 0, 250)]); } catch (Throwable $e) {}
    return $ok;
}
