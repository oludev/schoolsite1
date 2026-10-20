<?php
function app_top(string $title, array $menu, string $area): void {
    $u = user(); $here = basename($_SERVER['SCRIPT_NAME']) . (isset($_GET['t']) ? '?t=' . $_GET['t'] : '');
    $name = setting('school_name', 'Wineridge Academy'); ?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> | <?= e($area) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/style.css') ?>"></head><body>
<div class="app"><aside class="side"><a class="brand" href="<?= url('index.php') ?>"><span class="crest"><?= e(mb_substr($name, 0, 1)) ?></span><span><?= e($area) ?></span></a>
<?php foreach ($menu as $href => $label): ?><a href="<?= e($href) ?>" class="<?= $here === $href ? 'on' : '' ?>"><?= e($label) ?></a><?php endforeach; ?>
<a href="<?= url('index.php') ?>">View website</a>
<div class="who"><?= e($u['full_name']) ?><br><span class="mini" style="color:#D9A9B3"><?= e(ucfirst($u['role'])) ?></span><br><a href="logout.php" style="padding:0">Log out</a></div></aside>
<div class="content"><?= flash() ?>
<?php }
function app_bottom(): void { echo '</div></div></body></html>'; }

function do_login(array $roles, string $dest, string $heading, string $hint): void {
    $err = ''; $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check(); $un = trim($_POST['username'] ?? '');
        $fails = (int)q('SELECT COUNT(*) FROM login_attempts WHERE username=? AND created_at > (NOW() - INTERVAL 15 MINUTE)', [$un])->fetchColumn();
        if ($fails >= 6) $err = 'Too many failed attempts for this username. Wait 15 minutes and try again.';
        else {
            $row = q('SELECT * FROM users WHERE username=? AND active=1', [$un])->fetch();
            if ($row && in_array($row['role'], $roles, true) && password_verify($_POST['password'] ?? '', $row['password_hash'])) {
                session_regenerate_id(true); q('DELETE FROM login_attempts WHERE username=?', [$un]);
                $_SESSION['user'] = ['id' => $row['id'], 'role' => $row['role'], 'full_name' => $row['full_name'], 'username' => $row['username'], 'class_name' => $row['class_name'], 'must_change' => (int)$row['must_change']];
                $_SESSION['last'] = time(); audit('login'); go($dest);
            }
            q('INSERT INTO login_attempts (username,ip) VALUES (?,?)', [$un, $ip]); audit('login_failed', $un); $err = 'Wrong username or password.';
        }
    }
    $name = setting('school_name', 'Wineridge Academy'); ?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Log in | <?= e($name) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/style.css') ?>"></head><body>
<div class="login"><div class="box"><a class="brand" href="<?= url('index.php') ?>" style="margin-bottom:18px"><span class="crest"><?= e(mb_substr($name, 0, 1)) ?></span><span><?= e($name) ?></span></a>
<h1 style="font-size:1.8rem"><?= e($heading) ?></h1><p style="color:var(--mute)"><?= e($hint) ?></p><?= flash() ?>
<?php if ($err) echo '<div class="note err">' . e($err) . '</div>'; ?>
<form class="f" method="post"><?= csrf_field() ?><div><label for="u">Username</label><input id="u" name="username" autocomplete="username" required autofocus></div>
<div><label for="p">Password</label><input id="p" type="password" name="password" autocomplete="current-password" required></div>
<button class="btn" type="submit">Log in</button></form>
<p style="margin:18px 0 0"><a href="<?= url('index.php') ?>">Back to website</a></p></div></div></body></html>
<?php exit; }

function password_page(array $menu, string $area): void {
    $u = user(); $err = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check(); $cur = q('SELECT password_hash FROM users WHERE id=?', [$u['id']])->fetchColumn();
        if (!password_verify($_POST['old'] ?? '', $cur)) $err = 'Your current password is wrong.';
        elseif (strlen($_POST['new'] ?? '') < 8) $err = 'Use at least 8 characters.';
        elseif (($_POST['new'] ?? '') !== ($_POST['again'] ?? '')) $err = 'The two new passwords do not match.';
        elseif (($_POST['new'] ?? '') === ($_POST['old'] ?? '')) $err = 'Choose a password different from the old one.';
        else {
            q('UPDATE users SET password_hash=?, must_change=0 WHERE id=?', [password_hash($_POST['new'], PASSWORD_DEFAULT), $u['id']]);
            $_SESSION['user']['must_change'] = 0; audit('password_changed'); flash('Password changed.');
            go(in_array($u['role'], ['teacher', 'student'], true) ? 'portal/index.php' : 'admin/index.php');
        }
    }
    app_top('Change password', $menu, $area); ?>
<h1>Change password</h1><?php if ($u['must_change']) echo '<div class="note err">You must choose a new password before you continue.</div>'; if ($err) echo '<div class="note err">' . e($err) . '</div>'; ?>
<form method="post" class="panel f"><?= csrf_field() ?><div><label>Current password</label><input type="password" name="old" required autocomplete="current-password"></div>
<div><label>New password (8 characters or more)</label><input type="password" name="new" minlength="8" required autocomplete="new-password"></div>
<div><label>Repeat new password</label><input type="password" name="again" required autocomplete="new-password"></div><div><button class="btn">Change password</button></div></form>
<?php app_bottom(); }
