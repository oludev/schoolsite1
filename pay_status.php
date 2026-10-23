<?php require_once __DIR__ . '/includes/apply_lib.php';
$ref = trim($_GET['ref'] ?? ''); $p = payment_by_ref($ref);
if (!$p || ($_SESSION['pay_ref'] ?? '') !== $ref) json_out(['status' => 'unknown']);
json_out($p['status'] === 'paid' ? ['status' => 'paid', 'token' => $p['token']] : ['status' => $p['status']]);
