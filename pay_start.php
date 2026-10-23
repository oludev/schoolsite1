<?php require_once __DIR__ . '/includes/apply_lib.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'Invalid request.'], 405);
if (!csrf_ok()) json_out(['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.'], 419);
[$st] = admissions_state(); if ($st !== 'open') json_out(['ok' => false, 'error' => 'Applications are not open right now.']);
$name = trim($_POST['parent_name'] ?? ''); $email = trim($_POST['email'] ?? ''); $phone = trim($_POST['phone'] ?? '');
if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen(preg_replace('/\D/', '', $phone)) < 7) json_out(['ok' => false, 'error' => 'Please enter your name, a valid email address and a phone number.']);
$fee = app_fee(); $ref = 'APP' . strtoupper(bin2hex(random_bytes(5)));
q('INSERT INTO application_payments (ref,parent_name,email,phone,amount) VALUES (?,?,?,?,?)', [$ref, $name, $email, $phone, $fee]); $id = (int)db()->lastInsertId();
$_SESSION['pay_ref'] = $ref;
if ($fee <= 0) { $t = mark_paid($id, 'free', 'free-' . $id); $_SESSION['apply_token'] = $t; json_out(['ok' => true, 'free' => true, 'token' => $t]); }
json_out(['ok' => true, 'ref' => $ref, 'amount' => $fee, 'amount_text' => money($fee), 'name' => $name, 'email' => $email, 'phone' => $phone]);
