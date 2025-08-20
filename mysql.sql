-- DiceyTech Innovation Database Setup
-- Run this SQL code to create the database and tables

-- Create the database
CREATE DATABASE IF NOT EXISTS diceytech_db;
USE diceytech_db;

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    status ENUM('pending', 'approved', 'blocked') DEFAULT 'pending',
    has_special BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create lessons table
CREATE TABLE IF NOT EXISTS lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    content LONGTEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    is_special BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create messages table for broadcasts
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create student_lessons table for tracking lesson access
CREATE TABLE IF NOT EXISTS student_lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    lesson_id INT NOT NULL,
    completed BOOLEAN DEFAULT FALSE,
    progress INT DEFAULT 0,
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_lesson (student_id, lesson_id)
);

-- Insert sample lessons
INSERT INTO lessons (title, description, content, category, is_special) VALUES
('Introduction to Ethical Hacking', 'Learn the fundamentals of ethical hacking and cybersecurity', 
'# Introduction to Ethical Hacking\n\nEthical hacking is the practice of intentionally probing systems and networks to find security vulnerabilities...\n\n## Topics Covered:\n- What is ethical hacking?\n- Legal considerations\n- Common tools and techniques\n- Setting up a lab environment\n\n## Prerequisites:\n- Basic networking knowledge\n- Linux command line familiarity', 
'Ethical Hacking', FALSE),

('Advanced Penetration Testing', 'Deep dive into penetration testing methodologies', 
'# Advanced Penetration Testing\n\nThis advanced course covers sophisticated penetration testing techniques...\n\n## Advanced Topics:\n- Network pivoting\n- Advanced exploitation techniques\n- Post-exploitation strategies\n- Report writing\n\n## Warning:\nThis content is for authorized security professionals only.', 
'Ethical Hacking', TRUE),

('Web Design Fundamentals', 'Learn HTML, CSS, and JavaScript basics', 
'# Web Design Fundamentals\n\nStart your journey into web development with this comprehensive course...\n\n## What You Will Learn:\n- HTML5 structure and semantics\n- CSS3 styling and layouts\n- JavaScript programming basics\n- Responsive design principles\n\n## Projects:\n- Build a personal portfolio\n- Create a business website', 
'Web Design', FALSE),

('Termux Command Mastery', 'Master the Android terminal emulator', 
'# Termux Command Mastery\n\nLearn to use Termux effectively for mobile penetration testing...\n\n## Commands Covered:\n- Package management\n- Network tools\n- File operations\n- Automation scripts\n\n## Tools Installation:\n- Nmap\n- Metasploit\n- SQLmap\n- Custom scripts', 
'Termux', FALSE),

('GitHub Collaboration Best Practices', 'Learn version control and team collaboration', 
'# GitHub Collaboration\n\nMaster Git and GitHub for professional software development...\n\n## Topics:\n- Git fundamentals\n- Branching strategies\n- Pull requests\n- Code reviews\n- Project management\n\n## Hands-on Projects:\n- Contribute to open source\n- Team collaboration simulation', 
'GitHub', FALSE),

('Special Operations: Advanced Techniques', 'Exclusive high-level security techniques', 
'# Special Operations\n\n⚠️ RESTRICTED ACCESS ONLY ⚠️\n\nThis course contains advanced security techniques available only to specially approved students...\n\n## Exclusive Content:\n- Advanced bypass techniques\n- Zero-day research methodology\n- Custom tool development\n- Professional consulting strategies\n\n## Access Requirements:\n- Must be approved by admin\n- Sign additional agreements\n- Demonstrate prior knowledge', 
'Other', TRUE);

-- Insert sample broadcast messages
INSERT INTO messages (title, content) VALUES
('Welcome to DiceyTech Innovation!', 'Welcome to our IT Security School! We are excited to have you join our community of ethical hackers and security professionals. Please complete your profile and wait for admin approval to access courses.'),

('New Course Available: Web Design Fundamentals', 'We have just released a comprehensive web design course covering HTML5, CSS3, and JavaScript. This course is perfect for beginners looking to start their web development journey.'),

('Security Notice: Course Access', 'Please note that all advanced security courses require admin approval. These courses contain sensitive information and are only available to verified students. Contact admin if you need special access.');

-- Insert sample student (for testing)
INSERT INTO students (name, email, phone, status, has_special) VALUES
('Test Student', 'test@example.com', '+1234567890', 'approved', FALSE),
('Special Student', 'special@example.com', '+1234567891', 'approved', TRUE);

-- Create indexes for better performance
CREATE INDEX idx_students_status ON students(status);
CREATE INDEX idx_students_email ON students(email);
CREATE INDEX idx_lessons_category ON lessons(category);
CREATE INDEX idx_lessons_special ON lessons(is_special);
CREATE INDEX idx_messages_created ON messages(created_at);

-- Display table structures for verification
DESCRIBE students;
DESCRIBE lessons;
DESCRIBE messages;
DESCRIBE student_lessons;

-- Show sample data
SELECT 'Students' as Table_Name;
SELECT * FROM students;

SELECT 'Lessons' as Table_Name;
SELECT id, title, category, is_special FROM lessons;

SELECT 'Messages' as Table_Name;
SELECT * FROM messages;

-- Database setup complete message
SELECT 'Database setup completed successfully!' as Status;