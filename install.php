<?php
// Run once in the browser: http://your-site/install.php  then DELETE this file.
require __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `' . DB_NAME . '`');
    foreach (array_filter(array_map('trim', explode(';', file_get_contents(__DIR__ . '/schema.sql')))) as $sql) $pdo->exec($sql);
    if ($pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn() > 0) exit('<p>Already installed. If you are upgrading an older copy, run upgrade.php instead. Then delete install.php.</p>');
    $h = fn($p) => password_hash($p, PASSWORD_DEFAULT);
    $ins = $pdo->prepare('INSERT INTO users (role,full_name,username,class_name,password_hash) VALUES (?,?,?,?,?)');
    $mk = function ($role, $name, $user, $class, $pw) use ($ins, $pdo, $h) { $ins->execute([$role, $name, $user, $class, $h($pw)]); return (int)$pdo->lastInsertId(); };
    $mk('admin', 'School Administrator', 'admin', null, 'Admin@12345');
    $mk('principal', 'Dr. Ngozi Adeyemi', 'principal1', null, 'Password@123');
    $mk('hr', 'Mr. Segun Balogun', 'hr1', null, 'Password@123');
    $t = $mk('teacher', 'Mrs. Adaobi Okafor', 'teacher1', null, 'Password@123');
    $s1 = $mk('student', 'Tunde Bakare', 'student1', 'JSS 1', 'Password@123');
    $s2 = $mk('student', 'Amaka Eze', 'student2', 'JSS 1', 'Password@123');
    $set = $pdo->prepare('INSERT INTO settings (k,v) VALUES (?,?)');
    $set->execute(['apply_open', date('Y-m-d', strtotime('-7 days'))]); $set->execute(['apply_close', date('Y-m-d', strtotime('+45 days'))]); $set->execute(['school_name', 'Wineridge Academy']);
    $set->execute(['pension_pct', '8']); $set->execute(['tax_pct', '0']);
    $pdo->prepare('INSERT INTO staff_profiles (user_id,staff_no,position,department,qualification,hired_on,basic_salary,allowances,bank_name,account_no) VALUES (?,?,?,?,?,?,?,?,?,?)')->execute([$t, 'STF-001', 'Mathematics Teacher', 'Secondary', 'B.Sc. Ed. Mathematics', '2020-09-01', 150000, 20000, 'Sample Bank', '0123456789']);
    $sp = $pdo->prepare('INSERT INTO student_profiles (user_id,admission_no,dob,gender,guardian_name,guardian_phone,admitted_on) VALUES (?,?,?,?,?,?,?)');
    $sp->execute([$s1, 'ADM-2025-001', '2014-03-12', 'Male', 'Mr. Bakare', '08011111111', '2025-09-08']); $sp->execute([$s2, 'ADM-2025-002', '2014-07-30', 'Female', 'Mrs. Eze', '08022222222', '2025-09-08']);
    $pdo->exec("INSERT INTO exam_sessions (title,section,start_date,end_date,notes) VALUES
      ('First Term Examinations','Both', DATE_SUB(CURDATE(), INTERVAL 120 DAY), DATE_SUB(CURDATE(), INTERVAL 110 DAY), 'Results released to the e-portal.'),
      ('Second Term Examinations','Both', DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_SUB(CURDATE(), INTERVAL 50 DAY), 'Results released to the e-portal.'),
      ('Third Term Examinations','Both', DATE_ADD(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Timetable is on the e-portal.')");
    $r = $pdo->prepare("INSERT INTO results (student_id,session_id,subject,ca,exam,score,grade,remark,teacher_id,class_name) VALUES (?,?,?,?,?,?,?,?,?,'JSS 1')");
    $data = [[$s1, 1, 'Mathematics', 52], [$s1, 1, 'English', 61], [$s1, 1, 'Basic Science and Technology', 44], [$s1, 2, 'Mathematics', 63], [$s1, 2, 'English', 66], [$s1, 2, 'Basic Science and Technology', 58],
             [$s2, 1, 'Mathematics', 74], [$s2, 1, 'English', 70], [$s2, 1, 'Basic Science and Technology', 68], [$s2, 2, 'Mathematics', 71], [$s2, 2, 'English', 77], [$s2, 2, 'Basic Science and Technology', 72]];
    foreach ($data as [$sid, $ses, $sub, $sc]) { [$g, $rm] = grade_for($sc); $ca = (int)round($sc * 0.4); $r->execute([$sid, $ses, $sub, $ca, $sc - $ca, $sc, $g, $rm, $t]); }
    $pdo->exec("INSERT INTO term_records (student_id,session_id,days_opened,days_present,height_cm,weight_kg,physical_rating,sports,clubs) VALUES ($s1,2,60,57,142.5,36.0,'Very Good','Football (house team)','Debate club'),($s2,2,60,59,138.0,33.5,'Excellent','Athletics: 100m sprint','Music club')");
    $pdo->prepare('INSERT INTO exam_schedule (session_id,class_name,subject,exam_date,start_time,end_time,venue,invigilator_id) VALUES (3,?,?,?,?,?,?,?)')->execute(['JSS 1', 'Mathematics', date('Y-m-d', strtotime('+21 days')), '09:00', '11:00', 'Main hall', $t]);
    $pdo->prepare('INSERT INTO exam_schedule (session_id,class_name,subject,exam_date,start_time,end_time,venue,invigilator_id) VALUES (3,?,?,?,?,?,?,?)')->execute(['JSS 1', 'English', date('Y-m-d', strtotime('+22 days')), '09:00', '11:00', 'Main hall', $t]);
    $pdo->exec("INSERT INTO notices (title,body,audience,pinned) VALUES ('Third term exam timetable is out','Students can see their timetable in the e-portal under Exam timetable. Arrive 20 minutes early.','public',1),('Staff meeting on Friday','All teachers should meet in the staff room at 2:45 pm.','teachers',0)");
    $pdo->exec("INSERT INTO posts (title,category,excerpt,body) VALUES
      ('Welcome to the new term','News','Classes resume with a refreshed timetable and new clubs.','Dear parents, we are glad to welcome every pupil back. Please read the term calendar carefully.'),
      ('How to help your child read at home','Blog','Ten minutes a day makes a real difference.','Choose a quiet time, let your child pick the book, and talk about the story together.')");
    $pdo->exec("INSERT INTO events (title,event_date,location,description) VALUES
      ('Inter-house Sports Day', DATE_ADD(CURDATE(), INTERVAL 21 DAY), 'School field', 'Races, relays and a tug of war. Parents are welcome.'),
      ('Open Day for new parents', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Main hall', 'Tour the school and meet our teachers.')");
    $pdo->exec("INSERT INTO faqs (question,answer,sort_order) VALUES
      ('How do I apply?','Use the Apply now page while the admission window is open. You will get a reference number straight away.',1),
      ('How do I get an e-portal login?','Teachers and students receive their username from the school office.',2),
      ('What are the school hours?','Primary: 8:00 am to 2:30 pm. Secondary: 7:45 am to 3:30 pm.',3)");
    $pdo->exec("INSERT INTO activities (name,description) VALUES ('Football and athletics','Weekly training and inter-house competitions.'),('Debate and public speaking','Builds confidence and clear thinking.'),('Music and drama','Choir, instruments and an annual stage play.'),('Coding club','Learn to build simple apps and games.')");
    echo '<h2>Installed</h2><p><b>Staff login</b> (/admin/login.php): admin / Admin@12345, principal1 and hr1 (password Password@123)<br><b>E-portal</b> (/portal/login.php): teacher1, student1, student2 (password Password@123)</p><p><b>Now delete install.php and change the admin password.</b></p>';
} catch (Throwable $e) { echo '<p>Install failed: ' . htmlspecialchars($e->getMessage()) . '</p><p>Check the database details in config.php.</p>'; }
