# Wineridge Academy website and e-portal (PHP + MySQL)

## Set up (new install)
1. Upload this folder to a PHP 8.0+ host with MySQL/MariaDB (XAMPP or cPanel both work).
2. Edit `config.php`: database details, and `BASE_URL` if the site is in a sub-folder (for example `/school`).
3. Open `/install.php` once, then delete it.
4. Make `uploads/` writable.

## Upgrading the first version
Upload the new files over the old ones, open `/upgrade.php` once, then delete it. Existing data is kept.

## Logins (demo data)
- Staff area `/admin/login.php`: admin / Admin@12345, principal1, hr1 (Password@123)
- E-portal `/portal/login.php` (teachers and students only): teacher1, student1, student2 (Password@123)
Change every demo password. New accounts must choose their own password at first login.

## Who can do what
| Role | Access |
|---|---|
| Admin | Everything, including accounts, settings and the audit log |
| Principal | Applications, student records, grade book, report cards (principal comments), exam timetable, notice board |
| HR / Accountant | Staff records, payroll, leave approvals |
| Teacher (portal) | Enter scores, grade book, class report-card comments, timetable and invigilation duties, student records, class notices, own HR record, payslips and leave requests |
| Student (portal) | Own results, grade tracking, report card, timetable, notices, own record |

## E-portal and admin features
- **Digital grade tracking:** class grade book with positions and class averages; per-student trend charts (GPA and average by exam) and subject-by-subject progress with up/down arrows.
- **GPA and CGPA:** 5.0 scale (A=5, B=4, C=3, D=2, E=1, F=0), worked out from scores automatically. CGPA covers all exams to date.
- **Report cards:** built automatically from scores, with class average per subject, GPA, CGPA, position in class, teacher and principal comments, and a print button (print to PDF).
- **Exam scheduling:** timetable by exam, class, date, time, hall and invigilator. Clashes for a class, hall or invigilator are blocked. Students see their own class timetable; teachers see their duties.
- **Student records:** admission number, date of birth, guardian contacts, address, medical notes.
- **Staff, HR and payroll:** staff records with salary and bank details, monthly payroll generation with pension/tax rates, bonus and deduction edits, mark as paid, bank list CSV, printable payslips visible to staff once paid, leave requests and approvals.
- **Digital notice board:** post to the public site, all logged-in users, teachers only, or students (all or one class). Pin and expire notices. Teachers can post to their classes.
- **Security:** role-based permissions on every page, hashed passwords, CSRF tokens, prepared SQL, forced password change for new and reset accounts, lockout after 6 failed logins in 15 minutes, 30-minute inactivity logout, audit log of sensitive actions, secure session cookies, blocked PHP execution in `uploads/`. Use HTTPS in production.
