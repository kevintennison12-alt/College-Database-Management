-- College DBMS - Complete SQL File with Schema and Sample Data
-- Save this as 'college_dbms.sql' and import into your database

-- Create the database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS college_dbms;
USE college_dbms;

-- 1. Departments Table
CREATE TABLE IF NOT EXISTS Departments (
    department_id INT PRIMARY KEY,
    department_name VARCHAR(50) NOT NULL,
    building VARCHAR(30),
    budget DECIMAL(12,2)
);

-- 2. Faculty Table
CREATE TABLE IF NOT EXISTS Faculty (
    faculty_id INT PRIMARY KEY,
    first_name VARCHAR(30) NOT NULL,
    last_name VARCHAR(30) NOT NULL,
    email VARCHAR(50) UNIQUE,
    phone VARCHAR(15),
    department_id INT,
    hire_date DATE,
    salary DECIMAL(10,2),
    FOREIGN KEY (department_id) REFERENCES Departments(department_id)
);

-- 3. Students Table
CREATE TABLE IF NOT EXISTS Students (
    student_id INT PRIMARY KEY,
    first_name VARCHAR(30) NOT NULL,
    last_name VARCHAR(30) NOT NULL,
    email VARCHAR(50) UNIQUE,
    phone VARCHAR(15),
    enrollment_date DATE,
    major_department_id INT,
    FOREIGN KEY (major_department_id) REFERENCES Departments(department_id)
);

-- 4. Courses Table
CREATE TABLE IF NOT EXISTS Courses (
    course_id INT PRIMARY KEY,
    course_code VARCHAR(10) NOT NULL,
    title VARCHAR(100) NOT NULL,
    credits INT CHECK (credits > 0 AND credits <= 5),
    department_id INT,
    description TEXT,
    FOREIGN KEY (department_id) REFERENCES Departments(department_id)
);

-- 5. Classes Table (course offerings/sections)
CREATE TABLE IF NOT EXISTS Classes (
    class_id INT PRIMARY KEY,
    course_id INT NOT NULL,
    faculty_id INT,
    semester VARCHAR(20) NOT NULL,
    year INT NOT NULL,
    room_number VARCHAR(10),
    schedule VARCHAR(50),
    max_capacity INT,
    current_enrollment INT DEFAULT 0,
    FOREIGN KEY (course_id) REFERENCES Courses(course_id),
    FOREIGN KEY (faculty_id) REFERENCES Faculty(faculty_id)
);

-- 6. Enrollment Table
CREATE TABLE IF NOT EXISTS Enrollment (
    enrollment_id INT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    enrollment_date DATE,
    grade VARCHAR(2),
    FOREIGN KEY (student_id) REFERENCES Students(student_id),
    FOREIGN KEY (class_id) REFERENCES Classes(class_id),
    UNIQUE (student_id, class_id)
);

-- 7. Prerequisites Table
CREATE TABLE IF NOT EXISTS Prerequisites (
    prerequisite_id INT PRIMARY KEY,
    course_id INT NOT NULL,
    required_course_id INT NOT NULL,
    minimum_grade VARCHAR(2),
    FOREIGN KEY (course_id) REFERENCES Courses(course_id),
    FOREIGN KEY (required_course_id) REFERENCES Courses(course_id)
);

-- ====================== SAMPLE DATA ======================

-- Departments
INSERT INTO Departments VALUES 
(1, 'Computer Science', 'Engineering Building', 500000),
(2, 'Mathematics', 'Science Building', 350000),
(3, 'Physics', 'Science Building', 400000),
(4, 'English', 'Humanities Building', 300000),
(5, 'Biology', 'Science Building', 450000),
(6, 'Chemistry', 'Science Building', 420000),
(7, 'History', 'Humanities Building', 280000),
(8, 'Economics', 'Business Building', 380000);

