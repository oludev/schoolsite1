<?php require '_menu.php'; require_perm('payroll'); audit('payslip_view', $_GET['id'] ?? ''); app_top('Payslip', $menu, $area); echo payslip_html((int)($_GET['id'] ?? 0)); app_bottom();
