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

## One login page
Everyone signs in at `/login.php`. Admin, principal and HR go to the admin area. Teachers, students and parents go to the e-portal. Parent accounts are created under Accounts & roles and linked to their children by the children's usernames.

## Application fee, payment window and application form
1. Admin > Application payments: set the fee (0 = free), your bank account, and your Paystack and/or Flutterwave keys. Use test keys first.
2. Add your email (SMTP) details on the same page and press Send test email. Confirmation emails go to parents after payment and after they submit the form. The Email log on that page shows any failure.
3. Parents pay in a pop-up window on the Apply now page (bank transfer, Paystack card, Flutterwave, USSD). The server checks every online payment with Paystack or Flutterwave before it unlocks the form. Bank transfers are confirmed by you under Application payments.
4. After payment the full form appears on the same page. Parents can download a blank copy, and a filled copy after they submit (Print / Save as PDF, or Word .doc).
5. Requirements: PHP cURL enabled. On XAMPP for Windows, set curl.cainfo and openssl.cafile in php.ini to a CA bundle, or verification calls will fail with a certificate error.
6. Passport photographs and signatures are stored in /uploads with random file names. Keep that folder out of search engines and back it up.
