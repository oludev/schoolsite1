<?php require '_menu.php'; require_perm('reports'); $stu = (int)($_GET['student'] ?? 0); $sid = (int)($_GET['session'] ?? 0);
audit('report_card_view', "student $stu session $sid"); app_top('Report card', $menu, $area); echo report_card($stu, $sid); app_bottom();
