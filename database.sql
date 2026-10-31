CREATE DATABASE IF NOT EXISTS skillbridge_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE skillbridge_db;

CREATE TABLE users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(150) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('student','admin') NOT NULL DEFAULT 'student',
 verified TINYINT(1) NOT NULL DEFAULT 0,
 otp_code VARCHAR(6) DEFAULT NULL,
 otp_expires DATETIME DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE jobs (
 id INT AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(150) NOT NULL,
 company VARCHAR(150) NOT NULL,
 location VARCHAR(150) NOT NULL,
 job_type ENUM('Internship','Full-time','Part-time','Remote') NOT NULL DEFAULT 'Internship',
 category VARCHAR(100) NOT NULL,
 description TEXT NOT NULL,
 status ENUM('active','closed') NOT NULL DEFAULT 'active',
 created_by INT DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_jobs_creator FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE applications (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 job_id INT NOT NULL,
 cover_letter TEXT,
 status ENUM('pending','shortlisted','rejected','accepted') NOT NULL DEFAULT 'pending',
 applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY unique_application(user_id,job_id),
 CONSTRAINT fk_app_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_app_job FOREIGN KEY(job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Demo student account: register normally through the website to create a verified account.
-- Create the first admin by registering an account, then run:
-- UPDATE users SET role='admin', verified=1 WHERE email='your-email@example.com';

INSERT INTO jobs(title,company,location,job_type,category,description) VALUES
('PHP Developer Intern','CodeCrafters Labs','Hyderabad, Telangana','Internship','Technology','Work with a development team to build and maintain PHP applications. Basic PHP, MySQL, HTML, CSS and JavaScript knowledge is useful.'),
('Frontend Developer','BrightPixel Studio','Remote','Full-time','Technology','Build responsive user interfaces using HTML, CSS and JavaScript. Collaborate with designers and backend developers.'),
('Data Analyst Intern','InsightWorks','Hyderabad, Telangana','Internship','Data & Analytics','Support data cleaning, reporting and dashboard development. Familiarity with spreadsheets and SQL is preferred.'),
('Junior UI Designer','Northstar Digital','Hybrid','Part-time','Design','Create clear and consistent web interfaces, wireframes and design assets. A portfolio of practice work is welcome.'),
('QA Testing Intern','QualityLoop','Remote','Internship','Technology','Test web applications, document issues and work with developers to verify fixes.');