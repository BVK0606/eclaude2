-- Edutrace Student Management System Database Schema
-- Create Database
CREATE DATABASE IF NOT EXISTS edutrace;
USE edutrace;

-- Users table for authentication
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uname VARCHAR(50) UNIQUE NOT NULL,
    upassword VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Classes table
CREATE TABLE classes (
    class_id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    class_id INT,
    roll_no VARCHAR(20) UNIQUE NOT NULL,
    dob DATE,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL
);

-- Teachers table
CREATE TABLE teachers (
    teacher_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    qualification VARCHAR(100),
    experience INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Subjects table
CREATE TABLE subjects (
    subject_id INT PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(100) NOT NULL,
    class_id INT,
    teacher_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE SET NULL
);

-- Attendance table
CREATE TABLE attendance (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    subject_id INT,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') DEFAULT 'present',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, subject_id, date)
);

-- Marks table
CREATE TABLE marks (
    mark_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    subject_id INT,
    marks_obtained INT NOT NULL,
    total_marks INT DEFAULT 100,
    exam_type VARCHAR(50) DEFAULT 'regular',
    exam_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);

-- Notices table
CREATE TABLE notices (
    notice_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    created_by INT,
    target_role ENUM('all', 'admin', 'teacher', 'student') DEFAULT 'all',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert sample data
INSERT INTO users (uname, upassword, role, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@edutrace.com');

INSERT INTO classes (class_name) VALUES 
('10 A'), ('10 B'), ('10 C'), ('11 A'), ('11 B'), ('11 C'), ('12 A'), ('12 B'), ('12 C');

-- Indexes for better performance
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_students_class ON students(class_id);
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_marks_student_subject ON marks(student_id, subject_id);
CREATE INDEX idx_notices_active ON notices(is_active, created_at);



-- updated structure 
-- ALTER TABLE students ADD COLUMN full_name VARCHAR(100) AFTER roll_no;
-- ALTER TABLE teachers ADD COLUMN full_name VARCHAR(100) AFTER user_id;

---- Remove the foreign key constraint (the name may vary, adjust if needed)
--ALTER TABLE subjects DROP FOREIGN KEY subjects_ibfk_1;

-- Remove the class_id column from subjects
--ALTER TABLE subjects DROP COLUMN class_id;