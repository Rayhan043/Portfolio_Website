-- Run this once in the existing portfolio database using phpMyAdmin.
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
