BankAssist is a secure, web-based customer service management system designed to help banks manage service requests efficiently.
It provides role-based access for Admins, Staff, and Customers, enabling centralized request tracking, internal messaging, and performance monitoring.

Installation & Setup

1. Clone or Download the Project
```bash
git clone https://github.com/your-username/bankAssist.git
```
2. place the folder inside htdocs/BankAssist
   
3. Open phpMyAdmin
Create a database named bankassist
Import the SQL file:/database/bankassist.sql


4. Configure database credentials in: /includes/db_connect.php
$host = "localhost";
$user = "root";
$password = "";
$database = "bankassist";

5. Start Apache & MySQL and open:http://localhost/bankAssist

User guide:
Customers log in from login page while staff&admin log in from staff_login.php

Default account for admin with username: admin and password: admin123
An admin can create a staff and give them their password which can be changed anytime afterwards.



This project was developed as an academic final project to demonstrate secure web application development using PHP, MySQL, and a 3-tier architecture.
