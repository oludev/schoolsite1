<?php require '_menu.php'; if ($u['role'] !== 'teacher') go('portal/index.php');
[$sid, $cl, $pick] = picker(); app_top('Grade book', $menu, $area); ?>
<h1>Grade book</h1><p class="mini">Every score for a class in one place. Click a student's name to see their progress across exams.</p><?= $pick ?>
<?php if ($sid && $cl) echo '<div class="panel">' . gradebook_html($cl, $sid, 'tracking.php?student=') . '</div>'; app_bottom();
