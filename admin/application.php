<?php require_once __DIR__ . '/../config.php'; require_once __DIR__ . '/../includes/app.php'; require_once __DIR__ . '/../includes/apply_lib.php';
$u = require_role(['admin', 'principal', 'hr'], 'admin/login.php'); require_perm('applications');
$a = q('SELECT * FROM applications WHERE id=?', [(int)($_GET['id'] ?? 0)])->fetch(); if (!$a) { http_response_code(404); exit('Application not found.'); }
$pay = $a['payment_id'] ? (q('SELECT * FROM application_payments WHERE id=?', [$a['payment_id']])->fetch() ?: null) : null;
audit('application_viewed', $a['ref_no']); echo application_doc_html($pay, $a, 'print');
