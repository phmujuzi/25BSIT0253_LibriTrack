-- LibriTrack Library Management System
-- University of Kisubi (UNiK)
-- Import this file into phpMyAdmin

CREATE DATABASE IF NOT EXISTS libritrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE libritrack;

-- ADMINS
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- STUDENTS
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(30) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    course VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    status ENUM('active','suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- BOOKS
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150) NOT NULL,
    isbn VARCHAR(20),
    category VARCHAR(80) NOT NULL,
    copies_total INT DEFAULT 1,
    copies_available INT DEFAULT 1,
    shelf VARCHAR(30),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- BORROWINGS
CREATE TABLE borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    status ENUM('borrowed','returned','overdue') DEFAULT 'borrowed',
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (book_id) REFERENCES books(id)
);

-- NOTICES
CREATE TABLE notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin: username=admin, password=admin123
INSERT INTO admins (username, password, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Library Administrator');

-- Students: password=student123 for all
INSERT INTO students (student_id, full_name, email, course, password) VALUES
('UNiK/2022/001', 'Akello Grace', 'akello@unik.ac.ug', 'Bachelor of Information Technology', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('UNiK/2022/002', 'Okello David', 'okello@unik.ac.ug', 'Bachelor of Business Administration', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('UNiK/2022/003', 'Namukasa Faith', 'namukasa@unik.ac.ug', 'Bachelor of Education', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('UNiK/2023/001', 'Mugisha Brian', 'mugisha@unik.ac.ug', 'Bachelor of Information Technology', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('UNiK/2023/002', 'Nakato Sharon', 'nakato@unik.ac.ug', 'Bachelor of Laws', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Books
INSERT INTO books (title, author, isbn, category, copies_total, copies_available, shelf) VALUES
('Kintu', 'Jennifer Nansubuga Makumbi', '978-1-55597-795-2', 'Ugandan Literature', 3, 3, 'A1'),
('Song of Lawino', 'Okot p''Bitek', '978-0-435-90015-3', 'Ugandan Literature', 2, 2, 'A2'),
('Abyssinian Chronicles', 'Moses Isegawa', '978-0-375-70732-9', 'Ugandan Literature', 2, 2, 'A3'),
('Things Fall Apart', 'Chinua Achebe', '978-0-385-47454-2', 'African Fiction', 4, 4, 'B1'),
('Half of a Yellow Sun', 'Chimamanda Ngozi Adichie', '978-1-4000-9557-3', 'African Fiction', 3, 3, 'B2'),
('Long Walk to Freedom', 'Nelson Mandela', '978-0-316-54818-3', 'African History', 3, 3, 'B3'),
('Sowing the Mustard Seed', 'Yoweri Museveni', '978-0-333-62513-5', 'Ugandan History', 2, 2, 'C1'),
('The Constitution of Uganda 1995', 'Republic of Uganda', NULL, 'Law', 5, 5, 'D1'),
('Introduction to Algorithms', 'Cormen et al.', '978-0-262-03384-8', 'Computer Science', 3, 3, 'E1'),
('Python Crash Course', 'Eric Matthes', '978-1-59327-928-8', 'Computer Science', 4, 4, 'E2'),
('Database System Concepts', 'Silberschatz et al.', '978-0-07-352332-3', 'Computer Science', 3, 3, 'E3'),
('Business Management', 'Peter Drucker', '978-0-06-125897-1', 'Business', 3, 3, 'F1'),
('Principles of Economics', 'N. Gregory Mankiw', '978-1-305-58512-6', 'Economics', 4, 4, 'F2'),
('Farming for Life in Uganda', 'NARO Uganda', NULL, 'Agriculture', 3, 3, 'G1'),
('Human Anatomy & Physiology', 'Marieb & Hoehn', '978-0-321-74326-8', 'Health Sciences', 2, 2, 'H1'),
('The River and the Source', 'Margaret Ogola', '978-9966-888-00-5', 'African Fiction', 3, 3, 'B4'),
('Weep Not, Child', 'Ngugi wa Thiong''o', '978-0-14-118776-1', 'African Fiction', 3, 3, 'B5'),
('Research Methods', 'John Creswell', '978-1-4522-2609-5', 'Academic', 4, 4, 'I1'),
('Academic Writing', 'Stephen Bailey', '978-0-415-67320-5', 'Academic', 3, 3, 'I2'),
('Mathematics for Engineers', 'K.A. Stroud', '978-1-137-03120-4', 'Mathematics', 3, 3, 'J1');

-- Sample borrowings
INSERT INTO borrowings (student_id, book_id, borrow_date, due_date, status) VALUES
(1, 1, CURDATE() - INTERVAL 5 DAY, CURDATE() + INTERVAL 9 DAY, 'borrowed'),
(1, 9, CURDATE() - INTERVAL 10 DAY, CURDATE() + INTERVAL 4 DAY, 'borrowed'),
(2, 4, CURDATE() - INTERVAL 3 DAY, CURDATE() + INTERVAL 11 DAY, 'borrowed'),
(3, 12, CURDATE() - INTERVAL 20 DAY, CURDATE() - INTERVAL 6 DAY, 'overdue');

-- Update available copies for borrowed books
UPDATE books SET copies_available = copies_available - 1 WHERE id IN (1, 9, 4, 12);

-- Notices
INSERT INTO notices (title, body) VALUES
('Welcome to LibriTrack!', 'The University of Kisubi library system is now live. You can check your borrowed books, due dates, and more through this portal.'),
('Library Hours', 'The library is open Monday to Friday 8am – 8pm, and Saturday 9am – 5pm. Closed on Sundays and public holidays.'),
('Fine Policy', 'Overdue books are charged UGX 500 per day. Please return books on time to avoid fines.');

-- ============================================================
-- Assignment 4: tbl_content (Dynamic Featured Books)
-- id, title, description, image_url as required
-- ============================================================
CREATE TABLE IF NOT EXISTS tbl_content (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image_url   VARCHAR(500) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO tbl_content (title, description, image_url) VALUES
('Kintu',
 'A sweeping Ugandan epic by Jennifer Nansubuga Makumbi tracing the descendants of Kintu Kidda through centuries of family curses and redemption. A landmark of African literature.',
 'https://m.media-amazon.com/images/S/compressed.photo.goodreads.com/books/1486840973i/31944671.jpg'),

('Song of Lawino',
 'Okot p''Bitek''s celebrated poem narrated by Lawino, a traditional Acholi woman lamenting her husband''s rejection of African culture in favour of Western ways.',
 'https://betweenthecovers.cdn.bibliopolis.com/pictures/586052.jpg?auto=webp&v=1720724181'),

('Things Fall Apart',
 'Chinua Achebe''s masterpiece about Okonkwo, a proud Igbo warrior, and the collapse of traditional society under colonial rule. One of the most widely read African novels.',
 'https://betweenthecovers.cdn.bibliopolis.com/pictures/560640.jpg?auto=webp&v=1701374615'),

('Long Walk to Freedom',
 'Nelson Mandela''s autobiography — from his rural childhood through 27 years in prison to his presidency. An inspiring account of perseverance, sacrifice and justice.',
 'https://g.christianbook.com/g/slideshow/5/548182/main/548182_1_ftc.jpg'),

('Introduction to Algorithms',
 'The definitive textbook on computer algorithms by Cormen et al. Covers sorting, data structures, graph algorithms and complexity. Essential for all IT students.',
 'https://miro.medium.com/v2/resize:fit:720/format:webp/1*tDC1ojhd2zFhVaNZvNeKUA.jpeg'),

('Python Crash Course',
 'A beginner-friendly introduction to programming with Python by Eric Matthes. Covers variables, loops, functions and real-world projects. Perfect for first-year IT students.',
 'https://i5.walmartimages.com/seo/Python-Crash-Course-2nd-Edition-A-Hands-On-Project-Based-Introduction-to-Programming-Paperback-9781593279288_410f2915-a902-4cc5-8713-0f007907e37e.58bcfec84fa73bf063f2a455e2d306df.jpeg');
