<?php require __DIR__ . '/_menu.php'; require_perm('settings');
$slots = [
  'logo_file' => ['School logo', 'Shown in the website header and footer, the login page, the admin and portal menus, the printed application form and the browser tab. A PNG with a transparent background works best.'],
  'apply_photo' => ['Apply now page photo', 'The student photo beside the application form. A square or portrait photo of a student works best.'],
  'login_bg' => ['Login page background', 'The full-screen photo behind the login form. A wide, bright photo works best.'],
];
function remove_old_image(string $key): void { $old = basename(setting($key)); if ($old !== '' && preg_match('/^[a-f0-9]{16}\.(jpg|jpeg|png|webp|gif)$/', $old)) @unlink(__DIR__ . '/../uploads/' . $old); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    if (isset($_POST['remove']) && isset($slots[$_POST['remove']])) { $k = $_POST['remove']; remove_old_image($k); save_setting($k, ''); audit('image_removed', $k); flash($slots[$k][0] . ' removed. The default is used again.'); }
    elseif (isset($_POST['slot']) && isset($slots[$_POST['slot']])) {
        $k = $_POST['slot']; $new = upload_image('file');
        if (!$new) flash('Choose a JPG, PNG, WEBP or GIF image up to 4 MB.', 'err');
        elseif (!@getimagesize(__DIR__ . '/../uploads/' . $new)) { @unlink(__DIR__ . '/../uploads/' . $new); flash('That file is not a valid image.', 'err'); }
        else { remove_old_image($k); save_setting($k, $new); audit('image_uploaded', $k); flash($slots[$k][0] . ' updated.'); }
    }
    go('admin/branding.php'); }
app_top('Logo & images', $menu, $area); ?>
<h1>Logo & images</h1><p class="mini">Upload an image and it replaces the current one everywhere it is used. Use Remove to go back to the default.</p>
<?php foreach ($slots as $k => [$label, $help]): $cur = site_image($k); ?>
<div class="panel"><div class="split" style="align-items:center"><div><h3><?= e($label) ?></h3><p class="mini"><?= e($help) ?></p>
  <form method="post" enctype="multipart/form-data" class="f" style="max-width:none"><?= csrf_field() ?><input type="hidden" name="slot" value="<?= e($k) ?>">
    <div><input type="file" name="file" accept="image/png,image/jpeg,image/webp,image/gif" required></div><div class="acts"><button class="btn small">Upload and replace</button><?php if ($cur): ?><button class="btn small danger" name="remove" value="<?= e($k) ?>" formnovalidate onclick="return confirm('Remove this image and use the default?')">Remove</button><?php endif; ?></div></form></div>
  <div style="text-align:center;background:var(--navy-50);border-radius:12px;padding:16px"><?php if ($cur): ?><img src="<?= e($cur) ?>" alt="Current <?= e($label) ?>" style="max-height:150px;max-width:100%;margin:0 auto;border-radius:8px"><?php elseif ($k === 'logo_file' && site_logo()): ?><img src="<?= e(site_logo()) ?>" alt="Current logo" style="max-height:120px;margin:0 auto"><p class="mini" style="margin:8px 0 0">Using assets/logo.png</p><?php else: ?><p class="mini" style="margin:0">Using the default</p><?php endif; ?></div></div></div>
<?php endforeach; app_bottom();
