<?php require '_menu.php'; require_perm('settings');
$keys = ['school_name' => 'School name', 'motto' => 'Motto', 'address' => 'Address', 'phone' => 'Phone', 'email' => 'Email', 'hero_title' => 'Homepage headline', 'hero_text' => 'Homepage intro',
  'about_story' => 'Our story (About page)', 'mission' => 'Mission', 'vision' => 'Vision', 'stat_ratio' => 'Homepage fact: teacher ratio', 'stat_years' => 'Homepage fact: years of teaching', 'stat_pass' => 'Homepage fact: pass rate', 'ca_max' => 'Highest continuous assessment (CA) score. Default 40', 'exam_max' => 'Highest exam score. Default 60'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    if (isset($_POST['pw'])) {
        $me = q('SELECT password_hash FROM users WHERE id=?', [$u['id']])->fetchColumn();
        if (!password_verify($_POST['old'] ?? '', $me)) flash('Your current password is wrong.', 'err');
        elseif (strlen($_POST['new'] ?? '') < 8) flash('Use at least 8 characters for the new password.', 'err');
        else { q('UPDATE users SET password_hash=? WHERE id=?', [password_hash($_POST['new'], PASSWORD_DEFAULT), $u['id']]); audit('password_changed'); flash('Password changed.'); }
    } else {
        $o = $_POST['apply_open'] ?? ''; $c = $_POST['apply_close'] ?? '';
        if ($o && $c && $c < $o) { flash('The closing date must be after the opening date.', 'err'); go('admin/settings.php'); }
        audit('settings_saved'); save_setting('apply_open', $o); save_setting('apply_close', $c);
        foreach ($keys as $k => $l) save_setting($k, trim($_POST[$k] ?? ''));
        flash('Settings saved.');
    }
    go('admin/settings.php'); }
app_top('Settings', $menu, $area); ?>
<h1>Settings</h1>
<form method="post" class="panel f" style="max-width:none"><?= csrf_field() ?><h3>Admission window</h3><p style="color:var(--mute)">The Apply now form only works between these two dates (inclusive).</p>
<div class="row"><div><label>Opens on</label><input type="date" name="apply_open" value="<?= e(setting('apply_open')) ?>"></div><div><label>Closes on</label><input type="date" name="apply_close" value="<?= e(setting('apply_close')) ?>"></div></div>
<h3>School details and homepage text</h3>
<?php foreach ($keys as $k => $l): $long = in_array($k, ['hero_text', 'about_story', 'mission', 'vision']); ?><div><label><?= e($l) ?></label><?= $long ? '<textarea name="' . $k . '" rows="3">' . e(setting($k)) . '</textarea>' : '<input name="' . $k . '" value="' . e(setting($k)) . '">' ?></div><?php endforeach; ?>
<div><button class="btn">Save settings</button></div></form>
<form method="post" class="panel f"><?= csrf_field() ?><input type="hidden" name="pw" value="1"><h3>Change my password</h3>
<div><label>Current password</label><input type="password" name="old" required></div><div><label>New password</label><input type="password" name="new" minlength="8" required></div><div><button class="btn">Change password</button></div></form>
<?php app_bottom();
