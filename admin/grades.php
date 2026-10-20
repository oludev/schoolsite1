<?php require '_menu.php'; require_perm('grades'); [$sid, $cl, $pick] = picker(); app_top('Grade book', $menu, $area); ?>
<h1>Grade book</h1><?= $pick ?><?php if ($sid && $cl) echo '<div class="panel">' . gradebook_html($cl, $sid) . '</div>'; app_bottom();
