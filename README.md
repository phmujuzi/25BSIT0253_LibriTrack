# LibriTrack — Library Management System

## Student Information
| Field | Details |
|-------|---------|
| **Student Name** | Philip [Your Surname] |
| **Registration Number** | [Your Reg Number e.g. 23BIT012] |
| **Project Title** | LibriTrack Library Management System |
| **Institution** | University of Kisubi (UNiK) |

---

## Project Description

LibriTrack is a dynamic web-based Library Management System built for the University of Kisubi. It provides two portals — an **Admin portal** for librarians to manage books, students, and borrowings, and a **Student portal** for students to track their borrowed books, check due dates, and browse the catalog.

---

## Features

### Admin Portal
- Dashboard with live statistics (total books, students, borrowed, overdue)
- Book management — add, search, and delete books
- Issue and return books to students
- View all borrowings with filters (active, overdue, returned)
- Student registration and account management
- Post and manage library notices

### Student Portal
- Personal dashboard showing borrowed books and due dates
- Overdue alerts with fine calculation (UGX 500/day)
- Full borrowing history
- Browse the complete book catalog with search and filter
- View library notices
- Update profile and change password

---

## Technologies Used

| Technology | Purpose |
|-----------|---------|
| **PHP** | Server-side scripting and backend logic |
| **MySQL** | Database management |
| **HTML5** | Page structure and markup |
| **CSS3** | Styling and responsive layout |
| **JavaScript** | Client-side interactivity (modals, tab switching) |
| **XAMPP** | Local development server (Apache + MySQL) |

---

## Project Structure

```
libritrack/
├── index.php                  # Login page (Admin & Student)
├── database.sql               # MySQL database export
├── reset.php                  # Password reset utility
├── includes/
│   └── config.php             # Database connection & helpers
├── admin/
│   ├── dashboard.php          # Admin home dashboard
│   ├── books.php              # Book catalog management
│   ├── borrow.php             # Issue / Return books
│   ├── borrowings.php         # All borrowings list
│   ├── students.php           # Student management
│   ├── notices.php            # Library notices
│   ├── logout.php
│   └── partials/
│       ├── header.php
│       └── footer.php
└── student/
    ├── dashboard.php          # Student home
    ├── my_books.php           # Currently borrowed books
    ├── history.php            # Borrowing history
    ├── catalog.php            # Browse book catalog
    ├── notices.php            # View notices
    ├── profile.php            # Profile & password
    ├── logout.php
    └── partials/
        ├── header.php
        └── footer.php
```

---

## Requirements

- XAMPP (Apache + MySQL) — [Download here](https://www.apachefriends.org/)
- Web browser (Chrome, Firefox, Edge)
- Git

---

## Steps to Run the Project

### Step 1 — Install XAMPP
Download and install XAMPP from https://www.apachefriends.org/. Start both **Apache** and **MySQL** from the XAMPP Control Panel.

### Step 2 — Clone or Copy Project
```bash
# Option A: Clone from GitHub
git clone https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git

# Option B: Copy the libritrack folder manually
```
Place the `libritrack` folder inside:
```
C:\xampp\htdocs\libritrack\
```

### Step 3 — Import the Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **New** in the left sidebar
3. Create a database named exactly: `libritrack`
4. Click the `libritrack` database, then click the **Import** tab
5. Click **Choose File** and select `database.sql` from the project folder
6. Click **Go** to import

### Step 4 — Open the Application
Visit in your browser:
```
http://localhost/libritrack/
```

---

## Login Credentials

| Role | Username / Student ID | Password |
|------|-----------------------|----------|
| **Admin** | `admin` | `password` |
| **Student** | `UNiK/2022/001` | `password` |
| **Student** | `UNiK/2022/002` | `password` |

---

## Database Import Instructions

1. Open phpMyAdmin at `http://localhost/phpmyadmin`
2. Create a new database called `libritrack`
3. Select the database and click **Import**
4. Choose the file `database.sql` from the project root
5. Click **Go** — all tables and sample data will be loaded automatically

The database includes:
- 5 sample student accounts
- 20 books across 10 categories (Ugandan & African collection)
- Sample borrowing records
- Library notices

---

## Fine Policy

- Overdue fine: **UGX 500 per day**
- Maximum books per student: **4**
- Standard loan period: **14 days**

---

## License

This project was developed as an academic assignment for the University of Kisubi.
