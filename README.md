# 🍱 Tiffinly - Home-Cooked Meal Subscription Platform

**Tiffinly** is a full-stack web application that connects users with affordable, home-cooked Indian meals through a flexible subscription system. Built with PHP and MySQL, the platform supports three roles: **Users**, **Admins**, and **Delivery Partners**, each with dedicated dashboards and workflows.

---

## 🚀 Features

### 👤 Users
- Register & login with secure validation
- Subscribe to personalized meal plans
- Track orders and delivery status
- Contact support and manage profile

### 🛠 Admins
- Manage users, meals, subscriptions, and delivery staff
- Add, edit, or delete meal plans
- Generate reports and analytics
- Oversee customer support

### 🚚 Delivery Partners
- Access delivery assignments
- Update order delivery status
- View assigned zones and schedules

---

## 🔐 Authentication & Security
- Role-based access control (user/admin/delivery)
- Encrypted passwords using bcrypt
- Server-side and client-side validation
- Session management with protection against unauthorized access

---

## 🧑‍💻 Tech Stack

| Tech           | Description                          |
|----------------|--------------------------------------|
| PHP (OOP)      | Backend logic and authentication     |
| MySQL          | Relational database                  |
| HTML/CSS/JS    | Frontend structure & interactions    |
| Bootstrap 5    | Responsive and modern UI             |
| AJAX           | Seamless dynamic interactions        |
| Font Awesome   | Icons for better UX                  |

---


---

## 🧪 Setup Instructions

### ✅ Prerequisites
- PHP 8.x+
- MySQL 5.7+ or MariaDB
- Apache or any web server
- Composer (optional, for future upgrades)

### ⚙️ Installation

1. Clone the repository:
   -bash:
   git clone https://github.com/yourusername/tiffinly.git
   cd tiffinly
   
2. Import the database:
-Open phpMyAdmin or MySQL CLI
-Create a new database tiffinly
-Import database.sql from the project

3. Configure DB connection:
-Edit includes/config/db_connect.php with your local credentials

4. Start the server:
-Use XAMPP/LAMP or run:
php -S localhost:8000

5. Open http://localhost:8000 in your browser

📌 Why Tiffinly?
This project was created to solve real-world problems — connecting people with hygienic, 
home-style meals while learning full-stack web development, UI design, and backend architecture. 
Built with scalability, usability, and learning in mind.

🙋‍♀️ About the Developer
Mekhna Alphons Joby
Full-Stack Developer | Passionate about building creative, real-world web solutions
🔗 Interested in tech, design, and branding
