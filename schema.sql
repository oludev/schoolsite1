CREATE TABLE IF NOT EXISTS settings (k VARCHAR(60) PRIMARY KEY, v TEXT);
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role ENUM('admin','principal','hr','teacher','student') NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(120) NULL,
  class_name VARCHAR(40) NULL,
  password_hash VARCHAR(255) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  must_change TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  category ENUM('News','Blog') NOT NULL DEFAULT 'News',
  excerpt VARCHAR(300), body TEXT, image VARCHAR(200),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL, event_date DATE NOT NULL,
  location VARCHAR(150), description TEXT
);
CREATE TABLE IF NOT EXISTS faqs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question VARCHAR(250) NOT NULL, answer TEXT NOT NULL, sort_order INT DEFAULT 0
);
CREATE TABLE IF NOT EXISTS activities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL, description TEXT, image VARCHAR(200)
);
CREATE TABLE IF NOT EXISTS gallery (
  id INT AUTO_INCREMENT PRIMARY KEY,
  caption VARCHAR(160), album VARCHAR(80) DEFAULT 'General', image VARCHAR(200) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS exam_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL, section ENUM('Primary','Secondary','Both') DEFAULT 'Both',
  start_date DATE, end_date DATE, notes TEXT
);
CREATE TABLE IF NOT EXISTS results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL, session_id INT NOT NULL, subject VARCHAR(80) NOT NULL,
  score DECIMAL(5,2) NOT NULL, grade CHAR(2), remark VARCHAR(40), teacher_id INT, class_name VARCHAR(40) NULL, ca DECIMAL(5,2) NULL, exam DECIMAL(5,2) NULL,
  UNIQUE KEY uniq_result (student_id, session_id, subject),
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (session_id) REFERENCES exam_sessions(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ref_no VARCHAR(20) NOT NULL UNIQUE,
  child_name VARCHAR(120) NOT NULL, dob DATE NOT NULL, gender VARCHAR(10),
  section VARCHAR(20) NOT NULL, class_applied VARCHAR(40) NOT NULL, previous_school VARCHAR(150),
  parent_name VARCHAR(120) NOT NULL, phone VARCHAR(30) NOT NULL, email VARCHAR(120), address VARCHAR(250),
  status ENUM('New','Reviewing','Admitted','Declined') DEFAULT 'New',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120), email VARCHAR(120), subject VARCHAR(160), body TEXT,
  is_read TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS student_profiles (
  user_id INT PRIMARY KEY, admission_no VARCHAR(30), dob DATE NULL, gender VARCHAR(10),
  guardian_name VARCHAR(120), guardian_phone VARCHAR(30), guardian_email VARCHAR(120), address VARCHAR(250),
  medical_notes TEXT, admitted_on DATE NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS staff_profiles (
  user_id INT PRIMARY KEY, staff_no VARCHAR(30), position VARCHAR(80), department VARCHAR(80), qualification VARCHAR(120),
  phone VARCHAR(30), address VARCHAR(250), hired_on DATE NULL, basic_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
  allowances DECIMAL(12,2) NOT NULL DEFAULT 0, bank_name VARCHAR(80), account_no VARCHAR(30), emergency_contact VARCHAR(150),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS payroll (
  id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, month CHAR(7) NOT NULL,
  basic DECIMAL(12,2), allowances DECIMAL(12,2), bonus DECIMAL(12,2) DEFAULT 0, deductions DECIMAL(12,2) DEFAULT 0, net DECIMAL(12,2),
  status ENUM('Draft','Paid') DEFAULT 'Draft', paid_on DATE NULL,
  UNIQUE KEY uniq_pay (user_id, month),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS leave_requests (
  id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, leave_type VARCHAR(40), start_date DATE, end_date DATE, reason VARCHAR(300),
  status ENUM('Pending','Approved','Declined') DEFAULT 'Pending', decided_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS exam_schedule (
  id INT AUTO_INCREMENT PRIMARY KEY, session_id INT NOT NULL, class_name VARCHAR(40) NOT NULL, subject VARCHAR(80) NOT NULL,
  exam_date DATE NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, venue VARCHAR(80), invigilator_id INT NULL,
  FOREIGN KEY (session_id) REFERENCES exam_sessions(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS report_comments (
  student_id INT NOT NULL, session_id INT NOT NULL, teacher_comment VARCHAR(300), principal_comment VARCHAR(300),
  PRIMARY KEY (student_id, session_id)
);
CREATE TABLE IF NOT EXISTS notices (
  id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, body TEXT,
  audience ENUM('public','all','teachers','students') NOT NULL DEFAULT 'all', class_name VARCHAR(40) NULL,
  pinned TINYINT(1) DEFAULT 0, expires_on DATE NULL, posted_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, username VARCHAR(60), action VARCHAR(60), detail VARCHAR(300), ip VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(60), ip VARCHAR(45), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS term_records (
  student_id INT NOT NULL, session_id INT NOT NULL, days_opened SMALLINT NULL, days_present SMALLINT NULL,
  height_cm DECIMAL(5,1) NULL, weight_kg DECIMAL(5,1) NULL, physical_rating VARCHAR(30), sports VARCHAR(300), clubs VARCHAR(300),
  PRIMARY KEY (student_id, session_id)
);
CREATE TABLE IF NOT EXISTS testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY, parent_name VARCHAR(120) NOT NULL, role_line VARCHAR(160), quote TEXT NOT NULL, image VARCHAR(200),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
