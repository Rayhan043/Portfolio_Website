-- =====================================================
-- ML Command Center Portfolio - Database Schema
-- =====================================================

CREATE DATABASE IF NOT EXISTS ml_command_center CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ml_command_center;

-- ---------------------------------------------------
-- Admin users
-- ---------------------------------------------------
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: username = admin / password = ChangeMe123!
-- (hash generated with password_hash, see README to regenerate)
INSERT INTO admin_users (username, password_hash, email) VALUES
('admin', '$2b$10$VQVIgn.TQMqnMIi7RzaEiuNJVIrfr1.cbNzO4.sY3/u9BEEYpWRhm', 'admin@example.com');

-- ---------------------------------------------------
-- Site / profile settings (About, career goal, links, contact info)
-- ---------------------------------------------------
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO site_settings (setting_key, setting_value) VALUES
('full_name', 'Your Name'),
('tagline', 'Machine Learning Engineer'),
('about_me', 'Aspiring Machine Learning Engineer focused on building and deploying real-world ML systems.'),
('current_learning_path', 'Deep Learning, MLOps, and Large Language Models'),
('career_goal', 'To build production-grade ML systems that solve real problems at scale.'),
('github_url', 'https://github.com/yourusername'),
('linkedin_url', 'https://linkedin.com/in/yourusername'),
('email', 'you@example.com'),
('phone', '+880 000 000000'),
('location', 'Dhaka, Bangladesh'),
('twitter_url', ''),
('resume_downloads', '0');

-- ---------------------------------------------------
-- Projects (Project Vault)
-- ---------------------------------------------------
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    technologies VARCHAR(255) NOT NULL,
    github_link VARCHAR(255),
    demo_link VARCHAR(255),
    thumbnail VARCHAR(255),
    video_path VARCHAR(255),
    status ENUM('active','archived') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE project_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Skills matrix
-- ---------------------------------------------------
CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(100) NOT NULL,
    proficiency INT NOT NULL DEFAULT 70, -- 0-100
    sort_order INT DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO skills (name, category, proficiency) VALUES
('Python', 'Core', 90),
('Machine Learning', 'ML', 80),
('Deep Learning', 'ML', 70),
('Data Analysis', 'Data', 85),
('SQL', 'Core', 80),
('Data Visualization', 'Data', 75),
('Statistics', 'ML', 70),
('NLP', 'ML', 60),
('Computer Vision', 'ML', 55),
('Git/GitHub', 'Tools', 85);

-- ---------------------------------------------------
-- Experience timeline
-- ---------------------------------------------------
CREATE TABLE experience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    organization VARCHAR(150) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE NULL, -- NULL = present
    sort_order INT DEFAULT 0
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Certifications
-- ---------------------------------------------------
CREATE TABLE certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    issuer VARCHAR(150),
    issue_date DATE,
    image_path VARCHAR(255),
    pdf_path VARCHAR(255),
    sort_order INT DEFAULT 0
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Resume
-- ---------------------------------------------------
CREATE TABLE resume (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Contact messages
-- ---------------------------------------------------
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    company VARCHAR(150),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    is_archived TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ---------------------------------------------------
-- Research papers (public)
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS research_papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    authors VARCHAR(255) DEFAULT NULL,
    abstract TEXT,
    paper_url VARCHAR(500) DEFAULT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    published_date DATE DEFAULT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Private, password-protected study notes
-- Store files in uploads/private_notes with direct access denied.
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS private_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO site_settings (setting_key, setting_value)
VALUES ('profile_image', '')
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);