-- Faculty
INSERT INTO Faculty VALUES
(101, 'John', 'Smith', 'jsmith@college.edu', '555-1001', 1, '2010-08-15', 85000),
(102, 'Sarah', 'Johnson', 'sjohnson@college.edu', '555-1002', 1, '2012-05-20', 92000),
(103, 'Robert', 'Williams', 'rwilliams@college.edu', '555-1003', 2, '2008-03-10', 78000),
(104, 'Emily', 'Brown', 'ebrown@college.edu', '555-1004', 3, '2015-11-05', 75000),
(105, 'Michael', 'Davis', 'mdavis@college.edu', '555-1005', 4, '2017-09-12', 68000),
(106, 'Jennifer', 'Miller', 'jmiller@college.edu', '555-1006', 5, '2014-07-22', 82000),
(107, 'David', 'Wilson', 'dwilson@college.edu', '555-1007', 6, '2011-02-18', 79000),
(108, 'Jessica', 'Taylor', 'jtaylor@college.edu', '555-1008', 7, '2019-04-30', 71000),
(109, 'Christopher', 'Anderson', 'canderson@college.edu', '555-1009', 8, '2016-10-15', 88000),
(110, 'Amanda', 'Thomas', 'athomas@college.edu', '555-1010', 1, '2020-01-05', 73000);

-- Students
INSERT INTO Students VALUES
(1001, 'Michael', 'Davis', 'mdavis@student.college.edu', '555-2001', '2020-09-01', 1),
(1002, 'Jennifer', 'Miller', 'jmiller@student.college.edu', '555-2002', '2021-01-15', 1),
(1003, 'David', 'Wilson', 'dwilson@student.college.edu', '555-2003', '2019-08-25', 2),
(1004, 'Jessica', 'Taylor', 'jtaylor@student.college.edu', '555-2004', '2022-09-01', 4),
(1005, 'Christopher', 'Anderson', 'canderson@student.college.edu', '555-2005', '2020-09-01', 3),
(1006, 'Amanda', 'Thomas', 'athomas@student.college.edu', '555-2006', '2021-09-01', 5),
(1007, 'James', 'Jackson', 'jjackson@student.college.edu', '555-2007', '2022-01-15', 6),
(1008, 'Sarah', 'White', 'swhite@student.college.edu', '555-2008', '2021-09-01', 7),
(1009, 'Robert', 'Harris', 'rharris@student.college.edu', '555-2009', '2020-09-01', 8),
(1010, 'Linda', 'Martin', 'lmartin@student.college.edu', '555-2010', '2022-09-01', 1),
(1011, 'William', 'Thompson', 'wthompson@student.college.edu', '555-2011', '2019-09-01', 2),
(1012, 'Elizabeth', 'Garcia', 'egarcia@student.college.edu', '555-2012', '2021-09-01', 3),
(1013, 'Richard', 'Martinez', 'rmartinez@student.college.edu', '555-2013', '2020-01-15', 4),
(1014, 'Susan', 'Robinson', 'srobinson@student.college.edu', '555-2014', '2022-09-01', 5),
(1015, 'Joseph', 'Clark', 'jclark@student.college.edu', '555-2015', '2021-09-01', 6);

-- Courses
INSERT INTO Courses VALUES
(501, 'CS101', 'Introduction to Programming', 4, 1, 'Fundamentals of programming using Python'),
(502, 'CS201', 'Data Structures', 4, 1, 'Study of fundamental data structures and algorithms'),
(503, 'CS301', 'Database Systems', 4, 1, 'Design and implementation of database systems'),
(504, 'MATH101', 'Calculus I', 4, 2, 'Introduction to differential and integral calculus'),
(505, 'MATH202', 'Calculus II', 4, 2, 'Advanced calculus topics'),
(506, 'PHYS101', 'General Physics', 4, 3, 'Fundamentals of mechanics and thermodynamics'),
(507, 'PHYS201', 'Modern Physics', 4, 3, 'Introduction to relativity and quantum mechanics'),
(508, 'ENG101', 'Composition', 3, 4, 'Basic writing and composition skills'),
(509, 'ENG201', 'Advanced Composition', 3, 4, 'Advanced writing techniques'),
(510, 'BIO101', 'General Biology', 4, 5, 'Introduction to biological principles'),
(511, 'CHEM101', 'General Chemistry', 4, 6, 'Fundamentals of chemical principles'),
(512, 'HIST101', 'World History', 3, 7, 'Survey of world history'),
(513, 'ECON101', 'Principles of Economics', 3, 8, 'Introduction to micro and macro economics'),
(514, 'CS401', 'Software Engineering', 4, 1, 'Software development methodologies'),
(515, 'MATH301', 'Linear Algebra', 4, 2, 'Vector spaces and linear transformations');

