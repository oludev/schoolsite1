<?php
// For sites already running the first version. Run once in the browser, then DELETE this file.
require __DIR__ . '/config.php';
try {
    $pdo = db();
    foreach (array_filter(array_map('trim', explode(';', file_get_contents(__DIR__ . '/schema.sql')))) as $sql) $pdo->exec($sql);
    if (!$pdo->query("SHOW COLUMNS FROM users LIKE 'must_change'")->fetch()) $pdo->exec('ALTER TABLE users ADD must_change TINYINT(1) NOT NULL DEFAULT 0');
    if (!$pdo->query("SHOW COLUMNS FROM results LIKE 'class_name'")->fetch()) $pdo->exec('ALTER TABLE results ADD class_name VARCHAR(40) NULL');
    foreach (['ca', 'exam'] as $c) if (!$pdo->query("SHOW COLUMNS FROM results LIKE '$c'")->fetch()) $pdo->exec("ALTER TABLE results ADD $c DECIMAL(5,2) NULL");
    $pdo->exec('UPDATE results r JOIN users u ON u.id=r.student_id SET r.class_name=u.class_name WHERE r.class_name IS NULL');
    $pdo->exec("ALTER TABLE users MODIFY role ENUM('admin','principal','hr','teacher','student') NOT NULL");
    echo '<h2>Upgrade complete</h2><p>New tables and roles are ready. Delete upgrade.php now. Create Principal and HR accounts under Accounts &amp; roles in the admin area.</p>';
} catch (Throwable $e) { echo '<p>Upgrade failed: ' . htmlspecialchars($e->getMessage()) . '</p>'; }
