<?php require '_menu.php'; if ($u['role'] !== 'teacher') go('portal/index.php');
ob_start(); rc_list('teacher', 'portal/reportcards.php', 'reportcard.php'); $body = ob_get_clean();
app_top('Report cards', $menu, $area); ?><h1>Report cards</h1><?= $body; app_bottom();
