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
<div class="content"><?= admin_topbar() ?><?= flash() ?>
<?php }
function app_bottom(): void { echo '</div></div></body></html>'; }

function do_login(array $roles, $dest, string $heading, string $hint): void {
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
                $_SESSION['last'] = time(); audit('login'); go(is_array($dest) ? ($dest[$row['role']] ?? 'index.php') : $dest);
            }
            q('INSERT INTO login_attempts (username,ip) VALUES (?,?)', [$un, $ip]); audit('login_failed', $un); $err = 'Wrong username or password.';
        }
    }
    $name = setting('school_name', 'Wineridge Academy'); ?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($heading) ?> | <?= e($name) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/style.css') ?>"></head>
<body class="login-bg" style="--bg:url('https://images.pexels.com/photos/5905569/pexels-photo-5905569.jpeg?auto=compress&amp;cs=tinysrgb&amp;w=1920')">
<main class="lg">
  <div class="glass-card">
    <a class="brand" href="<?= url('index.php') ?>"><span class="crest"><?= e(mb_substr($name, 0, 1)) ?></span><span><?= e($name) ?></span></a>
    <h1><?= e($heading) ?></h1><?= flash() ?>
    <?php if ($err) echo '<div class="note err">' . e($err) . '</div>'; ?>
    <form class="f" method="post"><?= csrf_field() ?>
      <div><label for="u">Username</label><input id="u" name="username" autocomplete="username" required autofocus></div>
      <div><label for="p">Password</label><input id="p" type="password" name="password" autocomplete="current-password" required></div>
      <button class="btn" type="submit">Log in</button>
    </form>
  </div>
  <div class="squircle lg-sq"><p><?= e($hint) ?></p></div>
</main></body></html>
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
            go(in_array($u['role'], ['teacher', 'student', 'parent'], true) ? 'portal/index.php' : 'admin/index.php');
        }
    }
    app_top('Change password', $menu, $area); ?>
<h1>Change password</h1><?php if ($u['must_change']) echo '<div class="note err">You must choose a new password before you continue.</div>'; if ($err) echo '<div class="note err">' . e($err) . '</div>'; ?>
<form method="post" class="panel f"><?= csrf_field() ?><div><label>Current password</label><input type="password" name="old" required autocomplete="current-password"></div>
<div><label>New password (8 characters or more)</label><input type="password" name="new" minlength="8" required autocomplete="new-password"></div>
<div><label>Repeat new password</label><input type="password" name="again" required autocomplete="new-password"></div><div><button class="btn">Change password</button></div></form>
<?php app_bottom(); }

function admin_topbar(): string {
    $u = user(); if (!$u || !in_array($u['role'], ['admin', 'principal', 'hr'], true)) return '';
    $items = []; $total = 0;
    try {
        $add = function ($perm, $sql, $one, $many, $link) use (&$items, &$total) {
            if (!can($perm)) return; $n = (int)q($sql)->fetchColumn();
            if ($n) { $items[] = [$n . ' ' . ($n === 1 ? $one : $many), $link]; $total += $n; }
        };
        $add('applications', "SELECT COUNT(*) FROM applications WHERE status='New'", 'new application', 'new applications', 'applications.php');
        $add('content', 'SELECT COUNT(*) FROM messages WHERE is_read=0', 'unread message', 'unread messages', 'messages.php');
        $add('leave_admin', "SELECT COUNT(*) FROM leave_requests WHERE status='Pending'", 'leave request waiting', 'leave requests waiting', 'leave.php');
    } catch (Throwable $e) {}
    $h = '<div class="topbar noprint"><div class="notif"><button type="button" class="bell" id="bell" aria-label="Notifications" aria-expanded="false" aria-controls="notif-menu">'
       . '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>'
       . ($total ? '<span class="count">' . ($total > 99 ? '99+' : $total) . '</span>' : '') . '</button>'
       . '<div class="notif-menu" id="notif-menu" hidden><strong>Notifications</strong>';
    if ($items) foreach ($items as $i) $h .= '<a href="' . e($i[1]) . '">' . e($i[0]) . '</a>'; else $h .= '<p>You are all caught up.</p>';
    return $h . '</div></div><span class="tb-user">' . e($u['full_name']) . '</span><a class="btn small" href="logout.php">Log out</a></div>'
       . "<script>(function(){var b=document.getElementById('bell'),m=document.getElementById('notif-menu');if(!b)return;"
       . "b.addEventListener('click',function(e){e.stopPropagation();var o=m.hidden;m.hidden=!o;b.setAttribute('aria-expanded',o?'true':'false');});"
       . "document.addEventListener('click',function(e){if(!m.hidden&&!m.contains(e.target)){m.hidden=true;b.setAttribute('aria-expanded','false');}});"
       . "document.addEventListener('keydown',function(e){if(e.key==='Escape'){m.hidden=true;b.setAttribute('aria-expanded','false');}});})();</script>";
}