-- Classes
INSERT INTO Classes VALUES
(10001, 501, 101, 'Fall', 2023, 'ENG-101', 'MWF 10:00-10:50', 30, 28),
(10002, 501, 102, 'Spring', 2024, 'ENG-102', 'TTh 11:00-12:15', 30, 25),
(10003, 502, 101, 'Spring', 2024, 'SCI-205', 'MWF 13:00-13:50', 25, 22),
(10004, 503, 110, 'Fall', 2023, 'SCI-210', 'TTh 09:00-10:15', 20, 18),
(10005, 504, 103, 'Fall', 2023, 'SCI-110', 'TTh 14:00-15:15', 35, 30),
(10006, 505, 103, 'Spring', 2024, 'SCI-110', 'MWF 11:00-11:50', 35, 25),
(10007, 506, 104, 'Fall', 2023, 'SCI-201', 'MWF 09:00-09:50', 30, 22),
(10008, 507, 104, 'Spring', 2024, 'SCI-201', 'TTh 13:00-14:15', 25, 20),
(10009, 508, 105, 'Fall', 2023, 'HUM-101', 'MWF 14:00-14:50', 40, 35),
(10010, 509, 105, 'Spring', 2024, 'HUM-101', 'TTh 10:00-11:15', 35, 30),
(10011, 510, 106, 'Fall', 2023, 'SCI-301', 'MWF 12:00-12:50', 30, 25),
(10012, 511, 107, 'Spring', 2024, 'SCI-302', 'TTh 15:00-16:15', 30, 22),
(10013, 512, 108, 'Fall', 2023, 'HUM-201', 'MWF 15:00-15:50', 40, 32),
(10014, 513, 109, 'Spring', 2024, 'BUS-101', 'TTh 16:00-17:15', 35, 28),
(10015, 514, 102, 'Fall', 2023, 'ENG-201', 'MWF 16:00-16:50', 25, 20),
(10016, 515, 103, 'Spring', 2024, 'SCI-111', 'TTh 08:00-09:15', 30, 25);

-- Enrollment
INSERT INTO Enrollment VALUES
(1, 1001, 10001, '2023-08-20', 'A'),
(2, 1002, 10001, '2023-08-21', 'B+'),
(3, 1003, 10001, '2023-08-22', 'B'),
(4, 1004, 10009, '2023-08-20', 'A-'),
(5, 1005, 10007, '2023-08-21', 'B+'),
(6, 1006, 10011, '2023-08-22', 'A'),
(7, 1007, 10011, '2023-08-23', 'B'),
(8, 1008, 10013, '2023-08-20', 'C+'),
(9, 1009, 10013, '2023-08-21', 'A'),
(10, 1010, 10001, '2023-08-23', 'B-'),
(11, 1011, 10005, '2023-08-20', 'A'),
(12, 1012, 10007, '2023-08-21', 'B+'),
(13, 1013, 10009, '2023-08-22', 'A-'),
(14, 1014, 10011, '2023-08-23', 'B'),
(15, 1015, 10013, '2023-08-20', 'C'),
(16, 1001, 10003, '2024-01-10', NULL),
(17, 1002, 10002, '2024-01-11', NULL),
(18, 1003, 10006, '2024-01-12', NULL),
(19, 1004, 10010, '2024-01-10', NULL),
(20, 1005, 10008, '2024-01-11', NULL),
(21, 1006, 10012, '2024-01-12', NULL),
(22, 1007, 10012, '2024-01-10', NULL),
(23, 1008, 10014, '2024-01-11', NULL),
(24, 1009, 10014, '2024-01-12', NULL),
(25, 1010, 10003, '2024-01-10', NULL),
(26, 1011, 10006, '2024-01-11', NULL),
(27, 1012, 10008, '2024-01-12', NULL),
(28, 1013, 10010, '2024-01-10', NULL),
(29, 1014, 10012, '2024-01-11', NULL),
(30, 1015, 10014, '2024-01-12', NULL);

-- Prerequisites
INSERT INTO Prerequisites VALUES
(1, 502, 501, 'C'),
(2, 503, 502, 'B-'),
(3, 505, 504, 'C'),
(4, 507, 506, 'C+'),
(5, 509, 508, 'B-'),
(6, 514, 503, 'B'),
(7, 515, 505, 'C+');

-- Create a user for the application (optional)
CREATE USER IF NOT EXISTS 'college_admin'@'localhost' IDENTIFIED BY 'securepassword';
GRANT ALL PRIVILEGES ON college_dbms.* TO 'college_admin'@'localhost';
FLUSH PRIVILEGES;