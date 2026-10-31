# SkillBridge – Job and Internship Portal

SkillBridge is a PHP and MySQL-based job and internship portal developed as part of the **ApexPlanet Software Pvt. Ltd. Full Stack Web Development Internship – Task 5**.

The platform connects students with job and internship opportunities. Students can register, log in, explore available jobs, search and filter listings, and submit applications. Administrators can manage users, jobs, and applications through an admin dashboard.

## Features

### Student Features
- User registration and login
- OTP verification flow
- Student dashboard
- Browse available jobs and internships
- Search jobs using keywords
- Filter job listings
- AJAX-based job search
- Apply for jobs and internships
- View submitted applications
- Track application status

### Admin Features
- Admin dashboard with statistics
- Manage registered users
- Add, edit, and delete job listings
- View student applications
- Update application statuses
- Application analytics dashboard

## Technologies Used

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **AJAX:** Asynchronous job search
- **Email Library:** PHPMailer
- **Local Server:** XAMPP
- **Database Management:** phpMyAdmin
- **Version Control:** Git and GitHub

## Project Structure

```text
ApexPlanet-Task-5/
├── ajax/
│   └── search_jobs.php
├── assets/
│   ├── css/
│   └── js/
├── config/
│   ├── config.php
│   └── db.php
├── includes/
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── admin.php
├── admin_applications.php
├── admin_jobs.php
├── admin_users.php
├── dashboard.php
├── index.php
├── jobs.php
├── apply.php
├── my_applications.php
├── login.php
├── register.php
├── verify_otp.php
├── logout.php
├── database.sql
├── composer.json
├── composer.lock
├── ER_DIAGRAM.md
├── README.md
└── .gitignore
```

## Installation and Setup

1. Install and open XAMPP.
2. Start Apache and MySQL.
3. Place the project folder inside `C:\xampp\htdocs\`.
4. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
5. Import the `database.sql` file.
6. Check the database settings in `config/config.php`.
7. Open a terminal inside the project folder and run:

   ```bash
   composer install
   ```

8. Configure SMTP settings in `config/config.php` if email OTP delivery is required. Use a Gmail App Password rather than your normal Gmail password.
9. Open the project in your browser:

   `http://localhost/ApexPlanet-Task-5/`

## Database

- **Database Name:** `skillbridge_db`
- **Database Type:** MySQL
- **Database File:** `database.sql`

## Email Configuration

The project uses PHPMailer for email functionality. Configure valid SMTP credentials in the application configuration to enable actual email delivery.

Do not upload real SMTP passwords or private credentials to GitHub.

## Learning Outcomes

- PHP and MySQL integration
- Database design and relationships
- User authentication and OTP verification
- Session management
- CRUD operations
- AJAX-based search and filtering
- Job and internship application management
- Admin dashboard development
- Git and GitHub version control

## Internship Details

- **Organization:** ApexPlanet Software Pvt. Ltd.
- **Program:** Full Stack Web Development Internship
- **Project:** Task 5 – SkillBridge Job and Internship Portal

## GitHub Repository

[View SkillBridge on GitHub](https://github.com/katkambhavani/ApexPlanet-Task-5)

---

© 2026 SkillBridge. All rights reserved.