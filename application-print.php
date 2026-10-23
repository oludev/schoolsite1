<?php require_once __DIR__ . '/includes/apply_lib.php';
$pay = payment_by_token((string)($_GET['token'] ?? '')); if (!$pay) { http_response_code(404); exit('This link is not valid. Please use the link in your confirmation email.'); }
$app = $pay['application_id'] ? (q('SELECT * FROM applications WHERE id=?', [$pay['application_id']])->fetch() ?: null) : null;
$doc = ($_GET['format'] ?? 'print') === 'doc';
if ($doc) { header('Content-Type: application/msword; charset=utf-8'); header('Content-Disposition: attachment; filename="admission-application-form.doc"'); }
echo application_doc_html($pay, $app, $doc ? 'doc' : 'print');
