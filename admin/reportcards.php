<?php require '_menu.php'; require_perm('reports');
ob_start(); rc_list('principal', 'admin/reportcards.php', 'reportcard.php'); $body = ob_get_clean();
app_top('Report cards', $menu, $area); ?><h1>Report cards</h1><?= $body; app_bottom();
